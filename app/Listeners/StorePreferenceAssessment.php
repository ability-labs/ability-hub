<?php

namespace App\Listeners;

use App\Events\DatasheetUpdatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StorePreferenceAssessment
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DatasheetUpdatedEvent $event): void
    {
        $datasheet = $event->datasheet;
        $chart = $datasheet->report()->report(); // Contiene "columns" e "rows"
        $items = $datasheet->report()->items;      // Mappa degli items, indicizzata per chiave

        // Raggruppa le righe in base al valore "Order"
        $groups = [];
        foreach ($chart['rows'] as $row) {
            // Il primo elemento della riga è il valore di "Order"
            $order = $row[0];
            $groups[$order][] = $row;
        }
        // Ordina i gruppi in ordine crescente in base al valore "Order"
        ksort($groups);

        // Assegna i punti in base alla posizione del gruppo:
        // il primo gruppo riceve 3 punti, il secondo 2 e tutti gli altri 0.
        $groupPosition = 1;
        foreach ($groups as $order => $groupRows) {
            if ($groupRows[0][2] === 0 || $groupRows[0][2] <0) {
                // Se il punteggio originale del gruppo è 0, assegniamo 0 a tutti e continuiamo
                $score = 0;
            } else {
                if ($groupPosition == 1) {
                    $score = 5;
                } elseif ($groupPosition == 2) {
                    $score = 3;
                } else {
                    $score = 1;
                }
            }
            foreach ($groupRows as $row) {
                $reinforcerKey = $row[1];
                if (!isset($items[$reinforcerKey])) {
                    continue;
                }
                \App\Models\PreferenceAssessment::query()
                    ->upsert([
                            'learner_id'    => $datasheet->learner_id,
                            'datasheet_id'  => $datasheet->id,
                            'operator_id'   => $datasheet->operator_id,
                            'reinforcer_id' => $items[$reinforcerKey]['id'],
                            'score'         => $score,
                        ],
                        uniqueBy: ['datasheet_id','reinforcer_id'],
                        update: ['score']
                    );
            }
            $groupPosition++;
        }
    }
}
