<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_plans', function (Blueprint $table): void {
            $table->dropForeign(['class_id']);
            $table->foreignId('class_id')->nullable()->change();
            $table->foreign('class_id')->references('id')->on('classes')->restrictOnDelete();
            $table->string('plan_type', 20)->default('class');
            $table->unsignedTinyInteger('reminder_days')->default(1);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->index(['plan_type', 'execution_date', 'reminder_sent_at'], 'lesson_plan_reminders_index');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_plans', function (Blueprint $table): void {
            $table->dropIndex('lesson_plan_reminders_index');
            $table->dropColumn(['plan_type', 'reminder_days', 'reminder_sent_at']);
            $table->dropForeign(['class_id']);
            $table->foreignId('class_id')->nullable(false)->change();
            $table->foreign('class_id')->references('id')->on('classes')->restrictOnDelete();
        });
    }
};
