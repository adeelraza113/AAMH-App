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
        Schema::create('membership_cards', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("cnic");
            $table->string("dob");
            $table->string("phone");
            $table->string("email");
            $table->string("membership_type");
            $table->string("address");
            $table->string("no_of_members");
            $table->string("preferred_hospital");
            $table->string("emergency_name");
            $table->string("emergency_contact");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_cards');
    }
};
