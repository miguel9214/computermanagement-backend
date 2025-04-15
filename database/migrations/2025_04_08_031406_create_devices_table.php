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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');
            $table->string('property')->nullable();
            $table->string('status')->nullable();
            $table->string('os')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('cpu')->nullable();
            $table->string('office_package')->nullable();
            $table->string('asset_tag')->nullable();
            $table->string('printer_asset')->nullable();
            $table->string('scanner_asset')->nullable();
            $table->string('ram')->nullable();
            $table->string('hdd')->nullable();
            $table->string('ip')->nullable();
            $table->string('mac')->nullable();
            $table->string('serial')->nullable();
            $table->string('anydesk')->nullable();
            $table->string('operator')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('dependency_id')->constrained('dependencies')->onDelete('cascade');
            $table->foreignId('printer_id')->nullable()->constrained('printers')->onDelete('set null');
            $table->foreignId('scanner_id')->nullable()->constrained('scanners')->onDelete('set null');
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
        Schema::dropIfExists('devices');
    }
};
