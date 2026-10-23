<?php

namespace App\Console\Commands;

use App\Mail\LessonPlanReminder;
use App\Models\LessonPlan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

#[Signature('app:lesson-plan-reminders')]
#[Description('Send configured email reminders for upcoming lesson plans')]
class SendLessonPlanReminders extends Command
{
    public function handle(): int
    {
        $today = now('Asia/Jakarta')->startOfDay();
        $candidateDates = [
            $today->copy()->addDay()->toDateString(),
            $today->copy()->addDays(2)->toDateString(),
        ];
        $plans = LessonPlan::query()
            ->whereNotNull('created_by')
            ->whereNull('reminder_sent_at')
            ->where(function (Builder $query) use ($candidateDates): void {
                $query->whereDate('execution_date', $candidateDates[0])
                    ->orWhereDate('execution_date', $candidateDates[1]);
            })
            ->with(['creator', 'class'])
            ->orderBy('id')
            ->get();
        $sent = 0;

        foreach ($plans as $plan) {
            if (! $plan->execution_date->isSameDay($today->copy()->addDays($plan->reminder_days))) {
                continue;
            }

            Mail::to($plan->creator->email)->send(new LessonPlanReminder($plan));
            $plan->update(['reminder_sent_at' => now()]);
            $sent++;
        }

        $this->info("Sent {$sent} lesson plan reminder(s).");

        return self::SUCCESS;
    }
}
