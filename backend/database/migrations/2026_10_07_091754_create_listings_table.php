<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_id')->constrained('images');
            $table->foreignId('seller_id')->constrained('users');
            $table->foreignId('buyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('price')->nullable();
            $table->boolean('coin_purchase_available')->default(true);
            $table->boolean('trade_available')->default(false);
            $table->enum('status', ['active', 'sold', 'traded', 'cancelled'])->default('active');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};