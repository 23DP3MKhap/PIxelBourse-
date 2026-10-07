<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coin_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('listing_id')->nullable()->constrained('listings')->nullOnDelete();
            $table->enum('type', ['reward', 'purchase', 'sale', 'adjustment']);
            $table->bigInteger('amount'); 
            $table->unsignedBigInteger('fee')->default(0);
            $table->timestamp('created_at')->useCurrent(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coin_transactions');
    }
};