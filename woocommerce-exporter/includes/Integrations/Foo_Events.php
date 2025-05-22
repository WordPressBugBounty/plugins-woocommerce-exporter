<?php
/**
 * Author: Rymera Web Co.
 *
 * @package VisserLabs\WSE\Integrations
 */

namespace VisserLabs\WSE\Integrations;

use VisserLabs\WSE\Abstracts\Abstract_Class;

/**
 * Foo_Events class.
 * http://www.woocommerceevents.com/
 *
 * @since 2.7.3
 */
class Foo_Events extends Abstract_Class {

    /**
     * Count WooCommerce Booking exports.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array $count The default count array.
     * @return array
     */
    public function export_type_count( $count ) {
        global $wpdb;
        $count['ticket'] = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->posts} WHERE post_type = 'event_magic_tickets' AND post_status != 'auto-draft'" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $count;
    }

    /**
     * Run the integration.
     */
    public function run() {
        if ( ! is_plugin_active( 'fooevents/fooevents.php' ) ) {
            return;
        }

        add_filter( 'wsed_export_type_count', array( $this, 'export_type_count' ), 10, 1 );
    }
}
