<?php
/**
 * Plugin Name:          Store Exporter for WooCommerce
 * Plugin URI:           https://visser.com.au/
 * Description:          Export Products, Orders, Users, Categories, Tags and other store details out of WooCommerce into Excel spreadsheets and other simple formatted files (e.g. CSV, TSV, Excel formats including XLS and XLSX, XML, etc.)
 * Version:              2.7.3
 * Author:               Visser Labs
 * Author URI:           https://visser.com.au/
 * License:              GPL2
 * Text Domain:          woocommerce-exporter
 * Domain Path:          /languages/
 * WC requires at least: 5.0
 * WC tested up to:      8.4
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
define( 'WOO_CE_VERSION', '2.7.3' );

/**
 * Default batch size for exporting data.
 */
if ( ! defined( 'WSED_DEFAULT_BATCH_SIZE' ) ) {
    define( 'WSED_DEFAULT_BATCH_SIZE', 100 );
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

/***************************************************************************
 * Loads plugin text domain.
 * **************************************************************************
 *
 * Loads the plugin text domain for translation.
 */
function wse_textdomain() {
    $state = get_option( 'woo_ce_reset_language_english', false );
    if ( $state ) {
        return;
    }

    $locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-exporter' );

    load_textdomain(
        'woocommerce-exporter',
        WP_LANG_DIR . '/woocommerce-exporter/woocommerce-exporter-' . $locale . '.mo'
    );

    load_plugin_textdomain(
        'woocommerce-exporter',
        false,
        plugin_basename( __DIR__ ) . '/languages'
    );
}
add_action( 'init', 'wse_textdomain', 11 );

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

    $app->boot();
}
