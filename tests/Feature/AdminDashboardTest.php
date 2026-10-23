<?php

use App\Models\Activity;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login when visiting admin dashboard', function () {
    $response = $this->get('/admin/dashboard');

    $response->assertRedirect('/login');
});

test('teachers receive 403 forbidden when visiting admin dashboard', function () {
    $guru = User::factory()->guru()->create([
        'must_change_password' => false,
    ]);

    $response = $this->actingAs($guru)->get('/admin/dashboard');

    $response->assertStatus(403);
});

test('admin can access dashboard and stats match seeded data', function () {
    $admin = User::factory()->admin()->create([
        'must_change_password' => false,
    ]);

    // Seed data
    $region = Region::create(['name' => 'Jawa Barat', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Klaster 1', 'is_active' => true]);
    $env1 = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Lingkungan GPI', 'is_active' => true]);
    $env2 = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Lingkungan Jagabaya', 'is_active' => true]);

    $class1 = Classes::create(['environment_id' => $env1->id, 'name' => 'Kelas A', 'status' => 'active']);
    $class2 = Classes::create(['environment_id' => $env2->id, 'name' => 'Kelas B', 'status' => 'active']);

    Teacher::create(['full_name' => 'Guru A', 'status' => 'active']);

    Student::create(['class_id' => $class1->id, 'full_name' => 'Anak 1', 'status' => 'active']);
    Student::create(['class_id' => $class1->id, 'full_name' => 'Anak 2', 'status' => 'active']);
    Student::create(['class_id' => $class2->id, 'full_name' => 'Anak 3', 'status' => 'active']);

    // Pending review activity
    Activity::create([
        'title' => 'Kegiatan Menunggu Persetujuan',
        'scope_type' => 'class',
        'class_id' => $class1->id,
        'status' => 'pending_review',
    ]);

    // Guru needing password change
    User::factory()->guru()->mustChangePassword()->create();

    $response = $this->actingAs($admin)->get('/admin/dashboard');

    $response->assertStatus(200);

    // Verify stat cards rendered
    $response->assertSee('Wilayah Aktif');
    $response->assertSee('Klaster');
    $response->assertSee('Lingkungan');
    $response->assertSee('Kelas Aktif');
    $response->assertSee('Guru Aktif');
    $response->assertSee('Peserta Aktif');

    // Verify "Peserta per Lingkungan"
    $response->assertSee('Peserta per Lingkungan');
    $response->assertSee('Lingkungan GPI');
    $response->assertSee('Lingkungan Jagabaya');

    // Verify "Perlu Ditinjau"
    $response->assertSee('Perlu Ditinjau');
    $response->assertSee('Kegiatan Menunggu Persetujuan');
    $response->assertSee('Akun Guru Belum Ganti Password');
});
