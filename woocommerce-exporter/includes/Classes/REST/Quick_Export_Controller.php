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
use VisserLabs\WSE\Helpers\Export as Export_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Quick_Export_Controller.
 *
 * Provides REST endpoints consumed by the Quick Export Vue widget:
 *   GET /wse/v1/quick-export/config          – export types, formats, templates, nonce
 *   GET /wse/v1/quick-export/fields?type=X   – grouped field list for a given type
 *   GET /wse/v1/quick-export/filters?type=X  – filter / sort options for a given type
 *
 * @since 5.6.0
 */
class Quick_Export_Controller extends Abstract_Class {

    const REST_NAMESPACE = 'wse/v1';
    const REST_BASE      = 'quick-export';

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
     * a `selected` flag derived from the saved per-type option.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 5.6.0
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
        $groups       = $this->group_fields( $type, $fields, $saved_fields );

        return rest_ensure_response(
            array(
                'type'   => $type,
                'groups' => $groups,
            )
        );
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

        $sort = $this->get_type_sort_options( $type );
        return rest_ensure_response(
            array(
                'type'          => $type,
                'filters'       => $this->get_type_filters( $type ),
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
     * @since 2.8.1
     * @param array  $filters Filter definitions, appended by reference.
     * @param string $type    Export type slug.
     * @return void
     */
    private function maybe_add_custom_meta_filter( &$filters, $type ) {
        $is_product = 'product' === $type;
        if ( ! $is_product && 'order' !== $type ) {
            return;
        }
        $option_name = $is_product ? 'custom_products' : 'custom_orders';
        if ( empty( $this->get_registered_custom_meta_keys( $option_name ) ) ) {
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
     * Group a flat field list into logical sections using Field_Registry categories.
     *
     * Each returned group is: { label: string, fields: [{ name, label, selected, order }] }
     *
     * @param string   $type       Export type slug.
     * @param array    $fields     Flat array of [ 'name' => '', 'label' => '' ] items.
     * @param string[] $saved_keys Keys the user previously saved as enabled.
     *
     * @since 5.6.0
     * @return array
     */
    private function group_fields( $type, array $fields, array $saved_keys ) {
        $groups         = array();
        $order          = 1;
        $has_saved      = ! empty( $saved_keys );
        $default_keys   = $this->get_default_field_keys( $type );
        $default_lookup = array_flip( $default_keys );

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

            // Selection rule:
            // - If the user has previously saved a selection, honour it.
            // - Otherwise default to the curated "common" set for this export type.
            // - If we don't have a curated set for this type, fall back to the first
            // 10 fields so the export is usable without overwhelming the user.
            if ( $has_saved ) {
                $selected = in_array( $name, $saved_keys, true );
            } elseif ( ! empty( $default_keys ) ) {
                $selected = isset( $default_lookup[ $name ] );
            } else {
                $selected = $order <= 10;
            }

            $groups[ $group_label ]['fields'][] = array(
                'name'     => $name,
                'label'    => $label,
                'selected' => $selected,
                'order'    => $order++,
            );
        }

        return array_values( $groups );
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
     * @param string $type Export type slug.
     *
     * @since 5.6.0
     * @return array
     */
    private function get_type_filters( $type ) {
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
        // meta keys are registered.
        $this->maybe_add_custom_meta_filter( $filters, $type );

        foreach ( $filters as &$filter ) {
            $filter['enabled']     = false;
            $filter['description'] = $this->get_filter_description( $filter['key'] );
            $filter['icon']        = $this->get_filter_icon( $filter['key'] );
            $filter['sub_options'] = $this->get_filter_sub_options( $filter['key'] );
            if ( ! isset( $filter['group'] ) ) {
                $filter['group'] = '';
            }
        }
        unset( $filter );

        return $filters;
    }

    /**
     * Short helper text shown under each filter card title.
     *
     * @since 5.6.0
     * @param string $filter_key Filter key.
     * @return string
     */
    private function get_filter_description( $filter_key ) {
        $map = array(
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
        return isset( $map[ $filter_key ] ) ? $map[ $filter_key ] : '';
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
                $brand_taxonomies = array( 'product_brand', 'pwb-brand', 'pa_brand', 'yith_product_brand' );
                $options          = array();
                foreach ( $brand_taxonomies as $tax ) {
                    $options = $this->get_term_choices( $tax );
                    if ( ! empty( $options ) ) {
                        break;
                    }
                }
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
                'number'     => 200,
            )
        );
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return array();
        }
        $options = array();
        foreach ( $terms as $term ) {
            $options[] = array(
                'value' => (string) $term->term_id,
                'label' => (string) $term->name,
            );
        }
        return $options;
    }

    /**
     * Fetch brand choices for the Order Brand filter.
     *
     * Every brand taxonomy on the store is offered rather than just the first
     * one that happens to hold terms: the Order Brand filter matches the
     * submitted term IDs without pinning a taxonomy, so brands from two Brands
     * plugins can be mixed in one selection. Where more than one taxonomy
     * contributes, the taxonomy slug is appended so two plugins' brands of the
     * same name stay apart - the registered labels cannot do that job, every
     * Brands plugin calls its singular label "Brand".
     *
     * The Product Brand filter deliberately does not use this - woo_ce_get_products()
     * ( includes/product.php ) builds a tax_query pinned to the single taxonomy
     * woo_ce_brand_term_taxonomy resolves to, so offering IDs from the other
     * taxonomies there would return no products.
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
            $label     = $show_taxonomy ? $term->name . ' (' . $term->taxonomy . ')' : $term->name;
            $options[] = array(
                'value' => (string) $term->term_id,
                'label' => (string) $label,
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
     * @return array  { key: form_data_field_name, options: [ { value, label } ] }|null
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
                        'value' => 'product_name',
                        'label' => __( 'Product Name', 'woocommerce-exporter' ),
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
     * Curated "common" field set per export type. Used as the default selection
     * when the user has not yet saved a custom field selection — keeps the
     * initial export concise rather than dumping every column.
     *
     * @since 5.6.0
     * @param string $type Export type slug.
     * @return string[]
     */
    private function get_default_field_keys( $type ) {
        $defaults = array(
            'product'        => array( 'sku', 'name', 'regular_price', 'sale_price', 'stock_status', 'stock', 'type', 'tax_class', 'categories', 'tags' ),
            'order'          => array( 'order_id', 'order_status', 'order_date', 'order_total', 'billing_email', 'billing_first_name', 'billing_last_name', 'shipping_method', 'payment_method', 'customer_id' ),
            'customer'       => array( 'customer_id', 'billing_email', 'billing_first_name', 'billing_last_name', 'billing_company', 'billing_country', 'orders_count', 'total_spent', 'last_order_date', 'date_registered' ),
            'user'           => array( 'ID', 'user_login', 'user_email', 'first_name', 'last_name', 'roles', 'user_registered', 'billing_country', 'display_name', 'last_login' ),
            'coupon'         => array( 'coupon_code', 'discount_type', 'coupon_amount', 'usage_limit', 'usage_count', 'expiry_date', 'product_ids', 'minimum_amount', 'individual_use' ),
            'category'       => array( 'term_id', 'name', 'slug', 'parent', 'description', 'count', 'image', 'menu_order', 'display_type', 'thumbnail_id' ),
            'tag'            => array( 'term_id', 'name', 'slug', 'description', 'count' ),
            'brand'          => array( 'term_id', 'name', 'slug', 'description', 'count', 'thumbnail_id' ),
            'subscription'   => array( 'ID', 'order_status', 'order_date', 'order_total', 'billing_email', 'billing_first_name', 'billing_last_name', 'billing_period', 'billing_interval', 'next_payment' ),
            'review'         => array( 'comment_ID', 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_date', 'comment_content', 'comment_approved', 'rating' ),
            'attribute'      => array( 'attribute_id', 'attribute_label', 'attribute_name', 'attribute_type', 'attribute_orderby', 'attribute_public' ),
            'shipping_class' => array( 'term_id', 'name', 'slug', 'description', 'count' ),
        );

        return isset( $defaults[ $type ] ) ? $defaults[ $type ] : array();
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
