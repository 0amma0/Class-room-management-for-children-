<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Development;
use App\Models\DevelopmentCategory;
use App\Models\Student;
use App\Support\GuruClassAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DevelopmentController extends Controller
{
    public function index(Request $request, GuruClassAccess $access): View
    {
        $classes = $access->classesFor($request->user())->orderBy('name')->get(['id', 'name']);
        $classIds = $classes->modelKeys();
        $developments = Development::query()
            ->with(['student:id,class_id,full_name', 'student.class:id,name', 'category:id,name'])
            ->whereHas('student', fn ($query) => $query->whereIn('class_id', $classIds)->where('status', 'active'))
            ->orderByDesc('development_date')
            ->paginate(15);

        return view('guru.developments', [
            'classes' => $classes,
            'studentsByClass' => $classIds
                ? Student::query()->whereIn('class_id', $classIds)->where('status', 'active')->orderBy('full_name')->get(['id', 'class_id', 'full_name'])->groupBy('class_id')
                : collect(),
            'developments' => $developments,
        ]);
    }

    public function store(Request $request, GuruClassAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
            'development_date' => ['required', 'date', 'before_or_equal:today'],
            'assessment' => ['required', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        $access->studentsForClass($request->user(), (int) $data['class_id'])->findOrFail($data['student_id']);
        $category = DevelopmentCategory::query()->firstOrCreate(
            ['name' => 'Observasi Kemajuan'],
            ['description' => 'Catatan observasi kemajuan peserta berdasarkan modul guru.', 'is_active' => true],
        );

        DB::transaction(fn () => Development::create([
            ...$data,
            'category_id' => $category->id,
            'recorded_by' => $request->user()->id,
        ]));

        return redirect()->route('guru.developments')->with('success', 'Observasi perkembangan berhasil disimpan.');
    }

    public function update(Request $request, Development $development, GuruClassAccess $access): RedirectResponse
    {
        $this->developmentsFor($request, $access)->findOrFail($development->id);
        $data = $request->validate([
            'class_id' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
            'development_date' => ['required', 'date', 'before_or_equal:today'],
            'assessment' => ['required', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);
        $access->studentsForClass($request->user(), (int) $data['class_id'])->findOrFail($data['student_id']);
        $category = DevelopmentCategory::query()->firstOrCreate(
            ['name' => 'Observasi Kemajuan'],
            ['description' => 'Catatan observasi kemajuan peserta berdasarkan modul guru.', 'is_active' => true],
        );
        $development->update([
            ...$data,
            'category_id' => $category->id,
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('guru.developments')->with('success', 'Observasi perkembangan berhasil diperbarui.');
    }

    public function destroy(Request $request, Development $development, GuruClassAccess $access): RedirectResponse
    {
        $this->developmentsFor($request, $access)->findOrFail($development->id)->delete();

        return redirect()->route('guru.developments')->with('success', 'Observasi perkembangan berhasil dihapus.');
    }

    /**
     * @return Builder<Development>
     */
    private function developmentsFor(Request $request, GuruClassAccess $access): Builder
    {
        return Development::query()->whereHas('student', function (Builder $query) use ($request, $access): void {
            $query->whereIn('class_id', $access->classesFor($request->user())->select('classes.id'));
        });
    }
}
