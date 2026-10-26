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
     * Counts the terms of every brand taxonomy on the store, not just the one
     * woo_ce_brand_term_taxonomy resolves to, so the record count matches the
     * rows the Brand export writes. See #416.
     *
     * @since 2.7.3
     * @since 2.8.2 Counts every brand taxonomy rather than the resolved one.
     * @access public
     *
     * @param array $count The default count array.
     * @return array
     */
    public function export_type_count( $count ) {
        // brand.php carries the helper and is only loaded on demand, so pull the
        // Brand export files in first. Counts run on REST requests that load none.
        if ( ! function_exists( 'woo_ce_get_brand_term_count' ) && function_exists( 'woo_ce_load_export_types' ) ) {
            woo_ce_load_export_types( 'brand' );
        }

        if ( function_exists( 'woo_ce_get_brand_term_count' ) ) {
            $count['brand'] = woo_ce_get_brand_term_count();
        }

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
