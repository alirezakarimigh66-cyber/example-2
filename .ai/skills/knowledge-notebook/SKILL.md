---
name: knowledge-notebook
description: Consult and maintain Dopamin Market feature knowledge, routes, and implementation references before project work and after completed tasks.
---

# Knowledge Notebook

Read this index before project work, then consult the relevant feature notes. Source code is authoritative; verify notes against it when making changes.

## Current project

Dopamin Market is a Persian RTL Laravel application with phone/password authentication and protected user/admin dashboards. Administrators can manage products and categories. The public market catalog and checkout are not implemented yet.

- [Project baseline](features/project-baseline.md): public homepage, health endpoint, and initial application structure.
- [Phone authentication](features/authentication.md): shared login/register modal, normalized Iranian mobiles, session security, role dashboards, and secure administrator setup.

- [Product management](features/product-management.md): administrator-only product/category CRUD, search/filter/pagination, private image uploads and cleanup, and RTL management screens.

## Maintaining the notebook

After each completed task, update the relevant notes and this index to reflect the implemented state. For a new feature, create `features/{feature_name}.md` and add a brief description and relative link here. Update existing feature notes instead of duplicating them. For tasks without application changes, record relevant verified knowledge without inventing a feature or route.

Each feature note must include:

- What the feature does and how its main flow works.
- Related routes: HTTP methods, paths, names, and access requirements where applicable.
- Related controllers or Livewire components, views, models, and services, with repository-relative paths. Mark unused layers as not applicable.
- Important behavior, dependencies, limitations, and relevant tests when present.

Document verified implementation only. Distinguish planned work from existing behavior, remove stale details as features change, and never store secrets or personal data. Keep notes concise and reference source files rather than copying code.
