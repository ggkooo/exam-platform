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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_minutes'); // Duração em minutos
            $table->integer('total_questions')->default(0);
            $table->decimal('total_points', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('randomize_questions')->default(false);
            $table->boolean('show_results_immediately')->default(false);
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('max_attempts')->default(1);
            $table->unsignedBigInteger('created_by'); // ID do usuário que criou
            $table->timestamps();
            
            // Índices para performance
            $table->index(['is_active', 'start_time', 'end_time']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
