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
            $table->longText('custom_css')->nullable()->after('visual_elements');
            $table->longText('custom_html')->nullable()->after('custom_css');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('label_layouts', function (Blueprint $table) {
            $table->dropColumn(['custom_css', 'custom_html']);
        });
    }
};
