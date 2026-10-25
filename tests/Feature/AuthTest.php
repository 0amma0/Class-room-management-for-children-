<?php

use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('Sign In');
});

test('admin can authenticate and is redirected to admin dashboard', function () {
    $admin = User::factory()->admin()->create([
        'username' => 'admin_test',
        'password' => Hash::make('Password123'),
        'status' => 'active',
        'must_change_password' => false,
    ]);

    $response = $this->post('/login', [
        'login' => 'admin_test',
        'password' => 'Password123',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect('/admin/dashboard');
    $this->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('Sign Out')
        ->assertSee('overflow-y-auto', false);
});

test('guru can authenticate and is redirected to guru dashboard', function () {
    $guru = User::factory()->guru()->create([
        'username' => 'guru_test',
        'password' => Hash::make('Password123'),
        'status' => 'active',
        'must_change_password' => false,
    ]);

    $response = $this->post('/login', [
        'login' => 'guru_test',
        'password' => 'Password123',
    ]);

    $this->assertAuthenticatedAs($guru);
    $response->assertRedirect('/guru/dashboard');
});

test('an admin reset temporary password replaces the old password and remains usable for active teachers', function () {
    RateLimiter::clear(Str::transliterate(Str::lower('reset_password_guru').'|127.0.0.1'));
    $admin = User::factory()->admin()->create();
    $teacher = Teacher::create(['full_name' => 'Reset Password Teacher', 'status' => 'active']);
    $guru = User::factory()->guru()->create([
        'teacher_id' => $teacher->id,
        'username' => 'reset_password_guru',
        'password' => 'OldPassword123',
        'status' => 'active',
        'must_change_password' => false,
    ]);

    $this->post('/login', [
        'login' => $guru->username,
        'password' => 'OldPassword123',
    ])->assertRedirect('/guru/dashboard');
    $this->assertAuthenticatedAs($guru);
    $this->post('/logout')->assertRedirect('/');

    $this->actingAs($admin)
        ->post(route('admin.teachers.reset-password', $teacher->id))
        ->assertRedirect(route('admin.teachers'));

    $credentials = session('temporary_credentials');
    $guru->refresh();
    expect($guru->status)->toBe('active')
        ->and($guru->must_change_password)->toBeTrue()
        ->and(Hash::check($credentials['password'], $guru->password))->toBeTrue();

    $this->post('/logout')->assertRedirect('/');
    $this->post('/login', [
        'login' => $guru->username,
        'password' => 'OldPassword123',
    ])->assertSessionHasErrors('login');
    $this->assertGuest();

    $loginResponse = $this->post('/login', [
        'login' => $guru->username,
        'password' => $credentials['password'],
    ]);
    $loginResponse->assertRedirect('/guru/dashboard');

    $this->assertAuthenticatedAs($guru);
    $this->get('/guru/dashboard')->assertRedirect(route('password.change'));
    $this->get(route('password.change'))->assertOk();
    $this->post(route('password.update'), [
        'current_password' => $credentials['password'],
        'password' => 'NewTeacherPass123',
        'password_confirmation' => 'NewTeacherPass123',
    ])->assertRedirect(route('profile'));
    expect($guru->fresh()->must_change_password)->toBeFalse();
    $this->get('/guru/dashboard')->assertOk();
});

test('inactive users cannot authenticate', function () {
    $user = User::factory()->create([
        'username' => 'inactive_user',
        'password' => Hash::make('Password123'),
        'status' => 'inactive',
    ]);

    $response = $this->post('/login', [
        'login' => 'inactive_user',
        'password' => 'Password123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('login');
});

test('users with must_change_password must change it before accessing their dashboard', function () {
    $guru = User::factory()->guru()->mustChangePassword()->create([
        'username' => 'guru_must_change',
        'password' => Hash::make('Password123'),
        'status' => 'active',
    ]);

    $response = $this->post('/login', [
        'login' => 'guru_must_change',
        'password' => 'Password123',
    ]);

    $this->assertAuthenticatedAs($guru);
    $response->assertRedirect('/guru/dashboard');
    $this->get('/guru/dashboard')->assertRedirect(route('password.change'));
});

test('teacher accounts created by admin can sign in with the temporary password', function () {
    $admin = User::factory()->admin()->create([
        'username' => 'admin_teacher_create',
        'password' => Hash::make('AdminPass123'),
        'status' => 'active',
        'must_change_password' => false,
    ]);

    $region = Region::create(['name' => 'West Java', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Cluster Teacher Login', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Community Teacher Login', 'is_active' => true]);
    $class = Classes::create([
        'environment_id' => $environment->id,
        'name' => 'Teacher Login Class',
        'code' => 'TLC-01',
        'status' => 'active',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.teachers.store'), [
            'full_name' => 'Guru Login Test',
            'nickname' => 'Guru',
            'phone' => '08123456789',
            'address' => 'Bandung',
            'username' => 'guru_login_test',
            'email' => 'guru-login-test@example.com',
            'classes' => [$class->id],
            'primary_class_id' => $class->id,
        ])
        ->assertRedirect(route('admin.teachers'));

    $teacherUser = User::where('username', 'guru_login_test')->firstOrFail();
    $temporaryPassword = session('temporary_credentials.password');

    expect($teacherUser->role)->toBe('guru')
        ->and($teacherUser->status)->toBe('active')
        ->and($teacherUser->must_change_password)->toBeTrue()
        ->and(Hash::check($temporaryPassword, $teacherUser->password))->toBeTrue();

    $this->post('/logout')->assertRedirect('/');

    $response = $this->post('/login', [
        'login' => 'guru_login_test',
        'password' => $temporaryPassword,
    ]);

    $this->assertAuthenticatedAs($teacherUser);
    $response->assertRedirect('/guru/dashboard');
});

test('authenticated users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
