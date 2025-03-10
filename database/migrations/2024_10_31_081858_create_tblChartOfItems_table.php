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
        Schema::create('tblChartOfItems', function (Blueprint $table) {
            $table->id('ProductID');
            $table->string('ProductName');
            $table->string('Salt')->nullable();
            $table->integer('Pieces');
            $table->decimal('PurchasePrice', 18, 2);
            $table->decimal('SalePrice', 18, 2);
            $table->decimal('Discount', 18, 2)->nullable();
            $table->string('Remarks')->nullable();
            $table->string('Status')->nullable();
            $table->string('Lock')->nullable();
            $table->boolean('up');
            $table->unsignedBigInteger('BrandID')->nullable();
            $table->unsignedBigInteger('CategoryID')->nullable();
            $table->unsignedBigInteger('SubCategoryID')->nullable();
            $table->foreign('BrandID')->references('BrandId')->on('tblBrands')->onDelete('cascade');
            $table->foreign('CategoryID')->references('CategoryID')->on('tblCategory')->onDelete('cascade');
            $table->foreign('SubCategoryID')->references('SubCategoryID')->on('tblSubCategory')->onDelete('cascade');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblChartOfItems');
    }
};
