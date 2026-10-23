<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeacherAccountController extends Controller
{
    public function index(): View
    {
        $teachers = Teacher::query()
            ->with(['user', 'classes'])
            ->orderBy('full_name')
            ->paginate(15);
        $classes = Classes::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('admin.teachers.index', compact('teachers', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $temporaryPassword = Str::random(24);

        [$teacher, $user] = DB::transaction(function () use ($request, $data, $temporaryPassword): array {
            $teacherPayload = [
                'full_name' => $data['full_name'],
                'nickname' => $data['nickname'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => 'active',
            ];

            if (Schema::hasColumn('teachers', 'teacher_code')) {
                $teacherPayload['teacher_code'] = 'TCH-'.Str::upper((string) Str::ulid());
            }

            $teacher = Teacher::create($teacherPayload);
            $user = User::create([
                'name' => $data['full_name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($temporaryPassword),
                'role' => 'guru',
                'teacher_id' => $teacher->id,
                'status' => 'active',
                'must_change_password' => true,
            ]);

            $this->syncClasses($teacher, $data['classes'], (int) $data['primary_class_id']);
            $this->audit($request, 'created', 'teacher_account', $teacher->id, $data['classes']);

            return [$teacher, $user];
        });

        return redirect()->route('admin.teachers')
            ->with('success', 'Akun guru berhasil dibuat.')
            ->with('temporary_credentials', [
                'username' => $user->username,
                'password' => $temporaryPassword,
            ]);
    }

    public function update(Request $request, int $teacherId): RedirectResponse
    {
        $teacher = Teacher::with('user')->findOrFail($teacherId);

        if (! $teacher->user) {
            abort(404, 'Akun guru tidak ditemukan.');
        }

        $data = $this->validatedData($request, $teacher->user);

        DB::transaction(function () use ($request, $teacher, $data): void {
            $teacher->update([
                'full_name' => $data['full_name'],
                'nickname' => $data['nickname'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
            $teacher->user->update([
                'name' => $data['full_name'],
                'username' => $data['username'],
                'email' => $data['email'],
            ]);
            $this->syncClasses($teacher, $data['classes'], (int) $data['primary_class_id']);
            $this->audit($request, 'updated', 'teacher_account', $teacher->id, $data['classes']);
        });

        return redirect()->route('admin.teachers')->with('success', 'Data akun guru berhasil diperbarui.');
    }

    public function deactivate(Request $request, int $teacherId): RedirectResponse
    {
        $teacher = Teacher::with('user')->findOrFail($teacherId);

        if (! $teacher->user) {
            abort(404, 'Akun guru tidak ditemukan.');
        }

        DB::transaction(function () use ($request, $teacher): void {
            $primaryClassIds = DB::table('class_teachers')
                ->where('teacher_id', $teacher->id)
                ->where('is_primary', true)
                ->pluck('class_id');

            foreach ($primaryClassIds as $classId) {
                $successor = DB::table('class_teachers')
                    ->join('teachers', 'teachers.id', '=', 'class_teachers.teacher_id')
                    ->where('class_teachers.class_id', $classId)
                    ->where('class_teachers.teacher_id', '!=', $teacher->id)
                    ->where('teachers.status', 'active')
                    ->orderBy('class_teachers.teacher_id')
                    ->value('class_teachers.teacher_id');

                if ($successor === null) {
                    throw ValidationException::withMessages([
                        'account' => 'Tugaskan guru aktif lain sebagai guru utama sebelum menonaktifkan akun ini.',
                    ]);
                }

                DB::table('class_teachers')
                    ->where('class_id', $classId)
                    ->update(['is_primary' => false]);
                DB::table('class_teachers')
                    ->where('class_id', $classId)
                    ->where('teacher_id', $successor)
                    ->update(['is_primary' => true]);
            }

            $teacher->update(['status' => 'inactive']);
            $teacher->user->update(['status' => 'inactive']);
            DB::table('sessions')->where('user_id', $teacher->user->id)->delete();
            $this->audit($request, 'deactivated', 'teacher_account', $teacher->id);
        });

        return redirect()->route('admin.teachers')->with('success', 'Akun guru dinonaktifkan dan sesi aktifnya dicabut.');
    }

    public function destroy(Request $request, int $teacherId): RedirectResponse
    {
        $teacher = Teacher::with('user')->findOrFail($teacherId);

        if (! $teacher->user) {
            abort(404, 'Akun guru tidak ditemukan.');
        }

        $hasHistory = DB::table('class_teachers')->where('teacher_id', $teacher->id)->exists()
            || DB::table('teacher_reflections')->where('teacher_id', $teacher->id)->exists()
            || DB::table('knowledge_sharing')->where('teacher_id', $teacher->id)->exists();

        if ($hasHistory) {
            throw ValidationException::withMessages([
                'delete' => 'Guru masih memiliki penugasan atau riwayat terkait. Nonaktifkan akun untuk mempertahankan riwayat.',
            ]);
        }

        DB::transaction(function () use ($request, $teacher): void {
            $this->audit($request, 'deleted', 'teacher_account', $teacher->id);
            DB::table('sessions')->where('user_id', $teacher->user->id)->delete();
            $teacher->user->delete();
            $teacher->delete();
        });

        return redirect()->route('admin.teachers')->with('success', 'Akun guru berhasil dihapus permanen.');
    }

    public function reactivate(Request $request, int $teacherId): RedirectResponse
    {
        $teacher = Teacher::with('user')->findOrFail($teacherId);

        if (! $teacher->user) {
            abort(404, 'Akun guru tidak ditemukan.');
        }

        DB::transaction(function () use ($request, $teacher): void {
            $teacher->update(['status' => 'active']);
            $teacher->user->update(['status' => 'active']);
            $this->audit($request, 'reactivated', 'teacher_account', $teacher->id);
        });

        return redirect()->route('admin.teachers')->with('success', 'Akun guru berhasil diaktifkan kembali.');
    }

    public function resetPassword(Request $request, int $teacherId): RedirectResponse
    {
        $teacher = Teacher::with('user')->findOrFail($teacherId);

        if (! $teacher->user) {
            abort(404, 'Akun guru tidak ditemukan.');
        }

        $temporaryPassword = Str::random(24);

        DB::transaction(function () use ($request, $teacher, $temporaryPassword): void {
            $teacher->user->update([
                'password' => Hash::make($temporaryPassword),
                'must_change_password' => true,
            ]);
            DB::table('sessions')->where('user_id', $teacher->user->id)->delete();
            $this->audit($request, 'password_reset', 'teacher_account', $teacher->id);
        });

        return redirect()->route('admin.teachers')->with('temporary_credentials', [
            'username' => $teacher->user->username,
            'password' => $temporaryPassword,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:5000'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'classes' => ['required', 'array', 'min:1'],
            'classes.*' => ['required', 'integer', 'distinct', Rule::exists('classes', 'id')->where('status', 'active')],
            'primary_class_id' => ['required', 'integer', Rule::exists('classes', 'id')->where('status', 'active')],
        ], [
            'classes.required' => 'Pilih minimal satu kelas.',
            'primary_class_id.required' => 'Pilih guru utama untuk satu kelas.',
        ]);

        if (! in_array((int) $data['primary_class_id'], array_map('intval', $data['classes']), true)) {
            throw ValidationException::withMessages([
                'primary_class_id' => 'Kelas utama harus termasuk dalam daftar kelas yang ditugaskan.',
            ]);
        }

        return $data;
    }

    /**
     * @param  array<int, int|string>  $classIds
     */
    private function syncClasses(Teacher $teacher, array $classIds, int $primaryClassId): void
    {
        $classIds = array_map('intval', $classIds);
        $classIds = array_values(array_unique($classIds));
        $classes = Classes::query()->whereIn('id', $classIds)->lockForUpdate()->get(['id']);

        if ($classes->count() !== count($classIds)) {
            throw ValidationException::withMessages(['classes' => 'Salah satu kelas tidak lagi tersedia.']);
        }

        $existingAssignments = DB::table('class_teachers')
            ->where('teacher_id', $teacher->id)
            ->get(['class_id', 'is_primary']);
        $removedPrimaryClassIds = $existingAssignments
            ->where('is_primary', true)
            ->pluck('class_id')
            ->diff($classIds);

        foreach ($removedPrimaryClassIds as $removedClassId) {
            $successor = DB::table('class_teachers')
                ->join('teachers', 'teachers.id', '=', 'class_teachers.teacher_id')
                ->where('class_teachers.class_id', $removedClassId)
                ->where('class_teachers.teacher_id', '!=', $teacher->id)
                ->where('teachers.status', 'active')
                ->orderBy('class_teachers.teacher_id')
                ->value('class_teachers.teacher_id');

            if ($successor === null) {
                throw ValidationException::withMessages([
                    'classes' => 'Penugasan utama tidak dapat dicabut sebelum guru aktif lain ditetapkan sebagai guru utama.',
                ]);
            }

            DB::table('class_teachers')
                ->where('class_id', $removedClassId)
                ->update(['is_primary' => false]);
            DB::table('class_teachers')
                ->where('class_id', $removedClassId)
                ->where('teacher_id', $successor)
                ->update(['is_primary' => true]);
        }

        $syncData = [];

        foreach ($classIds as $classId) {
            if ($classId === $primaryClassId) {
                DB::table('class_teachers')
                    ->where('class_id', $classId)
                    ->update(['is_primary' => false]);
                $isPrimary = true;
            } else {
                $hasPrimaryTeacher = DB::table('class_teachers')
                    ->where('class_id', $classId)
                    ->where('teacher_id', '!=', $teacher->id)
                    ->where('is_primary', true)
                    ->exists();
                $isPrimary = ! $hasPrimaryTeacher;
            }

            $syncData[$classId] = ['is_primary' => $isPrimary];
        }

        $teacher->classes()->sync($syncData);
    }

    /**
     * @param  array<int, int|string>  $classIds
     */
    private function audit(Request $request, string $action, string $entity, int $entityId, array $classIds = []): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'entity_type' => $entity,
            'entity_id' => $entityId,
            'meta' => $classIds === [] ? null : ['class_ids' => array_map('intval', $classIds)],
            'ip' => $request->ip(),
        ]);
    }
}
