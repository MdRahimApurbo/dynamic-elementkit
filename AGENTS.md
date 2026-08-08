# Dynamic ElementKit contributor guide

## Project purpose

Dynamic ElementKit is a WordPress plugin that adds Elementor widgets, dynamic tags, WooCommerce templates, public landing templates, and site-wide header/footer templates.

## Important files

- `dynamic-elementkit.php` — plugin bootstrap, hooks, post type, routing, WooCommerce integration, and compatibility layer.
- `src/class-admin-page.php` — Dynamic ElementKit admin menu, dashboard, settings, and template creation workflow.
- `src/class-template-table.php` — template list table and admin actions.
- `src/Elementor/Widgets/` — Elementor widget implementations.
- `src/Elementor/DynamicTags/` — Elementor dynamic tag implementations.
- `src/Modules/` — module registration boundaries for Admin, Assets, Elementor, Frontend, WooCommerce, and Core loading.
- `src/Modules/Frontend/class-template-renderer.php` — reusable Elementor/WordPress template renderer used by the thin override entry point.
- `templates/override.php` — frontend Elementor document rendering override.
- `assets/` — frontend and admin CSS/JavaScript.
- `SECURITY.md` — security expectations and the ClickFix/malware response checklist.

## Naming and compatibility

- Use `DEK_`, `dek_`, and the `dynamic-elementkit` text domain for new code.
- Keep legacy `wpb-*` Elementor widget IDs, CSS classes, and script handles unless a migration is intentionally added; existing Elementor documents depend on them.
- The public plugin identity is Dynamic ElementKit. The old WooCommerce Page Builder name must not be reintroduced in visible UI.

## Template behavior

- Published templates use the `dek_template` post type.
- Only templates with the `landing` type are public and use `/landing/{slug}/`; headers, footers, and generic templates must never expose a public landing URL.
- Landing templates may be assigned a WooCommerce product through `_dek_landing_product_id`.
- Landing product assignment must use the AJAX search endpoint; do not preload the full product catalog into an admin form.
- The assigned landing product must resolve consistently in single-product widgets, the checkout widget, and product dynamic tags.
- Site headers and footers use the `header` and `footer` template types and render through `wp_body_open` and `wp_footer`.
- Template creation must remain nonce-protected and restricted to administrators.

## Security rules

- Verify nonces and capabilities for every admin or AJAX state-changing action.
- Sanitize input and escape output at the final rendering point.
- Never trust product IDs, prices, variation data, or checkout payloads from the browser; validate them with WooCommerce on the server.
- Keep signed checkout payloads and checkout cache protections intact.
- Restrict uploads by MIME, size, and WordPress upload validation. Never add executable upload types.
- Do not add clipboard, PowerShell, Terminal, or “verify you are human” instructions to any plugin page.

## Verification

Before packaging:

1. Run `node --check` on changed JavaScript files.
2. Run PHP lint with the project PHP binary when available: `php -l <file>`.
3. Run `git diff --check`.
4. Inspect the ZIP and confirm it contains one top-level `dynamic-elementkit/` directory, the main plugin file, `src/`, `templates/`, `assets/`, and no `.git`, `.DS_Store`, or staging directories.
5. Test activation, Elementor editing, template publishing, `/landing/{slug}/`, header/footer rendering, and checkout on a local WordPress site.

## Git workflow

- Work on a branch named `codex/<short-description>`; do not commit feature work directly to `main`.
- Review `git status` and `git diff` before staging.
- Stage only source, documentation, and intentional package files. Do not commit generated build directories or historical ZIPs unless explicitly requested.
- Use focused commits with imperative messages, for example: `Add general Elementor header and footer templates`.
- Never use `git reset --hard`, `git checkout --`, or broad deletion commands to clean up user work.
