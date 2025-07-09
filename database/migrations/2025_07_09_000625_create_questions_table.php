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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_id');
            $table->string('type'); // 'multiple_choice', 'true_false', 'essay', 'fill_blank'
            $table->text('question_text');
            $table->json('options')->nullable(); // Para múltipla escolha
            $table->text('correct_answer')->nullable();
            $table->decimal('points', 8, 2)->default(1);
            $table->integer('order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            $table->index(['exam_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
