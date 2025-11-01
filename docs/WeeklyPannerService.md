# Servizio di Pianificazione Settimanale

Il `WeeklyPlannerService` automatizza la creazione degli appuntamenti terapeutici settimanali per ogni apprendete del centro abilitativo. Il servizio nasce per trasformare le preferenze temporali del discente, le disponibilità degli operatori e gli obiettivi di terapia (minuti settimanali) in un'agenda coerente, evitando conflitti e garantendo continuità di trattamento.

## Scopo e contesto

- **Obiettivo clinico**: assicurare che ciascun apprendete raggiunga i minuti di terapia pianificati, distribuendo le sessioni in modo sostenibile lungo la settimana.
- **Vincoli organizzativi**: rispettare le priorità assegnate agli operatori, utilizzare le fasce orarie dichiarate da apprendenti e operatori, bloccare la creazione di appuntamenti sovrapposti.
- **Dominio applicativo**: si integra con il flusso di pianificazione del centro documentato in `docs/AppointmentsPlanning.md`, diventando l'implementazione concreta dell'algoritmo descritto nel documento di dominio.

## Attori e dati coinvolti

| Entità | Descrizione | Attributi rilevanti |
| ------ | ----------- | ------------------ |
| `Learner` | Apprendente seguito dal centro | `weekly_minutes`, `slots`, relazioni con `operators` |
| `Operator` | Professionista incaricato della terapia | `slots`, pivot `priority`, disciplina |
| `Slot` | Fascia oraria potenziale | `week_day`, `day_span`, orario di inizio/fine, `discipline_id` |
| `Appointment` | Sessione confermata | `starts_at`, `ends_at`, `duration_minutes`, legami con `learner` e `operator` |

## Flusso decisionale

```mermaid
digraph WeeklyPlanner {
  rankdir=LR;
  node [shape=rect, style=rounded, fillcolor="#F1F5F9", fontname="Inter", fontsize=11];
  subgraph cluster_inputs {
    label = "Input";
    I1[weekStartDate normalizzato al lunedì];
    I2[Learner con slots e weekly_minutes];
    I3[Operatori assegnati ordinati per priorità];
  }
  Start([Avvia pianificazione]) --> Normalize[Normalizza settimana e valida dati];
  Normalize --> Remaining[Calcola minuti ancora da pianificare];
  Remaining -->|0 minuti| ExitReached[Esci: obiettivo già soddisfatto];
  Remaining -->|>0 minuti| LoopSlots[Itera sulle fasce preferite del learner];
  LoopSlots --> TryMatch[Prova matching con operatori (priorità crescente)];
  TryMatch -->|Compatibile| CreateApp[Genera appuntamento e aggiorna minuti];
  CreateApp --> CheckRemaining{Minuti <= 0?};
  CheckRemaining -->|Sì| ExitDone[Obiettivo raggiunto];
  CheckRemaining -->|No| LoopSlots;
  TryMatch -->|Nessun match| Fallbacks;
  subgraph cluster_fallback {
    label = "Fallback progressivi";
    F1[Stesso giorno + stessa fascia];
    F2[Stesso giorno, fascia libera];
    F3[Ricerca globale su operatori assegnati];
  }
  Fallbacks --> F1 --> F2 --> F3 --> Exhausted[Capacità esaurita];
  F3 -->|Slot trovato| CreateApp;
  Exhausted --> ExitFail[Fallimento: nessun slot disponibile];
}
```

## Strategie di assegnazione

Il servizio applica livelli successivi di ricerca per saturare i minuti settimanali. La tabella riassume il comportamento implementato dai metodi `tryAssignSlot` ed `extendedFallback`.

| Livello | Metodo | Strategia | Descrizione operativa |
| ------- | ------ | --------- | --------------------- |
| Base | `tryAssignSlot` | Slot identico | Combina slot del learner con l'operatore prioritario che condivide lo stesso slot o firma temporale. |
| Fallback 1 | `tryAssignSlot` | Stesso giorno e fascia (`day_span`) | Utilizza qualunque slot dell'operatore nella stessa fascia giornaliera. |
| Fallback 2 | `tryAssignSlot` | Stesso giorno, qualsiasi fascia | Consente di cambiare fascia mantenendo il giorno, pur di assegnare l'appuntamento. |
| Fallback 3 | `extendedFallback` | Intera settimana | Scansiona tutti gli slot liberi degli operatori assegnati nella settimana corrente. |

## Regole chiave

1. **Un solo appuntamento per giorno**: l'array `daysTaken` evita doppi booking sullo stesso giorno per il medesimo learner.
2. **Conflitti evitati**: `hasConflictingAppointment` consulta gli appuntamenti esistenti di apprendenti e operatori per bloccare sovrapposizioni.
3. **Validazioni rigide**: `validateInputs` impedisce l'avvio del servizio se il learner è invalido, la data non è un lunedì o i minuti settimanali sono nulli.
4. **Transazioni atomiche**: l'intera pianificazione viene eseguita in `DB::transaction` per garantire che tutti gli appuntamenti del learner vengano creati o annullati insieme.
5. **Titoli esplicativi**: `generateAppointmentTitle` crea etichette `Learner / Operator`, utili per la stampa del calendario condiviso con famiglie e operatori.

## Output e riepilogo

Al termine, il servizio restituisce la lista degli `Appointment` creati; `getSchedulingSummary` fornisce inoltre un report aggregato con minuti pianificati, minuti rimanenti e percentuale di completamento. Questi dati alimentano i cruscotti direzionali e i reminder automatici verso coordinatori e famiglie.

## Metriche di monitoraggio suggerite

| Indicatore | Fonte | Uso operativo |
| ---------- | ----- | ------------- |
| % minuti coperti per learner | `getSchedulingSummary` | Valutare saturazione delle terapie pianificate |
| Giorni consecutivi occupati | Appuntamenti generati | Distribuire in modo equilibrato gli sforzi dell'apprendente |
| Numero di fallback utilizzati | Log applicativo (`reserved`/`extendedFallback`) | Comprendere dove potenziare le disponibilità operatori |

Il `WeeklyPlannerService` rappresenta quindi l'orchestratore centrale della pianificazione, coerente con le regole di dominio e pronto a supportare analisi predittive sui carichi terapeutici futuri.
