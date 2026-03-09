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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('role_id');
            $table->integer('department_id');
            $table->string('first_name');
            $table->string('surname');
            $table->string('email');
            $table->string('phone');
            $table->string('password');
            $table->string('dob');
            $table->integer('age');
            $table->string('gender');
            $table->string('image');
            $table->string('address');
            $table->string('qualification');
            $table->string('profession');
            $table->string('primary_contact_name');
            $table->string('relationship');
            $table->string('check1');
            $table->string('check2');
            $table->string('check3');
            $table->string('check4');
            $table->string('check5');
            $table->string('verify');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
