# Phone authentication and role dashboards

Implemented October 26, 2026 on Laravel 13.38 / PHP 8.5, with Blade and Vite/Tailwind 4. No new dependencies or Livewire components.

## Flow

The public homepage and shared header open one anonymous Blade authentication modal. Reuse `<x-auth-trigger mode="login|register">` inside `<x-layout>` to open it from another public location. GET entry routes also open the shared modal; there are no separate authentication pages.

The native dialog provides background inertness, focus containment, Escape dismissal, backdrop/close-button dismissal, focus restoration, keyboard tabs, and a scrollable mobile layout. Switching preserves phone/name and clears passwords. Fetch submits CSRF-protected forms with inline errors and pending/duplicate-submit protection. Standard POST/redirect submissions also display errors and reopen the correct mode. The Persian RTL interface follows the project’s purple-gray glass style with system dark-mode support.

Registration collects name, phone, password, and confirmation; email is not collected. Normalize local `09`, `+98`, `0098`, or `98` prefixes, spaces/hyphens/parentheses and Persian/Arabic digits to `+989xxxxxxxxx`. Validation requires an Iranian mobile number; a database unique index protects phone uniqueness. New passwords require 12+ characters, mixed-case Latin letters, a number, at most 72 bytes, and no null bytes for bcrypt compatibility.

Public registration explicitly assigns `UserRole::User`; `role` is excluded from the model’s fillable attributes. Login only queries phone/password and uses an identical failure message for unknown phones and bad passwords. Failed attempts are limited to five per normalized phone/IP per minute; broader IP throttles protect login (30/minute) and registration (5/minute). Success regenerates the session and returns the stored role’s dashboard URL, discarding arbitrary intended/external destinations. Logout invalidates the session and regenerates the CSRF token. Cross-role dashboard requests return 403.

## Routes

| Method | Path | Name | Access / behavior |
| --- | --- | --- | --- |
| GET | `/` | `home` | Public homepage, shared auth modal for guests |
| GET | `/login` | `login` | Guest redirect to homepage `auth=login` |
| GET | `/register` | `register` | Guest redirect to homepage `auth=register` |
| POST | `/login` | `login.store` | Guest phone/password login, throttled |
| POST | `/register` | `register.store` | Guest registration, throttled |
| POST | `/logout` | `logout` | Authenticated session logout |
| GET | `/dashboard` | `dashboard` | Authenticated regular users only; own account details |
| GET | `/admin/dashboard` | `admin.dashboard` | Administrators only; own account details and total account count |

Authenticated visitors to guest routes redirect to their own dashboard. Guests requesting protected HTML pages redirect to `home?auth=login`; JSON requests receive 401. POST routes use web CSRF protection.

## Source map / task files

Created:

- `app/UserRole.php`: role enum and centralized dashboard route mapping.
- `app/Support/IranianMobile.php`: shared normalization and phone validation.
- `app/Rules/HashablePassword.php`: bcrypt byte/null-byte validation.
- `app/Http/Requests/Auth/{LoginRequest,RegisterRequest}.php`: server validation, login attempts.
- `app/Http/Controllers/Auth/{SessionController,RegisteredUserController}.php`: login, logout, registration.
- `app/Http/Controllers/DashboardController.php`: account view and admin-only account count.
- `app/Http/Middleware/EnsureRole.php`: server role enforcement.
- `app/Console/Commands/CreateAdmin.php`: trusted operator administrator creation.
- `database/migrations/2026_10_26_153946_add_phone_and_role_to_users_table.php`: additive phone/role schema and nullable email.
- `resources/views/components/{layout,auth-modal,auth-trigger}.blade.php`: shell, one modal, reusable triggers.
- `resources/views/dashboard.blade.php`: role-specific dashboard content.
- `tests/Feature/{AuthenticationTest,CreateAdminTest}.php`: authentication/authorization and operator-command coverage.

Modified:

- `app/Models/User.php`: guarded role, role enum cast, phone mutator.
- `database/factories/UserFactory.php`: unique mobile, user role, admin state.
- `bootstrap/app.php`: role middleware and guest/authenticated redirects.
- `routes/web.php`: named authentication/dashboard routes.
- `resources/views/welcome.blade.php`, `resources/css/app.css`, `resources/js/app.js`: public entry, styles, modal behavior.
- `.gitignore`: exclude local `.taste-skill/` design/browser artifacts and isolated test database.
- This notebook index and baseline note.

Service layer: not applicable; small operations remain in their controllers/requests. No email verification, password reset, phone ownership verification/OTP, or account editing is implemented. Admin navigation now includes product and category management; see [product management](product-management.md).

## Migration and operations

- Run `php artisan migrate --no-interaction` on each target environment. It was applied locally during this task.
- Existing user IDs, emails, password hashes, and related data are preserved. Existing accounts receive role `user` and nullable phone; a trusted operator must assign a verified unique normalized phone before those accounts can use phone login. Never fabricate phone numbers or permit public claiming of an existing email account.
- Rollback drops phone/role data and deliberately leaves email nullable, because phone-only accounts cannot regain a non-null email automatically. Prefer forward migrations in deployed environments.
- Create the initial administrator with `php artisan app:create-admin 09xxxxxxxxx --name="Administrator"`. Two hidden prompts collect and confirm the password. There is no password argument, hard-coded credential, automatic administrator seeder, or public role selector. The command refuses existing phone accounts rather than promoting or overwriting them.
- `php artisan db:seed` is optional starter data only; it does not provision an administrator. No seeder was added for this feature.
- Development: `composer run dev`, or `php artisan serve` and `npm.cmd run dev` in separate Windows terminals. Build: `npm.cmd run build`.
- Deployment: HTTPS, `APP_ENV=production`, `APP_DEBUG=false`, correct `APP_URL`, persistent session/cache stores, `SESSION_SECURE_COOKIE=true`, HTTP-only cookies, and SameSite=Lax. Set session domain only as required. No dependency changes.
- A stale ignored `public/hot` pointed at an unavailable Vite server during verification; it was moved to ignored `.taste-skill/hot.previous` so built assets are served. Starting Vite recreates it.

## Verification

Run `php artisan test --compact tests/Feature/AuthenticationTest.php tests/Feature/CreateAdminTest.php tests/Feature/ExampleTest.php`, `php vendor/bin/pint --dirty --format agent`, and `php vendor/bin/phpstan analyse --memory-limit=512M`. Run the full suite with `php artisan test --compact` when desired.

Real headless Chromium/Edge checks used the installed design skill’s CDP helper and an isolated SQLite database, rather than adding a browser-test dependency. Widths 1440/768/390 verified modal opening, switching, phone preservation/password clearing, focus containment/restoration, password visibility, failed login errors, Escape, no overflow or JavaScript exceptions. Actual registration, user dashboard redirect, logout and canonical-phone login also passed. Screenshots and temporary checks live under ignored `.taste-skill/`. Backend tests cover admin redirects and both dashboard role boundaries. The design inspector had no failures; glass warnings are intentional to match the project brand.

Final results: 41 focused feature tests passed (190 assertions), Pint passed, PHPStan passed with zero errors, frontend build passed, and git diff whitespace checks passed. Additional real-browser checks passed for 419/429/500 and network-error recovery, duplicate-submission prevention, backdrop dismissal, and light-mode fit in a 390×700 viewport. The isolated test server was stopped after verification. The complete test suite was not run.
