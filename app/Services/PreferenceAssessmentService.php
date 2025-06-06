<?php

namespace App\Services;

use App\Models\Learner;
use App\Models\PreferenceAssessment;
use App\Models\Reinforcer;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PreferenceAssessmentService
{
    public function reportPreferences(Learner $learner): array
    {
        // Recupera tutti i PreferenceAssessment con il reinforcer associato, ordinati per updated_at
        $assessments = $learner->preferences()
            ->with('reinforcer')
            ->orderBy('updated_at')
            ->get();

        // Ottieni tutte le categorie di rinforzi (come labels)
        $allCategories = Reinforcer::all()->pluck('category')->unique()->values()->all();

        // Raggruppa i PreferenceAssessment per mese (formato "Y-m")
        $groups = $assessments->groupBy(function ($assessment) {
            return Carbon::parse($assessment->updated_at)->format('Y-m');
        })->sortKeys();

        // Se ci sono più di 5 mesi, prendi solo gli ultimi 5 (i più recenti)
        if ($groups->count() > 5) {
            $groups = $groups->slice(-5, 5);
        }

        $datasets = [];

        // Per ogni gruppo (mese), calcola la somma dei punteggi per ciascuna categoria
        // Le categorie sono prese dall'elenco completo, quindi anche quelle senza dati saranno riportate come 0
        foreach ($groups as $month => $groupAssessments) {
            $label = Carbon::createFromFormat('Y-m', $month)->format('M Y');
            $data = [];
            foreach ($allCategories as $category) {
                $sum = $groupAssessments->where('reinforcer.category', $category)->sum('score');
                $data[] = (int) $sum;
            }
            $datasets[] = [
                'label' => $label,
                'data'  => $data,
            ];
        }

        return [
            'labels'   => $allCategories,
            'datasets' => $datasets,
        ];
    }

    public function reportCategoryPreferences(Learner $learner): array
    {
        // Recupera tutti i PreferenceAssessment con il reinforcer associato, ordinati per updated_at
        $assessments = $learner->preferences()
            ->with('reinforcer')
            ->orderBy('updated_at')
            ->get();

        // Report generale (già implementato)
        $generalReport = $this->reportPreferences($learner);

        // Ottieni tutte le categorie generali (es. "Rinforzi Sensoriali", "Rinforzi Edibili", ecc.)
        $allCategories = Reinforcer::all()->pluck('category')->unique()->values()->all();

        $result = [
            'general' => [
                'label' => 'General Preference Assessment',
                'data'  => $generalReport,
            ],
        ];

        // Per ogni categoria, genera un report raggruppato per mese
        // ma limitando la query ai rinforzatori di quella categoria e usando le subcategorie come label
        foreach ($allCategories as $category) {
            // Filtra gli assessments relativi a questa categoria
            $catAssessments = $assessments->filter(function ($assessment) use ($category) {
                return $assessment->reinforcer->category === $category;
            });

            // Ottieni tutte le subcategorie uniche per questa categoria
            $subCategories = Reinforcer::query()
                ->whereRaw(
                    "JSON_EXTRACT(category, '$." . config('app.locale') . "') = '$category'"
                )
                ->pluck('subcategory')
                ->unique()
                ->values()
                ->all();

            // Se non troviamo subcategorie (cosa improbabile), usiamo un array vuoto
            if (empty($subCategories)) {
                $subCategories = [];
            }

            // Raggruppa gli assessments di questa categoria per mese
            $groups = $catAssessments->groupBy(function ($assessment) {
                return Carbon::parse($assessment->updated_at)->format('Y-m');
            })->sortKeys();

            // Se ci sono più di 5 mesi, prendi solo gli ultimi 5
            if ($groups->count() > 5) {
                $groups = $groups->slice(-5, 5);
            }

            $datasets = [];
            foreach ($groups as $month => $groupAssessments) {
                $label = Carbon::createFromFormat('Y-m', $month)->format('M Y');
                $data = [];
                foreach ($subCategories as $subCategory) {
                    $sum = $groupAssessments->where('reinforcer.subcategory', $subCategory)->sum('score');
                    $data[] = (int) $sum;
                }
                $datasets[] = [
                    'label' => $label,
                    'data'  => $data,
                ];
            }

            $result[Str::slug($category, '_')] = [
                'label' => $category,
                'data'  => [
                    'labels'   => $subCategories,
                    'datasets' => $datasets,
                ],
            ];
        }

        return $result;
    }
}
