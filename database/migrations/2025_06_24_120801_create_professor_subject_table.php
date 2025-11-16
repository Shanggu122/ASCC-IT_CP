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
        Schema::create('professor_subject', function (Blueprint $table) {
            $table->id();
            $table->string('Prof_ID', 12);
            $table->unsignedInteger('Subject_ID');
            $table->foreign('Prof_ID')->references('Prof_ID')->on('professors')->cascadeOnDelete();
            $table->foreign('Subject_ID')->references('Subject_ID')->on('t_subject')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professor_subject');
    }
};
