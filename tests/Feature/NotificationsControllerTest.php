<?php

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('notification history is grouped by date', function () {
    $user = User::factory()->guru()->create();
    $today = now()->setTime(10, 0);

    AppNotification::factory()->create([
        'user_id' => $user->id,
        'title' => 'Notifikasi hari ini',
        'created_at' => $today,
    ]);
    AppNotification::factory()->create([
        'user_id' => $user->id,
        'title' => 'Notifikasi kemarin',
        'created_at' => $today->copy()->subDay(),
    ]);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Hari ini')
        ->assertSee('Kemarin')
        ->assertSeeInOrder(['Hari ini', 'Notifikasi hari ini', 'Kemarin', 'Notifikasi kemarin']);
});
