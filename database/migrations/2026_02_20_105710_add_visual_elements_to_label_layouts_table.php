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
        Schema::table('label_layouts', function (Blueprint $table) {
            $table->json('visual_elements')->nullable()->after('font_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('label_layouts', function (Blueprint $table) {
            $table->dropColumn('visual_elements');
        });
    }
};
