<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Http\Livewire\Tenant\TenantUserManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = \App\Models\Plan::create([
            'name' => 'Basic Plan',
            'price' => 100.00,
        ]);

        $this->tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test',
            'cnpj' => '12345678000199',
            'plan_id' => $plan->id,
        ]);

        // Create admin user
        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ]);

        // Create roles
        Role::create(['name' => 'Admin Tenant', 'guard_name' => 'web']);
        Role::create(['name' => 'Operacional', 'guard_name' => 'web']);
    }

    public function test_can_create_user()
    {
        $this->actingAs($this->admin);

        Livewire::test(TenantUserManagement::class)
            ->set('name', 'New User')
            ->set('email', 'new@user.com')
            ->set('phone', '11999998888')
            ->set('password', 'password123')
            ->set('selected_roles', ['Operacional'])
            ->call('save');

        $this->assertDatabaseHas('users', [
            'email' => 'new@user.com',
            'tenant_id' => $this->tenant->id,
        ]);

        $user = User::where('email', 'new@user.com')->first();
        $this->assertTrue($user->hasRole('Operacional'));
    }

    public function test_can_edit_user()
    {
        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Old Name',
            'email' => 'old@email.com',
            'password' => 'password',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(TenantUserManagement::class)
            ->call('edit', $user)
            ->set('name', 'New Name')
            ->set('selected_roles', ['Admin Tenant'])
            ->call('save');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);

        $user->refresh();
        $this->assertTrue($user->hasRole('Admin Tenant'));
    }
}
