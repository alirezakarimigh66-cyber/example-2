# Project baseline

Initialized on October 24, 2026. The application began as a fresh Laravel starter. Phone authentication and role dashboards were implemented on October 26, 2026; see [authentication](authentication.md).

## Existing behavior and routes

| Method | Path | Name | Behavior | Source |
| --- | --- | --- | --- | --- |
| GET / HEAD | `/` | `home` | Renders the public account-entry homepage with the shared authentication modal for guests. | `routes/web.php` |
| GET / HEAD | `/up` | Framework-managed | Laravel health endpoint configured during application bootstrap. | `bootstrap/app.php` |

## Implementation

- Controller: not applicable to the homepage; its route renders a view directly.
- View: `resources/views/welcome.blade.php` contains the public account-entry homepage.
- Model: not applicable to the homepage. `app/Models/User.php` now supports phone authentication and roles; see the authentication note.
- Service: no feature-specific service is used by the homepage.
- Routing configuration: `bootstrap/app.php` loads `routes/web.php` and `routes/console.php` and configures `/up`.
- Console: `routes/console.php` defines the starter `inspire` command; it is not a website route.

## Notebook initialization

The entrypoint is `.ai/skills/knowledge-notebook/SKILL.md`. Feature notes live in `.ai/skills/knowledge-notebook/features/`. Initialization adds documentation only, with no application routes or behavior changes.
