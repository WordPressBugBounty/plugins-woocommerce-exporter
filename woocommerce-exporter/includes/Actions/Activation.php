<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Actions
 */

namespace VisserLabs\WSE\Actions;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Helpers\Helper;
use VisserLabs\WSE\Helpers\Export as Export_Helper;

/**
 * Activation class.
 *
 * @since 2.7.3
 */
class Activation extends Abstract_Class {

    /**
     * Holds boolean value whether the plugin is being activated network wide.
     *
     * @var bool
     */
    protected $network_wide;

    /**
     * Constructor.
     *
     * @param bool $network_wide Whether the plugin is being activated network wide.
     */
    public function __construct( $network_wide ) {
        $this->network_wide = $network_wide;
    }

    /**
     * Run plugin activation actions.
     *
     * @since 2.7.3
     */
    public function run() {
        /**
         * If previous multisite installs site store license options using normal get/add/update_option functions.
         * These stores the option on a per sub-site basis. We need move these options network wide in multisite setup
         * via get/add/update_site_option functions.
         */
        if ( is_multisite() ) {
            $installed_version = get_option( WSE_OPTION_INSTALLED_VERSION );
            if ( $installed_version ) {
                update_site_option( WSE_OPTION_INSTALLED_VERSION, $installed_version );
                delete_option( WSE_OPTION_INSTALLED_VERSION );
            }
        }

        // Record initial install date for new installs only.
        // WSE_OPTION_INSTALLED_VERSION is still the old value at this point.
        // false = fresh install (no previous version), so we record the date.
        if ( false === get_site_option( WSE_OPTION_INSTALLED_VERSION ) ) {
            if ( false === get_option( 'woo_ce_initial_install_date' ) ) {
                update_option( 'woo_ce_initial_install_date', time() );
            }
        }

        // Repair a corrupted export date format. Runs on activation and on every
        // version change (see App::initialize()), and is idempotent.
        $this->repair_corrupt_date_format();

        /*
         * Flush the cached Brand count and the active-module list. Both changed
         * meaning in this release, so a cached copy would still report one brand
         * taxonomy and an inactive Perfect WooCommerce Brands. On an upgrade this
         * runs on the version change, so both are rebuilt on the next page load.
         */
        delete_transient( WOO_CE_PREFIX . '_brand_count' );
        delete_transient( WOO_CE_PREFIX . '_modules_active' );

        // The SPA type counts include the Brand count, and the Commission count
        // now leaves out reversed commissions, so both are rebuilt too.
        delete_transient( WOO_CE_PREFIX . '_export_type_count' );
        delete_transient( WOO_CE_PREFIX . '_commission_count' );

        // Update current installed plugin version.
        update_site_option( WSE_OPTION_INSTALLED_VERSION, Helper::get_plugin_version() );

        // Set activation code triggered flag.
        update_option( 'wse_activation_code_triggered', 'yes' );
    }

    /**
     * Repair a corrupted export date-format option.
     *
     * The option can hold `''`, `'1'` or `'custom'` — see the two Settings write paths,
     * both hardened alongside this. Readers coerce such a value at read time, so the
     * plugin behaves correctly either way; this rewrites the stored row as well, so the
     * corruption stops being carried around and no reader that has not been routed
     * through the helper can resurrect the bug.
     *
     * Deliberately does NOT try to guess what the store's format used to be. That cannot
     * be recovered, and guessing would silently reinterpret every stored date bound. The
     * documented default is restored instead.
     *
     * Idempotent — a valid stored format, including one an admin chooses after an earlier
     * run, is left untouched.
     *
     * @since 2.8.2
     * @return void
     */
    private function repair_corrupt_date_format() {
        $option_key = WOO_CE_PREFIX . '_date_format';
        $stored     = get_option( $option_key, null );

        // An absent row is not corruption — each reader's own default already covers it.
        if ( null === $stored || Export_Helper::is_valid_date_format( $stored ) ) {
            return;
        }

        update_option( $option_key, Export_Helper::DEFAULT_DATE_FORMAT );

        if ( ! function_exists( 'wc_get_logger' ) ) {
            return;
        }

        wc_get_logger()->info(
            sprintf(
                'Store Exporter repaired a corrupted export date format (stored %1$s); it is now "%2$s". Fixed-date export filters were resolving to the current time instead of the selected range.',
                wp_json_encode( $stored ),
                Export_Helper::DEFAULT_DATE_FORMAT
            ),
            array( 'source' => 'woocommerce-exporter' )
        );
    }
}
