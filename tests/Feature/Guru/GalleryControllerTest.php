<?php

use App\Models\Classes;
use App\Models\ClassGalleryPhoto;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('a teacher can upload and view a private gallery photo for an assigned class', function () {
    Storage::fake('local');
    $region = Region::create(['name' => 'Wilayah', 'code' => 'WIL', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Klaster', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Lingkungan', 'is_active' => true]);
    $teacher = Teacher::create(['full_name' => 'Guru', 'status' => 'active']);
    $guru = User::factory()->guru()->create(['teacher_id' => $teacher->id]);
    $class = Classes::create(['environment_id' => $environment->id, 'name' => 'Kelas A', 'status' => 'active']);
    $class->teachers()->attach($teacher);

    $this->actingAs($guru)->post(route('guru.gallery.store'), [
        'class_id' => $class->id,
        'photo_date' => now()->toDateString(),
        'session_label' => 'Sesi cerita',
        'caption' => 'Karya bersama',
        'image' => UploadedFile::fake()->image('karya.png'),
    ])->assertRedirect(route('guru.gallery'));

    $photo = ClassGalleryPhoto::query()->firstOrFail();
    expect($photo->session_label)->toBe('Sesi cerita')
        ->and($photo->caption)->toBe('Karya bersama');
    Storage::disk('local')->assertExists($photo->file_path);

    $this->get(route('guru.gallery.file', $photo))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('a teacher cannot upload gallery photos to or view photos from an unassigned class', function () {
    Storage::fake('local');
    $region = Region::create(['name' => 'Wilayah', 'code' => 'WIL', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Klaster', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Lingkungan', 'is_active' => true]);
    $teacher = Teacher::create(['full_name' => 'Guru', 'status' => 'active']);
    $guru = User::factory()->guru()->create(['teacher_id' => $teacher->id]);
    $class = Classes::create(['environment_id' => $environment->id, 'name' => 'Kelas Privat', 'status' => 'active']);
    $photo = ClassGalleryPhoto::query()->create([
        'class_id' => $class->id,
        'uploaded_by' => $guru->id,
        'photo_date' => now()->toDateString(),
        'file_path' => 'class-gallery/private.png',
    ]);
    Storage::disk('local')->put($photo->file_path, 'private');

    $this->actingAs($guru)->post(route('guru.gallery.store'), [
        'class_id' => $class->id,
        'photo_date' => now()->toDateString(),
        'image' => UploadedFile::fake()->image('unauthorized.png'),
    ])->assertNotFound();

    $this->get(route('guru.gallery.file', $photo))->assertNotFound();
    $this->delete(route('guru.gallery.destroy', $photo))->assertNotFound();
    $this->assertDatabaseCount('class_gallery_photos', 1);
});

test('a teacher can delete a gallery photo only from an assigned class', function () {
    Storage::fake('local');
    $region = Region::create(['name' => 'Wilayah Hapus', 'code' => 'WIL-H', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Klaster Hapus', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Lingkungan Hapus', 'is_active' => true]);
    $teacher = Teacher::create(['full_name' => 'Guru Hapus', 'status' => 'active']);
    $guru = User::factory()->guru()->create(['teacher_id' => $teacher->id]);
    $class = Classes::create(['environment_id' => $environment->id, 'name' => 'Kelas Hapus', 'status' => 'active']);
    $class->teachers()->attach($teacher);
    $photo = ClassGalleryPhoto::create([
        'class_id' => $class->id,
        'uploaded_by' => $guru->id,
        'photo_date' => now()->toDateString(),
        'file_path' => 'class-gallery/remove.png',
    ]);
    Storage::disk('local')->put($photo->file_path, 'private image');

    $this->actingAs($guru)->delete(route('guru.gallery.destroy', $photo))
        ->assertRedirect(route('guru.gallery'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('class_gallery_photos', ['id' => $photo->id]);
    Storage::disk('local')->assertMissing('class-gallery/remove.png');

    $missingFilePhoto = ClassGalleryPhoto::create([
        'class_id' => $class->id,
        'uploaded_by' => $guru->id,
        'photo_date' => now()->toDateString(),
        'file_path' => 'class-gallery/missing.png',
    ]);
    $this->actingAs($guru)->delete(route('guru.gallery.destroy', $missingFilePhoto))
        ->assertRedirect(route('guru.gallery'))
        ->assertSessionHas('success');
    $this->assertDatabaseMissing('class_gallery_photos', ['id' => $missingFilePhoto->id]);
});
