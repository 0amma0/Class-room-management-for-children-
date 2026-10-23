<?php

use App\Actions\DatabaseBackupManager;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

uses(RefreshDatabase::class);

test('database trigger rejects activities with a scope that has no matching owner', function () {
    expect(fn () => Activity::create([
        'title' => 'Invalid activity',
        'scope_type' => 'class',
        'status' => 'draft',
    ]))->toThrow(QueryException::class, 'Invalid activity scope');

    $this->assertDatabaseMissing('activities', ['title' => 'Invalid activity']);
});

test('database trigger permits an activity with its matching class scope', function () {
    $region = Region::create(['name' => 'Jawa Barat']);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Klaster Uji']);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Lingkungan Uji']);
    $class = Classes::create(['environment_id' => $environment->id, 'name' => 'Kelas Uji', 'code' => 'KLS-UJI']);

    $activity = Activity::create([
        'title' => 'Kegiatan valid',
        'scope_type' => 'class',
        'class_id' => $class->id,
        'status' => 'draft',
    ]);

    $this->assertModelExists($activity);
});

test('admin can view and download private backups but guru cannot', function () {
    Storage::fake('local');
    Storage::disk('local')->put('backups/snapshot.sql', 'SQL dump');
    $admin = User::factory()->admin()->create();
    $guru = User::factory()->guru()->create();

    $this->actingAs($admin)
        ->get('/admin/backups')
        ->assertOk()
        ->assertSee('snapshot.sql');

    $this->actingAs($admin)
        ->get('/admin/backups/snapshot.sql')
        ->assertDownload('snapshot.sql');

    $this->actingAs($guru)->get('/admin/backups')->assertForbidden();
});

test('restore command rejects paths outside the private backup directory', function () {
    $this->artisan('app:db-restore', ['file' => '../outside.sql'])
        ->assertFailed();
});

test('backup manager reports unsupported database drivers rather than returning an empty backup', function () {
    expect(fn () => app(DatabaseBackupManager::class)->backup())
        ->toThrow(RuntimeException::class, 'require a MySQL or MariaDB connection');
});

test('audit log can record a backup without storing a database password', function () {
    $admin = User::factory()->admin()->create();
    $entry = AuditLog::create([
        'user_id' => $admin->id,
        'action' => 'created',
        'entity_type' => 'database_backup',
        'meta' => ['filename' => 'snapshot.sql'],
    ]);

    $this->assertModelExists($entry);
    expect($entry->meta)->toBe(['filename' => 'snapshot.sql']);
});

test('admin can create and update master region data', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/regions', [
        'name' => 'Jawa Barat',
        'code' => 'JB',
        'description' => 'Wilayah operasional.',
        'is_active' => '1',
        'is_development' => '0',
    ]);

    $response->assertRedirect('/admin/regions');
    $this->assertDatabaseHas('regions', ['name' => 'Jawa Barat', 'code' => 'JB']);

    $region = Region::where('code', 'JB')->firstOrFail();
    $this->actingAs($admin)->put('/admin/regions/'.$region->id, [
        'name' => 'Jawa Barat Updated',
        'code' => 'JB',
        'is_active' => '1',
        'is_development' => '0',
    ])->assertRedirect('/admin/regions');

    $this->assertDatabaseHas('regions', ['id' => $region->id, 'name' => 'Jawa Barat Updated']);
});

test('admin can create, update and deactivate participants', function () {
    $admin = User::factory()->admin()->create();
    $region = Region::create(['name' => 'Jawa Barat']);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Klaster Peserta']);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Lingkungan Peserta']);
    $class = Classes::create([
        'environment_id' => $environment->id,
        'name' => 'Kelas Peserta',
        'code' => 'KLS-PESERTA',
        'status' => 'active',
    ]);

    $this->actingAs($admin)
        ->get('/admin/students')
        ->assertOk()
        ->assertSee('Add participant')
        ->assertSee('name="full_name"', false);

    $this->actingAs($admin)->post('/admin/students', [
        'class_id' => $class->id,
        'full_name' => 'Anak Uji',
        'nickname' => 'Uji',
        'gender' => 'P',
        'parent_name' => 'Wali Uji',
        'parent_phone' => '081234567890',
    ])->assertRedirect('/admin/students');

    $student = Student::where('full_name', 'Anak Uji')->firstOrFail();
    expect($student->student_code)->not->toBeEmpty();
    $this->assertDatabaseHas('audit_logs', [
        'entity_type' => 'student',
        'entity_id' => $student->id,
        'action' => 'created',
    ]);

    $this->actingAs($admin)->put('/admin/students/'.$student->id, [
        'class_id' => $class->id,
        'full_name' => 'Anak Uji Diperbarui',
        'nickname' => 'Uji Baru',
        'gender' => 'P',
        'parent_name' => 'Wali Uji',
        'parent_phone' => '081234567890',
        'status' => 'active',
    ])->assertRedirect('/admin/students');

    $this->assertDatabaseHas('students', [
        'id' => $student->id,
        'full_name' => 'Anak Uji Diperbarui',
        'nickname' => 'Uji Baru',
    ]);

    $this->actingAs($admin)
        ->delete('/admin/students/'.$student->id)
        ->assertRedirect('/admin/students');

    $this->assertDatabaseHas('students', [
        'id' => $student->id,
        'status' => 'inactive',
    ]);
    expect(AuditLog::where('entity_type', 'student')->where('entity_id', $student->id)->count())->toBe(3);
});

