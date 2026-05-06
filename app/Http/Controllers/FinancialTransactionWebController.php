<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\FinancialAccount;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialTransactionWebController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->tenant_id, 403);

        $transactions = $this->query($request)
            ->latest('due_on')
            ->paginate(20)
            ->withQueryString();

        return view('finance.transactions.index', [
            'transactions' => $transactions,
            'filters' => $request->only(['farm_id', 'type', 'status', 'financial_account_id', 'financial_category_id', 'from', 'to']),
            'accounts' => $this->accounts($request),
            'categories' => $this->categories($request),
            'farms' => $this->farms($request),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->tenant_id, 403);

        return view('finance.transactions.create', [
            'accounts' => $this->accounts($request),
            'categories' => $this->categories($request),
            'farms' => $this->farms($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->tenant_id, 403);

        $data = $request->validate([
            'farm_id' => ['required', 'uuid', 'exists:farms,id'],
            'financial_account_id' => ['required', 'uuid', 'exists:financial_accounts,id'],
            'financial_category_id' => ['required', 'uuid', 'exists:financial_categories,id'],
            'type' => ['required', 'string', 'in:revenue,expense'],
            'status' => ['required', 'string', 'max:60'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_on' => ['required', 'date'],
            'paid_on' => ['nullable', 'date'],
        ]);

        abort_unless($request->user()->canAccessFarm($data['farm_id']), 403);

        $account = FinancialAccount::query()->where('tenant_id', $request->user()->tenant_id)->whereKey($data['financial_account_id'])->firstOrFail();
        $category = FinancialCategory::query()->where('tenant_id', $request->user()->tenant_id)->whereKey($data['financial_category_id'])->firstOrFail();
        abort_unless($account->farm_id === null || $account->farm_id === $data['farm_id'], 403);
        abort_unless($category->type === $data['type'], 422);

        FinancialTransaction::create($data + [
            'tenant_id' => $request->user()->tenant_id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('finance.transactions.index')->with('status', 'Transação financeira cadastrada com sucesso.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->tenant_id, 403);
        $query = $this->query($request);

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Fazenda', 'Conta', 'Categoria', 'Tipo', 'Status', 'Descrição', 'Valor', 'Vencimento', 'Pagamento']);
            $query->chunk(200, function ($records) use ($handle): void {
                foreach ($records as $record) {
                    fputcsv($handle, [$record->farm?->name, $record->account?->name, $record->category?->name, $record->type, $record->status, $record->description, $record->amount, optional($record->due_on)->format('Y-m-d'), optional($record->paid_on)->format('Y-m-d')]);
                }
            });
            fclose($handle);
        }, 'finance-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function query(Request $request)
    {
        return FinancialTransaction::query()
            ->with(['farm', 'account', 'category'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->when(! $request->user()->hasRole('admin'), fn ($query) => $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id')))
            ->when($request->filled('farm_id'), fn ($query) => $query->where('farm_id', $request->input('farm_id')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('financial_account_id'), fn ($query) => $query->where('financial_account_id', $request->input('financial_account_id')))
            ->when($request->filled('financial_category_id'), fn ($query) => $query->where('financial_category_id', $request->input('financial_category_id')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('due_on', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('due_on', '<=', $request->input('to')));
    }

    private function accounts(Request $request): array
    {
        return FinancialAccount::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->pluck('name', 'id')->all();
    }

    private function categories(Request $request): array
    {
        return FinancialCategory::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->pluck('name', 'id')->all();
    }

    private function farms(Request $request): array
    {
        $query = Farm::query()->where('tenant_id', $request->user()->tenant_id);
        if (! $request->user()->hasRole('admin')) {
            $query->whereIn('id', $request->user()->farms()->pluck('farms.id'));
        }
        return $query->orderBy('name')->pluck('name', 'id')->all();
    }
}
