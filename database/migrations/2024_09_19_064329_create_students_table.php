<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('phone');
            $table->string('nin');
            $table->string('nic');
            $table->string('current_residence');
            $table->string('original_residence');
            $table->foreignId('fac_id')->constrained('faculties', 'id')->onDelete('cascade');
            $table->foreignId('dep_id')->constrained('departments', 'id')->onDelete('cascade');

            $table->index('dep_id');
            $table->index('fac_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
