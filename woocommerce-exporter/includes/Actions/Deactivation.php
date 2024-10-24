<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Actions
 */

namespace VisserLabs\WSE\Actions;

use VisserLabs\WSE\Abstracts\Abstract_Class;

/**
 * Deactivation class.
 *
 * @since 2.7.3
 */
class Deactivation extends Abstract_Class {

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
     * Plugin deactivation.
     *
     * @since 13.3.5
     * @access public
     *
     * @param int $blog_id Blog ID.
     */
    private function _deactivate_plugin( $blog_id ) {
        delete_option( 'wse_activation_code_triggered' );
        delete_site_option( WSE_OPTION_INSTALLED_VERSION );
    }

    /**
     * Run plugin deactivation actions.
     */
    public function run() {
        global $wpdb;

        // check if it is a multisite network.
        if ( is_multisite() ) {

            // check if the plugin has been activated on the network or on a single site.
            if ( $this->network_wide ) {
                // get ids of all sites.
                $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

                foreach ( $blog_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    $this->_deactivate_plugin( $blog_id );
                }

                restore_current_blog();
            } else {
                // activated on a single site, in a multi-site.
                $this->_deactivate_plugin( $wpdb->blogid );
            }
        } else {
            // activated on a single site.
            $this->_deactivate_plugin( $wpdb->blogid );
        }
    }
}
