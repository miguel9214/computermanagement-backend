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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('maintainable_type'); // Device, Printer, Scanner
            $table->unsignedBigInteger('maintainable_id');
            $table->date('maintenance_date');
            $table->date('next_maintenance_date')->nullable();
            $table->string('maintenance_type'); // preventivo, correctivo, limpieza
            $table->text('description')->nullable();
            $table->text('performed_tasks')->nullable();
            $table->string('technician')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('status')->default('completado'); // programado, en_proceso, completado, cancelado
            $table->text('notes')->nullable();
            $table->string('physical_format_path')->nullable(); // Ruta del archivo PDF/imagen del formato físico
            $table->unsignedBigInteger('created_by_user')->nullable();
            $table->unsignedBigInteger('updated_by_user')->nullable();

            $table->index(['maintainable_type', 'maintainable_id']);
            $table->foreign('created_by_user')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('updated_by_user')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
