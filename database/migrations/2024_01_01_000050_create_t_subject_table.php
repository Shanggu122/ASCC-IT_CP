<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('t_subject')) {
            return;
        }

        Schema::create('t_subject', function (Blueprint $table) {
            $table->increments('Subject_ID');
            $table->string('Subject_Name', 255);
            $table->unsignedTinyInteger('Dept_ID')->nullable();
            $table->string('Subject_Code', 50)->nullable();
            $table->text('Description')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_subject');
    }
};
