<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Roadmaps
        Schema::create('roadmaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('general'); // Added: matches controller usage
            $table->string('title');
            $table->string('career_goal'); // e.g. "Frontend Engineer"
            $table->string('level')->default('Beginner');
            $table->text('course_code')->nullable();
            $table->string('estimate'); // "14 Weeks"
            $table->string('status')->default('active');
            $table->integer('progress_percent')->default(0);
        });

        // 2. Phases
        Schema::create('roadmap_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roadmap_id')->constrained('roadmaps')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order_index');
            $table->json('skills'); // Store skills as JSON array ["HTML", "CSS"]
            $table->timestamps();
        });

        // 3. Tasks
        Schema::create('roadmap_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phase_id')->constrained('roadmap_phases')->onDelete('cascade');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->boolean('completed')->default(false);
            $table->integer('order_index');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_tasks');
        Schema::dropIfExists('roadmap_phases');
        Schema::dropIfExists('roadmaps');
    }
};
