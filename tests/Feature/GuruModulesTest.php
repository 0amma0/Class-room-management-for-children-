<?php

use App\Mail\LessonPlanReminder;
use App\Models\Activity;
use App\Models\ActivityPhoto;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\CurriculumLesson;
use App\Models\Environment;
use App\Models\LessonPlan;
use App\Models\ParentVisit;
use App\Models\Region;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherReflection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function assignedGuruFixture(): array
{
    $region = Region::create(['name' => 'West Java', 'code' => 'TEST-WJ', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Cluster A', 'code' => 'TEST-CA', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Community A', 'code' => 'TEST-EA', 'is_active' => true]);
    $teacher = Teacher::create(['full_name' => 'Assigned Teacher', 'status' => 'active']);
    $guru = User::factory()->guru()->create([
        'teacher_id' => $teacher->id,
        'must_change_password' => false,
    ]);
    $class = Classes::create([
        'environment_id' => $environment->id,
        'name' => 'Class A',
        'code' => 'TEST-CLASS-A',
        'status' => 'active',
    ]);
    $class->teachers()->attach($teacher);
    $student = Student::create([
        'class_id' => $class->id,
        'full_name' => 'Participant A',
        'status' => 'active',
    ]);

    return [$guru, $teacher, $class, $student];
}

test('a teacher can record attendance and development for an assigned participant', function () {
    [$guru, , $class, $student] = assignedGuruFixture();

    $this->actingAs($guru)->post(route('guru.attendances.store'), [
        'class_id' => $class->id,
        'attendance_date' => now()->toDateString(),
        'session_number' => 1,
        'attendance' => [$student->id => ['status' => 'absent', 'note' => 'Sick']],
    ])->assertRedirect(route('guru.attendances', [
        'class_id' => $class->id,
        'attendance_date' => now()->toDateString(),
        'session_number' => 1,
    ]));

    $this->assertDatabaseHas('attendances', [
        'student_id' => $student->id,
        'class_id' => $class->id,
        'status' => 'absent',
        'session_number' => 1,
        'note' => 'Sick',
        'recorded_by' => $guru->id,
    ]);

    $this->actingAs($guru)->post(route('guru.developments.store'), [
        'class_id' => $class->id,
        'student_id' => $student->id,
        'development_date' => now()->toDateString(),
        'assessment' => 'Baik',
        'note' => 'Participated in the session.',
    ])->assertRedirect(route('guru.developments'));

    $this->assertDatabaseHas('developments', [
        'student_id' => $student->id,
        'assessment' => 'Baik',
        'note' => 'Participated in the session.',
    ]);
});

test('a teacher cannot record attendance for an unassigned class', function () {
    [$guru, , $class, $student] = assignedGuruFixture();
    $otherClass = Classes::create([
        'environment_id' => $class->environment_id,
        'name' => 'Class B',
        'code' => 'TEST-CLASS-B',
        'status' => 'active',
    ]);

    $this->actingAs($guru)->post(route('guru.attendances.store'), [
        'class_id' => $otherClass->id,
        'attendance_date' => now()->toDateString(),
        'session_number' => 1,
        'attendance' => [$student->id => ['status' => 'present']],
    ])->assertForbidden();

    $this->assertDatabaseCount('attendances', 0);
});

