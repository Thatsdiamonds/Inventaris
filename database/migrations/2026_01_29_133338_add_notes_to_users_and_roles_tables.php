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
        if (!Schema::hasColumn('users', 'notes')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('password');
            });
        }

        if (!Schema::hasColumn('roles', 'notes')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'notes')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }

        if (Schema::hasColumn('roles', 'notes')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }
};
