<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_download_requests', function (Blueprint $table): void {
            $table->string('requested_action', 20)->default('download')->after('requester_email');
        });

        DB::table('learning_materials')->insertOrIgnore([
            'slug' => 'modul-buku-3',
            'type' => 'lesson',
            'title' => 'Modul Buku 3',
            'description' => 'Modul pembelajaran Buku 3. Akses membaca atau mengunduh memerlukan persetujuan Admin.',
            'is_system' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('learning_materials')
            ->where('slug', 'modul-buku-3')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('public_download_requests')
                    ->whereColumn('public_download_requests.learning_material_id', 'learning_materials.id');
            })
            ->delete();

        Schema::table('public_download_requests', function (Blueprint $table): void {
            $table->dropColumn('requested_action');
        });
    }
};
