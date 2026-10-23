<?php

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('a public visitor can start a private conversation and receive an admin reply', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->post(route('support.public.store'), [
        'name' => 'Pengunjung',
        'body' => 'Saya ingin bertanya.',
    ]);

    $response->assertRedirect();
    $token = Str::afterLast(parse_url($response->headers->get('Location'), PHP_URL_PATH), '/');
    expect($token)->toHaveLength(64);
    $conversation = SupportConversation::query()->firstOrFail();
    expect($conversation->guest_token_hash)->toBe(hash('sha256', $token));
    expect($conversation->guest_token_hash)->not->toBe($token);
    $this->assertDatabaseHas('support_messages', [
        'support_conversation_id' => $conversation->id,
        'sender_type' => 'public',
        'body' => 'Saya ingin bertanya.',
    ]);
    $this->assertDatabaseHas('app_notifications', [
        'user_id' => $admin->id,
        'support_conversation_id' => $conversation->id,
        'type' => 'support',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.support.messages.store', $conversation), ['body' => 'Admin akan membantu.'])
        ->assertRedirect(route('admin.support', ['conversation_id' => $conversation->id]));

    $this->get(route('support.public.show', $token))
        ->assertOk()
        ->assertSee('Saya ingin bertanya.')
        ->assertSee('Admin akan membantu.');
    $this->assertDatabaseHas('support_messages', [
        'support_conversation_id' => $conversation->id,
        'sender_type' => 'admin',
        'body' => 'Admin akan membantu.',
    ]);
});

test('a teacher can contact the admin and cannot read or reply to another teacher conversation', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->guru()->create();
    $otherTeacher = User::factory()->guru()->create();

    $this->actingAs($teacher)
        ->post(route('guru.support.store'), ['body' => 'Saya membutuhkan bantuan.'])
        ->assertRedirect(route('guru.support'));

    $conversation = SupportConversation::query()->firstOrFail();
    expect($conversation->user_id)->toBe($teacher->id);
    $this->assertDatabaseHas('app_notifications', [
        'support_conversation_id' => $conversation->id,
        'type' => 'support',
    ]);

    $this->actingAs($otherTeacher)->get(route('guru.support'))
        ->assertOk()
        ->assertDontSee('Saya membutuhkan bantuan.');
    $this->actingAs($otherTeacher)
        ->post(route('guru.support.messages.store', $conversation), ['body' => 'Pesan yang tidak berhak'])
        ->assertNotFound();

    expect(SupportMessage::query()->where('support_conversation_id', $conversation->id)->count())->toBe(1);

    $this->actingAs($admin)
        ->post(route('admin.support.messages.store', $conversation), ['body' => 'Berikut bantuan dari Admin.'])
        ->assertRedirect(route('admin.support', ['conversation_id' => $conversation->id]));
    $this->assertDatabaseHas('app_notifications', [
        'user_id' => $teacher->id,
        'support_conversation_id' => $conversation->id,
        'type' => 'support',
    ]);
});

test('a public conversation rejects a missing message without creating records', function () {
    $this->post(route('support.public.store'), ['body' => ''])
        ->assertSessionHasErrors('body');

    $this->assertDatabaseCount('support_conversations', 0);
    $this->assertDatabaseCount('support_messages', 0);
});

test('an administrator can delete any conversation and its related messages and notifications', function () {
    $admin = User::factory()->admin()->create();
    $conversation = SupportConversation::query()->create([
        'source' => 'public',
        'guest_name' => 'Pengunjung',
        'guest_token_hash' => hash('sha256', Str::random(64)),
    ]);
    $message = $conversation->messages()->create([
        'sender_type' => 'public',
        'body' => 'Pertanyaan lama',
    ]);
    $notification = $admin->appNotifications()->create([
        'type' => 'support',
        'support_conversation_id' => $conversation->id,
        'title' => 'Pesan bantuan baru',
        'message' => 'Pertanyaan lama',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.support.destroy', $conversation))
        ->assertRedirect(route('admin.support'));

    $this->assertDatabaseMissing('support_conversations', ['id' => $conversation->id]);
    $this->assertDatabaseMissing('support_messages', ['id' => $message->id]);
    $this->assertDatabaseMissing('app_notifications', ['id' => $notification->id]);
});

test('a teacher can delete only their own support conversation', function () {
    $teacher = User::factory()->guru()->create();
    $otherTeacher = User::factory()->guru()->create();
    $ownedConversation = SupportConversation::query()->create([
        'user_id' => $teacher->id,
        'source' => 'guru',
    ]);
    $otherConversation = SupportConversation::query()->create([
        'user_id' => $otherTeacher->id,
        'source' => 'guru',
    ]);

    $this->actingAs($teacher)
        ->delete(route('guru.support.destroy', $otherConversation))
        ->assertNotFound();

    $this->assertModelExists($otherConversation);

    $this->delete(route('guru.support.destroy', $ownedConversation))
        ->assertRedirect(route('guru.support'));

    $this->assertDatabaseMissing('support_conversations', ['id' => $ownedConversation->id]);
});
