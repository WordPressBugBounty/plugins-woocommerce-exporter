<?php
/**
 * WordPress Abilities API integration.
 *
 * @since   2.8.3
 * @package VisserLabs\WSE\Classes
 */

namespace VisserLabs\WSE\Classes;

use VisserLabs\WSE\Abstracts\Abstract_Class;

defined( 'ABSPATH' ) || exit;

/**
 * Abilities
 *
 * Registers the Store Exporter abilities with the WordPress Abilities API, so
 * MCP clients can discover the export types and fields and manage the custom
 * meta fields.
 *
 * Every ability dispatches an internal request to the matching wse/v1 REST
 * route. The ability and the admin SPA therefore run the same validation,
 * sanitization and hooks. The abilities give metadata only, never export row
 * data.
 *
 * @since 2.8.3
 */
class Abilities extends Abstract_Class {

    const CATEGORY = 'visser-labs';

    const REST_NAMESPACE = '/wse/v1';

    /**
     * Export type keys that list-export-types returns. The SPA-only keys
     * (export_url, upsell_url) are left out.
     *
     * @var string[]
     */
    const EXPORT_TYPE_KEYS = array( 'slug', 'label', 'category', 'description', 'records', 'last_export', 'premium' );

    /**
     * Register hooks.
     *
     * @since 2.8.3
     * @return void
     */
    public function run() {
        if ( ! function_exists( 'wp_register_ability' ) || ( defined( 'WSE_DISABLE_ABILITIES' ) && WSE_DISABLE_ABILITIES ) ) {
            return;
        }

        add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
        add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
    }

    /**
     * Whether a site has turned off the abilities through the filter.
     *
     * Read when the Abilities API initializes, so a theme can also use the filter.
     *
     * @since 2.8.3
     * @return bool
     */
    public function is_disabled() {
        /**
         * Filters whether to skip the Store Exporter abilities.
         *
         * @since 2.8.3
         *
         * @param bool $disabled True to skip registration. Default false.
         */
        return (bool) apply_filters( 'wse_disable_abilities', false );
    }

    /**
     * Register the shared Visser Labs ability category.
     *
     * @since 2.8.3
     * @return void
     */
    public function register_category() {
        if ( $this->is_disabled() || wp_has_ability_category( self::CATEGORY ) ) {
            return;
        }

        wp_register_ability_category(
            self::CATEGORY,
            array(
                'label'       => __( 'Visser Labs', 'woocommerce-exporter' ),
                'description' => __( 'Abilities for the Visser Labs plugin family.', 'woocommerce-exporter' ),
            )
        );
    }

    /**
     * Register all abilities.
     *
     * @since 2.8.3
     * @return void
     */
    public function register_abilities() {
        if ( $this->is_disabled() ) {
            return;
        }

        $annotations = array(
            'read'             => array(
                'readonly'    => true,
                'destructive' => false,
                'idempotent'  => true,
            ),
            'idempotent_write' => array(
                'readonly'    => false,
                'destructive' => true,
                'idempotent'  => true,
            ),
        );

        foreach ( $this->get_ability_definitions() as $name => $definition ) {
            // The namespace is shared, so skip a name that another Visser Labs plugin owns.
            if ( wp_has_ability( self::CATEGORY . '/' . $name ) ) {
                continue;
            }

            wp_register_ability(
                self::CATEGORY . '/' . $name,
                array(
                    'label'               => $definition['label'],
                    'description'         => $definition['description'],
                    'category'            => self::CATEGORY,
                    'input_schema'        => $definition['input_schema'],
                    'execute_callback'    => array( $this, $definition['callback'] ),
                    'permission_callback' => array( $this, 'can_export' ),
                    'meta'                => array(
                        'annotations'  => $annotations[ $definition['flags'] ],
                        'show_in_rest' => true,
                    ),
                )
            );
        }
    }

