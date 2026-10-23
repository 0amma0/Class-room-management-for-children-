<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_gallery_photos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('photo_date');
            $table->string('session_label', 100)->nullable();
            $table->string('caption', 255)->nullable();
            $table->string('file_path');
            $table->timestamps();
            $table->index(['class_id', 'photo_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_gallery_photos');
    }
};
