<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('smoke test: all public, admin, and guru routes return 200 with no 500 and clean logs', function () {
    $logPath = storage_path('logs/laravel.log');
    $logOffset = file_exists($logPath) ? filesize($logPath) : 0;

    // Run full database seeder
    $this->seed(DatabaseSeeder::class);

    $admin = User::factory()->admin()->create();
    $guru = User::where('role', 'guru')->where('must_change_password', false)->first();

    // 1. Public and Auth routes
    $this->get('/')->assertStatus(200);
    $this->get('/login')->assertStatus(200);

    // 2. Admin routes
    $adminRoutes = [
        '/admin/dashboard',
        '/admin/regions',
        '/admin/clusters',
        '/admin/environments',
        '/admin/classes',
        '/admin/students',
        '/admin/teachers',
    ];

    foreach ($adminRoutes as $route) {
        $response = $this->actingAs($admin)->get($route);
        $response->assertStatus(200);
        $response->assertDontSee('500 Internal Server Error');
    }

    // 3. Guru routes
    $guruRoutes = [
        '/guru/dashboard',
        '/guru/classes',
        '/guru/students',
        '/guru/attendances',
        '/guru/developments',
        '/guru/activities',
    ];

    foreach ($guruRoutes as $route) {
        $response = $this->actingAs($guru)->get($route);
        $response->assertStatus(200);
        $response->assertDontSee('500 Internal Server Error');
    }

    // Verify storage/logs/laravel.log is empty / free of error entries
    $logContent = file_exists($logPath) ? file_get_contents($logPath) : '';
    $newLogEntries = substr($logContent, $logOffset === false ? 0 : $logOffset);
    expect($newLogEntries)->not->toContain('.ERROR:');
});
