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
    Schema::create('projects', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->string('link')->nullable();
        $table->text('about'); // Description
        $table->string('category')->default('Personal'); // Personal, Paid
        $table->decimal('value', 10, 2)->nullable(); // For paid projects
        $table->json('skills')->nullable(); // Store tags as JSON ["Vue", "Laravel"]
        $table->json('tools')->nullable();  // Store tags as JSON ["VS Code"]
        $table->string('status')->default('Active');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
