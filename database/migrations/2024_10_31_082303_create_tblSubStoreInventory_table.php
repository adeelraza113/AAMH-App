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
        Schema::create('tblSubStoreInventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('PID'); 
            $table->integer('Qty');
            $table->integer('SectionID');
            $table->foreign('PID')->references('ProductID')->on('tblChartOfItems')->onDelete('cascade');
            $table->timestamps();
        });
        
    }

    
    
    
    
    


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblSubStoreInventory');
    }
};
