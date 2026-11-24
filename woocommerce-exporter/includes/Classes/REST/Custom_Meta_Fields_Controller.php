<?php
/**
 * Custom Meta Fields REST Controller.
 *
 * @since   2.7.10
 * @package VisserLabs\WSE\Classes\REST
 */

namespace VisserLabs\WSE\Classes\REST;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Classes\Export\Order;

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
     * "Available In" flag for the Quick Export surface.
     *
     * Callers name the flag through the class instead of retyping the literal:
     * an unknown flag reads as available (see is_definition_available()), so a
     * typo would otherwise disable the gate silently rather than fail loudly.
     *
     * @since 2.8.3
     */
    const AREA_QUICK_EXPORT = 'quick_export';

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
     * Definitions whose export fields are not named after the stored key.
     *
     * Attributes export as `attribute_<slug>` and order item meta as
     * `order_items_<key>`. The SPA posts a key ticked in their dedicated
     * section under the field name get_prefixed_field_names() resolves for it.
     *
     * Keep in sync with PREFIXED_META_DEFINITIONS in
     * src/js/admin-app/stores/quickExport.js.
     *
     * @since 2.8.3
     * @var string[]
     */
    private const PREFIXED_DEFINITIONS = array( 'custom_order_items', 'custom_order_products', 'custom_attributes' );

    /**
     * Option lists the legacy woo_ce_extend_<type>_fields() append as flat fields.
     *
     * Each injected field is named after the raw stored key. Only a definition
     * that has its own picker section for the type is listed, so its keys show
     * there and not also as ordinary fields. custom_users is listed only for
     * user: order, customer and subscription have no User Meta section, so their
     * injected user meta fields stay in the ordinary field list. custom_orders is
     * not injected, and prefixed definitions inject prefixed names, so none of
     * those are listed.
     *
     * @since 2.8.3
     * @var array<string,string[]>
     */
    private const INJECTED_DEFINITIONS = array(
        'product'      => array( 'custom_products' ),
        'category'     => array( 'custom_categories' ),
        'tag'          => array( 'custom_tags' ),
        'brand'        => array( 'custom_brands' ),
        'subscription' => array( 'custom_subscriptions' ),
        'user'         => array( 'custom_users' ),
        'customer'     => array( 'custom_customers' ),
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
     * Return the definitions of a type that the SPA lists in a dedicated picker section.
     *
     * Prefixed definitions (attributes, order items) are excluded and unknown
     * types return an empty array.
     *
     * @param string $type Export type slug.
     *
     * @since 2.8.3
     * @return array<array{name:string,meta_keys:string[],availability:array<string,bool>}>
     */
    public static function get_picker_definitions( string $type ): array {
        $definitions = array();

        foreach ( self::FIELD_DEFINITIONS_MAP[ $type ] ?? array() as $def ) {
            if ( in_array( $def['name'], self::PREFIXED_DEFINITIONS, true ) ) {
                continue;
            }

            $definitions[] = array(
                'name'         => $def['name'],
                'meta_keys'    => self::resolve_meta_keys( $def['name'] ),
                'availability' => self::resolve_availability( $def['name'] ),
            );
        }

        return $definitions;
    }

    /**
     * Return a type's PREFIXED definitions, shaped like a picker section.
     *
     * The counterpart of get_picker_definitions(), which leaves them out. Each
     * definition carries only the keys the engine actually registers a field
     * for, so a key it cannot export is never offered ({@see get_prefixed_field_names()}).
     *
     * @param string                                  $type        Export type slug.
     * @param array<string,array<string,string>>|null $field_names The result of get_prefixed_field_names() when the caller already has it, so it is not built again.
     *
     * @since 2.8.3
     * @return array<array{name:string,meta_keys:string[],availability:array<string,bool>}>
     */
    public static function get_prefixed_picker_definitions( string $type, ?array $field_names = null ): array {
        $definitions = array();

        foreach ( $field_names ?? self::get_prefixed_field_names( $type ) as $name => $by_key ) {
            $definitions[] = array(
                'name'         => (string) $name,
                'meta_keys'    => array_map( 'strval', array_keys( $by_key ) ),
                'availability' => self::resolve_availability( (string) $name ),
            );
        }

        return $definitions;
    }

    /**
     * Remove the custom meta fields the legacy field builders inject.
     *
     * The legacy woo_ce_extend_<type>_fields() functions append one flat field
     * per stored key of the INJECTED_DEFINITIONS lists, named after the raw key.
     * The dedicated picker section already lists those keys, so they must not
     * also appear as ordinary (Basic Info) fields.
     *
     * For every field name, the number of injected copies is the number of times
     * the name occurs across the stored keys of the type's injected definitions
     * (duplicates are counted, not merged). The remaining occurrences are the
     * built-in copies: the first of those are kept and every further occurrence
     * is dropped. Stripping does not depend on the definition's availability.
     *
     * @param string $type   Export type slug.
     * @param array  $fields Flat list of fields, each with a 'name'.
     *
     * @since 2.8.3
     * @return array
     */
    public static function strip_picker_meta_fields( string $type, array $fields ): array {
        $injected = array();
        foreach ( self::INJECTED_DEFINITIONS[ $type ] ?? array() as $definition_name ) {
            foreach ( self::resolve_meta_keys( $definition_name ) as $key ) {
                $injected[ $key ] = ( $injected[ $key ] ?? 0 ) + 1;
            }
        }

        if ( empty( $injected ) ) {
            return $fields;
        }

        $normalise = static function ( $field ): string {
            return sanitize_text_field( trim( (string) ( $field['name'] ?? '' ) ) );
        };

        $totals = array();
        foreach ( $fields as $field ) {
            $name            = $normalise( $field );
            $totals[ $name ] = ( $totals[ $name ] ?? 0 ) + 1;
        }

        $allowed = array();
        foreach ( $injected as $name => $count ) {
            $allowed[ $name ] = max( 0, ( $totals[ $name ] ?? 0 ) - $count );
        }

        $kept = array();
        foreach ( $fields as $field ) {
            $name = $normalise( $field );

            if ( isset( $allowed[ $name ] ) ) {
                if ( $allowed[ $name ] <= 0 ) {
                    continue;
                }
                --$allowed[ $name ];
            }

            $kept[] = $field;
        }

        return $kept;
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
     * @since 2.8.3 Lists the prefixed keys the engine cannot export as `unavailable_keys`.
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
                'availability' => self::resolve_availability( $def['name'] ),
            );
        }

        $prefixed_keys = self::partition_prefixed_keys( $type );

        return rest_ensure_response(
            array(
                'type'              => $type,
                'label'             => $supported_types[ $type ]['label'],
                'field_definitions' => $definitions,
                'meta_field_names'  => (object) $prefixed_keys['available'],
                'unavailable_keys'  => (object) $prefixed_keys['unavailable'],
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
     * @since 2.8.3 Lists the prefixed keys the engine cannot export as `unavailable_keys`.
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
            $current      = (array) self::resolve_availability( $def_name );
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
                'unavailable_keys'  => (object) self::get_unavailable_prefixed_keys( $type ),
                'ignored'           => $ignored,
            )
        );
    }

    /**
     * Map each stored key of a type's prefixed definitions to its export field name.
     *
     * The engine registers these fields under a name derived with sanitize_key(),
     * which the browser cannot reproduce, so the SPA reads the name from here.
     * A key the engine does not register under that name is left out (see
     * get_unavailable_prefixed_keys()), so a picker never ticks another field
     * for it. Every prefixed definition of the type is listed, even with no keys.
     *
     * @param string $type Export type slug.
     *
     * @since 2.8.3
     * @return array<string,array<string,string>> Field names keyed by definition, then by meta key.
     */
    public static function get_prefixed_field_names( string $type ): array {
        return self::partition_prefixed_keys( $type )['available'];
    }

    /**
     * List the prefixed definitions' keys the engine cannot export under their field name.
     *
     * An Order Item Meta or Order Item Product Meta key whose `order_items_<key>`
     * name is a built-in Order Items field (e.g. `Name` → `order_items_name`), or a
     * name an earlier source already claimed, is not registered by the engine.
     *
     * @param string $type Export type slug.
     *
     * @since 2.8.3
     * @return array<string,array<string,string>> The field name each key resolves to, keyed by definition, then by meta key.
     */
    public static function get_unavailable_prefixed_keys( string $type ): array {
        return self::partition_prefixed_keys( $type )['unavailable'];
    }

    /**
     * Split the stored keys of a type's prefixed definitions by whether the engine registers them.
     *
     * @param string $type Export type slug.
     *
     * @since 2.8.3
     * @return array{available: array<string,array<string,string>>, unavailable: array<string,array<string,string>>}
     */
    public static function partition_prefixed_keys( string $type ): array {
        $available   = array();
        $unavailable = array();
        $owners      = null;

        foreach ( self::FIELD_DEFINITIONS_MAP[ $type ] ?? array() as $def ) {
            if ( ! in_array( $def['name'], self::PREFIXED_DEFINITIONS, true ) ) {
                continue;
            }

            $available[ $def['name'] ] = array();
            $is_order_item_definition  = array_key_exists( $def['name'], Order::CUSTOM_ORDER_ITEM_SOURCES );
            if ( $is_order_item_definition && null === $owners ) {
                $owners = Order::instance()->get_custom_order_item_fields();
            }

            foreach ( self::resolve_meta_keys( $def['name'] ) as $key ) {
                $field_name = self::resolve_prefixed_field_name( $def['name'], $key );
                if ( $is_order_item_definition && ( $owners[ $field_name ]['source'] ?? '' ) !== $def['name'] ) {
                    $unavailable[ $def['name'] ][ $key ] = $field_name;
                    continue;
                }
                $available[ $def['name'] ][ $key ] = $field_name;
            }
        }

        return array(
            'available'   => $available,
            'unavailable' => $unavailable,
        );
    }

    /**
     * Build the export field name the engine registers for a prefixed definition's key.
     *
     * Mirrors the Order engine's `order_items_<sanitize_key(key)>` and the
     * Product field builder's `attribute_<remove_accents(sanitize_key(key))>`.
     * The Product field builders run with woo_ce_filter_sanitize_key() hooked,
     * which caps the sanitized key at 48 characters, so the attribute name is
     * derived under the same filter.
     *
     * @param string $definition_name Prefixed definition name.
     * @param string $key             Stored meta key.
     *
     * @since 2.8.3
     * @return string
     */
    private static function resolve_prefixed_field_name( string $definition_name, string $key ): string {
        if ( 'custom_attributes' === $definition_name ) {
            // Leave the filter in place when a caller already hooked it, so a nested call does not unhook it.
            $already_hooked = false !== has_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );
            if ( ! $already_hooked ) {
                add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );
            }
            $slug = remove_accents( sanitize_key( $key ) );
            if ( ! $already_hooked ) {
                remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );
            }

            return 'attribute_' . $slug;
        }

        return Order::get_order_item_field_name( $key );
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
    private static function resolve_meta_keys( string $definition_name ): array {
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
     * Whether a definition is offered in one export surface.
     *
     * The shared read side of the "Available In" setting: the Custom Meta Fields
     * screen writes the flags, the pickers read them in the SPA, and the Quick
     * Export filters read them through here.
     *
     * `$area` is an availability FLAG name (e.g. `quick_export`), not a request
     * `context` value such as `quick-export`. An unknown flag reads as
     * available, so a caller that cannot name its surface is never silently
     * narrowed.
     *
     * @param string $definition_name Definition option name (e.g. 'custom_products').
     * @param string $area            Availability flag, e.g. self::AREA_QUICK_EXPORT.
     *
     * @since 2.8.3
     * @return bool
     */
    public static function is_definition_available( string $definition_name, string $area ): bool {
        $availability = self::resolve_availability( $definition_name );

        return $availability[ $area ] ?? true;
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
    private static function resolve_availability( string $definition_name ): array {
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
