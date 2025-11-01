# AbilityHub Agent Guidelines

## General Principles

* Follow PSR-12 standards, use typing wherever possible, and keep UUID/trait usage consistent with existing models (`User`, `Learner`, `Operator`, `Appointment`).
* Prefer self-documenting code and update domain documentation (`docs/WeeklyPannerService.md`, README) whenever you introduce relevant changes.

## Backend

* Keep controllers thin by delegating business logic to Actions and Services, as already done for learners and weekly planning.
* Use Form Requests to validate inputs and normalize dates/formatting before touching the domain.
* Respect operator priorities and weekly minutes: any mutation must sync the pivots via the `SyncsOperatorsWithPriority` trait and update `weekly_minutes`.
* For new APIs or MCP channels, reuse services (`WeeklyPlannerService`, `DashboardDataService`, `PreferenceAssessmentService`) to keep a single source of truth.

## Frontend

* Continue using Blade components, AlpineJS, and Tailwind for dynamic interactions, following the existing patterns in the appointments calendar and reusable components.
* Maintain accessibility (e.g., `sr-only`, focus management) and compatibility with print/mobile where already supported by existing components.
* Remember that the current frontend will be complemented by a SPA/API-first app: isolate presentation logic into components and leverage Ziggy/Vite for modular assets.

## Database and Data

* Use UUIDs and foreign keys with `cascade/null` as in existing migrations; any new many-to-many relationship must follow the `availability_*` or `learner_operator` examples.
* Keep derived data in sync (appointment titles, `duration_minutes`, datasheet templates) using existing methods or by extending the relevant services.

## Localization and Accessibility

* For new strings, use Laravel localization and ensure the language selector keeps working both server-side and via AJAX.
* Keep usage of the `AppLocale` enum and translatable fields (discipline, reinforcer, datasheet type) consistent.

## API-first and MCP Readiness

* Design new features with three channels in mind (Blade, API, MCP): expose DTOs/resource layers where needed and avoid duplicating logic that already exists in services.
* When you introduce protected APIs, align authentication with Sanctum (first-party cookies + third-party tokens) and reuse existing validators to avoid drift across channels (even if the dependency is not yet installed, plan the middleware accordingly).

## Tooling and Quality

* Use the `composer run dev` script for local environments and keep Vite/Tailwind/Alpine dependencies up to date.
* Run Pint and PHPUnit regularly; add tests for critical services or actions (e.g., planner, availability) whenever you modify them.
