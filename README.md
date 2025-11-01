# AbilityHub

AbilityHub è una piattaforma gestionale per centri abilitativi che coordinano percorsi educativi per persone con autismo, DSA, ADHD e altre difficoltà comunicative. L'obiettivo principale è supportare il team multidisciplinare nella costruzione di terapie coerenti, tracciabili e condivise.

## Missione del progetto

- **Centralizzare i dati clinico-educativi** di apprendenti, operatori e famiglie, riducendo i silos informativi che spesso ostacolano la continuità terapeutica.
- **Coordinare l'agenda settimanale** del centro, automatizzando l'assegnazione di appuntamenti in base a priorità, disponibilità e obiettivi di minuti terapeutici.
- **Supportare la presa dati strutturata** tramite schede digitali (datasheet) e assessment delle preferenze, così da generare insight utili a supervisioni, ricerca e training.
- **Abilitare l'analisi assistita da algoritmi e LLM**, trasformando le raccolte dati in suggerimenti predittivi su progressi, criticità e personalizzazione degli interventi.

## Funzionalità principali

| Area | Funzionalità | Valore per il centro abilitativo |
| ---- | ------------ | -------------------------------- |
| Pianificazione terapeutica | Calendario condiviso, servizio di pianificazione settimanale, gestione dei conflitti e rispetto dei minuti obiettivo | Garantisce continuità operativa e riduce gli errori umani nella costruzione dell'orario |
| Gestione equipe | Profilazione di operatori, assegnazione priorità, sincronizzazione automatica delle disponibilità | Consente di bilanciare il carico di lavoro e di rispettare la seniority clinica |
| Monitoraggio apprendenti | Schede di presa dati, assessment preferenze, reportistica aggregata | Permette di adattare le terapie sulla base di evidenze osservabili |
| Collaborazione e famiglie | Onboarding guidato, notifiche, inviti e condivisione di report | Favorisce trasparenza e coinvolgimento del contesto familiare |

## Perché contribuire

AbilityHub nasce per offrire strumenti di lavoro concreti alle équipe che operano in centri abilitativi. La roadmap evolve verso un ecosistema API-first e MCP-ready, in cui servizi e azioni applicative fungono da singola fonte di verità per web app, app mobile e canali conversational AI. Ogni contributo che migliora l'accuratezza dei servizi, la qualità dei dati o la fruibilità dei flussi operativi aiuta professionisti e famiglie a prendere decisioni informate.

## Come iniziare a contribuire

1. Clona il repository e installa le dipendenze PHP con `composer install` (PHP ≥ 8.2).
2. Copia `.env.example` in `.env`, genera la chiave (`php artisan key:generate`) ed esegui `php artisan migrate`.
3. Installa le dipendenze front-end (`npm install`) e avvia l'ambiente di sviluppo con `composer run dev`.
4. Consulta la cartella `docs/` per comprendere i servizi complessi (planner, datasheet, assessment) prima di proporre modifiche funzionali.

Per ulteriori dettagli architetturali, consulta i servizi in `app/Services` e la documentazione aggiornata nella directory `docs/`.
