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
        Schema::create('maintenance_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained('maintenances')->onDelete('cascade');
            $table->string('image_path');
            $table->string('image_type')->default('equipo'); // equipo, antes, despues, formato
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->unsignedBigInteger('created_by_user')->nullable();

            $table->foreign('created_by_user')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_images');
    }
};
