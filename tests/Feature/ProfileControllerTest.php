<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('an authenticated user can update profile information without changing the password', function () {
    $user = User::factory()->admin()->create([
        'password' => 'OldPassword123',
        'email' => 'old@example.test',
    ]);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Updated Admin',
        'email' => 'new@example.test',
        'phone' => '08123456789',
    ]);

    $response->assertRedirect(route('profile'));
    $user->refresh();
    expect($user->name)->toBe('Updated Admin')
        ->and($user->email)->toBe('new@example.test')
        ->and($user->phone)->toBe('08123456789')
        ->and(Hash::check('OldPassword123', $user->password))->toBeTrue()
        ->and($user->must_change_password)->toBeFalse();
});

test('a password change requires the correct current password on its separate form', function () {
    $user = User::factory()->admin()->create(['password' => 'OldPassword123']);

    $response = $this->actingAs($user)->post(route('password.update'), [
        'current_password' => 'incorrect-password',
        'password' => 'NewPassword123',
        'password_confirmation' => 'NewPassword123',
    ]);

    $response->assertSessionHasErrors('current_password');
    expect(Hash::check('OldPassword123', $user->fresh()->password))->toBeTrue();
});

test('an admin must use a strong new password on the profile password form', function () {
    $user = User::factory()->admin()->create(['password' => 'OldPassword123']);

    $this->actingAs($user)->post(route('password.update'), [
        'current_password' => 'OldPassword123',
        'password' => 'WeakPassword123',
        'password_confirmation' => 'WeakPassword123',
    ])->assertSessionHasErrors('password');

    expect(Hash::check('OldPassword123', $user->fresh()->password))->toBeTrue();
});

test('a user can upload a profile photo to public storage', function () {
    Storage::fake('public');
    $user = User::factory()->guru()->create();

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('profile.jpg'),
    ])->assertRedirect(route('profile'));

    $user->refresh();
    expect($user->avatar_path)->toStartWith('avatars/');
    expect($user->avatar_type)->toBeNull();
    $this->assertTrue(Storage::disk('public')->exists($user->avatar_path));
});

test('profile details and an uploaded avatar save independently from password changes', function () {
    Storage::fake('public');
    $user = User::factory()->guru()->create([
        'password' => 'OldPassword123',
        'email' => 'before@example.test',
    ]);

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Updated Teacher',
        'email' => 'after@example.test',
        'current_password' => 'OldPassword123',
        'password' => 'NewPassword123',
        'password_confirmation' => 'NewPassword123',
        'avatar' => UploadedFile::fake()->image('profile-update.png'),
    ])->assertRedirect(route('profile'));

    $user->refresh();
    expect($user->name)->toBe('Updated Teacher')
        ->and($user->email)->toBe('after@example.test')
        ->and(Hash::check('OldPassword123', $user->password))->toBeTrue()
        ->and($user->avatar_path)->toStartWith('avatars/')
        ->and($user->avatar_type)->toBeNull()
        ->and($user->must_change_password)->toBeFalse();
    $this->assertTrue(Storage::disk('public')->exists($user->avatar_path));
});

test('a user can change their password without submitting profile fields', function () {
    $user = User::factory()->admin()->create([
        'password' => 'OldPassword123',
        'name' => 'Unchanged Admin',
    ]);

    $this->actingAs($user)->post(route('password.update'), [
        'current_password' => 'OldPassword123',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ])->assertRedirect(route('profile'));

    expect($user->fresh()->name)->toBe('Unchanged Admin')
        ->and(Hash::check('NewPassword123!', $user->fresh()->password))->toBeTrue();
});

test('a user can choose one of the preset profile avatars', function () {
    Storage::fake('public');
    $user = User::factory()->guru()->create(['avatar_path' => 'avatars/old-photo.jpg']);
    Storage::disk('public')->put('avatars/old-photo.jpg', 'old image');

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar_type' => 'short-blue',
    ])->assertRedirect(route('profile'));

    $user->refresh();
    expect($user->avatar_type)->toBe('short-blue')
        ->and($user->avatar_path)->toBeNull();
    $this->assertFalse(Storage::disk('public')->exists('avatars/old-photo.jpg'));
});

