<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Traits
 */

namespace VisserLabs\WSE\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Brands_Detection_Trait
 *
 * Detects whether WooCommerce Brands functionality is available — either via
 * the standalone WooCommerce Brands plugin or WooCommerce core's merged
 * Brands package (9.4+). Both load the `WC_Brands` class during
 * `plugins_loaded`, so consumers running at `setup_theme:100` or later can
 * rely on this check.
 */
trait Brands_Detection_Trait {

    /**
     * Whether WooCommerce Brands functionality is available.
     *
     * @access protected
     *
     * @return bool
     */
    protected function is_brands_available() {
        return class_exists( 'WC_Brands' );
    }
}
