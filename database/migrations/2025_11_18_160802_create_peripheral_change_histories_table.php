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
        Schema::create('peripheral_change_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->date('change_date');
            $table->string('change_type'); // ram, hdd, ssd, teclado, mouse, monitor, impresora, escaner, otro
            $table->string('component_name'); // Nombre del componente
            $table->string('old_value')->nullable(); // Valor anterior (ej: 8GB RAM)
            $table->string('new_value')->nullable(); // Nuevo valor (ej: 16GB RAM)
            $table->text('reason')->nullable(); // Razón del cambio
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('supplier')->nullable(); // Proveedor
            $table->string('technician')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by_user')->nullable();
            $table->unsignedBigInteger('updated_by_user')->nullable();

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
        Schema::dropIfExists('peripheral_change_histories');
    }
};
