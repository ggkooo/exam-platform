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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable(); // Código da disciplina (ex: MAT001)
            $table->text('description')->nullable();
            $table->integer('workload_hours')->nullable(); // Carga horária
            $table->boolean('is_active')->default(true);
            $table->integer('created_by'); // ID do usuário que criou
            $table->timestamps();
            
            $table->index(['course_id', 'is_active']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
