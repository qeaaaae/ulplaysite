<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class CommentTest extends TestCase
{
    public function test_store_comment_succeeds(): void
    {
        $user = User::factory()->create();
        $news = News::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('comments.store', $news), [
            'body' => 'Nice article!',
        ]);

        $response->assertRedirect(route('news.show', $news));
        $this->assertDatabaseHas('comments', [
            'news_id' => $news->id,
            'user_id' => $user->id,
            'body' => 'Nice article!',
        ]);
    }

    public function test_store_comment_fails_within_cooldown(): void
    {
        $user = User::factory()->create();
        $news = News::factory()->create();
        Comment::create([
            'news_id' => $news->id,
            'user_id' => $user->id,
            'body' => 'First',
        ]);
        $this->actingAs($user);

        $response = $this->post(route('comments.store', $news), [
            'body' => 'Second comment',
        ]);

        $response->assertSessionHasErrors('body');
    }

    public function test_store_comment_succeeds_after_cooldown(): void
    {
        Carbon::setTestNow($now = Carbon::create(2025, 1, 15, 12, 0, 0));
        $user = User::factory()->create();
        $news = News::factory()->create();
        Comment::create([
            'news_id' => $news->id,
            'user_id' => $user->id,
            'body' => 'First',
        ]);
        Carbon::setTestNow($now->copy()->addSeconds(31));
        $this->actingAs($user);

        $response = $this->post(route('comments.store', $news), [
            'body' => 'Second comment',
        ]);

        Carbon::setTestNow();
        $response->assertRedirect(route('news.show', $news));
        $this->assertDatabaseCount('comments', 2);
    }

    public function test_helpful_increments_count(): void
    {
        $user = User::factory()->create();
        $news = News::factory()->create();
        $comment = Comment::create(['news_id' => $news->id, 'user_id' => User::factory()->create()->id, 'body' => 'Test']);
        $this->actingAs($user);

        $response = $this->postJson(route('comments.helpful', $comment));

        $response->assertJson(['result' => true, 'count' => 1]);
    }

    public function test_store_comment_returns_json_when_wants_json(): void
    {
        $user = User::factory()->create();
        $news = News::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('comments.store', $news), [
            'body' => 'Nice article!',
        ]);

        $response->assertOk();
        $response->assertJson(['result' => true]);
        $response->assertJsonStructure(['html']);
    }

    public function test_store_comment_returns_json_errors_within_cooldown(): void
    {
        $user = User::factory()->create();
        $news = News::factory()->create();
        Comment::create([
            'news_id' => $news->id,
            'user_id' => $user->id,
            'body' => 'First',
        ]);
        $this->actingAs($user);

        $response = $this->postJson(route('comments.store', $news), [
            'body' => 'Second',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('body');
    }

    public function test_store_comment_validates_body_required(): void
    {
        $user = User::factory()->create();
        $news = News::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('comments.store', $news), []);

        $response->assertSessionHasErrors('body');
    }

    public function test_update_own_comment_succeeds(): void
    {
        $user = User::factory()->create();
        $news = News::factory()->create();
        $comment = Comment::create(['news_id' => $news->id, 'user_id' => $user->id, 'body' => 'Original']);
        $this->actingAs($user);

        $response = $this->patchJson(route('comments.update', $comment), ['body' => 'Updated']);

        $response->assertOk();
        $response->assertJson(['result' => true, 'body' => 'Updated']);
        $comment->refresh();
        $this->assertSame('Updated', $comment->body);
        $this->assertNotNull($comment->edited_at);
    }

    public function test_update_others_comment_forbidden_for_user(): void
    {
        $user = User::factory()->create();
        $news = News::factory()->create();
        $comment = Comment::create(['news_id' => $news->id, 'user_id' => User::factory()->create()->id, 'body' => 'Original']);
        $this->actingAs($user);

        $response = $this->patchJson(route('comments.update', $comment), ['body' => 'Hacked']);

        $response->assertForbidden();
        $comment->refresh();
        $this->assertSame('Original', $comment->body);
    }

    public function test_update_comment_allowed_for_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $news = News::factory()->create();
        $comment = Comment::create(['news_id' => $news->id, 'user_id' => User::factory()->create()->id, 'body' => 'Original']);
        $this->actingAs($admin);

        $response = $this->patchJson(route('comments.update', $comment), ['body' => 'Admin edit']);

        $response->assertOk();
        $comment->refresh();
        $this->assertSame('Admin edit', $comment->body);
    }

    public function test_destroy_own_comment_succeeds(): void
    {
        $user = User::factory()->create();
        $news = News::factory()->create();
        $comment = Comment::create(['news_id' => $news->id, 'user_id' => $user->id, 'body' => 'To delete']);
        $this->actingAs($user);

        $response = $this->deleteJson(route('comments.destroy', $comment));

        $response->assertOk();
        $response->assertJson(['result' => true]);
        $this->assertModelMissing($comment);
    }

    public function test_destroy_others_comment_forbidden_for_user(): void
    {
        $user = User::factory()->create();
        $news = News::factory()->create();
        $comment = Comment::create(['news_id' => $news->id, 'user_id' => User::factory()->create()->id, 'body' => 'Keep']);
        $this->actingAs($user);

        $response = $this->deleteJson(route('comments.destroy', $comment));

        $response->assertForbidden();
        $this->assertModelExists($comment);
    }

    public function test_destroy_comment_allowed_for_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $news = News::factory()->create();
        $comment = Comment::create(['news_id' => $news->id, 'user_id' => User::factory()->create()->id, 'body' => 'To delete']);
        $this->actingAs($admin);

        $response = $this->deleteJson(route('comments.destroy', $comment));

        $response->assertOk();
        $this->assertModelMissing($comment);
    }
}
