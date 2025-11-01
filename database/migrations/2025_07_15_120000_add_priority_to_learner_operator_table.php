<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('learner_operator', function (Blueprint $table) {
            $table->unsignedInteger('priority')
                ->default(1)
                ->after('operator_id');

            $table->index(['learner_id', 'priority']);
        });

        $existingAssignments = DB::table('learner_operator')
            ->select('learner_id', 'operator_id', 'created_at')
            ->orderBy('learner_id')
            ->orderBy('created_at')
            ->orderBy('operator_id')
            ->get()
            ->groupBy('learner_id');

        foreach ($existingAssignments as $learnerId => $assignments) {
            $priority = 1;

            foreach ($assignments as $assignment) {
                DB::table('learner_operator')
                    ->where('learner_id', $learnerId)
                    ->where('operator_id', $assignment->operator_id)
                    ->update(['priority' => $priority++]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('learner_operator', function (Blueprint $table) {
            $table->dropIndex('learner_operator_learner_id_priority_index');
            $table->dropColumn('priority');
        });
    }
};
