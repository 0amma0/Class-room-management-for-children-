<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\LessonPlan;
use App\Models\TeacherReflection;
use App\Models\User;
use App\Support\GuruClassAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TeacherModuleController extends Controller
{
    public function index(Request $request, GuruClassAccess $access): View
    {
        $classes = $access->classesFor($request->user())->orderBy('name')->get(['id', 'name']);
        $classIds = $classes->modelKeys();
        $reflections = TeacherReflection::query()
            ->with(['class:id,name', 'lessonPlan:id,theme', 'teacher:id,full_name'])
            ->orderByDesc('reflection_date')
            ->orderByDesc('id')
            ->paginate(10);
        $plans = LessonPlan::query()
            ->whereIn('class_id', $classIds)
            ->orderByDesc('execution_date')
            ->get(['id', 'class_id', 'theme', 'execution_date']);

        return view('guru.teacher-module', compact('classes', 'plans', 'reflections'));
    }

    public function storeReflection(Request $request, GuruClassAccess $access): RedirectResponse
    {
        abort_unless($request->user()->teacher_id !== null, 403, 'Akun guru belum terhubung ke data guru.');
        $data = $this->validatedReflectionData($request);
        DB::transaction(function () use ($request, $access, $data): void {
            $reflection = TeacherReflection::create($this->reflectionAttributes($request, $access, $data));
            $reflection->load(['class:id,name', 'teacher:id,full_name']);
            $recipients = User::query()
                ->whereIn('role', ['admin', 'guru'])
                ->where('status', 'active')
                ->get(['id']);

            foreach ($recipients as $recipient) {
                AppNotification::query()->create([
                    'user_id' => $recipient->id,
                    'type' => 'reflection',
                    'teacher_reflection_id' => $reflection->id,
                    'title' => 'Refleksi baru dari '.$reflection->teacher->full_name,
                    'message' => $reflection->class->name.' · '.$reflection->reflection_date->format('d M Y'),
                ]);
            }
        });

        return redirect()->route('guru.teacher-module')->with('success', 'Refleksi pembelajaran berhasil disimpan.');
    }

    public function updateReflection(Request $request, TeacherReflection $reflection, GuruClassAccess $access): RedirectResponse
    {
        $this->reflectionsFor($request, $access)->findOrFail($reflection->id);
        $data = $this->validatedReflectionData($request);
        $reflection->update($this->reflectionAttributes($request, $access, $data));

        return redirect()->route('guru.teacher-module')->with('success', 'Refleksi berhasil diperbarui.');
    }

    public function destroyReflection(Request $request, TeacherReflection $reflection, GuruClassAccess $access): RedirectResponse
    {
        $this->reflectionsFor($request, $access)->findOrFail($reflection->id)->delete();

        return redirect()->route('guru.teacher-module')->with('success', 'Refleksi berhasil dihapus.');
    }

    public function pdf(): BinaryFileResponse
    {
        $path = base_path('docs/pengembangan/MODUL PEGANGAN GURU MENGAJAR KELAS ANAK.pdf');
        abort_unless(is_file($path), 404, 'Dokumen modul guru tidak ditemukan.');

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="MODUL-PEGANGAN-GURU.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedReflectionData(Request $request): array
    {
        return $request->validate([
            'class_id' => ['required', 'integer'],
            'lesson_plan_id' => ['nullable', 'integer'],
            'reflection_date' => ['required', 'date', 'before_or_equal:today'],
            'objectives_met' => ['nullable', 'string', 'max:5000'],
            'plan_alignment' => ['nullable', 'string', 'max:5000'],
            'what_went_well' => ['nullable', 'string', 'max:5000'],
            'challenges' => ['nullable', 'string', 'max:5000'],
            'improvement' => ['nullable', 'string', 'max:5000'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function reflectionAttributes(Request $request, GuruClassAccess $access, array $data): array
    {
        $access->classesFor($request->user())->whereKey($data['class_id'])->firstOrFail();

        if (filled($data['lesson_plan_id'] ?? null)) {
            LessonPlan::query()
                ->whereIn('class_id', $access->classesFor($request->user())->select('classes.id'))
                ->where('class_id', $data['class_id'])
                ->findOrFail($data['lesson_plan_id']);
        }

        return [
            ...$data,
            'teacher_id' => $request->user()->teacher_id,
        ];
    }

    /**
     * @return Builder<TeacherReflection>
     */
    private function reflectionsFor(Request $request, GuruClassAccess $access): Builder
    {
        return TeacherReflection::query()
            ->where('teacher_id', $request->user()->teacher_id)
            ->whereIn('class_id', $access->classesFor($request->user())->select('classes.id'));
    }
}
