<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_create_courses_table.php
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_code', 20)->unique();
            $table->string('course_name');
            $table->string('next_course_code')->nullable();
            $table->enum('category', ['MAJOR', 'ELECTIVE', 'FOCUS', 'UNIVERSITY', 'OTHER'])->default('MAJOR');
            $table->unsignedTinyInteger('credit')->default(3);

            // Arrays -> JSON columns
            $table->json('associated_skills')->nullable();
            $table->json('course_content_outline')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
