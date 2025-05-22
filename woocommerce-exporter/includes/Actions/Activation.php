<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Actions
 */

namespace VisserLabs\WSE\Actions;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Helpers\Helper;

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

        // Update current installed plugin version.
        update_site_option( WSE_OPTION_INSTALLED_VERSION, Helper::get_plugin_version() );

        // Set activation code triggered flag.
        update_option( 'wse_activation_code_triggered', 'yes' );
    }
}