test('a teacher can save handbook-based lesson plans and reflections', function () {
    [$guru, $teacher, $class] = assignedGuruFixture();

    $this->actingAs($guru)->post(route('guru.lesson-plans.store'), [
        'plan_type' => 'class',
        'class_id' => $class->id,
        'execution_date' => now()->toDateString(),
        'reminder_days' => 1,
        'level' => 'Level 1',
        'theme' => 'Friendship',
        'objectives' => 'Session objective',
        'program' => ['Doa Pembuka' => 'Opening prayer'],
        'program_times' => ['Doa Pembuka' => '09:00'],
        'materials' => [['tool' => 'Paper', 'purpose' => 'Coloring']],
        'catch_up_notes' => 'Review with absent participants.',
    ])->assertRedirect(route('guru.lesson-plans'));

    $plan = LessonPlan::query()->firstOrFail();
    expect($plan->program['Doa Pembuka']['activity'])->toBe('Opening prayer')
        ->and($plan->program['Doa Pembuka']['time'])->toBe('09:00')
        ->and($plan->materials[0]['purpose'])->toBe('Coloring')
        ->and($plan->student_count)->toBe(1);

    $this->actingAs($guru)->post(route('guru.teacher-module.reflections.store'), [
        'class_id' => $class->id,
        'lesson_plan_id' => $plan->id,
        'reflection_date' => now()->toDateString(),
        'objectives_met' => 'Met',
        'plan_alignment' => 'Followed the plan',
        'what_went_well' => 'Children participated',
        'challenges' => 'None',
        'improvement' => 'Continue',
    ])->assertRedirect(route('guru.teacher-module'));

    $reflection = TeacherReflection::query()->firstOrFail();
    $this->assertDatabaseHas('teacher_reflections', [
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'lesson_plan_id' => $plan->id,
        'plan_alignment' => 'Followed the plan',
    ]);

    $this->actingAs($guru)->put(route('guru.teacher-module.reflections.update', $reflection), [
        'class_id' => $class->id,
        'lesson_plan_id' => $plan->id,
        'reflection_date' => now()->toDateString(),
        'objectives_met' => 'Revised',
        'plan_alignment' => 'Updated alignment',
    ])->assertRedirect(route('guru.teacher-module'));
    $this->assertDatabaseHas('teacher_reflections', [
        'id' => $reflection->id,
        'objectives_met' => 'Revised',
    ]);

    $this->actingAs($guru)->delete(route('guru.teacher-module.reflections.destroy', $reflection))
        ->assertRedirect(route('guru.teacher-module'));
    $this->assertDatabaseMissing('teacher_reflections', ['id' => $reflection->id]);
});

test('a teacher can mark lessons for assigned classes but cannot access another class checklist', function () {
    [$guru, , $class] = assignedGuruFixture();
    $otherClass = Classes::create([
        'environment_id' => $class->environment_id,
        'name' => 'Unassigned Class',
        'code' => 'TEST-CLASS-OTHER',
        'status' => 'active',
    ]);
    $lesson = CurriculumLesson::query()
        ->whereHas('grade', fn ($query) => $query->where('name', 'Program 24 Lesson'))
        ->orderBy('lesson_number')
        ->firstOrFail();

    $this->actingAs($guru)
        ->get(route('guru.class-progress', ['class_id' => $class->id]))
        ->assertOk()
        ->assertSee('Lesson 24');

    $this->actingAs($guru)->put(route('guru.class-progress.update'), [
        'class_id' => $class->id,
        'completed_lessons' => [$lesson->id],
        'completed_at' => now()->toDateString(),
    ])->assertRedirect(route('guru.class-progress', ['class_id' => $class->id]));

    $this->assertDatabaseHas('class_curriculum_progress', [
        'class_id' => $class->id,
        'curriculum_lesson_id' => $lesson->id,
        'status' => 'selesai',
    ]);

    $this->actingAs($guru)
        ->get(route('guru.class-progress', ['class_id' => $otherClass->id]))
        ->assertNotFound();

    $this->actingAs($guru)->put(route('guru.class-progress.update'), [
        'class_id' => $otherClass->id,
        'completed_lessons' => [$lesson->id],
    ])->assertNotFound();

    $this->assertDatabaseMissing('class_curriculum_progress', [
        'class_id' => $otherClass->id,
        'curriculum_lesson_id' => $lesson->id,
    ]);
});

