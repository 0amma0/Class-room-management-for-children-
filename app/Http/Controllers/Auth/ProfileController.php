<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->loadMissing('teacher');

        return view('profile.show', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'avatar_type' => ['nullable', Rule::in(['bangs', 'pigtails', 'afro', 'short', 'bun', 'braids', 'short-blue'])],
        ];
        if ($user->isGuru()) {
            $rules = [
                ...$rules,
                'teacher_full_name' => ['sometimes', 'required', 'string', 'max:255'],
                'teacher_nickname' => ['sometimes', 'nullable', 'string', 'max:100'],
                'teacher_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
                'teacher_address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            ];
        }
        $data = $request->validate($rules);

        $previousAvatar = $user->avatar_path;
        $hasAvatarTypeColumn = Schema::hasColumn('users', 'avatar_type');
        $avatarType = $hasAvatarTypeColumn ? ($data['avatar_type'] ?? $user->avatar_type) : null;
        $avatarPath = $previousAvatar;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');

            if (! is_string($avatarPath)) {
                throw ValidationException::withMessages(['avatar' => 'Foto profil gagal disimpan. Silakan coba lagi.']);
            }

            $avatarType = null;
        } elseif ($hasAvatarTypeColumn && array_key_exists('avatar_type', $data)) {
            $avatarPath = null;
        }

        $profileAttributes = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'avatar_path' => $avatarPath,
        ];
        if ($hasAvatarTypeColumn) {
            $profileAttributes['avatar_type'] = $avatarType;
        }
        $user->fill($profileAttributes);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->isGuru() && $user->teacher !== null) {
            $teacherData = array_intersect_key($data, array_flip([
                'teacher_full_name',
                'teacher_nickname',
                'teacher_phone',
                'teacher_address',
            ]));
            if ($teacherData !== []) {
                $user->teacher->fill([
                    'full_name' => $teacherData['teacher_full_name'] ?? $user->teacher->full_name,
                    'nickname' => $teacherData['teacher_nickname'] ?? $user->teacher->nickname,
                    'phone' => $teacherData['teacher_phone'] ?? $user->teacher->phone,
                    'address' => $teacherData['teacher_address'] ?? $user->teacher->address,
                ])->save();
            }
        }

        if ($previousAvatar !== null && $previousAvatar !== $avatarPath) {
            Storage::disk('public')->delete($previousAvatar);
        }

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    }
}
