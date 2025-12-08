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
        Schema::create('employee_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees','id')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions','id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_permission');
    }
};
