<?php
/**
 * Plugin Name:          Store Exporter for WooCommerce
 * Plugin URI:           https://visser.com.au/
 * Description:          Export Products, Orders, Users, Categories, Tags and other store details out of WooCommerce into Excel spreadsheets and other simple formatted files (e.g. CSV, TSV, Excel formats including XLS and XLSX, XML, etc.)
 * Version:              2.8.3
 * Author:               Visser Labs
 * Author URI:           https://visser.com.au/
 * License:              GPL2
 * Text Domain:          woocommerce-exporter
 * Domain Path:          /languages/
 *
 * Requires at least: 5.4
 * Tested up to: 7.1
 *
 * WC requires at least: 5.0
 * WC tested up to:      11.3
 *
 * @package  VisserLabs\WSE
 * @author   Rymera Web Co <josh@rymera.com.au>
 * @license  GPL v2 or later
 * @link     https://rymera.com.au/
 */

/***************************************************************************
 * Main plugin file
 * **************************************************************************
 *
 * This file is the main entry point for the plugin. It is responsible for
 * loading the plugin's dependencies and initializing the plugin.
 */

/***************************************************************************
 * Ensure that the plugin is not accessed or called directly
 * **************************************************************************
 */
defined( 'ABSPATH' ) || exit;

/***************************************************************************
 * Plugin Constants
 * **************************************************************************
 */

define( 'WOO_CE_DIRNAME', basename( __DIR__ ) );
define( 'WOO_CE_RELPATH', basename( __DIR__ ) . '/' . basename( __FILE__ ) );
define( 'WOO_CE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_CE_VIEWS_PATH', plugin_dir_path( __FILE__ ) . 'views/' );
define( 'WOO_CE_IMAGES_URL', plugin_dir_url( __FILE__ ) . 'images/' );
define( 'WOO_CE_PLUGIN_BASE_NAME', plugin_basename( WOO_CE_RELPATH ) );
define( 'WOO_CE_PLUGINPATH', WP_PLUGIN_URL . '/' . basename( __DIR__ ) );
define( 'WOO_CE_PLUGIN_FILE', __FILE__ );
define( 'WOO_CE_PREFIX', 'woo_ce' );
define( 'WOO_CE_VERSION', '2.8.3' );
define( 'WOO_CE_UPSELL_URL', 'https://visser.com.au/plugins/woocommerce-export/' );

/**
 * Default batch size for exporting data.
 */
if ( ! defined( 'WSED_DEFAULT_BATCH_SIZE' ) ) {
    define( 'WSED_DEFAULT_BATCH_SIZE', 100 );
}

/**
 * Action Scheduler hook and groups the scheduled-export machinery looks its actions up by.
 *
 * Ported from Store Exporter Deluxe, which defines the same three names with the same values. The
 * scheduled-export screens and the dashboard widget read them on every wp-admin load, so without
 * them a published scheduled export turns an ordinary dashboard into an "Undefined constant"
 * fatal. Guarded with `! defined()` to match the sibling constants above. That guard is what makes
 * the port safe rather than the self-deactivation in bootstrap/app.php: this block runs at file
 * load, well before that file is required below, and Deluxe sorts first in `active_plugins`, so
 * both plugins' define blocks really can run in the same request. Deluxe wins the race and these
 * become no-ops - harmless because the values here are byte-identical to its own.
 */
if ( ! defined( 'WSED_AS_HOOK' ) ) {
    define( 'WSED_AS_HOOK', 'wsed_scheduled_export' );
}
if ( ! defined( 'WSED_AS_GROUP' ) ) {
    define( 'WSED_AS_GROUP', 'wsed-scheduled-export' );
}
if ( ! defined( 'WSED_AS_ASYNC_GROUP' ) ) {
    define( 'WSED_AS_ASYNC_GROUP', 'wsed-scheduled-export-async' );
}

/**
 * Parent levels resolved for a category's hierarchy columns in Category exports.
 */
if ( ! defined( 'WSE_CATEGORY_MAX_ANCESTORS' ) ) {
    define( 'WSE_CATEGORY_MAX_ANCESTORS', 6 );
}

/**
 * Plugin version key.
 */
if ( ! defined( 'WSE_OPTION_INSTALLED_VERSION' ) ) {
    define( 'WSE_OPTION_INSTALLED_VERSION', 'wse_option_installed_version' );
}

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// The text domain functions below use VisserLabs\WSE\Helpers\Helper, and they
// run on any PHP version, before bootstrap/app.php is loaded.
require_once WOO_CE_PATH . 'includes/autoload.php';

/**
 * Load the custom-folder translation for a locale, ahead of the language packs.
 *
 * A site can keep its .mo in WP_LANG_DIR/woocommerce-exporter/ to override the
 * regular language pack. Strings are looked up in the order their files were
 * loaded, and an earlier __() call may already have loaded the pack just in
 * time. So the domain is reloaded from scratch: the custom file first, then the
 * pack in WP_LANG_DIR/plugins/ and the plugin's languages/ folder for strings
 * the custom file does not translate.
 *
 * The reload drops every file loaded for this domain and locale, including one
 * that other code loaded directly before this runs. Translation plugins that
 * hook load_textdomain() re-apply on the three loads below.
 *
 * @since 2.8.3
 *
 * @param string $locale Locale to load.
 * @return bool True when a custom-folder file was found and loaded.
 */
