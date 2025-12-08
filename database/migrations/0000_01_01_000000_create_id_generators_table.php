<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('id_generators', function (Blueprint $table) {
            $table->date('date')->primary();
            $table->unsignedInteger('last_increment')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('id_generators');
    }
};
