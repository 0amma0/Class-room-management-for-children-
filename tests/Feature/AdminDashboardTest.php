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
use Illuminate\Support\Facades\Storage;

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
    $response->assertSee(
        '<link rel="icon" type="image/png" href="'.asset('images/Avatar/Bangs-01.png').'?v=1">',
        false,
    );

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

test('admin teacher assignment form filters classes through their location hierarchy', function () {
    $admin = User::factory()->admin()->create();
    $region = Region::create(['name' => 'Region Filter', 'code' => 'REG-FILTER', 'is_active' => true]);
    $cluster = Cluster::create([
        'region_id' => $region->id,
        'name' => 'Cluster Filter',
        'code' => 'CLU-FILTER',
        'is_active' => true,
    ]);
    $environment = Environment::create([
        'cluster_id' => $cluster->id,
        'name' => 'Environment Filter',
        'code' => 'ENV-FILTER',
        'is_active' => true,
    ]);
    Classes::create([
        'environment_id' => $environment->id,
        'name' => 'Class Filter',
        'code' => 'CLS-FILTER',
        'status' => 'active',
    ]);

    $this->actingAs($admin)->get(route('admin.teachers'))
        ->assertOk()
        ->assertSee('data-filter="region"', false)
        ->assertSee('data-filter="cluster"', false)
        ->assertSee('data-filter="environment"', false)
        ->assertSee('data-region-id="'.$region->id.'"', false)
        ->assertSee('data-cluster-id="'.$cluster->id.'"', false)
        ->assertSee('Class Filter')
        ->assertSee('name="classes[]"', false);
});

test('the teacher avatar stored on their account is displayed in their dashboard and the admin teacher list', function () {
    Storage::fake('public');
    Storage::disk('public')->put('avatars/teacher-profile.png', 'avatar image');
    $teacher = Teacher::create(['full_name' => 'Teacher Avatar', 'status' => 'active']);
    $guru = User::factory()->guru()->create([
        'teacher_id' => $teacher->id,
        'avatar_path' => 'avatars/teacher-profile.png',
        'must_change_password' => false,
    ]);
    $admin = User::factory()->admin()->create();
    $avatarUrl = Storage::disk('public')->url('avatars/teacher-profile.png');

    $this->actingAs($guru)->get(route('guru.dashboard'))
        ->assertOk()
        ->assertSee($avatarUrl, false);
    $this->actingAs($admin)->get(route('admin.teachers'))
        ->assertOk()
        ->assertSee($avatarUrl, false);
});
