<?php
/**
 * Custom Meta Fields REST Controller.
 *
 * @since   2.7.10
 * @package VisserLabs\WSE\Classes\REST
 */

namespace VisserLabs\WSE\Classes\REST;

use VisserLabs\WSE\Abstracts\Abstract_Class;

defined( 'ABSPATH' ) || exit;

/**
 * Custom_Meta_Fields_Controller.
 *
 * Provides CRUD REST endpoints for per-export-type custom meta field
 * definitions. Each export type can have one or more named definitions
 * (e.g. Products has both "Custom Product Meta" and "Custom Product
 * Attributes"). Every definition stores its own meta_keys array and
 * availability flags independently.
 *
 * Routes:
 *   GET  wse/v1/custom-meta-fields
 *   GET  wse/v1/custom-meta-fields/{type}
 *   PUT  wse/v1/custom-meta-fields/{type}
 *
 * @since 2.7.10
 */
class Custom_Meta_Fields_Controller extends Abstract_Class {

    const REST_NAMESPACE = 'wse/v1';
    const REST_BASE      = 'custom-meta-fields';

    /**
     * Per-type field definition map.
     *
     * Each entry is an array of definition descriptors. 'name' is the woo_ce
     * option key suffix used to store that definition's meta keys. Types not
     * listed here fall back to a single definition keyed as "custom_{slug}".
     *
     * @since 2.7.10
     * @var array<string, array<array{name:string,label:string,description:string}>>
     */
    private const FIELD_DEFINITIONS_MAP = array(
        'product'      => array(
            array(
                'name'        => 'custom_products',
                'label'       => 'Product Meta',
                'description' => 'Post meta keys to append as extra columns to each product export row — one key per line.',
            ),
            array(
                'name'        => 'custom_attributes',
                'label'       => 'Product Attributes',
                'description' => 'WooCommerce attribute taxonomy slugs to export as separate columns — use lowercase with hyphens (e.g. pa_color). One slug per line.',
            ),
        ),
        'category'     => array(
            array(
                'name'        => 'custom_categories',
                'label'       => 'Category Meta',
                'description' => 'Term meta keys to append as extra columns to each category export row — one key per line.',
            ),
        ),
        'tag'          => array(
            array(
                'name'        => 'custom_tags',
                'label'       => 'Tag Meta',
                'description' => 'Term meta keys to append as extra columns to each tag export row — one key per line.',
            ),
        ),
        'brand'        => array(
            array(
                'name'        => 'custom_brands',
                'label'       => 'Brand Meta',
                'description' => 'Term meta keys to append as extra columns to each brand export row — one key per line.',
            ),
        ),
        'order'        => array(
            array(
                'name'        => 'custom_orders',
                'label'       => 'Order Meta',
                'description' => 'Post meta keys to append as extra columns to each order export row — one key per line.',
            ),
            array(
                'name'        => 'custom_order_items',
                'label'       => 'Order Item Meta',
                'description' => 'Order item meta keys to append as extra columns to each order export row — one key per line.',
            ),
            array(
                'name'        => 'custom_order_products',
                'label'       => 'Order Item Product Meta',
                'description' => 'Product meta keys from order line items to append as extra columns to each order export row — one key per line.',
            ),
        ),
        'subscription' => array(
            array(
                'name'        => 'custom_subscriptions',
                'label'       => 'Subscription Meta',
                'description' => 'Post meta keys to append as extra columns to each subscription export row — one key per line.',
            ),
        ),
        'user'         => array(
            array(
                'name'        => 'custom_users',
                'label'       => 'User Meta',
                'description' => 'User meta keys to append as extra columns to each user export row — one key per line.',
            ),
        ),
        'customer'     => array(
            array(
                'name'        => 'custom_customers',
                'label'       => 'Customer Meta',
                'description' => 'User meta keys to append as extra columns to each customer export row — one key per line.',
            ),
        ),
    );