function wse_load_custom_textdomain( $locale ) {
    $domain = 'woocommerce-exporter';
    // The filtered locale names the file only. load_textdomain() makes the
    // locale it is given the current one for every domain, so it gets the real one.
    $mofile = $domain . '-' . apply_filters( 'plugin_locale', $locale, $domain ) . '.mo';

    if ( ! is_readable( WP_LANG_DIR . '/' . $domain . '/' . $mofile ) ) {
        return false;
    }

    // Since WP 6.5 a reloadable unload keeps the files loaded for each locale.
    // Drop this locale's files too, or the pack stays ahead of the custom file.
    if ( class_exists( 'WP_Translation_Controller' ) ) {
        WP_Translation_Controller::get_instance()->unload_textdomain( $domain, $locale );
    }
    unload_textdomain( $domain, true );
    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $mofile, $locale );
    load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile, $locale );
    load_textdomain( $domain, WOO_CE_PATH . 'languages/' . $mofile, $locale );

    return true;
}

/***************************************************************************
 * Loads plugin text domain.
 * **************************************************************************
 *
 * Loads the plugin text domain for translation.
 */
function wse_textdomain() {
    if ( VisserLabs\WSE\Helpers\Helper::is_language_reset() ) {
        return;
    }

    // Registers the plugin's languages/ folder, so a later locale switch still finds it.
    load_plugin_textdomain(
        'woocommerce-exporter',
        false,
        plugin_basename( __DIR__ ) . '/languages'
    );

    // determine_locale(): the user's language in the admin, as load_plugin_textdomain() uses.
    wse_load_custom_textdomain( determine_locale() );
}
add_action( 'init', 'wse_textdomain', 11 );

/**
 * Reload the custom-folder translation when the locale changes.
 *
 * Switching locale reloads the domain from WP_LANG_DIR/plugins/ and the
 * plugin's languages/ folder only, so the custom-folder file (see
 * wse_load_custom_textdomain()) would be lost after the switch.
 *
 * @since 2.8.3
 *
 * @param string $locale The new locale.
 * @return void
 */
function wse_textdomain_on_locale_change( $locale ) {
    if ( VisserLabs\WSE\Helpers\Helper::is_language_reset() ) {
        return;
    }

    wse_load_custom_textdomain( $locale );
}
add_action( 'change_locale', 'wse_textdomain_on_locale_change' );

/**
 * Keep the plugin's strings English when "Reset language to English" is on.
 *
 * Skipping wse_textdomain() alone is not enough: since WP 4.6 the first __()
 * call loads the domain just in time from WP_LANG_DIR/plugins/, and a
 * translation plugin such as Loco can load it too. This blocks every PHP .mo
 * load of the domain, on every request: admin screens, REST, exports and
 * emails. The SPA's JS translations are skipped separately, in
 * WP_Admin::set_admin_app_translations().
 *
 * @since 2.8.3
 *
 * @param bool   $override Whether to override the .mo file loading.
 * @param string $domain   Text domain being loaded.
 * @return bool
 */
function wse_block_textdomain_on_reset_language( $override, $domain ) {
    if ( 'woocommerce-exporter' === $domain && VisserLabs\WSE\Helpers\Helper::is_language_reset() ) {
        return true;
    }
    return $override;
}
add_filter( 'override_load_textdomain', 'wse_block_textdomain_on_reset_language', 10, 2 );

/***************************************************************************
 * Checks required minimum PHP version to run the plugin.
 ***************************************************************************
 *
 * Checks the required minimum PHP version to run the plugin and prints
 * admin notice for admins if the PHP version is not met.
 */
if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
    /**
     * Admin notice for required PHP version to run the plugin
     */
    function wse_required_php_version() {
        include WOO_CE_PATH . 'templates/admin/parts/require-php-version.php';
    }

    add_action( 'admin_notices', 'wse_required_php_version' );
} else {
    /***************************************************************************
     * Loads the plugin.
     ***************************************************************************
    *
    * Here we load the plugin if all checks passed.
    */

    /**
     * Our bootstrap class instance.
     *
     * @var VisserLabs\WSE\App $app
     */
    $app = require_once 'bootstrap/app.php';

    /*
     * Guard the boot call. `require_once` only returns the App instance when
     * bootstrap/app.php is included cleanly on its first include. In a bad-state
     * load it can return null (stale OPcache or an incomplete update leaves
     * exporter.php and bootstrap/app.php out of sync) or true (already
     * included this request). Booting on a non-App value fatals the whole site,
     * so we skip it and let the request self-recover on the next clean load.
     */
    if ( $app instanceof \VisserLabs\WSE\App ) {
        $app->boot();
    }
}
