<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploader_id')->constrained('users');
            $table->foreignId('owner_id')->constrained('users');
            $table->string('title', 100)->nullable();
            $table->string('file_path', 255);
            $table->unsignedBigInteger('click_count')->default(0);
            $table->unsignedBigInteger('value')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};