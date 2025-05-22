<?php
/**
 * Author: Rymera Web Co.
 *
 * @package VisserLabs\WSE\Integrations
 */

namespace VisserLabs\WSE\Integrations;

use VisserLabs\WSE\Abstracts\Abstract_Class;

/**
 * Woocommerce_Product_Vendors class.
 * http://www.woothemes.com/products/product-vendors/
 *
 * @since 2.7.3
 */
class Woocommerce_Product_Vendors extends Abstract_Class {
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
        $count['product_vendor'] = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'wcpv_product_vendors'" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $count;
    }

    /**
     * Run the integration.
     */
    public function run() {
        if ( ! is_plugin_active( 'woocommerce-product-vendors/woocommerce-product-vendors.php' ) ) {
            return;
        }

        add_filter( 'wsed_export_type_count', array( $this, 'export_type_count' ), 10, 1 );
    }
}
