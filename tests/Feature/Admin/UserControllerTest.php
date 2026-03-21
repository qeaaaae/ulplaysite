<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function test_index_returns_200(): void
    {
        $this->actingAsAdmin();
        User::factory()->count(2)->create();

        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(200);
    }

    public function test_store_creates_user(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newadmin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => false,
            'is_blocked' => false,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'newadmin@test.com']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_create_returns_200(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.users.create'));

        $response->assertStatus(200);
    }

    public function test_edit_returns_200(): void
    {
        $this->actingAsAdmin();
        $user = User::factory()->create();

        $response = $this->get(route('admin.users.edit', $user));

        $response->assertStatus(200);
    }

    public function test_update_modifies_user(): void
    {
        $this->actingAsAdmin();
        $user = User::factory()->create();

        $response = $this->patch(route('admin.users.update', $user), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'phone' => $user->phone,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertSame('Updated Name', $user->fresh()->name);
    }

    public function test_destroy_deletes_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $this->actingAs($admin);

        $response = $this->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_destroy_prevents_self_deletion(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $response = $this->delete(route('admin.users.destroy', $user));

        $response->assertSessionHas('error');
        $this->assertNotNull(User::find($user->id));
    }

    public function test_block_blocks_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $this->actingAs($admin);

        $response = $this->post(route('admin.users.block', $user));

        $response->assertSessionHas('message');
        $this->assertTrue($user->fresh()->is_blocked);
    }

    public function test_block_prevents_self_block(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $response = $this->post(route('admin.users.block', $user));

        $response->assertSessionHas('error');
    }

    public function test_unblock_unblocks_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_blocked' => true]);
        $this->actingAs($admin);

        $response = $this->post(route('admin.users.unblock', $user));

        $response->assertSessionHas('message');
        $this->assertFalse($user->fresh()->is_blocked);
    }

    public function test_restore_restores_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $user->delete();
        $this->actingAs($admin);

        $response = $this->post(route('admin.users.restore', $user));

        $response->assertSessionHas('message');
        $this->assertNull($user->fresh()->deleted_at);
    }

    public function test_restore_prevents_self_restore(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $response = $this->post(route('admin.users.restore', $user));

        $response->assertSessionHas('error');
    }

    public function test_update_validates_email_unique_except_self(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['email' => 'user@test.com']);
        $other = User::factory()->create(['email' => 'other@test.com']);
        $this->actingAs($admin);

        $response = $this->patch(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $other->email,
        ]);

        $response->assertSessionHasErrors('email');
    }
}
