<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->unsignedSmallInteger('publicationYear');

            $table->enum('lang', ['en', 'fa', 'pa']);
            $table->string('edition')->nullable();
            $table->string('translator')->nullable();
            $table->string('isbn')->unique()->nullable();
            $table->string('code')->nullable();
            $table->text('description')->nullable();

            $table->foreignId('cat_id')
                ->constrained('categories')
                ->onDelete('cascade');

            $table->foreignId('dep_id')
                ->constrained('departments')
                ->onDelete('cascade');

            $table->foreignId('sec_id')->nullable()
                ->constrained('sections')
                ->onDelete('cascade');

            $table->enum('format', ['hard', 'pdf', 'both']);
            $table->enum('borrow', ['yes', 'no'])->default('no');

            $table->timestamps();

            $table->index(['cat_id', 'dep_id', 'publicationYear']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
