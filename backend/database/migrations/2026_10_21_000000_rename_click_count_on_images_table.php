<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('images', 'click_count')) {
            Schema::table('images', function (Blueprint $table) {
                $table->renameColumn('click_count', 'clicks_count');
            });
        }
    }

    public function down(): void {}
};
