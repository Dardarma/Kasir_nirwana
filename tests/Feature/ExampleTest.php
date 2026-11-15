<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_requires_its_own_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')->assertOk()
            ->assertDontSee('Rincian per hari')
            ->assertSee('Belum ada fitur yang tersedia');

        $role = Role::create(['name' => 'Dashboard', 'slug' => 'dashboard', 'is_active' => true]);
        $permission = Permission::create(['name' => 'Lihat Dashboard', 'slug' => 'dashboard.view']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->get('/')->assertOk()->assertSee('Rincian per hari');
        $this->get(route('stok.laba_rugi'))->assertForbidden();
    }
}
