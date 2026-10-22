<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coin_transactions', function (Blueprint $table) {
            $table->enum('type', ['reward', 'purchase', 'sale', 'adjustment', 'site_sale'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('coin_transactions', function (Blueprint $table) {
            $table->enum('type', ['reward', 'purchase', 'sale', 'adjustment'])->change();
        });
    }
};
