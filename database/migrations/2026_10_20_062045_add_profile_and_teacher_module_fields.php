<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone')->nullable();
            $table->string('avatar_path')->nullable();
        });

        Schema::table('classes', function (Blueprint $table): void {
            $table->string('photo_path')->nullable();
        });

        Schema::table('teacher_reflections', function (Blueprint $table): void {
            $table->text('objectives_met')->nullable();
            $table->text('plan_alignment')->nullable();
        });

        Schema::table('attendances', function (Blueprint $table): void {
            $table->unsignedTinyInteger('session_number')->nullable();
        });

        Schema::table('lesson_plans', function (Blueprint $table): void {
            $table->dropForeign(['curriculum_lesson_id']);
            $table->foreignId('curriculum_lesson_id')->nullable()->change();
            $table->foreign('curriculum_lesson_id')
                ->references('id')
                ->on('curriculum_lessons')
                ->restrictOnDelete();
            $table->string('level')->nullable();
            $table->unsignedInteger('student_count')->default(0);
            $table->string('theme')->nullable();
            $table->text('objectives')->nullable();
            $table->json('program')->nullable();
            $table->json('materials')->nullable();
            $table->text('catch_up_notes')->nullable();
        });

        Schema::create('public_activity_cards', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_activity_cards');

        Schema::table('lesson_plans', function (Blueprint $table): void {
            $table->dropForeign(['curriculum_lesson_id']);
            $table->dropColumn([
                'level',
                'student_count',
                'theme',
                'objectives',
                'program',
                'materials',
                'catch_up_notes',
            ]);
            $table->foreignId('curriculum_lesson_id')->nullable(false)->change();
            $table->foreign('curriculum_lesson_id')
                ->references('id')
                ->on('curriculum_lessons')
                ->restrictOnDelete();
        });

        Schema::table('classes', function (Blueprint $table): void {
            $table->dropColumn('photo_path');
        });

        Schema::table('teacher_reflections', function (Blueprint $table): void {
            $table->dropColumn(['objectives_met', 'plan_alignment']);
        });

        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropColumn('session_number');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['phone', 'avatar_path']);
        });
    }
};
