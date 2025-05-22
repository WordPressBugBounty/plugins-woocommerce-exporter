<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE
 */

namespace VisserLabs\WSE;

use VisserLabs\WSE\Actions\Activation;
use VisserLabs\WSE\Actions\Deactivation;
use VisserLabs\WSE\Traits\Singleton_Trait;
use VisserLabs\WSE\Factories\Admin_Notice;
use VisserLabs\WSE\Helpers\Helper;

defined( 'ABSPATH' ) || exit;

require_once WOO_CE_PATH . 'includes/autoload.php';

/**
 * Class App
 */
class App {

    use Singleton_Trait;

    /**
     * Holds the class object instances.
     *
     * @var array An array of object class instance.
     */
    protected $objects;

    /**
     * Holds the failed plugin dependencies.
     *
     * @since 2.7.3
     * @access private
     *
     * @var array An array of failed plugin dependencies.
     */
    private $_failed_dependencies;

    /**
     * App constructor.
     */
    public function __construct() {
        $this->objects = array();
    }

    /**
     * Called at the end of file to initialize autoloader
     */
    public function boot() {
        /***************************************************************************
         * Declare WooCommerce HPOS Compatibility
         ***************************************************************************
         *
         * We declare WooCommerce HPOS compatibility to allow HPOS to work with
         * WooCommerce Store Exporter Deluxe.
         */
        add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );

        register_deactivation_hook( WOO_CE_PLUGIN_FILE, array( $this, 'deactivation_actions' ) );

        // Check plugin dependencies.
        if ( ! $this->_check_dependencies() ) {
            return;
        }

        register_activation_hook( WOO_CE_PLUGIN_FILE, array( $this, 'activation_actions' ) );

        // Execute codes that need to run on 'init' hook.
        add_action( 'init', array( $this, 'initialize' ) );

        /***************************************************************************
         * Run the plugin
         ***************************************************************************
         *
         * We run the plugin classes on `setup_theme` hook with priority 100 as
         * we depend on WooCommerce plugin to be loaded first and we need to make
         * sure that WP_Rewrite global object is already available.
         */
        add_action( 'setup_theme', array( $this, 'run' ), 100 );

