<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grades')->restrictOnDelete();
            $table->unsignedInteger('lesson_number');
            $table->string('title');
            $table->timestamps();
            $table->unique(['grade_id', 'lesson_number']);
        });

        Schema::create('lesson_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_lesson_id')->constrained('curriculum_lessons')->restrictOnDelete();
            $table->string('section_code', 1);
            $table->string('heading')->nullable();
            $table->text('intro_text')->nullable();
            $table->boolean('is_included')->default(true);
            $table->timestamps();
            $table->unique(['curriculum_lesson_id', 'section_code']);
        });

        Schema::create('prayers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->nullable();
            $table->longText('content');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('audio_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->longText('content');
            $table->string('category')->nullable();
            $table->unsignedInteger('reference_no')->nullable();
            $table->string('source_citation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('body');
            $table->timestamps();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('instructions');
            $table->timestamps();
        });

        Schema::create('coloring_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_lesson_id')->constrained('curriculum_lessons')->restrictOnDelete();
            $table->unsignedInteger('drawing_number');
            $table->string('image_url');
            $table->timestamps();
            $table->unique(['curriculum_lesson_id', 'drawing_number']);
        });

        Schema::create('lesson_prayers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_lesson_id')->constrained('curriculum_lessons')->restrictOnDelete();
            $table->foreignId('prayer_id')->constrained('prayers')->restrictOnDelete();
            $table->text('note')->nullable();
            $table->unique(['curriculum_lesson_id', 'prayer_id']);
        });

        Schema::create('lesson_songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_lesson_id')->constrained('curriculum_lessons')->restrictOnDelete();
            $table->foreignId('song_id')->constrained('songs')->restrictOnDelete();
            $table->text('note')->nullable();
            $table->unique(['curriculum_lesson_id', 'song_id']);
        });

        Schema::create('lesson_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_lesson_id')->constrained('curriculum_lessons')->restrictOnDelete();
            $table->foreignId('quote_id')->constrained('quotes')->restrictOnDelete();
            $table->longText('vocabulary_notes')->nullable();
            $table->unique(['curriculum_lesson_id', 'quote_id']);
        });

        Schema::create('lesson_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_lesson_id')->constrained('curriculum_lessons')->restrictOnDelete();
            $table->foreignId('story_id')->constrained('stories')->restrictOnDelete();
            $table->unique(['curriculum_lesson_id', 'story_id']);
        });

        Schema::create('lesson_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_lesson_id')->constrained('curriculum_lessons')->restrictOnDelete();
            $table->foreignId('game_id')->constrained('games')->restrictOnDelete();
            $table->unique(['curriculum_lesson_id', 'game_id']);
        });

        Schema::create('lesson_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_lesson_id')->constrained('curriculum_lessons')->restrictOnDelete();
            $table->string('title');
            $table->string('file_url');
            $table->string('file_type');
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('class_curriculum_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('curriculum_lesson_id')->constrained('curriculum_lessons')->restrictOnDelete();
            $table->string('status')->default('belum');
            $table->date('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['class_id', 'curriculum_lesson_id']);
        });

        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('curriculum_lesson_id')->constrained('curriculum_lessons')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('execution_date')->nullable();
            $table->string('status')->default('belum_dimulai');
            $table->text('teacher_note')->nullable();
            $table->text('closing_note')->nullable();
            $table->timestamps();
            $table->index(['class_id', 'execution_date']);
        });

        Schema::create('student_quote_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('quote_id')->constrained('quotes')->restrictOnDelete();
            $table->string('status')->default('belum');
            $table->date('completed_date')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'quote_id']);
        });

        Schema::create('teacher_reflections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->foreignId('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('lesson_plan_id')->nullable()->constrained('lesson_plans')->nullOnDelete();
            $table->date('reflection_date');
            $table->text('what_went_well')->nullable();
            $table->text('challenges')->nullable();
            $table->text('improvement')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_sharing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->string('cover_image')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('parent_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('questioner_name');
            $table->string('questioner_contact')->nullable();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('menunggu');
            $table->boolean('is_public')->default(false);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->string('image_url')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('link_url')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
        Schema::dropIfExists('information');
        Schema::dropIfExists('parent_questions');
        Schema::dropIfExists('knowledge_sharing');
        Schema::dropIfExists('teacher_reflections');
        Schema::dropIfExists('student_quote_progress');
        Schema::dropIfExists('lesson_plans');
        Schema::dropIfExists('class_curriculum_progress');
        Schema::dropIfExists('lesson_media');
        Schema::dropIfExists('lesson_games');
        Schema::dropIfExists('lesson_stories');
        Schema::dropIfExists('lesson_quotes');
        Schema::dropIfExists('lesson_songs');
        Schema::dropIfExists('lesson_prayers');
        Schema::dropIfExists('coloring_pages');
        Schema::dropIfExists('games');
        Schema::dropIfExists('stories');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('songs');
        Schema::dropIfExists('prayers');
        Schema::dropIfExists('lesson_sections');
        Schema::dropIfExists('curriculum_lessons');
    }
};
