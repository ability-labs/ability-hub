# AbilityHub

AbilityHub è una piattaforma gestionale per centri di abilitazione che coordina utenti, operatori, apprendenti, slot disciplinari e appuntamenti settimanali basati su disponibilità condivise.

## Architettura e stack

- **Backend:** Laravel 11 con Filament e servizi applicativi (es. `WeeklyPlannerService`) governa la logica di dominio e affida la validazione alle Form Request.
- **Frontend:** Blade, componenti riusabili e AlpineJS alimentano un’interfaccia responsiva compilata tramite Vite e Tailwind.
- **Persistenza:** Tabelle ad UUID con pivot per discipline, disponibilità e priorità rappresentano calendario e preferenze settimanali.

## Funzionalità principali

- **Calendario appuntamenti e pianificazione settimanale** con creazione, aggiornamento, cancellazione e generazione automatica via servizi dedicati e interfaccia FullCalendar/Alpine.
- **Gestione apprendenti** con assegnazione operatori prioritizzati, minuti settimanali e sincronizzazione tramite azioni transazionali.
- **Gestione operatori** con colori, discipline e vista calendario dedicata, oltre al toggle della loro disponibilità sugli slot.
- **Disponibilità condivise**: apprendenti e operatori attivano/disattivano slot compatibili con controlli di coerenza disciplinare.
- **Datasheet e Preference Assessment** con report generati a partire da tipi configurabili e servizi di aggregazione categorie/subcategorie.
- **Onboarding a invito** con generazione e invio di codici registrazione via notifica e modello dedicato.

## Roadmap verso API-first e MCP

L’app espone attualmente solo rotte web autenticate; la logica di dominio è centralizzata in servizi e azioni riutilizzabili, facilitando l’esposizione futura di API REST con Laravel Sanctum e la serializzazione dei dati verso un server MCP che condivida gli stessi aggregati (viste, API, MCP).

## Setup e avvio

1. Clona il repository e installa le dipendenze PHP: `composer install` (PHP ≥ 8.2).
2. Copia l’`.env`, genera la chiave applicativa ed esegui le migrazioni (`php artisan migrate`).
3. Installa le dipendenze front-end e avvia Vite: `npm install && npm run dev`.
4. Avvia il server locale (`php artisan serve`) o usa lo script `composer run dev` che orchestra server, queue, log viewer e Vite in parallelo.

## Strumenti di qualità e test

- PHPUnit, Laravel Pint e Collision sono già inclusi per testing e static analysis; esegui `./vendor/bin/pint` e `php artisan test` prima di ogni PR.
- Le migrazioni definiscono chiavi UUID e vincoli `cascade/null` per mantenere l’integrità dei dati durante le evoluzioni.

## Documentazione e supporto

- Il servizio di pianificazione è documentato in `docs/WeeklyPannerService.md`; aggiorna questo file per ogni modifica sostanziale all’algoritmo.
