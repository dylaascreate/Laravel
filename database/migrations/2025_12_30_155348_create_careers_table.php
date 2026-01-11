<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. "Frontend Engineer"
            $table->string('domain');       // e.g. "Engineering", "Design"
            $table->string('status')->default('Active'); // e.g. "Active", "Draft"
            $table->text('description')->nullable();

            // Made nullable because the initial 'Create Career' form might not include skills
            $table->json('skills')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};
