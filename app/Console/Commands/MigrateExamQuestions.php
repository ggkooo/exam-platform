<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Support\Facades\DB;

class MigrateExamQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:exam-questions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra questões do sistema antigo (com exam_id) para o novo sistema (exam_questions table)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando migração das questões de provas...');

        DB::transaction(function () {
            // Busca todas as questões que têm exam_id (sistema antigo)
            $questionsWithExam = Question::whereNotNull('exam_id')->get();

            if ($questionsWithExam->isEmpty()) {
                $this->info('Nenhuma questão encontrada para migrar.');
                return;
            }

            $this->info("Encontradas {$questionsWithExam->count()} questões para migrar.");

            $progressBar = $this->output->createProgressBar($questionsWithExam->count());
            $progressBar->start();

            foreach ($questionsWithExam as $question) {
                try {
                    // Verifica se já existe na tabela exam_questions
                    $existingRelation = ExamQuestion::where('exam_id', $question->exam_id)
                                                   ->where('question_id', $question->id)
                                                   ->first();

                    if (!$existingRelation) {
                        // Cria a relação na nova tabela
                        ExamQuestion::create([
                            'exam_id' => $question->exam_id,
                            'question_id' => $question->id,
                            'order' => $question->order ?? 1,
                            'points' => $question->points,
                            'is_active' => $question->is_active ?? true,
                        ]);
                    }

                    // Remove o exam_id da questão para torná-la uma questão do banco
                    $question->update(['exam_id' => null]);

                } catch (\Exception $e) {
                    $this->error("Erro ao migrar questão ID {$question->id}: " . $e->getMessage());
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

            // Atualiza o total de questões em cada exame
            $exams = Exam::all();
            foreach ($exams as $exam) {
                $totalQuestions = $exam->examQuestions()->count();
                $exam->update(['total_questions' => $totalQuestions]);
            }

            $this->info('Migração concluída com sucesso!');
            $this->info('Todas as questões foram movidas para o banco de questões e vinculadas aos exames através da tabela exam_questions.');
        });

        return 0;
    }
}
