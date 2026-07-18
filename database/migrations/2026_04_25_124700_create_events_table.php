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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('location');
            $table->string('excerpt', 180)->nullable();
            $table->text('description');
            $table->timestamp('starts_at');
            $table->decimal('budget_allocated', 12, 2)->default(0);
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('participants_count')->default(0);
            $table->string('cover_image_path')->nullable();
            $table->boolean('registration_enabled')->default(true);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
