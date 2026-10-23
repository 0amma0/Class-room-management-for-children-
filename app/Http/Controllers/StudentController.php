<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->studentsForUser($request->user())
            ->with(['class:id,name,environment_id', 'class.environment.cluster.region'])
            ->orderBy('status')
            ->orderBy('full_name');

        $filters = [
            'region_id' => $request->query('region_id'),
            'cluster_id' => $request->query('cluster_id'),
            'environment_id' => $request->query('environment_id'),
        ];

        $this->applyHierarchyFilters($query, $filters, $request->user());

        $students = $query->paginate(15)->appends($filters);

        $regions = Region::query()->orderBy('name')->get(['id', 'name']);
        $clusters = Cluster::query()
            ->when($filters['region_id'], fn ($clusterQuery) => $clusterQuery->where('region_id', $filters['region_id']))
            ->orderBy('name')
            ->get(['id', 'region_id', 'name']);
        $environments = Environment::query()
            ->when($filters['cluster_id'], fn ($environmentQuery) => $environmentQuery->where('cluster_id', $filters['cluster_id']))
            ->when($filters['region_id'] && ! $filters['cluster_id'], fn ($environmentQuery) => $environmentQuery->whereHas('cluster', fn ($clusterQuery) => $clusterQuery->where('region_id', $filters['region_id'])))
            ->orderBy('name')
            ->get(['id', 'cluster_id', 'name']);
        $classes = $this->availableClasses($request->user())
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('guru.students', compact('students', 'classes', 'regions', 'clusters', 'environments', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $this->authorizeClass($request->user(), (int) $data['class_id']);

        DB::transaction(function () use ($request, $data): void {
            $student = Student::create([
                ...$data,
                'status' => 'active',
            ]);
            $this->audit($request, 'created', $student, ['class_id' => $student->class_id]);
        });

        return $this->redirectToList($request, 'Peserta berhasil ditambahkan.');
    }

    public function update(Request $request, int $studentId): RedirectResponse
    {
        $student = $this->studentsForUser($request->user())->findOrFail($studentId);
        $data = $this->validatedData($request, $student);
        $this->authorizeClass($request->user(), (int) $data['class_id']);

        DB::transaction(function () use ($request, $student, $data): void {
            $student->update($data);
            $this->audit($request, 'updated', $student, ['class_id' => $student->class_id]);
        });

        return $this->redirectToList($request, 'Data peserta berhasil diperbarui.');
    }

    public function deactivate(Request $request, int $studentId): RedirectResponse
    {
        $student = $this->studentsForUser($request->user())->findOrFail($studentId);

        DB::transaction(function () use ($request, $student): void {
            $student->update(['status' => 'inactive']);
            $this->audit($request, 'deactivated', $student);
        });

        return $this->redirectToList($request, 'Peserta berhasil dinonaktifkan.');
    }

    public function destroy(Request $request, int $studentId): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $student = $this->studentsForUser($request->user())->findOrFail($studentId);
        $hasHistory = DB::table('attendances')->where('student_id', $student->id)->exists()
            || DB::table('developments')->where('student_id', $student->id)->exists()
            || DB::table('parent_questions')->where('student_id', $student->id)->exists()
            || DB::table('parent_visits')->where('student_id', $student->id)->exists()
            || DB::table('student_quote_progress')->where('student_id', $student->id)->exists();

        if ($hasHistory) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'delete' => 'Peserta memiliki riwayat terkait dan tidak dapat dihapus permanen. Nonaktifkan peserta untuk mempertahankan riwayat.',
            ]);
        }

        DB::transaction(function () use ($request, $student): void {
            $this->audit($request, 'deleted', $student);
            $student->delete();
        });

        return $this->redirectToList($request, 'Peserta berhasil dihapus permanen.');
    }

    /**
     * @return Builder<Student>
     */
    private function studentsForUser(User $user): Builder
    {
        $query = Student::query();

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereHas('class', function (Builder $classQuery) use ($user): void {
            $classQuery->where('classes.status', 'active')
                ->whereHas('teachers', function (Builder $teacherQuery) use ($user): void {
                    $teacherQuery->whereKey($user->teacher_id)
                        ->where('teachers.status', 'active');
                });
        });
    }

    /**
     * @return Builder<Classes>
     */
    private function availableClasses(User $user): Builder
    {
        $query = Classes::query()->where('classes.status', 'active');

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereHas('teachers', function (Builder $teacherQuery) use ($user): void {
            $teacherQuery->whereKey($user->teacher_id)
                ->where('teachers.status', 'active');
        });
    }

    private function authorizeClass(User $user, int $classId): void
    {
        abort_unless($this->availableClasses($user)->whereKey($classId)->exists(), 403);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyHierarchyFilters(Builder $query, array $filters, User $user): void
    {
        $regionId = $filters['region_id'] ?? null;
        $clusterId = $filters['cluster_id'] ?? null;
        $environmentId = $filters['environment_id'] ?? null;

        if ($regionId !== null && $regionId !== '') {
            $query->whereHas('class.environment.cluster', fn (Builder $clusterQuery) => $clusterQuery->where('region_id', (int) $regionId));
        }

        if ($clusterId !== null && $clusterId !== '') {
            $query->whereHas('class.environment.cluster', fn (Builder $clusterQuery) => $clusterQuery->whereKey((int) $clusterId));
        }

        if ($environmentId !== null && $environmentId !== '') {
            $query->whereHas('class', fn (Builder $classQuery) => $classQuery->where('environment_id', (int) $environmentId));
        }

        if (! $user->isAdmin()) {
            $query->whereHas('class.teachers', function (Builder $teacherQuery) use ($user): void {
                $teacherQuery->whereKey($user->teacher_id)
                    ->where('teachers.status', 'active');
            });
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Student $student = null): array
    {
        return $request->validate([
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'student_code' => ['nullable', 'string', 'max:255', Rule::unique('students', 'student_code')->ignore($student?->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'address' => ['nullable', 'string', 'max:5000'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function audit(Request $request, string $action, Student $student, ?array $meta = null): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'entity_type' => 'student',
            'entity_id' => $student->id,
            'meta' => $meta,
            'ip' => $request->ip(),
        ]);
    }

    private function redirectToList(Request $request, string $message): RedirectResponse
    {
        $route = $request->user()->isAdmin() ? 'admin.students' : 'guru.students';

        return redirect()->route($route)->with('success', $message);
    }
}
