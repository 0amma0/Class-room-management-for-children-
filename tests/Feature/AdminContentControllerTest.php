<?php

use App\Models\Activity;
use App\Models\ActivityPhoto;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\PublicActivityCard;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('an administrator can upload a class photo and manage public activity cards', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $region = Region::create(['name' => 'West Java', 'code' => 'CONTENT-WJ', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Content Cluster', 'code' => 'CONTENT-C', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Content Community', 'code' => 'CONTENT-E', 'is_active' => true]);
    $class = Classes::create([
        'environment_id' => $environment->id,
        'name' => 'Review Class',
        'code' => 'CONTENT-REVIEW',
        'status' => 'active',
    ]);

    $this->actingAs($admin)->post(route('admin.master-data.store', ['resource' => 'classes']), [
        'environment_id' => $environment->id,
        'name' => 'Photo Class',
        'code' => 'CONTENT-CLASS',
        'status' => 'active',
        'photo' => UploadedFile::fake()->image('class.jpg'),
    ])->assertRedirect(route('admin.classes'));

    $class = Classes::query()->where('code', 'CONTENT-CLASS')->firstOrFail();
    Storage::disk('public')->assertExists($class->photo_path);

    $this->actingAs($admin)->post(route('admin.activity-cards.store'), [
        'title' => 'Family Visits',
        'description' => 'Build connections with families.',
        'sort_order' => 1,
        'is_active' => 1,
        'image' => UploadedFile::fake()->image('activity.jpg'),
    ])->assertRedirect(route('admin.activity-cards'));

    $publicHome = $this->get(route('home'));
    $card = PublicActivityCard::query()->firstOrFail();
    $imageUrl = Storage::disk('public')->url($card->image_path);
    Storage::disk('public')->assertExists($card->image_path);
    $publicHome->assertOk()->assertSee('Family Visits')->assertSee($imageUrl, false);
    $this->get(route('home'))->assertOk()->assertSee($imageUrl, false);

    $this->actingAs($admin)->delete(route('admin.activity-cards.destroy', $card))
        ->assertRedirect(route('admin.activity-cards'));
    $this->assertDatabaseMissing('public_activity_cards', ['id' => $card->id]);

    $activity = Activity::create([
        'title' => 'Teacher submission',
        'scope_type' => 'class',
        'class_id' => $class->id,
        'status' => 'pending_review',
        'created_by' => $admin->id,
    ]);
    $this->actingAs($admin)->put(route('admin.activities.update', $activity), ['status' => 'published'])
        ->assertRedirect(route('admin.activities'));
    $this->assertDatabaseHas('activities', [
        'id' => $activity->id,
        'status' => 'published',
        'reviewed_by' => $admin->id,
    ]);
});

test('an administrator cannot create a public card without an admin-selected image', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.activity-cards.store'), [
        'title' => 'Bahá’í Children’s Classes for Spiritual Education',
        'description' => 'A public class card.',
        'sort_order' => 0,
        'is_active' => 1,
    ])->assertSessionHasErrors('image');

    $this->assertDatabaseCount('public_activity_cards', 0);
});

test('a class photo selected by an administrator appears on the public class card', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $region = Region::create(['name' => 'West Java', 'code' => 'PHOTO-WJ', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Photo Cluster', 'code' => 'PHOTO-C', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Photo Community', 'code' => 'PHOTO-E', 'is_active' => true]);

    $this->actingAs($admin)->post(route('admin.master-data.store', ['resource' => 'classes']), [
        'environment_id' => $environment->id,
        'name' => 'Public Photo Class',
        'code' => 'PHOTO-CLASS',
        'status' => 'active',
        'photo' => UploadedFile::fake()->image('public-class.jpg'),
    ])->assertRedirect(route('admin.classes'));

    $class = Classes::query()->where('code', 'PHOTO-CLASS')->firstOrFail();
    $photoUrl = Storage::disk('public')->url($class->photo_path);
    Storage::disk('public')->assertExists($class->photo_path);
    $this->get(route('home'))->assertOk()->assertSee($photoUrl, false)->assertSee('Class photo for Public Photo Class');
});

test('an administrator can delete all reviewed activities and their stored photos', function () {
    Storage::fake('public');
    Storage::fake('local');
    $admin = User::factory()->admin()->create();
    $region = Region::create(['name' => 'Review Region']);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Review Cluster']);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Review Community']);
    $class = Classes::create([
        'environment_id' => $environment->id,
        'name' => 'Review Class',
        'status' => 'active',
    ]);
    $activity = Activity::create([
        'title' => 'Activity to remove',
        'scope_type' => 'class',
        'class_id' => $class->id,
        'status' => 'published',
    ]);
    $photo = ActivityPhoto::create([
        'activity_id' => $activity->id,
        'file_url' => 'activity-photos/review.png',
        'caption' => 'Activity image',
    ]);
    Storage::disk('public')->put($photo->file_url, 'image');
    Storage::disk('local')->put($photo->file_url, 'image');

    $this->actingAs($admin)->get(route('admin.activities'))
        ->assertOk()
        ->assertSee('onsubmit="return confirm(', false);

    $guru = User::factory()->guru()->create();
    $this->actingAs($guru)->delete(route('admin.activities.destroy-all'))->assertForbidden();
    $this->assertModelExists($activity);

    $this->actingAs($admin)->delete(route('admin.activities.destroy-all'))
        ->assertRedirect(route('admin.activities'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
    $this->assertDatabaseMissing('activity_photos', ['id' => $photo->id]);
    Storage::disk('public')->assertMissing($photo->file_url);
    Storage::disk('local')->assertMissing($photo->file_url);
    $this->assertDatabaseHas('audit_logs', ['entity_type' => 'activity_review_bulk']);
});
