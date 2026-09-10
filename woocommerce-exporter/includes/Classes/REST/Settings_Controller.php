<?php
/**
 * Settings REST Controller.
 *
 * @since   2.7.10
 * @package VisserLabs\WSE\Classes\REST
 */

namespace VisserLabs\WSE\Classes\REST;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Abstracts\Abstract_Exporter;

defined( 'ABSPATH' ) || exit;

/**
 * Settings_Controller.
 *
 * Provides settings read/write endpoints for the Vue SPA settings page.
 *
 * Store Exporter (free) only exposes the General, Export Formats and
 * Advanced sections — Archives, Scheduling, CRON Exports, Orders Screen,
 * Export Triggers and Audit Log are premium-only (Store Exporter Deluxe)
 * concepts and are intentionally not surfaced here. Within Export Formats,
 * only the CSV settings are readable/writable here; the XML/RSS attribute
 * options are Deluxe-only and are intentionally excluded from both the read
 * payload and the save allowlist.
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
        $debug_option = (bool) woo_ce_get_option( 'debug_mode', 0 );

        // A store upgraded from the pre-SPA screen may still hold the legacy 1/0.
        // Returned verbatim it matches neither radio and renders both unselected,
        // so map it to the slug the export engine resolves it to. Delegated rather
        // than re-tested here so the engine and the screen cannot disagree about
        // what a stored value means, including what an unset option defaults to.
        $header_formatting = Abstract_Exporter::stored_header_formatting_includes_headers() ? 'include' : 'exclude';

        return rest_ensure_response(
            array(
                // General.
                'filename'               => woo_ce_get_option( 'filename', 'export-{type}-{store}-{date}-{time}' ),
                'encoding'               => woo_ce_get_option( 'encoding', 'UTF-8' ),
                'date_format'            => woo_ce_get_option( 'date_format', 'F j, Y' ),
                'field_escape'           => woo_ce_get_option( 'field_escape', 'all' ),
                'header_formatting'      => $header_formatting,
                'bom'                    => (bool) woo_ce_get_option( 'bom', 1 ),
                'flush_cache'            => woo_ce_get_option( 'flush_cache', 'flush' ),
                'excel_formulas'         => (bool) woo_ce_get_option( 'excel_formulas', 0 ),
                'csv_formula_escaping'   => (bool) woo_ce_get_option( 'csv_formula_escaping', 1 ),
                'timeout'                => (int) woo_ce_get_option( 'timeout', 0 ),
                // Export Formats — CSV.
                'delimiter'              => woo_ce_get_option( 'delimiter', ',' ),
                'category_separator'     => woo_ce_get_option( 'category_separator', '|' ),
                'line_ending_formatting' => woo_ce_get_option( 'line_ending_formatting', 'windows' ),
                // Advanced.
                // debug_mode is the raw option, which is what the Advanced
                // toggle edits. The banner must instead follow the constant —
                // see get_debug_mode_state().
                'debug_mode'             => $debug_option,
                'debug_mode_active'      => $this->is_debug_constant_on(),
                'debug_mode_overridden'  => $this->is_debug_constant_overridden( $debug_option ),
                'logging_mode'           => (bool) woo_ce_get_option( 'logging_mode', 0 ),
                // Meta.
                'categories'             => $this->get_categories(),
                'premium_formats'        => $this->get_premium_format_definitions(),
            )
        );
    }

    /**
     * Whether the WOO_CE_DEBUG constant is on.
     *
     * The constant, not the option, is what every server-side export gate
     * tests, so it is the only honest source for the SPA's debug banner.
     *
     * @since 2.8.0
     * @return bool
     */
    private function is_debug_constant_on() {
        return defined( 'WOO_CE_DEBUG' ) && WOO_CE_DEBUG;
    }

    /**
     * Whether WOO_CE_DEBUG is forced from outside the plugin's own option.
     *
     * The bootstrap defines WOO_CE_DEBUG from the debug_mode option only when
     * the constant is not already set, so a wp-config.php define() wins and the
     * two legitimately disagree. When they disagree, the option cannot change
     * the gate, and the SPA must keep reporting the constant.
     *
     * @since 2.8.0
     * @param bool $debug_option The debug_mode option as stored at the start of
     *                           this request, before any save in this request.
     * @return bool
     */
    private function is_debug_constant_overridden( $debug_option ) {
        return $this->is_debug_constant_on() !== (bool) $debug_option;
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
        // Read BEFORE the write. WOO_CE_DEBUG was frozen at `init`, earlier in
        // this same request, so it cannot reflect a value saved below. Only the
        // pre-save option can tell us whether the constant was already forced
        // from outside (wp-config.php) or merely mirrors the option.
        $debug_option_before = (bool) woo_ce_get_option( 'debug_mode', 0 );

        $allowed = array(
            // General.
            'filename'               => 'sanitize_text_field',
            'encoding'               => 'sanitize_text_field',
            'date_format'            => 'sanitize_text_field',
            'field_escape'           => 'sanitize_text_field',
            // Canonicalise through the engine's own normaliser rather than a second
            // hardcoded allowlist: `sanitize_text_field()` alone would let a bad
            // client store `yes` or `includ`, which no radio matches. Routing the
            // write through normalize_header_formatting() keeps the set of "omit
            // headers" values in Abstract_Exporter::HEADER_FORMATTING_EXCLUDED alone.
            // It also accepts the legacy `1`/`0` shapes, resolves an unknown value
            // to `include` (the engine's own default) and leaves an empty one on
            // the currently stored setting instead of silently flipping it.
            'header_formatting'      => static function ( $value ) {
                return Abstract_Exporter::normalize_header_formatting( $value ) ? 'include' : 'exclude';
            },
            'bom'                    => 'boolval',
            'flush_cache'            => 'sanitize_text_field',
            'excel_formulas'         => 'boolval',
            'csv_formula_escaping'   => array( __CLASS__, 'sanitize_bool_to_int' ),
            'timeout'                => 'absint',
            // Export Formats — CSV.
            'delimiter'              => 'sanitize_text_field',
            'category_separator'     => 'sanitize_text_field',
            'line_ending_formatting' => 'sanitize_text_field',
            // Advanced.
            'debug_mode'             => 'boolval',
            'logging_mode'           => 'boolval',
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

        // The client resolves the banner as: overridden ? constant : saved
        // option. Without the override flag the client would have to choose
        // between a constant that is one request stale and an option that a
        // wp-config.php define() makes irrelevant — the A25 defect.
        return rest_ensure_response(
            array_merge(
                array( 'saved' => true ),
                $saved,
                array(
                    'debug_mode_active'     => $this->is_debug_constant_on(),
                    'debug_mode_overridden' => $this->is_debug_constant_overridden( $debug_option_before ),
                )
            )
        );
    }

    /**
     * Coerce a request value to the integer 1/0 the legacy settings screen and
     * the export engine read back with woo_ce_get_option().
     *
     * @since 2.8.1
     *
     * @param mixed $value Raw request value.
     * @return int 1 when truthy, 0 otherwise.
     */
    private static function sanitize_bool_to_int( $value ) {
        return $value ? 1 : 0;
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
                'description' => __( 'Configure CSV export formatting. XML and RSS require Store Exporter Deluxe.', 'woocommerce-exporter' ),
            ),
            array(
                'key'         => 'advanced',
                'label'       => __( 'Advanced', 'woocommerce-exporter' ),
                'description' => __( 'System, performance and debug options', 'woocommerce-exporter' ),
            ),
        );
    }

    /**
     * Build the Deluxe-gated format definitions rendered by the Export
     * Formats settings screen (XML/RSS cards), sourced from the same
     * allowlist/upsell helpers as the Quick Export format tiles.
     *
     * @since 2.8.0
     * @return array<array{key:string,upsell_url:string}>
     */
    private function get_premium_format_definitions() {
        $formats = array();
        foreach ( array( 'xml', 'rss' ) as $format ) {
            if ( ! woo_ce_is_premium_export_format( $format ) ) {
                continue;
            }
            $formats[] = array(
                'key'        => $format,
                'upsell_url' => woo_ce_get_export_format_upsell_url( $format ),
            );
        }
        return $formats;
    }
}
