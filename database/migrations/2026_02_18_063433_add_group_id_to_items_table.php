<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('id');
            $table->foreign('group_id')->references('id')->on('item_types')->nullOnDelete();
        });

        // Backfill: link existing items to their item_type group by name match
        DB::statement('
            UPDATE items
            SET group_id = (
                SELECT id FROM item_types WHERE item_types.name = items.name LIMIT 1
            )
            WHERE group_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
    }
};
