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
            // Remove a foreign key constraint temporariamente
            $table->dropForeign(['exam_id']);
            
            // Modifica a coluna para permitir NULL
            $table->unsignedBigInteger('exam_id')->nullable()->change();
            
            // Recria a foreign key constraint permitindo NULL
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Remove a foreign key constraint
            $table->dropForeign(['exam_id']);
            
            // Volta a coluna para NOT NULL
            $table->unsignedBigInteger('exam_id')->nullable(false)->change();
            
            // Recria a foreign key constraint
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
        });
    }
};