    /**
     * Permission callback for every ability. Same check as every wse/v1 REST route.
     *
     * @since 2.8.3
     * @return bool
     */
    public function can_export() {
        return current_user_can( apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' ) );
    }

    /**
     * Full names of every ability this class registers.
     *
     * The visser-labs namespace is shared by every Visser Labs plugin, so a caller
     * that must act on Store Exporter abilities only uses this list, not a
     * namespace prefix match.
     *
     * @since 2.8.3
     * @return string[]
     */
    public function get_ability_names() {
        return array_map(
            function ( $name ) {
                return self::CATEGORY . '/' . $name;
            },
            array_keys( $this->get_ability_definitions() )
        );
    }

    /**
     * Ability definitions, keyed by the ability name without the namespace.
     *
     * @since 2.8.3
     * @return array
     */
    private function get_ability_definitions() {
        $type_input = $this->object_schema(
            array(
                'type' => array(
                    'type'        => 'string',
                    'description' => __( 'Export type slug, for example order or product. Call visser-labs/list-export-types for the valid values.', 'woocommerce-exporter' ),
                ),
            ),
            array( 'type' )
        );

        return array(
            // Discovery.
            'list-export-types'  => array(
                'label'        => __( 'List export types', 'woocommerce-exporter' ),
                'description'  => __( 'Lists the export types (orders, products, users and others) with their record counts and last export date. A type with premium set to true needs Store Exporter Deluxe.', 'woocommerce-exporter' ),
                'flags'        => 'read',
                'input_schema' => $this->object_schema(),
                'callback'     => 'list_export_types',
            ),
            'get-export-fields'  => array(
                'label'        => __( 'Get export fields', 'woocommerce-exporter' ),
                'description'  => __( 'Gives the fields that one export type can export.', 'woocommerce-exporter' ),
                'flags'        => 'read',
                'input_schema' => $type_input,
                'callback'     => 'get_export_fields',
            ),
            'get-export-filters' => array(
                'label'        => __( 'Get export filters', 'woocommerce-exporter' ),
                'description'  => __( 'Gives the filter and sort options that the Quick Export screen shows for one export type. Use it to look up valid values, for example order statuses and countries.', 'woocommerce-exporter' ),
                'flags'        => 'read',
                'input_schema' => $type_input,
                'callback'     => 'get_export_filters',
            ),

            // Export configuration.
            'get-custom-fields'  => array(
                'label'        => __( 'Get custom meta fields', 'woocommerce-exporter' ),
                'description'  => __( 'Gives the custom meta keys that are added to the exports. Send type for one export type, or nothing for all types.', 'woocommerce-exporter' ),
                'flags'        => 'read',
                'input_schema' => $this->object_schema(
                    array(
                        'type' => array(
                            'type'        => 'string',
                            'description' => __( 'Custom meta type, for example order or product.', 'woocommerce-exporter' ),
                        ),
                    )
                ),
                'callback'     => 'get_custom_fields',
            ),
            'set-custom-fields'  => array(
                'label'        => __( 'Set custom meta fields', 'woocommerce-exporter' ),
                'description'  => __( 'Replaces the full meta key list of each named definition, so send the existing keys to keep them. An availability flag you omit keeps its saved value. Each definition name must be one that visser-labs/get-custom-fields returns.', 'woocommerce-exporter' ),
                'flags'        => 'idempotent_write',
                'input_schema' => $this->object_schema(
                    array(
                        'type'              => array( 'type' => 'string' ),
                        'field_definitions' => array(
                            'type'  => 'array',
                            'items' => array(
                                'type'       => 'object',
                                'properties' => array(
                                    'name'         => array( 'type' => 'string' ),
                                    'meta_keys'    => array(
                                        'type'  => 'array',
                                        'items' => array( 'type' => 'string' ),
                                    ),
                                    'availability' => array(
                                        'type'       => 'object',
                                        'properties' => array(
                                            'quick_export' => array( 'type' => 'boolean' ),
                                            'scheduled_export' => array( 'type' => 'boolean' ),
                                            'export_template' => array( 'type' => 'boolean' ),
                                        ),
                                    ),
                                ),
                                'required'   => array( 'name', 'meta_keys' ),
                            ),
                        ),
                    ),
                    array( 'type', 'field_definitions' )
                ),
                'callback'     => 'set_custom_fields',
            ),
        );
    }

    /***********************************************************************
     * Execute callbacks
     **********************************************************************/

    /**
     * List the export types.
     *
     * @since 2.8.3
     * @return array|\WP_Error
     */
    public function list_export_types() {
        $data = $this->dispatch( 'GET', '/export-items' );

        if ( is_wp_error( $data ) ) {
            return $data;
        }

        return array(
            'types' => array_map(
                function ( $item ) {
                    return array_intersect_key( $item, array_flip( self::EXPORT_TYPE_KEYS ) );
                },
                $data['items']
            ),
        );
    }

    /**
     * Get the fields of one export type.
     *
     * @param array $input Ability input.
     *
     * @since 2.8.3
     * @return array|\WP_Error
     */
    public function get_export_fields( $input ) {
        $valid = $this->validate_export_type( $input['type'] );

        return is_wp_error( $valid ) ? $valid : $this->dispatch( 'GET', '/export-fields', array( 'type' => $input['type'] ) );
    }

    /**
     * Get the filter and sort options of one export type.
     *
     * @param array $input Ability input.
     *
     * @since 2.8.3
     * @return array|\WP_Error
     */
    public function get_export_filters( $input ) {
        $valid = $this->validate_export_type( $input['type'] );

        return is_wp_error( $valid ) ? $valid : $this->dispatch( 'GET', '/quick-export/filters', array( 'type' => $input['type'] ) );
    }

    /**
     * Get the custom meta keys of one type, or of every type.
     *
     * @param array $input Ability input.
     *
     * @since 2.8.3
     * @return array|\WP_Error
     */
    public function get_custom_fields( $input ) {
        if ( ! empty( $input['type'] ) ) {
            $valid = $this->validate_custom_meta_type( $input['type'] );

            return is_wp_error( $valid ) ? $valid : $this->dispatch( 'GET', '/custom-meta-fields/' . $input['type'] );
        }

        $list = $this->dispatch( 'GET', '/custom-meta-fields' );
        if ( is_wp_error( $list ) ) {
            return $list;
        }

        $types = array();
        foreach ( $list['types'] as $type ) {
            $data = $this->dispatch( 'GET', '/custom-meta-fields/' . $type['type'] );
            if ( is_wp_error( $data ) ) {
                return $data;
            }
            $types[] = $data;
        }

        return array( 'types' => $types );
    }

    /**
     * Set the custom meta keys of one type.
     *
     * @param array $input Ability input.
     *
     * @since 2.8.3
     * @return array|\WP_Error
     */
    public function set_custom_fields( $input ) {
        $valid = $this->validate_custom_meta_type( $input['type'] );
        if ( is_wp_error( $valid ) ) {
            return $valid;
        }

        $result = $this->dispatch(
            'PUT',
            '/custom-meta-fields/' . $input['type'],
            array( 'field_definitions' => $input['field_definitions'] )
        );

        if ( ! is_wp_error( $result ) && ! empty( $result['ignored'] ) ) {
            return new \WP_Error(
                'wse_unknown_definition',
                /* translators: %s: comma-separated custom meta definition names. */
                sprintf( __( 'Unknown definitions were not saved: %s. Call visser-labs/get-custom-fields for valid names. Valid definitions in the same call were saved.', 'woocommerce-exporter' ), implode( ', ', (array) $result['ignored'] ) ),
                array(
                    'status'  => 400,
                    'saved'   => $result['field_definitions'],
                    'ignored' => (array) $result['ignored'],
                )
            );
        }

        return $result;
    }

    /***********************************************************************
     * Helpers
     **********************************************************************/

    /**
     * Dispatch an internal request to a wse/v1 REST route.
     *
     * The route runs its own permission check, argument validation and hooks, the
     * same as a request from the admin SPA.
     *
     * @param string $method HTTP method.
     * @param string $route  Route below the wse/v1 namespace, with a leading slash.
     * @param array  $params Query params for GET, JSON body params for other methods.
     *
     * @since 2.8.3
     * @return array|\WP_Error Response data, or the route's error.
     */
    private function dispatch( $method, $route, array $params = array() ) {
        $request = new \WP_REST_Request( $method, self::REST_NAMESPACE . $route );

        if ( 'GET' === $method ) {
            $request->set_query_params( $params );
        } else {
            $request->set_header( 'Content-Type', 'application/json' );
            $request->set_body( wp_json_encode( $params ) );
        }

        $response = rest_do_request( $request );

        return $response->is_error() ? $response->as_error() : $response->get_data();
    }

    /**
     * Build an object input schema.
     *
     * The default of an empty object lets a client call the ability with no input.
     *
     * @param array $properties Schema properties.
     * @param array $required   Required property names.
     *
     * @since 2.8.3
     * @return array
     */
    private function object_schema( array $properties = array(), array $required = array() ) {
        $schema = array(
            'type'                 => 'object',
            'additionalProperties' => false,
            'default'              => array(),
        );

        if ( ! empty( $properties ) ) {
            $schema['properties'] = $properties;
        }

        if ( ! empty( $required ) ) {
            $schema['required'] = $required;
        }

        return $schema;
    }

    /**
     * Make sure an export type exists in the free plugin.
     *
     * The premium types need Store Exporter Deluxe, so they are refused with a
     * clear message instead of an empty field list.
     *
     * @param string $type Export type slug.
     *
     * @since 2.8.3
     * @return true|\WP_Error
     */
    private function validate_export_type( $type ) {
        if ( ! array_key_exists( $type, woo_ce_get_export_types() ) ) {
            return new \WP_Error(
                'wse_invalid_export_type',
                /* translators: %s: export type slug. */
                sprintf( __( 'Unknown export type: %s. Call visser-labs/list-export-types for the valid values.', 'woocommerce-exporter' ), $type ),
                array( 'status' => 400 )
            );
        }

        if ( woo_ce_is_premium_export_type( $type ) ) {
            return new \WP_Error(
                'wse_premium_export_type',
                /* translators: %s: export type slug. */
                sprintf( __( 'The %s export type needs Store Exporter Deluxe.', 'woocommerce-exporter' ), $type ),
                array( 'status' => 400 )
            );
        }

        return true;
    }

    /**
     * Check that a type has custom meta fields.
     *
     * @param string $type Custom meta type slug.
     *
     * @since 2.8.3
     * @return true|\WP_Error
     */
    private function validate_custom_meta_type( $type ) {
        $list = $this->dispatch( 'GET', '/custom-meta-fields' );
        if ( is_wp_error( $list ) ) {
            return $list;
        }

        if ( ! in_array( $type, array_column( $list['types'], 'type' ), true ) ) {
            return new \WP_Error(
                'wse_invalid_custom_meta_type',
                /* translators: %s: custom meta type slug. */
                sprintf( __( 'Unknown custom meta type: %s. Call visser-labs/get-custom-fields without a type for the valid values.', 'woocommerce-exporter' ), $type ),
                array( 'status' => 400 )
            );
        }

        return true;
    }
}