    /**
     * Register REST API hooks.
     *
     * @since 2.7.10
     * @return void
     */
    public function run() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Build the supported export types map from woo_ce_get_export_types().
     *
     * @since 2.7.10
     * @return array<string, array{label: string, description: string}>
     */
    private function get_supported_types(): array {
        if ( ! function_exists( 'woo_ce_get_export_types' ) ) {
            return array();
        }

        $supported_slugs = array_keys( self::FIELD_DEFINITIONS_MAP );
        $types           = array();

        foreach ( woo_ce_get_export_types() as $slug => $label ) {
            if ( ! in_array( $slug, $supported_slugs, true ) ) {
                continue;
            }

            /* translators: %s: export type label */
            $description = sprintf( __( 'Manage custom meta fields for %s exports.', 'woocommerce-exporter' ), strtolower( $label ) );

            $types[ $slug ] = array(
                'label'       => $label,
                'description' => $description,
            );
        }

        return $types;
    }

    /**
     * Return the field definitions for a given export type.
     *
     * Falls back to a single definition keyed as "custom_{slug}" for types
     * not explicitly listed in FIELD_DEFINITIONS_MAP.
     *
     * @param string $type Export type slug.
     *
     * @since 2.7.10
     * @return array<array{name:string,label:string,description:string}>
     */
    private function get_type_field_definitions( string $type ): array {
        if ( isset( self::FIELD_DEFINITIONS_MAP[ $type ] ) ) {
            return self::FIELD_DEFINITIONS_MAP[ $type ];
        }

        $human = ucwords( str_replace( '_', ' ', $type ) );

        return array(
            array(
                'name'        => 'custom_' . $type,
                /* translators: %s: export type human label */
                'label'       => sprintf( __( '%s Meta', 'woocommerce-exporter' ), $human ),
                /* translators: %s: export type human label (lowercase) */
                'description' => sprintf( __( 'Meta keys to append as extra columns to each %s export row — one key per line.', 'woocommerce-exporter' ), strtolower( $human ) ),
            ),
        );
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
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_types_list' ),
                'permission_callback' => array( $this, 'check_permission' ),
            )
        );

        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_BASE . '/(?P<type>[a-z_]+)',
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_type' ),
                    'permission_callback' => array( $this, 'check_permission' ),
                    'args'                => array(
                        'type' => array(
                            'required'          => true,
                            'type'              => 'string',
                            'sanitize_callback' => 'sanitize_key',
                        ),
                    ),
                ),
                array(
                    'methods'             => \WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_type' ),
                    'permission_callback' => array( $this, 'check_permission' ),
                    'args'                => array(
                        'type'              => array(
                            'required'          => true,
                            'type'              => 'string',
                            'sanitize_callback' => 'sanitize_key',
                        ),
                        'field_definitions' => array(
                            'required' => true,
                            'type'     => 'array',
                            'items'    => array( 'type' => 'object' ),
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * Permission callback — requires view_woocommerce_reports capability.
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
     * GET wse/v1/custom-meta-fields — list all export types with total field counts.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 2.7.10
     * @return \WP_REST_Response
     */
    public function get_types_list( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
        $types = array();

        foreach ( $this->get_supported_types() as $type_slug => $meta ) {
            $total_keys = 0;
            foreach ( $this->get_type_field_definitions( $type_slug ) as $def ) {
                $total_keys += count( $this->resolve_meta_keys( $def['name'] ) );
            }

            $types[] = array(
                'type'        => $type_slug,
                'label'       => $meta['label'],
                'description' => $meta['description'],
                'field_count' => $total_keys,
            );
        }

        return rest_ensure_response( array( 'types' => $types ) );
    }

    /**
     * GET wse/v1/custom-meta-fields/{type} — field definitions with meta keys
     * and availability for one export type.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 2.7.10
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_type( $request ) {
        $type            = $request->get_param( 'type' );
        $supported_types = $this->get_supported_types();

        if ( ! isset( $supported_types[ $type ] ) ) {
            return new \WP_Error(
                'wse_invalid_type',
                __( 'Invalid export type.', 'woocommerce-exporter' ),
                array( 'status' => 404 )
            );
        }

        $definitions = array();
        foreach ( $this->get_type_field_definitions( $type ) as $def ) {
            $definitions[] = array(
                'name'         => $def['name'],
                'label'        => $def['label'],
                'description'  => $def['description'],
                'meta_keys'    => $this->resolve_meta_keys( $def['name'] ),
                'availability' => $this->resolve_availability( $def['name'] ),
            );
        }

        return rest_ensure_response(
            array(
                'type'              => $type,
                'label'             => $supported_types[ $type ]['label'],
                'field_definitions' => $definitions,
            )
        );
    }

    /**
     * PUT wse/v1/custom-meta-fields/{type} — save field definitions.
     *
     * Each definition in the payload is validated against the known definitions
     * for the type. Unknown definition names are ignored and listed in `ignored`.
     * An availability flag missing from the payload keeps its saved value.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 2.7.10
     * @return \WP_REST_Response|\WP_Error
     */
    public function update_type( $request ) {
        $type            = $request->get_param( 'type' );
        $supported_types = $this->get_supported_types();

        if ( ! isset( $supported_types[ $type ] ) ) {
            return new \WP_Error(
                'wse_invalid_type',
                __( 'Invalid export type.', 'woocommerce-exporter' ),
                array( 'status' => 404 )
            );
        }

        $valid_def_names = array_column( $this->get_type_field_definitions( $type ), 'name' );
        $raw_definitions = (array) $request->get_param( 'field_definitions' );
        $saved           = array();
        $ignored         = array();

        foreach ( $raw_definitions as $raw_def ) {
            $raw_def  = (array) $raw_def;
            $def_name = sanitize_key( $raw_def['name'] ?? '' );

            if ( ! in_array( $def_name, $valid_def_names, true ) ) {
                $ignored[] = $def_name;
                continue;
            }

            $keys = array_values(
                array_filter(
                    array_map( 'sanitize_text_field', (array) ( $raw_def['meta_keys'] ?? array() ) )
                )
            );

            $raw_avail    = (array) ( $raw_def['availability'] ?? array() );
            $current      = (array) $this->resolve_availability( $def_name );
            $availability = array();

            foreach ( array( 'quick_export', 'scheduled_export', 'export_template' ) as $flag ) {
                $availability[ $flag ] = array_key_exists( $flag, $raw_avail ) ? ! empty( $raw_avail[ $flag ] ) : ! empty( $current[ $flag ] );
            }

            woo_ce_update_option( $def_name, $keys );
            woo_ce_update_option( $def_name . '_availability', $availability );

            $saved[] = array(
                'name'         => $def_name,
                'meta_keys'    => $keys,
                'availability' => $availability,
            );
        }

        return rest_ensure_response(
            array(
                'success'           => true,
                'type'              => $type,
                'label'             => $supported_types[ $type ]['label'],
                'field_definitions' => $saved,
                'ignored'           => $ignored,
            )
        );
    }

    /**
     * Retrieve and normalise stored meta keys for a definition name.
     *
     * Handles both array storage (modern) and newline-delimited string (legacy).
     *
     * @param string $definition_name woo_ce option suffix (e.g. 'custom_products').
     *
     * @since 2.7.10
     * @return string[]
     */
    private function resolve_meta_keys( string $definition_name ): array {
        $stored = woo_ce_get_option( $definition_name, array() );

        if ( empty( $stored ) ) {
            return array();
        }

        if ( is_string( $stored ) ) {
            $stored = explode( "\n", trim( $stored ) );
        }

        return array_values(
            array_filter( array_map( 'sanitize_text_field', (array) $stored ) )
        );
    }

    /**
     * Retrieve availability settings for a definition, defaulting all to enabled.
     *
     * The availability option key is "{definition_name}_availability"
     * (e.g. "custom_products_availability").
     *
     * @param string $definition_name Definition option name (e.g. 'custom_products').
     *
     * @since 2.7.10
     * @return array{quick_export: bool, scheduled_export: bool, export_template: bool}
     */
    private function resolve_availability( string $definition_name ): array {
        $saved = woo_ce_get_option( $definition_name . '_availability', null );

        if ( is_array( $saved ) ) {
            return array(
                'quick_export'     => ! empty( $saved['quick_export'] ),
                'scheduled_export' => ! empty( $saved['scheduled_export'] ),
                'export_template'  => ! empty( $saved['export_template'] ),
            );
        }

        return array(
            'quick_export'     => true,
            'scheduled_export' => true,
            'export_template'  => true,
        );
    }
}
