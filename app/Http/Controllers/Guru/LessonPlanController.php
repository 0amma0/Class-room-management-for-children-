<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\LessonPlan;
use App\Support\GuruClassAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonPlanController extends Controller
{
    private const PROGRAM_SECTIONS = [
        'Doa Pembuka',
        'Doa',
        'Menyanyi Lagu',
        'Menghafal Kutipan',
        'Cerita',
        'Permainan',
        'Mewarnai',
        'Doa Penutup',
    ];

    public function index(Request $request, GuruClassAccess $access): View
    {
        $classes = $access->classesFor($request->user())->withCount([
            'students' => fn ($query) => $query->where('status', 'active'),
        ])->orderBy('name')->get(['id', 'name']);
        $plans = LessonPlan::query()
            ->where(function (Builder $query) use ($classes, $request): void {
                $query->whereIn('class_id', $classes->modelKeys())
                    ->orWhere(fn (Builder $query) => $query
                        ->whereNull('class_id')
                        ->where('created_by', $request->user()->id));
            })
            ->with(['class:id,name', 'creator:id,name'])
            ->orderByDesc('execution_date')
            ->paginate(10);

        return view('guru.lesson-plans', [
            'classes' => $classes,
            'plans' => $plans,
            'programSections' => self::PROGRAM_SECTIONS,
        ]);
    }

    public function store(Request $request, GuruClassAccess $access): RedirectResponse
    {
        $data = $this->validatedPlanData($request);
        $attributes = $this->planAttributes($request, $access, $data);
        LessonPlan::create($attributes);

        return redirect()->route('guru.lesson-plans')->with('success', 'Rencana pelajaran berhasil disimpan.');
    }

    public function update(Request $request, LessonPlan $lessonPlan, GuruClassAccess $access): RedirectResponse
    {
        $this->plansFor($request, $access)->findOrFail($lessonPlan->id);
        $data = $this->validatedPlanData($request, true);
        $lessonPlan->update($this->planAttributes($request, $access, $data, $lessonPlan));

        return redirect()->route('guru.lesson-plans')->with('success', 'Rencana pelajaran berhasil diperbarui.');
    }

    public function destroy(Request $request, LessonPlan $lessonPlan, GuruClassAccess $access): RedirectResponse
    {
        $this->plansFor($request, $access)->findOrFail($lessonPlan->id)->delete();

        return redirect()->route('guru.lesson-plans')->with('success', 'Rencana pelajaran berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPlanData(Request $request, bool $allowExistingProgram = false): array
    {
        return $request->validate([
            'plan_type' => ['required', 'in:class,other'],
            'class_id' => ['nullable', 'integer', 'required_if:plan_type,class'],
            'execution_date' => ['required', 'date'],
            'level' => ['nullable', 'string', 'max:100', 'required_if:plan_type,class'],
            'theme' => ['required', 'string', 'max:255'],
            'objectives' => ['required', 'string', 'max:5000'],
            'reminder_days' => ['required', 'integer', 'in:1,2'],
            'program' => [$allowExistingProgram ? 'sometimes' : 'nullable', 'array'],
            'program.*' => ['nullable', 'string', 'max:1000'],
            'program_times' => ['nullable', 'array'],
            'program_times.*' => ['nullable', 'string', 'max:50'],
            'materials' => ['nullable', 'array'],
            'materials.*.tool' => ['nullable', 'string', 'max:255'],
            'materials.*.purpose' => ['nullable', 'string', 'max:500'],
            'catch_up_notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function planAttributes(Request $request, GuruClassAccess $access, array $data, ?LessonPlan $existing = null): array
    {
        $class = $data['plan_type'] === 'class'
            ? $access->classesFor($request->user())
                ->withCount(['students' => fn ($query) => $query->where('status', 'active')])
                ->whereKey($data['class_id'])
                ->firstOrFail()
            : null;
        $program = $data['plan_type'] === 'class' ? ($existing?->program ?? []) : [];
        if ($data['plan_type'] === 'class' && array_key_exists('program', $data)) {
            $program = collect(self::PROGRAM_SECTIONS)->mapWithKeys(fn (string $section): array => [
                $section => [
                    'time' => $data['program_times'][$section] ?? '',
                    'activity' => $data['program'][$section] ?? '',
                ],
            ])->all();
        }
        $materials = $existing?->materials ?? [];
        if (array_key_exists('materials', $data)) {
            $materials = collect($data['materials'] ?? [])
                ->filter(fn (array $item): bool => filled($item['tool'] ?? null) || filled($item['purpose'] ?? null))
                ->values()
                ->all();
        }

        return [
            'class_id' => $class?->id,
            'plan_type' => $data['plan_type'],
            'curriculum_lesson_id' => null,
            'created_by' => $request->user()->id,
            'execution_date' => $data['execution_date'],
            'level' => $data['level'] ?? null,
            'student_count' => $class?->students_count ?? 0,
            'theme' => $data['theme'],
            'objectives' => $data['objectives'],
            'program' => $program,
            'materials' => $materials,
            'catch_up_notes' => $data['catch_up_notes'] ?? null,
            'status' => 'draft',
            'reminder_days' => (int) $data['reminder_days'],
            'reminder_sent_at' => null,
        ];
    }

    /**
     * @return Builder<LessonPlan>
     */
    private function plansFor(Request $request, GuruClassAccess $access): Builder
    {
        return LessonPlan::query()
            ->where(function (Builder $query) use ($request, $access): void {
                $query->whereIn('class_id', $access->classesFor($request->user())->select('classes.id'))
                    ->orWhere(fn (Builder $query) => $query
                        ->whereNull('class_id')
                        ->where('created_by', $request->user()->id));
            });
    }
}
