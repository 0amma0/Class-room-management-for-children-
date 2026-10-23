<?php

use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guru can access all /guru/* routes with status 200', function (string $uri) {
    $guru = User::factory()->guru()->create([
        'must_change_password' => false,
    ]);

    $response = $this->actingAs($guru)->get($uri);

    $response->assertStatus(200);
})->with([
    '/guru/dashboard',
    '/guru/classes',
    '/guru/students',
    '/guru/attendances',
    '/guru/developments',
    '/guru/activities',
    '/guru/lesson-plans',
    '/guru/parent-visits',
    '/guru/teacher-module',
]);

test('guests are redirected to login when accessing /guru/* routes', function (string $uri) {
    $response = $this->get($uri);

    $response->assertRedirect('/login');
})->with([
    '/guru/dashboard',
    '/guru/classes',
    '/guru/students',
    '/guru/attendances',
    '/guru/developments',
    '/guru/activities',
    '/guru/lesson-plans',
    '/guru/parent-visits',
    '/guru/teacher-module',
]);

test('teachers only see participants from classes assigned to their account', function () {
    $region = Region::create(['name' => 'West Java', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Cluster A', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Community A', 'is_active' => true]);

    $assignedTeacher = Teacher::create(['full_name' => 'Assigned Teacher', 'status' => 'active']);
    $otherTeacher = Teacher::create(['full_name' => 'Other Teacher', 'status' => 'active']);
    $guru = User::factory()->guru()->create([
        'teacher_id' => $assignedTeacher->id,
        'must_change_password' => false,
    ]);

    $assignedClass = Classes::create([
        'environment_id' => $environment->id,
        'name' => 'Assigned Class',
        'status' => 'active',
    ]);
    $otherClass = Classes::create([
        'environment_id' => $environment->id,
        'name' => 'Other Class',
        'status' => 'active',
    ]);
    $assignedClass->teachers()->attach($assignedTeacher);
    $otherClass->teachers()->attach($otherTeacher);

    Student::create([
        'class_id' => $assignedClass->id,
        'full_name' => 'Assigned Participant',
        'parent_name' => 'Assigned Guardian',
        'status' => 'active',
    ]);
    Student::create([
        'class_id' => $otherClass->id,
        'full_name' => 'Other Participant',
        'parent_name' => 'Other Guardian',
        'status' => 'active',
    ]);

    $response = $this->actingAs($guru)->get('/guru/students');

    $response->assertStatus(200);
    $response->assertSee('Assigned Participant');
    $response->assertSee('Assigned Guardian');
    $response->assertDontSee('Other Participant');
    $response->assertDontSee('Other Guardian');
});

test('guru can manage participants in an assigned class', function () {
    $region = Region::create(['name' => 'Central Java']);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Cluster CRUD']);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Community CRUD']);
    $teacher = Teacher::create(['full_name' => 'CRUD Teacher', 'status' => 'active']);
    $guru = User::factory()->guru()->create([
        'teacher_id' => $teacher->id,
        'must_change_password' => false,
    ]);
    $class = Classes::create([
        'environment_id' => $environment->id,
        'name' => 'Assigned CRUD Class',
        'status' => 'active',
    ]);
    $class->teachers()->attach($teacher);

    $this->actingAs($guru)->post('/guru/students', [
        'class_id' => $class->id,
        'full_name' => 'New Participant',
        'parent_name' => 'New Guardian',
    ])->assertRedirect('/guru/students');

    $student = Student::where('full_name', 'New Participant')->firstOrFail();
    $this->assertDatabaseHas('audit_logs', [
        'entity_type' => 'student',
        'entity_id' => $student->id,
        'action' => 'created',
    ]);

    $this->actingAs($guru)->put('/guru/students/'.$student->id, [
        'class_id' => $class->id,
        'full_name' => 'Updated Participant',
        'parent_name' => 'Updated Guardian',
        'status' => 'active',
    ])->assertRedirect('/guru/students');

    $this->assertDatabaseHas('students', [
        'id' => $student->id,
        'full_name' => 'Updated Participant',
        'parent_name' => 'Updated Guardian',
    ]);

    $this->actingAs($guru)
        ->delete('/guru/students/'.$student->id)
        ->assertRedirect('/guru/students');

    $this->assertDatabaseHas('students', ['id' => $student->id, 'status' => 'inactive']);
});

test('guru cannot add participants to or modify participants from an unassigned class', function () {
    $region = Region::create(['name' => 'East Java']);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Cluster Isolation']);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Community Isolation']);
    $assignedTeacher = Teacher::create(['full_name' => 'Assigned Isolation Teacher', 'status' => 'active']);
    $otherTeacher = Teacher::create(['full_name' => 'Other Isolation Teacher', 'status' => 'active']);
    $guru = User::factory()->guru()->create([
        'teacher_id' => $assignedTeacher->id,
        'must_change_password' => false,
    ]);
    $assignedClass = Classes::create([
        'environment_id' => $environment->id,
        'name' => 'Assigned Isolation Class',
        'status' => 'active',
    ]);
    $otherClass = Classes::create([
        'environment_id' => $environment->id,
        'name' => 'Other Isolation Class',
        'status' => 'active',
    ]);
    $assignedClass->teachers()->attach($assignedTeacher);
    $otherClass->teachers()->attach($otherTeacher);
    $otherStudent = Student::create([
        'class_id' => $otherClass->id,
        'full_name' => 'Private Other Participant',
        'status' => 'active',
    ]);

    $this->actingAs($guru)->post('/guru/students', [
        'class_id' => $otherClass->id,
        'full_name' => 'Unauthorized Participant',
    ])->assertForbidden();

    $this->actingAs($guru)->put('/guru/students/'.$otherStudent->id, [
        'class_id' => $assignedClass->id,
        'full_name' => 'Unauthorized Update',
    ])->assertNotFound();

    $this->assertDatabaseHas('students', [
        'id' => $otherStudent->id,
        'full_name' => 'Private Other Participant',
        'class_id' => $otherClass->id,
    ]);
    $this->assertDatabaseMissing('students', ['full_name' => 'Unauthorized Participant']);
});
