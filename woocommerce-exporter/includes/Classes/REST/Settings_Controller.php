<?php
/**
 * Settings REST Controller.
 *
 * @since   2.7.10
 * @package VisserLabs\WSE\Classes\REST
 */

namespace VisserLabs\WSE\Classes\REST;

use VisserLabs\WSE\Abstracts\Abstract_Class;

defined( 'ABSPATH' ) || exit;

/**
 * Settings_Controller.
 *
 * Provides settings read/write endpoints for the Vue SPA settings page.
 *
 * Store Exporter (free) only exposes the General, Export Formats and
 * Advanced sections — Archives, Scheduling, CRON Exports, Orders Screen,
 * Export Triggers and Audit Log are premium-only (Store Exporter Deluxe)
 * concepts and are intentionally not surfaced here.
 *
 * @since 2.7.10
 */
class Settings_Controller extends Abstract_Class {

    const REST_NAMESPACE = 'wse/v1';
    const REST_BASE      = 'settings';

    /**
     * Register hooks.
     *
     * @since 2.7.10
     * @return void
     */
    public function run() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST routes.
     *
     * @since 2.7.10
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_BASE,
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_settings' ),
                    'permission_callback' => array( $this, 'check_permission' ),
                ),
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'save_settings' ),
                    'permission_callback' => array( $this, 'check_permission' ),
                ),
            )
        );

        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_BASE . '/actions',
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'dispatch_action' ),
                'permission_callback' => array( $this, 'check_permission' ),
                'args'                => array(
                    'action' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_key',
                        'validate_callback' => function ( $value ) {
                            return in_array(
                                $value,
                                array(
                                    'reset_hidden_export_types',
                                    'reset_notices',
                                    'clear_export_transient',
                                ),
                                true
                            );
                        },
                    ),
                ),
            )
        );
    }

    /**
     * Permission callback — requires view_woocommerce_reports (or filtered capability).
     *
     * @since 2.7.10
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
     * Return current plugin settings.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 2.7.10
     * @return \WP_REST_Response
     */
    public function get_settings( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
        return rest_ensure_response(
            array(
                // General.
                'filename'                   => woo_ce_get_option( 'filename', 'export-{type}-{store}-{date}-{time}' ),
                'encoding'                   => woo_ce_get_option( 'encoding', 'UTF-8' ),
                'date_format'                => woo_ce_get_option( 'date_format', 'F j, Y' ),
                'field_escape'               => woo_ce_get_option( 'field_escape', 'all' ),
                'header_formatting'          => woo_ce_get_option( 'header_formatting', 'include' ),
                'bom'                        => (bool) woo_ce_get_option( 'bom', 1 ),
                'flush_cache'                => woo_ce_get_option( 'flush_cache', 'flush' ),
                'excel_formulas'             => (bool) woo_ce_get_option( 'excel_formulas', 0 ),
                'timeout'                    => (int) woo_ce_get_option( 'timeout', 0 ),
                // Export Formats — CSV.
                'delimiter'                  => woo_ce_get_option( 'delimiter', ',' ),
                'category_separator'         => woo_ce_get_option( 'category_separator', '|' ),
                'line_ending_formatting'     => woo_ce_get_option( 'line_ending_formatting', 'windows' ),
                // Export Formats — XML.
                'xml_attribute_url'          => (bool) woo_ce_get_option( 'xml_attribute_url', 1 ),
                'xml_attribute_title'        => (bool) woo_ce_get_option( 'xml_attribute_title', 1 ),
                'xml_attribute_date'         => (bool) woo_ce_get_option( 'xml_attribute_date', 1 ),
                'xml_attribute_time'         => (bool) woo_ce_get_option( 'xml_attribute_time', 0 ),
                'xml_attribute_export'       => (bool) woo_ce_get_option( 'xml_attribute_export', 1 ),
                'xml_attribute_orderby'      => (bool) woo_ce_get_option( 'xml_attribute_orderby', 0 ),
                'xml_attribute_order'        => (bool) woo_ce_get_option( 'xml_attribute_order', 0 ),
                'xml_attribute_limit'        => (bool) woo_ce_get_option( 'xml_attribute_limit', 0 ),
                'xml_attribute_offset'       => (bool) woo_ce_get_option( 'xml_attribute_offset', 0 ),
                // Export Formats — RSS.
                'rss_title'                  => woo_ce_get_option( 'rss_title', '' ),
                'rss_link'                   => woo_ce_get_option( 'rss_link', '' ),
                'rss_description'            => woo_ce_get_option( 'rss_description', '' ),
                // Advanced.
                'hide_archives_tab'          => (bool) woo_ce_get_option( 'hide_archives_tab', 0 ),
                'hide_scheduled_exports_tab' => (bool) woo_ce_get_option( 'hide_scheduled_exports_tab', 0 ),
                'debug_mode'                 => (bool) woo_ce_get_option( 'debug_mode', 0 ),
                'logging_mode'               => (bool) woo_ce_get_option( 'logging_mode', 0 ),
                // Meta.
                'categories'                 => $this->get_categories(),
            )
        );
    }

    /**
     * Save plugin settings.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 2.7.10
     * @return \WP_REST_Response
     */
    public function save_settings( $request ) {
        $allowed = array(
            // General.
            'filename'                   => 'sanitize_text_field',
            'encoding'                   => 'sanitize_text_field',
            'date_format'                => 'sanitize_text_field',
            'field_escape'               => 'sanitize_text_field',
            'header_formatting'          => 'sanitize_text_field',
            'bom'                        => 'boolval',
            'flush_cache'                => 'sanitize_text_field',
            'excel_formulas'             => 'boolval',
            'timeout'                    => 'absint',
            // Export Formats — CSV.
            'delimiter'                  => 'sanitize_text_field',
            'category_separator'         => 'sanitize_text_field',
            'line_ending_formatting'     => 'sanitize_text_field',
            // Export Formats — XML.
            'xml_attribute_url'          => 'boolval',
            'xml_attribute_title'        => 'boolval',
            'xml_attribute_date'         => 'boolval',
            'xml_attribute_time'         => 'boolval',
            'xml_attribute_export'       => 'boolval',
            'xml_attribute_orderby'      => 'boolval',
            'xml_attribute_order'        => 'boolval',
            'xml_attribute_limit'        => 'boolval',
            'xml_attribute_offset'       => 'boolval',
            // Export Formats — RSS.
            'rss_title'                  => 'sanitize_text_field',
            'rss_link'                   => 'esc_url_raw',
            'rss_description'            => 'sanitize_text_field',
            // Advanced.
            'hide_archives_tab'          => 'boolval',
            'hide_scheduled_exports_tab' => 'boolval',
            'debug_mode'                 => 'boolval',
            'logging_mode'               => 'boolval',
        );

        $saved = array();
        foreach ( $allowed as $key => $sanitizer ) {
            $value = $request->get_param( $key );
            if ( null !== $value ) {
                $sanitized     = $sanitizer( $value );
                $saved[ $key ] = $sanitized;
                woo_ce_update_option( $key, $sanitized );
            }
        }

        return rest_ensure_response( array_merge( array( 'saved' => true ), $saved ) );
    }

    /**
     * Dispatch a one-time maintenance action.
     *
     * Supported actions: reset_hidden_export_types, reset_notices,
     * clear_export_transient.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 2.7.10
     * @return \WP_REST_Response|\WP_Error
     */
    public function dispatch_action( \WP_REST_Request $request ) {
        $action = $request->get_param( 'action' );

        switch ( $action ) {
            case 'reset_hidden_export_types':
                woo_ce_update_option( 'hidden_export_types', false );
                return rest_ensure_response(
                    array(
                        'success' => true,
                        'message' => __( 'All hidden Export Types have been restored.', 'woocommerce-exporter' ),
                    )
                );

            case 'reset_notices':
                if ( function_exists( 'woo_ce_nuke_dismissed_notices' ) ) {
                    woo_ce_nuke_dismissed_notices();
                }
                return rest_ensure_response(
                    array(
                        'success' => true,
                        'message' => __( 'All dismissed notices have been restored.', 'woocommerce-exporter' ),
                    )
                );

            case 'clear_export_transient':
                $exporter = new \VisserLabs\WSE\Classes\Exporter();
                $exporter->clear_export_transient();
                return rest_ensure_response(
                    array(
                        'success' => true,
                        'message' => __( 'Export transient cache has been cleared.', 'woocommerce-exporter' ),
                    )
                );

            default:
                return new \WP_Error(
                    'wse_unknown_action',
                    __( 'Unknown action.', 'woocommerce-exporter' ),
                    array( 'status' => 400 )
                );
        }
    }

    /**
     * Return list of settings categories.
     *
     * @since 2.7.10
     * @return array
     */
    private function get_categories() {
        return array(
            array(
                'key'         => 'general',
                'label'       => __( 'General', 'woocommerce-exporter' ),
                'description' => __( 'Export behavior and preferences', 'woocommerce-exporter' ),
            ),
            array(
                'key'         => 'formats',
                'label'       => __( 'Export Formats', 'woocommerce-exporter' ),
                'description' => __( 'Configure CSV, XML and RSS formats', 'woocommerce-exporter' ),
            ),
            array(
                'key'         => 'advanced',
                'label'       => __( 'Advanced', 'woocommerce-exporter' ),
                'description' => __( 'System, performance and debug options', 'woocommerce-exporter' ),
            ),
        );
    }
}
