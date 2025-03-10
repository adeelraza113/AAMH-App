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
        Schema::create('tblSubCategory', function (Blueprint $table) {
            $table->id('SubCategoryID');
            $table->string('SubCategoryName');
            $table->unsignedBigInteger('CategoryID');
            $table->foreign('CategoryID')->references('CategoryID')->on('tblCategory')->onDelete('cascade');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblSubCategory');
    }
};
