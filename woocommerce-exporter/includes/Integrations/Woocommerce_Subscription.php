<?php
/**
 * Author: Rymera Web Co.
 *
 * @package VisserLabs\WSE\Integrations
 */

namespace VisserLabs\WSE\Integrations;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Woocommerce_Subscription class.
 *
 * @since 2.7.3
 */
class Woocommerce_Subscription extends Abstract_Class {

    /**
     * Count WooCommerce Subscription exports.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array $count The default count array.
     * @return array
     */
    public function export_type_count( $count ) {
        global $wpdb;
        $count['subscription'] = 0;
        // WooCommerce Subscriptions.
        if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
            $query = "SELECT COUNT(*) as count FROM {$wpdb->prefix}wc_orders WHERE type = 'shop_subscription'";
        } else {
            $query = "SELECT COUNT(*) as count FROM {$wpdb->posts} WHERE post_type = 'shop_subscription'";
        }
        $count['subscription'] = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $count;
    }

    /**
     * Run the integration.
     */
    public function run() {
        if ( ! is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
            return;
        }

        add_filter( 'wsed_export_type_count', array( $this, 'export_type_count' ), 10, 1 );
    }
}
