<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ParentVisit;
use App\Models\Student;
use App\Support\GuruClassAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentVisitController extends Controller
{
    public function index(Request $request, GuruClassAccess $access): View
    {
        $classes = $access->classesFor($request->user())->orderBy('name')->get(['id', 'name']);
        $visits = ParentVisit::query()
            ->whereIn('class_id', $classes->modelKeys())
            ->with(['class:id,name', 'student:id,full_name'])
            ->orderByDesc('visit_date')
            ->paginate(10);

        return view('guru.parent-visits', [
            'classes' => $classes,
            'studentsByClass' => Student::query()
                ->whereIn('class_id', $classes->modelKeys())
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get(['id', 'class_id', 'full_name'])
                ->groupBy('class_id'),
            'visits' => $visits,
        ]);
    }

    public function store(Request $request, GuruClassAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
            'parent_name' => ['required', 'string', 'max:255'],
            'visit_date' => ['required', 'date', 'before_or_equal:today'],
            'purpose' => ['required', 'string', 'max:255'],
            'discussion_result' => ['nullable', 'string', 'max:5000'],
            'note' => ['nullable', 'string', 'max:5000'],
            'follow_up' => ['nullable', 'string', 'max:5000'],
        ]);
        $access->studentsForClass($request->user(), (int) $data['class_id'])->findOrFail($data['student_id']);

        ParentVisit::create([
            ...$data,
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('guru.parent-visits')->with('success', 'Kunjungan keluarga berhasil dicatat.');
    }

    public function update(Request $request, ParentVisit $parentVisit, GuruClassAccess $access): RedirectResponse
    {
        $this->visitsFor($request, $access)->findOrFail($parentVisit->id);
        $data = $this->validatedVisitData($request);
        $access->studentsForClass($request->user(), (int) $data['class_id'])->findOrFail($data['student_id']);
        $parentVisit->update([...$data, 'recorded_by' => $request->user()->id]);

        return redirect()->route('guru.parent-visits')->with('success', 'Kunjungan keluarga berhasil diperbarui.');
    }

    public function destroy(Request $request, ParentVisit $parentVisit, GuruClassAccess $access): RedirectResponse
    {
        $this->visitsFor($request, $access)->findOrFail($parentVisit->id)->delete();

        return redirect()->route('guru.parent-visits')->with('success', 'Catatan kunjungan berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedVisitData(Request $request): array
    {
        return $request->validate([
            'class_id' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
            'parent_name' => ['required', 'string', 'max:255'],
            'visit_date' => ['required', 'date', 'before_or_equal:today'],
            'purpose' => ['required', 'string', 'max:255'],
            'discussion_result' => ['nullable', 'string', 'max:5000'],
            'note' => ['nullable', 'string', 'max:5000'],
            'follow_up' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    /**
     * @return Builder<ParentVisit>
     */
    private function visitsFor(Request $request, GuruClassAccess $access): Builder
    {
        return ParentVisit::query()
            ->whereIn('class_id', $access->classesFor($request->user())->select('classes.id'));
    }
}
