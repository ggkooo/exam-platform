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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable(); // Código do módulo (ex: MOD001)
            $table->text('description')->nullable();
            $table->integer('order')->default(1); // Ordem do módulo na disciplina
            $table->boolean('is_active')->default(true);
            $table->integer('created_by'); // ID do usuário que criou
            $table->timestamps();
            
            $table->index(['subject_id', 'order']);
            $table->index(['is_active']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