test('teacher account creation saves the profile, login, assignment and one primary teacher atomically', function () {
    $admin = User::factory()->admin()->create();
    $region = Region::create(['name' => 'Jawa Barat']);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Klaster A']);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Lingkungan A']);
    $class = Classes::create(['environment_id' => $environment->id, 'name' => 'Kelas A', 'code' => 'KLS-A']);

    $response = $this->actingAs($admin)->post('/admin/teachers', [
        'full_name' => 'Guru Uji',
        'nickname' => 'Kak Uji',
        'phone' => '081234567890',
        'email' => 'guru-uji@example.test',
        'username' => 'guru_uji',
        'classes' => [(string) $class->id],
        'primary_class_id' => (string) $class->id,
    ]);

    $response->assertRedirect('/admin/teachers');
    $this->assertDatabaseHas('teachers', ['full_name' => 'Guru Uji', 'status' => 'active']);
    $this->assertDatabaseHas('users', [
        'username' => 'guru_uji',
        'email' => 'guru-uji@example.test',
        'role' => 'guru',
        'must_change_password' => true,
    ]);
    $this->assertDatabaseHas('class_teachers', [
        'class_id' => $class->id,
        'is_primary' => true,
    ]);
    $response->assertSessionHas('temporary_credentials');

    $user = User::where('username', 'guru_uji')->firstOrFail();
    $credentials = session('temporary_credentials');
    expect(Hash::check($credentials['password'], $user->password))->toBeTrue();
    $this->assertDatabaseHas('audit_logs', ['entity_type' => 'teacher_account', 'action' => 'created']);

    $this->post('/logout')->assertRedirect('/');
    $this->post('/login', [
        'login' => $credentials['username'],
        'password' => $credentials['password'],
    ])->assertRedirect('/guru/dashboard');

    $this->assertAuthenticatedAs($user);
    $this->get('/admin/dashboard')->assertForbidden();
    $this->get('/guru/dashboard')->assertOk();
});

test('administrator can permanently delete a participant without history', function () {
    $admin = User::factory()->admin()->create();
    $region = Region::create(['name' => 'Delete Region']);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Delete Cluster']);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Delete Environment']);
    $class = Classes::create(['environment_id' => $environment->id, 'name' => 'Delete Class', 'code' => 'DELETE-CLASS']);
    $student = Student::create(['class_id' => $class->id, 'full_name' => 'Delete Participant']);

    $this->actingAs($admin)->delete(route('admin.students.destroy', ['studentId' => $student->id]))
        ->assertRedirect(route('admin.students'));

    $this->assertDatabaseMissing('students', ['id' => $student->id]);
    $this->assertDatabaseHas('audit_logs', [
        'entity_type' => 'student',
        'entity_id' => $student->id,
        'action' => 'deleted',
    ]);
});

test('administrator cannot permanently delete a participant with history', function () {
    $admin = User::factory()->admin()->create();
    $region = Region::create(['name' => 'History Region']);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'History Cluster']);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'History Environment']);
    $class = Classes::create(['environment_id' => $environment->id, 'name' => 'History Class', 'code' => 'HISTORY-CLASS']);
    $student = Student::create(['class_id' => $class->id, 'full_name' => 'History Participant']);
    DB::table('attendances')->insert([
        'student_id' => $student->id,
        'class_id' => $class->id,
        'attendance_date' => '2026-10-20',
        'status' => 'present',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)->delete(route('admin.students.destroy', ['studentId' => $student->id]))
        ->assertSessionHasErrors('delete');

    $this->assertDatabaseHas('students', ['id' => $student->id]);
    $this->assertDatabaseHas('attendances', ['student_id' => $student->id]);
});

