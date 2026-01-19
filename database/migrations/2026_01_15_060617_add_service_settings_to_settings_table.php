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
        Schema::table('settings', function (Blueprint $table) {
            $table->integer('maintenance_threshold')->default(30)->after('logo_path');
            $table->string('currency')->default('IDR')->after('maintenance_threshold');
        });

        // Ensure at least one setting row exists
        if (\App\Models\Setting::count() === 0) {
            \App\Models\Setting::create([
                'nama_gereja' => 'Inventaris Management',
                'maintenance_threshold' => 30,
                'currency' => 'IDR'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['maintenance_threshold', 'currency']);
        });
    }
};
