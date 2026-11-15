<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_permissions_for_all_existing_features_and_assigns_them_to_super_admin(): void
    {
        $this->seed(AccessControlSeeder::class);

        $expectedSlugs = array_keys(config('access.permissions'));
        $superAdmin = Role::where('slug', 'super-admin')->firstOrFail();

        $this->assertDatabaseCount('permissions', count($expectedSlugs));

        foreach ($expectedSlugs as $slug) {
            $this->assertDatabaseHas('permissions', ['slug' => $slug]);
        }

        $this->assertEqualsCanonicalizing(
            $expectedSlugs,
            $superAdmin->permissions()->pluck('slug')->all(),
        );

        $customPermission = Permission::create([
            'name' => 'Permission Tambahan',
            'slug' => 'custom.extra',
        ]);
        $superAdmin->permissions()->attach($customPermission);

        $this->seed(AccessControlSeeder::class);

        $this->assertTrue($superAdmin->permissions()->whereKey($customPermission->id)->exists());
    }

    public function test_super_admin_can_manage_roles_and_assign_permissions(): void
    {
        $this->seed(AccessControlSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'super-admin')->firstOrFail());
        $permission = Permission::where('slug', 'users.view')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSeeText('Manajemen Role');

        $this->actingAs($admin)
            ->post(route('roles.store'), [
                'name' => 'Operator',
                'slug' => 'operator',
                'description' => 'Operator aplikasi',
                'is_active' => '1',
                'permissions' => [$permission->id],
            ])
            ->assertRedirect(route('roles.index'));

        $role = Role::where('slug', 'operator')->firstOrFail();
        $this->assertTrue($role->permissions()->whereKey($permission->id)->exists());
    }

    public function test_user_without_role_management_permission_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('roles.index'))
            ->assertForbidden();
    }

    public function test_roles_manage_permission_does_not_grant_role_management_to_non_super_admin(): void
    {
        $this->seed(AccessControlSeeder::class);

        $role = Role::create(['name' => 'Operator', 'slug' => 'operator', 'is_active' => true]);
        $role->permissions()->attach(Permission::where('slug', 'roles.manage')->firstOrFail());
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $this->actingAs($user)->get(route('roles.index'))->assertForbidden();
        $this->actingAs($user)->get(route('permissions.index'))->assertForbidden();
    }

    public function test_super_admin_can_manage_custom_permissions_but_not_delete_system_permissions(): void
    {
        $this->seed(AccessControlSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'super-admin')->firstOrFail());

        $this->actingAs($admin)
            ->get(route('permissions.index'))
            ->assertOk()
            ->assertSeeText('Pengaturan Permission')
            ->assertSee('class="pagination"', false)
            ->assertSee('page-item', false);

        $this->actingAs($admin)
            ->post(route('permissions.store'), [
                'name' => 'Lihat Laporan Khusus',
                'slug' => 'laporan-khusus.view',
                'description' => 'Akses laporan khusus.',
            ])
            ->assertRedirect(route('permissions.index'));

        $customPermission = Permission::where('slug', 'laporan-khusus.view')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('permissions.update', $customPermission), [
                'name' => 'Lihat Laporan Rahasia',
                'description' => 'Penjelasan diperbarui.',
            ])
            ->assertRedirect(route('permissions.index'));

        $this->assertDatabaseHas('permissions', [
            'id' => $customPermission->id,
            'name' => 'Lihat Laporan Rahasia',
        ]);

        $this->actingAs($admin)
            ->delete(route('permissions.destroy', $customPermission))
            ->assertRedirect();
        $this->assertDatabaseMissing('permissions', ['id' => $customPermission->id]);

        $systemPermission = Permission::where('slug', 'users.view')->firstOrFail();
        $this->actingAs($admin)
            ->delete(route('permissions.destroy', $systemPermission))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('permissions', ['id' => $systemPermission->id]);
    }

    public function test_user_permissions_limit_user_management_actions(): void
    {
        $this->seed(AccessControlSeeder::class);

        $viewerRole = Role::create([
            'name' => 'User Viewer',
            'slug' => 'user-viewer',
            'is_active' => true,
        ]);
        $viewerRole->permissions()->attach(Permission::where('slug', 'users.view')->firstOrFail());

        $viewer = User::factory()->create();
        $viewer->roles()->attach($viewerRole);

        $this->actingAs($viewer)->get(route('users.index'))->assertOk();
        $this->actingAs($viewer)->post(route('users.store'), [])->assertForbidden();
    }
}
