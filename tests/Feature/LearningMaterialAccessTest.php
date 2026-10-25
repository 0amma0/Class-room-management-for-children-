<?php

use App\Models\AppNotification;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\CurriculumLesson;
use App\Models\Environment;
use App\Models\Grade;
use App\Models\LearningMaterial;
use App\Models\PublicDownloadRequest;
use App\Models\Region;
use App\Models\Teacher;
use App\Models\TeacherReflection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function learningAccessClass(string $name): Classes
{
    $region = Region::query()->create(['name' => 'Region '.$name]);
    $cluster = Cluster::query()->create(['region_id' => $region->id, 'name' => 'Cluster '.$name]);
    $environment = Environment::query()->create(['cluster_id' => $cluster->id, 'name' => 'Environment '.$name]);

    return Classes::query()->create([
        'environment_id' => $environment->id,
        'name' => $name,
        'status' => 'active',
    ]);
}

test('public material requests stay locked until an admin approves them', function () {
    $admin = User::factory()->admin()->create();
    $material = LearningMaterial::query()->where('slug', '24-lesson')->firstOrFail();

    $response = $this->post(route('learning-materials.requests.store', $material), [
        'requester_name' => 'Public Reader',
        'requester_email' => 'reader@example.test',
    ]);

    $response->assertRedirect();
    $token = Str::after((string) $response->headers->get('Location'), '/permintaan-materi/');
    expect(Str::length($token))->toBe(64);

    $downloadRequest = PublicDownloadRequest::query()->firstOrFail();
    expect($downloadRequest->access_token_hash)->toBe(hash('sha256', $token));
    $this->assertDatabaseHas('app_notifications', [
        'user_id' => $admin->id,
        'public_download_request_id' => $downloadRequest->id,
        'type' => 'download_request',
    ]);
    $this->actingAs($admin)->get(route('admin.materials'))
        ->assertOk()
        ->assertSee('Permintaan izin materi')
        ->assertSee('Public Reader');
    $this->get(route('learning-materials.requests.show', $token))->assertOk()->assertSee('menunggu persetujuan');
    $this->get(route('learning-materials.requests.download', $token))->assertForbidden();

    $this->actingAs($admin)
        ->post(route('admin.materials.requests.decide', $downloadRequest), ['decision' => 'approved'])
        ->assertRedirect();
    $this->assertDatabaseHas('public_download_requests', [
        'id' => $downloadRequest->id,
        'status' => 'approved',
        'reviewed_by' => $admin->id,
    ]);
    $this->get(route('learning-materials.requests.show', $token))->assertOk()->assertSee('Unduh materi');
    $this->get(route('learning-materials.requests.download', $token))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

test('the class page requires approval for reading or downloading the Book 3 module', function () {
    $admin = User::factory()->admin()->create();
    $module = LearningMaterial::query()->where('slug', 'modul-buku-3')->firstOrFail();

    $this->get(route('classes'))->assertOk()->assertSee('Minta izin membaca');
    $this->get(route('classes.module'))->assertForbidden();
    $this->get(route('classes.module.download'))->assertForbidden();

    $response = $this->post(route('learning-materials.requests.store', $module), [
        'requester_name' => 'Module Reader',
        'requester_email' => 'module@example.test',
        'requested_action' => 'read',
    ]);
    $token = Str::after((string) $response->headers->get('Location'), '/permintaan-materi/');
    $downloadRequest = PublicDownloadRequest::query()->firstOrFail();

    expect($downloadRequest->requested_action)->toBe('read');
    $this->get(route('learning-materials.requests.read', $token))->assertForbidden();
    $this->get(route('learning-materials.requests.download', $token))->assertForbidden();

    $this->actingAs($admin)->post(route('admin.materials.requests.decide', $downloadRequest), [
        'decision' => 'approved',
    ])->assertRedirect();

    $this->get(route('learning-materials.requests.show', $token))
        ->assertOk()
        ->assertSee('Baca materi');
    $this->get(route('learning-materials.requests.read', $token))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'inline; filename="modul-buku-3.pdf"');
    $this->get(route('learning-materials.requests.download', $token))->assertForbidden();

    $downloadResponse = $this->post(route('learning-materials.requests.store', $module), [
        'requester_name' => 'Module Downloader',
        'requester_email' => 'download@example.test',
        'requested_action' => 'download',
    ]);
    $downloadToken = Str::after((string) $downloadResponse->headers->get('Location'), '/permintaan-materi/');
    $downloadRequest = PublicDownloadRequest::query()->where('requester_email', 'download@example.test')->firstOrFail();
    $this->actingAs($admin)->post(route('admin.materials.requests.decide', $downloadRequest), [
        'decision' => 'approved',
    ])->assertRedirect();
    $this->get(route('learning-materials.requests.download', $downloadToken))->assertDownload('modul-buku-3.pdf');

    $guru = User::factory()->guru()->create(['must_change_password' => false]);
    $this->actingAs($guru)->get(route('learning-materials.staff-download', $module))->assertForbidden();
    $this->actingAs($guru)->get(route('guru.materials'))->assertDontSee('Modul Buku 3');
});

test('approved song requests redirect to the configured Google Drive folder', function () {
    $admin = User::factory()->admin()->create();
    $material = LearningMaterial::query()->where('slug', 'draft-lagu')->firstOrFail();
    $response = $this->post(route('learning-materials.requests.store', $material), [
        'requester_name' => 'Public Singer',
        'requester_email' => 'singer@example.test',
    ]);
    $token = Str::after((string) $response->headers->get('Location'), '/permintaan-materi/');
    $downloadRequest = PublicDownloadRequest::query()->firstOrFail();

    $this->actingAs($admin)->post(route('admin.materials.requests.decide', $downloadRequest), [
        'decision' => 'approved',
    ]);

    $this->get(route('learning-materials.requests.download', $token))
        ->assertRedirect('https://drive.google.com/drive/folders/169LSwexNqc9muvauKcBWUZMVEtVRsqcg');
});

test('public, admin, and teacher material pages show their permitted actions', function () {
    $this->get(route('learning-materials.index'))
        ->assertOk()
        ->assertSee('Dokumentasi 24 Lesson')
        ->assertSee('Draft Lagu Anak')
        ->assertSee('Ajukan izin unduh');

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get(route('admin.materials'))
        ->assertOk()
        ->assertSee('Tambah materi lagu, gambar, atau dokumen')
        ->assertSee('Permintaan izin materi');

    $guru = User::factory()->guru()->create();
    $this->actingAs($guru)->get(route('guru.materials'))
        ->assertOk()
        ->assertSee('Draft Lagu & Materi Pelajaran', false)
        ->assertDontSee('Permintaan izin materi')
        ->assertDontSee('Modul Buku 3');
});

test('public material requests remain unavailable when rejected and other roles cannot decide', function () {
    $guru = User::factory()->guru()->create();
    $material = LearningMaterial::query()->where('slug', 'draft-lagu')->firstOrFail();

    $response = $this->post(route('learning-materials.requests.store', $material), [
        'requester_name' => 'Public Reader',
        'requester_email' => 'reader@example.test',
    ]);
    $token = Str::after((string) $response->headers->get('Location'), '/permintaan-materi/');
    $downloadRequest = PublicDownloadRequest::query()->firstOrFail();

    $this->actingAs($guru)
        ->post(route('admin.materials.requests.decide', $downloadRequest), ['decision' => 'approved'])
        ->assertForbidden();

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->post(route('admin.materials.requests.decide', $downloadRequest), [
            'decision' => 'rejected',
            'review_note' => 'Perlu konfirmasi tambahan.',
        ])
        ->assertRedirect();

    $this->get(route('learning-materials.requests.show', $token))
        ->assertOk()
        ->assertSee('tidak disetujui')
        ->assertSee('Perlu konfirmasi tambahan.');
    $this->get(route('learning-materials.requests.download', $token))->assertForbidden();
});

test('an administrator can delete a download request and its related notifications only', function () {
    $admin = User::factory()->admin()->create();
    $guru = User::factory()->guru()->create();
    $material = LearningMaterial::query()->where('slug', '24-lesson')->firstOrFail();
    $firstRequest = PublicDownloadRequest::factory()->create(['learning_material_id' => $material->id]);
    $secondRequest = PublicDownloadRequest::factory()->create(['learning_material_id' => $material->id]);
    $notification = AppNotification::query()->create([
        'user_id' => $admin->id,
        'type' => 'download_request',
        'public_download_request_id' => $firstRequest->id,
        'title' => 'Request notification',
        'message' => 'First request',
    ]);
    AppNotification::query()->create([
        'user_id' => $admin->id,
        'type' => 'download_request',
        'public_download_request_id' => $secondRequest->id,
        'title' => 'Other request notification',
        'message' => 'Second request',
    ]);

    $this->actingAs($admin)->get(route('admin.materials'))->assertOk()->assertSee('Delete');

    $this->actingAs($guru)
        ->delete(route('admin.materials.requests.destroy', $firstRequest))
        ->assertForbidden();
    $this->assertModelExists($firstRequest);

    $this->actingAs($admin)
        ->delete(route('admin.materials.requests.destroy', $firstRequest))
        ->assertRedirect(route('admin.materials'));

    $this->assertDatabaseMissing('public_download_requests', ['id' => $firstRequest->id]);
    $this->assertDatabaseMissing('app_notifications', ['id' => $notification->id]);
    $this->assertDatabaseHas('public_download_requests', ['id' => $secondRequest->id]);
    $this->assertDatabaseHas('app_notifications', ['public_download_request_id' => $secondRequest->id]);
});

test('users can delete only their own notifications', function () {
    $admin = User::factory()->admin()->create();
    $guru = User::factory()->guru()->create();
    $notification = AppNotification::query()->create([
        'user_id' => $admin->id,
        'type' => 'reflection',
        'title' => 'Reflection notification',
        'message' => 'New reflection',
    ]);

    $this->actingAs($guru)
        ->delete(route('notifications.destroy', $notification))
        ->assertNotFound();
    $this->assertModelExists($notification);

    $this->actingAs($admin)
        ->delete(route('notifications.destroy', $notification))
        ->assertRedirect(route('notifications.index'));
    $this->assertModelMissing($notification);
});

test('admins can upload private learning materials and teachers can download them', function () {
    Storage::fake('local');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.materials.store'), [
        'title' => 'Gambar Kegiatan',
        'type' => 'image',
        'description' => 'Ilustrasi untuk kelas.',
        'file' => UploadedFile::fake()->image('gambar.png'),
    ])->assertRedirect(route('admin.materials'));

    $material = LearningMaterial::query()->where('title', 'Gambar Kegiatan')->firstOrFail();
    $this->assertTrue(Storage::disk('local')->exists($material->file_path));

    $guru = User::factory()->guru()->create();
    $this->actingAs($guru)
        ->get(route('learning-materials.staff-download', $material))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('admins see lesson totals without a lesson todo list and can update progress', function () {
    $admin = User::factory()->admin()->create();
    $class = learningAccessClass('Kelas GPI');
    $programGrade = Grade::query()->where('name', 'Program 24 Lesson')->firstOrFail();
    $lessons = CurriculumLesson::query()->where('grade_id', $programGrade->id)->orderBy('lesson_number')->get();

    expect($lessons)->toHaveCount(24);
    $this->actingAs($admin)
        ->get(route('admin.class-progress', ['class_id' => $class->id]))
        ->assertOk()
        ->assertSee('Kelas')
        ->assertSee('0 / 24')
        ->assertDontSee('Lesson 24')
        ->assertDontSee('Simpan checklist lesson');

    $this->actingAs($admin)->put(route('admin.class-progress.update'), [
        'class_id' => $class->id,
        'completed_lessons' => [$lessons[0]->id, $lessons[1]->id],
        'completed_at' => now()->toDateString(),
    ])->assertRedirect(route('admin.class-progress', ['class_id' => $class->id]));

    $this->assertDatabaseHas('class_curriculum_progress', [
        'class_id' => $class->id,
        'curriculum_lesson_id' => $lessons[0]->id,
        'status' => 'selesai',
    ]);
    $this->assertDatabaseHas('class_curriculum_progress', [
        'class_id' => $class->id,
        'curriculum_lesson_id' => $lessons[2]->id,
        'status' => 'belum',
    ]);
});

test('new teacher reflections notify admins and teachers and are readable across assigned classes', function () {
    $author = Teacher::query()->create(['full_name' => 'Guru GPI', 'status' => 'active']);
    $authorUser = User::factory()->guru()->create([
        'teacher_id' => $author->id,
        'must_change_password' => false,
    ]);
    $otherTeacher = Teacher::query()->create(['full_name' => 'Guru Lain', 'status' => 'active']);
    $otherGuru = User::factory()->guru()->create([
        'teacher_id' => $otherTeacher->id,
        'must_change_password' => false,
    ]);
    $admin = User::factory()->admin()->create();
    $class = learningAccessClass('Kelas Refleksi');
    $class->teachers()->attach($author->id);

    $this->actingAs($authorUser)->post(route('guru.teacher-module.reflections.store'), [
        'class_id' => $class->id,
        'reflection_date' => now()->toDateString(),
        'objectives_met' => 'Tujuan sesi tercapai.',
        'what_went_well' => 'Anak-anak aktif.',
    ])->assertRedirect(route('guru.teacher-module'));

    $reflection = TeacherReflection::query()->firstOrFail();
    $this->assertDatabaseCount('app_notifications', 3);
    $this->assertDatabaseHas('app_notifications', [
        'user_id' => $otherGuru->id,
        'teacher_reflection_id' => $reflection->id,
        'type' => 'reflection',
    ]);
    $this->assertDatabaseHas('app_notifications', [
        'user_id' => $admin->id,
        'teacher_reflection_id' => $reflection->id,
        'type' => 'reflection',
    ]);

    $this->actingAs($otherGuru)->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Refleksi baru dari Guru GPI');
    $this->actingAs($otherGuru)->get(route('guru.teacher-module'))
        ->assertOk()
        ->assertSee('Guru GPI')
        ->assertSee('Tujuan sesi tercapai.');
    $notification = $otherGuru->appNotifications()->firstOrFail();
    $this->actingAs($otherGuru)
        ->get(route('notifications.open', $notification))
        ->assertRedirect(route('reflections.show', $reflection));
    $this->get(route('reflections.show', $reflection))
        ->assertOk()
        ->assertSee('Anak-anak aktif.');
    expect($notification->fresh()->read_at)->not->toBeNull();
});
