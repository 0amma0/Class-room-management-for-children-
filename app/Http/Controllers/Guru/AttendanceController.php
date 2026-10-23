<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Support\GuruClassAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request, GuruClassAccess $access): View
    {
        $classes = $access->classesFor($request->user())->orderBy('name')->get(['id', 'name']);
        $classId = (int) $request->query('class_id', $classes->first()?->id);
        $students = $classId ? $access->studentsForClass($request->user(), $classId)->orderBy('full_name')->get() : collect();
        $attendanceDate = $request->query('attendance_date', now()->toDateString());
        $sessionNumber = (int) $request->query('session_number', 1);
        $records = $classId
            ? Attendance::query()->where('class_id', $classId)
                ->whereDate('attendance_date', $attendanceDate)
                ->where('session_number', $sessionNumber)
                ->get()
                ->keyBy('student_id')
            : collect();

        return view('guru.attendances', compact('classes', 'classId', 'students', 'attendanceDate', 'sessionNumber', 'records'));
    }

    public function store(Request $request, GuruClassAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'integer'],
            'attendance_date' => ['required', 'date', 'before_or_equal:today'],
            'session_number' => ['required', 'integer', 'between:1,24'],
            'attendance' => ['required', 'array', 'min:1'],
            'attendance.*.status' => ['required', Rule::in(['present', 'absent', 'sick', 'permission'])],
            'attendance.*.note' => ['nullable', 'string', 'max:2000'],
        ]);
        $students = $access->studentsForClass($request->user(), (int) $data['class_id'])
            ->whereIn('id', array_keys($data['attendance']))
            ->get(['id']);

        abort_unless($students->count() === count($data['attendance']), 422, 'Daftar peserta tidak cocok dengan kelas yang dipilih.');

        DB::transaction(function () use ($data, $students, $request): void {
            foreach ($students as $student) {
                $entry = $data['attendance'][$student->id];
                Attendance::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'attendance_date' => $data['attendance_date'],
                    ],
                    [
                        'class_id' => $data['class_id'],
                        'session_number' => $data['session_number'],
                        'status' => $entry['status'],
                        'note' => $entry['note'] ?? null,
                        'recorded_by' => $request->user()->id,
                    ],
                );
            }
        });

        return redirect()->route('guru.attendances', [
            'class_id' => $data['class_id'],
            'attendance_date' => $data['attendance_date'],
            'session_number' => $data['session_number'],
        ])->with('success', 'Kehadiran berhasil disimpan.');
    }

    public function destroy(Request $request, Attendance $attendance, GuruClassAccess $access): RedirectResponse
    {
        $access->studentsForClass($request->user(), $attendance->class_id)->findOrFail($attendance->student_id);
        $attendance->delete();

        return redirect()->route('guru.attendances', [
            'class_id' => $attendance->class_id,
            'attendance_date' => $attendance->attendance_date->toDateString(),
            'session_number' => $attendance->session_number,
        ])->with('success', 'Catatan kehadiran berhasil dihapus.');
    }
}
