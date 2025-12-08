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
        Schema::create('proifles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers','id');
            $table->string('whatsApp')->nullable();
            $table->string('facebook')->nullable();
            $table->string('gitHub')->nullable();
            $table->string('linkedIn')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proifles');
    }
};
