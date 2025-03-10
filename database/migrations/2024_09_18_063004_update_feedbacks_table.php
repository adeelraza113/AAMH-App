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
        Schema::table('feedbacks', function (Blueprint $table) {
            //
            $table->boolean('follow_up_permission')->default(false);
            $table->string('overall_satisfaction');
            $table->string('consultation_rating');
            $table->string('quality_of_facilities');
            $table->string('staff_behavior');
            $table->string('empathy_and_respect');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            //
        });
    }
};
