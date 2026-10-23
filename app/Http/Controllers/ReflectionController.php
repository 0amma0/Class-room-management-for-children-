<?php

namespace App\Http\Controllers;

use App\Models\TeacherReflection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReflectionController extends Controller
{
    public function show(Request $request, TeacherReflection $reflection): View
    {
        abort_unless($request->user()->isAdmin() || $request->user()->isGuru(), 403);

        $request->user()->appNotifications()
            ->where('teacher_reflection_id', $reflection->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $reflection->load(['teacher:id,full_name,nickname', 'class:id,name', 'lessonPlan:id,theme']);

        return view('reflections.show', compact('reflection'));
    }
}
