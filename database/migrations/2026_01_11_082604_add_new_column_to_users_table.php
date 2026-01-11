<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('active')->after('email');
            $table->text('bio')->nullable();
            $table->string('github')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('avatar')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
        });
    }
};
