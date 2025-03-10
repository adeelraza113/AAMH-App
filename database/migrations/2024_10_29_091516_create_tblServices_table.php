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
        Schema::create('tblServices', function (Blueprint $table) {
            $table->id('ServiceId');
            $table->string('ServiceName');
            $table->unsignedBigInteger('SectionId');
            $table->timestamps();
            $table->foreign('SectionId')->references('SectionId')->on('tblSections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblServices');
    }
};
