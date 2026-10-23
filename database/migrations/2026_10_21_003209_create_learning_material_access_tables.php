<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_materials', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('type', 30);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->text('external_url')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['is_active', 'type']);
        });

        Schema::create('public_download_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_material_id')->constrained()->restrictOnDelete();
            $table->string('requester_name');
            $table->string('requester_email');
            $table->string('access_token_hash', 64)->unique();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('app_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->foreignId('teacher_reflection_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('public_download_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at', 'created_at']);
        });

        $now = now();
        $programGradeId = DB::table('grades')->where('name', 'Program 24 Lesson')->value('id');
        if ($programGradeId === null) {
            $programGradeId = DB::table('grades')->insertGetId([
                'name' => 'Program 24 Lesson',
                'description' => 'Daftar lesson program kelas anak',
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (range(1, 24) as $lessonNumber) {
            DB::table('curriculum_lessons')->insertOrIgnore([
                'grade_id' => $programGradeId,
                'lesson_number' => $lessonNumber,
                'title' => 'Lesson '.str_pad((string) $lessonNumber, 2, '0', STR_PAD_LEFT),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('learning_materials')->insertOrIgnore([
            [
                'slug' => '24-lesson',
                'type' => 'lesson',
                'title' => 'Dokumentasi 24 Lesson',
                'description' => 'Dokumen pelajaran berisi 24 lesson yang dapat dibaca dan dicetak.',
                'external_url' => null,
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'draft-lagu',
                'type' => 'song',
                'title' => 'Draft Lagu Anak',
                'description' => 'Folder lagu anak di Google Drive.',
                'external_url' => 'https://drive.google.com/drive/folders/169LSwexNqc9muvauKcBWUZMVEtVRsqcg',
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('public_download_requests');
        Schema::dropIfExists('learning_materials');
    }
};
