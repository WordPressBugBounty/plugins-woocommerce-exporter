<?php
/**
 * Export Fields REST Controller.
 *
 * @since   2.8.0
 * @package VisserLabs\WSE\Classes\REST
 */

namespace VisserLabs\WSE\Classes\REST;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Classes\Export\Field_Registry;
use VisserLabs\WSE\Helpers\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Export_Fields_Controller.
 *
 * Provides available export field definitions for a given export type.
 * Used by the Export Template form to populate the Add Export Fields picker.
 *
 * @since 2.8.0
 */
class Export_Fields_Controller extends Abstract_Class {

    const REST_NAMESPACE = 'wse/v1';
    const REST_BASE      = 'export-fields';

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
                'callback'            => array( $this, 'get_fields' ),
                'permission_callback' => array( $this, 'check_permission' ),
                'args'                => array(
                    'type' => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_key',
                    ),
                ),
            )
        );
    }

    /**
     * Return the list of available fields for an export type.
     *
     * Fields are sourced from the legacy woo_ce_get_{type}_fields() function so
     * that any third-party integrations extending that filter still appear in the
     * picker. User-saved visibility preferences are deliberately ignored here -
     * the picker must show the full catalogue, not the user's current selection.
     * Categories are resolved via Field_Registry for accuracy and consistency.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 2.8.0
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_fields( $request ) {
        $type = (string) $request->get_param( 'type' );

        // Load all export-type include files so cross-type helper functions
        // (e.g. woo_ce_detect_product_brands defined in brand.php) are available
        // when extend files fire via the woo_ce_{type}_fields filter.
        if ( function_exists( 'woo_ce_load_export_types' ) ) {
            woo_ce_load_export_types();
        }

        $function = 'woo_ce_get_' . $type . '_fields';

        if ( ! function_exists( $function ) ) {
            return new \WP_Error(
                'fields_not_found',
                /* translators: %s: export type slug */
                sprintf( __( 'No fields found for export type: %s.', 'woocommerce-exporter' ), $type ),
                array( 'status' => 404 )
            );
        }

        $raw = call_user_func( $function );

        if ( ! is_array( $raw ) ) {
            return rest_ensure_response(
                array(
                    'fields'     => array(),
                    'categories' => array(),
                )
            );
        }

        $predefined = Field_Registry::get_predefined( $type );

        $fields = array();
        foreach ( $raw as $field ) {
            if ( empty( $field['name'] ) ) {
                continue;
            }

            // The picker must expose the full field catalogue so users can discover and
            // select any available field. The legacy field functions may set hidden=1
            // based on the user's saved Quick Export preferences, but those preferences
            // are irrelevant here - we intentionally skip that filter.

            $fields[] = array(
                'name'       => $field['name'],
                'label'      => isset( $field['label'] ) ? (string) $field['label'] : $field['name'],
                'dataType'   => $this->infer_data_type( $field['name'] ),
                'category'   => Field_Registry::get_category( $type, $field['name'] ),
                'predefined' => in_array( $field['name'], $predefined, true ),
            );
        }

        $categories = array_values( array_unique( array_column( $fields, 'category' ) ) );

        // `label` is the saved header, so it stays in the site language. The
        // display labels follow the user's language; switch locale once, not per field.
        $data_types = array_values( array_unique( array_column( $fields, 'dataType' ) ) );

        list( $category_labels, $data_type_labels ) = Helper::in_user_locale(
            function () use ( $categories, $data_types ) {
                $category_labels = array();
                foreach ( $categories as $category ) {
                    $category_labels[ $category ] = Field_Registry::get_category_label( $category );
                }
                $data_type_labels = array();
                foreach ( $data_types as $data_type ) {
                    $data_type_labels[ $data_type ] = $this->get_data_type_label( $data_type );
                }
                return array( $category_labels, $data_type_labels );
            }
        );

        foreach ( $fields as &$field_row ) {
            $field_row['dataTypeLabel'] = $data_type_labels[ $field_row['dataType'] ];
            $field_row['categoryLabel'] = $category_labels[ $field_row['category'] ];
        }
        unset( $field_row );

        return rest_ensure_response(
            array(
                'fields'     => $fields,
                'categories' => $categories,
            )
        );
    }

    /**
     * Translated display label for a data type returned by infer_data_type().
     *
     * The English value stays the stable key; this is only what gets rendered.
     *
     * @param string $data_type 'Number' or 'Text'.
     *
     * @since 2.8.3
     * @return string
     */
    private function get_data_type_label( string $data_type ): string {
        return 'Number' === $data_type
            ? __( 'Number', 'woocommerce-exporter' )
            : __( 'Text', 'woocommerce-exporter' );
    }

    /**
     * Infer a display data type from a field name.
     *
     * @param string $name Field name/slug.
     *
     * @since 2.8.0
     * @return string 'Number' or 'Text'.
     */
    private function infer_data_type( string $name ): string {
        $numeric_patterns = array(
            '_id',
            'price',
            'total',
            'count',
            'quantity',
            'qty',
            'stock',
            'amount',
            'weight',
            'width',
            'height',
            'length',
            'number',
            'rating',
            'downloads',
            'sold',
            'limit',
            'index',
            'percent',
            'tax_rate',
            'discount',
            'refund',
        );
        foreach ( $numeric_patterns as $pattern ) {
            if ( false !== strpos( $name, $pattern ) ) {
                return 'Number';
            }
        }
        return 'Text';
    }

    /**
     * Permission callback - requires view_woocommerce_reports (or filtered capability).
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
}