test('saving profile details keeps an existing uploaded avatar when no avatar choice changes', function () {
    Storage::fake('public');
    $user = User::factory()->guru()->create(['avatar_path' => 'avatars/current-photo.jpg']);
    Storage::disk('public')->put('avatars/current-photo.jpg', 'existing image');

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Updated Teacher',
        'email' => $user->email,
        'phone' => '08123456789',
    ])->assertRedirect(route('profile'));

    expect($user->fresh()->avatar_path)->toBe('avatars/current-photo.jpg');
    $this->assertTrue(Storage::disk('public')->exists('avatars/current-photo.jpg'));
});

test('profile settings show preset avatars and keep username and role read-only', function () {
    $user = User::factory()->guru()->create([
        'username' => 'teacher.account',
        'must_change_password' => false,
    ]);

    $this->actingAs($user)->get(route('profile'))
        ->assertOk()
        ->assertSee('Profile Settings')
        ->assertSee('Choose your avatar')
        ->assertSee('action="'.route('password.update').'"', false)
        ->assertSee('value="teacher.account" readonly', false)
        ->assertSee('Role assigned by your organization');
});

test('profile rejects avatar types outside the bundled presets', function () {
    $user = User::factory()->guru()->create();

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar_type' => 'admin-avatar',
    ])->assertSessionHasErrors('avatar_type');

    expect($user->fresh()->avatar_type)->toBeNull();
});

test('admin and teacher accounts receive separate profile forms', function () {
    $admin = User::factory()->admin()->create();
    $guru = User::factory()->guru()->create();

    $this->actingAs($admin)->get(route('profile'))
        ->assertOk()
        ->assertSee('Admin account details')
        ->assertSee('name="username"', false)
        ->assertDontSee('Teaching profile');
    $this->actingAs($guru)->get(route('profile'))
        ->assertOk()
        ->assertSee('Teaching profile')
        ->assertDontSee('Admin account details');
});

test('an admin can update their login username and another accounts username is rejected', function () {
    $admin = User::factory()->admin()->create(['username' => 'original_admin']);
    $otherAdmin = User::factory()->admin()->create(['username' => 'other_admin']);

    $this->actingAs($admin)->put(route('profile.update'), [
        'name' => $admin->name,
        'username' => 'updated_admin',
        'email' => $admin->email,
    ])->assertRedirect(route('profile'));

    expect($admin->fresh()->username)->toBe('updated_admin');

    $this->actingAs($admin)->put(route('profile.update'), [
        'name' => $admin->name,
        'username' => $otherAdmin->username,
        'email' => $admin->email,
    ])->assertSessionHasErrors('username');

    expect($admin->fresh()->username)->toBe('updated_admin');
});

test('a teacher cannot change their login username through profile input', function () {
    $guru = User::factory()->guru()->create(['username' => 'fixed_teacher_login']);

    $this->actingAs($guru)->put(route('profile.update'), [
        'name' => $guru->name,
        'username' => 'changed_by_teacher',
        'email' => $guru->email,
    ])->assertRedirect(route('profile'));

    expect($guru->fresh()->username)->toBe('fixed_teacher_login');
});

test('a teacher can update linked teacher profile details from the teacher profile form', function () {
    $guru = User::factory()->guru()->create();

    $this->actingAs($guru)->put(route('profile.update'), [
        'name' => $guru->name,
        'email' => $guru->email,
        'teacher_full_name' => 'Nama Guru Baru',
        'teacher_nickname' => 'Kak Baru',
        'teacher_phone' => '0811222333',
        'teacher_address' => 'Alamat Guru Baru',
    ])->assertRedirect(route('profile'));

    $this->assertDatabaseHas('teachers', [
        'id' => $guru->teacher_id,
        'full_name' => 'Nama Guru Baru',
        'nickname' => 'Kak Baru',
        'phone' => '0811222333',
        'address' => 'Alamat Guru Baru',
    ]);
});
