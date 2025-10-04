<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learner_operator', function (Blueprint $table) {
            $table->foreignUuid('learner_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUuid('operator_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['learner_id', 'operator_id']);
        });

        // migrate existing single-operator assignments into the pivot table
        $existingAssignments = DB::table('learners')
            ->whereNotNull('operator_id')
            ->select('id as learner_id', 'operator_id')
            ->get();

        if ($existingAssignments->isNotEmpty()) {
            $now = now();
            DB::table('learner_operator')->insert(
                $existingAssignments->map(fn ($record) => [
                    'learner_id'  => $record->learner_id,
                    'operator_id' => $record->operator_id,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ])->all()
            );
        }

        Schema::table('learners', function (Blueprint $table) {
            $table->dropConstrainedForeignId('operator_id');
        });
    }

    public function down(): void
    {
        Schema::table('learners', function (Blueprint $table) {
            $table->foreignUuid('operator_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        });

        $existingAssignments = DB::table('learner_operator')
            ->select('learner_id', DB::raw('MIN(operator_id) as operator_id'))
            ->groupBy('learner_id')
            ->get();

        foreach ($existingAssignments as $assignment) {
            DB::table('learners')
                ->where('id', $assignment->learner_id)
                ->update(['operator_id' => $assignment->operator_id]);
        }

        Schema::dropIfExists('learner_operator');
    }
};
