<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\FinancialAccount;
use App\Models\FinancialCategory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class FinanceTransactionsWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_transactions_index_loads(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();
        FinancialAccount::create(['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'name' => 'Caixa', 'type' => 'cash', 'opening_balance' => 0, 'is_active' => true]);
        FinancialCategory::create(['tenant_id' => $tenant->id, 'name' => 'Venda de animais', 'type' => 'revenue', 'is_active' => true]);

        $this->actingAs($user)
            ->get('/finance/transactions')
            ->assertOk()
            ->assertSee('Transações financeiras');
    }

    public function test_financial_transactions_create_loads(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();
        FinancialAccount::create(['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'name' => 'Caixa', 'type' => 'cash', 'opening_balance' => 0, 'is_active' => true]);
        FinancialCategory::create(['tenant_id' => $tenant->id, 'name' => 'Manutenção', 'type' => 'expense', 'is_active' => true]);

        $this->actingAs($user)
            ->get('/finance/transactions/create')
            ->assertOk()
            ->assertSee('Nova transação financeira');
    }

    private function seedTenantUserAndFarm(): array
    {
        $tenant = Tenant::create(['name' => 'Tenant Teste', 'slug' => 'tenant-teste-'.uniqid(), 'is_active' => true]);
        $user = User::create(['tenant_id' => $tenant->id, 'name' => 'Administrador Campo Aberto', 'email' => 'finance'.uniqid().'@campoaberto.local', 'password' => bcrypt('password'), 'is_active' => true]);
        $farm = Farm::create(['tenant_id' => $tenant->id, 'name' => 'Fazenda Teste', 'code' => 'FZ-'.uniqid(), 'state' => 'PE']);

        DB::table('farm_users')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'user_id' => $user->id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$tenant, $user, $farm];
    }
}
