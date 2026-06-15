<?php
/**
 * Author: Rymera Web Co.
 *
 * @package VisserLabs\WSE\Integrations
 */

namespace VisserLabs\WSE\Integrations;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Traits\Brands_Detection_Trait;

/**
 * Woocommerce_Brands class.
 * https://woocommerce.com/products/brands/
 *
 * Supports both the standalone WooCommerce Brands plugin and the Brands
 * feature merged into WooCommerce core in 9.4+. Both define the `WC_Brands`
 * class and register the `product_brand` taxonomy.
 *
 * @since 2.7.3
 */
class Woocommerce_Brands extends Abstract_Class {

    use Brands_Detection_Trait;

    /**
     * Count WooCommerce Brand exports.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array $count The default count array.
     * @return array
     */
    public function export_type_count( $count ) {
        $taxonomy = apply_filters( 'woo_ce_brand_term_taxonomy', 'product_brand' );

        global $wpdb;
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $count['brand'] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s", $taxonomy ) );
        return $count;
    }

    /**
     * Run the integration.
     */
    public function run() {
        if ( ! $this->is_brands_available() ) {
            return;
        }

        add_filter( 'wsed_export_type_count', array( $this, 'export_type_count' ), 10, 1 );
    }
}
