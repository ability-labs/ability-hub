# Documentazione del Servizio `WeeklyPlanner`

**Data**: 10/07/2025 - **Autore**: Salvo Bonanno

Questo documento descrive il processo di pianificazione settimanale gestito dal servizio `WeeklyPlannerService`. Il servizio è responsabile di assegnare appuntamenti ai discenti (learner) per raggiungere il loro obiettivo di minuti settimanali, tenendo conto della disponibilità degli operatori e delle fasce orarie preferite.

---
## 1. Panoramica del Servizio

Il `WeeklyPlannerService` è progettato per automatizzare la creazione di appuntamenti settimanali. Utilizza un approccio basato sulla priorità delle fasce orarie e sulla prevenzione dei conflitti per ottimizzare la programmazione.

**Componenti Chiave:**

* **Discente (Learner):** L'individuo che necessita di appuntamenti, con un obiettivo di minuti settimanali (`weekly_minutes`).
* **Operatore (Operator):** Il professionista che fornisce il servizio, associato a uno o più discenti.
* **Fascia Oraria (Slot):** Una finestra di tempo disponibile per gli appuntamenti, definita per giorno della settimana, ora di inizio e durata. Le fasce orarie possono essere associate sia a discenti (preferenze) che a operatori (disponibilità).
* **Appuntamento (Appointment):** Un evento di pianificazione concreto creato dal servizio, che lega un discente, un operatore e una fascia oraria specifica in un dato momento.

---
## 2. Processo di Pianificazione (`scheduleForLearner`)

Il metodo principale `scheduleForLearner(Learner $learner, Carbon $weekStartDate)` è il punto di ingresso per avviare il processo di pianificazione per un discente specifico in una determinata settimana.

### Fasi del Processo:

1.  **Normalizzazione della Data di Inizio Settimana:**
    * La data `$weekStartDate` fornita viene copiata e **forzata al lunedì** della settimana di riferimento (`$weekStartDate->copy()->startOfWeek()`). Questo assicura che tutta la logica di pianificazione sia coerente e si riferisca sempre all'inizio della settimana standard.

2.  **Validazione degli Input (`validateInputs`):**
    * Viene eseguito un controllo preliminare per assicurare che il `Learner` sia valido e che il suo obiettivo di `weekly_minutes` sia maggiore di zero.
    * Viene emesso un log di avviso se la `$weekStartDate` iniziale non è un lunedì, anche se viene poi normalizzata.
    * Se la validazione fallisce, il processo si interrompe.

3.  **Verifica dell'Operatore:**
    * Si controlla che il `Learner` abbia un `Operator` associato. Senza un operatore, non è possibile pianificare appuntamenti. In tal caso, il processo si interrompe.

4.  **Calcolo Minuti Rimanenti (`getRemainingMinutesForWeek`):**
    * Il servizio calcola quanti minuti mancano al discente per raggiungere il suo obiettivo settimanale (`weekly_minutes`), sottraendo la durata degli appuntamenti già esistenti per quella settimana.
    * Se i minuti rimanenti sono zero o meno (cioè l'obiettivo è già raggiunto o superato), il processo si interrompe.

5.  **Reperimento Fasce Orarie Disponibili (`getAvailableSlots`):**
    * Questa è una fase cruciale che determina quali fasce orarie possono essere utilizzate per la pianificazione.
    * Vengono recuperate le fasce orarie preferite dal **discente** e quelle disponibili per l'**operatore**.
    * Viene applicata una **logica di prioritizzazione** (`getPrioritizedSlots`):
        * Se il discente non ha dichiarato preferenze di fasce orarie, vengono considerate tutte le fasce orarie disponibili per l'operatore.
        * Se il discente ha preferenze, vengono prima considerate le fasce orarie **comuni** (preferite dal discente E disponibili per l'operatore).
        * Successivamente, come **fallback**, vengono considerate le fasce orarie disponibili per l'operatore ma non specificamente preferite dal discente.
    * Infine, le fasce orarie prioritizzate vengono filtrate (`filterConflictingSlots`) per **escludere quelle che genererebbero conflitti** con appuntamenti esistenti (sia per il discente che per l'operatore) nella settimana corrente.

6.  **Creazione degli Appuntamenti (`createAppointmentsFromSlots`):**
    * Se ci sono fasce orarie disponibili (dopo la prioritizzazione e il filtraggio), il servizio itera su di esse.
    * Per ogni fascia oraria:
        * Viene calcolato l'esatto `starts_at` e `ends_at` dell'appuntamento, basandosi sul giorno della settimana della fascia oraria e l'ora di inizio.
        * La **durata effettiva** dell'appuntamento viene determinata come il minimo tra la durata della fascia oraria e i `minutesToSchedule` rimanenti. Questo assicura che non si ecceda l'obiettivo del discente.
        * Viene eseguito un **doppio controllo dei conflitti** subito prima della creazione per gestire eventuali race condition o modifiche dell'ultimo minuto.
        * Se non ci sono conflitti, un nuovo record `Appointment` viene creato nel database, collegando discente, operatore, disciplina, orario e durata.
        * I `minutesToSchedule` vengono aggiornati, sottraendo la durata del nuovo appuntamento.
    * Il ciclo continua finché i `minutesToSchedule` raggiungono zero o non ci sono più fasce orarie disponibili.

---
## 3. Report e Riepilogo (`getSchedulingSummary`)

Il metodo `getSchedulingSummary(Learner $learner, Carbon $weekStartDate)` fornisce una panoramica dettagliata dello stato di pianificazione per un discente in una data settimana.

### Dettagli del Riepilogo:

Il metodo restituisce un array associativo contenente le seguenti informazioni:

* **`learner_id`**: L'ID del discente.
* **`week_start`**: La data di inizio (lunedì) della settimana di riferimento (formato `YYYY-MM-DD`).
* **`weekly_minutes_target`**: L'obiettivo di minuti settimanali per il discente.
* **`scheduled_minutes`**: Il totale dei minuti di appuntamenti programmati per il discente in quella settimana.
* **`remaining_minutes`**: I minuti che mancano per raggiungere l'obiettivo settimanale (`weekly_minutes_target - scheduled_minutes`). Sarà sempre un valore non negativo.
* **`appointments_count`**: Il numero totale di appuntamenti programmati per il discente in quella settimana.
* **`completion_percentage`**: La percentuale dell'obiettivo settimanale che è stata raggiunta (calcolata come `(scheduled_minutes / weekly_minutes_target) * 100`). Se l'obiettivo è zero, la percentuale sarà zero per evitare divisioni per zero.

---
## 4. Gestione dei Conflitti

Il servizio implementa una robusta logica di prevenzione dei conflitti attraverso il metodo `hasConflictingAppointment`. Questo metodo verifica la presenza di sovrapposizioni con appuntamenti esistenti sia per il discente che per l'operatore. Un potenziale appuntamento è considerato in conflitto se il suo intervallo di tempo si sovrappone a quello di un appuntamento già esistente. Questa verifica viene effettuata sia durante la fase di filtraggio iniziale delle fasce orarie che come ulteriore controllo prima della creazione di ogni singolo appuntamento.

---
## 5. Strategia di Fallback (No Preferenze del Discente)

Un aspetto importante della logica è la gestione dei discenti che non hanno specificato alcuna preferenza di fascia oraria (`learner->slots` è vuoto). In questo scenario, il servizio non si interrompe, ma ricade automaticamente sull'utilizzo di tutte le fasce orarie disponibili per l'operatore associato al discente. Questo massimizza le possibilità di pianificazione anche in assenza di input dettagliati da parte del discente.
