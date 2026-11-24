<?php
/**
 * Quick Export REST Controller.
 *
 * @since   5.6.0
 * @package VisserLabs\WSE\Classes\REST
 */

namespace VisserLabs\WSE\Classes\REST;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Classes\Export\Field_Registry;
use VisserLabs\WSE\Classes\Export\Order as Order_Export;
use VisserLabs\WSE\Helpers\Export as Export_Helper;
use VisserLabs\WSE\Helpers\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Quick_Export_Controller.
 *
 * Provides REST endpoints consumed by the Quick Export Vue widget:
 *   GET /wse/v1/quick-export/config          – export types, formats, templates, nonce
 *   GET /wse/v1/quick-export/fields?type=X   – grouped field list (plus custom_meta_fields) for a given type
 *   GET /wse/v1/quick-export/filters?type=X  – filter / sort options for a given type
 *
 * @since 5.6.0
 */
class Quick_Export_Controller extends Abstract_Class {

    const REST_NAMESPACE = 'wse/v1';
    const REST_BASE      = 'quick-export';

    /**
     * Maximum number of choices rendered into a filter multiselect. Keeps the
     * /filters payload bounded on a large catalog or term set.
     *
     * @since 2.8.2
     */
    const CHOICE_LIMIT = 200;

    /**
     * `context` value the Quick Export wizard sends to /filters.
     *
     * Keep in sync with the request in src/js/admin-app/stores/quickExport.js.
     *
     * @since 2.8.3
     */
    const QUICK_EXPORT_CONTEXT = 'quick-export';

    /**
     * The "Available In" flag each requesting surface is gated on.
     *
     * Only the `context` parameter tells the callers of /filters apart. A
     * request with no context — or one this map does not know — is left
     * ungated, so a viewer running a cached pre-upgrade bundle keeps the
     * filters it already had. The flag scopes where a custom meta definition
     * is offered; it is not a security boundary.
     *
     * @since 2.8.3
     * @var array<string, string>
     */
    private const CONTEXT_AVAILABILITY_FLAGS = array(
        self::QUICK_EXPORT_CONTEXT => Custom_Meta_Fields_Controller::AREA_QUICK_EXPORT,
    );

