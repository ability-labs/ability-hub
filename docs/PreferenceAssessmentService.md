# Servizio di Reportistica Preference Assessment

Il `PreferenceAssessmentService` analizza le preferenze dei rinforzi registrate dagli operatori per ogni apprendete e restituisce dataset pronti per la visualizzazione e per l'elaborazione tramite algoritmi predittivi o Large Language Models (LLM). I report prodotti alimentano le schede di presa dati e supportano le decisioni sulle terapie del centro abilitativo.

## Scopi principali

- **Aggregare i punteggi dei rinforzi** per categoria e sotto-categoria, offrendo una lettura temporale delle preferenze dell'apprendente.
- **Limitare l'analisi al periodo recente** per mantenere le visualizzazioni focalizzate sui trend più recenti.
- **Fornire dataset coerenti con la struttura attesa dai widget front-end** e dai motori analitici interni.

## Flusso elaborativo

```mermaid
flowchart TD
    A[Avvio reportPreferences] --> B[Carica PreferenceAssessment con reinforcer associati]
    B --> C[Ordina per updated_at]
    C --> D[Estrai categorie uniche dai reinforcer]
    D --> E[Raggruppa i record per mese (formato Y-m)]
    E --> F{Più di 5 mesi?}
    F -- Sì --> G[Considera solo gli ultimi 5]
    F -- No --> H[Mantieni gruppi correnti]
    G --> I[Costruisci dataset per categoria]
    H --> I
    I --> J[Restituisci labels + datasets con somme dei punteggi]
```

Lo stesso schema viene riutilizzato da `reportCategoryPreferences`, con l'aggiunta di una seconda fase in cui gli assessment vengono filtrati per categoria e aggregati per sotto-categoria.

## Dataset generati

| Metodo | Labels | Dataset | Utilizzo |
| ------ | ------ | ------- | -------- |
| `reportPreferences` | Categorie globali dei rinforzi | Array di serie mensili con somma dei punteggi per categoria | Grafici a barre o radar sull'andamento delle preferenze complessive |
| `reportCategoryPreferences.general` | Categorie globali | Output di `reportPreferences` con etichetta "General Preference Assessment" | Panoramica generale per supervisori e famiglie |
| `reportCategoryPreferences.{slug}` | Sotto-categorie relative alla categoria selezionata | Serie mensili limitate alla categoria e alle sue sotto-categorie | Analisi mirata su tipologie di rinforzo specifiche (sensoriali, edibili, sociali, ecc.) |

## Integrazione con datasheet e analisi avanzata

- I report alimentano le schede di presa dati digitali, permettendo agli operatori di visualizzare trend e di compilare le sessioni successive con maggiore consapevolezza.
- Le strutture `labels` e `datasets` sono pensate per essere trasformate rapidamente in prompt o feature per modelli predittivi, che possono suggerire adattamenti terapeutici basati sull'andamento delle preferenze.

## Requisiti sui dati

| Aspetto | Regola | Impatto |
| ------- | ------ | ------- |
| Completezza categorie | Il servizio interroga `Reinforcer::all()` per ottenere l'elenco completo delle categorie. | Garantisce coerenza tra periodi con o senza dati espliciti. |
| Normalizzazione date | Tutte le date vengono mappate al formato `Y-m` prima di essere aggregate. | Permette il confronto temporale mensile e facilita la visualizzazione. |
| Limite temporale | Massimo cinque mesi inclusi nei dataset. | Mantiene i grafici leggibili e focalizzati sulle evoluzioni recenti. |

## Output di esempio

```json
{
  "labels": ["Rinforzi Sensoriali", "Rinforzi Edibili"],
  "datasets": [
    {"label": "Mag 2025", "data": [6, 4]},
    {"label": "Giu 2025", "data": [3, 7]}
  ]
}
```

Questi dataset sono pronti per essere passati a librerie grafiche o trasformati in prompt per LLM che generano raccomandazioni cliniche personalizzate.
