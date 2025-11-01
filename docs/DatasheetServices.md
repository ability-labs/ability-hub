# Servizi Datasheet e Preference Assessment

I servizi nella directory `app/Services/Datasheets` orchestrano la trasformazione delle schede di presa dati (datasheet) in report strutturati. Questi report vengono compilati dagli operatori durante o dopo le sessioni con gli studenti del centro abilitativo e costituiscono la base per analisi statistiche e suggerimenti generati da algoritmi o LLM.

## Architettura generale

```mermaid
graph TD
    A[Datasheet (modello Eloquent)] --> B[DatasheetReportFactory]
    B -->|Determina ReportType| C{Mappa strategia}
    C -->|Preference Assessment| D[Classe specifica (es. SingleItem, PairedChoice...)]
    D --> E[ReportAbstract::report()]
    E --> F[Output: tabelle + metadati]
    F --> G[Dashboard, esportazioni, prompt LLM]
```

1. **`DatasheetReportFactory`** riceve una `Datasheet` e, in base al `ReportType`, istanzia la classe concreta appropriata.
2. Ogni classe concreta estende `PreferenceAssessmentAbstract`, che fornisce template comuni per item, sessioni e report, oltre a utilità per la simulazione (`mockData`).
3. I punteggi vengono calcolati scorrendo le sessioni raccolte dagli operatori; il risultato alimenta grafici, KPI e raccomandazioni automatizzate.

## Ruolo di `ReportAbstract`

`ReportAbstract` definisce l'interfaccia che ogni report deve implementare:

- `report()` per ottenere la tabella finale (tipicamente colonne `Order`, `Item`, `Points`).
- `getReportTemplate()` e `getDatasetTemplate()` per inizializzare strutture dati omogenee nel front-end.
- `hasItems()`, `hasSessions()`, `hasLegend()` e metodi correlati per descrivere dinamicamente l'interfaccia di inserimento.

Questa astrazione permette di aggiungere nuovi protocolli di valutazione mantenendo un contratto chiaro tra backend e front-end (o sistemi analitici esterni).

## Panoramica delle classi di Preference Assessment

| Classe | Minimo/Suggerito item | Logica di punteggio | Legenda | Sequenze |
| ------ | -------------------- | ------------------- | ------- | -------- |
| `SingleItem` | 4 / 7 | Somma punti in base alla legenda `Interacts`, `Avoids`, `No Answer`. | Sì (`LEGEND`) | No |
| `PairedChoice` | 4 / 6 | Incrementa il punteggio dell'item scelto in ogni coppia. | No | Coppie fisse (`size=2`) |
| `MultipleStimulus` | 4 / 7 | Assegna punteggio decrescente in base all'ordine di scelta. | No | No |
| `MultipleStimulusWithReplacement` | 5 / 7 | Incrementa di 1 l'item selezionato, mantenendo gli stimoli in lista. | No | Sequenza fissa, strategia `keep-chosen`. |
| `MultipleStimulusWithoutReplacement` | 5 / 7 | Usa l'ordine di sequenza come punteggio, rimuovendo l'item scelto. | No | Sequenza decrementale, strategia `remove-chosen`. |
| `FreeOperantObservation` | 0 / 5 | Applica punteggi della legenda (`Approached`, `DNA`, `EW`). | Sì | No |

## Dettagli operativi per gli operatori

- **Compilazione delle sessioni**: ogni sessione contiene colonne predefinite (`Order`, `Item`, `Choice`, ecc.) generate dai metodi `getColumnsSchema()` e `getSessionTemplate()`.
- **Suggerimento item**: `mockData()` e `pickRandomItems()` possono generare dataset dimostrativi utili per formazione e testing.
- **Controllo qualità**: i metodi `getMinimumItems()` e `getSuggestedItems()` guidano gli operatori sul numero corretto di stimoli da testare, evitando schede incomplete.

## Benefici analitici

- I report ottenuti sono direttamente utilizzabili come **feature per modelli predittivi** o per **prompt LLM** che generano piani terapeutici personalizzati.
- Le informazioni su sequenze e legende consentono agli algoritmi di interpretare il contesto (es. un punteggio basso dovuto a evitamento, oppure sequenze che richiedono ri-presentazioni specifiche).
- Centralizzare la logica nel backend assicura coerenza con il front-end e riduce il rischio di interpretazioni divergenti dei protocolli di assessment.

## Esempio di output (`SingleItem`)

| Ordine | Item | Punti |
| ------ | ---- | ----- |
| 1 | Gioco sensoriale | 5 |
| 2 | Costruzioni | 3 |
| 3 | Libri illustrati | 0 |

Gli esiti così strutturati possono essere archiviati, condivisi con le famiglie e utilizzati per generare insight automatici sulle preferenze dello studente.
