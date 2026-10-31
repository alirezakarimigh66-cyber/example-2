# Administrator product and category management

Implemented October 31, 2026 using Laravel 13.38, PHP 8.5, Blade, and Tailwind 4. No dependencies or Livewire components added. The public storefront and checkout remain unimplemented.

## Behavior

Administrators open Products or Categories from the admin dashboard/navigation. All product/category pages, mutations, and image responses require `auth` and `role:admin`. Guests redirect to the shared login modal (JSON requests receive 401); regular users receive 403. Web mutations retain CSRF protection.

The inventory lists newest products first, 15 per page, with eager-loaded categories, name substring search, and category filtering. Filters combine and persist in pagination links. Zero is a valid search term. The interface uses Persian RTL text, the existing purple-gray framed dashboard, system light/dark mode, and a horizontally scrollable product table on narrow screens.

Products contain a required name, category, decimal price in tomans (up to two decimal places), nonnegative integer stock, and active/inactive status. Description and image are optional. New product forms start inactive with zero stock. Detail pages expose the complete product information. Forms retain validated text after errors and show Persian error summaries; file inputs must be selected again. Product/category names and descriptions are escaped in Blade.

The create-categories migration installs the three requested categories in Persian: Bodybuilding Supplements (`مکمل‌های بدنسازی`), Pills and Tablets (`قرص‌ها و تبلت‌ها`), and Vitamins and Minerals (`ویتامین‌ها و مواد معدنی`). `CategorySeeder` is idempotent by name and is called by `DatabaseSeeder`; migrations supply the defaults without requiring a full seed. Administrators can add, rename, and delete unused categories. Categories with products cannot be deleted: reassign those products first. A restrictive foreign key also protects against concurrent assignment/deletion.

Deletion opens a native confirmation dialog naming the object, with Cancel initially focused, Escape dismissal, and duplicate-confirm protection. The form is submitted only after confirmation. `resources/js/admin-products.js` is imported by the existing application entrypoint.

## Image storage and failure handling

Uploads accept content-validated JPG/JPEG, PNG, or WebP, at most 4 MiB and 6000 × 6000 pixels. SVG and GIF are rejected. Laravel generates filenames under `products/` on the private `local` disk (`storage/app/private`); request-supplied image paths are ignored. No public storage symlink is needed. Images are streamed through the administrator-only image route with `nosniff`, private/no-store caching, and a restrictive CSP.

`ProductManager` writes new uploads before a database transaction, locks existing product rows during updates/deletion, removes the new upload if persistence fails, and deletes replaced/removed/deleted images after the product transaction succeeds. Editing without an upload preserves the saved image; an explicit removal checkbox clears it. A new upload takes precedence over removal if both are submitted. Failed cleanup is reported/logged; there is no background cleanup retry. Missing image files return 404. A future public catalog needs a deliberate public image-delivery design.

## Routes

All routes below are in `routes/web.php`, under administrator middleware. GET routes also accept HEAD.

| Method | Path | Name | Behavior |
| --- | --- | --- | --- |
| GET | `/admin/products` | `admin.products.index` | Searchable, filtered, paginated inventory |
| GET | `/admin/products/create` | `admin.products.create` | New product form |
| POST | `/admin/products` | `admin.products.store` | Validate and create |
| GET | `/admin/products/{product}` | `admin.products.show` | Complete product detail |
| GET | `/admin/products/{product}/edit` | `admin.products.edit` | Edit form |
| PUT/PATCH | `/admin/products/{product}` | `admin.products.update` | Validate and update |
| DELETE | `/admin/products/{product}` | `admin.products.destroy` | Delete product and image |
| GET | `/admin/products/{product}/image` | `admin.products.image` | Private image stream |
| GET | `/admin/categories` | `admin.categories.index` | Paginated categories, product counts, and create form |
| POST | `/admin/categories` | `admin.categories.store` | Create category |
| GET | `/admin/categories/{category}/edit` | `admin.categories.edit` | Rename form |
| PUT/PATCH | `/admin/categories/{category}` | `admin.categories.update` | Rename category |
| DELETE | `/admin/categories/{category}` | `admin.categories.destroy` | Delete unused category |

`GET /admin/dashboard` (`admin.dashboard`) now links to both management areas. There are no public product/category endpoints.

## Source map

- Models: `app/Models/{Product,Category}.php`; backed enum: `app/ProductStatus.php`.
- Controllers: `app/Http/Controllers/Admin/{ProductController,ProductImageController,CategoryController}.php`.
- Validation: `app/Http/Requests/Admin/{ProductRequest,ProductIndexRequest,CategoryRequest}.php`.
- Storage/persistence service: `app/Support/ProductManager.php`.
- Migrations: `database/migrations/2026_10_31_143803_create_categories_table.php` and `2026_10_31_143805_create_products_table.php`.
- Factories: `database/factories/{ProductFactory,CategoryFactory}.php`; seeder: `database/seeders/CategorySeeder.php`.
- Shared views: `resources/views/components/{admin-layout,admin-errors}.blade.php`.
- Product views: `resources/views/admin/products/{index,form,show}.blade.php`.
- Category views: `resources/views/admin/categories/{index,edit}.blade.php`.
- Integration: `resources/views/dashboard.blade.php`, `resources/css/app.css`, `resources/js/{app,admin-products}.js`.

## Operations and verification

Run `php artisan migrate --no-interaction` on other environments. The two additive migrations were applied to the local database. No administrator or sample products are provisioned by these migrations. Existing admin provisioning remains the trusted `app:create-admin` command documented in the authentication note.

Focused coverage: `tests/Feature/{ProductManagementTest,CategoryManagementTest}.php` covers all route role boundaries, CRUD, default categories, validation, escaped output, combined filters/pagination, private image responses, upload rejection/replacement/removal, rollback cleanup, and protection of in-use categories. Tiny PNG/GIF fixtures in `tests/Fixtures/` allow uploads to be tested without PHP GD.

The 106 feature tests covering product/category management and the existing authentication/dashboard behavior passed (428 assertions). The final focused suite passed again: 65 tests, 239 assertions. PHPStan passed with zero errors; Pint and the Vite build passed. Real headless browser checks exercised actual create/upload/image display/edit/delete, Cancel/Escape confirmation handling, search empty state, Persian rendering, light/dark mode, and no document overflow at 1440/768/390 widths. Browser fixtures, screenshots, design reports, and an isolated SQLite database are ignored under `.taste-skill/products/`. Final design inspection has no failures; the category screen retains the existing minimum-height dashboard canvas when its three real rows leave unused space.

A stale `public/hot` referenced an inactive Vite server, preventing CSS/JS from loading. It was moved to `.taste-skill/products/hot.stale-20261031` so built assets are used; starting Vite recreates the marker. The build still emits the pre-existing optional-fontaine notice; no dependency was added. Run `php artisan test --compact` for the complete project suite.
