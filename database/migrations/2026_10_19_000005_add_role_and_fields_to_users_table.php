<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('id');
            $table->string('role')->default('guru')->after('password'); // admin, guru
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete()->after('role');
            $table->string('status')->default('active')->after('teacher_id'); // active, inactive
            $table->boolean('must_change_password')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn(['username', 'role', 'teacher_id', 'status', 'must_change_password']);
        });
    }
};