test('a teacher can update and archive activities and manage family visits', function () {
    [$guru, , $class, $student] = assignedGuruFixture();

    $this->actingAs($guru)->post(route('guru.activities.store'), [
        'class_id' => $class->id,
        'title' => 'Session activity',
        'description' => 'Initial description',
        'activity_date' => now()->toDateString(),
    ])->assertRedirect(route('guru.activities'));
    $activity = Activity::query()->firstOrFail();

    $this->actingAs($guru)->put(route('guru.activities.update', $activity), [
        'class_id' => $class->id,
        'title' => 'Updated activity',
        'description' => 'Updated description',
        'activity_date' => now()->toDateString(),
    ])->assertRedirect(route('guru.activities'));
    $this->assertDatabaseHas('activities', ['id' => $activity->id, 'title' => 'Updated activity', 'status' => 'pending_review']);

    $this->actingAs($guru)->delete(route('guru.activities.destroy', $activity))
        ->assertRedirect(route('guru.activities'));
    $this->assertDatabaseHas('activities', ['id' => $activity->id, 'status' => 'archived']);

    $this->actingAs($guru)->post(route('guru.parent-visits.store'), [
        'class_id' => $class->id,
        'student_id' => $student->id,
        'parent_name' => 'Guardian',
        'visit_date' => now()->toDateString(),
        'purpose' => 'Progress discussion',
        'discussion_result' => 'Initial note',
        'follow_up' => 'Check next month',
    ])->assertRedirect(route('guru.parent-visits'));
    $visit = ParentVisit::query()->firstOrFail();

    $this->actingAs($guru)->put(route('guru.parent-visits.update', $visit), [
        'class_id' => $class->id,
        'student_id' => $student->id,
        'parent_name' => 'Guardian Updated',
        'visit_date' => now()->toDateString(),
        'purpose' => 'Progress discussion',
        'discussion_result' => 'Updated note',
        'follow_up' => 'Check next month',
    ])->assertRedirect(route('guru.parent-visits'));
    $this->assertDatabaseHas('parent_visits', ['id' => $visit->id, 'parent_name' => 'Guardian Updated']);

    $this->actingAs($guru)->delete(route('guru.parent-visits.destroy', $visit))
        ->assertRedirect(route('guru.parent-visits'));
    $this->assertDatabaseMissing('parent_visits', ['id' => $visit->id]);
});

test('teacher activity images stay private until an admin publishes the activity', function () {
    Storage::fake('public');
    Storage::fake('local');
    [$guru, , $class] = assignedGuruFixture();
    $admin = User::factory()->admin()->create();
    $unassignedGuru = User::factory()->guru()->create(['must_change_password' => false]);

    $this->actingAs($guru)->post(route('guru.activities.store'), [
        'class_id' => $class->id,
        'title' => 'Activity with image',
        'description' => 'A reviewed class activity.',
        'activity_date' => now()->toDateString(),
        'image' => UploadedFile::fake()->image('class-activity.jpg'),
    ])->assertRedirect(route('guru.activities'));

    $activity = Activity::query()->where('title', 'Activity with image')->firstOrFail();
    $photo = ActivityPhoto::query()->where('activity_id', $activity->id)->firstOrFail();
    expect($activity->status)->toBe('pending_review')
        ->and($photo->is_approved)->toBeFalse();
    Storage::disk('local')->assertExists($photo->file_url);
    Storage::disk('public')->assertMissing($photo->file_url);
    $imageUrl = route('activity-photos.show', $photo);
    Auth::logout();
    $this->get($imageUrl)->assertNotFound();
    $this->actingAs($unassignedGuru)->get($imageUrl)->assertNotFound();
    $this->actingAs($guru)->get($imageUrl)->assertOk();
    $this->actingAs($admin)->get($imageUrl)->assertOk();
    $this->get(route('home'))->assertDontSee($imageUrl, false);

    $this->actingAs($guru)->put(route('guru.activities.update', $activity), [
        'class_id' => $class->id,
        'title' => 'Updated activity with image',
        'description' => 'Updated and resubmitted.',
        'activity_date' => now()->toDateString(),
        'image' => UploadedFile::fake()->image('updated-class-activity.jpg'),
    ])->assertRedirect(route('guru.activities'));

    $activity->refresh();
    $photo = ActivityPhoto::query()->where('activity_id', $activity->id)->latest('id')->firstOrFail();
    expect($activity->title)->toBe('Updated activity with image')
        ->and($activity->status)->toBe('pending_review')
        ->and($photo->is_approved)->toBeFalse();
    Storage::disk('local')->assertExists($photo->file_url);
    Storage::disk('public')->assertMissing($photo->file_url);
    $imageUrl = route('activity-photos.show', $photo);
    $this->get(route('home'))->assertDontSee($imageUrl, false);

    $this->actingAs($admin)->put(route('admin.activities.update', $activity), [
        'status' => 'published',
    ])->assertRedirect(route('admin.activities'));

    expect($photo->fresh()->is_approved)->toBeTrue();
    Auth::logout();
    $this->get($imageUrl)->assertOk();
    $this->get(route('home'))->assertSee('Updated activity with image')->assertSee($imageUrl, false);
    $this->get(route('home'))->assertSee($imageUrl, false);

    $activity->update(['status' => 'archived']);
    $this->get($imageUrl)->assertNotFound();
});

