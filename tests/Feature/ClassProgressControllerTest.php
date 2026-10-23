<?php

use App\Models\ClassCurriculumProgress;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\CurriculumLesson;
use App\Models\Environment;
use App\Models\Grade;
use App\Models\Region;
use App\Models\Teacher;
use App\Models\TeacherReflection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an admin sees regional lesson totals and reflections without the lesson checklist', function () {
    $region = Region::create(['name' => 'West Java', 'code' => 'JB', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Cluster A', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Community A', 'is_active' => true]);
    $class = Classes::create(['environment_id' => $environment->id, 'name' => 'Class A', 'status' => 'active']);
    $teacher = Teacher::factory()->create(['full_name' => 'Teacher A']);
    $class->teachers()->attach($teacher);
    $grade = Grade::query()->firstOrCreate(['name' => 'Program 24 Lesson']);
    $lesson = CurriculumLesson::query()->firstOrCreate(
        ['grade_id' => $grade->id, 'lesson_number' => 1],
        ['title' => 'First lesson'],
    );
    ClassCurriculumProgress::create([
        'class_id' => $class->id,
        'curriculum_lesson_id' => $lesson->id,
        'status' => 'selesai',
        'completed_at' => now()->toDateString(),
    ]);
    TeacherReflection::create([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'reflection_date' => now()->toDateString(),
        'what_went_well' => 'Children joined in.',
    ]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.class-progress', ['region_id' => $region->id]))
        ->assertOk()
        ->assertSee('Filter wilayah')
        ->assertSee('1 / 24')
        ->assertSee('Teacher A')
        ->assertSee('Refleksi Guru')
        ->assertSee('Children joined in.')
        ->assertDontSee('First lesson')
        ->assertDontSee('Simpan checklist lesson');
});

test('an admin sees only classes from the selected region', function () {
    $westJava = Region::create(['name' => 'West Java', 'is_active' => true]);
    $otherRegion = Region::create(['name' => 'Other Region', 'is_active' => true]);
    $westCluster = Cluster::create(['region_id' => $westJava->id, 'name' => 'West Cluster', 'is_active' => true]);
    $otherCluster = Cluster::create(['region_id' => $otherRegion->id, 'name' => 'Other Cluster', 'is_active' => true]);
    $westEnvironment = Environment::create(['cluster_id' => $westCluster->id, 'name' => 'West Community', 'is_active' => true]);
    $otherEnvironment = Environment::create(['cluster_id' => $otherCluster->id, 'name' => 'Other Community', 'is_active' => true]);
    Classes::create(['environment_id' => $westEnvironment->id, 'name' => 'West Class', 'status' => 'active']);
    Classes::create(['environment_id' => $otherEnvironment->id, 'name' => 'Other Class', 'status' => 'active']);
    Grade::query()->firstOrCreate(['name' => 'Program 24 Lesson']);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.class-progress', ['region_id' => $westJava->id]))
        ->assertOk()
        ->assertSee('West Class')
        ->assertDontSee('Other Class');
});
