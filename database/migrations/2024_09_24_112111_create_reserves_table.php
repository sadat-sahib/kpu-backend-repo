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
        Schema::create('reserves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('user_type');
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->enum('status', ['active', 'inactive'])->default('inactive')->index(); // status defined once
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'user_type']);
            $table->index(['book_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserves');
    }
};
