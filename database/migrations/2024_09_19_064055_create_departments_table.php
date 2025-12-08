<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('fac_id')->constrained('faculties', 'id')->onDelete('cascade');
            $table->index(columns: 'fac_id');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