test('teacher activity uploads reject non-image files without partial writes', function () {
    Storage::fake('public');
    Storage::fake('local');
    [$guru, , $class] = assignedGuruFixture();

    $this->actingAs($guru)->post(route('guru.activities.store'), [
        'class_id' => $class->id,
        'title' => 'Activity with invalid upload',
        'image' => UploadedFile::fake()->create('notes.txt', 4, 'text/plain'),
    ])->assertSessionHasErrors('image');

    $this->assertDatabaseMissing('activities', ['title' => 'Activity with invalid upload']);
    $this->assertDatabaseCount('activity_photos', 0);
    expect(Storage::disk('local')->allFiles('activity-photos'))->toBeEmpty();
});

test('legacy public activity photos can be moved into private storage', function () {
    Storage::fake('public');
    Storage::fake('local');
    [$guru, , $class] = assignedGuruFixture();
    $activity = Activity::query()->create([
        'title' => 'Legacy activity',
        'scope_type' => 'class',
        'class_id' => $class->id,
        'status' => 'pending_review',
        'created_by' => $guru->id,
    ]);
    $path = 'activity-photos/legacy-photo.jpg';
    Storage::disk('public')->putFileAs(
        'activity-photos',
        UploadedFile::fake()->image('legacy-photo.jpg'),
        'legacy-photo.jpg',
    );
    ActivityPhoto::query()->create([
        'activity_id' => $activity->id,
        'file_url' => $path,
        'is_approved' => false,
    ]);

    $this->artisan('app:secure-activity-photos')->assertSuccessful();

    Storage::disk('local')->assertExists($path);
    Storage::disk('public')->assertMissing($path);
});

test('teacher module routes render and the original PDF is served privately', function () {
    [$guru] = assignedGuruFixture();

    $this->actingAs($guru)->get(route('guru.teacher-module'))->assertOk()->assertSee('Teacher handbook');
    $this->actingAs($guru)->get(route('guru.teacher-module.pdf'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

test('a teacher can save a non-class activity plan and add it to Google Calendar', function () {
    [$guru] = assignedGuruFixture();
    $eventDate = now()->addDays(3)->toDateString();

    $this->actingAs($guru)->post(route('guru.lesson-plans.store'), [
        'plan_type' => 'other',
        'execution_date' => $eventDate,
        'theme' => 'Rapat relawan',
        'objectives' => 'Menyusun jadwal kegiatan.',
        'reminder_days' => 2,
    ])->assertRedirect(route('guru.lesson-plans'));

    $plan = LessonPlan::query()->firstOrFail();
    expect($plan->plan_type)->toBe('other')
        ->and($plan->class_id)->toBeNull()
        ->and($plan->reminder_days)->toBe(2);

    $this->actingAs($guru)
        ->get(route('guru.lesson-plans'))
        ->assertOk()
        ->assertSee('Rapat relawan')
        ->assertSee('https://calendar.google.com/calendar/render?action=TEMPLATE', false);
});

test('the scheduled reminder emails the plan owner on the selected day', function () {
    Mail::fake();
    [$guru, , $class] = assignedGuruFixture();
    $plan = LessonPlan::query()->create([
        'class_id' => $class->id,
        'created_by' => $guru->id,
        'plan_type' => 'class',
        'execution_date' => now('Asia/Jakarta')->addDay()->toDateString(),
        'reminder_days' => 1,
        'theme' => 'Kelas persahabatan',
        'objectives' => 'Belajar bekerja sama.',
        'status' => 'draft',
    ]);

    $this->artisan('app:lesson-plan-reminders')->assertSuccessful();

    expect($plan->fresh()->reminder_sent_at)->not->toBeNull();
    Mail::assertSent(LessonPlanReminder::class, fn ($mail): bool => $mail->lessonPlan->is($plan));
});
