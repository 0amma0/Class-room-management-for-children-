<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_contacts', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 30)->unique();
            $table->string('name', 100);
            $table->string('phone', 30);
            $table->string('avatar_type', 30)->default('bangs');
            $table->timestamps();
        });

        $timestamp = now();
        DB::table('public_contacts')->insert([
            [
                'slug' => 'sarir',
                'name' => 'Sarir Imani',
                'phone' => '+62 813-1888-9341',
                'avatar_type' => 'afro',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'slug' => 'najmi',
                'name' => 'Najmi Rahmani',
                'phone' => '+62 896-2365-1159',
                'avatar_type' => 'bangs',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('public_contacts');
    }
};
