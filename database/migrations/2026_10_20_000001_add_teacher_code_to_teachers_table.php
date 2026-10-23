<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('teachers', 'teacher_code')) {
            Schema::table('teachers', function (Blueprint $table): void {
                $table->string('teacher_code')->nullable()->unique()->after('status');
            });
        }

        DB::table('teachers')->whereNull('teacher_code')->orderBy('id')->chunkById(500, function ($teachers): void {
            foreach ($teachers as $teacher) {
                DB::table('teachers')
                    ->where('id', $teacher->id)
                    ->update(['teacher_code' => 'TCH-'.str_pad((string) $teacher->id, 8, '0', STR_PAD_LEFT)]);
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('teachers', 'teacher_code')) {
            Schema::table('teachers', function (Blueprint $table): void {
                $table->dropUnique(['teacher_code']);
                $table->dropColumn('teacher_code');
            });
        }
    }
};
