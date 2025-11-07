<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create consultation types table
        Schema::create('t_consultation_types', function (Blueprint $table) {
            $table->string('Consult_type_ID')->primary();
            $table->string('Consult_Type');
            $table->timestamps();
        });

        // Create professors table
        Schema::create('professors', function (Blueprint $table) {
            $table->string('Prof_ID')->primary();
            $table->string('Name');
            $table->string('Email')->unique();
            $table->string('Password');
            $table->string('Schedule')->nullable();
            $table->string('Dept_ID')->nullable();
            $table->string('profile_picture')->nullable();
            $table->rememberToken();
            $table->boolean('is_active')->default(true);
        });

        // Create students table
        Schema::create('t_student', function (Blueprint $table) {
            $table->string('Stud_ID')->primary();
            $table->string('Name');
            $table->string('Email')->unique();
            $table->string('Password');
            $table->string('Dept_ID');
            $table->string('profile_picture')->nullable();
            $table->rememberToken();
            $table->boolean('is_active')->default(true);
        });

        // Create subjects table
        Schema::create('t_subject', function (Blueprint $table) {
            $table->string('Subject_ID')->primary();
            $table->string('subject_code')->unique();
            $table->string('subject_name');
            $table->integer('Units');
            $table->string('Dept_ID');
        });

        // Create consultation bookings table
        Schema::create('t_consultation_bookings', function (Blueprint $table) {
            $table->string('Booking_ID')->primary();
            $table->string('Student_ID');
            $table->string('Prof_ID');
            $table->string('Subject_ID');
            $table->string('Consult_type_ID');
            $table->string('Custom_Type')->nullable();
            $table->string('Booking_Date');
            $table->string('Mode');
            $table->string('Status')->default('pending');
            $table->text('reschedule_reason')->nullable();
            $table->timestamp('Created_At')->useCurrent();
            $table->timestamp('Updated_At')->useCurrent();

            $table->foreign('Student_ID')->references('Stud_ID')->on('t_student');
            $table->foreign('Prof_ID')->references('Prof_ID')->on('professors');
            $table->foreign('Subject_ID')->references('Subject_ID')->on('t_subject');
            $table->foreign('Consult_type_ID')->references('Consult_type_ID')->on('t_consultation_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_consultation_bookings');
        Schema::dropIfExists('t_subject');
        Schema::dropIfExists('t_student');
        Schema::dropIfExists('professors');
        Schema::dropIfExists('t_consultation_types');
    }
}