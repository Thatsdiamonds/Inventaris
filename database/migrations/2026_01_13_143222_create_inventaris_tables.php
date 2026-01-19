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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_gereja');
            $table->string('alamat')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('uqcode')->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->enum('condition', ['baik', 'rusak', 'perbaikan', 'dimusnahkan']);
            $table->string('photo_path')->nullable();
            $table->integer('service_interval_days');
            $table->boolean('service_required')->default(false);
            $table->date('last_service_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('vendor');
            $table->date('date_in');
            $table->date('date_out')->nullable();
            $table->text('description')->nullable();
            $table->integer('cost')->nullable();
            $table->timestamps();
        });

        Schema::create('disposal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->text('reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposal');
        Schema::dropIfExists('services');
        Schema::dropIfExists('items');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('settings');
    }
};