test('a guru cannot permanently delete participants through admin routes', function () {
    $guru = User::factory()->guru()->create(['must_change_password' => false]);
    $region = Region::create(['name' => 'Access Region']);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Access Cluster']);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Access Environment']);
    $class = Classes::create(['environment_id' => $environment->id, 'name' => 'Access Class', 'code' => 'ACCESS-CLASS']);
    $student = Student::create(['class_id' => $class->id, 'full_name' => 'Access Participant']);

    $this->actingAs($guru)->delete(route('admin.students.destroy', ['studentId' => $student->id]))
        ->assertForbidden();

    $this->assertDatabaseHas('students', ['id' => $student->id]);
});

test('administrator can permanently delete a hierarchy record without children', function () {
    $admin = User::factory()->admin()->create();
    $region = Region::create(['name' => 'Empty Region']);

    $this->actingAs($admin)->delete(route('admin.master-data.destroy', [
        'resource' => 'regions',
        'id' => $region->id,
    ]))->assertRedirect(route('admin.regions'));

    $this->assertDatabaseMissing('regions', ['id' => $region->id]);
    $this->assertDatabaseHas('audit_logs', [
        'entity_type' => 'regions',
        'entity_id' => $region->id,
        'action' => 'deleted',
    ]);
});

test('administrator cannot permanently delete hierarchy records with children', function () {
    $admin = User::factory()->admin()->create();
    $region = Region::create(['name' => 'Protected Region']);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Protected Cluster']);

    $this->actingAs($admin)->delete(route('admin.master-data.destroy', [
        'resource' => 'regions',
        'id' => $region->id,
    ]))->assertSessionHasErrors('delete');

    $this->assertDatabaseHas('regions', ['id' => $region->id]);
    $this->assertDatabaseHas('clusters', ['id' => $cluster->id]);
});

test('administrator can permanently delete an unassigned teacher account', function () {
    $admin = User::factory()->admin()->create();
    $teacher = Teacher::factory()->create(['full_name' => 'Unassigned Teacher']);
    $teacherUser = User::factory()->guru()->create(['teacher_id' => $teacher->id]);

    $this->actingAs($admin)->delete(route('admin.teachers.destroy', ['teacherId' => $teacher->id]))
        ->assertRedirect(route('admin.teachers'));

    $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
    $this->assertDatabaseMissing('users', ['id' => $teacherUser->id]);
});

test('administrator cannot permanently delete a teacher who still has class assignments', function () {
    $admin = User::factory()->admin()->create();
    $teacher = Teacher::factory()->create(['full_name' => 'Assigned Teacher']);
    User::factory()->guru()->create(['teacher_id' => $teacher->id]);
    $region = Region::create(['name' => 'Teacher Delete Region']);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Teacher Delete Cluster']);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Teacher Delete Environment']);
    $class = Classes::create(['environment_id' => $environment->id, 'name' => 'Teacher Delete Class', 'code' => 'TEACHER-DELETE-CLASS']);
    $class->teachers()->attach($teacher->id);

    $this->actingAs($admin)->delete(route('admin.teachers.destroy', ['teacherId' => $teacher->id]))
        ->assertSessionHasErrors('delete');

    $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
    $this->assertDatabaseHas('class_teachers', ['teacher_id' => $teacher->id, 'class_id' => $class->id]);
});

test('failed deactivation of a sole primary teacher rolls back the account state', function () {
    $admin = User::factory()->admin()->create();
    $teacher = Teacher::factory()->create();
    $guru = User::factory()->guru()->create([
        'teacher_id' => $teacher->id,
        'status' => 'active',
    ]);
    $region = Region::create(['name' => 'Jawa Barat']);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Klaster A']);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Lingkungan A']);
    $class = Classes::create(['environment_id' => $environment->id, 'name' => 'Kelas A', 'code' => 'KLS-A']);
    $class->teachers()->attach($teacher->id, ['is_primary' => true]);

    $this->actingAs($admin)
        ->delete('/admin/teachers/'.$teacher->id)
        ->assertSessionHasErrors('account');

    $this->assertDatabaseHas('teachers', ['id' => $teacher->id, 'status' => 'active']);
    $this->assertDatabaseHas('users', ['id' => $guru->id, 'status' => 'active']);
    $this->assertDatabaseHas('class_teachers', [
        'class_id' => $class->id,
        'teacher_id' => $teacher->id,
        'is_primary' => true,
    ]);
});
