=== Dynamic ElementKit ===
Contributors: mdrahimapurbo
Tags: woocommerce, elementor, page builder, ecommerce, shop
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 2.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Elementor-powered WooCommerce toolkit for dynamic product, shop, cart, checkout, and order-confirmation page designs.

== Description ==

Dynamic ElementKit is a powerful plugin that integrates Elementor with WooCommerce, allowing you to create stunning custom shop pages, product grids, and product sliders using Elementor's drag-and-drop interface.

= Key Features =

* **Elementor Integration** - Seamlessly works with Elementor page builder
* **Product Grid Widget** - Display products in beautiful grid layouts
* **Product Slider Widget** - Create responsive product carousels
* **Shop Page Builder** - Customize your entire shop page with Elementor
* **Cart and Checkout Widgets** - Build custom purchase flows with Elementor
* **Single Product Widgets** - Add product title, price, rating, metadata, tabs, and purchase controls
* **Dynamic Product Tags** - Use WooCommerce product data in Elementor content
* **Template Management** - Create and activate WooCommerce templates from the dashboard
* **WooCommerce Compatible** - Works with WooCommerce-compatible themes

= Requirements =

* WordPress 5.8 or higher
* WooCommerce 6.0 or higher
* Elementor 3.0 or higher
* PHP 7.4 or higher

= How It Works =

1. Install and activate the plugin
2. Go to Dynamic ElementKit in your WordPress dashboard
3. Create or activate a WooCommerce template
4. Start building your custom WooCommerce pages with Elementor widgets

== Installation ==

= Automatic Installation =

1. Log in to your WordPress dashboard
2. Navigate to Plugins > Add New
3. Search for "Dynamic ElementKit"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Go to Plugins > Add New > Upload Plugin
3. Choose the zip file and click "Install Now"
4. Activate the plugin

= From GitHub =

1. Clone or download the repository
2. Upload the `dynamic-elementkit` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' screen in WordPress

== Frequently Asked Questions ==

= Does this plugin work with any theme? =

Yes, Dynamic ElementKit works with any theme that is compatible with WooCommerce and Elementor.

= Is Elementor required? =

Yes, this plugin requires Elementor to be installed and active. It won't function without Elementor.

= Can I customize the product grid layout? =

Yes, the Product Grid widget provides various customization options including columns, spacing, and styling.

= How do I update the plugin from GitHub? =

Enter your GitHub repository URL in the plugin settings page to enable automatic updates.

== Changelog ==

= 2.2.7 =
* Allow authenticated Elementor previews for auto-draft templates.

= 2.2.6 =
* Moved frontend template rendering into a dedicated modular renderer class
* Reduced `templates/override.php` to a safe, minimal entry point

= 2.2.5 =
* Fixed Elementor preview 404 errors by resolving `elementor-preview` template IDs directly

= 2.2.4 =
* Fixed Elementor authenticated previews for Header, Footer, and other non-landing templates
* Kept non-landing templates private while allowing their Elementor editor canvas to load

= 2.2.3 =
* Restored standard right-side spacing for template and landing-page tables

= 2.2.2 =
* Restricted public URLs to Landing Pages only; headers, footers, and templates no longer use `/landing/`
* Removed the URL column from the generic Templates screen
* Made active Elementor headers and footers replace common theme header/footer containers

= 2.2.1 =
* Refined the dashboard with balanced quick-access cards and clear primary actions

= 2.2.0 =
* Replaced the landing product dropdown with a memory-safe AJAX product search
* Searches only matching products and limits each result set to 20 items

= 2.1.9 =
* Moved the separate Landing Pages menu directly below WordPress Pages

= 2.1.8 =
* Simplified the Landing Pages screen to use the standard WordPress page heading and action button

= 2.1.7 =
* Moved Landing Pages to its own top-level WordPress admin menu

= 2.1.6 =
* Added a dedicated Landing Pages admin menu and creation flow
* Removed product assignment from the generic template creator
* Added consistent global admin buttons, fields, modal, and toggle styles

= 2.1.5 =
* Added modular Core, Admin, Assets, Elementor, Frontend, and WooCommerce module boundaries
* Centralized module registration and compatibility loading

= 2.1.4 =
* Expanded templates beyond WooCommerce with General Page, Site Header, Site Footer, and Landing Page types
* Added site-wide Elementor header and footer rendering
* Updated the dashboard UI and copy for general WordPress website design

= 2.1.3 =
* Added product assignment for landing templates
* Connected assigned products to single-product widgets, checkout, and product dynamic tags

= 2.1.2 =
* Added public `/landing/{slug}/` URLs for published templates
* Added landing URL links to the template table
* Added automatic rewrite refresh after upgrade

= 2.1.1 =
* Added template slug creation
* Redirected new templates directly to the Elementor editor
* Added Elementor custom-post-type support for Dynamic ElementKit templates

= 2.1.0 =
* Refreshed and standardized the admin branding
* Added the Dynamic ElementKit Dashboard landing page
* Added Dashboard, Templates, and Settings admin navigation
* Added an option to enable or disable the landing page

= 2.0.1 =
* Added CSRF protection to custom checkout AJAX requests
* Restricted watermark uploads to validated image files below 2 MB
* Added security deployment guidance

= 2.0.0 =
* Rebranded and reorganized as Dynamic ElementKit
* Added a structured `src/Elementor` code layout
* Preserved legacy Elementor widget identifiers for existing pages
* Added migration support for legacy template post types

== Upgrade Notice ==

= 1.0.0 =
Initial release. Install now to start building custom WooCommerce pages with Elementor!

== Screenshots ==

Coming soon.

== Documentation ==

For detailed documentation, visit our [GitHub repository](https://github.com/mdrahimapurbo/dynamic-elementkit).

== Support ==

For support, please visit our [GitHub Issues page](https://github.com/mdrahimapurbo/dynamic-elementkit/issues).

== Contributing ==

We welcome contributions! Please feel free to submit issues and pull requests on our [GitHub repository](https://github.com/mdrahimapurbo/dynamic-elementkit).
