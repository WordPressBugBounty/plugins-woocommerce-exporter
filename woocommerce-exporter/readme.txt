=== Store Exporter – Export WooCommerce Products, Orders, Subscriptions, Customers ===

Contributors: jkohlbach, RymeraWebCo, visser.labs, smub
Tags: woocommerce export, export products, export orders, export subscriptions, csv excel export
Requires at least: 2.9.2
Tested up to: 7.1
Requires PHP: 5.6
Stable tag: 2.8.3
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

= 2.8.3 =
* Improvement: Remove the incorrect money-back guarantee line from the Deluxe upgrade modal and the Deluxe feature screens
* Improvement: Cancel export now stops the export, and a new export can start at once
* Improvement: Translate the admin app. A translation in wp-content/languages/woocommerce-exporter now takes priority, and "Reset language to English" also keeps exports in English
* Improvement: Product quick export can sort by two more "Then by" levels
* Improvement: Category and tag quick exports have a language filter (WPML and Polylang)
* Improvement: Category and tag product filters have a "Match type" choice (include or filter out)
* Improvement: CSV/TSV rows now end with the line ending formatting setting (Windows CRLF by default) instead of always LF
* Bug Fix: Export modules: the Store Toolkit and Product Importer Deluxe cards open the download page even when the plugin is installed and active, and the Store Toolkit link points at a screen that no current release registers
* Bug Fix: WordPress dashboard shows a fatal error ("Undefined constant WSED_AS_HOOK") when a published scheduled export exists
* Bug Fix: Filename setting has no effect on exported files (wrong option key)
* Bug Fix: Field delimiter accepts multi-character values that break or corrupt CSV exports
* Bug Fix: Settings toggles that default to on cannot be saved off
* Bug Fix: Settings screen shows an inflated "unsaved changes" count
* Bug Fix: Quick export ascending/descending sort toggle has no effect
* Bug Fix: Quick export field order resets to the default order
* Bug Fix: Quick export default field selection misses the key columns
* Bug Fix: Quick export lists custom meta keys both in basic info and in their own section
* Bug Fix: Custom meta field "Available In" setting is ignored by quick export
* Bug Fix: Quick export: switching off a filter card leaves its options set
* Bug Fix: Quick export: order item meta and order item product meta keys never appear as export columns
* Bug Fix: Quick export: "Order Items Formatting", "Max Unique Order Items" and "Order Item Types" filters have no effect
* Bug Fix: Orders export "Product Name" sort option is ignored
* Bug Fix: Orders export with "Individual rows" copies the previous item's values into the next row
* Bug Fix: Orders export: order items category is blank for variation line items
* Bug Fix: Orders export: custom product attribute variation columns are blank
* Bug Fix: Orders export: coupon description and coupon expiry date are blank on WooCommerce 8.7+
* Bug Fix: Orders export: PayPal payment columns are blank
* Bug Fix: Orders export: refund rows leave modified date and type ID blank
* Bug Fix: Column label overrides are ignored for order item columns
* Bug Fix: Categories and tags exports write "&" as "&amp;" and ignore sort, limit and offset
* Bug Fix: Users export ignores the "Sort by" setting
* Bug Fix: Users export HTML-escapes billing and shipping values
* Bug Fix: Users export with a user membership filter that matches no members exports every user
* Bug Fix: "Image (Embed)" columns export blank (deprecated shop_thumbnail size)
* Bug Fix: ID range input drops IDs or accepts values that are not whole numbers
* Bug Fix: A refused export request clears the running export's settings
* Bug Fix: Active-integration cache goes stale after WP-CLI activation or plugin deactivation
* Bug Fix: Table checks match similar table names (SHOW TABLES LIKE without esc_like)
* Bug Fix: Orders export: AliDropship order item columns are blank. Their headers are now "Order Items: AliDropship Product ID", "Product URL", "Store URL" and "Store Name". Update an import that maps these columns by header text
* Bug Fix: Export progress dialog keeps the "Export in Progress" title after the export completes
* Bug Fix: Admin app footer shows an impossible memory readout
* Bug Fix: Custom meta field rows in manage fields cannot be used with the keyboard
* Bug Fix: Quick export opened with a ?type= link does not select that export type
* Bug Fix: Tickets export (requires Store Exporter Deluxe): attendee e-mail, ticket sent and barcode columns are synced with Store Exporter Deluxe 5.4.6, including its woo_ce_fooevents_barcode_path and woo_ce_fooevents_barcode_image filters
* Bug Fix: Store Export screen shows a critical error when the tab parameter is passed as an array

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

= Older versions =
See [changelog.txt](https://plugins.svn.wordpress.org/woocommerce-exporter/trunk/changelog.txt) for the changelog of older versions.

== Upgrade Notice ==

= 2.4 =
2.4 is an urgent Plugin release to patch a CSV Injection Vulnerability affecting exports reported by Vishnupriya Ilango of Fortinet's FortiGuard Labs.

== Disclaimer ==

It is not responsible for any harm or wrong doing this Plugin may cause. Users are fully responsible for their own use. This Plugin is to be used WITHOUT warranty.
