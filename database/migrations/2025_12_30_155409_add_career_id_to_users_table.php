<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Adds 'career_id' column, links to 'careers' table
            // nullOnDelete means if a Career is deleted, the user remains but career_id becomes null
            $table->foreignId('career_id')
                ->nullable()
                ->constrained('careers')
                ->nullOnDelete()
                ->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['career_id']);
            $table->dropColumn('career_id');
        });
    }
};
