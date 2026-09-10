<?php
/**
 * Export Items REST Controller.
 *
 * @since   2.8.0
 * @package VisserLabs\WSE\Classes\REST
 */

namespace VisserLabs\WSE\Classes\REST;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Helpers\Helper;
use VisserLabs\WSE\Helpers\Export as Export_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Export_Items_Controller.
 *
 * Provides the full export type listing with per-type metadata for the
 * "All Export Items" Vue page.
 *
 * @since 2.8.0
 */
class Export_Items_Controller extends Abstract_Class {

    const REST_NAMESPACE = 'wse/v1';
    const REST_BASE      = 'export-items';

    /**
     * Register hooks.
     *
     * @since 2.8.0
     * @return void
     */
    public function run() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST routes.
     *
     * @since 2.8.0
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_BASE,
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_export_items' ),
                'permission_callback' => array( $this, 'check_permission' ),
            )
        );
    }

    /**
     * Permission callback.
     *
     * @since 2.8.0
     * @return bool|\WP_Error
     */
    public function check_permission() {
        if ( ! current_user_can( apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' ) ) ) {
            return new \WP_Error(
                'rest_forbidden',
                __( 'Sorry, you are not allowed to access this endpoint.', 'woocommerce-exporter' ),
                array( 'status' => rest_authorization_required_code() )
            );
        }
        return true;
    }

    /**
     * Return the full export items payload.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 2.8.0
     * @return \WP_REST_Response
     */
    public function get_export_items( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
        return rest_ensure_response(
            array(
                'items'           => $this->get_items(),
                'schedules_count' => $this->get_schedules_count(),
                'archives_count'  => $this->get_archives_count(),
                'plugin'          => $this->get_plugin_info(),
                'urls'            => $this->get_tab_urls(),
            )
        );
    }

    /**
     * Build the list of export items enriched with record counts and last export dates.
     *
     * @since 2.8.0
     * @return array
     */
    private function get_items() {
        $definitions  = $this->get_export_type_definitions();
        $counts       = Export_Helper::get_export_type_count();
        $last_exports = $this->get_last_export_dates();
        $base_url     = admin_url( 'admin.php?page=woo_ce&tab=quick_export' );

        $items = array();
        foreach ( $definitions as $slug => $def ) {
            $is_premium = woo_ce_is_premium_export_type( $slug );
            $items[]    = array(
                'slug'        => $slug,
                'label'       => $def['label'],
                'category'    => $def['category'],
                'description' => $def['description'],
                'records'     => isset( $counts[ $slug ] ) ? (int) $counts[ $slug ] : 0,
                'last_export' => isset( $last_exports[ $slug ] ) ? $this->format_export_date( $last_exports[ $slug ] ) : null,
                'export_url'  => esc_url_raw( $base_url . '#export-' . $slug ),
                'premium'     => $is_premium,
                'upsell_url'  => $is_premium ? woo_ce_get_export_type_upsell_url( $slug ) : null,
            );
        }

        return $items;
    }

    /**
     * Fetch the most recent export date per export type from the archive attachments.
     *
     * @since 2.8.0
     * @return array  Keyed by export-type slug, value is the MySQL date string.
     */
    private function get_last_export_dates() {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT pm.meta_value AS export_type, MAX(p.post_date) AS last_date
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                 WHERE p.post_type = %s
                   AND pm.meta_key = %s
                 GROUP BY pm.meta_value",
                'attachment',
                '_woo_export_type'
            )
        );

        $dates = array();
        foreach ( $rows as $row ) {
            $dates[ $row->export_type ] = $row->last_date;
        }
        return $dates;
    }

    /**
     * Format a MySQL date for display in the export items list.
     *
     * @param string $date_string MySQL date string.
     *
     * @since 2.8.0
     * @return string
     */
    private function format_export_date( $date_string ) {
        $timestamp = strtotime( $date_string );
        $today     = strtotime( 'today' );
        $yesterday = strtotime( 'yesterday' );

        if ( $timestamp >= $today ) {
            /* translators: %s: formatted time */
            return sprintf( __( 'Today, %s', 'woocommerce-exporter' ), date_i18n( wc_time_format(), $timestamp ) );
        }
        if ( $timestamp >= $yesterday ) {
            /* translators: %s: formatted time */
            return sprintf( __( 'Yesterday, %s', 'woocommerce-exporter' ), date_i18n( wc_time_format(), $timestamp ) );
        }
        return date_i18n( wc_date_format(), $timestamp );
    }

    /**
     * Static export type definitions: label, category, and description.
     *
     * @since 2.8.0
     * @return array
     */
    private function get_export_type_definitions() {
        return array(
            'product'        => array(
                'label'       => __( 'Products', 'woocommerce-exporter' ),
                'category'    => __( 'Products', 'woocommerce-exporter' ),
                'description' => __( 'Export all product data including variations, pricing, stock, and custom attributes.', 'woocommerce-exporter' ),
            ),
            'category'       => array(
                'label'       => __( 'Categories', 'woocommerce-exporter' ),
                'category'    => __( 'Products', 'woocommerce-exporter' ),
                'description' => __( 'Export product categories and their hierarchy, descriptions, and thumbnail images.', 'woocommerce-exporter' ),
            ),
            'order'          => array(
                'label'       => __( 'Orders', 'woocommerce-exporter' ),
                'category'    => __( 'Orders', 'woocommerce-exporter' ),
                'description' => __( 'Export orders with line items, totals, payment details, and shipping information.', 'woocommerce-exporter' ),
            ),
            'customer'       => array(
                'label'       => __( 'Customers', 'woocommerce-exporter' ),
                'category'    => __( 'Customers', 'woocommerce-exporter' ),
                'description' => __( 'Export customer details including billing, shipping addresses, and purchase history.', 'woocommerce-exporter' ),
            ),
            'coupon'         => array(
                'label'       => __( 'Coupons', 'woocommerce-exporter' ),
                'category'    => __( 'Marketing', 'woocommerce-exporter' ),
                'description' => __( 'Export coupon data including codes, discount types, usage limits, and expiry dates.', 'woocommerce-exporter' ),
            ),
            'subscription'   => array(
                'label'       => __( 'Subscriptions', 'woocommerce-exporter' ),
                'category'    => __( 'Subscriptions', 'woocommerce-exporter' ),
                'description' => __( 'Export subscription information and renewal data from WooCommerce Subscriptions.', 'woocommerce-exporter' ),
            ),
            'tag'            => array(
                'label'       => __( 'Tags', 'woocommerce-exporter' ),
                'category'    => __( 'Products', 'woocommerce-exporter' ),
                'description' => __( 'Export product tags assigned to your products including slugs and descriptions.', 'woocommerce-exporter' ),
            ),
            'brand'          => array(
                'label'       => __( 'Brands', 'woocommerce-exporter' ),
                'category'    => __( 'Products', 'woocommerce-exporter' ),
                'description' => __( 'Export product brands and associated information from your WooCommerce store.', 'woocommerce-exporter' ),
            ),
            'user'           => array(
                'label'       => __( 'Users', 'woocommerce-exporter' ),
                'category'    => __( 'Users', 'woocommerce-exporter' ),
                'description' => __( 'Export user account information including roles, registration dates, and metadata.', 'woocommerce-exporter' ),
            ),
            'review'         => array(
                'label'       => __( 'Reviews', 'woocommerce-exporter' ),
                'category'    => __( 'Products', 'woocommerce-exporter' ),
                'description' => __( 'Export product reviews and ratings including reviewer details and comments.', 'woocommerce-exporter' ),
            ),
            'product_vendor' => array(
                'label'       => __( 'Product Vendors', 'woocommerce-exporter' ),
                'category'    => __( 'Product Vendors', 'woocommerce-exporter' ),
                'description' => __( 'Export vendor details, brands, and associated store information.', 'woocommerce-exporter' ),
            ),
            'commission'     => array(
                'label'       => __( 'Commissions', 'woocommerce-exporter' ),
                'category'    => __( 'Product Vendors', 'woocommerce-exporter' ),
                'description' => __( 'Export vendor commission data including rates, totals, and payment status.', 'woocommerce-exporter' ),
            ),
            'shipping_class' => array(
                'label'       => __( 'Shipping Class', 'woocommerce-exporter' ),
                'category'    => __( 'Shipping Class', 'woocommerce-exporter' ),
                'description' => __( 'Export shipping zones, methods, and class configurations from your store.', 'woocommerce-exporter' ),
            ),
            'ticket'         => array(
                'label'       => __( 'Tickets', 'woocommerce-exporter' ),
                'category'    => __( 'Events', 'woocommerce-exporter' ),
                'description' => __( 'Export event ticket data and booking details from your WooCommerce store.', 'woocommerce-exporter' ),
            ),
            'booking'        => array(
                'label'       => __( 'Bookings', 'woocommerce-exporter' ),
                'category'    => __( 'Bookings', 'woocommerce-exporter' ),
                'description' => __( 'Export booking data including dates, times, customer and product details.', 'woocommerce-exporter' ),
            ),
            'attribute'      => array(
                'label'       => __( 'Attributes', 'woocommerce-exporter' ),
                'category'    => __( 'Products', 'woocommerce-exporter' ),
                'description' => __( 'Export product attribute definitions and their configured term values.', 'woocommerce-exporter' ),
            ),
        );
    }

    /**
     * Count all scheduled export posts.
     *
     * @since 2.8.0
     * @return int
     */
    private function get_schedules_count() {
        $counts = wp_count_posts( 'scheduled_export' );
        return ( isset( $counts->publish ) ? (int) $counts->publish : 0 )
            + ( isset( $counts->draft ) ? (int) $counts->draft : 0 );
    }

    /**
     * Count all export archives.
     *
     * @since 2.8.0
     * @return int
     */
    private function get_archives_count() {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID)
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                 WHERE p.post_type = %s
                   AND pm.meta_key = %s",
                'attachment',
                '_woo_export_type'
            )
        );
    }

    /**
     * Get plugin version and system info.
     *
     * @since 2.8.0
     * @return array
     */
    private function get_plugin_info() {
        $memory_used  = Helper::get_current_memory_usage();
        $memory_limit = $this->parse_memory_limit( WP_MEMORY_LIMIT );

        return array(
            'version'      => Helper::get_plugin_version( false, false ),
            'memory_used'  => (int) $memory_used,
            'memory_limit' => $memory_limit,
            'load_time'    => $this->get_page_load_time(),
        );
    }

    /**
     * Get admin tab URLs.
     *
     * @since 2.8.0
     * @return array
     */
    private function get_tab_urls() {
        $base = admin_url( 'admin.php?page=woo_ce' );

        return array(
            'overview'     => esc_url_raw( $base ),
            'export'       => esc_url_raw( $base . '&tab=quick_export' ),
            'export_items' => esc_url_raw( $base . '&tab=export_items' ),
            'schedules'    => esc_url_raw( $base . '&tab=scheduled_export' ),
            'templates'    => esc_url_raw( $base . '&tab=export_template' ),
            'archives'     => esc_url_raw( $base . '&tab=archive' ),
            'settings'     => esc_url_raw( $base . '&tab=settings' ),
            'tools'        => esc_url_raw( $base . '&tab=tools' ),
        );
    }

    /**
     * Parse a WordPress memory limit string into megabytes.
     *
     * @param string $limit Memory limit string (e.g. "256M").
     *
     * @since 2.8.0
     * @return int
     */
    private function parse_memory_limit( $limit ) {
        if ( ! $limit || -1 === (int) $limit ) {
            return 0;
        }
        $unit  = strtoupper( substr( (string) $limit, -1 ) );
        $value = (int) $limit;

        switch ( $unit ) {
            case 'G':
                return $value * 1024;
            case 'M':
                return $value;
            case 'K':
                return (int) ( $value / 1024 );
            default:
                return (int) ( $value / 1024 / 1024 );
        }
    }

    /**
     * Return elapsed time since WP bootstrap.
     *
     * @since 2.8.0
     * @return float
     */
    private function get_page_load_time() {
        if ( defined( 'WP_START_TIMESTAMP' ) ) {
            return round( microtime( true ) - WP_START_TIMESTAMP, 2 );
        }
        return 0.0;
    }
}