        /***************************************************************************
         * Maybe add HTML5 support
         ***************************************************************************
         *
         * We need HTML5 support for the theme in order for the newer script tag
         * attributes to work (_i.e._ `type="module"`).
         */
        add_action( 'after_setup_theme', array( $this, 'maybe_add_html5_support' ), 999 );
    }

    /**
     * Enables HTML5 support for the theme if not already. We require this in order for the newer script tag
     * attributes to work (_i.e._ `type="module"`).
     *
     * @since 2.7.3
     * @return void
     */
    public function maybe_add_html5_support() {

        if ( current_theme_supports( 'html5', 'script' ) ) {
            return;
        }

        add_theme_support( 'html5', array( 'script' ) );
    }

    /**
     * Register classes to run.
     *
     * @param array $objects Array of class instances.
     *
     * @return void
     */
    public function register_objects( $objects ) {

        $this->objects = array_merge( $this->objects, $objects );
    }

    /**
     * Plugin activation actions
     *
     * @param bool $sitewide Whether the plugin is being activated network-wide.
     */
    public function activation_actions( $sitewide ) {

        /***************************************************************************
         * Plugin activation actions
         ***************************************************************************
         *
         * We run the plugin actions here when it's activated.
         */
        ( new Activation( $sitewide ) )->run();

        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation actions
     *
     * @param bool $sitewide Whether the plugin is being deactivated network-wide.
     */
    public function deactivation_actions( $sitewide ) {

        /***************************************************************************
         * Plugin deactivation actions
         ***************************************************************************
         *
         * We run the plugin actions here when it's deactivated.
         */
        ( new Deactivation( $sitewide ) )->run();

        flush_rewrite_rules();
    }

    /**
     * Method that houses codes to be executed on init hook.
     *
     * @since 13.3.5.1
     * @access public
     */
    public function initialize() {
        // Execute activation codebase if not yet executed on plugin activation ( Mostly due to plugin dependencies ).
        $installed_version = get_site_option( WSE_OPTION_INSTALLED_VERSION, false );

        if ( version_compare( $installed_version, Helper::get_plugin_version(), '!=' ) || get_option( 'wse_activation_code_triggered', false ) !== 'yes' ) {
            if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
                require_once ABSPATH . '/wp-admin/includes/plugin.php';
            }

            $sitewide = is_plugin_active_for_network( 'woocommerce-store-exporter/exporter.php' );
            $this->activation_actions( $sitewide );
        }
    }

    /**
     * Run the plugin classes.
     *
     * @return void
     */
    public function run() {

        /***************************************************************************
         * We make sure that the classes to be run extends the abstract class.
         ***************************************************************************
         *
         * We make sure that the classes to be run extends the abstract class or has
         * implemented a `run` method.
         */
        foreach ( $this->objects as $object ) {
            if ( ! method_exists( $object, 'run' ) ) {
                _doing_it_wrong(
                    __METHOD__,
                    esc_html__(
                        'The class does not have a run method. Please make sure to extend the Abstract_Class class.',
                        'woocommerce-exporter'
                    ),
                    esc_html( Helper::get_plugin_data( 'Version' ) )
                );
                continue;
            }
            $class_object = strtolower( wp_basename( get_class( $object ) ) );

            $this->objects[ $class_object ] = apply_filters(
                'wsed_class_object',
                $object,
                $class_object,
                $this
            );
            $this->objects[ $class_object ]->run();
        }

        // Old plugin bootstrap.
        require_once WOO_CE_PATH . 'includes/bootstrap.php';
    }

    /**
     * Check plugin dependencies.
     *
     * @since 2.7.3
     * @access private
     */
    private function _check_dependencies() {
        $admin_notice                                  = null;
        $this->_failed_dependencies['missing_plugins'] = $this->_check_missing_required_plugins();
        if ( ! empty( $this->_failed_dependencies['missing_plugins'] ) ) {

            // Initialize the missing required plugins admin notice.
            $admin_notice = new Admin_Notice(
                sprintf(/* translators: %1$s = opening <strong> tag; %2$s = closing </strong> tag; %3$s = opening <p> tag; %4$s = closing </p> tag */
                    esc_html__(
                        '%3$s%1$sStore Exporter for WooCommerce %2$splugin missing dependency.%4$s',
                        'woocommerce-exporter'
                    ),
                    '<strong>',
                    '</strong>',
                    '<p>',
                    '</p>'
                ),
                'failed_dependency',
                'html',
                $this->_failed_dependencies
            );
        } elseif ( is_plugin_active( 'woocommerce-store-exporter-deluxe/exporter-deluxe.php' ) ) {
            // Display a notice that Woocommerce Store Exporter Deluxe is already installed and activated.
            $admin_notice = new Admin_Notice(
                sprintf(/* translators: %1$s = opening <em> tag; %2$s = closing </em> tag */
                    esc_html__(
                        '%1$sPlease deactivate any other instances of %2$sWooCommerce - Store Exporter Deluxe%3$s before re-activating this Plugin.%4$s',
                        'woocommerce-exporter'
                    ),
                    '<p>',
                    '<strong><em>',
                    '</em></strong>',
                    '</p>'
                ),
                'dependency_conflict'
            );

            // Deactivate the plugin.
            deactivate_plugins( plugin_basename( WOO_CE_PLUGIN_FILE ), false, is_network_admin() );

            // Remove the plugin activated notice.
            if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
                unset( $_GET['activate'] );
            }
        }

        /***************************************************************************
         * Required plugins check failed
         ***************************************************************************
         *
         * Display the admin notice if the required plugins check failed
         * and bail out.
         */
        if ( null !== $admin_notice ) {
            $admin_notice->run();

            return false;
        }

        return true;
    }

    /**
     * Checks required plugins if they are active.
     *
     * @since 2.7.3
     * @access public
     *
     * @return array List of plugins that are not active.
     */
    private static function _check_missing_required_plugins() {

        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . '/wp-admin/includes/plugin.php';
        }

        $i       = 0;
        $plugins = array();

        $required_plugins = array(
            'woocommerce/woocommerce.php',
        );

        foreach ( $required_plugins as $plugin ) {
            if ( ! is_plugin_active( $plugin ) ) {
                $plugin_name                  = explode( '/', $plugin );
                $plugins[ $i ]['plugin-key']  = $plugin_name[0];
                $plugins[ $i ]['plugin-base'] = $plugin;
                $plugins[ $i ]['plugin-name'] = str_replace(
                    'Woocommerce',
                    'WooCommerce',
                    ucwords( str_replace( '-', ' ', $plugin_name[0] ) )
                );
            }

            ++$i;
        }

        return $plugins;
    }

    /**
     * Declare compatibility with WooCommerce HPOS.
     *
     * @since 5.3.2
     */
    public function declare_hpos_compatibility() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WOO_CE_PLUGIN_FILE, true );
        }
    }
}

/***************************************************************************
 * Instantiate classes
 ***************************************************************************
 *
 * Instantiate classes to be registered and run.
 */
App::instance()->register_objects(
    array_merge(
        require_once WOO_CE_PATH . 'bootstrap/class-objects.php',
        require_once WOO_CE_PATH . 'bootstrap/integration-objects.php',
    )
);

return App::instance();
