<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->orderBy('name')
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $data = $request->validated();

        $user = User::query()->create([
            'tenant_id' => $actor->tenant_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->syncAuthorizedFarms($user, $actor, $data['farm_ids'] ?? []);
        $this->audit($actor, 'user.created', $user, null, Arr::except($user->toArray(), ['password']));

        return redirect()->route('users.show', $user)->with('success', 'Usuário criado com sucesso.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $before = $user->only(['name', 'email', 'is_active']);
        $data = $request->validated();

        $user->fill(Arr::only($data, ['name', 'email', 'is_active']));
        $user->is_active = $request->boolean('is_active');

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        if (array_key_exists('farm_ids', $data)) {
            $this->syncAuthorizedFarms($user, $actor, $data['farm_ids']);
        }

        $this->audit($actor, 'user.updated', $user, $before, $user->only(['name', 'email', 'is_active']));

        return redirect()->route('users.show', $user)->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $before = $user->only(['name', 'email', 'is_active']);
        $user->delete();
        $this->audit($request->user(), 'user.deleted', $user, $before, null);

        return redirect()->route('users.index')->with('success', 'Usuário removido com sucesso.');
    }

    private function syncAuthorizedFarms(User $user, User $actor, array $farmIds): void
    {
        $authorizedFarmIds = Farm::query()
            ->where('tenant_id', $actor->tenant_id)
            ->whereIn('id', $farmIds)
            ->pluck('id')
            ->all();

        $sync = collect($authorizedFarmIds)->mapWithKeys(fn (string $farmId) => [
            $farmId => [
                'id' => (string) str()->uuid(),
                'tenant_id' => $actor->tenant_id,
                'is_active' => true,
            ],
        ])->all();

        $user->farms()->sync($sync);
    }

    private function audit(User $actor, string $action, User $target, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::query()->create([
            'tenant_id' => $actor->tenant_id,
            'user_id' => $actor->id,
            'action' => $action,
            'auditable_type' => User::class,
            'auditable_id' => $target->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'created_at' => now(),
        ]);
    }
}
