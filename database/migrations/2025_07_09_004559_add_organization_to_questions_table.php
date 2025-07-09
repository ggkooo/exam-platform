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
        Schema::table('questions', function (Blueprint $table) {
            // Adiciona campos para organização hierárquica
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('module_id')->nullable()->constrained()->onDelete('set null');
            
            // Adiciona campos adicionais para questões
            $table->string('title')->nullable(); // Título/nome da questão
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium');
            $table->json('tags')->nullable(); // Tags para categorização adicional
            $table->integer('created_by')->nullable(); // ID do usuário que criou
            
            // Índices para melhor performance
            $table->index(['course_id', 'subject_id', 'module_id']);
            $table->index(['difficulty_level']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['module_id']);
            $table->dropColumn([
                'course_id', 
                'subject_id', 
                'module_id',
                'title',
                'difficulty_level',
                'tags',
                'created_by'
            ]);
        });
    }
};
