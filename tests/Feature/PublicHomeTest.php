<?php

use App\Models\Activity;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public home page returns 200 for guests and does not leak private student or parent data', function () {
    // 1. Seed domain data
    $region = Region::create(['name' => 'Jawa Barat', 'code' => 'JB', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Klaster Utama', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Lingkungan Mawar', 'is_active' => true]);

    $class = Classes::create([
        'environment_id' => $environment->id,
        'name' => 'Kelas Melati',
        'location' => 'Balai Warga',
        'schedule' => 'Minggu 10:00',
        'status' => 'active',
    ]);

    // Create a student with identifiable private data
    $secretStudentName = 'Ananda Rahasia Pribadi';
    $secretParentName = 'Orang Tua Bapak Rahasia';
    $secretAddress = 'Alamat Sangat Rahasia No 999';

    Student::create([
        'class_id' => $class->id,
        'full_name' => $secretStudentName,
        'nickname' => 'Rahasia',
        'parent_name' => $secretParentName,
        'address' => $secretAddress,
        'status' => 'active',
    ]);

    // 2. Request public home page
    $response = $this->get('/');

    // 3. Assert HTTP 200
    $response->assertStatus(200);

    // 4. PRIVACY ASSERTIONS: Ensure sensitive private data is NEVER leaked to public HTML
    $response->assertDontSee($secretStudentName);
    $response->assertDontSee($secretParentName);
    $response->assertDontSee($secretAddress);

    // General class info should be visible
    $response->assertSee('Kelas Melati');
    $response->assertSee('Lingkungan Mawar');
    $response->assertSee('href="'.route('regions').'"', false);
    $response->assertSee('href="'.route('classes').'"', false);

    // Rule: if student count per class is < 3, it should display "< 3"
    $response->assertSee('&lt; 3', false);
});

test('public home renders gentle motion hooks without changing its content', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('home-hero-copy', false)
        ->assertSee('home-hero-art', false)
        ->assertSee('home-motion-card', false)
        ->assertSee('home-cloud-divider', false)
        ->assertSee('reveal-on-scroll', false)
        ->assertSee('new IntersectionObserver', false)
        ->assertSee('prefers-reduced-motion: reduce', false)
        ->assertSee('Learn, Play,', false)
        ->assertSee('Explore Regions', false);
});

test('public home stats match database records', function () {
    $region = Region::create(['name' => 'Jawa Barat', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Klaster 1', 'is_active' => true]);
    $env = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Lingkungan 1', 'is_active' => true]);

    $class = Classes::create(['environment_id' => $env->id, 'name' => 'Kelas 1', 'status' => 'active']);
    $teacher1 = Teacher::create(['full_name' => 'Guru 1', 'status' => 'active']);
    $teacher2 = Teacher::create(['full_name' => 'Guru 2', 'status' => 'active']);
    $class->teachers()->attach([$teacher1->id, $teacher2->id]);

    Student::create(['class_id' => $class->id, 'full_name' => 'Anak 1', 'status' => 'active']);
    Student::create(['class_id' => $class->id, 'full_name' => 'Anak 2', 'status' => 'active']);
    Student::create(['class_id' => $class->id, 'full_name' => 'Anak 3', 'status' => 'active']);

    Activity::create([
        'title' => 'Kegiatan Terbit',
        'status' => 'published',
        'activity_date' => now(),
        'scope_type' => 'class',
        'class_id' => $class->id,
    ]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Kelas Aktif');
    $response->assertSee('Guru Aktif');
    $response->assertSee('Peserta Aktif');
    $response->assertSee('Lingkungan Aktif');
    $response->assertSee('2 Teachers');
    $response->assertSee('3 Participants');
    $response->assertSee('Kegiatan Terbit');
});

test('public region page shows active communities and classes without exposing student details', function () {
    $region = Region::create(['name' => 'Jawa Barat', 'code' => 'JB', 'is_active' => true]);
    Region::create(['name' => 'Jawa Tengah', 'code' => 'JT', 'is_active' => false]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Klaster Utama', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'GPI', 'is_active' => true]);
    $class = Classes::create([
        'environment_id' => $environment->id,
        'name' => 'Kelas GPI 01',
        'schedule' => 'Saturday 09:00',
        'status' => 'active',
    ]);
    $teacher = Teacher::factory()->create(['status' => 'active']);
    $class->teachers()->attach($teacher);
    Student::create([
        'class_id' => $class->id,
        'full_name' => 'Nama Anak Rahasia',
        'parent_name' => 'Nama Orang Tua Rahasia',
        'status' => 'active',
    ]);

    $response = $this->get('/regions');

    $response->assertOk();
    $response->assertSee('West Java Region');
    $response->assertSee('GPI');
    $response->assertSee('Kelas GPI 01');
    $response->assertSee('Central Java');
    $response->assertDontSee('Nama Anak Rahasia');
    $response->assertDontSee('Nama Orang Tua Rahasia');
});

test('public region page explains when there is no active region', function () {
    $response = $this->get('/regions');

    $response->assertOk();
    $response->assertSee('No active region yet');
});

test('public classes page shows active classes and the original Book 3 module', function () {
    $region = Region::create(['name' => 'West Java', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Class Cluster', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'GPI', 'is_active' => true]);
    $class = Classes::create([
        'environment_id' => $environment->id,
        'name' => 'GPI Class 01',
        'schedule' => 'Saturday · 09:00–11:00',
        'status' => 'active',
    ]);
    Student::create(['class_id' => $class->id, 'full_name' => 'Private Student', 'parent_name' => 'Private Parent', 'status' => 'active']);

    $this->get(route('classes'))
        ->assertOk()
        ->assertSee('GPI Class 01')
        ->assertSee('Minta izin membaca')
        ->assertSee('Minta izin mengunduh')
        ->assertDontSee(route('classes.module'), false)
        ->assertDontSee(route('classes.module.download'), false)
        ->assertDontSee('Private Student')
        ->assertDontSee('Private Parent');

    $this->get(route('classes.module'))->assertForbidden();
    $this->get(route('classes.module.download'))->assertForbidden();
});
