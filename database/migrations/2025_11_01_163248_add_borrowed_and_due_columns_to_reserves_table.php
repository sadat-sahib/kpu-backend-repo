<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reserves', function (Blueprint $table) {
            $table->date('borrowed_at')->nullable()->after('status');
            $table->date('due_at')->nullable()->after('borrowed_at');
        });
    }

    public function down(): void
    {
        Schema::table('reserves', function (Blueprint $table) {
            $table->dropColumn(['borrowed_at', 'due_at']);
        });
    }

};
