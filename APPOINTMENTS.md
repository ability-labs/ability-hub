# 📘 Appointments Planning (Detailed Specification)

## Title

**Ability Hub – Weekly Therapy Scheduling Rules and Algorithm**

---

## Objective

Automatically generate **weekly appointments** for each **Learner** (student/patient) to completely satisfy their required `weekly_minutes`, using their availability and operator priorities.

---

## Core Rules

### 1. Learner Weekly Requirement

Each learner must be scheduled for **enough sessions to cover `weekly_minutes`**.
The system **must never fail** unless **no operators have any available slots** in the week.

---

### 2. Scheduling Hierarchy

#### Step 1 – Base Availability

* Start from the **Learner’s declared slots** (`Learner::slots`).
* Each slot includes:

    * `week_day` (1 = Monday … 7 = Sunday)
    * `day_span` (“Morning” or “Afternoon”)
    * Start and end times.

#### Step 2 – Operator Priority

* Learners are assigned to multiple operators through the pivot table `learner_operator(learner_id, operator_id, priority)`.
* Operators are sorted by ascending priority (`1` = highest).
* For each learner slot:

    1. Try the **highest-priority operator** first.
    2. If busy, try the next one, and so on.

#### Step 3 – Progressive Fallbacks

If no assigned operator is available in the learner’s exact slot:

| Fallback Level | Strategy                 | Description                                                               |
| -------------- | ------------------------ | ------------------------------------------------------------------------- |
| **F1**         | Same day + same span     | Try other slots for the learner’s operators in the same day and day_span. |
| **F2**         | Same day, any span       | Try other slots in the same day, regardless of span.                      |
| **F3**         | Any day                  | Try other days of the week for the learner’s assigned operators.          |
| **F4**         | Global operator fallback | Use any available slot from any assigned operator in the week.            |

> The search continues until all weekly minutes are fulfilled.

#### Step 4 – One appointment per day

Each learner can have **only one appointment per day**.

#### Step 5 – Conflicts

A candidate slot is **invalid** if:

* The learner already has an appointment overlapping that time.
* The operator already has an appointment overlapping that time.

---

## Termination Conditions

* **Success:** the sum of created appointments equals or exceeds `weekly_minutes`.
* **Failure:** no free slot is found among any assigned operators (true capacity exhaustion).

---

## Determinism

To ensure reproducibility:

* Learners are processed in ascending `created_at` order.
* Operators are processed by ascending `priority`.
* Slots are ordered by weekday and time.

---

## Transaction Safety

Each learner’s scheduling runs in a database transaction:

* All appointments for that learner are created atomically.
* If no slots are found, the transaction is rolled back.

---

## Algorithm Summary (Pseudocode)

```text
for learner in learners ordered by created_at:
    remaining = learner.weekly_minutes - already_scheduled
    for each learner_slot:
        for each operator (priority ASC):
            if operator free in this slot → schedule
        fallback same_day + same_span
        fallback same_day any_span
    if still remaining:
        fallback any day (all operators)
    continue until remaining <= 0 or no free slots left
```

---

## Output

For each learner:

```json
{
  "learner_id": "uuid",
  "appointments": [
    {
      "operator_id": "uuid",
      "slot_id": "uuid",
      "starts_at": "2025-11-03T10:30:00Z",
      "ends_at": "2025-11-03T12:00:00Z",
      "duration_minutes": 90
    }
  ],
  "status": "complete | partial | failed",
  "remaining_minutes": 0
}
```

---

## Failure Case

Failure occurs **only if**:

* No assigned operators have **any available slot** in the week (`noAvailableCapacity`).
* This indicates a true lack of staffing, not a logic issue.

## Flowchart
```mermaid
A[Start Scheduling for Learner] --> B[Load learner.slots & learner.operators]
B --> C[Sort operators by priority (1=highest)]
C --> D[remaining = learner.weekly_minutes - already_scheduled]
D --> E{remaining > 0 ?}

E -->|No| Z1[✅ Already fulfilled weekly target → Exit]
E -->|Yes| F[Iterate learner slots (ordered by weekday/time)]

F --> G[Try operator with priority=1]
G --> H{Operator available in this slot?}

H -->|Yes| I[📅 Schedule appointment]
I --> J[Update remaining -= duration]
J --> K{remaining <= 0 ?}
K -->|Yes| Z2[✅ Weekly target reached → Exit]
K -->|No| F

H -->|No| L[Try next operator (priority+1)]
L --> M{More operators available?}
M -->|Yes| G
M -->|No| N[Fallback Level 1: same day + same span]

N --> O{Available slot for any assigned operator?}
O -->|Yes| I
O -->|No| P[Fallback Level 2: same day any span]

P --> Q{Available slot for any assigned operator?}
Q -->|Yes| I
Q -->|No| R[Fallback Level 3: any day, same operator set]

R --> S{Available slot for any assigned operator in the week?}
S -->|Yes| I
S -->|No| T[Fallback Level 4: any free slot from any assigned operator]

T --> U{Found free slot?}
U -->|Yes| I
U -->|No| V[🚫 No available capacity in the week]

V --> Z3[❌ Fail: true capacity exhaustion (hire more staff)]

Z2 --> ZEND[End scheduling for this learner]
Z1 --> ZEND
Z3 --> ZEND
```
### 🧩 Legend

|Symbol|Meaning|
|-|-|
|🔹 Blue nodes (A–F)|Initialization and looping logic|
|🔸 Orange nodes|Fallback layers|
|✅ Green|Success (weekly target reached or already fulfilled)|
|❌ Red|True failure – no operator availability|
|📅 Schedule|Appointment successfully created (transactional)|
