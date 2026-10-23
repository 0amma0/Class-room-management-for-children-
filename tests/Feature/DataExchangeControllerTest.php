<?php

use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Support\XlsxWorkbook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('an administrator can download an Excel report and import a participant workbook', function () {
    $admin = User::factory()->admin()->create();
    $workbook = app(XlsxWorkbook::class);
    $path = $workbook->create([
        ['name' => 'Data peserta', 'rows' => [
            ['region_code', 'region_name', 'cluster_code', 'cluster_name', 'environment_code', 'environment_name', 'class_code', 'class_name', 'student_code', 'full_name', 'nickname', 'gender', 'birth_date', 'address', 'parent_name', 'parent_phone'],
            ['WJ-IMPORT', 'West Java', 'C-IMPORT', 'Cluster Import', 'E-IMPORT', 'Community Import', 'CLASS-IMPORT', 'Class Import', 'STU-IMPORT-01', 'Imported Participant', '', 'P', '2020-01-15', 'Address', 'Guardian', '081234'],
        ]],
    ]);

    try {
        $uploadedFile = new UploadedFile(
            $path,
            'participants.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            UPLOAD_ERR_OK,
            true,
        );

        $report = $this->actingAs($admin)->get(route('admin.students.export'));
        $report->assertOk();
        expect($report->headers->get('content-type'))->toContain('spreadsheetml.sheet');

        $this->actingAs($admin)->post(route('admin.students.import'), ['file' => $uploadedFile])
            ->assertRedirect(route('admin.students'));

        $this->assertDatabaseHas('students', [
            'student_code' => 'STU-IMPORT-01',
            'full_name' => 'Imported Participant',
            'gender' => 'P',
        ]);
        expect(Region::query()->where('code', 'WJ-IMPORT')->exists())->toBeTrue()
            ->and(Cluster::query()->where('code', 'C-IMPORT')->exists())->toBeTrue()
            ->and(Environment::query()->where('code', 'E-IMPORT')->exists())->toBeTrue()
            ->and(Classes::query()->where('code', 'CLASS-IMPORT')->exists())->toBeTrue();
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

test('an Excel import with duplicate participant codes is rejected without partial writes', function () {
    $admin = User::factory()->admin()->create();
    $workbook = app(XlsxWorkbook::class);
    $headers = ['region_code', 'region_name', 'cluster_code', 'cluster_name', 'environment_code', 'environment_name', 'class_code', 'class_name', 'student_code', 'full_name', 'nickname', 'gender', 'birth_date', 'address', 'parent_name', 'parent_phone'];
    $path = $workbook->create([
        ['name' => 'Data peserta', 'rows' => [
            $headers,
            ['WJ-DUP', 'West Java', 'C-DUP', 'Cluster', 'E-DUP', 'Community', 'CLASS-DUP', 'Class', 'STU-DUP', 'First', '', '', '', '', '', ''],
            ['WJ-DUP', 'West Java', 'C-DUP', 'Cluster', 'E-DUP', 'Community', 'CLASS-DUP', 'Class', 'STU-DUP', 'Second', '', '', '', '', '', ''],
        ]],
    ]);

    try {
        $file = new UploadedFile($path, 'duplicates.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', UPLOAD_ERR_OK, true);
        $response = $this->actingAs($admin)->from(route('admin.students'))->post(route('admin.students.import'), ['file' => $file]);

        $response->assertRedirect(route('admin.students'))->assertSessionHasErrors('file');
        $this->assertDatabaseCount('students', 0);
        $this->assertDatabaseCount('regions', 0);
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

test('an administrator export applies hierarchy filters and omits credentials', function () {
    $admin = User::factory()->admin()->create(['name' => 'Export Admin']);
    $includedRegion = Region::create(['name' => 'Wilayah Terpilih', 'code' => 'R-SELECT', 'is_active' => true]);
    $excludedRegion = Region::create(['name' => 'Wilayah Lain', 'code' => 'R-OTHER', 'is_active' => true]);
    $includedCluster = Cluster::create(['region_id' => $includedRegion->id, 'name' => 'Klaster Terpilih', 'is_active' => true]);
    $excludedCluster = Cluster::create(['region_id' => $excludedRegion->id, 'name' => 'Klaster Lain', 'is_active' => true]);
    $includedEnvironment = Environment::create(['cluster_id' => $includedCluster->id, 'name' => 'Lingkungan Terpilih', 'is_active' => true]);
    $excludedEnvironment = Environment::create(['cluster_id' => $excludedCluster->id, 'name' => 'Lingkungan Lain', 'is_active' => true]);
    $includedClass = Classes::create(['environment_id' => $includedEnvironment->id, 'name' => 'Kelas Ekspor', 'status' => 'active']);
    $excludedClass = Classes::create(['environment_id' => $excludedEnvironment->id, 'name' => 'Kelas Jangan Ekspor', 'status' => 'active']);
    Student::create(['class_id' => $includedClass->id, 'student_code' => 'STU-IN', 'full_name' => 'Peserta Terpilih', 'status' => 'active']);
    Student::create(['class_id' => $excludedClass->id, 'student_code' => 'STU-OUT', 'full_name' => 'Peserta Lain', 'status' => 'active']);

    $response = $this->actingAs($admin)->get(route('admin.backups.export', [
        'data_type' => 'all',
        'region_id' => $includedRegion->id,
    ]));
    $response->assertOk();
    $path = $response->baseResponse->getFile()->getPathname();

    try {
        $rows = app(XlsxWorkbook::class)->readFirstSheet($path);
        $values = collect($rows)->pluck('values')->flatten()->all();
        expect($values)->toContain('Kelas Ekspor')
            ->and($values)->not->toContain('Kelas Jangan Ekspor');
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }

    $adminExport = $this->get(route('admin.backups.export', ['data_type' => 'admins']));
    $adminExport->assertOk();
    $adminPath = $adminExport->baseResponse->getFile()->getPathname();

    try {
        $values = collect(app(XlsxWorkbook::class)->readFirstSheet($adminPath))
            ->pluck('values')
            ->flatten()
            ->all();
        expect($values)->toContain('Export Admin')
            ->and($values)->not->toContain($admin->password);
    } finally {
        if (is_file($adminPath)) {
            unlink($adminPath);
        }
    }
});

test('a teacher export contains only participants from assigned classes and rejects other classes', function () {
    $region = Region::create(['name' => 'Wilayah', 'code' => 'R-GURU', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Klaster', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Lingkungan', 'is_active' => true]);
    $teacher = Teacher::create(['full_name' => 'Guru Ekspor', 'status' => 'active']);
    $guru = User::factory()->guru()->create(['teacher_id' => $teacher->id]);
    $assignedClass = Classes::create(['environment_id' => $environment->id, 'name' => 'Kelas Guru', 'status' => 'active']);
    $unassignedClass = Classes::create(['environment_id' => $environment->id, 'name' => 'Kelas Bukan Guru', 'status' => 'active']);
    $assignedClass->teachers()->attach($teacher);
    Student::create(['class_id' => $assignedClass->id, 'student_code' => 'STU-GURU-IN', 'full_name' => 'Peserta Guru', 'status' => 'active']);
    Student::create(['class_id' => $unassignedClass->id, 'student_code' => 'STU-GURU-OUT', 'full_name' => 'Peserta Bukan Guru', 'status' => 'active']);

    $response = $this->actingAs($guru)->get(route('guru.students.export'));
    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $path = $response->baseResponse->getFile()->getPathname();

    try {
        $rows = app(XlsxWorkbook::class)->readFirstSheet($path);
        $values = collect($rows)->pluck('values')->flatten()->all();
        expect($values)->toContain('Peserta Guru')
            ->and($values)->not->toContain('Peserta Bukan Guru');
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }

    $this->get(route('guru.students.export', ['class_id' => $unassignedClass->id]))->assertNotFound();
});
