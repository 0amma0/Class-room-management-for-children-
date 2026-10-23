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
        Schema::table('stories', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('illustration_path')->nullable();
            $table->string('illustration_model')->nullable();
            $table->timestamp('illustration_generated_at')->nullable();
            $table->index(['user_id', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table): void {
            $table->dropIndex('stories_user_id_updated_at_index');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'illustration_path',
                'illustration_model',
                'illustration_generated_at',
            ]);
        });
    }
};
