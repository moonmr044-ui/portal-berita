<?php

declare(strict_types=1);

namespace Tests\Feature\Newsroom;

use App\Enums\RoleName;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\NewsPortalDemoSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KruAndCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(NewsPortalDemoSeeder::class);
    }
    public function test_super_admin_can_access_edit_kru_page(): void
    {
        $superAdmin = User::role(RoleName::SUPER_ADMIN->value)->first();
        $targetUser = User::role(RoleName::REPORTER->value)->first();

        $response = $this->actingAs($superAdmin)
            ->withSession([
                'crew_last_activity' => now()->timestamp,
                'email_otp_verified' => true,
            ])
            ->get(route('newsroom.users.edit', $targetUser));

        $response->assertOk();
        $response->assertSee('Sunting Data Akun Kru');
    }

    public function test_super_admin_can_update_kru(): void
    {
        $superAdmin = User::role(RoleName::SUPER_ADMIN->value)->first();
        $targetUser = User::role(RoleName::REPORTER->value)->first();

        $response = $this->actingAs($superAdmin)
            ->withSession([
                'crew_last_activity' => now()->timestamp,
                'email_otp_verified' => true,
            ])
            ->put(route('newsroom.users.update', $targetUser), [
                'name' => 'Nama Kru Diperbarui',
                'email' => $targetUser->email,
                'role' => RoleName::REPORTER->value,
                'is_active' => 1,
            ]);

        $response->assertRedirect(route('newsroom.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Nama Kru Diperbarui',
        ]);
    }

    public function test_super_admin_can_access_edit_category_page(): void
    {
        $superAdmin = User::role(RoleName::SUPER_ADMIN->value)->first();
        $category = Category::first();

        $response = $this->actingAs($superAdmin)
            ->withSession([
                'crew_last_activity' => now()->timestamp,
                'email_otp_verified' => true,
            ])
            ->get(route('newsroom.categories.edit', $category));

        $response->assertOk();
        $response->assertSee('Sunting Rubrik');
    }

    public function test_super_admin_can_delete_kru(): void
    {
        $superAdmin = User::role(RoleName::SUPER_ADMIN->value)->first();
        $targetUser = User::factory()->create([
            'is_crew' => true,
        ]);
        $targetUser->assignRole(RoleName::REPORTER->value);

        $response = $this->actingAs($superAdmin)
            ->withSession([
                'crew_last_activity' => now()->timestamp,
                'email_otp_verified' => true,
            ])
            ->delete(route('newsroom.users.destroy', $targetUser));

        $response->assertRedirect(route('newsroom.users.index'));
        $this->assertSoftDeleted('users', [
            'id' => $targetUser->id,
        ]);
    }
}