    /**
     * Register hooks.
     *
     * @since 5.6.0
     * @return void
     */
    public function run() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST routes.
     *
     * @since 5.6.0
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_BASE . '/config',
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_config' ),
                'permission_callback' => array( $this, 'check_permission' ),
                'args'                => array(
                    'refresh' => array(
                        'required'          => false,
                        'type'              => 'boolean',
                        'default'           => false,
                        'sanitize_callback' => 'rest_sanitize_boolean',
                    ),
                ),
            )
        );

        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_BASE . '/fields',
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_fields' ),
                'permission_callback' => array( $this, 'check_permission' ),
                'args'                => array(
                    'type' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_BASE . '/filters',
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_filters' ),
                'permission_callback' => array( $this, 'check_permission' ),
                'args'                => array(
                    'type' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );
    }

    /**
     * Permission callback – requires view_woocommerce_reports capability.
     *
     * @since 5.6.0
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
     * GET /wse/v1/quick-export/config
     *
     * Returns all supported export types enriched with record counts, the list
     * of available export formats, and the AJAX nonce required by the Quick
     * Export AJAX handler.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 5.6.0
     * @return \WP_REST_Response
     */
    public function get_config( $request ) {
        // When the client explicitly requests a refresh (e.g. user clicks the
        // Quick Export "Refresh" button), bust the cached record-count
        // transient so counts are recomputed from the database.
        if ( ! empty( $request['refresh'] ) ) {
            delete_transient( WOO_CE_PREFIX . '_export_type_count' );
        }

        return rest_ensure_response(
            array(
                'types'      => $this->get_export_types_with_counts(),
                'formats'    => $this->get_export_formats(),
                'nonce'      => wp_create_nonce( 'manual_export' ),
                'ajaxUrl'    => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
                'dateFormat' => (string) get_option( 'date_format', 'F j, Y' ),
            )
        );
    }

    /**
     * GET /wse/v1/quick-export/fields?type=X
     *
     * Returns the field list for the given export type, grouped by logical
     * section (e.g. "Billing Address", "Shipping Address").  Each field carries
     * a `selected` flag derived from the saved per-type option. Registered
     * custom meta keys are not part of the groups; they are returned separately
     * as `custom_meta_fields` (name, definition, column_key, selected, available).
     *
     * The field names the type defaults to are returned as `default_fields`:
     * the curated set for the type, or every served field name when the type
     * has no curated set that matches one of its fields. It is the same list
     * the `selected` flags follow when nothing is saved.
     *
     * `meta_field_names` maps the keys of the prefixed custom meta definitions
     * (order item meta, attributes) to the export field name the engine
     * registers for them, keyed by definition, so the SPA can post a key ticked
     * in their section.
     *
     * `field_order` lists every field name, custom meta keys included, in the
     * order the groups served them before the meta keys were stripped. The SPA
     * seeds its column order from it, so a saved meta column keeps the position
     * it had when it was still served as an ordinary field.
     *
     * `saved_field_order` is the column order the store owner last exported
     * with, flat and across groups, or an empty array when they have never run
     * an export of this type. The groups cannot carry it: group_fields()
     * buckets the fields into categories, which keeps the order within a group
     * but loses it between groups.
     *
     * Both order lists name a column the way the Manage Fields screen holds its
     * position: by the meta key for a prefixed definition the type does not
     * serve as an ordinary field, and by the field name for everything else.
     * The one exception is a bare key a plain definition registers as well:
     * the two export different columns, so the prefixed one stays under its
     * derived field name and each column keeps a slot of its own. Every picker
     * row carries the name its column is held under as `column_key`, so the SPA
     * places and labels it under the same one.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 5.6.0
     * @since 2.8.3 Adds `custom_meta_fields`, `field_order`, `saved_field_order` and `default_fields`.
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_fields( $request ) {
        $type = $request->get_param( 'type' );

        if ( ! $this->is_valid_type( $type ) ) {
            return new \WP_Error(
                'invalid_export_type',
                __( 'Invalid export type.', 'woocommerce-exporter' ),
                array( 'status' => 400 )
            );
        }

        $fields = $this->load_fields_for_type( $type );
        if ( null === $fields ) {
            return new \WP_Error(
                'fields_unavailable',
                __( 'Fields for this export type are not available.', 'woocommerce-exporter' ),
                array( 'status' => 404 )
            );
        }

        $saved_fields = $this->get_saved_field_keys( $type );
        $definitions  = Custom_Meta_Fields_Controller::get_picker_definitions( $type );
        $field_names  = Custom_Meta_Fields_Controller::get_prefixed_field_names( $type );
        $meta_columns = $this->unserved_meta_column_keys( $fields, $definitions, $field_names );
        $saved_order  = $this->get_saved_field_order( $type, $fields, $definitions, $meta_columns );
        $field_order  = $this->flatten_group_names( $this->group_fields( $type, $fields, $saved_fields ) );
        $field_order  = $this->to_column_keys( $meta_columns, $field_order );
        $saved_order  = $this->to_column_keys( $meta_columns, $saved_order );
        $fields       = Custom_Meta_Fields_Controller::strip_picker_meta_fields( $type, $fields );
        $groups       = $this->add_group_display_labels( $this->group_fields( $type, $fields, $saved_fields ) );

        // The picker lists the plain definitions plus the prefixed keys this
        // type does not serve as ordinary fields — the only place a ticked
        // Order Item Meta column can be seeded back from on a page load.
        $picker_definitions = array_merge(
            $definitions,
            $this->unserved_prefixed_definitions( $type, $meta_columns, $field_names )
        );

        return rest_ensure_response(
            array(
                'type'               => $type,
                'groups'             => $groups,
                'custom_meta_fields' => $this->get_custom_meta_fields_for_picker( $picker_definitions, $saved_fields, $field_names, $meta_columns ),
                'meta_field_names'   => (object) $field_names,
                'field_order'        => $field_order,
                'saved_field_order'  => $saved_order,
                'default_fields'     => $this->resolve_default_field_keys( $type, $fields ),
            )
        );
    }

    /**
     * Custom meta keys listed in the dedicated Manage Fields section.
     *
     * Every picker definition is returned. A key is `selected` when it is part
     * of the user's last saved selection, and `available` when its definition
     * is enabled for Quick Export.
     *
     * The saved selection names a column the way the EXPORT posted it, which
     * for a key this type does not serve as an ordinary field is the derived
     * field name (`order_items_<key>`) and not the bare meta key the picker
     * lists it under. Matching the bare key alone would leave the row unticked
     * on the next visit, so the column would be dropped from the next export.
     *
     * De-duplication follows the same name: two definitions that resolve a key
     * to the same exported column get one row, mirroring the `fields.has(name)`
     * rule the SPA applies in `selectedCustomMetaFields()`. A plain definition
     * and a prefixed one that merely share a bare key export different
     * columns, so both keep a row.
     *
     * `column_key` is the name the Manage Fields screen holds the row's
     * position and label override under — the same name `field_order` and
     * `saved_field_order` list it by. It is the bare meta key, except for a
     * prefixed row whose key a plain definition registers as well: that row
     * keeps its derived field name, so the two stop sharing one slot.
     *
     * @param array                              $definitions  Picker definitions, plain ones first.
     * @param string[]                           $saved_keys   Column names the user previously saved as enabled.
     * @param array<string,array<string,string>> $field_names  From Custom_Meta_Fields_Controller::get_prefixed_field_names().
     * @param array<string,string>               $meta_columns From unserved_meta_column_keys().
     *
     * @since 2.8.3
     * @return array<array{name:string,definition:string,column_key:string,selected:bool,available:bool}>
     */
    private function get_custom_meta_fields_for_picker( array $definitions, array $saved_keys, array $field_names, array $meta_columns ): array {
        $result       = array();
        $seen         = array();
        $saved_lookup = array_flip( $saved_keys );

        foreach ( $definitions as $def ) {
            // Absent for a plain definition, whose keys are saved under the bare key.
            $derived = $field_names[ $def['name'] ] ?? null;

            foreach ( $def['meta_keys'] as $key ) {
                $column = null === $derived ? $key : ( $derived[ $key ] ?? '' );

                if ( '' === $column || isset( $seen[ $column ] ) ) {
                    continue;
                }

                // Only a prefixed row can be held under anything but its bare
                // key; reading the map for a plain one could pick up the entry
                // of a prefixed column whose derived name equals that key.
                $column_key = null === $derived ? (string) $key : ( $meta_columns[ $column ] ?? (string) $key );

                $seen[ $column ] = true;
                $result[]        = array(
                    'name'       => $key,
                    'definition' => $def['name'],
                    'column_key' => $column_key,
                    'selected'   => isset( $saved_lookup[ $column ] ),
                    'available'  => ! empty( $def['availability'][ Custom_Meta_Fields_Controller::AREA_QUICK_EXPORT ] ),
                );
            }
        }

        return $result;
    }

    /**
     * Flatten grouped fields into their field names, in served order.
     *
     * @param array $groups Groups from group_fields().
     *
     * @since 2.8.3
     * @return string[]
     */
    private function flatten_group_names( array $groups ): array {
        $names = array();

        foreach ( $groups as $group ) {
            foreach ( $group['fields'] as $field ) {
                $names[] = $field['name'];
            }
        }

        return $names;
    }

    /**
     * A prefixed definition's columns the type does NOT serve as ordinary fields.
     *
     * The engine registers an Order Item Meta, Order Item Product Meta or
     * Attribute key under a derived field name (`order_items_<key>`,
     * `attribute_<slug>`). Whether the Manage Fields screen then holds that
     * column under the derived name or under the meta key depends on whether
     * the type's own field list carries it:
     *
     * - It does not for the Order Item definitions, so the picker lists those
     *   keys in its own Custom Meta Fields section and saves a row's position
     *   under the bare meta key.
     * - It DOES for Product attributes: `woo_ce_extend_product_fields()`
     *   appends an ordinary `attribute_<slug>` field for every stored key, and
     *   `strip_picker_meta_fields()` leaves it in place, so the SPA orders it
     *   by that name like any other field.
     *
     * Only the first case is returned. A key a plain definition registers as
     * well is the exception: such a column stays under its derived field name,
     * which no other column can claim.
     *
     * @param array                              $fields      Flat field list from load_fields_for_type().
     * @param array                              $definitions The type's PLAIN picker definitions.
     * @param array<string,array<string,string>> $field_names From Custom_Meta_Fields_Controller::get_prefixed_field_names().
     *
     * @since 2.8.3
     * @return array<string,string> The name each derived field name is held under.
     */
    private function unserved_meta_column_keys( array $fields, array $definitions, array $field_names ): array {
        $served = array_fill_keys( array_column( $fields, 'name' ), true );
        $keys   = array();
        $plain  = array();

        foreach ( $definitions as $def ) {
            foreach ( $def['meta_keys'] as $plain_key ) {
                $plain[ (string) $plain_key ] = true;
            }
        }

        foreach ( $field_names as $by_key ) {
            foreach ( $by_key as $key => $field_name ) {
                if ( isset( $served[ $field_name ] ) ) {
                    continue;
                }
                $keys[ $field_name ] = isset( $plain[ (string) $key ] ) ? (string) $field_name : (string) $key;
            }
        }

        return $keys;
    }

    /**
     * A type's prefixed definitions, carrying only the keys it does not serve.
     *
     * A Product attribute is served as an ordinary `attribute_<slug>` field and
     * is restored from the groups like any other, so listing its key here as
     * well would put a second row for the same column in Manage Fields. An
     * Order Item Meta key is not served, so this is the only place the screen
     * can learn that it is ticked.
     *
     * @param string                             $type          Export type slug.
     * @param array<string,string>               $meta_columns  From unserved_meta_column_keys().
     * @param array<string,array<string,string>> $by_definition From Custom_Meta_Fields_Controller::get_prefixed_field_names().
     *
     * @since 2.8.3
     * @return array<array{name:string,meta_keys:string[],availability:array<string,bool>}>
     */
    private function unserved_prefixed_definitions( string $type, array $meta_columns, array $by_definition ): array {
        $definitions = array();

        foreach ( Custom_Meta_Fields_Controller::get_prefixed_picker_definitions( $type, $by_definition ) as $definition ) {
            $field_names = $by_definition[ $definition['name'] ] ?? array();

            $definition['meta_keys'] = array_values(
                array_filter(
                    $definition['meta_keys'],
                    static function ( $key ) use ( $field_names, $meta_columns ) {
                        return isset( $meta_columns[ $field_names[ $key ] ?? '' ] );
                    }
                )
            );

            if ( ! empty( $definition['meta_keys'] ) ) {
                $definitions[] = $definition;
            }
        }

        return $definitions;
    }

    /**
     * Name each column of an order list the way the SPA holds its position.
     *
     * A derived field name in `$meta_columns` becomes the name it is held under;
     * every other name passes through. A name that collides with another keeps
     * only its first position, so one column cannot occupy two slots.
     *
     * @param array<string,string> $meta_columns From unserved_meta_column_keys().
     * @param string[]             $names        Field names in column order.
     *
     * @since 2.8.3
     * @return string[]
     */
    private function to_column_keys( array $meta_columns, array $names ): array {
        $columns = array();
        $seen    = array();

        foreach ( $names as $name ) {
            $column = $meta_columns[ $name ] ?? $name;
            if ( isset( $seen[ $column ] ) ) {
                continue;
            }
            $seen[ $column ] = true;
            $columns[]       = $column;
        }

        return $columns;
    }

    /**
     * GET /wse/v1/quick-export/filters?type=X
     *
     * Returns the filter checkbox definitions and sort field options for the
     * given export type.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 5.6.0
     * @since 2.8.3 Gates the custom-meta filter on the requesting surface's "Available In" flag.
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_filters( $request ) {
        $type = $request->get_param( 'type' );

        if ( ! $this->is_valid_type( $type ) ) {
            return new \WP_Error(
                'invalid_export_type',
                __( 'Invalid export type.', 'woocommerce-exporter' ),
                array( 'status' => 400 )
            );
        }

        if ( function_exists( 'woo_ce_load_export_types' ) ) {
            woo_ce_load_export_types();
        }

        $context           = (string) $request->get_param( 'context' );
        $availability_flag = self::CONTEXT_AVAILABILITY_FLAGS[ $context ] ?? '';
        $sort              = $this->get_type_sort_options( $type );
        return rest_ensure_response(
            array(
                'type'          => $type,
                'filters'       => $this->get_type_filters( $type, $availability_flag ),
                'sort'          => $sort,
                'sort_fields'   => $sort ? $sort['options'] : array(),
                'custom_meta'   => $this->get_type_custom_meta_fields( $type ),
                'extra_options' => $this->get_type_extra_options( $type ),
                'type_label'    => $this->get_export_type_definitions()[ $type ]['label'] ?? $type,
            )
        );
    }

    /**
     * Normalised list of registered custom-meta keys for a legacy option.
     *
     * The `custom_products` / `custom_orders` options are stored either as an
     * array of keys or as a newline/comma-separated string (the legacy textarea
     * format). Returns a trimmed, de-blanked, re-indexed array either way.
     *
     * @since 2.8.1
     * @param string $option_name Legacy option name (e.g. `custom_products`).
     * @return string[] Registered meta keys.
     */
    private function get_registered_custom_meta_keys( $option_name ) {
        $saved = function_exists( 'woo_ce_get_option' ) ? woo_ce_get_option( $option_name, '' ) : '';
        $keys  = is_array( $saved ) ? $saved : preg_split( '/[\r\n,]+/', (string) $saved );
        $keys  = array_filter( array_map( 'trim', (array) $keys ), 'strlen' );
        return array_values( $keys );
    }

    /**
     * Append the per-key custom-meta VALUE filter card for a Product or Order
     * export, when meta keys are registered. Products and Orders share the same
     * card shape, differing only by option name, key, label and group - hence
     * the single helper.
     *
     * The definition's "Available In" selection gates the card: unticking an
     * area withholds the card — and with it every per-key value input — from
     * that area alone. The flag is empty for a request that named no surface,
     * which leaves the card offered as before.
     *
     * @since 2.8.1
     * @since 2.8.3 Added the $availability_flag parameter.
     * @param array  $filters           Filter definitions, appended by reference.
     * @param string $type              Export type slug.
     * @param string $availability_flag "Available In" flag the requesting surface is gated on,
     *                                  or '' to leave the card ungated.
     * @return void
     */
    private function maybe_add_custom_meta_filter( &$filters, $type, $availability_flag = '' ) {
        $is_product = 'product' === $type;
        if ( ! $is_product && 'order' !== $type ) {
            return;
        }
        $option_name = $is_product ? 'custom_products' : 'custom_orders';
        if ( empty( $this->get_registered_custom_meta_keys( $option_name ) ) ) {
            return;
        }
        if ( '' !== $availability_flag
            && ! Custom_Meta_Fields_Controller::is_definition_available( $option_name, $availability_flag )
        ) {
            return;
        }
        $filters[] = array(
            'key'   => $is_product ? 'product_filter_custom_meta_enabled' : 'order_filter_custom_meta_enabled',
            'label' => $is_product
                ? __( 'Custom Product meta', 'woocommerce-exporter' )
                : __( 'Custom Order meta', 'woocommerce-exporter' ),
            'group' => $is_product ? 'Product Filters' : 'Order Filters',
        );
    }

    /**
     * Custom-meta textarea definitions per export type. Mirrors the legacy
     * woo_ce_{type}_custom_fields() screens so the SPA can render the same
     * inputs (Custom Product meta, Custom Product Attributes, etc.).
     *
     * @since 5.6.0
     * @param string $type Export type slug.
     * @return array<array{name:string,label:string,description:string,value:string}>
     */
    private function get_type_custom_meta_fields( $type ) {
        $map = array(
            'product'      => array(
                array(
                    'name'        => 'custom_products',
                    'label'       => __( 'Custom Product meta', 'woocommerce-exporter' ),
                    'description' => __( 'Custom Product meta keys — one per line.', 'woocommerce-exporter' ),
                ),
                array(
                    'name'        => 'custom_attributes',
                    'label'       => __( 'Custom Product Attributes', 'woocommerce-exporter' ),
                    'description' => __( 'Attribute slugs (lowercase, dashes for spaces) — one per line.', 'woocommerce-exporter' ),
                ),
            ),
            'category'     => array(
                array(
                    'name'        => 'custom_categories',
                    'label'       => __( 'Custom Category meta', 'woocommerce-exporter' ),
                    'description' => __( 'Custom Category meta keys — one per line.', 'woocommerce-exporter' ),
                ),
            ),
            'tag'          => array(
                array(
                    'name'        => 'custom_tags',
                    'label'       => __( 'Custom Tag meta', 'woocommerce-exporter' ),
                    'description' => __( 'Custom Tag meta keys — one per line.', 'woocommerce-exporter' ),
                ),
            ),
            'brand'        => array(
                array(
                    'name'        => 'custom_brands',
                    'label'       => __( 'Custom Brand meta', 'woocommerce-exporter' ),
                    'description' => __( 'Custom Brand meta keys — one per line.', 'woocommerce-exporter' ),
                ),
            ),
            'order'        => array(
                array(
                    'name'        => 'custom_orders',
                    'label'       => __( 'Custom Order meta', 'woocommerce-exporter' ),
                    'description' => __( 'Custom Order meta keys — one per line.', 'woocommerce-exporter' ),
                ),
            ),
            'subscription' => array(
                array(
                    'name'        => 'custom_subscriptions',
                    'label'       => __( 'Custom Subscription meta', 'woocommerce-exporter' ),
                    'description' => __( 'Custom Subscription meta keys — one per line.', 'woocommerce-exporter' ),
                ),
            ),
            'user'         => array(
                array(
                    'name'        => 'custom_users',
                    'label'       => __( 'Custom User meta', 'woocommerce-exporter' ),
                    'description' => __( 'Custom User meta keys — one per line.', 'woocommerce-exporter' ),
                ),
            ),
            'customer'     => array(
                array(
                    'name'        => 'custom_customers',
                    'label'       => __( 'Custom Customer meta', 'woocommerce-exporter' ),
                    'description' => __( 'Custom Customer meta keys — one per line.', 'woocommerce-exporter' ),
                ),
            ),
        );

        $definitions = isset( $map[ $type ] ) ? $map[ $type ] : array();

        // Pre-fill saved values from legacy plugin options.
        foreach ( $definitions as &$def ) {
            $saved = function_exists( 'woo_ce_get_option' ) ? woo_ce_get_option( $def['name'], '' ) : '';
            if ( is_array( $saved ) ) {
                $saved = implode( "\n", $saved );
            }
            $def['value'] = (string) $saved;
        }
        unset( $def );

        return $definitions;
    }

    /**
     * Extra export-time formatting options per type — Description/Excerpt
     * formatting, product image formatting, etc. Returns radio-group
     * definitions consumable by the SPA.
     *
     * @since 5.6.0
     * @param string $type Export type slug.
     * @return array<array{key:string,label:string,description:string,options:array,value:string}>
     */
    private function get_type_extra_options( $type ) {
        $options = array();

        if ( 'product' === $type ) {
            // Grouped Product formatting.
            $saved     = function_exists( 'woo_ce_get_option' ) ? woo_ce_get_option( 'grouped_formatting', 1 ) : 1;
            $options[] = array(
                'key'         => 'product_grouped_formatting',
                'label'       => __( 'Grouped Product formatting', 'woocommerce-exporter' ),
                'description' => __( 'Choose the Grouped Product formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ),
                'options'     => array(
                    array(
                        'value' => '0',
                        'label' => __( 'Export Grouped Products as Product ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => '1',
                        'label' => __( 'Export Grouped Products as Product SKU', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => '2',
                        'label' => __( 'Export Grouped Products as Product Name', 'woocommerce-exporter' ),
                    ),
                ),
                'value'       => (string) $saved,
            );

            // Up-sells formatting.
            $saved     = function_exists( 'woo_ce_get_option' ) ? woo_ce_get_option( 'upsell_formatting', 1 ) : 1;
            $options[] = array(
                'key'         => 'product_upsell_formatting',
                'label'       => __( 'Up-sells formatting', 'woocommerce-exporter' ),
                'description' => __( 'Choose the up-sell formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ),
                'options'     => array(
                    array(
                        'value' => '0',
                        'label' => __( 'Export Up-Sells as Product ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => '1',
                        'label' => __( 'Export Up-Sells as Product SKU', 'woocommerce-exporter' ),
                    ),
                ),
                'value'       => (string) $saved,
            );

            // Cross-sells formatting.
            $saved     = function_exists( 'woo_ce_get_option' ) ? woo_ce_get_option( 'crosssell_formatting', 1 ) : 1;
            $options[] = array(
                'key'         => 'product_crosssell_formatting',
                'label'       => __( 'Cross-sells formatting', 'woocommerce-exporter' ),
                'description' => __( 'Choose the cross-sell formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ),
                'options'     => array(
                    array(
                        'value' => '0',
                        'label' => __( 'Export Cross-Sells as Product ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => '1',
                        'label' => __( 'Export Cross-Sells as Product SKU', 'woocommerce-exporter' ),
                    ),
                ),
                'value'       => (string) $saved,
            );

            // Variation formatting.
            $saved     = function_exists( 'woo_ce_get_option' ) ? woo_ce_get_option( 'variation_formatting', 0 ) : 0;
            $options[] = array(
                'key'         => 'variation_formatting',
                'label'       => __( 'Variation formatting', 'woocommerce-exporter' ),
                'description' => __( 'Choose the default formatting rule that is applied to Product Variations.', 'woocommerce-exporter' ),
                'options'     => array(
                    array(
                        'value' => '0',
                        'label' => __( 'Leave empty Variant details intact', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => '1',
                        'label' => __( 'Default Variant details to Parent Product', 'woocommerce-exporter' ),
                    ),
                ),
                'value'       => (string) $saved,
            );
        }

        // Description/Excerpt formatting applies to: product, category, tag, brand, order.
        if ( in_array( $type, array( 'product', 'category', 'tag', 'brand', 'order' ), true ) ) {
            $saved     = function_exists( 'woo_ce_get_option' ) ? woo_ce_get_option( 'description_excerpt_formatting', 0 ) : 0;
            $options[] = array(
                'key'         => 'description_excerpt_formatting',
                'label'       => __( 'Description/Excerpt formatting', 'woocommerce-exporter' ),
                'description' => __( 'Choose the HTML tag formatting rule that is applied to the Description/Excerpt within the Product, Category, Tag, Brand and Order export.', 'woocommerce-exporter' ),
                'options'     => array(
                    array(
                        'value' => '0',
                        'label' => __( 'Leave HTML tags from Description/Excerpt intact', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => '1',
                        'label' => __( 'Strip HTML tags from Description/Excerpt', 'woocommerce-exporter' ),
                    ),
                ),
                'value'       => (string) $saved,
            );
        }

        if ( 'product' === $type ) {
            // Product image formatting.
            $saved     = function_exists( 'woo_ce_get_option' ) ? woo_ce_get_option( 'product_image_formatting', 1 ) : 1;
            $options[] = array(
                'key'         => 'product_image_formatting',
                'label'       => __( 'Product image formatting', 'woocommerce-exporter' ),
                'description' => __( 'Choose the featured image formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ),
                'options'     => array(
                    array(
                        'value' => '0',
                        'label' => __( 'Export Product Image as Attachment ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => '1',
                        'label' => __( 'Export Product Image as Image URL', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => '2',
                        'label' => __( 'Export Product Image as Image filepath', 'woocommerce-exporter' ),
                    ),
                ),
                'value'       => (string) $saved,
            );

            // Product gallery formatting — media type.
            $saved     = function_exists( 'woo_ce_get_option' ) ? woo_ce_get_option( 'gallery_formatting', 1 ) : 1;
            $options[] = array(
                'key'         => 'product_gallery_formatting',
                'label'       => __( 'Product gallery formatting', 'woocommerce-exporter' ),
                'description' => __( 'Choose the product gallery formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ),
                'options'     => array(
                    array(
                        'value' => '0',
                        'label' => __( 'Export Product Gallery as Attachment ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => '1',
                        'label' => __( 'Export Product Gallery as Image URL', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => '2',
                        'label' => __( 'Export Product Gallery as Image filepath', 'woocommerce-exporter' ),
                    ),
                ),
                'value'       => (string) $saved,
            );

            // Product gallery formatting — cell layout.
            $saved     = function_exists( 'woo_ce_get_option' ) ? woo_ce_get_option( 'gallery_unique', 0 ) : 0;
            $options[] = array(
                'key'         => 'product_gallery_unique',
                'label'       => __( 'Product gallery cell layout', 'woocommerce-exporter' ),
                'description' => __( 'Choose whether gallery images are combined into a single cell or exported as individual image cells.', 'woocommerce-exporter' ),
                'options'     => array(
                    array(
                        'value' => '0',
                        'label' => __( 'Export Product Gallery as a single combined image cell', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => '1',
                        'label' => __( 'Export Product Gallery as individual image cells', 'woocommerce-exporter' ),
                    ),
                ),
                'value'       => (string) $saved,
            );
        }

        return $options;
    }

    // -------------------------------------------------------------------------
    // Private helpers – config
    // -------------------------------------------------------------------------

    /**
     * Build the full export-type list enriched with record counts.
     *
     * @since 5.6.0
     * @return array
     */
    private function get_export_types_with_counts() {
        $definitions = $this->get_export_type_definitions();
        $counts      = Export_Helper::get_export_type_count();
        $types       = array();

        foreach ( $definitions as $slug => $def ) {
            $is_premium = woo_ce_is_premium_export_type( $slug );
            $types[]    = array(
                'slug'       => $slug,
                'label'      => $def['label'],
                'category'   => $def['category'],
                'records'    => isset( $counts[ $slug ] ) ? (int) $counts[ $slug ] : 0,
                'premium'    => $is_premium,
                'upsell_url' => $is_premium ? woo_ce_get_export_type_upsell_url( $slug ) : null,
            );
        }

        return $types;
    }

    /**
     * Return the list of supported export formats.
     *
     * @since 5.6.0
     * @return array  Each entry: { value, label, premium, upsell_url }
     */
    private function get_export_formats() {
        $formats = array(
            array(
                'value' => 'csv',
                'label' => 'CSV',
            ),
            array(
                'value' => 'tsv',
                'label' => 'TSV',
            ),
            array(
                'value' => 'xls',
                'label' => 'XLS',
            ),
            array(
                'value' => 'xlsx',
                'label' => 'XLSX',
            ),
            array(
                'value' => 'xml',
                'label' => 'XML',
            ),
        );

        foreach ( $formats as &$format ) {
            $is_premium           = woo_ce_is_premium_export_format( $format['value'] );
            $format['premium']    = $is_premium;
            $format['upsell_url'] = $is_premium ? woo_ce_get_export_format_upsell_url( $format['value'] ) : null;
        }
        unset( $format );

        return $formats;
    }

    // -------------------------------------------------------------------------
    // Private helpers – fields
    // -------------------------------------------------------------------------

    /**
     * Call the appropriate woo_ce_get_{type}_fields() function.
     *
     * Returns null when the function does not exist (e.g. integration not active).
     *
     * @param string $type Export type slug.
     *
     * @since 5.6.0
     * @return array|null
     */
    private function load_fields_for_type( $type ) {
        $function_map = array(
            'product'        => 'woo_ce_get_product_fields',
            'category'       => 'woo_ce_get_category_fields',
            'order'          => 'woo_ce_get_order_fields',
            'customer'       => 'woo_ce_get_customer_fields',
            'coupon'         => 'woo_ce_get_coupon_fields',
            'subscription'   => 'woo_ce_get_subscription_fields',
            'tag'            => 'woo_ce_get_tag_fields',
            'brand'          => 'woo_ce_get_brand_fields',
            'user'           => 'woo_ce_get_user_fields',
            'review'         => 'woo_ce_get_review_fields',
            'product_vendor' => 'woo_ce_get_product_vendor_fields',
            'commission'     => 'woo_ce_get_commission_fields',
            'shipping_class' => 'woo_ce_get_shipping_class_fields',
            'ticket'         => 'woo_ce_get_ticket_fields',
            'booking'        => 'woo_ce_get_booking_fields',
            'attribute'      => 'woo_ce_get_attribute_fields',
        );

        if ( ! isset( $function_map[ $type ] ) ) {
            return null;
        }

        $fn = $function_map[ $type ];

        // REST requests don't go through the admin page bootstrap, so the
        // legacy export-type field helpers may not be loaded yet. Trigger the
        // loader if available before invoking the function.
        if ( ! function_exists( $fn ) && function_exists( 'woo_ce_load_export_types' ) ) {
            woo_ce_load_export_types();
        }

        if ( ! function_exists( $fn ) ) {
            return null;
        }

        return (array) $fn( 'full' );
    }

    /**
     * Retrieve the saved field key list for a type from the plugin option.
     *
     * Returns an empty array when no saved selection exists, meaning "all fields
     * are enabled".
     *
     * @param string $type Export type slug.
     *
     * @since 5.6.0
     * @return string[]
     */
    private function get_saved_field_keys( $type ) {
        $option = woo_ce_get_option( $type . '_fields', array() );
        if ( ! is_array( $option ) ) {
            return array();
        }
        return array_keys( array_filter( $option ) );
    }

    /**
     * The flat column order the store owner last exported this type with.
     *
     * Every export writes the column positions it ran with to the
     * woo_ce_{type}_sorting option ({@see \VisserLabs\WSE\Classes\Exporter::save_export_fields()}).
     * That order cannot be read back off the served groups: group_fields()
     * buckets the fields into Field_Registry categories, so an order move
     * within a group survives but a move between groups does not. The SPA
     * seeds its column order from this list instead.
     *
     * The saved columns come first, in the order they were exported in,
     * followed by every other served field in the order the export type lists
     * them — so a field the owner ticks next keeps a sensible position.
     *
     * A saved column need not be one of `$fields`. An Order Item Meta key is
     * exported under a derived field name the type's field list does not carry
     * — the picker lists it in its own Manage Fields section instead — so those
     * names are placed from the picker's own key list.
     *
     * Returns an empty array when the owner has never run an export of this
     * type, or when the saved order names nothing the screen can still place:
     * the SPA then keeps the grouped order rather than reshuffling the columns.
     *
     * @param string               $type         Export type slug.
     * @param array                $fields       Flat field list from load_fields_for_type().
     * @param array                $definitions  Picker definitions for the type.
     * @param array<string,string> $meta_columns From unserved_meta_column_keys().
     *
     * @since 2.8.3
     * @return string[]
     */
    private function get_saved_field_order( string $type, array $fields, array $definitions, array $meta_columns ): array {
        $sorting = woo_ce_get_option( $type . '_sorting', array() );
        if ( ! is_array( $sorting ) || empty( $sorting ) ) {
            return array();
        }

        // The field names this type serves, de-duplicated, in served order.
        $served = array();
        $seen   = array();
        foreach ( $fields as $field ) {
            $name = isset( $field['name'] ) ? (string) $field['name'] : '';
            if ( '' === $name || isset( $seen[ $name ] ) ) {
                continue;
            }
            $seen[ $name ] = true;
            $served[]      = $name;
        }

        $placeable = $this->placeable_column_names( $served, $definitions, $meta_columns );

        // A name the screen can no longer place is dropped: keeping it would
        // only push the columns that follow it one slot down the saved order.
        $saved = array();
        $index = 0;
        foreach ( $sorting as $name => $position ) {
            $name = (string) $name;
            if ( isset( $placeable[ $name ] ) ) {
                $saved[] = array(
                    'name'     => $name,
                    'position' => (int) $position,
                    'index'    => $index,
                );
            }
            ++$index;
        }

        if ( empty( $saved ) ) {
            return array();
        }

        // The saved order's own order breaks a tie, so two columns stored at
        // the same position cannot swap between requests (usort is only stable
        // on PHP 8.0+).
        usort(
            $saved,
            function ( $a, $b ) {
                if ( $a['position'] === $b['position'] ) {
                    return $a['index'] <=> $b['index'];
                }
                return $a['position'] <=> $b['position'];
            }
        );

        $order   = array_column( $saved, 'name' );
        $ordered = array_flip( $order );
        foreach ( $served as $name ) {
            if ( ! isset( $ordered[ $name ] ) ) {
                $order[] = $name;
            }
        }

        return $order;
    }

    /**
     * The column names the Manage Fields screen can still give a position to.
     *
     * Every served field, plus the registered custom meta keys the picker
     * lists in their own section and the derived field names an export posts
     * those keys under. Anything else in the saved order names a column the
     * screen no longer has.
     *
     * @param string[]             $served       The field names the type serves.
     * @param array                $definitions  Picker definitions for the type.
     * @param array<string,string> $meta_columns From unserved_meta_column_keys().
     *
     * @since 2.8.3
     * @return array<string,true> A lookup set, keyed by column name.
     */
    private function placeable_column_names( array $served, array $definitions, array $meta_columns ): array {
        $placeable = array_fill_keys( $served, true );

        foreach ( $definitions as $def ) {
            foreach ( $def['meta_keys'] as $key ) {
                $placeable[ (string) $key ] = true;
            }
        }

        foreach ( array_keys( $meta_columns ) as $field_name ) {
            $placeable[ (string) $field_name ] = true;
        }

        return $placeable;
    }

    /**
     * Group a flat field list into logical sections using Field_Registry categories.
     *
     * Each returned group is: { label: string, fields: [{ name, label, selected, order }] }
     *
     * @param string   $type       Export type slug.
     * @param array    $fields     Flat array of [ 'name' => '', 'label' => '' ] items.
     * @param string[] $saved_keys Keys the user previously saved as enabled.
     *
     * @since 5.6.0
     * @since 2.8.3 Selects every field when the type has no matching curated set,
     *              instead of the first 10 by position.
     * @return array
     */
    private function group_fields( $type, array $fields, array $saved_keys ) {
        $groups         = array();
        $order          = 1;
        $has_saved      = ! empty( $saved_keys );
        $default_lookup = array_flip( $this->resolve_default_field_keys( $type, $fields ) );

        foreach ( $fields as $field ) {
            $name        = isset( $field['name'] ) ? $field['name'] : '';
            $label       = isset( $field['label'] ) ? $field['label'] : $name;
            $group_label = Field_Registry::get_category( $type, $name );

            if ( ! isset( $groups[ $group_label ] ) ) {
                $groups[ $group_label ] = array(
                    'label'  => $group_label,
                    'fields' => array(),
                );
            }

            // Selection rule: a saved selection always wins; otherwise the field
            // is selected when resolve_default_field_keys() lists it, which is
            // the curated set for the type, or every field when the type has no
            // curated set that matches one of its fields.
            $selected = $has_saved
                ? in_array( $name, $saved_keys, true )
                : isset( $default_lookup[ $name ] );

            $groups[ $group_label ]['fields'][] = array(
                'name'     => $name,
                'label'    => $label,
                'selected' => $selected,
                'order'    => $order++,
            );
        }

        return array_values( $groups );
    }

    /**
     * Add each group's heading, translated into the user's language.
     *
     * This request runs in the site locale, because field labels are saved as
     * export headers. The group headings only display, so they follow the
     * user's language like the rest of the screen. `label` stays the stable
     * English category key.
     *
     * @param array $groups Groups from group_fields().
     *
     * @since 2.8.3
     * @return array Groups with a `display_label` added.
     */
    private function add_group_display_labels( array $groups ) {
        return Helper::in_user_locale(
            function () use ( $groups ) {
                foreach ( $groups as &$group ) {
                    $group['display_label'] = Field_Registry::get_category_label( $group['label'] );
                }
                unset( $group );
                return $groups;
            }
        );
    }

    // -------------------------------------------------------------------------
    // Private helpers – filters
    // -------------------------------------------------------------------------

    /**
     * Return the filter option definitions for a given export type.
     *
     * Each filter is: { key: string, label: string, enabled: bool }
     * The `key` maps directly to the form_data field name accepted by
     * the AJAX export handler.
     *
     * @param string $type              Export type slug.
     * @param string $availability_flag "Available In" flag the requesting surface is gated on,
     *                                  or '' to leave the custom-meta filter ungated.
     *
     * @since 5.6.0
     * @since 2.8.3 Added the $availability_flag parameter.
     * @return array
     */
    private function get_type_filters( $type, $availability_flag = '' ) {
        $definitions = array(
            'product'        => array(
                // Product Filters.
                array(
                    'key'   => 'product_filter_category_enabled',
                    'label' => __( 'Categories', 'woocommerce-exporter' ),
                    'group' => 'Product Filters',
                ),
                array(
                    'key'   => 'product_filter_tag_enabled',
                    'label' => __( 'Tags', 'woocommerce-exporter' ),
                    'group' => 'Product Filters',
                ),
                array(
                    'key'   => 'product_filter_product_type_enabled',
                    'label' => __( 'Product Types', 'woocommerce-exporter' ),
                    'group' => 'Product Filters',
                ),
                array(
                    'key'   => 'product_filter_product_status_enabled',
                    'label' => __( 'Product Status', 'woocommerce-exporter' ),
                    'group' => 'Product Filters',
                ),
                array(
                    'key'   => 'product_filter_brand_enabled',
                    'label' => __( 'Brands', 'woocommerce-exporter' ),
                    'group' => 'Product Filters',
                ),
                array(
                    'key'   => 'product_filter_shipping_class_enabled',
                    'label' => __( 'Shipping Class', 'woocommerce-exporter' ),
                    'group' => 'Product Filters',
                ),
                array(
                    'key'   => 'product_filter_sku_include',
                    'label' => __( 'SKU', 'woocommerce-exporter' ),
                    'group' => 'Product Filters',
                ),
                // Inventory Filters.
                array(
                    'key'   => 'product_filter_stock_status_enabled',
                    'label' => __( 'Stock Status', 'woocommerce-exporter' ),
                    'group' => 'Inventory Filters',
                ),
                array(
                    'key'   => 'product_filter_stock_quantity_enabled',
                    'label' => __( 'Stock Quantity', 'woocommerce-exporter' ),
                    'group' => 'Inventory Filters',
                ),
                array(
                    'key'   => 'product_filter_featured_enabled',
                    'label' => __( 'Featured', 'woocommerce-exporter' ),
                    'group' => 'Inventory Filters',
                ),
                // Date Filters.
                array(
                    'key'   => 'product_dates_filter_enabled',
                    'label' => __( 'Published Date', 'woocommerce-exporter' ),
                    'group' => 'Date Filters',
                ),
                array(
                    'key'   => 'product_modified_dates_filter_enabled',
                    'label' => __( 'Modified Date', 'woocommerce-exporter' ),
                    'group' => 'Date Filters',
                ),
                // User Filters.
                array(
                    'key'   => 'product_filter_user_role_enabled',
                    'label' => __( 'User Role', 'woocommerce-exporter' ),
                    'group' => 'User Filters',
                ),
            ),
            'order'          => array(
                // Order Filters.
                array(
                    'key'   => 'orders_filters[status]',
                    'label' => __( 'Order Status', 'woocommerce-exporter' ),
                    'group' => 'Order Filters',
                ),
                array(
                    'key'   => 'orders_filters[payment_method]',
                    'label' => __( 'Payment Gateway', 'woocommerce-exporter' ),
                    'group' => 'Order Filters',
                ),
                array(
                    'key'   => 'order_filter_billing_country_enabled',
                    'label' => __( 'Billing Country', 'woocommerce-exporter' ),
                    'group' => 'Order Filters',
                ),
                array(
                    'key'   => 'order_filter_shipping_country_enabled',
                    'label' => __( 'Shipping Country', 'woocommerce-exporter' ),
                    'group' => 'Order Filters',
                ),
                array(
                    'key'   => 'order_filter_shipping_enabled',
                    'label' => __( 'Shipping Method', 'woocommerce-exporter' ),
                    'group' => 'Order Filters',
                ),
                array(
                    'key'   => 'order_filter_user_role_enabled',
                    'label' => __( 'User Role', 'woocommerce-exporter' ),
                    'group' => 'Order Filters',
                ),
                array(
                    'key'   => 'order_filter_customer_enabled',
                    'label' => __( 'Customer', 'woocommerce-exporter' ),
                    'group' => 'Order Filters',
                ),
                array(
                    'key'   => 'order_filter_coupon_enabled',
                    'label' => __( 'Coupon Code', 'woocommerce-exporter' ),
                    'group' => 'Order Filters',
                ),
                // Product Filters (applied to order line items).
                array(
                    'key'   => 'order_filter_product_enabled',
                    'label' => __( 'Product', 'woocommerce-exporter' ),
                    'group' => 'Product Filters',
                ),
                array(
                    'key'   => 'order_filter_category_enabled',
                    'label' => __( 'Product Category', 'woocommerce-exporter' ),
                    'group' => 'Product Filters',
                ),
                array(
                    'key'   => 'order_filter_tag_enabled',
                    'label' => __( 'Product Tag', 'woocommerce-exporter' ),
                    'group' => 'Product Filters',
                ),
                array(
                    'key'   => 'order_filter_brand_enabled',
                    'label' => __( 'Product Brand', 'woocommerce-exporter' ),
                    'group' => 'Product Filters',
                ),
                // Date Filters.
                array(
                    'key'   => 'orders_filters[date]',
                    'label' => __( 'Order Date', 'woocommerce-exporter' ),
                    'group' => 'Date Filters',
                ),
                array(
                    'key'   => 'orders_filters[modified_date]',
                    'label' => __( 'Date Modified', 'woocommerce-exporter' ),
                    'group' => 'Date Filters',
                ),
                // Formatting.
                array(
                    'key'   => 'order_filter_items_enabled',
                    'label' => __( 'Order Items Formatting', 'woocommerce-exporter' ),
                    'group' => 'Formatting',
                ),
                array(
                    'key'   => 'order_filter_max_order_items_enabled',
                    'label' => __( 'Max Unique Order Items', 'woocommerce-exporter' ),
                    'group' => 'Formatting',
                ),
                array(
                    'key'   => 'order_filter_digital_enabled',
                    'label' => __( 'Digital Products', 'woocommerce-exporter' ),
                    'group' => 'Formatting',
                ),
                array(
                    'key'   => 'order_filter_notes_enabled',
                    'label' => __( 'Exported Order Notes', 'woocommerce-exporter' ),
                    'group' => 'Formatting',
                ),
                array(
                    'key'   => 'order_filter_item_types_enabled',
                    'label' => __( 'Order Item Types', 'woocommerce-exporter' ),
                    'group' => 'Formatting',
                ),
            ),
            'customer'       => array(),
            'user'           => array(
                array(
                    'key'   => 'user_filter_user_role_enabled',
                    'label' => __( 'User Role', 'woocommerce-exporter' ),
                    'group' => 'User Filters',
                ),
                array(
                    'key'   => 'user_dates_filter_enabled',
                    'label' => __( 'Date Registered', 'woocommerce-exporter' ),
                    'group' => 'Date Filters',
                ),
                array(
                    'key'   => 'user_modified_dates_filter_enabled',
                    'label' => __( 'Date Modified', 'woocommerce-exporter' ),
                    'group' => 'Date Filters',
                ),
            ),
            'coupon'         => array(
                array(
                    'key'   => 'coupon_filter_coupon_type_enabled',
                    'label' => __( 'Discount Type', 'woocommerce-exporter' ),
                    'group' => 'Coupon Filters',
                ),
                array(
                    'key'   => 'coupon_dates_filter_enabled',
                    'label' => __( 'Date Created', 'woocommerce-exporter' ),
                    'group' => 'Date Filters',
                ),
            ),
            'subscription'   => array(
                array(
                    'key'   => 'subscription_filter_status_enabled',
                    'label' => __( 'Subscription Status', 'woocommerce-exporter' ),
                    'group' => 'Subscription Filters',
                ),
                array(
                    'key'   => 'subscription_filter_product_enabled',
                    'label' => __( 'Subscription Product', 'woocommerce-exporter' ),
                    'group' => 'Subscription Filters',
                ),
                array(
                    'key'   => 'subscription_dates_filter_enabled',
                    'label' => __( 'Subscription Date', 'woocommerce-exporter' ),
                    'group' => 'Date Filters',
                ),
                array(
                    'key'   => 'subscription_filter_items_enabled',
                    'label' => __( 'Items Formatting', 'woocommerce-exporter' ),
                    'group' => 'Formatting',
                ),
            ),
            'review'         => array(
                array(
                    'key'   => 'review_filter_status_enabled',
                    'label' => __( 'Review Status', 'woocommerce-exporter' ),
                    'group' => 'Review Filters',
                ),
                array(
                    'key'   => 'review_dates_filter_enabled',
                    'label' => __( 'Review Date', 'woocommerce-exporter' ),
                    'group' => 'Date Filters',
                ),
            ),
            'booking'        => array(
                array(
                    'key'   => 'booking_filter_status_enabled',
                    'label' => __( 'Booking Status', 'woocommerce-exporter' ),
                    'group' => 'Booking Filters',
                ),
                array(
                    'key'   => 'booking_dates_filter_enabled',
                    'label' => __( 'Date Created', 'woocommerce-exporter' ),
                    'group' => 'Date Filters',
                ),
            ),
            'category'       => array(),
            'tag'            => array(),
            'brand'          => array(),
            'commission'     => array(),
            'shipping_class' => array(),
        );

        $filters = isset( $definitions[ $type ] ) ? $definitions[ $type ] : array();

        // Custom-meta VALUE filters. The classic Quick Export screen exposed a
        // per-key value input plumbed into product_custom_meta / order_custom_meta;
        // re-add it here so the SPA can scope by custom meta. Only offered when
        // meta keys are registered and the definition is available in the
        // requesting surface.
        $this->maybe_add_custom_meta_filter( $filters, $type, $availability_flag );

        // Plugin-gated filters. Each is only offered when its dependency is
        // active AND its option list is non-empty, so no empty card renders.
        $this->maybe_add_plugin_gated_filters( $filters, $type );

        // Term Language filter for the Category and Tag exports.
        $this->maybe_add_term_language_filter( $filters, $type );

        foreach ( $filters as &$filter ) {
            $filter['enabled']     = false;
            $filter['description'] = $this->get_filter_description( $filter['key'] );
            $filter['icon']        = $this->get_filter_icon( $filter['key'] );
            $filter['sub_options'] = $this->get_filter_sub_options( $filter['key'] );
            if ( ! isset( $filter['group'] ) ) {
                $filter['group'] = '';
            }
            $filter['group_label'] = $this->get_group_label( (string) $filter['group'] );
        }
        unset( $filter );

        return $filters;
    }

    /**
     * Translated display label for a filter group.
     *
     * The English group name stays the stable key (the SPA groups and looks up
     * descriptions by it); this is only the label to render.
     *
     * @param string $group Filter group key.
     *
     * @since 2.8.3
     * @return string Translated label; unknown groups are returned unchanged.
     */
    private function get_group_label( string $group ): string {
        switch ( $group ) {
            case 'Booking Filters':
                return __( 'Booking Filters', 'woocommerce-exporter' );
            case 'Category Filters':
                return __( 'Category Filters', 'woocommerce-exporter' );
            case 'Coupon Filters':
                return __( 'Coupon Filters', 'woocommerce-exporter' );
            case 'Date Filters':
                return __( 'Date Filters', 'woocommerce-exporter' );
            case 'Formatting':
                return __( 'Formatting', 'woocommerce-exporter' );
            case 'Inventory Filters':
                return __( 'Inventory Filters', 'woocommerce-exporter' );
            case 'Order Filters':
                return __( 'Order Filters', 'woocommerce-exporter' );
            case 'Product Filters':
                return __( 'Product Filters', 'woocommerce-exporter' );
            case 'Review Filters':
                return __( 'Review Filters', 'woocommerce-exporter' );
            case 'Subscription Filters':
                return __( 'Subscription Filters', 'woocommerce-exporter' );
            case 'Tag Filters':
                return __( 'Tag Filters', 'woocommerce-exporter' );
            case 'User Filters':
                return __( 'User Filters', 'woocommerce-exporter' );
            default:
                return $group;
        }
    }

    /**
     * Append the Language filter card for a Category or Tag export when a
     * multilingual plugin (Polylang or WPML) offers more than one language
     * and translates the export's taxonomy - otherwise the selection would
     * be ignored (Polylang) or match no terms (WPML).
     *
     * @since 2.8.3
     * @param array  $filters Filter definitions, appended by reference.
     * @param string $type    Export type slug.
     * @return void
     */
    private function maybe_add_term_language_filter( &$filters, $type ) {
        $groups     = array(
            'category' => 'Category Filters',
            'tag'      => 'Tag Filters',
        );
        $taxonomies = array(
            'category' => 'product_cat',
            'tag'      => 'product_tag',
        );
        if ( ! isset( $groups[ $type ] ) || ! function_exists( 'woo_ce_get_multilingual_languages' ) ) {
            return;
        }

        // Polylang first: its WPML compatibility layer would otherwise answer the WPML filter.
        $is_translated = function_exists( 'pll_is_translated_taxonomy' )
            ? pll_is_translated_taxonomy( $taxonomies[ $type ] )
            : (bool) apply_filters( 'wpml_is_translated_taxonomy', false, $taxonomies[ $type ] );

        if ( $is_translated && count( woo_ce_get_multilingual_languages() ) > 1 ) {
            $filters[] = array(
                'key'   => "{$type}_filter_language_include",
                'label' => __( 'Language', 'woocommerce-exporter' ),
                'group' => $groups[ $type ],
            );
        }
    }

    /**
     * Append the filter cards whose backing feature requires a third-party plugin.
     *
     * Each is only appended when BOTH its dependency is detected AND its
     * resulting option list is non-empty, so no empty card is ever rendered.
     *
     * @since 2.8.2
     * @param array  $filters Filter definitions, appended by reference.
     * @param string $type    Export type slug.
     * @return void
     */
    private function maybe_add_plugin_gated_filters( &$filters, $type ) {
        if ( 'product' !== $type || ! function_exists( 'woo_ce_detect_export_plugin' ) ) {
            return;
        }

        // WPML / WooCommerce Multilingual.
        if ( function_exists( 'woo_ce_detect_wpml' ) && woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
            $languages = function_exists( 'icl_get_languages' ) ? (array) icl_get_languages( 'skip_missing=N' ) : array();
            if ( ! empty( $languages ) ) {
                $filters[] = array(
                    'key'   => 'product_filter_language_include',
                    'label' => __( 'Language', 'woocommerce-exporter' ),
                    'group' => 'Product Filters',
                );
            }
        }

        // Product Vendors / YITH WooCommerce Multi Vendor.
        if ( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
            $vendors = function_exists( 'woo_ce_get_product_vendors' ) ? (array) woo_ce_get_product_vendors( array( 'hide_empty' => 1 ), 'full' ) : array();
            if ( ! empty( $vendors ) ) {
                $filters[] = array(
                    'key'   => 'product_filter_vendor_include',
                    'label' => __( 'Product Vendor', 'woocommerce-exporter' ),
                    'group' => 'Product Filters',
                );
            }
        }
    }

    /**
     * Short helper text shown under each filter card title.
     *
     * @since 5.6.0
     * @param string $filter_key Filter key.
     * @return string
     */
    private function get_filter_description( $filter_key ) {
        $map         = array(
            // Product filters.
            'product_filter_product_type_enabled'   => __( 'Include products based on their type.', 'woocommerce-exporter' ),
            'product_filter_product_status_enabled' => __( 'Include products based on their status.', 'woocommerce-exporter' ),
            'product_filter_category_enabled'       => __( 'Include products from specific categories.', 'woocommerce-exporter' ),
            'product_filter_tag_enabled'            => __( 'Include products with specific tags.', 'woocommerce-exporter' ),
            'product_dates_filter_enabled'          => __( 'Include products created within a date range.', 'woocommerce-exporter' ),
            'product_modified_dates_filter_enabled' => __( 'Include products modified within a date range.', 'woocommerce-exporter' ),
            'product_filter_custom_meta_enabled'    => __( 'Include products matching a custom meta value.', 'woocommerce-exporter' ),
            'product_filter_brand_enabled'          => __( 'Include products from specific brands.', 'woocommerce-exporter' ),
            'product_filter_user_role_enabled'      => __( 'Include products created by users with specific roles.', 'woocommerce-exporter' ),
            'product_filter_stock_status_enabled'   => __( 'Filter products by their inventory stock status.', 'woocommerce-exporter' ),
            'product_filter_stock_quantity_enabled' => __( 'Filter products by stock quantity threshold.', 'woocommerce-exporter' ),
            'product_filter_featured_enabled'       => __( 'Include featured or non-featured products only.', 'woocommerce-exporter' ),
            'product_filter_shipping_class_enabled' => __( 'Include products from specific shipping classes.', 'woocommerce-exporter' ),
            'product_filter_sku_include'            => __( 'Include or exclude specific products, by SKU.', 'woocommerce-exporter' ),
            'product_filter_language_include'       => __( 'Include products in a specific WPML language.', 'woocommerce-exporter' ),
            'category_filter_language_include'      => __( 'Include categories in specific languages.', 'woocommerce-exporter' ),
            'tag_filter_language_include'           => __( 'Include tags in specific languages.', 'woocommerce-exporter' ),
            'product_filter_vendor_include'         => __( 'Include products from specific vendors.', 'woocommerce-exporter' ),
            // Order filters.
            'orders_filters[status]'                => __( 'Include orders by status.', 'woocommerce-exporter' ),
            'orders_filters[payment_method]'        => __( 'Include orders by payment gateway.', 'woocommerce-exporter' ),
            'orders_filters[date]'                  => __( 'Include orders within a date range.', 'woocommerce-exporter' ),
            'orders_filters[modified_date]'         => __( 'Include orders modified within a date range.', 'woocommerce-exporter' ),
            'order_filter_billing_country_enabled'  => __( 'Include orders by billing country.', 'woocommerce-exporter' ),
            'order_filter_shipping_country_enabled' => __( 'Include orders by shipping country.', 'woocommerce-exporter' ),
            'order_filter_shipping_enabled'         => __( 'Include orders by shipping method.', 'woocommerce-exporter' ),
            'order_filter_user_role_enabled'        => __( 'Include orders placed by users with specific roles.', 'woocommerce-exporter' ),
            'order_filter_customer_enabled'         => __( 'Include orders from a specific customer email.', 'woocommerce-exporter' ),
            'order_filter_coupon_enabled'           => __( 'Include orders that used a specific coupon code.', 'woocommerce-exporter' ),
            'order_filter_product_enabled'          => __( 'Include orders containing a specific product (by ID).', 'woocommerce-exporter' ),
            'order_filter_category_enabled'         => __( 'Include orders containing products from specific categories.', 'woocommerce-exporter' ),
            'order_filter_tag_enabled'              => __( 'Include orders containing products with specific tags.', 'woocommerce-exporter' ),
            'order_filter_brand_enabled'            => __( 'Include orders containing products from specific brands.', 'woocommerce-exporter' ),
            'order_filter_items_enabled'            => __( 'Choose how order line items are formatted in the export.', 'woocommerce-exporter' ),
            'order_filter_max_order_items_enabled'  => __( 'Limit the number of unique order items included per row.', 'woocommerce-exporter' ),
            'order_filter_digital_enabled'          => __( 'Include or exclude orders based on digital product content.', 'woocommerce-exporter' ),
            'order_filter_notes_enabled'            => __( 'Control whether order notes are included in the export.', 'woocommerce-exporter' ),
            'order_filter_item_types_enabled'       => __( 'Choose which order item types to include in the export.', 'woocommerce-exporter' ),
            'order_filter_custom_meta_enabled'      => __( 'Include orders matching a custom meta value.', 'woocommerce-exporter' ),
            // User filters.
            'user_filter_user_role_enabled'         => __( 'Include users by role.', 'woocommerce-exporter' ),
            'user_dates_filter_enabled'             => __( 'Include users registered within a date range.', 'woocommerce-exporter' ),
            'user_modified_dates_filter_enabled'    => __( 'Include users modified within a date range.', 'woocommerce-exporter' ),
            // Coupon filters.
            'coupon_filter_coupon_type_enabled'     => __( 'Include coupons by discount type.', 'woocommerce-exporter' ),
            'coupon_dates_filter_enabled'           => __( 'Include coupons created within a date range.', 'woocommerce-exporter' ),
            // Subscription filters.
            'subscription_filter_status_enabled'    => __( 'Include subscriptions by status.', 'woocommerce-exporter' ),
            'subscription_filter_product_enabled'   => __( 'Include subscriptions for a specific product (by ID or SKU).', 'woocommerce-exporter' ),
            'subscription_dates_filter_enabled'     => __( 'Include subscriptions created within a date range.', 'woocommerce-exporter' ),
            'subscription_filter_items_enabled'     => __( 'Choose how subscription line items are formatted in the export.', 'woocommerce-exporter' ),
            // Review filters.
            'review_filter_status_enabled'          => __( 'Include reviews by approval status.', 'woocommerce-exporter' ),
            'review_dates_filter_enabled'           => __( 'Include reviews created within a date range.', 'woocommerce-exporter' ),
        );
        $description = isset( $map[ $filter_key ] ) ? $map[ $filter_key ] : '';

        // The product picker is capped at self::CHOICE_LIMIT and the SPA
        // multiselect has no remote search, so products beyond the cap are
        // unselectable. Say so on the card, so the user can tell a full list
        // from a truncated one.
        if ( 'product_filter_sku_include' === $filter_key && $this->product_catalog_exceeds_choice_limit() ) {
            $description .= ' ' . sprintf(
                /* translators: %s: number of products listed in the picker. */
                __( 'Showing the first %s products, sorted by title.', 'woocommerce-exporter' ),
                number_format_i18n( self::CHOICE_LIMIT )
            );
        }

        return $description;
    }

    /**
     * Lucide icon name for each filter card. The SPA falls back to a default
     * icon when this is empty or unknown.
     *
     * @since 5.6.0
     * @param string $filter_key Filter key.
     * @return string
     */
    private function get_filter_icon( $filter_key ) {
        $map = array(
            // Product.
            'product_filter_product_type_enabled'   => 'Box',
            'product_filter_product_status_enabled' => 'CheckCircle2',
            'product_filter_category_enabled'       => 'Tag',
            'product_filter_tag_enabled'            => 'Tags',
            'product_dates_filter_enabled'          => 'Calendar',
            'product_modified_dates_filter_enabled' => 'CalendarClock',
            'product_filter_custom_meta_enabled'    => 'Tags',
            'product_filter_brand_enabled'          => 'Award',
            'product_filter_user_role_enabled'      => 'Users',
            'product_filter_stock_status_enabled'   => 'Package',
            'product_filter_stock_quantity_enabled' => 'BarChart2',
            'product_filter_featured_enabled'       => 'Star',
            'product_filter_shipping_class_enabled' => 'Truck',
            'product_filter_sku_include'            => 'Barcode',
            'product_filter_language_include'       => 'Languages',
            'category_filter_language_include'      => 'Languages',
            'tag_filter_language_include'           => 'Languages',
            'product_filter_vendor_include'         => 'Store',
            // Order.
            'orders_filters[status]'                => 'CheckCircle2',
            'orders_filters[date]'                  => 'Calendar',
            'orders_filters[modified_date]'         => 'CalendarClock',
            'orders_filters[payment_method]'        => 'CreditCard',
            'order_filter_billing_country_enabled'  => 'MapPin',
            'order_filter_shipping_country_enabled' => 'MapPin',
            'order_filter_shipping_enabled'         => 'Truck',
            'order_filter_user_role_enabled'        => 'Users',
            'order_filter_customer_enabled'         => 'User',
            'order_filter_coupon_enabled'           => 'Ticket',
            'order_filter_product_enabled'          => 'ShoppingCart',
            'order_filter_category_enabled'         => 'Tag',
            'order_filter_tag_enabled'              => 'Tags',
            'order_filter_brand_enabled'            => 'Award',
            'order_filter_items_enabled'            => 'List',
            'order_filter_max_order_items_enabled'  => 'Hash',
            'order_filter_digital_enabled'          => 'Download',
            'order_filter_notes_enabled'            => 'FileText',
            'order_filter_item_types_enabled'       => 'Layers',
            'order_filter_custom_meta_enabled'      => 'Tags',
            // User.
            'user_filter_user_role_enabled'         => 'Users',
            'user_dates_filter_enabled'             => 'Calendar',
            'user_modified_dates_filter_enabled'    => 'CalendarClock',
            // Coupon.
            'coupon_filter_coupon_type_enabled'     => 'Percent',
            'coupon_dates_filter_enabled'           => 'Calendar',
            // Subscription.
            'subscription_filter_status_enabled'    => 'CheckCircle2',
            'subscription_filter_product_enabled'   => 'ShoppingCart',
            'subscription_dates_filter_enabled'     => 'Calendar',
            'subscription_filter_items_enabled'     => 'List',
            // Review.
            'review_filter_status_enabled'          => 'CheckCircle2',
            'review_dates_filter_enabled'           => 'Calendar',
        );
        return isset( $map[ $filter_key ] ) ? $map[ $filter_key ] : 'Filter';
    }

    /**
     * Sub-control definitions rendered when the user expands a filter card.
     *
     * Returns an array of controls. Each control has:
     *   - type:  'multiselect' | 'select' | 'radio_group' | 'date_range' | 'text' | 'number' | 'comparison_number'
     *   - name:  form_data field name to submit
     *   - label: short label rendered above the control (optional)
     *   - options: [{ value, label }] (multiselect / select / radio_group)
     *   - placeholder: input hint (text / number only)
     *
     * @since 5.6.0
     * @param string $filter_key Filter key.
     * @return array
     */
    private function get_filter_sub_options( $filter_key ) {
        switch ( $filter_key ) {
            case 'product_filter_product_type_enabled':
                if ( function_exists( 'woo_ce_get_product_types' ) ) {
                    $types   = (array) woo_ce_get_product_types();
                    $options = array();
                    foreach ( $types as $slug => $details ) {
                        // woo_ce_get_product_types() returns ['name' => '…', 'count' => N] per slug.
                        $label_str = is_array( $details ) && isset( $details['name'] )
                            ? (string) $details['name']
                            : (string) $details;
                        $options[] = array(
                            'value' => (string) $slug,
                            'label' => $label_str,
                        );
                    }
                    return array(
                        array(
                            'type'    => 'multiselect',
                            'name'    => 'product_filter_product_type[]',
                            'label'   => __( 'Product types', 'woocommerce-exporter' ),
                            'options' => $options,
                        ),
                    );
                }
                return array();

            case 'product_filter_product_status_enabled':
                $statuses = function_exists( 'get_post_statuses' ) ? get_post_statuses() : array(
                    'publish' => __( 'Published', 'woocommerce-exporter' ),
                    'pending' => __( 'Pending Review', 'woocommerce-exporter' ),
                    'draft'   => __( 'Draft', 'woocommerce-exporter' ),
                    'private' => __( 'Private', 'woocommerce-exporter' ),
                );
                $options  = array();
                foreach ( $statuses as $slug => $label ) {
                    $options[] = array(
                        'value' => (string) $slug,
                        'label' => (string) $label,
                    );
                }
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'product_filter_product_status[]',
                        'label'   => __( 'Product statuses', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            case 'product_filter_category_enabled':
                $options = $this->get_term_choices( 'product_cat' );
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'product_filter_category[]',
                        'label'   => __( 'Categories', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                    $this->get_match_type_control(
                        'product_filter_category_exclude',
                        __( 'Include selected categories', 'woocommerce-exporter' ),
                        __( 'Filter out selected categories', 'woocommerce-exporter' )
                    ),
                );

            case 'product_filter_tag_enabled':
                $options = $this->get_term_choices( 'product_tag' );
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'product_filter_tag[]',
                        'label'   => __( 'Tags', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                    $this->get_match_type_control(
                        'product_filter_tag_exclude',
                        __( 'Include selected tags', 'woocommerce-exporter' ),
                        __( 'Filter out selected tags', 'woocommerce-exporter' )
                    ),
                );

            case 'product_dates_filter_enabled':
                // "Date created" maps to the reader's published-date bounds (post_date).
                return array(
                    array(
                        'type'       => 'date_range',
                        'name'       => 'product_dates_filter',
                        'preset_key' => 'product_filter_published_dates',
                        'from_key'   => 'product_published_dates_from',
                        'to_key'     => 'product_published_dates_to',
                        'label'      => __( 'Date created', 'woocommerce-exporter' ),
                    ),
                );

            case 'product_modified_dates_filter_enabled':
                // "Date modified" maps to the reader's product_dates bounds (post_modified).
                return array(
                    array(
                        'type'       => 'date_range',
                        'name'       => 'product_modified_dates_filter',
                        'preset_key' => 'product_filter_dates',
                        'from_key'   => 'product_dates_from',
                        'to_key'     => 'product_dates_to',
                        'label'      => __( 'Date modified', 'woocommerce-exporter' ),
                    ),
                );

            case 'orders_filters[status]':
                $statuses = function_exists( 'woo_ce_get_order_statuses' ) ? woo_ce_get_order_statuses() : array();
                $options  = array();
                foreach ( (array) $statuses as $status ) {
                    if ( is_object( $status ) && isset( $status->slug, $status->name ) ) {
                        $options[] = array(
                            'value' => (string) $status->slug,
                            'label' => (string) $status->name,
                        );
                    } elseif ( is_array( $status ) && isset( $status['slug'], $status['name'] ) ) {
                        $options[] = array(
                            'value' => (string) $status['slug'],
                            'label' => (string) $status['name'],
                        );
                    }
                }
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'orders_filter_status[]',
                        'label'   => __( 'Order statuses', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            case 'orders_filters[date]':
                // Control name differs from the reader keys, so map them explicitly.
                return array(
                    array(
                        'type'       => 'date_range',
                        'name'       => 'orders_filter_date',
                        'preset_key' => 'order_dates_filter',
                        'from_key'   => 'order_dates_from',
                        'to_key'     => 'order_dates_to',
                        'label'      => __( 'Order date', 'woocommerce-exporter' ),
                    ),
                );

            case 'orders_filters[modified_date]':
                return array(
                    array(
                        'type'       => 'date_range',
                        'name'       => 'orders_filter_modified_date',
                        'preset_key' => 'order_modified_dates_filter',
                        'from_key'   => 'order_modified_dates_from',
                        'to_key'     => 'order_modified_dates_to',
                        'label'      => __( 'Date modified', 'woocommerce-exporter' ),
                    ),
                );

            case 'user_filter_user_role_enabled':
                $roles   = function_exists( 'woo_ce_get_user_roles' ) ? woo_ce_get_user_roles() : array();
                $options = array();
                foreach ( (array) $roles as $slug => $details ) {
                    $label     = is_array( $details ) && isset( $details['name'] ) ? $details['name'] : (string) $details;
                    $options[] = array(
                        'value' => (string) $slug,
                        'label' => (string) $label,
                    );
                }
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'user_filter_user_role[]',
                        'label'   => __( 'User roles', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            case 'user_dates_filter_enabled':
                return array(
                    array(
                        'type'  => 'date_range',
                        'name'  => 'user_dates_filter',
                        'label' => __( 'Date registered', 'woocommerce-exporter' ),
                    ),
                );

            case 'user_modified_dates_filter_enabled':
                return array(
                    array(
                        'type'  => 'date_range',
                        'name'  => 'user_modified_dates_filter',
                        'label' => __( 'Date modified', 'woocommerce-exporter' ),
                    ),
                );

            case 'product_filter_user_role_enabled':
                $roles   = function_exists( 'woo_ce_get_user_roles' ) ? woo_ce_get_user_roles() : array();
                $options = array();
                foreach ( (array) $roles as $slug => $details ) {
                    $label     = is_array( $details ) && isset( $details['name'] ) ? $details['name'] : (string) $details;
                    $options[] = array(
                        'value' => (string) $slug,
                        'label' => (string) $label,
                    );
                }
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'product_filter_user_role[]',
                        'label'   => __( 'User roles', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            case 'product_filter_brand_enabled':
                // Offer the brands of every brand taxonomy. woo_ce_get_products()
                // matches the term IDs against all of them. See #416.
                $options = $this->get_brand_term_choices();
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'product_filter_brand[]',
                        'label'   => __( 'Brands', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            case 'product_filter_stock_status_enabled':
                return array(
                    array(
                        'type'    => 'radio_group',
                        'name'    => 'product_filter_stock_status',
                        'label'   => __( 'Stock Status', 'woocommerce-exporter' ),
                        'default' => 'all',
                        'options' => array(
                            array(
                                'value' => 'all',
                                'label' => __( 'Both', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'instock',
                                'label' => __( 'In Stock', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'outofstock',
                                'label' => __( 'Out of Stock', 'woocommerce-exporter' ),
                            ),
                        ),
                    ),
                );

            case 'product_filter_stock_quantity_enabled':
                return array(
                    array(
                        'type'          => 'comparison_number',
                        'name'          => 'product_filter_stock_quantity',
                        'operator_name' => 'product_filter_stock_quantity_operator',
                        'label'         => __( 'Stock Quantity', 'woocommerce-exporter' ),
                        'operators'     => array(
                            array(
                                'value' => '>',
                                'label' => __( 'Greater than', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => '>=',
                                'label' => __( 'At least', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => '<',
                                'label' => __( 'Less than', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => '<=',
                                'label' => __( 'At most', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => '=',
                                'label' => __( 'Exactly', 'woocommerce-exporter' ),
                            ),
                        ),
                    ),
                );

            case 'product_filter_featured_enabled':
                return array(
                    array(
                        'type'    => 'radio_group',
                        'name'    => 'product_filter_featured',
                        'label'   => __( 'Featured', 'woocommerce-exporter' ),
                        'default' => 'all',
                        'options' => array(
                            array(
                                'value' => 'all',
                                'label' => __( 'Both', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'featured',
                                'label' => __( 'Featured', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'not_featured',
                                'label' => __( 'Not Featured', 'woocommerce-exporter' ),
                            ),
                        ),
                    ),
                );

            case 'product_filter_shipping_class_enabled':
                $options = $this->get_term_choices( 'product_shipping_class' );
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'product_filter_shipping_class[]',
                        'label'   => __( 'Shipping classes', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            case 'product_filter_sku_include':
                // Values are product IDs, consumed via post__in / post__not_in in
                // woo_ce_get_products(). The label only shows the SKU. The exclude
                // toggle flips the SAME selection between the two.
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'product_filter_sku[]',
                        'label'   => __( 'Products', 'woocommerce-exporter' ),
                        'options' => $this->get_product_choices(),
                    ),
                    $this->get_match_type_control(
                        'product_filter_sku_exclude',
                        __( 'Include selected products', 'woocommerce-exporter' ),
                        __( 'Exclude selected products', 'woocommerce-exporter' )
                    ),
                );

            case 'product_filter_language_include':
                $options = array();
                if ( function_exists( 'icl_get_languages' ) ) {
                    foreach ( (array) icl_get_languages( 'skip_missing=N' ) as $code => $language ) {
                        $label     = is_array( $language ) && isset( $language['native_name'] ) ? $language['native_name'] : (string) $code;
                        $options[] = array(
                            'value' => (string) $code,
                            'label' => (string) $label,
                        );
                    }
                }
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'product_filter_language[]',
                        'label'   => __( 'Languages', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            case 'category_filter_language_include':
            case 'tag_filter_language_include':
                $options = array();
                if ( function_exists( 'woo_ce_get_multilingual_languages' ) ) {
                    foreach ( woo_ce_get_multilingual_languages() as $code => $label ) {
                        $options[] = array(
                            'value' => (string) $code,
                            'label' => (string) $label,
                        );
                    }
                }
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 0 === strpos( $filter_key, 'tag_' ) ? 'tag_filter_language[]' : 'category_filter_language[]',
                        'label'   => __( 'Languages', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            case 'product_filter_vendor_include':
                $options = array();
                if ( function_exists( 'woo_ce_get_product_vendors' ) ) {
                    foreach ( (array) woo_ce_get_product_vendors( array( 'hide_empty' => 1 ), 'full' ) as $vendor ) {
                        if ( is_object( $vendor ) && isset( $vendor->term_id, $vendor->name ) ) {
                            $options[] = array(
                                'value' => (string) $vendor->term_id,
                                'label' => (string) $vendor->name,
                            );
                        }
                    }
                }
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'product_filter_vendor[]',
                        'label'   => __( 'Vendors', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            // ── Order filter sub-options ──────────────────────────────────────

            case 'orders_filters[payment_method]':
                $gateways = function_exists( 'woo_ce_get_order_payment_gateways' ) ? (array) woo_ce_get_order_payment_gateways() : array();
                $options  = array();
                foreach ( $gateways as $gateway ) {
                    if ( is_object( $gateway ) && isset( $gateway->id ) ) {
                        $options[] = array(
                            'value' => (string) $gateway->id,
                            'label' => (string) ( isset( $gateway->method_title ) ? $gateway->method_title : $gateway->id ),
                        );
                    }
                }
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'orders_filter_payment_method[]',
                        'label'   => __( 'Payment gateways', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            case 'order_filter_billing_country_enabled':
            case 'order_filter_shipping_country_enabled':
                $countries = WC()->countries ? WC()->countries->get_countries() : array();
                $options   = array();
                foreach ( (array) $countries as $code => $name ) {
                    $options[] = array(
                        'value' => (string) $code,
                        'label' => (string) $name,
                    );
                }
                $field_name = ( 'order_filter_billing_country_enabled' === $filter_key )
                    ? 'order_filter_billing_country[]'
                    : 'order_filter_shipping_country[]';
                $label      = ( 'order_filter_billing_country_enabled' === $filter_key )
                    ? __( 'Billing countries', 'woocommerce-exporter' )
                    : __( 'Shipping countries', 'woocommerce-exporter' );
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => $field_name,
                        'label'   => $label,
                        'options' => $options,
                    ),
                );

            case 'order_filter_shipping_enabled':
                $methods = function_exists( 'woo_ce_get_order_shipping_methods' ) ? (array) woo_ce_get_order_shipping_methods() : array();
                $options = array();
                foreach ( $methods as $method ) {
                    if ( is_object( $method ) && isset( $method->id ) ) {
                        if ( method_exists( $method, 'get_title' ) && '' !== (string) $method->get_title() ) {
                            $label = (string) $method->get_title();
                        } elseif ( ! empty( $method->method_title ) ) {
                            $label = (string) $method->method_title;
                        } elseif ( ! empty( $method->title ) ) {
                            $label = (string) $method->title;
                        } else {
                            $label = (string) $method->id;
                        }
                        $options[] = array(
                            'value' => (string) $method->id,
                            'label' => $label,
                        );
                    }
                }
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'order_filter_shipping[]',
                        'label'   => __( 'Shipping methods', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            case 'order_filter_user_role_enabled':
                $roles   = function_exists( 'woo_ce_get_user_roles' ) ? (array) woo_ce_get_user_roles() : array();
                $options = array();
                foreach ( $roles as $slug => $details ) {
                    $label     = is_array( $details ) && isset( $details['name'] ) ? $details['name'] : (string) $details;
                    $options[] = array(
                        'value' => (string) $slug,
                        'label' => (string) $label,
                    );
                }
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'order_filter_user_role[]',
                        'label'   => __( 'User roles', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            case 'order_filter_customer_enabled':
                return array(
                    array(
                        'type'        => 'text',
                        'name'        => 'order_filter_customer',
                        'label'       => __( 'Customer email', 'woocommerce-exporter' ),
                        'placeholder' => __( 'e.g. customer@example.com', 'woocommerce-exporter' ),
                    ),
                );

            case 'order_filter_coupon_enabled':
                return array(
                    array(
                        'type'        => 'text',
                        'name'        => 'order_filter_coupon',
                        'label'       => __( 'Coupon code', 'woocommerce-exporter' ),
                        'placeholder' => __( 'e.g. SALE20', 'woocommerce-exporter' ),
                    ),
                );

            case 'order_filter_product_enabled':
                return array(
                    array(
                        'type'        => 'text',
                        'name'        => 'order_filter_product',
                        'label'       => __( 'Product IDs', 'woocommerce-exporter' ),
                        'placeholder' => __( 'Comma-separated product IDs, e.g. 10,42,99', 'woocommerce-exporter' ),
                    ),
                );

            case 'order_filter_category_enabled':
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'order_filter_category[]',
                        'label'   => __( 'Product categories', 'woocommerce-exporter' ),
                        'options' => $this->get_term_choices( 'product_cat' ),
                    ),
                );

            case 'order_filter_tag_enabled':
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'order_filter_tag[]',
                        'label'   => __( 'Product tags', 'woocommerce-exporter' ),
                        'options' => $this->get_term_choices( 'product_tag' ),
                    ),
                );

            case 'order_filter_brand_enabled':
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'order_filter_brand[]',
                        'label'   => __( 'Brands', 'woocommerce-exporter' ),
                        'options' => $this->get_brand_term_choices(),
                    ),
                );

            case 'order_filter_items_enabled':
                return array(
                    array(
                        'type'    => 'radio_group',
                        'name'    => 'order_filter_items',
                        'label'   => __( 'Items formatting', 'woocommerce-exporter' ),
                        // The wizard submits nothing when the declared default is picked, so the
                        // default must be the format the export falls back to.
                        'default' => Order_Export::get_fallback_order_items_format(),
                        'options' => array(
                            array(
                                'value' => 'combined',
                                'label' => __( 'Combined', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'unique',
                                'label' => __( 'Unique', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'individual',
                                'label' => __( 'Individual', 'woocommerce-exporter' ),
                            ),
                        ),
                    ),
                );

            case 'order_filter_max_order_items_enabled':
                return array(
                    array(
                        'type'        => 'number',
                        'name'        => 'order_filter_max_order_items',
                        'label'       => __( 'Maximum unique items', 'woocommerce-exporter' ),
                        'placeholder' => '10',
                    ),
                );

            case 'order_filter_digital_enabled':
                return array(
                    array(
                        'type'    => 'select',
                        'name'    => 'order_filter_digital',
                        'label'   => __( 'Digital products', 'woocommerce-exporter' ),
                        'default' => '',
                        'options' => array(
                            array(
                                'value' => '',
                                'label' => __( 'Include all', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'include_digital',
                                'label' => __( 'Digital products only', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'exclude_digital',
                                'label' => __( 'Exclude digital products', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'exclude_digital_only',
                                'label' => __( 'Exclude digital-only orders', 'woocommerce-exporter' ),
                            ),
                        ),
                    ),
                );

            case 'order_filter_notes_enabled':
                return array(
                    array(
                        'type'    => 'radio_group',
                        'name'    => 'order_filter_flag_notes',
                        'label'   => __( 'Order notes', 'woocommerce-exporter' ),
                        'default' => '0',
                        'options' => array(
                            array(
                                'value' => '0',
                                'label' => __( 'Exclude order notes', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => '1',
                                'label' => __( 'Include order notes', 'woocommerce-exporter' ),
                            ),
                        ),
                    ),
                );

            case 'order_filter_item_types_enabled':
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'order_filter_item_types[]',
                        'label'   => __( 'Order item types', 'woocommerce-exporter' ),
                        'options' => array(
                            array(
                                'value' => 'line_item',
                                'label' => __( 'Line Items', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'fee',
                                'label' => __( 'Fees', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'shipping',
                                'label' => __( 'Shipping', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'tax',
                                'label' => __( 'Taxes', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'coupon',
                                'label' => __( 'Coupons', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'refund',
                                'label' => __( 'Refunds', 'woocommerce-exporter' ),
                            ),
                        ),
                    ),
                );

            // ── Coupon filter sub-options ─────────────────────────────────────

            case 'coupon_filter_coupon_type_enabled':
                $discount_types = function_exists( 'wc_get_coupon_types' ) ? wc_get_coupon_types() : array(
                    'percent'       => __( 'Percentage discount', 'woocommerce-exporter' ),
                    'fixed_cart'    => __( 'Fixed cart discount', 'woocommerce-exporter' ),
                    'fixed_product' => __( 'Fixed product discount', 'woocommerce-exporter' ),
                );
                $options        = array();
                foreach ( (array) $discount_types as $slug => $label ) {
                    $options[] = array(
                        'value' => (string) $slug,
                        'label' => (string) $label,
                    );
                }
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'coupon_filter_discount_type[]',
                        'label'   => __( 'Discount types', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            case 'coupon_dates_filter_enabled':
                return array(
                    array(
                        'type'  => 'date_range',
                        'name'  => 'coupon_dates_filter',
                        'label' => __( 'Date created', 'woocommerce-exporter' ),
                    ),
                );

            // ── Subscription filter sub-options ──────────────────────────────

            case 'subscription_filter_status_enabled':
                $sub_statuses = function_exists( 'wcs_get_subscription_statuses' )
                    ? (array) wcs_get_subscription_statuses()
                    : array(
                        'wc-active'         => __( 'Active', 'woocommerce-exporter' ),
                        'wc-on-hold'        => __( 'On Hold', 'woocommerce-exporter' ),
                        'wc-cancelled'      => __( 'Cancelled', 'woocommerce-exporter' ),
                        'wc-expired'        => __( 'Expired', 'woocommerce-exporter' ),
                        'wc-pending-cancel' => __( 'Pending Cancel', 'woocommerce-exporter' ),
                        'wc-pending'        => __( 'Pending', 'woocommerce-exporter' ),
                    );
                $options      = array();
                foreach ( $sub_statuses as $slug => $label ) {
                    $options[] = array(
                        'value' => (string) $slug,
                        'label' => (string) $label,
                    );
                }
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'subscription_filter_status[]',
                        'label'   => __( 'Subscription statuses', 'woocommerce-exporter' ),
                        'options' => $options,
                    ),
                );

            case 'subscription_filter_product_enabled':
                return array(
                    array(
                        'type'        => 'text',
                        'name'        => 'subscription_filter_product',
                        'label'       => __( 'Product IDs', 'woocommerce-exporter' ),
                        'placeholder' => __( 'Comma-separated product IDs, e.g. 10,42,99', 'woocommerce-exporter' ),
                    ),
                );

            case 'subscription_dates_filter_enabled':
                return array(
                    array(
                        'type'  => 'date_range',
                        'name'  => 'subscription_dates_filter',
                        'label' => __( 'Subscription date', 'woocommerce-exporter' ),
                    ),
                );

            case 'subscription_filter_items_enabled':
                return array(
                    array(
                        'type'    => 'radio_group',
                        'name'    => 'subscription_filter_items',
                        'label'   => __( 'Items formatting', 'woocommerce-exporter' ),
                        'default' => 'combined',
                        'options' => array(
                            array(
                                'value' => 'combined',
                                'label' => __( 'Combined', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'unique',
                                'label' => __( 'Unique', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'individual',
                                'label' => __( 'Individual', 'woocommerce-exporter' ),
                            ),
                        ),
                    ),
                );

            // ── Review filter sub-options ─────────────────────────────────────

            case 'review_filter_status_enabled':
                return array(
                    array(
                        'type'    => 'radio_group',
                        'name'    => 'review_filter_status',
                        'label'   => __( 'Approval status', 'woocommerce-exporter' ),
                        'default' => 'all',
                        'options' => array(
                            array(
                                'value' => 'all',
                                'label' => __( 'All', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'approve',
                                'label' => __( 'Approved', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'hold',
                                'label' => __( 'Pending', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'spam',
                                'label' => __( 'Spam', 'woocommerce-exporter' ),
                            ),
                        ),
                    ),
                );

            case 'review_dates_filter_enabled':
                return array(
                    array(
                        'type'  => 'date_range',
                        'name'  => 'review_dates_filter',
                        'label' => __( 'Review date', 'woocommerce-exporter' ),
                    ),
                );

            // ── Booking filter sub-options ────────────────────────────────────

            case 'booking_filter_status_enabled':
                return array(
                    array(
                        'type'    => 'multiselect',
                        'name'    => 'booking_filter_status[]',
                        'label'   => __( 'Booking statuses', 'woocommerce-exporter' ),
                        'options' => array(
                            array(
                                'value' => 'unpaid',
                                'label' => __( 'Unpaid', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'pending-confirmation',
                                'label' => __( 'Pending Confirmation', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'confirmed',
                                'label' => __( 'Confirmed', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'paid',
                                'label' => __( 'Paid', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'cancelled',
                                'label' => __( 'Cancelled', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'complete',
                                'label' => __( 'Complete', 'woocommerce-exporter' ),
                            ),
                            array(
                                'value' => 'in-cart',
                                'label' => __( 'In Cart', 'woocommerce-exporter' ),
                            ),
                        ),
                    ),
                );

            case 'booking_dates_filter_enabled':
                return array(
                    array(
                        'type'  => 'date_range',
                        'name'  => 'booking_dates_filter',
                        'label' => __( 'Booking date', 'woocommerce-exporter' ),
                    ),
                );

            // Custom-meta VALUE filter sub-options: one text control per
            // registered key. product_custom_meta stores a single string;
            // order_custom_meta is comma-exploded by the engine.
            case 'product_filter_custom_meta_enabled':
            case 'order_filter_custom_meta_enabled':
                $option_name = ( 'product_filter_custom_meta_enabled' === $filter_key ) ? 'custom_products' : 'custom_orders';
                $name_prefix = ( 'product_filter_custom_meta_enabled' === $filter_key ) ? 'product_filter_custom_meta-' : 'order_filter_custom_meta-';
                $controls    = array();
                foreach ( $this->get_registered_custom_meta_keys( $option_name ) as $meta_key ) {
                    $controls[] = array(
                        'type'        => 'text',
                        'name'        => $name_prefix . $meta_key,
                        'label'       => $meta_key,
                        'placeholder' => __( 'Filter by value…', 'woocommerce-exporter' ),
                    );
                }
                return $controls;

            default:
                return array();
        }
    }

    /**
     * Fetch term choices for the multiselect sub-controls.
     *
     * @since 5.6.0
     * @param string $taxonomy Taxonomy slug.
     * @return array<array{value:string,label:string}>
     */
    private function get_term_choices( $taxonomy ) {
        $terms = get_terms(
            array(
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'number'     => $this->get_term_choices_limit( $taxonomy ),
            )
        );
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return array();
        }
        $options = array();
        foreach ( $terms as $term ) {
            // Term names are stored entity-encoded; the multiselect prints the label as text.
            $options[] = array(
                'value' => (string) $term->term_id,
                'label' => wp_specialchars_decode( (string) $term->name, ENT_QUOTES ),
            );
        }
        return $options;
    }

    /**
     * Build the "Match type" radio that flips a card's selection between
     * including and filtering out the selected items.
     *
     * '0' includes, '1' filters out: the value the engine's `*_exclude` flags
     * read (includes/product.php).
     *
     * @param string $name          Control name, e.g. `product_filter_category_exclude`.
     * @param string $include_label Label of the include choice.
     * @param string $exclude_label Label of the filter-out choice.
     *
     * @since 2.8.3
     * @return array The radio_group control.
     */
    private function get_match_type_control( $name, $include_label, $exclude_label ) {
        return array(
            'type'    => 'radio_group',
            'name'    => $name,
            'label'   => __( 'Match type', 'woocommerce-exporter' ),
            'default' => '0',
            'options' => array(
                array(
                    'value' => '0',
                    'label' => $include_label,
                ),
                array(
                    'value' => '1',
                    'label' => $exclude_label,
                ),
            ),
        );
    }

    /**
     * Fetch a bounded list of products for the SKU multiselect.
     *
     * Values are product IDs; the label shows the SKU. Capped at
     * self::CHOICE_LIMIT so the payload stays bounded on a large catalog.
     *
     * @since 2.8.2
     * @return array<array{value:string,label:string}>
     */
    private function get_product_choices() {
        $products = get_posts(
            array(
                'post_type'      => 'product',
                'post_status'    => 'any',
                // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- Capped at self::CHOICE_LIMIT, same bound as get_term_choices(); keeps the filter payload bounded on a large catalog.
                'posts_per_page' => self::CHOICE_LIMIT,
                'orderby'        => 'title',
                'order'          => 'ASC',
            )
        );
        if ( empty( $products ) ) {
            return array();
        }
        $options = array();
        foreach ( $products as $product ) {
            $sku       = get_post_meta( $product->ID, '_sku', true );
            $label     = $sku ? sprintf( '%s (%s)', $product->post_title, $sku ) : $product->post_title;
            $options[] = array(
                'value' => (string) $product->ID,
                'label' => (string) $label,
            );
        }
        return $options;
    }

    /**
     * Whether the catalog holds more products than the SKU picker can list.
     *
     * Asks only for the single product past the cap, so the check costs one
     * bounded ID query rather than a full count.
     *
     * @since 2.8.2
     * @return bool
     */
    private function product_catalog_exceeds_choice_limit() {
        static $exceeds = null;
        if ( null === $exceeds ) {
            $overflow = get_posts(
                array(
                    'post_type'      => 'product',
                    'post_status'    => 'any',
                    'posts_per_page' => 1,
                    'offset'         => self::CHOICE_LIMIT,
                    'fields'         => 'ids',
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                    'no_found_rows'  => true,
                )
            );
            $exceeds  = ! empty( $overflow );
        }
        return $exceeds;
    }

    /**
     * Resolve how many terms a filter multiselect may list.
     *
     * The bound is filterable per taxonomy so a store with more terms than
     * self::CHOICE_LIMIT can still pick every term.
     *
     * Return -1, or 0, to list every term. -1 is mapped onto 0 here because
     * WP_Term_Query runs absint() over its number argument, so -1 would arrive
     * as 1. A value that is not numeric, not a whole number, or outside PHP's
     * integer range leaves the default in place.
     *
     * CAUTION: a very large term list enlarges the filters payload. Raise the
     * bound to the number of terms the store holds rather than removing it.
     *
     * @since 2.8.2
     * @param string $taxonomy Taxonomy slug the choices are read from.
     * @return int Number of terms to request. 0 lists every term.
     */
    private function get_term_choices_limit( $taxonomy ) {
        /**
         * Filters the maximum number of terms listed in a Quick Export filter multiselect.
         *
         * Applies to the Category, Tag and Shipping class multiselects on the Product
         * and Order filter cards. It does not reach the Brands multiselects: those are
         * built by get_brand_term_choices(), which lists every brand already.
         *
         * @since 2.8.2
         * @param int    $limit    Maximum number of terms to list. Return -1 or 0 for every term.
         * @param string $taxonomy Taxonomy slug the choices are read from.
         */
        $limit = apply_filters( 'woo_ce_quick_export_term_choices_limit', self::CHOICE_LIMIT, $taxonomy );

        if ( ! is_numeric( $limit ) ) {
            return self::CHOICE_LIMIT;
        }

        $limit = (float) $limit;

        // Only a whole number inside PHP's integer range can be honoured as a
        // bound. A fractional value casts down - 0.4 becomes 0 - and 0 means
        // every term, the opposite of the small bound the caller asked for.
        if ( floor( $limit ) !== $limit || abs( $limit ) > PHP_INT_MAX ) {
            return self::CHOICE_LIMIT;
        }

        return $limit < 1 ? 0 : (int) $limit;
    }

    /**
     * Fetch brand choices for the Brand filters.
     *
     * Every brand taxonomy on the store is offered rather than just the first
     * one that happens to hold terms: the Order Brand filter matches the
     * submitted term IDs without pinning a taxonomy, so brands from two Brands
     * plugins can be mixed in one selection. Where more than one taxonomy
     * contributes, the taxonomy slug is appended so two plugins' brands of the
     * same name stay apart - the registered labels cannot do that job, every
     * Brands plugin calls its singular label "Brand".
     *
     * The Product Brand filter uses this too - woo_ce_get_products()
     * ( includes/product.php ) matches the term IDs against every brand taxonomy.
     *
     * The term count is left uncapped, unlike get_term_choices() for Categories and
     * Tags: a brand this dropdown silently omitted could not be filtered on at all.
     *
     * Falls back to the first brand taxonomy holding terms when
     * woo_ce_get_brand_filter_terms() ( includes/brand.php ) is unavailable.
     *
     * @since 2.8.1
     * @return array<array{value:string,label:string}>
     */
    private function get_brand_term_choices() {
        if ( ! function_exists( 'woo_ce_get_brand_filter_terms' ) ) {
            foreach ( array( 'product_brand', 'pwb-brand', 'pa_brand', 'yith_product_brand' ) as $tax ) {
                $brand_options = $this->get_term_choices( $tax );
                if ( ! empty( $brand_options ) ) {
                    return $brand_options;
                }
            }
            return array();
        }

        $terms = woo_ce_get_brand_filter_terms();
        if ( empty( $terms ) ) {
            return array();
        }

        $show_taxonomy = count( array_unique( wp_list_pluck( $terms, 'taxonomy' ) ) ) > 1;

        $options = array();
        foreach ( $terms as $term ) {
            // woo_ce_get_brand_filter_terms() already decoded the name; the
            // multiselect prints the label as text. Only the value is a term ID,
            // never the suffixed label, or the selection would export nothing.
            $name  = (string) $term->name;
            $label = $name;
            if ( $show_taxonomy ) {
                $label = sprintf(
                    /* translators: 1: brand name, 2: the brand taxonomy slug, e.g. pwb-brand */
                    __( '%1$s (%2$s)', 'woocommerce-exporter' ),
                    $name,
                    $term->taxonomy
                );
            }
            $options[] = array(
                'value' => (string) $term->term_id,
                'label' => $label,
            );
        }
        return $options;
    }

    /**
     * Return the sort field options for a given export type.
     *
     * @param string $type Export type slug.
     *
     * @since 5.6.0
     * @since 2.8.3 Products also return `levels`: the "Then by" field/direction form keys.
     * @return array  { key: form_data_field_name, options: [ { value, label } ], levels?: [ { field_key, order_key } ] }|null
     */
    private function get_type_sort_options( $type ) {
        $sort_definitions = array(
            'product'        => array(
                'key'     => 'product_orderby',
                'options' => array(
                    array(
                        'value' => 'ID',
                        'label' => __( 'ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'title',
                        'label' => __( 'Title', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'date',
                        'label' => __( 'Date Created', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'modified',
                        'label' => __( 'Date Modified', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'rand',
                        'label' => __( 'Random', 'woocommerce-exporter' ),
                    ),
                ),
                'levels'  => array(
                    array(
                        'field_key' => 'product_orderby_field1',
                        'order_key' => 'product_order_field1',
                    ),
                    array(
                        'field_key' => 'product_orderby_field2',
                        'order_key' => 'product_order_field2',
                    ),
                ),
            ),
            'order'          => array(
                'key'     => 'order_orderby',
                'options' => array(
                    array(
                        'value' => 'ID',
                        'label' => __( 'ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'title',
                        'label' => __( 'Title', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'date',
                        'label' => __( 'Date Created', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'modified',
                        'label' => __( 'Date Modified', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'rand',
                        'label' => __( 'Random', 'woocommerce-exporter' ),
                    ),
                ),
            ),
            'customer'       => array(
                'key'     => 'customer_orderby',
                'options' => array(
                    array(
                        'value' => 'ID',
                        'label' => __( 'ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'date',
                        'label' => __( 'Date Created', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'modified',
                        'label' => __( 'Date Modified', 'woocommerce-exporter' ),
                    ),
                ),
            ),
            'user'           => array(
                'key'     => 'user_orderby',
                'options' => array(
                    array(
                        'value' => 'ID',
                        'label' => __( 'ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'display_name',
                        'label' => __( 'Display Name', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'user_login',
                        'label' => __( 'Username', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'user_email',
                        'label' => __( 'Email', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'user_nicename',
                        'label' => __( 'Nicename', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'user_url',
                        'label' => __( 'URL', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'registered',
                        'label' => __( 'Date Registered', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'rand',
                        'label' => __( 'Random', 'woocommerce-exporter' ),
                    ),
                ),
            ),
            'coupon'         => array(
                'key'     => 'coupon_orderby',
                'options' => array(
                    array(
                        'value' => 'ID',
                        'label' => __( 'ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'title',
                        'label' => __( 'Code', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'date',
                        'label' => __( 'Date Created', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'modified',
                        'label' => __( 'Date Modified', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'rand',
                        'label' => __( 'Random', 'woocommerce-exporter' ),
                    ),
                ),
            ),
            'category'       => array(
                'key'     => 'category_orderby',
                'options' => array(
                    array(
                        'value' => 'id',
                        'label' => __( 'Term ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'name',
                        'label' => __( 'Category Name', 'woocommerce-exporter' ),
                    ),
                ),
            ),
            'tag'            => array(
                'key'     => 'tag_orderby',
                'options' => array(
                    array(
                        'value' => 'id',
                        'label' => __( 'Term ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'name',
                        'label' => __( 'Tag Name', 'woocommerce-exporter' ),
                    ),
                ),
            ),
            'brand'          => array(
                'key'     => 'brand_orderby',
                'options' => array(
                    array(
                        'value' => 'id',
                        'label' => __( 'Term ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'name',
                        'label' => __( 'Brand Name', 'woocommerce-exporter' ),
                    ),
                ),
            ),
            'subscription'   => array(
                'key'     => 'subscription_orderby',
                'options' => array(
                    array(
                        'value' => 'ID',
                        'label' => __( 'ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'start_date',
                        'label' => __( 'Start Date', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'expiry_date',
                        'label' => __( 'Expiry Date', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'end_date',
                        'label' => __( 'End Date', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'status',
                        'label' => __( 'Status', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'name',
                        'label' => __( 'Name', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'order_id',
                        'label' => __( 'Order ID', 'woocommerce-exporter' ),
                    ),
                ),
            ),
            'review'         => array(
                'key'     => 'review_orderby',
                'options' => array(
                    array(
                        'value' => 'id',
                        'label' => __( 'ID', 'woocommerce-exporter' ),
                    ),
                ),
            ),
            'commission'     => array(
                'key'     => 'commission_orderby',
                'options' => array(
                    array(
                        'value' => 'ID',
                        'label' => __( 'ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'title',
                        'label' => __( 'Title', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'date',
                        'label' => __( 'Date Created', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'modified',
                        'label' => __( 'Date Modified', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'rand',
                        'label' => __( 'Random', 'woocommerce-exporter' ),
                    ),
                ),
            ),
            'shipping_class' => array(
                'key'     => 'shipping_class_orderby',
                'options' => array(
                    array(
                        'value' => 'id',
                        'label' => __( 'Term ID', 'woocommerce-exporter' ),
                    ),
                    array(
                        'value' => 'name',
                        'label' => __( 'Name', 'woocommerce-exporter' ),
                    ),
                ),
            ),
        );

        // Offer only the order sort keys the active order store honours, so the dropdown
        // never shows a control wc_get_orders() would silently ignore.
        if ( 'order' === $type ) {
            $sort_definitions['order']['options'] = array_values(
                array_filter(
                    $sort_definitions['order']['options'],
                    function ( $option ) {
                        return ! Order_Export::is_ignored_orderby( $option['value'] );
                    }
                )
            );
        }

        return isset( $sort_definitions[ $type ] ) ? $sort_definitions[ $type ] : null;
    }

    // -------------------------------------------------------------------------
    // Private helpers – shared
    // -------------------------------------------------------------------------

    /**
     * Check whether the given slug is a recognised export type.
     *
     * @param string $type Export type slug.
     *
     * @since 5.6.0
     * @return bool
     */
    private function is_valid_type( $type ) {
        return array_key_exists( $type, $this->get_export_type_definitions() );
    }

    /**
     * Curated "common" field set per export type. Read through
     * resolve_default_field_keys(), which turns it into the default selection
     * when the user has not yet saved a custom one — keeping the initial export
     * concise rather than dumping every column.
     *
     * Every key must be a field name registered by the type's
     * woo_ce_get_{type}_fields() list — group_fields() silently skips a key
     * that matches nothing, leaving that column unselected. User
     * `billing_country` is registered only on some stores (it is read from
     * the WC_Admin_Profile customer meta fields) and drops out elsewhere.
     *
     * Only the free export types are listed; a premium type cannot be
     * exported here, and resolve_default_field_keys() selects every field for
     * a type with no curated set.
     *
     * @since 5.6.0
     * @since 2.8.3 Corrected keys that matched no registered field.
     * @param string $type Export type slug.
     * @return string[]
     */
    private function get_default_field_keys( $type ) {
        $defaults = array(
            'product'  => array( 'sku', 'name', 'type', 'price', 'sale_price', 'stock_status', 'quantity', 'tax_class', 'category', 'tag' ),
            'order'    => array( 'purchase_id', 'purchase_date', 'payment_status', 'purchase_total', 'billing_first_name', 'billing_last_name', 'billing_email', 'shipping_method', 'payment_gateway', 'user_id' ),
            'user'     => array( 'user_id', 'user_name', 'email', 'first_name', 'last_name', 'full_name', 'user_role', 'date_registered', 'billing_country' ),
            'category' => array( 'term_id', 'name', 'slug', 'parent_id', 'description', 'count', 'image', 'display_type' ),
            'tag'      => array( 'term_id', 'name', 'slug', 'description', 'count' ),
        );

        return isset( $defaults[ $type ] ) ? $defaults[ $type ] : array();
    }

    /**
     * The field names an export type defaults to when nothing is saved.
     *
     * The single source of truth for the default selection: group_fields()
     * ticks exactly these fields, and get_fields() serves the same list as
     * `default_fields`, so the response cannot disagree with its own flags.
     *
     * A type whose curated set matches none of the fields it serves defaults
     * to every field, the way the classic screen did. The list is never cut
     * by field position.
     *
     * @param string $type   Export type slug.
     * @param array  $fields Flat array of [ 'name' => '', 'label' => '' ] items.
     *
     * @since 2.8.3
     * @return string[]
     */
    private function resolve_default_field_keys( $type, array $fields ) {
        $names    = array_column( $fields, 'name' );
        $defaults = $this->get_default_field_keys( $type );

        return array_intersect( $defaults, $names ) ? $defaults : $names;
    }

    /**
     * Static export type definitions shared with Export_Items_Controller.
     *
     * Returns [ slug => [ label, category ] ] for every supported type.
     *
     * @since 5.6.0
     * @return array
     */
    private function get_export_type_definitions() {
        return array(
            'product'        => array(
                'label'    => __( 'Products', 'woocommerce-exporter' ),
                'category' => __( 'Products', 'woocommerce-exporter' ),
            ),
            'category'       => array(
                'label'    => __( 'Categories', 'woocommerce-exporter' ),
                'category' => __( 'Products', 'woocommerce-exporter' ),
            ),
            'order'          => array(
                'label'    => __( 'Orders', 'woocommerce-exporter' ),
                'category' => __( 'Orders', 'woocommerce-exporter' ),
            ),
            'customer'       => array(
                'label'    => __( 'Customers', 'woocommerce-exporter' ),
                'category' => __( 'Customers', 'woocommerce-exporter' ),
            ),
            'coupon'         => array(
                'label'    => __( 'Coupons', 'woocommerce-exporter' ),
                'category' => __( 'Marketing', 'woocommerce-exporter' ),
            ),
            'subscription'   => array(
                'label'    => __( 'Subscriptions', 'woocommerce-exporter' ),
                'category' => __( 'Subscriptions', 'woocommerce-exporter' ),
            ),
            'tag'            => array(
                'label'    => __( 'Tags', 'woocommerce-exporter' ),
                'category' => __( 'Products', 'woocommerce-exporter' ),
            ),
            'brand'          => array(
                'label'    => __( 'Brands', 'woocommerce-exporter' ),
                'category' => __( 'Products', 'woocommerce-exporter' ),
            ),
            'user'           => array(
                'label'    => __( 'Users', 'woocommerce-exporter' ),
                'category' => __( 'Users', 'woocommerce-exporter' ),
            ),
            'review'         => array(
                'label'    => __( 'Reviews', 'woocommerce-exporter' ),
                'category' => __( 'Products', 'woocommerce-exporter' ),
            ),
            'product_vendor' => array(
                'label'    => __( 'Product Vendors', 'woocommerce-exporter' ),
                'category' => __( 'Product Vendors', 'woocommerce-exporter' ),
            ),
            'commission'     => array(
                'label'    => __( 'Commissions', 'woocommerce-exporter' ),
                'category' => __( 'Product Vendors', 'woocommerce-exporter' ),
            ),
            'shipping_class' => array(
                'label'    => __( 'Shipping Class', 'woocommerce-exporter' ),
                'category' => __( 'Shipping Class', 'woocommerce-exporter' ),
            ),
            'ticket'         => array(
                'label'    => __( 'Tickets', 'woocommerce-exporter' ),
                'category' => __( 'Events', 'woocommerce-exporter' ),
            ),
            'booking'        => array(
                'label'    => __( 'Bookings', 'woocommerce-exporter' ),
                'category' => __( 'Bookings', 'woocommerce-exporter' ),
            ),
            'attribute'      => array(
                'label'    => __( 'Attributes', 'woocommerce-exporter' ),
                'category' => __( 'Products', 'woocommerce-exporter' ),
            ),
        );
    }
}
