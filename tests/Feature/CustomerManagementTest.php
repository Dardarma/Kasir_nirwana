<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_and_user_management_use_separate_pages_and_tables(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Akses pengujian.',
            'is_active' => true,
        ]));

        $this->actingAs($user)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Manajemen User');

        $this->actingAs($user)
            ->get(route('customers.index'))
            ->assertOk()
            ->assertSee('Manajemen Customer');

        $this->actingAs($user)
            ->post(route('customers.store'), [
                'name' => 'Customer Baru',
                'status' => 'aktif',
            ])
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', [
            'name' => 'Customer Baru',
            'status' => 'aktif',
        ]);
        $this->assertDatabaseMissing('users', ['name' => 'Customer Baru']);
    }

    public function test_deleting_a_customer_deactivates_it_to_preserve_relations(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'name' => 'Customer Relasi',
            'status' => 'aktif',
        ]);

        $this->actingAs($user)
            ->delete(route('customers.destroy', $customer))
            ->assertForbidden();

        $role = Role::create(['name' => 'Kelola Customer', 'slug' => 'kelola-customer', 'is_active' => true]);
        $permission = Permission::create(['name' => 'Nonaktifkan Customer', 'slug' => 'customers.delete']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'status' => 'non_aktif',
        ]);
    }
}
