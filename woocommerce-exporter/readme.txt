=== Store Exporter – Export WooCommerce Products, Orders, Subscriptions, Customers ===

Contributors: jkohlbach, RymeraWebCo, visser.labs, smub
Tags: woocommerce export, export products, export orders, export subscriptions, csv excel export
Requires at least: 2.9.2
Tested up to: 7.1
Requires PHP: 5.6
Stable tag: 2.8.2
License: GPLv2 or later

Export WooCommerce products, orders, customers, categories, tags, subscriptions & more into formatted files like CSV, XML, Excel 2007, XLS, XLSX.

== Description ==

= The Best WooCommerce Export Plugin – Products, Orders, Subscriptions, Users, and More! =

[Get Premium](https://visser.com.au/) (Scheduled exports + more features) | [Documentation](https://visser.com.au/kb-categories/woocommerce-store-exporter-deluxe/)

**Export WooCommerce Products, Orders, Categories, Tags, Users and other store details into Excel spreadsheets that suit your store requirements.**

Simply click Quick Export to produce concise, ready to use Excel spreadshseets. 

Screen-loads of export fields, filters and options are available to customise each export type, with native export integration for 125+ WooCommerce Plugins.

This WooCommerce export Plugin maintains compatibility with the latest WooCommerce release through regular Plugin updates, we also proudly maintain compatibility with legacy releases of WooCommerce.

Supported since 2012 Store Exporter for WooCommerce is maintained by an active community of store owners and developers providing feature suggestions and feedback.

= WooCommerce export types =

* Export Products
* Export Categories
* Export Tags
* Export Brands (*)
* Export Orders
* Export Customers (*)
* Export Users
* Export Coupons (*)
* Export Subscriptions (*)
* Export Product Vendors (*)
* Export Bookings (*)
* Export Commissions (*)
* Export Shipping Classes (*)
* Export Attributes (*)

= WooCommerce export features =

&#128312; Toggle and save export fields
&#128312; Field label editor (*)
&#128312; Scheduled exports (*)
&#128312; Export templates (*)
&#128312; Compatible with WordPress Multisite 
&#128312; Compatible with WPML (WordPress Multilingual) (*)
&#128312; Compatible with WP-CLI (*)
&#128312; Export to CSV file
&#128312; Export to TSV file (*)
&#128312; Export to Excel 2007 (XLS) file (*)
&#128312; Export to Excel 2013 (XLSX) file (*)
&#128312; Export to XML file (*)
&#128312; Export to RSS file (*)
&#128312; Export to WordPress Media
&#128312; Export to e-mail addresses (*)
&#128312; Export to fixed filename (*)
&#128312; Export to remote FTP/FTPS/SFTP (*)
&#128312; Export to remote POST (*)
&#128312; Supports external CRON (*)
&#128312; WP-CLI support (*)

= See our WooCommerce export Plugin in action =

[youtube https://www.youtube.com/watch?v=xYeP2eDEOM4]

(Thanks to Martha Waugh!)

= WooCommerce export filters =

* Export Products by Product Category/Tag
* Export Products by Product Status
* Export Products by Type including Variations
* Export Products by Stock Status/Quantity
* Export Products by Featured
* Export Products by Date Modified
* Export Orders by Order Date
* Export Orders by Order Status (*)
* Export Orders by Customer (*)
* Export Orders by Billing/Shipping Country (*)
* Export Orders by Product (*)
* Export Orders by Product Category/Tag/Brand (*)
* Export Orders by Order ID/Invoice Number (*)
* Export Orders by Payment Gateway/Shipping Method (*)
* Export Orders by Coupon Code (*)
* Export Customers by Order Status (*)
* Export Customers by User Role (*)

(*) Requires the Pro upgrade to enable additional store export functionality.

= Native export integration with 125+ WooCommerce Plugins =

Just a few of the features unlocked in the [Pro upgrade of Store Exporter](https://visser.com.au/plugins/store-exporter-deluxe/?platform=wc) include:

- Export All in One SEO Pack
- Export Advanced Google Product Feed
- Export Product Addons
- Export Sequential Order Number Pro
- Export Checkout Manager
- Export Checkout Manager Pro
- Export Checkout Field Editor
- Export Cost of Goods
- Export Per-Product Shipping
- Export Print Invoice & Delivery Note
- Export Local Pickups Plus
- Export WooCommerce Subscriptions
- Export Checkout Field Manager
- Export Currency Switcher
- Export WooCommerce PDF Invoices & Packing Slips
- Export WooCommerce Checkout Add-ons
- Export Product Vendors
- Export WooCommerce Jetpack
- Import fields for Product Importer Deluxe

... and many more free and Premium extensions for WooCommerce and WordPress.

[For more information visit our site.](https://visser.com.au/plugins/woocommerce-export/)

Happy exporting! :)

== Installation ==

1. Upload the folder 'woocommerce-exporter' to the '/wp-content/plugins/' directory
2. Activate 'WooCommerce - Store Exporter' through the 'Plugins' menu in WordPress

See Usage section before for instructions on how to generate export files.

== Usage ==

1. Open WooCommerce > Store Export from the WordPress Administration
2. Select the Export tab on the Store Exporter screen
3. Select which export type and WooCommerce details you would like to export
4. Click Export
5. Download archived copies of previous exports from the Archives tab

Done!

== Support ==

If you have any problems, questions or suggestions please create a topic here on the Store Exporter dedicated forum.

[Store Exporter on w.org](https://wordpress.org/support/plugin/woocommerce-exporter)

== Frequently asked questions ==

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/c7ebe92f-31e4-484c-a3e5-49e94b69dbaf)

== Screenshots ==

1. The Overview screen is the central hub for managing your export operations.
2. Each export type contains its own fields, filters and options.
3. Select the Product fields to be included in the export, selections are remembered.
4. Filters are available for each export type.
5. Adjust options to suit different languages, file formats and column formatting.
6. Native export integration with 100+ WooCommerce and WordPress Plugins.
7. Use the Field Editor to label export columns
8. Manage Plugin options from the Settings screen.

== Changelog ==

= 2.8.2 =
* Feature: Register Store Exporter features with the WordPress Abilities API so AI assistants can use them through MCP
* Feature: New AI / MCP screen to set up StoreAgent and allow or block AI changes
* Improvement: Product education page for the Advanced Coupons free plugin
* Improvement: Help buttons link to the matching documentation article for each screen
* Improvement: Add a filter to raise the 200-term cap on quick export filter dropdowns
* Improvement: CSV/TSV cells are now quoted by default ("Escape all cells"). Set field escape formatting to "Escape cells as Excel would" to keep the previous output
* Improvement: The product category and tag "Filter out" option is now applied, so a store that saved it gets the opposite set of products
* Improvement: Brand export, brand count and the product brand column include every brand taxonomy (WooCommerce Brands, Perfect WooCommerce Brands, YITH), but not the global "Brand" product attribute
* Bug Fix: "Review Time" field always exports blank in reviews export
* Bug Fix: Brand export sees only one brand taxonomy; Perfect WooCommerce Brands is never detected (stale class name)
* Bug Fix: Commission count includes reversed commissions
* Bug Fix: Custom meta fields selected in quick export manage fields never appear as export columns
* Bug Fix: Escape formatting setting is ignored by the export engine
* Bug Fix: CSV/TSV formula escaping can be bypassed with a backslash before a quote, or with a quote in an unescaped cell
* Bug Fix: Order ID filter exports every order when a range is too wide or the input is not valid
* Bug Fix: Order export brand filter matches only one brand taxonomy
* Bug Fix: Manual/"Fixed date" order filter collapses to "now" when the date format setting is corrupt
* Bug Fix: Order items fields export blank for every order in quick export
* Bug Fix: Product export: combining "Variation" with other product types drops or times out variation rows
* Bug Fix: Products export leaves stock status blank for on-backorder products
* Bug Fix: Quick export ignores per-field column label overrides in the exported file
* Bug Fix: Quick export: product SKU, language and product vendor filters are missing from the admin app
* Bug Fix: An export that finishes but is never downloaded blocks the next export for 30 minutes
* Bug Fix: ID range filters drop IDs when a range is followed by single IDs
* Bug Fix: Product-to-order lookup queries a misspelled order items table name
* Bug Fix: Export screen stops with a fatal error when a custom meta option holds an empty array
* Bug Fix: Tag and brand custom meta columns export blank when Yoast SEO is active

= 2.8.1 =
* Bug Fix: Free plugin exposed Deluxe-only export formats (XLS/XLSX/XML) and their settings; they are now gated with an upgrade prompt
* Bug Fix: Removed the inert Deluxe-only Hide Archives Tab and Hide Scheduled Exports Tab toggles from Advanced settings
* Bug Fix: Debugging Mode did not block exports in the admin app; also fixed Deluxe branding and invalid markup on the debug banner
* Bug Fix: Export Modules buttons and the Help button linked to dead visser.com.au URLs
* Bug Fix: Quick Export: all Order filters silently ignored (toggle value and date key mismatch between the admin app and the exporter)
* Bug Fix: Quick Export: all Product filters silently ignored (key mismatch), including status, type, stock status and stock quantity
* Bug Fix: Quick Export date-range filters silently ignored for Product, Order, User, Coupon and Subscription exports
* Bug Fix: Quick Export: Customer, Coupon code and Product ID text filters rendered with an empty body
* Bug Fix: Quick Export: Custom meta value filter missing for Product and Order exports
* Bug Fix: Order filters: Product Brand never applied, and Customer-by-email matched only guest orders
* Bug Fix: Order filters silently drop matching orders when the Analytics lookup tables are stale
* Bug Fix: Digital Products order filter silently fails when the order_product_lookup table is missing or the table prefix differs
* Bug Fix: Order export fails with a fatal error when the order items formatting setting is missing or invalid
* Bug Fix: Coupon export: Coupon Code not selected by default
* Bug Fix: Customer export leaks the previous customer's identity and spend totals onto guest-order rows
* Bug Fix: Taxonomy exports (Category, Tag, Brand, Attribute, Shipping Class) duplicate every row past 100 terms
* Bug Fix: CSV/TSV exports ignore the Header formatting option (header row always written)
* Bug Fix: CSV/TSV formula injection unmitigated; new "Escape formulas in CSV/TSV" setting, on by default
* Bug Fix: Quick Export could start before the field list finished loading and export extra options as defaults
* Bug Fix: Export fails with a fatal error on PHP 8 when the Timeout setting is saved empty
* Security: Download endpoint allowed path traversal and skipped the capability check
* Security: Export Template post type used the generic post capabilities, so Contributors could create templates
* Security: Archives bulk-delete had no nonce or capability check
* Security: Archived export files were written to the public uploads folder; they now live in a protected export sub-directory and are removed when the attachment is deleted

= 2.8.0 =
* Feature: Admin SPA modernization — new Vue 3 admin interface backed by a REST API
* Bug Fix: Order export filters (customer, billing/shipping country, user role, order ID, payment gateway) silently ignored when HPOS is disabled
* Bug Fix: Subscription export shows a stale coupon after a recurring coupon is removed from the subscription
* Bug Fix: Fatal error crashing the whole site when the plugin bootstrap loads in a bad state (stale OPcache or incomplete update)

= 2.7.9 =
* Bug Fix: Brands are not being exported
* Bug Fix: Custom user meta fields (woo_ce_custom_users) missing from order exports after v5.x refactor
* Bug Fix: Export fails with fatal error when date format uses slashes (d/m/Y)
* Bug Fix: Fatal error calling get_description() on int when exporting subscriptions with coupons
* Bug Fix: Fatal error: attempt to assign property 'order_id' on null when exporting subscriptions with HPOS enabled
* Bug Fix: Fatal error: call to get_description() on int when exporting orders/subscriptions with recurring discount coupons
* Bug Fix: Incorrect "last month" date filter when using UTC-1 or negative timezones
* Bug Fix: Quick Export filters silently ignored for non-user export types (follow-up to #233)
* Bug Fix: Quick Export of users fails with 500 error on large user sites, permanently locks exports
* Bug Fix: Subscription Items export reads from parent order instead of current subscription
* Bug Fix: Subscription Items: product variation and variation ID columns always empty for variable subscriptions
* Bug Fix: Subscription export 500 error when HPOS enabled and date filter applied
* Bug Fix: User export: WooCommerce Memberships filter ignored (exports all users)
* Bug Fix: User export: disabled filters still applied (stale filter state)
* Feature: Add support to export Cost of Goods Sold (COGS) in the products and order type export

= 2.7.8 =
* Bug Fix: Custom order meta incorrectly appear for customer and subscription export
* Bug Fix: Export to SFTP doesn't work for scheduled exports
* Bug Fix: Filtering orders by multiple coupon codes only exports a single coupon order
* Bug Fix: PHP 8.2: Cannot use dynamic creation of properties during export
* Bug Fix: Special characters break Excel export
* Bug Fix: Text with file extension is not exported to XLS format
* Bug Fix: WooCommerce Subscriptions division by zero
* Bug Fix: Potential security issues

= Older versions =
See [changelog.txt](https://plugins.svn.wordpress.org/woocommerce-exporter/trunk/changelog.txt) for the changelog of older versions.

== Upgrade Notice ==

= 2.4 =
2.4 is an urgent Plugin release to patch a CSV Injection Vulnerability affecting exports reported by Vishnupriya Ilango of Fortinet's FortiGuard Labs.

== Disclaimer ==

It is not responsible for any harm or wrong doing this Plugin may cause. Users are fully responsible for their own use. This Plugin is to be used WITHOUT warranty.
