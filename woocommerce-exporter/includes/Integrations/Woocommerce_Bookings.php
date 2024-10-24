<?php
/**
 * Author: Rymera Web Co.
 *
 * @package VisserLabs\WSE\Integrations
 */

namespace VisserLabs\WSE\Integrations;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Helpers\Export as Export_Helper;
use VisserLabs\WSE\Helpers\Formatting;

/**
 * Woocommerce_Bookings class.
 * https://woocommerce.com/products/woocommerce-bookings/
 *
 * @since 2.7.3
 */
class Woocommerce_Bookings extends Abstract_Class {

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
        $count['booking'] = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->posts} WHERE post_type = 'wc_booking'" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $count;
    }

    /**
     * Run the integration.
     */
    public function run() {
        if ( ! is_plugin_active( 'woocommerce-bookings/woocommerce-bookings.php' ) ) {
            return;
        }

        add_filter( 'wsed_export_type_count', array( $this, 'export_type_count' ), 10, 1 );
    }
}
