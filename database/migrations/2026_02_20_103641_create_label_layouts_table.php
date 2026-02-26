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
        Schema::create('label_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('width', 8, 2)->default(100.00); // mm
            $table->decimal('height', 8, 2)->default(30.00); // mm
            $table->string('paper_size')->default('A4'); // A4, Letter, Custom
            $table->decimal('margin_top', 8, 2)->default(0.00); // mm
            $table->decimal('margin_bottom', 8, 2)->default(0.00);
            $table->decimal('margin_left', 8, 2)->default(0.00);
            $table->decimal('margin_right', 8, 2)->default(0.00);
            $table->decimal('gap_x', 8, 2)->default(0.00); // mm
            $table->decimal('gap_y', 8, 2)->default(0.00); // mm
            $table->integer('font_size')->default(12); // pt
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('label_layouts');
    }
};
