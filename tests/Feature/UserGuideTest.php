<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('administrators see the admin guide and teachers cannot access it', function () {
    $admin = User::factory()->admin()->create();
    $guru = User::factory()->guru()->create();

    $this->actingAs($admin)
        ->get(route('admin.user-guide'))
        ->assertOk()
        ->assertSee('Mengelola akun Guru')
        ->assertSee('Backup dan ekspor data');

    $this->actingAs($guru)->get(route('admin.user-guide'))->assertForbidden();
});

test('teachers see their guide and administrators cannot access it', function () {
    $admin = User::factory()->admin()->create();
    $guru = User::factory()->guru()->create();

    $this->actingAs($guru)
        ->get(route('guru.user-guide'))
        ->assertOk()
        ->assertSee('Kelas dan peserta')
        ->assertSee('Galeri kelas');

    $this->actingAs($admin)->get(route('guru.user-guide'))->assertForbidden();
});
