# Resoconto Valutazione Effort Progetto: AbilityHub

Il presente documento fornisce una stima analitica dell'impegno profuso nello sviluppo del software **AbilityHub**, basata sull'analisi della cronologia dei commit (milestone) e sul volume di codice prodotto.

## Metodologia di Calcolo
La stima è stata effettuata incrociando i seguenti dati:
*   **Giorni attivi di sviluppo:** Numero di giorni solari in cui sono stati registrati commit.
*   **Volume di codice:** File modificati, righe inserite e rimosse (churn).
*   **Complessità funzionale:** Analisi del dominio (es. gestione calendari, logica ABA, multi-partecipazione).
*   **Frequenza dei commit:** Indicatore del ritmo di lavoro e della rifinitura.

**Tariffa Oraria Simbolica applicata:** 25,00 €/ora

---

## Dettaglio Milestone

### 1. Scaffolding Iniziale e Core Funzionale
*   **Periodo:** 10 Febbraio 2025 – 25 Febbraio 2025
*   **Attività:** Inizializzazione Laravel, setup DB (UUIDs, Pivot), architettura base (Learner, Operator), autenticazione e UI Dashboard iniziale.
*   **Metriche:** 76 commit in 8 giorni di lavoro intensivo. ~9.000 righe di codice.
*   **Effort Stimato:** **45 ore**
*   **Costo Stimato:** 1.125,00 €

### 2. Sviluppo Modulo "Preference Assessment" (ABA)
*   **Periodo:** 25 Febbraio 2025 – 06 Giugno 2025
*   **Attività:** Implementazione logica per Test di Preferenza ABA (Single Item, ecc.). Sebbene la sezione sia rimasta parziale, lo sviluppo della logica di assessment è stato complesso.
*   **Metriche:** 69 commit in 6 giorni di lavoro. ~5.500 righe di codice.
*   **Effort Stimato:** **35 ore**
*   **Costo Stimato:** 875,00 €

### 3. Pivot Strategico e Planning Settimanale (V1)
*   **Periodo:** 06 Giugno 2025 – 18 Settembre 2025
*   **Attività:** Spostamento del focus verso le esigenze reali del committente. Sviluppo del motore di pianificazione appuntamenti e disponibilità operatori.
*   **Metriche:** 28 commit in 8 giorni di lavoro. ~3.300 righe di codice.
*   **Effort Stimato:** **35 ore**
*   **Costo Stimato:** 875,00 €

### 4. Raffinamento Planner e Consolidamento
*   **Periodo:** 18 Settembre 2025 – 11 Novembre 2025
*   **Attività:** Bugfixing del planner, miglioramento delle performance, refactoring architetturale per garantire stabilità dopo i primi "test su strada".
*   **Metriche:** 37 commit in 7 giorni di lavoro. ~5.100 righe di codice (alto volume di refactoring).
*   **Effort Stimato:** **30 ore**
*   **Costo Stimato:** 750,00 €

### 5. Evoluzione Multi-Partecipante e Tipologie Appuntamento
*   **Periodo:** 11 Novembre 2025 – 03 Marzo 2026
*   **Attività:** Introduzione del supporto per più operatori/studenti per singolo appuntamento e gestione delle diverse tipologie di prestazione.
*   **Metriche:** 11 commit in 4 giorni di lavoro. ~4.000 righe di codice.
*   **Effort Stimato:** **20 ore**
*   **Costo Stimato:** 500,00 €

### 6. Finalizzazione UX/UI e Correzioni Finali
*   **Periodo:** 03 Marzo 2026 – 26 Marzo 2026
*   **Attività:** Revisione layout grafico, ottimizzazione mobile, risoluzione problemi date/ora legale e ultime rifiniture.
*   **Metriche:** 27 commit in 3 giorni di lavoro. ~4.900 righe di codice.
*   **Effort Stimato:** **15 ore**
*   **Costo Stimato:** 375,00 €

---

## Riepilogo Totale

| Voce | Valore |
| :--- | :--- |
| **Totale Ore Lavorate** | **180 ore** |
| **Tariffa Oraria (Simbolica)** | 25,00 €/h |
| **VALORE ECONOMICO TOTALE** | **4.500,00 €** |

> [!NOTE]
> Il calcolo si riferisce esclusivamente allo sviluppo software diretto. Non include le ore di analisi requisiti extra-coding, il supporto post-rilascio o i costi di infrastruttura.
