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
        Schema::table('items', function (Blueprint $table) {
            // Indexing generic columns used in WHERE clauses
            $table->index('is_active');
            $table->index('condition');
            
            // Indexing for sorting
            $table->index('created_at');
            $table->index('name'); // Also used in LIKE search (prefix only)

            // Indexing Service Dates for date range queries
            $table->index('acquisition_date');
            $table->index('last_service_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['condition']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['name']);
            $table->dropIndex(['acquisition_date']);
            $table->dropIndex(['last_service_date']);
        });
    }
};
