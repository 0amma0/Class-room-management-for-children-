<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->unique('code');
        });

        Schema::table('clusters', function (Blueprint $table) {
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->unique(['region_id', 'code']);
        });

        Schema::table('environments', function (Blueprint $table) {
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->unique(['cluster_id', 'code']);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->text('address')->nullable();
            $table->string('teacher_code')->nullable()->unique();
        });

        DB::table('teachers')->whereNull('teacher_code')->orderBy('id')->chunkById(500, function ($teachers): void {
            foreach ($teachers as $teacher) {
                DB::table('teachers')
                    ->where('id', $teacher->id)
                    ->update(['teacher_code' => 'TCH-'.str_pad((string) $teacher->id, 8, '0', STR_PAD_LEFT)]);
            }
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('grade_id')->references('id')->on('grades')->restrictOnDelete();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('student_code')->nullable()->unique();
        });

        DB::table('students')->whereNull('student_code')->orderBy('id')->chunkById(500, function ($students): void {
            foreach ($students as $student) {
                DB::table('students')
                    ->where('id', $student->id)
                    ->update(['student_code' => 'STU-'.str_pad((string) $student->id, 8, '0', STR_PAD_LEFT)]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('teacher_id');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->index(['status', 'activity_date']);
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('class_id')->constrained('classes')->restrictOnDelete();
            $table->date('attendance_date');
            $table->string('status');
            $table->text('note')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'attendance_date']);
            $table->index(['class_id', 'attendance_date']);
        });

        Schema::create('development_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('developments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('development_categories')->restrictOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('development_date');
            $table->string('assessment');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'development_date']);
            $table->index(['category_id', 'assessment']);
        });

        Schema::create('activity_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->restrictOnDelete();
            $table->string('file_url');
            $table->string('caption')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['activity_id', 'is_approved']);
        });

        Schema::create('parent_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('parent_name');
            $table->date('visit_date');
            $table->string('purpose');
            $table->text('discussion_result')->nullable();
            $table->text('note')->nullable();
            $table->text('follow_up')->nullable();
            $table->timestamps();
            $table->index(['class_id', 'visit_date']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['entity_type', 'entity_id']);
            $table->index(['user_id', 'created_at']);
        });

        $this->createActivityScopeTriggers();
    }

    public function down(): void
    {
        $this->dropActivityScopeTriggers();

        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('parent_visits');
        Schema::dropIfExists('activity_photos');
        Schema::dropIfExists('developments');
        Schema::dropIfExists('development_categories');
        Schema::dropIfExists('attendances');

        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['status', 'activity_date']);
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn('reviewed_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['teacher_id']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['student_code']);
            $table->dropColumn('student_code');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['grade_id']);
        });

        Schema::dropIfExists('grades');

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique(['teacher_code']);
            $table->dropColumn(['address', 'teacher_code']);
        });

        Schema::table('environments', function (Blueprint $table) {
            $table->dropUnique(['cluster_id', 'code']);
            $table->dropColumn(['code', 'description']);
        });

        Schema::table('clusters', function (Blueprint $table) {
            $table->dropUnique(['region_id', 'code']);
            $table->dropColumn(['code', 'description']);
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('description');
        });
    }

    private function createActivityScopeTriggers(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            foreach (['INSERT', 'UPDATE'] as $operation) {
                $trigger = 'activities_scope_'.strtolower($operation);
                DB::unprepared(<<<SQL
                    CREATE TRIGGER {$trigger}
                    BEFORE {$operation} ON activities
                    FOR EACH ROW
                    WHEN NOT (
                        (NEW.scope_type = 'class' AND NEW.class_id IS NOT NULL AND NEW.environment_id IS NULL AND NEW.cluster_id IS NULL)
                        OR (NEW.scope_type = 'environment' AND NEW.environment_id IS NOT NULL AND NEW.class_id IS NULL AND NEW.cluster_id IS NULL)
                        OR (NEW.scope_type = 'cluster' AND NEW.cluster_id IS NOT NULL AND NEW.class_id IS NULL AND NEW.environment_id IS NULL)
                    )
                    BEGIN
                        SELECT RAISE(ABORT, 'Invalid activity scope');
                    END;
                    SQL);
            }

            return;
        }

        if (DB::getDriverName() === 'mysql') {
            foreach (['INSERT', 'UPDATE'] as $operation) {
                $trigger = 'activities_scope_'.strtolower($operation);
                DB::unprepared(<<<SQL
                    CREATE TRIGGER {$trigger}
                    BEFORE {$operation} ON activities
                    FOR EACH ROW
                    BEGIN
                        IF NOT (
                            (NEW.scope_type = 'class' AND NEW.class_id IS NOT NULL AND NEW.environment_id IS NULL AND NEW.cluster_id IS NULL)
                            OR (NEW.scope_type = 'environment' AND NEW.environment_id IS NOT NULL AND NEW.class_id IS NULL AND NEW.cluster_id IS NULL)
                            OR (NEW.scope_type = 'cluster' AND NEW.cluster_id IS NOT NULL AND NEW.class_id IS NULL AND NEW.environment_id IS NULL)
                        ) THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid activity scope';
                        END IF;
                    END
                    SQL);
            }

            return;
        }

        throw new RuntimeException('Activity scope triggers are not implemented for this database driver.');
    }

    private function dropActivityScopeTriggers(): void
    {
        foreach (['insert', 'update'] as $operation) {
            $trigger = 'activities_scope_'.$operation;
            DB::unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }
};
