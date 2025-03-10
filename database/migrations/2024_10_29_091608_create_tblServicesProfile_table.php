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
        Schema::create('tblServicesProfile', function (Blueprint $table) {
            $table->id('ServiceProfileID');
            $table->unsignedBigInteger('ServiceID');
            $table->unsignedBigInteger('EmployeeCode');
            $table->decimal('NormalFees', 18, 2);
            $table->decimal('Discount', 18, 2)->nullable();
            $table->text('Description');   
            $table->timestamps();
            $table->foreign('ServiceID')->references('ServiceId')->on('tblServices')->onDelete('cascade');
            $table->foreign('EmployeeCode')->references('EmployeeCode')->on('tblEmployeeSetup')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblServicesProfile');
    }
};
