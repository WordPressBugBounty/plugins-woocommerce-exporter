<?php
/**
 * Overview REST Controller.
 *
 * @since   5.5.0
 * @package VisserLabs\WSE\Classes\REST
 */

namespace VisserLabs\WSE\Classes\REST;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Helpers\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Overview_Controller.
 *
 * Provides dashboard summary data for the Vue overview page.
 *
 * @since 5.5.0
 */
class Overview_Controller extends Abstract_Class {

    const REST_NAMESPACE = 'wse/v1';
    const REST_BASE      = 'overview';

    /**
     * Register hooks.
     *
     * @since 5.5.0
     * @return void
     */
    public function run() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST routes.
     *
     * @since 5.5.0
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_BASE,
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_overview' ),
                'permission_callback' => array( $this, 'check_permission' ),
            )
        );
    }

    /**
     * Permission callback.
     *
     * @since 5.5.0
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
     * Return overview dashboard data.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 5.5.0
     * @return \WP_REST_Response
     */
    public function get_overview( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
        return rest_ensure_response(
            array(
                'plugin' => $this->get_plugin_info(),
                'urls'   => $this->get_tab_urls(),
            )
        );
    }

    /**
     * Get plugin version and system info.
     *
     * @since 5.5.0
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
     * @since 5.5.0
     * @return array
     */
    private function get_tab_urls() {
        $base = admin_url( 'admin.php?page=woo_ce' );

        return array(
            'overview'     => esc_url_raw( $base ),
            'export'       => esc_url_raw( $base . '&tab=export' ),
            'export_items' => esc_url_raw( $base . '&tab=export_items' ),
            'schedules'    => esc_url_raw( $base . '&tab=scheduled_export' ),
            'templates'    => esc_url_raw( $base . '&tab=export_template' ),
            'archives'     => esc_url_raw( $base . '&tab=archive' ),
            'settings'     => esc_url_raw( $base . '&tab=settings' ),
            'tools'        => esc_url_raw( $base . '&tab=tools' ),
        );
    }

    /**
     * Parse a WordPress memory limit string (e.g. "256M") into megabytes.
     *
     * @since 5.5.0
     * @param string $limit Memory limit string.
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
     * Return elapsed time since WP bootstrap (in seconds).
     *
     * @since 5.5.0
     * @return float
     */
    private function get_page_load_time() {
        if ( defined( 'WP_START_TIMESTAMP' ) ) {
            return round( microtime( true ) - WP_START_TIMESTAMP, 2 );
        }
        return 0.0;
    }
}
