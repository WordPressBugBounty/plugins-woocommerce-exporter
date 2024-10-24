<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( is_admin() ) {

    /* Start of: WordPress Administration */

    if ( ! function_exists( 'woo_ce_get_export_type_order_count' ) ) {
        /**
         * Get the number of orders that are included in the export.
         *
         * @return int The number of orders that are included in the export.
         */
        function woo_ce_get_export_type_order_count() {

            $count     = 0;
            $post_type = 'shop_order';

            $woocommerce_version = woo_get_woo_version();
            // Check if this is a WooCommerce 2.2+ instance (new Post Status).
            if ( version_compare( $woocommerce_version, '2.2' ) >= 0 ) {
                $post_status = ( function_exists( 'wc_get_order_statuses' ) ? apply_filters( 'woo_ce_order_post_status', array_keys( wc_get_order_statuses() ) ) : 'any' );
            } else {
                $post_status = apply_filters( 'woo_ce_order_post_status', woo_ce_post_statuses() );
            }

            // Override for WordPress MultiSite.
            if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && woo_ce_is_network_admin() ) {
                $sites = get_sites();
                foreach ( $sites as $site ) {
                    switch_to_blog( $site->blog_id );
                    $args        = array(
                        'post_type'      => $post_type,
                        'posts_per_page' => 1,
                        'post_status'    => $post_status,
                        'fields'         => 'ids',
                    );
                    $count_query = new WP_Query( $args );
                    $count      += $count_query->found_posts;
                    restore_current_blog();
                }
                return $count;
            }

            // Check if the existing Transient exists.
            $cached = get_transient( WOO_CE_PREFIX . '_order_count' );
            if ( false === $cached ) {
                $args        = array(
                    'post_type'      => $post_type,
                    'posts_per_page' => 1,
                    'post_status'    => $post_status,
                    'fields'         => 'ids',
                );
                $count_query = new WP_Query( $args );
                $count       = $count_query->found_posts;
                set_transient( WOO_CE_PREFIX . '_order_count', $count, HOUR_IN_SECONDS );
            } else {
                $count = $cached;
            }
            return $count;
        }
    }

    /**
     * Save the scheduled export.
     *
     * @param int $post_ID The post ID.
     * @return void
     */
    function woo_ce_order_scheduled_export_save( $post_ID = 0 ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        // Order Date.
        $auto_order_date                 = sanitize_text_field( $_POST['order_dates_filter'] );
        $auto_order_dates_from           = false;
        $auto_order_dates_to             = false;
        $auto_order_date_variable        = false;
        $auto_order_date_variable_length = false;
        if ( 'variable' === $auto_order_date ) {
            $auto_order_date_variable        = sanitize_text_field( $_POST['order_dates_filter_variable'] );
            $auto_order_date_variable_length = sanitize_text_field( $_POST['order_dates_filter_variable_length'] );
        } elseif ( 'manual' === $auto_order_date ) {
            $auto_order_dates_from = sanitize_text_field( $_POST['order_dates_from'] );
            $auto_order_dates_to   = sanitize_text_field( $_POST['order_dates_to'] );
        }
        update_post_meta( $post_ID, '_filter_order_date', $auto_order_date );
        update_post_meta( $post_ID, '_filter_order_dates_from', $auto_order_dates_from );
        update_post_meta( $post_ID, '_filter_order_dates_to', $auto_order_dates_to );
        update_post_meta( $post_ID, '_filter_order_date_variable', $auto_order_date_variable );
        update_post_meta( $post_ID, '_filter_order_date_variable_length', $auto_order_date_variable_length );

        // Order Modified Date.
        $auto_order_modified_date                 = sanitize_text_field( $_POST['order_modified_dates_filter'] );
        $auto_order_modified_dates_from           = false;
        $auto_order_modified_dates_to             = false;
        $auto_order_modified_date_variable        = false;
        $auto_order_modified_date_variable_length = false;
        if ( 'variable' === $auto_order_modified_date ) {
            $auto_order_modified_date_variable        = sanitize_text_field( $_POST['order_modified_dates_filter_variable'] );
            $auto_order_modified_date_variable_length = sanitize_text_field( $_POST['order_modified_dates_filter_variable_length'] );
        } elseif ( 'manual' === $auto_order_modified_date ) {
            $auto_order_modified_dates_from = sanitize_text_field( $_POST['order_modified_dates_from'] );
            $auto_order_modified_dates_to   = sanitize_text_field( $_POST['order_modified_dates_to'] );
        }
        update_post_meta( $post_ID, '_filter_order_modified_date', $auto_order_modified_date );
        update_post_meta( $post_ID, '_filter_order_modified_dates_from', $auto_order_modified_dates_from );
        update_post_meta( $post_ID, '_filter_order_modified_dates_to', $auto_order_modified_dates_to );
        update_post_meta( $post_ID, '_filter_order_modified_date_variable', $auto_order_modified_date_variable );
        update_post_meta( $post_ID, '_filter_order_modified_date_variable_length', $auto_order_modified_date_variable_length );

        update_post_meta( $post_ID, '_filter_order_orderby', ( isset( $_POST['order_filter_orderby'] ) ? sanitize_text_field( $_POST['order_filter_orderby'] ) : false ) );
        update_post_meta( $post_ID, '_filter_order_items', ( isset( $_POST['order_items_filter'] ) ? sanitize_text_field( $_POST['order_items_filter'] ) : false ) );
        update_post_meta( $post_ID, '_filter_order_max_order_items', ( isset( $_POST['order_max_order_items'] ) ? sanitize_text_field( $_POST['order_max_order_items'] ) : 10 ) );
        update_post_meta( $post_ID, '_filter_order_flag_notes', ( isset( $_POST['order_flag_notes'] ) ? sanitize_text_field( $_POST['order_flag_notes'] ) : false ) );
        update_post_meta( $post_ID, '_filter_order_items_digital', ( isset( $_POST['order_items_digital_filter'] ) ? sanitize_text_field( $_POST['order_items_digital_filter'] ) : false ) );
        update_post_meta( $post_ID, '_filter_order_status', ( isset( $_POST['order_filter_status'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', (array) $_POST['order_filter_status'] ) ) : false ) );
        update_post_meta( $post_ID, '_filter_order_item_types', ( isset( $_POST['order_filter_order_items_types'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', (array) $_POST['order_filter_order_items_types'] ) ) : false ) );
        $auto_order_product = ( isset( $_POST['order_filter_product'] ) ? $_POST['order_filter_product'] : false );
        // Select2 passes us a string whereas Chosen gives us an array.
        if ( is_array( $auto_order_product ) && count( $auto_order_product ) === 1 ) {
            $auto_order_product = explode( ',', $auto_order_product[0] );
        }
        update_post_meta( $post_ID, '_filter_order_product', ( ! empty( $auto_order_product ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $auto_order_product ) ) : false ) );
        update_post_meta( $post_ID, '_filter_order_product_exclude', ( isset( $_POST['order_filter_product_exclude'] ) ? absint( $_POST['order_filter_product_exclude'] ) : false ) );

        $user_count      = woo_ce_get_export_type_count( 'user' );
        $user_list_limit = apply_filters( 'woo_ce_order_filter_customer_list_limit', 100, $user_count );

        if ( $user_count < $user_list_limit ) {
            update_post_meta( $post_ID, '_filter_order_customer', ( isset( $_POST['order_filter_customer'] ) ? array_map( 'absint', (array) $_POST['order_filter_customer'] ) : false ) );
        } else {
            update_post_meta( $post_ID, '_filter_order_customer', ( isset( $_POST['order_filter_customer'] ) ? sanitize_text_field( $_POST['order_filter_customer'] ) : false ) );
        }

        update_post_meta( $post_ID, '_filter_order_billing_country', ( isset( $_POST['order_filter_billing_country'] ) ? array_map( 'sanitize_text_field', (array) $_POST['order_filter_billing_country'] ) : false ) );
        update_post_meta( $post_ID, '_filter_order_shipping_country', ( isset( $_POST['order_filter_shipping_country'] ) ? array_map( 'sanitize_text_field', (array) $_POST['order_filter_shipping_country'] ) : false ) );
        update_post_meta( $post_ID, '_filter_order_category', ( isset( $_POST['order_filter_category'] ) ? array_map( 'absint', (array) $_POST['order_filter_category'] ) : false ) );
        update_post_meta( $post_ID, '_filter_order_tag', ( isset( $_POST['order_filter_tag'] ) ? array_map( 'absint', (array) $_POST['order_filter_tag'] ) : false ) );
        update_post_meta( $post_ID, '_filter_order_user_role', ( isset( $_POST['order_filter_user_role'] ) ? array_map( 'sanitize_text_field', (array) $_POST['order_filter_user_role'] ) : false ) );

        $coupon_count      = woo_ce_get_export_type_count( 'coupon' );
        $coupon_list_limit = apply_filters( 'woo_ce_order_filter_coupon_list_limit', 100, $coupon_count );

        if ( $coupon_count < $coupon_list_limit ) {
            update_post_meta( $post_ID, '_filter_order_coupon', ( isset( $_POST['order_filter_coupon'] ) ? array_map( 'absint', (array) $_POST['order_filter_coupon'] ) : false ) );
        } else {
            update_post_meta( $post_ID, '_filter_order_coupon', ( isset( $_POST['order_filter_coupon'] ) ? sanitize_text_field( $_POST['order_filter_coupon'] ) : false ) );
        }

        update_post_meta( $post_ID, '_filter_order_payment', ( isset( $_POST['order_filter_payment'] ) ? array_map( 'sanitize_text_field', (array) $_POST['order_filter_payment'] ) : false ) );
        update_post_meta( $post_ID, '_filter_order_shipping', ( isset( $_POST['order_filter_shipping'] ) ? array_map( 'sanitize_text_field', (array) $_POST['order_filter_shipping'] ) : false ) );
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    add_action( 'woo_ce_extend_scheduled_export_save', 'woo_ce_order_scheduled_export_save' );

    /**
     * Set the arguments for the dataset
     *
     * @param array  $args        The arguments for the dataset.
     * @param string $export_type The export type.
     * @return array The arguments for the dataset.
     */
    function woo_ce_order_dataset_args( $args, $export_type = '', $data = array() ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        // Check if we're dealing with the Order Export Type.
        if ( 'order' !== $export_type ) {
            return $args;
        }

        // Check the state of Filter tick boxes.
        if ( ! isset( $data['order_filter_status_include'] ) ) {
            unset( $data['order_filter_status'] );
        }
        // order_dates_filter.
        if ( ! isset( $data['order_filter_billing_country_include'] ) ) {
            unset( $data['order_filter_billing_country'] );
        }
        if ( ! isset( $data['order_filter_shipping_country_include'] ) ) {
            unset( $data['order_filter_shipping_country'] );
        }
        if ( ! isset( $data['order_filter_user_role_include'] ) ) {
            unset( $data['order_filter_user_role'] );
        }
        if ( ! isset( $data['order_filter_coupon_include'] ) ) {
            unset( $data['order_filter_coupon'] );
        }
        if ( ! isset( $data['order_filter_product_include'] ) ) {
            unset( $data['order_filter_product'], $data['order_filter_product_exclude'] );
        }
        if ( ! isset( $data['order_filter_category_include'] ) ) {
            unset( $data['order_filter_category'] );
        }
        if ( ! isset( $data['order_filter_tag_include'] ) ) {
            unset( $data['order_filter_tag'] );
        }

        // Merge in the form data for this dataset.
        $defaults = array(
            'order_status'                                => ( isset( $data['order_filter_status'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', (array) $data['order_filter_status'] ) ) : false ),
            'order_dates_filter'                          => ( isset( $data['order_dates_filter'] ) ? sanitize_text_field( $data['order_dates_filter'] ) : false ),
            'order_dates_from'                            => ( isset( $data['order_dates_from'] ) ? woo_ce_format_order_date( sanitize_text_field( $data['order_dates_from'] ) ) : '' ),
            'order_dates_to'                              => ( isset( $data['order_dates_to'] ) ? woo_ce_format_order_date( sanitize_text_field( $data['order_dates_to'] ) ) : '' ),
            'order_dates_filter_variable'                 => ( isset( $data['order_dates_filter_variable'] ) ? absint( $data['order_dates_filter_variable'] ) : false ),
            'order_dates_filter_variable_length'          => ( isset( $data['order_dates_filter_variable_length'] ) ? sanitize_text_field( $data['order_dates_filter_variable_length'] ) : false ),
            'order_modified_dates_filter'                 => ( isset( $data['order_modified_dates_filter'] ) ? sanitize_text_field( $data['order_modified_dates_filter'] ) : false ),
            'order_modified_dates_from'                   => ( isset( $data['order_modified_dates_from'] ) ? woo_ce_format_order_date( sanitize_text_field( $data['order_modified_dates_from'] ) ) : '' ),
            'order_modified_dates_to'                     => ( isset( $data['order_modified_dates_to'] ) ? woo_ce_format_order_date( sanitize_text_field( $data['order_modified_dates_to'] ) ) : '' ),
            'order_modified_dates_filter_variable'        => ( isset( $data['order_modified_dates_filter_variable'] ) ? absint( $data['order_modified_dates_filter_variable'] ) : false ),
            'order_modified_dates_filter_variable_length' => ( isset( $data['order_modified_dates_filter_variable_length'] ) ? sanitize_text_field( $data['order_modified_dates_filter_variable_length'] ) : false ),
            'order_billing_country'                       => ( isset( $data['order_filter_billing_country'] ) ? array_map( 'sanitize_text_field', (array) $data['order_filter_billing_country'] ) : false ),
            'order_shipping_country'                      => ( isset( $data['order_filter_shipping_country'] ) ? array_map( 'sanitize_text_field', (array) $data['order_filter_shipping_country'] ) : false ),
            'order_user_roles'                            => ( isset( $data['order_filter_user_role'] ) ? woo_ce_format_user_role_filters( array_map( 'sanitize_text_field', (array) $data['order_filter_user_role'] ) ) : false ),
            'order_coupon'                                => ( isset( $data['order_filter_coupon'] ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $data['order_filter_coupon'] ) ) : false ),
            'order_product'                               => ( isset( $data['order_filter_product'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', (array) $data['order_filter_product'] ) ) : false ),
            'order_product_exclude'                       => ( isset( $data['order_filter_product_exclude'] ) ? absint( $data['order_filter_product_exclude'] ) : false ),
            'order_product'                               => ( isset( $data['order_filter_product'] ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $data['order_filter_product'] ) ) : false ), // phpcs:ignore Universal.Arrays.DuplicateArrayKey.Found
            'order_customer'                              => ( isset( $data['order_filter_customer'] ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $data['order_filter_customer'] ) ) : false ),
            'order_category'                              => ( isset( $data['order_filter_category'] ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $data['order_filter_category'] ) ) : false ),
            'order_tag'                                   => ( isset( $data['order_filter_tag'] ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $data['order_filter_tag'] ) ) : false ),
            'order_ids'                                   => ( isset( $data['order_filter_id'] ) ? sanitize_text_field( $data['order_filter_id'] ) : false ),
            'order_payment'                               => ( isset( $data['order_filter_payment_gateway'] ) ? array_map( 'sanitize_text_field', (array) $data['order_filter_payment_gateway'] ) : false ),
            'order_shipping'                              => ( isset( $data['order_filter_shipping_method'] ) ? array_map( 'sanitize_text_field', (array) $data['order_filter_shipping_method'] ) : false ),
            'order_items_digital'                         => ( isset( $data['order_filter_digital_products'] ) ? sanitize_text_field( $data['order_filter_digital_products'] ) : false ),
            'order_items'                                 => ( isset( $data['order_items'] ) ? sanitize_text_field( $data['order_items'] ) : false ),
            'order_items_types'                           => ( isset( $data['order_items_types'] ) ? array_map( 'sanitize_text_field', (array) $data['order_items_types'] ) : false ),
            'order_flag_notes'                            => ( isset( $data['order_flag_notes'] ) ? absint( $data['order_flag_notes'] ) : false ),
            'max_order_items'                             => ( isset( $data['max_order_items'] ) ? absint( $data['max_order_items'] ) : 10 ),
            'order_orderby'                               => ( isset( $data['order_orderby'] ) ? sanitize_text_field( $data['order_orderby'] ) : 'DATE' ),
            'order_order'                                 => ( isset( $data['order_order'] ) ? sanitize_text_field( $data['order_order'] ) : 'DESC' ),
            'product_image_formatting'                    => woo_ce_get_option( 'product_image_formatting', 1 ),
            'gallery_formatting'                          => woo_ce_get_option( 'gallery_formatting', 1 ),
        );

        $args = wp_parse_args( $args, $defaults );

        // Default empty values.
        if ( empty( $args['max_order_items'] ) ) {
            $args['max_order_items'] = 10;
        }

        // Save dataset export specific options.
        if ( woo_ce_get_option( 'order_status' ) !== $args['order_status'] ) {
            woo_ce_update_option( 'order_status', $args['order_status'] );
        }

        // Order Date.
        if ( woo_ce_get_option( 'order_dates_filter' ) !== $args['order_dates_filter'] ) {
            woo_ce_update_option( 'order_dates_filter', $args['order_dates_filter'] );
        }
        if ( woo_ce_get_option( 'order_dates_from' ) !== $args['order_dates_from'] ) {
            woo_ce_update_option( 'order_dates_from', woo_ce_format_order_date( $args['order_dates_from'], 'save' ) );
        }
        if ( woo_ce_get_option( 'order_dates_to' ) !== $args['order_dates_to'] ) {
            woo_ce_update_option( 'order_dates_to', woo_ce_format_order_date( $args['order_dates_to'], 'save' ) );
        }
        if ( woo_ce_get_option( 'order_dates_filter_variable' ) !== $args['order_dates_filter_variable'] ) {
            woo_ce_update_option( 'order_dates_filter_variable', $args['order_dates_filter_variable'] );
        }
        if ( woo_ce_get_option( 'order_dates_filter_variable_length' ) !== $args['order_dates_filter_variable_length'] ) {
            woo_ce_update_option( 'order_dates_filter_variable_length', $args['order_dates_filter_variable_length'] );
        }

        // Order Modified Date.
        if ( woo_ce_get_option( 'order_modified_dates_filter' ) !== $args['order_modified_dates_filter'] ) {
            woo_ce_update_option( 'order_modified_dates_filter', $args['order_modified_dates_filter'] );
        }
        if ( woo_ce_get_option( 'order_modified_dates_from' ) !== $args['order_modified_dates_from'] ) {
            woo_ce_update_option( 'order_modified_dates_from', woo_ce_format_order_date( $args['order_modified_dates_from'], 'save' ) );
        }
        if ( woo_ce_get_option( 'order_modified_dates_to' ) !== $args['order_modified_dates_to'] ) {
            woo_ce_update_option( 'order_modified_dates_to', woo_ce_format_order_date( $args['order_modified_dates_to'], 'save' ) );
        }
        if ( woo_ce_get_option( 'order_modified_dates_filter_variable' ) !== $args['order_modified_dates_filter_variable'] ) {
            woo_ce_update_option( 'order_modified_dates_filter_variable', $args['order_modified_dates_filter_variable'] );
        }
        if ( woo_ce_get_option( 'order_modified_dates_filter_variable_length' ) !== $args['order_modified_dates_filter_variable_length'] ) {
            woo_ce_update_option( 'order_modified_dates_filter_variable_length', $args['order_modified_dates_filter_variable_length'] );
        }

        if ( woo_ce_get_option( 'order_billing_country' ) !== $args['order_billing_country'] ) {
            woo_ce_update_option( 'order_billing_country', $args['order_billing_country'] );
        }
        if ( woo_ce_get_option( 'order_shipping_country' ) !== $args['order_shipping_country'] ) {
            woo_ce_update_option( 'order_shipping_country', $args['order_shipping_country'] );
        }
        if ( woo_ce_get_option( 'order_product' ) !== $args['order_product'] ) {
            woo_ce_update_option( 'order_product', $args['order_product'] );
        }
        if ( woo_ce_get_option( 'order_customer' ) !== $args['order_customer'] ) {
            woo_ce_update_option( 'order_customer', $args['order_customer'] );
        }
        if ( woo_ce_get_option( 'order_category' ) !== $args['order_category'] ) {
            woo_ce_update_option( 'order_category', $args['order_category'] );
        }
        if ( woo_ce_get_option( 'order_tag' ) !== $args['order_tag'] ) {
            woo_ce_update_option( 'order_tag', $args['order_tag'] );
        }
        if ( woo_ce_get_option( 'order_user_roles' ) !== $args['order_user_roles'] ) {
            woo_ce_update_option( 'order_user_roles', $args['order_user_roles'] );
        }
        if ( woo_ce_get_option( 'order_coupon' ) !== $args['order_coupon'] ) {
            woo_ce_update_option( 'order_coupon', $args['order_coupon'] );
        }
        // Product.
        if ( woo_ce_get_option( 'order_product_exclude' ) !== $args['order_product_exclude'] ) {
            woo_ce_update_option( 'order_product_exclude', $args['order_product_exclude'] );
        }
        // Category.
        // Tag.
        if ( woo_ce_get_option( 'order_order_ids' ) !== $args['order_ids'] ) {
            woo_ce_update_option( 'order_order_ids', $args['order_ids'] );
        }
        if ( woo_ce_get_option( 'order_payment_method' ) !== $args['order_payment'] ) {
            woo_ce_update_option( 'order_payment_method', $args['order_payment'] );
        }
        if ( woo_ce_get_option( 'order_shipping_method' ) !== $args['order_shipping'] ) {
            woo_ce_update_option( 'order_shipping_method', $args['order_shipping'] );
        }
        if ( woo_ce_get_option( 'order_digital_products' ) !== $args['order_items_digital'] ) {
            woo_ce_update_option( 'order_digital_products', $args['order_items_digital'] );
        }
        if ( woo_ce_get_option( 'order_items_formatting' ) !== $args['order_items'] ) {
            woo_ce_update_option( 'order_items_formatting', $args['order_items'] );
        }
        if ( woo_ce_get_option( 'order_items_types' ) !== $args['order_items_types'] ) {
            woo_ce_update_option( 'order_items_types', $args['order_items_types'] );
        }
        if ( woo_ce_get_option( 'order_flag_notes' ) !== $args['order_flag_notes'] ) {
            woo_ce_update_option( 'order_flag_notes', $args['order_flag_notes'] );
        }
        if ( woo_ce_get_option( 'max_order_items' ) !== $args['max_order_items'] ) {
            woo_ce_update_option( 'max_order_items', $args['max_order_items'] );
        }
        if ( woo_ce_get_option( 'order_orderby' ) !== $args['order_orderby'] ) {
            woo_ce_update_option( 'order_orderby', $args['order_orderby'] );
        }
        if ( woo_ce_get_option( 'order_order' ) !== $args['order_order'] ) {
            woo_ce_update_option( 'order_order', $args['order_order'] );
        }

        return $args;
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_order_dataset_args', 10, 3 );

    /* End of: WordPress Administration */

}

/**
 * Modify the order dataset arguments
 *
 * @param array  $args         The arguments for the dataset.
 * @param string $export_type  The export type.
 * @param int    $is_scheduled Whether the export is scheduled or not.
 * @return array
 */
function woo_ce_cron_order_dataset_args( $args, $post_id, $export_type = '', $is_scheduled = 0 ) {

    // Check if we're dealing with the Order Export Type.
    if ( 'order' !== $export_type ) {
        return $args;
    }

    $order_orderby                 = false;
    $order_filter_status           = false;
    $order_filter_customer         = false;
    $order_filter_product          = false;
    $order_filter_product_exclude  = false;
    $order_filter_billing_country  = false;
    $order_filter_shipping_country = false;
    $order_filter_category         = false;
    $order_filter_tag              = false;
    $order_filter_payment          = false;
    $order_filter_shipping         = false;
    $order_filter_user_role        = false;
    $order_filter_coupon           = false;
    $order_filter_digital          = false;

    // Order Date.
    $order_dates_filter                = false;
    $order_filter_date_variable        = false;
    $order_filter_date_variable_length = false;
    $order_filter_dates_from           = false;
    $order_filter_dates_to             = false;

    // Order Modified Date.
    $order_modified_dates_filter                = false;
    $order_filter_modified_date_variable        = false;
    $order_filter_modified_date_variable_length = false;
    $order_filter_modified_dates_from           = false;
    $order_filter_modified_dates_to             = false;

    $order_filter_order_item       = false;
    $order_filter_order_item_types = false;
    $max_order_items               = woo_ce_get_option( 'max_order_items', 10 );

    if ( $is_scheduled ) {
        $scheduled_export = $post_id;

        $order_orderby                 = get_post_meta( $scheduled_export, '_filter_order_orderby', true );
        $order_filter_status           = get_post_meta( $scheduled_export, '_filter_order_status', true );
        $order_filter_customer         = get_post_meta( $scheduled_export, '_filter_order_customer', true );
        $order_filter_product          = get_post_meta( $scheduled_export, '_filter_order_product', true );
        $order_filter_product_exclude  = get_post_meta( $scheduled_export, '_filter_order_product_exclude', true );
        $order_filter_billing_country  = get_post_meta( $scheduled_export, '_filter_order_billing_country', true );
        $order_filter_shipping_country = get_post_meta( $scheduled_export, '_filter_order_shipping_country', true );
        $order_filter_category         = get_post_meta( $scheduled_export, '_filter_order_category', true );
        $order_filter_tag              = get_post_meta( $scheduled_export, '_filter_order_tag', true );
        $order_filter_payment          = get_post_meta( $scheduled_export, '_filter_order_payment', true );
        $order_filter_shipping         = get_post_meta( $scheduled_export, '_filter_order_shipping', true );
        $order_filter_user_role        = get_post_meta( $scheduled_export, '_filter_order_user_role', true );
        $order_filter_coupon           = get_post_meta( $scheduled_export, '_filter_order_coupon', true );
        $order_filter_digital          = get_post_meta( $scheduled_export, '_filter_order_items_digital', true );

        // Order Date.
        $order_dates_filter = get_post_meta( $scheduled_export, '_filter_order_date', true );
        if ( $order_dates_filter ) {
            switch ( $order_dates_filter ) {

                case 'manual':
                    $order_filter_dates_from = get_post_meta( $scheduled_export, '_filter_order_dates_from', true );
                    $order_filter_dates_to   = get_post_meta( $scheduled_export, '_filter_order_dates_to', true );
                    break;

                case 'variable':
                    $order_filter_date_variable        = get_post_meta( $scheduled_export, '_filter_order_date_variable', true );
                    $order_filter_date_variable_length = get_post_meta( $scheduled_export, '_filter_order_date_variable_length', true );
                    break;

            }
        }

        // Order Modified Date.
        $order_modified_dates_filter = get_post_meta( $scheduled_export, '_filter_order_modified_date', true );
        if ( $order_modified_dates_filter ) {
            switch ( $order_modified_dates_filter ) {

                case 'manual':
                    $order_filter_modified_dates_from = get_post_meta( $scheduled_export, '_filter_order_modified_dates_from', true );
                    $order_filter_modified_dates_to   = get_post_meta( $scheduled_export, '_filter_order_modified_dates_to', true );
                    break;

                case 'variable':
                    $order_filter_modified_date_variable        = get_post_meta( $scheduled_export, '_filter_order_modified_date_variable', true );
                    $order_filter_modified_date_variable_length = get_post_meta( $scheduled_export, '_filter_order_modified_date_variable_length', true );
                    break;

            }
        }
        $order_filter_order_item       = get_post_meta( $scheduled_export, '_filter_order_items', true );
        $order_filter_order_item_types = get_post_meta( $scheduled_export, '_filter_order_item_types', true );
        $max_order_items               = get_post_meta( $scheduled_export, '_filter_order_max_order_items', true );
    } else {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['order_status'] ) ) {
            $order_filter_status = sanitize_text_field( $_GET['order_status'] );
            $order_filter_status = explode( ',', $order_filter_status );
        }
        // Customer.
        if ( isset( $_GET['order_product'] ) ) {
            $order_filter_product = sanitize_text_field( $_GET['order_product'] );
            $order_filter_product = explode( ',', $order_filter_product );
            if ( isset( $_GET['order_product_exclude'] ) ) {
                $order_filter_product_exclude = absint( $_GET['order_product_exclude'] );
            }
        }
        if ( isset( $_GET['billing_country'] ) ) {
            $order_filter_billing_country = sanitize_text_field( $_GET['billing_country'] );
            $order_filter_billing_country = explode( ',', $order_filter_billing_country );
        }
        if ( isset( $_GET['shipping_country'] ) ) {
            $order_filter_shipping_country = sanitize_text_field( $_GET['shipping_country'] );
            $order_filter_shipping_country = explode( ',', $order_filter_shipping_country );
        }
        if ( isset( $_GET['payment_gateway'] ) ) {
            $order_filter_payment = sanitize_text_field( $_GET['order_payment'] );
            $order_filter_payment = explode( ',', $order_filter_payment );
        }
        if ( isset( $_GET['shipping_method'] ) ) {
            $order_filter_shipping = sanitize_text_field( $_GET['shipping_method'] );
            $order_filter_shipping = explode( ',', $order_filter_shipping );
        }
        // User Role.
        // Coupon Code.
        if ( isset( $_GET['order_items_digital'] ) ) {
            $order_filter_digital = sanitize_text_field( $_GET['order_items_digital'] );
        }
        if ( isset( $_GET['order_date_from'] ) || isset( $_GET['order_date_to'] ) ) {
            // @mod - The CRON export engine does not support variable date filtering, yet. Check in 2.4+.
            $order_dates_filter      = 'manual';
            $order_filter_dates_from = ( isset( $_GET['order_date_from'] ) ? sanitize_text_field( $_GET['order_date_from'] ) : false );
            $order_filter_dates_to   = ( isset( $_GET['order_date_to'] ) ? sanitize_text_field( $_GET['order_date_to'] ) : false );
        }
        if ( isset( $_GET['max_order_items'] ) ) {
            $max_order_items = absint( $_GET['max_order_items'] );
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
    }

    // Merge in the form data for this dataset.
    $overrides = array(
        'order_orderby'                               => ( ! empty( $order_orderby ) ? $order_orderby : 'DATE' ),
        'order_status'                                => ( ! empty( $order_filter_status ) ? $order_filter_status : false ),
        'order_customer'                              => ( ! empty( $order_filter_customer ) ? (array) $order_filter_customer : array() ),
        'order_product'                               => ( ! empty( $order_filter_product ) ? (array) $order_filter_product : array() ),
        'order_product_exclude'                       => ( ! empty( $order_filter_product_exclude ) ? $order_filter_product_exclude : false ),
        'order_billing_country'                       => ( ! empty( $order_filter_billing_country ) ? array_map( 'sanitize_text_field', (array) $order_filter_billing_country ) : false ),
        'order_shipping_country'                      => ( ! empty( $order_filter_shipping_country ) ? $order_filter_shipping_country : false ),
        'order_category'                              => ( ! empty( $order_filter_category ) ? $order_filter_category : false ),
        'order_tag'                                   => ( ! empty( $order_filter_tag ) ? $order_filter_tag : false ),
        'order_payment'                               => ( ! empty( $order_filter_payment ) ? $order_filter_payment : false ),
        'order_shipping'                              => ( ! empty( $order_filter_shipping ) ? array_map( 'sanitize_text_field', (array) $order_filter_shipping ) : false ),
        'order_user_roles'                            => ( ! empty( $order_filter_user_role ) ? array_map( 'sanitize_text_field', (array) $order_filter_user_role ) : false ),
        'order_coupon'                                => ( ! empty( $order_filter_coupon ) ? array_map( 'sanitize_text_field', (array) $order_filter_coupon ) : false ),
        'order_items_digital'                         => ( ! empty( $order_filter_digital ) ? $order_filter_digital : false ),
        // Order Date.
        'order_dates_filter'                          => $order_dates_filter,
        'order_dates_filter_variable'                 => ( ! empty( $order_filter_date_variable ) ? absint( $order_filter_date_variable ) : false ),
        'order_dates_filter_variable_length'          => ( ! empty( $order_filter_date_variable_length ) ? sanitize_text_field( $order_filter_date_variable_length ) : false ),
        'order_dates_from'                            => ( ! empty( $order_filter_dates_from ) ? $order_filter_dates_from : false ),
        'order_dates_to'                              => ( ! empty( $order_filter_dates_to ) ? $order_filter_dates_to : false ),
        // Order Modified Date.
        'order_modified_dates_filter'                 => $order_modified_dates_filter,
        'order_modified_dates_filter_variable'        => ( ! empty( $order_filter_modified_date_variable ) ? absint( $order_filter_modified_date_variable ) : false ),
        'order_modified_dates_filter_variable_length' => ( ! empty( $order_filter_modified_date_variable_length ) ? sanitize_text_field( $order_filter_modified_date_variable_length ) : false ),
        'order_modified_dates_from'                   => ( ! empty( $order_filter_modified_dates_from ) ? $order_filter_modified_dates_from : false ),
        'order_modified_dates_to'                     => ( ! empty( $order_filter_modified_dates_to ) ? $order_filter_modified_dates_to : false ),
        'order_items'                                 => ( ! empty( $order_filter_order_item ) ? $order_filter_order_item : false ),
        'order_items_types'                           => ( ! empty( $order_filter_order_item_types ) ? $order_filter_order_item_types : false ),
        'max_order_items'                             => ( ! empty( $max_order_items ) ? $max_order_items : false ),
    );

    $args = wp_parse_args( $overrides, $args );

    return $args;
}
add_filter( 'woo_ce_extend_cron_dataset_args', 'woo_ce_cron_order_dataset_args', 10, 4 );

/**
 * Returns a list of Order export columns.
 *
 * @param string $format The format of the fields to return.
 * @param int    $post_ID The ID of the post to return the fields for.
 * @return array
 */
function woo_ce_get_order_fields( $format = 'full', $post_ID = 0 ) {

    $export_type = 'order';

    $fields   = array();
    $fields[] = array(
        'name'  => 'purchase_id',
        'label' => __( 'Order ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'post_id',
        'label' => __( 'Post ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'purchase_total',
        'label' => __( 'Order Total', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'purchase_subtotal',
        'label' => __( 'Order Subtotal', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_currency',
        'label' => __( 'Order Currency', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_discount',
        'label' => __( 'Order Discount', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'coupon_code',
        'label' => __( 'Coupon Code', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'coupon_expiry_date',
        'label' => __( 'Coupon Expiry Date', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'coupon_description',
        'label' => __( 'Coupon Description', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'purchase_total_tax',
        'label' => __( 'Order Total Tax', 'woocommerce-exporter' ),
    );

    // phpcs:disable Squiz.PHP.CommentedOutCode.Found

    /*
    $fields[] = array(
        'name' => 'order_incl_tax',
        'label' => __( 'Order Incl. Tax', 'woocommerce-exporter' ),
    );
    */

    // phpcs:enable Squiz.PHP.CommentedOutCode.Found

    $fields[] = array(
        'name'  => 'order_subtotal_excl_tax',
        'label' => __( 'Order Subtotal Excl. Tax', 'woocommerce-exporter' ),
    );

    // phpcs:disable Squiz.PHP.CommentedOutCode.Found

    /*
    $fields[] = array(
        'name' => 'order_tax_rate',
        'label' => __( 'Order Tax Rate', 'woocommerce-exporter' ),
    );
    */

    // phpcs:enable Squiz.PHP.CommentedOutCode.Found

    $fields[] = array(
        'name'  => 'order_sales_tax',
        'label' => __( 'Sales Tax Total', 'woocommerce-exporter' ),
    );
    // Tax Rates.
    if ( apply_filters( 'woo_ce_allow_individual_tax_fields', true ) ) {
        $tax_rates = woo_ce_get_order_tax_rates();
        if ( ! empty( $tax_rates ) ) {
            foreach ( $tax_rates as $tax_rate ) {
                $fields[] = array(
                    'name'  => sprintf( 'purchase_total_tax_rate_%d', $tax_rate['rate_id'] ),
                    // translators: %s: Tax Rate Label.
                    'label' => sprintf(
                        // translators: %1$1s - tax class name, %2$2s - tax rate label.
                        __( 'Order Total Tax: %1$1s%2$2s', 'woocommerce-exporter' ),
                        ! empty( $tax_rate['tax_class'] ) ? $tax_rate['tax_class']['name'] . ' - ' : '',
                        $tax_rate['label']
                    ),
                );
            }
        }
    }
    $fields[] = array(
        'name'  => 'order_shipping_tax',
        'label' => __( 'Shipping Tax Total', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_incl_tax',
        'label' => __( 'Shipping Incl. Tax', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_excl_tax',
        'label' => __( 'Shipping Excl. Tax', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'refund_total',
        'label' => __( 'Refund Total', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'refund_date',
        'label' => __( 'Refund Date', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_tax_percentage',
        'label' => __( 'Order Tax Percentage', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'payment_gateway_id',
        'label' => __( 'Payment Gateway ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'payment_gateway',
        'label' => __( 'Payment Gateway', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_method_id',
        'label' => __( 'Shipping Method ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_method',
        'label' => __( 'Shipping Method', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_instance_id',
        'label' => __( 'Shipping Instance ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_cost',
        'label' => __( 'Shipping Cost', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_weight_total',
        'label' => __( 'Shipping Weight', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'payment_status',
        'label' => __( 'Order Status', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'post_status',
        'label' => __( 'Post Status', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_key',
        'label' => __( 'Order Key', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'transaction_id',
        'label' => __( 'Transaction ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'created_via',
        'label' => __( 'Created Via', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'cart_hash',
        'label' => __( 'Cart Hash', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'purchase_date',
        'label' => __( 'Order Date', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'modified_date',
        'label' => __( 'Order Modified Date', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'purchase_time',
        'label' => __( 'Order Time', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'customer_message',
        'label' => __( 'Customer Message', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'customer_notes',
        'label' => __( 'Customer Notes', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_notes',
        'label' => __( 'Order Notes', 'woocommerce-exporter' ),
    );
    // PayPal.
    $fields[] = array(
        'name'  => 'paypal_payer_paypal_address',
        'label' => __( 'PayPal: Payer PayPal Address', 'woocommerce-exporter' ),
        'hover' => __( 'PayPal', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'paypal_payer_first_name',
        'label' => __( 'PayPal: Payer first name', 'woocommerce-exporter' ),
        'hover' => __( 'PayPal', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'paypal_payer_last_name',
        'label' => __( 'PayPal: Payer last name', 'woocommerce-exporter' ),
        'hover' => __( 'PayPal', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'paypal_payment_type',
        'label' => __( 'PayPal: Payment type', 'woocommerce-exporter' ),
        'hover' => __( 'PayPal', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'paypal_payment_status',
        'label' => __( 'PayPal: Payment status', 'woocommerce-exporter' ),
        'hover' => __( 'PayPal', 'woocommerce-exporter' ),
    );

    $fields[] = array(
        'name'  => 'total_quantity',
        'label' => __( 'Total Quantity', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'total_order_items',
        'label' => __( 'Total Order Items', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'user_id',
        'label' => __( 'User ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'user_name',
        'label' => __( 'Username', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'user_role',
        'label' => __( 'User Role', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'ip_address',
        'label' => __( 'Checkout IP Address', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'browser_agent',
        'label' => __( 'Checkout Browser Agent', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'has_downloads',
        'label' => __( 'Has Downloads', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'has_downloaded',
        'label' => __( 'Has Downloaded', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_full_name',
        'label' => __( 'Billing: Full Name', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_first_name',
        'label' => __( 'Billing: First Name', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_last_name',
        'label' => __( 'Billing: Last Name', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_company',
        'label' => __( 'Billing: Company', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_address',
        'label' => __( 'Billing: Street Address (Full)', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_address_1',
        'label' => __( 'Billing: Street Address 1', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_address_2',
        'label' => __( 'Billing: Street Address 2', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_city',
        'label' => __( 'Billing: City', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_postcode',
        'label' => __( 'Billing: ZIP Code', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_state',
        'label' => __( 'Billing: State (prefix)', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_state_full',
        'label' => __( 'Billing: State', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_country',
        'label' => __( 'Billing: Country (prefix)', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_country_full',
        'label' => __( 'Billing: Country', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_phone',
        'label' => __( 'Billing: Phone Number', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'billing_email',
        'label' => __( 'Billing: E-mail Address', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_full_name',
        'label' => __( 'Shipping: Full Name', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_first_name',
        'label' => __( 'Shipping: First Name', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_last_name',
        'label' => __( 'Shipping: Last Name', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_company',
        'label' => __( 'Shipping: Company', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_address',
        'label' => __( 'Shipping: Street Address (Full)', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_address_1',
        'label' => __( 'Shipping: Street Address 1', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_address_2',
        'label' => __( 'Shipping: Street Address 2', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_city',
        'label' => __( 'Shipping: City', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_postcode',
        'label' => __( 'Shipping: ZIP Code', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_state',
        'label' => __( 'Shipping: State (prefix)', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_state_full',
        'label' => __( 'Shipping: State', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_country',
        'label' => __( 'Shipping: Country (prefix)', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'shipping_country_full',
        'label' => __( 'Shipping: Country', 'woocommerce-exporter' ),
    );

    // phpcs:disable Squiz.PHP.CommentedOutCode.Found

    /*
    $fields[] = array(
        'name' => '',
        'label' => __( '', 'woocommerce-exporter' ),
    );
    */

    // phpcs:enable Squiz.PHP.CommentedOutCode.Found

    // Drop in our content filters here.
    add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

    // Allow Plugin/Theme authors to add support for additional Order columns.
    $fields = apply_filters( sprintf( WOO_CE_PREFIX . '_%s_fields', $export_type ), $fields, $export_type );

    // Remove our content filters here to play nice with other Plugins.
    remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

    $fields[] = array(
        'name'  => 'order_items_id',
        'label' => __( 'Order Items: ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_product_id',
        'label' => __( 'Order Items: Product ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_variation_id',
        'label' => __( 'Order Items: Variation ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_sku',
        'label' => __( 'Order Items: SKU', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_name',
        'label' => __( 'Order Items: Product Name', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_variation',
        'label' => __( 'Order Items: Product Variation', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_image_embed',
        'label' => __( 'Order Items: Featured Image (Embed)', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_description',
        'label' => __( 'Order Items: Product Description', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_excerpt',
        'label' => __( 'Order Items: Product Excerpt', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_publish_date',
        'label' => __( 'Order Items: Publish Date', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_modified_date',
        'label' => __( 'Order Items: Modified Date', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_tax_class',
        'label' => __( 'Order Items: Tax Class', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_quantity',
        'label' => __( 'Order Items: Quantity', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_total',
        'label' => __( 'Order Items: Total', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_subtotal',
        'label' => __( 'Order Items: Subtotal', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_rrp',
        'label' => __( 'Order Items: RRP', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_discount',
        'label' => __( 'Order Items: Discount', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_stock',
        'label' => __( 'Order Items: Stock', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_shipping_class',
        'label' => __( 'Order Items: Shipping Class', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_tax',
        'label' => __( 'Order Items: Tax', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_tax_percentage',
        'label' => __( 'Order Items: Tax Percentage', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_tax_subtotal',
        'label' => __( 'Order Items: Tax Subtotal', 'woocommerce-exporter' ),
    );
    // Order Item: Tax Rate - ....
    if ( apply_filters( 'woo_ce_allow_individual_tax_fields', true ) ) {
        $tax_rates = woo_ce_get_order_tax_rates();
        if ( ! empty( $tax_rates ) ) {
            foreach ( $tax_rates as $tax_rate ) {
                $fields[] = array(
                    'name'  => sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] ),
                    // translators: %s: Tax Rate Label.
                    'label' => sprintf(
                        // translators: %1$1s - tax class name, %2$2s - tax rate label.
                        __( 'Order Items: Tax Rate - %1$1s%2$2s', 'woocommerce-exporter' ),
                        ! empty( $tax_rate['tax_class'] ) ? $tax_rate['tax_class']['name'] . ' - ' : '',
                        $tax_rate['label']
                    ),
                );
            }
        }
    }
    $fields[] = array(
        'name'  => 'order_items_refund_subtotal',
        'label' => __( 'Order Items: Refund Subtotal', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_refund_subtotal_incl_tax',
        'label' => __( 'Order Items: Refund Subtotal Incl. Tax', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_refund_quantity',
        'label' => __( 'Order Items: Refund Quantity', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_type',
        'label' => __( 'Order Items: Type', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_type_id',
        'label' => __( 'Order Items: Type ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_category',
        'label' => __( 'Order Items: Category', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_tag',
        'label' => __( 'Order Items: Tag', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_total_sales',
        'label' => __( 'Order Items: Total Sales', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_weight',
        'label' => __( 'Order Items: Weight', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_height',
        'label' => __( 'Order Items: Height', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_width',
        'label' => __( 'Order Items: Width', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_length',
        'label' => __( 'Order Items: Length', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_total_weight',
        'label' => __( 'Order Items: Total Weight', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_refund_items_prices_include_tax',
        'label' => __( 'Refund Items: Prices Include Tax', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_refund_items_refund_amount',
        'label' => __( 'Refund Items: Refund Amount', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_refund_items_refunded_by',
        'label' => __( 'Refund Items: Refunded By', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_refund_items_refunded_payment',
        'label' => __( 'Refund Items: Refunded Payment', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_refund_items_refund_reason',
        'label' => __( 'Refund Items: Refund Reason', 'woocommerce-exporter' ),
    );

    // Drop in our content filters here.
    add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

    // Allow Plugin/Theme authors to add support for additional Order Item columns.
    $fields = apply_filters( sprintf( WOO_CE_PREFIX . '_%s_fields', 'order_items' ), $fields, $export_type );

    // Remove our content filters here to play nice with other Plugins.
    remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

    // Check if we're dealing with an Export Template.
    $sorting = false;
    if ( ! empty( $post_ID ) ) {
        $remember = get_post_meta( $post_ID, sprintf( '_%s_fields', $export_type ), true );
        $hidden   = get_post_meta( $post_ID, sprintf( '_%s_hidden', $export_type ), false );
        $sorting  = get_post_meta( $post_ID, sprintf( '_%s_sorting', $export_type ), true );
    } else {
        $remember = woo_ce_get_option( $export_type . '_fields', array() );
        $hidden   = woo_ce_get_option( $export_type . '_hidden', array() );
    }
    if ( ! empty( $remember ) ) {
        $remember = maybe_unserialize( $remember );
        $hidden   = maybe_unserialize( $hidden );
        $size     = count( $fields );
        for ( $i = 0; $i < $size; $i++ ) {
            $fields[ $i ]['disabled'] = ( isset( $fields[ $i ]['disabled'] ) ? $fields[ $i ]['disabled'] : 0 );
            $fields[ $i ]['hidden']   = ( isset( $fields[ $i ]['hidden'] ) ? $fields[ $i ]['hidden'] : 0 );
            $fields[ $i ]['default']  = 1;
            if ( isset( $fields[ $i ]['name'] ) ) {
                // If not found turn off default.
                if ( ! array_key_exists( $fields[ $i ]['name'], $remember ) ) {
                    $fields[ $i ]['default'] = 0;
                }
                // Remove the field from exports if found.
                if ( array_key_exists( $fields[ $i ]['name'], $hidden ) ) {
                    $fields[ $i ]['hidden'] = 1;
                }
            }
        }
    }

    switch ( $format ) {

        case 'summary':
            $output = array();
            $size   = count( $fields );
            for ( $i = 0; $i < $size; $i++ ) {
                if ( isset( $fields[ $i ] ) ) {
                    $output[ $fields[ $i ]['name'] ] = 'on';
                }
            }
            return $output;
            break; // phpcs:ignore Squiz.PHP.NonExecutableCode.Unreachable

        case 'full':
        default:
            // Load the default sorting.
            if ( empty( $sorting ) ) {
                $sorting = woo_ce_get_option( sprintf( '%s_sorting', $export_type ), array() );
            }
            $size = count( $fields );
            for ( $i = 0; $i < $size; $i++ ) {
                if ( ! isset( $fields[ $i ]['name'] ) ) {
                    unset( $fields[ $i ] );
                    continue;
                }
                $fields[ $i ]['reset'] = $i;
                $fields[ $i ]['order'] = ( isset( $sorting[ $fields[ $i ]['name'] ] ) ? $sorting[ $fields[ $i ]['name'] ] : $i );
            }
            // Check if we are using PHP 5.3 and above.
            if ( version_compare( phpversion(), '5.3' ) >= 0 ) {
                usort( $fields, woo_ce_sort_fields( 'order' ) );
            }
            return $fields;
            break; // phpcs:ignore Squiz.PHP.NonExecutableCode.Unreachable
    }
}

/**
 * Check if we should override field labels from the Field Editor.
 *
 * @param array $fields Array of export fields.
 * @return array
 */
function woo_ce_override_order_field_labels( $fields = array() ) {

    global $export;

    $export_type = 'order';

    $labels = false;

    // Check if this is a Quick Export or CRON export.
    if ( isset( $export->export_template ) ) {
        $export_template = $export->export_template;
        if ( ! empty( $export_template ) ) {
            $labels = get_post_meta( $export_template, sprintf( '_%s_labels', $export_type ), true );
        }
    }

    // Check if this is a Scheduled Export.
    $scheduled_export = absint( get_transient( WOO_CE_PREFIX . '_scheduled_export_id' ) );
    if ( $scheduled_export ) {
        $export_fields = get_post_meta( $scheduled_export, '_export_fields', true );
        if ( 'template' === $export_fields ) {
            $export_template = get_post_meta( $scheduled_export, '_export_template', true );
            if ( ! empty( $export_template ) ) {
                $labels = get_post_meta( $export_template, sprintf( '_%s_labels', $export_type ), true );
            }
        }
    }

    // Default to Quick Export labels.
    if ( empty( $labels ) ) {
        $labels = woo_ce_get_option( sprintf( '%s_labels', $export_type ), array() );
    }

    // Allow Plugin/Theme authors to easily override export field labels.
    $labels = apply_filters( 'woo_ce_override_order_field_labels', $labels );

    if ( ! empty( $labels ) ) {
        foreach ( $fields as $key => $field ) {
            if ( isset( $labels[ $field['name'] ] ) ) {
                $fields[ $key ]['label'] = $labels[ $field['name'] ];
            }
        }
    }
    return $fields;
}
add_filter( 'woo_ce_order_fields', 'woo_ce_override_order_field_labels', 11 );
add_filter( 'woo_ce_order_items_fields', 'woo_ce_override_order_field_labels', 11 );


/**
 * Returns the export column header label based on an export column slug.
 *
 * @param string $name        The name of the field to retrieve.
 * @param string $format      Whether to return the field name or the field value.
 * @param bool   $order_items Whether to include order items.
 * @return string|null The value of the field, or null if the field doesn't exist.
 */
function woo_ce_get_order_field( $name = null, $format = 'name', $order_items = false, $fields = array() ) {

    global $export;

    $output = '';
    if ( $name ) {
        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_field() > woo_ce_get_order_fields(): ' . ( time() - $export->start_time ) ) );
        }
        $size = count( $fields );
        for ( $i = 0; $i < $size; $i++ ) {
            if ( $fields[ $i ]['name'] === $name ) {
                switch ( $format ) {

                    case 'name':
                        $output = $fields[ $i ]['label'];
                        if ( 'unique' === $order_items ) {
                            $output = str_replace( __( 'Order Items: ', 'woocommerce-exporter' ), '', $output );
                        }

                        // Allow Plugin/Theme authors to easily override export field labels.
                        $output = apply_filters( 'woo_ce_get_order_field_label', $output, $order_items );

                        break;

                    case 'full':
                        $output = $fields[ $i ];
                        break;

                }
                return $output;
            }
        }
        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'for $fields...: ' . ( time() - $export->start_time ) ) );
        }
    }
    return $output;
}

/**
 * Returns an array of export column header labels based on an export column slug.
 *
 * @param array  $export_fields An array of fields to export.
 * @param string $format        The format to return the fields in. Either 'name' or 'id'.
 * @return array
 */
function woo_ce_get_order_field_array( $export_fields = null, $format = 'name' ) {

    global $export;

    $output = array();
    if ( $export_fields ) {
        $fields = woo_ce_get_order_fields();
        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_field() > woo_ce_get_order_fields(): ' . ( time() - $export->start_time ) ) );
        }

        foreach ( $export_fields as $name => $field ) {
            $size = count( $fields );
            for ( $i = 0; $i < $size; $i++ ) {
                if ( $fields[ $i ]['name'] === $name ) {
                    switch ( $format ) {

                        case 'name':
                            $output[] = $fields[ $i ]['label'];
                            break;

                        case 'full':
                            $output[] = $fields[ $i ];
                            break;

                    }
                    break;
                }
            }
        }

        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'for $fields...: ' . ( time() - $export->start_time ) ) );
        }
    }
    return $output;
}

/**
 * Returns a list of Order IDs.
 *
 * @param string $export_type The type of export.
 * @param array  $args        An array of arguments.
 * @return array
 */
function woo_ce_get_orders( $export_type = 'order', $args = array(), $export = array() ) {
    // phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_print_r
    if ( empty( $export ) ) {
		global $export;
	}

    $limit_volume = -1;
    $offset       = 0;

    if ( $args ) {
        $post_ids           = ( isset( $args['order_ids'] ) ? $args['order_ids'] : false );
        $payment            = ( isset( $args['order_payment'] ) ? $args['order_payment'] : false );
        $shipping           = ( isset( $args['order_shipping'] ) ? $args['order_shipping'] : false );
        $user_roles         = ( isset( $args['order_user_roles'] ) ? $args['order_user_roles'] : false );
        $coupon             = ( isset( $args['order_coupon'] ) ? $args['order_coupon'] : false );
        $product            = ( isset( $args['order_product'] ) ? $args['order_product'] : false );
        $product_category   = ( isset( $args['order_category'] ) ? $args['order_category'] : false );
        $product_tag        = ( isset( $args['order_tag'] ) ? $args['order_tag'] : false );
        $product_brand      = ( isset( $args['order_brand'] ) ? $args['order_brand'] : false );
        $product_vendor     = ( isset( $args['order_product_vendor'] ) ? $args['order_product_vendor'] : false );
        $limit_volume       = ( isset( $args['limit_volume'] ) ? $args['limit_volume'] : false );
        $offset             = $args['offset'];
        $orderby            = ( isset( $args['order_orderby'] ) ? $args['order_orderby'] : 'DATE' );
        $order              = ( isset( $args['order_order'] ) ? $args['order_order'] : 'DESC' );
        $order_dates_filter = ( isset( $args['order_dates_filter'] ) ? $args['order_dates_filter'] : false );
        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'order_dates_filter' ) );
            woo_ce_error_log( sprintf( 'Debug: %s', $order_dates_filter ) );
        }
        switch ( $order_dates_filter ) {

            case 'tomorrow':
                $order_dates_from = woo_ce_get_order_date_filter( 'tomorrow', 'from' );
                $order_dates_to   = woo_ce_get_order_date_filter( 'tomorrow', 'to' );
                break;

            case 'today':
                $order_dates_from = woo_ce_get_order_date_filter( 'today', 'from' );
                $order_dates_to   = woo_ce_get_order_date_filter( 'today', 'to' );
                break;

            case 'yesterday':
                $order_dates_from = woo_ce_get_order_date_filter( 'yesterday', 'from' );
                $order_dates_to   = woo_ce_get_order_date_filter( 'yesterday', 'to' );
                break;

            case 'current_week':
                $order_dates_from = woo_ce_get_order_date_filter( 'current_week', 'from' );
                $order_dates_to   = woo_ce_get_order_date_filter( 'current_week', 'to' );
                break;

            case 'last_week':
                $order_dates_from = woo_ce_get_order_date_filter( 'last_week', 'from' );
                $order_dates_to   = woo_ce_get_order_date_filter( 'last_week', 'to' );
                break;

            case 'current_month':
                $order_dates_from = woo_ce_get_order_date_filter( 'current_month', 'from' );
                $order_dates_to   = woo_ce_get_order_date_filter( 'current_month', 'to' );
                break;

            case 'last_month':
                $order_dates_from = woo_ce_get_order_date_filter( 'last_month', 'from' );
                $order_dates_to   = woo_ce_get_order_date_filter( 'last_month', 'to' );
                break;

            case 'current_year':
                $order_dates_from = woo_ce_get_order_date_filter( 'current_year', 'from' );
                $order_dates_to   = woo_ce_get_order_date_filter( 'current_year', 'to' );
                break;

            case 'last_year':
                $order_dates_from = woo_ce_get_order_date_filter( 'last_year', 'from' );
                $order_dates_to   = woo_ce_get_order_date_filter( 'last_year', 'to' );
                break;

            case 'manual':
                $date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );

                // Populate empty from or to dates.
                if ( ! empty( $args['order_dates_from'] ) ) {
                    $order_dates_from = woo_ce_format_order_date( $args['order_dates_from'] );
                } else {
                    // Default From date to the first Order.
                    $order_dates_from = woo_ce_get_order_first_gmdate( $date_format );
                }
                if ( ! empty( $args['order_dates_to'] ) ) {
                    // @mod - Testing the fix for the last date missing out, this line is the bane of 2017 getting it to play nice with all date formats.
                    // $order_dates_to = woo_ce_format_order_date( gmdate( $date_format, gmdate( strtotime( "+1 day", strtotime( woo_ce_format_order_date( $args['order_dates_to'] ) ) ) ) ) );.
                    $order_dates_to = woo_ce_format_order_date( $args['order_dates_to'] );
                    $order_dates_to = apply_filters( 'woo_ce_get_orders_order_dates_to', $order_dates_to, $args['order_dates_to'] );
                } else {
                    // Default To date to tomorrow.
                    $order_dates_to = woo_ce_format_order_date( woo_ce_get_order_date_filter( 'today', 'to', $date_format ) );
                }

                // phpcs:disable Squiz.PHP.CommentedOutCode.Found

                /*
                // @mod - I don't think this is relevant since we now send order_dates_to as 23:59:59 instead of 00:00:00, confirm in 2.4+.
                // Check if the same date has been provided for both order_dates_from and order_dates_to.
                if ( $order_dates_from == $order_dates_to ) {
                    // Add a day to order_dates_to.
                    $order_dates_to = woo_ce_format_order_date( gmdate( $date_format, gmdate( strtotime( "+1 day", strtotime( $order_dates_to ) ) ) ) );
                }
                */

                // phpcs:enable Squiz.PHP.CommentedOutCode.Found

                // Check if the provided dates match the date format.
                $validate_from = woo_ce_validate_order_date( $order_dates_from, woo_ce_format_order_date( $date_format ) );
                $validate_to   = woo_ce_validate_order_date( $order_dates_to, woo_ce_format_order_date( $date_format ) );
                if ( ! $validate_from && ! $validate_to ) {
                    //phpcs:disable WordPress.DateTime.CurrentTimeTimestamp.Requested, WordPress.DateTime.RestrictedFunctions.date_date
                    $order_dates_from = woo_ce_format_order_date( gmdate( $date_format, strtotime( $order_dates_from, current_time( 'timestamp', 0 ) ) ) );
                    $order_dates_to   = woo_ce_format_order_date( gmdate( $date_format, strtotime( $order_dates_to, current_time( 'timestamp', 0 ) ) ) );
                    //phpcs:enable WordPress.DateTime.CurrentTimeTimestamp.Requested, WordPress.DateTime.RestrictedFunctions.date_date
                }

                // WP_Query only accepts D-m-Y so we must format dates to that, fun times....
                if ( 'd/m/Y' !== $date_format ) {
                    $date_format = woo_ce_format_order_date( $date_format );
                    if ( function_exists( 'date_create_from_format' ) && function_exists( 'date_format' ) ) {

                        // Check if we've been passed a mixed format.

                        if ( strpos( $order_dates_from, '-' ) !== false ) {
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'order_dates_from' ) );
                                woo_ce_error_log( sprintf( 'Debug: %s', $order_dates_from ) );
                            }
                            $date_check = explode( '-', $order_dates_from );
                            if ( checkgmdate( $date_check[0], $date_check[1], $date_check[2] ) ) {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 1' ) );
                                }
                                $order_dates_from = date_create_from_format( 'm-d-Y', $order_dates_from );
                                if ( $order_dates_from ) {
                                    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                        woo_ce_error_log( sprintf( 'Debug: %s', 'option 1: accepted' ) );
                                        woo_ce_error_log( sprintf( 'Debug: %s', print_r( $date_check, true ) ) );
                                    }
                                    $order_dates_from = date_format( $order_dates_from, 'd-m-Y' );
                                    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                        woo_ce_error_log( sprintf( 'Debug: %s', $order_dates_from ) );
                                    }
                                }
                            } elseif ( checkgmdate( $date_check[1], $date_check[0], $date_check[2] ) ) {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 2' ) );
                                }
                                $order_dates_from = date_create_from_format( 'd-m-Y', $order_dates_from );
                                if ( $order_dates_from ) {
                                    $order_dates_from = date_format( $order_dates_from, 'd-m-Y' );
                                }
                            } else {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 3' ) );
                                }
                                $order_dates_from = date_create_from_format( $date_format, $order_dates_from );
                                if ( $order_dates_from ) {
                                    $order_dates_from = date_format( $order_dates_from, 'd-m-Y' );
                                }
                            }
                        } else {
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'option 4' ) );
                            }
                            $order_dates_from = date_create_from_format( $date_format, $order_dates_from );
                            if ( $order_dates_from ) {
                                $order_dates_from = date_format( $order_dates_from, 'd-m-Y' );
                            }
                        }
                        unset( $date_check );

                        if ( strpos( $order_dates_to, '-' ) !== false ) {
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'order_dates_to' ) );
                                woo_ce_error_log( sprintf( 'Debug: %s', $order_dates_to ) );
                            }
                            $date_check = explode( '-', $order_dates_to );
                            if ( checkgmdate( $date_check[0], $date_check[1], $date_check[2] ) ) {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 1' ) );
                                }
                                $order_dates_to = date_create_from_format( 'm-d-Y', $order_dates_to );
                                if ( $order_dates_to ) {
                                    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                        woo_ce_error_log( sprintf( 'Debug: %s', 'option 1: accepted' ) );
                                        woo_ce_error_log( sprintf( 'Debug: %s', print_r( $date_check, true ) ) );
                                    }
                                    $order_dates_to = date_format( $order_dates_to, 'd-m-Y' );
                                    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                        woo_ce_error_log( sprintf( 'Debug: %s', $order_dates_to ) );
                                    }
                                }
                            } elseif ( checkgmdate( $date_check[1], $date_check[0], $date_check[2] ) ) {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 2' ) );
                                }
                                $order_dates_to = date_create_from_format( 'd-m-Y', $order_dates_to );
                                if ( $order_dates_to ) {
                                    $order_dates_to = date_format( $order_dates_to, 'd-m-Y' );
                                }
                            } else {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 3' ) );
                                }
                                $order_dates_to = date_create_from_format( $date_format, $order_dates_to );
                                if ( $order_dates_to ) {
                                    $order_dates_to = date_format( $order_dates_to, 'd-m-Y' );
                                }
                            }
                        } else {
                            $order_dates_to = date_create_from_format( $date_format, $order_dates_to );
                            if ( $order_dates_to ) {
                                $order_dates_to = date_format( $order_dates_to, 'd-m-Y' );
                            }
                        }
                        unset( $date_check );

                    }
                }
                break;

            case 'variable':
                $order_filter_date_variable        = $args['order_dates_filter_variable'];
                $order_filter_date_variable_length = $args['order_dates_filter_variable_length'];
                if ( false !== $order_filter_date_variable && false !== $order_filter_date_variable_length ) {
                    $timestamp        = strtotime( sprintf( '-%d %s', $order_filter_date_variable, $order_filter_date_variable_length ), current_time( 'timestamp', 0 ) ); // phpcs:ignore
                    $order_dates_from = gmdate( 'd-m-Y-H-i-s', mktime( gmdate( 'H', $timestamp ), gmdate( 'i', $timestamp ), gmdate( 's', $timestamp ), gmdate( 'n', $timestamp ), gmdate( 'd', $timestamp ), gmdate( 'Y', $timestamp ) ) );
                    $order_dates_to   = woo_ce_get_order_date_filter( 'today', 'to' );
                    unset( $order_filter_date_variable, $order_filter_date_variable_length, $timestamp );
                }
                break;

            default:
                $order_dates_from = false;
                $order_dates_to   = false;
                break;

        }
        if ( ! empty( $order_dates_from ) && ! empty( $order_dates_to ) ) {
            // From.
            $order_dates_from = explode( '-', $order_dates_from );
            // Check that a valid date was provided.
            if ( isset( $order_dates_from[0] ) && isset( $order_dates_from[1] ) && isset( $order_dates_from[2] ) ) {
                $order_dates_from = array(
                    'year'   => absint( $order_dates_from[2] ),
                    'month'  => absint( $order_dates_from[1] ),
                    'day'    => absint( $order_dates_from[0] ),
                    'hour'   => ( isset( $order_dates_from[3] ) ? $order_dates_from[3] : 0 ),
                    'minute' => ( isset( $order_dates_from[4] ) ? $order_dates_from[4] : 0 ),
                    'second' => ( isset( $order_dates_from[5] ) ? $order_dates_from[5] : 0 ),
                );
                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                    woo_ce_error_log( sprintf( 'Debug: %s', 'order_dates_from' ) );
                    woo_ce_error_log( sprintf( 'Debug: %s', print_r( $order_dates_from, true ) ) );
                }
            } else {
                $order_dates_from = false;
            }
            // To.
            $order_dates_to = explode( '-', $order_dates_to );
            // Check that a valid date was provided.
            if ( isset( $order_dates_to[0] ) && isset( $order_dates_to[1] ) && isset( $order_dates_to[2] ) ) {
                $order_dates_to = array(
                    'year'   => absint( $order_dates_to[2] ),
                    'month'  => absint( $order_dates_to[1] ),
                    'day'    => absint( $order_dates_to[0] ),
                    'hour'   => ( isset( $order_dates_to[3] ) ? $order_dates_to[3] : 23 ),
                    'minute' => ( isset( $order_dates_to[4] ) ? $order_dates_to[4] : 59 ),
                    'second' => ( isset( $order_dates_to[5] ) ? $order_dates_to[5] : 59 ),
                );
                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                    woo_ce_error_log( sprintf( 'Debug: %s', 'order_dates_to' ) );
                    woo_ce_error_log( sprintf( 'Debug: %s', print_r( $order_dates_to, true ) ) );
                }
                // Check for bad values.
                switch ( $order_dates_filter ) {

                    case 'last_month':
                        if ( $order_dates_from['month'] !== $order_dates_to['month'] ) {
                            $order_dates_to['hour']   = 0;
                            $order_dates_to['minute'] = 0;
                            $order_dates_to['second'] = 0;
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'order_dates_to, last_month override' ) );
                                woo_ce_error_log( sprintf( 'Debug: %s', print_r( $order_dates_to, true ) ) );
                            }
                        }
                        break;

                }
            } else {
                $order_dates_to = false;
            }
        }
        // Order Modified Date.
        $order_modified_dates_filter = ( isset( $args['order_modified_dates_filter'] ) ? $args['order_modified_dates_filter'] : false );
        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'order_modified_dates_filter' ) );
            woo_ce_error_log( sprintf( 'Debug: %s', $order_modified_dates_filter ) );
        }
        switch ( $order_modified_dates_filter ) {

            case 'tomorrow':
                $order_modified_dates_from = woo_ce_get_order_date_filter( 'tomorrow', 'from' );
                $order_modified_dates_to   = woo_ce_get_order_date_filter( 'tomorrow', 'to' );
                break;

            case 'today':
                $order_modified_dates_from = woo_ce_get_order_date_filter( 'today', 'from' );
                $order_modified_dates_to   = woo_ce_get_order_date_filter( 'today', 'to' );
                break;

            case 'yesterday':
                $order_modified_dates_from = woo_ce_get_order_date_filter( 'yesterday', 'from' );
                $order_modified_dates_to   = woo_ce_get_order_date_filter( 'yesterday', 'to' );
                break;

            case 'current_week':
                $order_modified_dates_from = woo_ce_get_order_date_filter( 'current_week', 'from' );
                $order_modified_dates_to   = woo_ce_get_order_date_filter( 'current_week', 'to' );
                break;

            case 'last_week':
                $order_modified_dates_from = woo_ce_get_order_date_filter( 'last_week', 'from' );
                $order_modified_dates_to   = woo_ce_get_order_date_filter( 'last_week', 'to' );
                break;

            case 'current_month':
                $order_modified_dates_from = woo_ce_get_order_date_filter( 'current_month', 'from' );
                $order_modified_dates_to   = woo_ce_get_order_date_filter( 'current_month', 'to' );
                break;

            case 'last_month':
                $order_modified_dates_from = woo_ce_get_order_date_filter( 'last_month', 'from' );
                $order_modified_dates_to   = woo_ce_get_order_date_filter( 'last_month', 'to' );
                break;

            case 'current_year':
                $order_modified_dates_from = woo_ce_get_order_date_filter( 'current_year', 'from' );
                $order_modified_dates_to   = woo_ce_get_order_date_filter( 'current_year', 'to' );
                break;

            case 'last_year':
                $order_modified_dates_from = woo_ce_get_order_date_filter( 'last_year', 'from' );
                $order_modified_dates_to   = woo_ce_get_order_date_filter( 'last_year', 'to' );
                break;

            case 'manual':
                $date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );

                // Populate empty from or to dates.
                if ( ! empty( $args['order_modified_dates_from'] ) ) {
                    $order_modified_dates_from = woo_ce_format_order_date( $args['order_modified_dates_from'] );
                } else {
                    // Default From date to the first Order.
                    $order_modified_dates_from = woo_ce_get_order_first_gmdate( $date_format );
                }
                if ( ! empty( $args['order_modified_dates_to'] ) ) {
                    // @mod - Testing the fix for the last date missing out, this line is the bane of 2017 getting it to play nice with all date formats.
                    // $order_modified_dates_to = woo_ce_format_order_date( gmdate( $date_format, gmdate( strtotime( "+1 day", strtotime( woo_ce_format_order_date( $args['order_modified_dates_to'] ) ) ) ) ) );.
                    $order_modified_dates_to = woo_ce_format_order_date( $args['order_modified_dates_to'] );
                    $order_modified_dates_to = apply_filters( 'woo_ce_get_orders_order_modified_dates_to', $order_modified_dates_to, $args['order_modified_dates_to'] );
                } else {
                    // Default To date to tomorrow.
                    $order_modified_dates_to = woo_ce_format_order_date( woo_ce_get_order_date_filter( 'today', 'to', $date_format ) );
                }

                // phpcs:disable Squiz.PHP.CommentedOutCode.Found

                /*
                // @mod - I don't think this is relevant since we now send order_dates_to as 23:59:59 instead of 00:00:00, confirm in 2.4+.
                // Check if the same date has been provided for both order_dates_from and order_dates_to.
                if ( $order_dates_from == $order_dates_to ) {
                    // Add a day to order_dates_to.
                    $order_dates_to = woo_ce_format_order_date( gmdate( $date_format, gmdate( strtotime( "+1 day", strtotime( $order_dates_to ) ) ) ) );
                }
                */

                // phpcs:enable Squiz.PHP.CommentedOutCode.Found

                // Check if the provided dates match the date format.
                $validate_from = woo_ce_validate_order_date( $order_modified_dates_from, woo_ce_format_order_date( $date_format ) );
                $validate_to   = woo_ce_validate_order_date( $order_modified_dates_to, woo_ce_format_order_date( $date_format ) );
                if ( ! $validate_from && ! $validate_to ) {
                    $order_modified_dates_from = woo_ce_format_order_date( gmdate( $date_format, strtotime( $order_modified_dates_from, current_time( 'timestamp', 0 ) ) ) ); // phpcs:ignore
                    $order_modified_dates_to   = woo_ce_format_order_date( gmdate( $date_format, strtotime( $order_modified_dates_to, current_time( 'timestamp', 0 ) ) ) ); // phpcs:ignore
                }

                // WP_Query only accepts D-m-Y so we must format dates to that, fun times....
                if ( 'd/m/Y' !== $date_format ) {
                    $date_format = woo_ce_format_order_date( $date_format );
                    if ( function_exists( 'date_create_from_format' ) && function_exists( 'date_format' ) ) {

                        // Check if we've been passed a mixed format.

                        if ( strpos( $order_modified_dates_from, '-' ) !== false ) {
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'order_modified_dates_from' ) );
                                woo_ce_error_log( sprintf( 'Debug: %s', $order_modified_dates_from ) );
                            }
                            $date_check = explode( '-', $order_modified_dates_from );
                            if ( checkgmdate( $date_check[0], $date_check[1], $date_check[2] ) ) {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 1' ) );
                                }
                                $order_modified_dates_from = date_create_from_format( 'm-d-Y', $order_modified_dates_from );
                                if ( $order_modified_dates_from ) {
                                    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                        woo_ce_error_log( sprintf( 'Debug: %s', 'option 1: accepted' ) );
                                        woo_ce_error_log( sprintf( 'Debug: %s', print_r( $date_check, true ) ) );
                                    }
                                    $order_modified_dates_from = date_format( $order_modified_dates_from, 'd-m-Y' );
                                    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                        woo_ce_error_log( sprintf( 'Debug: %s', $order_modified_dates_from ) );
                                    }
                                }
                            } elseif ( checkgmdate( $date_check[1], $date_check[0], $date_check[2] ) ) {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 2' ) );
                                }
                                $order_modified_dates_from = date_create_from_format( 'd-m-Y', $order_modified_dates_from );
                                if ( $order_modified_dates_from ) {
                                    $order_modified_dates_from = date_format( $order_modified_dates_from, 'd-m-Y' );
                                }
                            } else {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 3' ) );
                                }
                                $order_modified_dates_from = date_create_from_format( $date_format, $order_modified_dates_from );
                                if ( $order_modified_dates_from ) {
                                    $order_modified_dates_from = date_format( $order_modified_dates_from, 'd-m-Y' );
                                }
                            }
                        } else {
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'option 4' ) );
                            }
                            $order_modified_dates_from = date_create_from_format( $date_format, $order_modified_dates_from );
                            if ( $order_modified_dates_from ) {
                                $order_modified_dates_from = date_format( $order_modified_dates_from, 'd-m-Y' );
                            }
                        }
                        unset( $date_check );

                        if ( strpos( $order_modified_dates_to, '-' ) !== false ) {
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'order_modified_dates_to' ) );
                                woo_ce_error_log( sprintf( 'Debug: %s', $order_modified_dates_to ) );
                            }
                            $date_check = explode( '-', $order_modified_dates_to );
                            if ( checkgmdate( $date_check[0], $date_check[1], $date_check[2] ) ) {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 1' ) );
                                }
                                $order_modified_dates_to = date_create_from_format( 'm-d-Y', $order_modified_dates_to );
                                if ( $order_modified_dates_to ) {
                                    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                        woo_ce_error_log( sprintf( 'Debug: %s', 'option 1: accepted' ) );
                                        woo_ce_error_log( sprintf( 'Debug: %s', print_r( $date_check, true ) ) );
                                    }
                                    $order_modified_dates_to = date_format( $order_modified_dates_to, 'd-m-Y' );
                                    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                        woo_ce_error_log( sprintf( 'Debug: %s', $order_modified_dates_to ) );
                                    }
                                }
                            } elseif ( checkgmdate( $date_check[1], $date_check[0], $date_check[2] ) ) {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 2' ) );
                                }
                                $order_modified_dates_to = date_create_from_format( 'd-m-Y', $order_modified_dates_to );
                                if ( $order_modified_dates_to ) {
                                    $order_modified_dates_to = date_format( $order_modified_dates_to, 'd-m-Y' );
                                }
                            } else {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 3' ) );
                                }
                                $order_modified_dates_to = date_create_from_format( $date_format, $order_modified_dates_to );
                                if ( $order_modified_dates_to ) {
                                    $order_modified_dates_to = date_format( $order_modified_dates_to, 'd-m-Y' );
                                }
                            }
                        } else {
                            $order_modified_dates_to = date_create_from_format( $date_format, $order_modified_dates_to );
                            if ( $order_modified_dates_to ) {
                                $order_modified_dates_to = date_format( $order_modified_dates_to, 'd-m-Y' );
                            }
                        }
                        unset( $date_check );

                    }
                }
                break;

            case 'variable':
                $order_filter_modified_date_variable        = $args['order_modified_dates_filter_variable'];
                $order_filter_modified_date_variable_length = $args['order_modified_dates_filter_variable_length'];
                if ( false !== $order_filter_modified_date_variable && false !== $order_filter_modified_date_variable_length ) {
                    $timestamp                 = strtotime( sprintf( '-%d %s', $order_filter_modified_date_variable, $order_filter_modified_date_variable_length ), current_time( 'timestamp', 0 ) ); // phpcs:ignore
                    $order_modified_dates_from = gmdate( 'd-m-Y-H-i-s', mktime( gmdate( 'H', $timestamp ), gmdate( 'i', $timestamp ), gmdate( 's', $timestamp ), gmdate( 'n', $timestamp ), gmdate( 'd', $timestamp ), gmdate( 'Y', $timestamp ) ) );
                    $order_modified_dates_to   = woo_ce_get_order_date_filter( 'today', 'to' );
                    unset( $order_filter_modified_date_variable, $order_filter_modified_date_variable_length, $timestamp );
                }
                break;

            default:
                $order_modified_dates_from = false;
                $order_modified_dates_to   = false;
                break;

        }
        if ( ! empty( $order_modified_dates_from ) && ! empty( $order_modified_dates_to ) ) {
            // From.
            $order_modified_dates_from = explode( '-', $order_modified_dates_from );
            // Check that a valid date was provided.
            if ( isset( $order_modified_dates_from[0] ) && isset( $order_modified_dates_from[1] ) && isset( $order_modified_dates_from[2] ) ) {
                $order_modified_dates_from = array(
                    'year'   => absint( $order_modified_dates_from[2] ),
                    'month'  => absint( $order_modified_dates_from[1] ),
                    'day'    => absint( $order_modified_dates_from[0] ),
                    'hour'   => ( isset( $order_modified_dates_from[3] ) ? $order_modified_dates_from[3] : 0 ),
                    'minute' => ( isset( $order_modified_dates_from[4] ) ? $order_modified_dates_from[4] : 0 ),
                    'second' => ( isset( $order_modified_dates_from[5] ) ? $order_modified_dates_from[5] : 0 ),
                );
                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                    woo_ce_error_log( sprintf( 'Debug: %s', 'order_modified_dates_from' ) );
                    woo_ce_error_log( sprintf( 'Debug: %s', print_r( $order_modified_dates_from, true ) ) );
                }
            } else {
                $order_modified_dates_from = false;
            }
            // To.
            $order_modified_dates_to = explode( '-', $order_modified_dates_to );
            // Check that a valid date was provided.
            if ( isset( $order_modified_dates_to[0] ) && isset( $order_modified_dates_to[1] ) && isset( $order_modified_dates_to[2] ) ) {
                $order_modified_dates_to = array(
                    'year'   => absint( $order_modified_dates_to[2] ),
                    'month'  => absint( $order_modified_dates_to[1] ),
                    'day'    => absint( $order_modified_dates_to[0] ),
                    'hour'   => ( isset( $order_modified_dates_to[3] ) ? $order_modified_dates_to[3] : 23 ),
                    'minute' => ( isset( $order_modified_dates_to[4] ) ? $order_modified_dates_to[4] : 59 ),
                    'second' => ( isset( $order_modified_dates_to[5] ) ? $order_modified_dates_to[5] : 59 ),
                );
                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                    woo_ce_error_log( sprintf( 'Debug: %s', 'order_modified_dates_to' ) );
                    woo_ce_error_log( sprintf( 'Debug: %s', print_r( $order_modified_dates_to, true ) ) );
                }
                // Check for bad values.
                switch ( $order_modified_dates_filter ) {

                    case 'last_month':
                        if ( $order_modified_dates_from['month'] !== $order_modified_dates_to['month'] ) {
                            $order_modified_dates_to['hour']   = 0;
                            $order_modified_dates_to['minute'] = 0;
                            $order_modified_dates_to['second'] = 0;
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_modified_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'order_modified_dates_to, last_month override' ) );
                                woo_ce_error_log( sprintf( 'Debug: %s', print_r( $order_modified_dates_to, true ) ) );
                            }
                        }
                        break;

                }
            } else {
                $order_modified_dates_to = false;
            }
        }

        $order_status        = ( isset( $args['order_status'] ) ? $args['order_status'] : array() );
        $user_ids            = ( isset( $args['order_customer'] ) ? $args['order_customer'] : false );
        $billing_country     = ( isset( $args['order_billing_country'] ) ? $args['order_billing_country'] : false );
        $shipping_country    = ( isset( $args['order_shipping_country'] ) ? $args['order_shipping_country'] : false );
        $order_items         = ( isset( $args['order_items'] ) ? $args['order_items'] : array() );
        $order_items_digital = ( isset( $args['order_items_digital'] ) ? $args['order_items_digital'] : false );
    }
    $args = array(
        'type'             => 'shop_order',
        'orderby'          => $orderby,
        'order'            => $order,
        'offset'           => $offset,
        'limit'            => $limit_volume,
        'return'           => 'ids',
        'suppress_filters' => false,
        'status'           => apply_filters( 'woo_ce_order_post_status', array_keys( wc_get_order_statuses() ) ),
    );

    $woocommerce_version = woo_get_woo_version();

    // Filter Orders by Order ID.
    if ( ! empty( $post_ids ) ) {

        // Trim any leading hash character.
        $post_ids = str_replace( '#', '', $post_ids );

        $has_post_id_ranges = false;
        // Check for Order ID ranges (e.g. 100-199).
        if ( strpos( $post_ids, '-' ) !== false ) {
            $has_post_id_ranges = true;
        }

        // Explode the Order IDs.
        $post_ids = explode( ',', $post_ids );
        if ( $has_post_id_ranges ) {
            foreach ( $post_ids as $key => $order_id ) {
                if ( strpos( $order_id, '-' ) !== false ) {
                    $order_id_ranges = explode( '-', $order_id );
                    if ( false !== $order_id_ranges ) {
                        $order_id_ranges = range( $order_id_ranges[0], $order_id_ranges[1] );
                        unset( $post_ids[ $key ] );
                        $post_ids = array_merge( $post_ids, $order_id_ranges );
                    }
                    unset( $order_id_ranges );
                }
            }
        }
        unset( $has_post_id_ranges );
    }
    // Filter Orders by Product.
    if ( ! empty( $product ) ) {
        if ( is_array( $post_ids ) ) {
            $post_ids = array_merge( $post_ids, woo_ce_get_product_assoc_order_ids( $product ) );
        } else {
            $post_ids = woo_ce_get_product_assoc_order_ids( $product );
        }
    }

    // These Filters are only applied to WP_Query if Orders are not filtered by date.
    if (
        empty( $order_dates_from ) &&
        empty( $order_dates_to ) &&
        empty( $order_modified_dates_from ) &&
        empty( $order_modified_dates_to )
    ) {

        // Filter Orders by Order ID.
        if (
            ! empty( $post_ids ) &&
            empty( $product )
        ) {
            // Check if we're looking up a Sequential Order Number.
            if ( woo_ce_detect_export_plugin( 'seq' ) || woo_ce_detect_export_plugin( 'seq_pro' ) ) {
                $args['meta_query'][] = array(
                    'key'     => ( woo_ce_detect_export_plugin( 'seq_pro' ) ? '_order_number_formatted' : '_order_number' ),
                    'value'   => $post_ids,
                    'compare' => 'IN',
                );
            } else {
                $size = count( $post_ids );
                if ( $size > 1 ) {
                    $args['post__in'] = array_map( 'absint', (array) $post_ids );
                } else {
                    $args['p'] = absint( $post_ids[0] );
                }
            }
        }

        // Filter Orders by Product.
        if ( ! empty( $product ) ) {
            if ( ! empty( $post_ids ) ) {
                $size = count( $post_ids );
                if ( $size > 1 ) {
                    $args['post__in'] = array_map( 'absint', (array) $post_ids );
                } else {
                    $args['p'] = absint( $post_ids[0] );
                }
            } else {
                // This means that no Post ID's were returned, fail the export.
                $args['post__in'] = array( 0 );
            }
        }
    }
    // Filter Orders by Payment Method.
    if ( ! empty( $payment ) ) {
        $args['meta_query'][] = array(
            'key'   => '_payment_method',
            'value' => $payment,
        );
    }
    // Filter Orders by Order Status.
    if ( ! empty( $order_status ) ) {
        $args['status'] = $order_status;
    }
    if ( ! empty( $user_ids ) ) {
        // Check if we're dealing with a string or list of users.
        if ( is_string( $user_ids ) ) {
            $user_ids = explode( ',', $user_ids );
        }
        $user_emails = array();
        foreach ( $user_ids as $user_id ) {
            $user = get_userdata( $user_id );
            if ( $user ) {
                $user_emails[] = $user->user_email;
            }
        }
        if ( ! empty( $user_emails ) ) {
            $args['meta_query'][] = array(
                'key'   => '_billing_email',
                'value' => $user_emails,
            );
        }
        unset( $user_id, $user_emails );
    }
    // Filter Orders by Billing Country.
    if ( ! empty( $billing_country ) ) {
        $args['meta_query'][] = array(
            'key'   => '_billing_country',
            'value' => $billing_country,
        );
    }
    // Filter Orders by Shipping Country.
    if ( ! empty( $shipping_country ) ) {
        $args['meta_query'][] = array(
            'key'   => '_shipping_country',
            'value' => $shipping_country,
        );
    }
    // Filter Order dates.
    if ( ! empty( $order_dates_from ) && ! empty( $order_dates_to ) ) {
        $args['date_query'] = array(
            array(
                'column'    => apply_filters( 'woo_ce_get_orders_filter_order_dates_column', 'date_created_gmt' ),
                'before'    => $order_dates_to,
                'after'     => $order_dates_from,
                'inclusive' => true,
            ),
        );
    }
    // Filter Order Modified dates.
    if ( ! empty( $order_modified_dates_from ) && ! empty( $order_modified_dates_to ) ) {
        if ( apply_filters( 'woo_ce_date_filter_or_relation', false ) ) {
            $args['date_query']['relation'] = 'OR';
        }
        $args['date_query'][] = array(
            array(
                'column'    => apply_filters( 'woo_ce_get_orders_filter_order__modified_dates_column', 'post_modified' ),
                'before'    => $order_modified_dates_to,
                'after'     => $order_modified_dates_from,
                'inclusive' => true,
            ),
        );
    }
    // Check if we are filtering Orders by Last Export.
    if ( 'last_export' === $order_dates_filter ) {
        $args['meta_query'][] = array(
            'key'     => '_woo_cd_exported',
            'compare' => 'NOT EXISTS',
        );
    }

    // Check if we are only filtering Orders by the Guest User Role.
    if ( ! empty( $user_roles ) ) {
        // Check if we are only filtering Orders by the Guest User Role.
        $size = count( $export->args['order_user_roles'] );
        if ( 1 === $size && 'guest' === $export->args['order_user_roles'][0] ) {
            $args['meta_query'][] = array(
                'key'   => '_customer_user',
                'value' => 0,
            );
        }
    }

    $orders = array();

    // Allow other developers to bake in their own filters.
    $args = apply_filters( 'woo_ce_get_orders_args', $args, $export );

    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_args', false ) ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_orders(), args: ' . print_r( $args, true ) ) );
    }

    $order_query = new WC_Order_Query( $args );
    $order_ids   = $order_query->get_orders();

    if ( WOO_CE_LOGGING ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_orders(): before $order_ids->posts loop: ' . ( time() - $export->start_time ) ) );
    }

    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_orders_args', false ) ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_orders(), order_ids: ' . print_r( $order_ids->posts, true ) ) );
    }

    if ( ! empty( $order_ids ) ) {

        // Check if the Filter Orders filter still needs to be run.
        if (
            (
                ! empty( $order_dates_from ) &&
                ! empty( $order_dates_to )
            ) && ! empty( $post_ids )
        ) {
            $order_ids = array_intersect( $order_ids, $post_ids );
        }

        foreach ( $order_ids as $order_id ) {

            $order = wc_get_order( $order_id );

            // Check if we need to spin up Order Items.
            if (
                ! empty( $product_category ) ||
                ! empty( $product_tag ) ||
                ! empty( $product_brand ) ||
                ! empty( $order_items_digital )
            ) {

                // Get a list of Order Item ID's linked to this Order.
                $order_items = woo_ce_get_order_item_ids( $order_id );

            }

            // Filter Orders by User Roles.
            $order_user_id = $order->get_customer_id();
            if ( ! empty( $user_roles ) ) {
                $user_ids = array();
                $size     = count( $export->args['order_user_roles'] );
                // Check if we are only filtering Orders by the Guest User Role.
                if ( ( 1 === $size && 'guest' === $export->args['order_user_roles'][0] ) === false ) {
                    for ( $i = 0; $i < $size; $i++ ) {
                        $args     = array(
                            'role'   => $export->args['order_user_roles'][ $i ],
                            'fields' => 'ID',
                        );
                        $user_id  = get_users( $args );
                        $user_ids = array_merge( $user_ids, $user_id );
                    }
                    if ( ! in_array( $order_user_id, $user_ids, true ) ) {
                        unset( $order );
                        continue;
                    }
                }
            }

            // Filter Orders by Coupons.
            // $order_coupon_code = woo_ce_get_order_assoc_coupon( $order_id, $order );
            $order_coupon_code = null;
            if ( ! empty( $coupon ) ) {
                $coupon_ids = array();
                $size       = count( $export->args['order_coupon'] );
                for ( $i = 0; $i < $size; $i++ ) {
                    $coupon_ids[] = strtolower( get_the_title( $coupon[ $i ] ) );
                }
                if ( ! in_array( strtolower( $order_coupon_code ), $coupon_ids, true ) ) {
                    unset( $order );
                    continue;
                }
            }

            // Filter Orders on Product Category.
            if ( ! empty( $product_category ) ) {
                if ( ! empty( $order_items ) ) {
                    $term_taxonomy = 'product_cat';
                    $args          = array(
                        'fields' => 'ids',
                    );
                    $category_ids  = array();
                    foreach ( $order_items as $order_item ) {
                        $product_categories = wp_get_post_terms( $order_item->product_id, $term_taxonomy, $args );
                        if ( $product_categories ) {
                            $category_ids = array_merge( $category_ids, $product_categories );
                            unset( $product_categories );
                        }
                    }
                    if ( count( array_intersect( $product_category, $category_ids ) ) === 0 ) {
                        unset( $order );
                        continue;
                    }
                    unset( $category_ids );
                } else {
                    // If the Order has no Order Items assigned to it we can safely remove it from the export.
                    unset( $order );
                    continue;
                }
            }

            // Filter Orders by Product Tag.
            if ( ! empty( $product_tag ) ) {
                if ( ! empty( $order_items ) ) {
                    $term_taxonomy = 'product_tag';
                    $args          = array(
                        'fields' => 'ids',
                    );
                    $tag_ids       = array();
                    foreach ( $order_items as $order_item ) {
                        $product_tags = wp_get_post_terms( $order_item->product_id, $term_taxonomy, $args );
                        if ( $product_tags ) {
                            $tag_ids = array_merge( $tag_ids, $product_tags );
                            unset( $product_tags );
                        }
                    }
                    if ( empty( $tag_ids ) || count( array_intersect( $product_tag, $tag_ids ) ) === 0 ) {
                        unset( $order );
                        continue;
                    }
                    unset( $tag_ids );
                } else {
                    // If the Order has no Order Items assigned to it we can safely remove it from the export.
                    unset( $order );
                    continue;
                }
            }

            // Filter Orders by Product Brand.
            if ( ! empty( $product_brand ) ) {
                if ( ! empty( $order_items ) ) {
                    $term_taxonomy = apply_filters( 'woo_ce_brand_term_taxonomy', 'product_brand' );
                    $args          = array(
                        'fields' => 'ids',
                    );
                    $brand_ids     = array();
                    foreach ( $order_items as $order_item ) {
                        $product_brands = wp_get_post_terms( $order_item->product_id, $term_taxonomy, $args );
                        if ( $product_brands ) {
                            $brand_ids = array_merge( $brand_ids, $product_brands );
                            unset( $product_brands );
                        }
                    }
                    if ( empty( $brand_ids ) || count( array_intersect( $product_brand, $brand_ids ) ) === 0 ) {
                        unset( $order );
                        continue;
                    }
                    unset( $brand_ids );
                } else {
                    // If the Order has no Order Items assigned to it we can safely remove it from the export.
                    unset( $order );
                    continue;
                }
            }

            // Filter Orders by Shipping Method.
            if ( ! empty( $shipping ) ) {
                $shipping_id = woo_ce_get_order_assoc_shipping_method_meta( $order_id, $order );
                // Shipping Zones add a suffix number separated by a : character.
                if ( strpos( $shipping_id, ':' ) !== false ) {
                    foreach ( $shipping as $shipping_method ) {
                        if ( strpos( $shipping_id, $shipping_method ) === false ) {
                            unset( $order );
                            break;
                        }
                    }
                    if ( isset( $order ) === false ) {
                        continue;
                    }
                } elseif ( ! in_array( $shipping_id, $shipping, true ) ) {
                    unset( $order );
                    continue;
                }
                unset( $shipping_id );
            }

            // Filter Orders by Product Vendor.
            if ( ! empty( $product_vendor ) ) {
                // Get a list of Orders by the selected Product Vendors.
                $vendor_ids = woo_ce_get_product_vendor_assoc_orders( $product_vendor );
                if ( ! empty( $vendor_ids ) ) {
                    if ( ! in_array( $order_id, $vendor_ids, true ) ) {
                        unset( $order );
                        continue;
                    }
                }
                unset( $vendor_ids );
            }

            // Filter Orders by Digital Products.
            if ( ! empty( $order_items_digital ) ) {

                $downloadable = $order->has_downloadable_item();

                switch ( $order_items_digital ) {

                    // Filter Orders by Digital-only Orders.
                    case 'include_digital':
                        $exclude = false;
                        if ( false === $downloadable ) {
                            $exclude = true;
                        }

                        if ( $exclude ) {
                            // Do not include this Order ID in the export.
                            unset( $order );
                            continue 2;
                        }
                        break;

                    // Exclude Orders with Digital Products from Orders export.
                    case 'exclude_digital':
                        $exclude = false;
                        if ( true === $downloadable ) {
                            $exclude = true;
                        }
                        if ( $exclude ) {
                            // Do not include this Order ID in the export.
                            unset( $order );
                            continue 2;
                        }
                        break;

                    // Exclude Digital-only Orders from Orders export.
                    case 'exclude_digital_only':
                        $exclude            = array();
                        $downloadable_items = $order->get_downloadable_items();
                        if ( ! empty( $downloadable_items ) ) {
                            $items_total              = count( $order->get_items( 'line-item' ) );
                            $downloadable_items_total = count( $downloadable_items );
                            // Remove if there are no physical Products in that Order.
                            if ( $items_total === $downloadable_items_total ) {
                                unset( $order );
                                continue 2;
                            }
                        }
                        break;
                }
                unset( $exclude );
            }

            // phpcs:disable Squiz.PHP.CommentedOutCode.Found

            /*
            // Filter Orders by Booking Start Date.
            // @mod - Commented out for WC 3.0 compatibility, confirm in 2.3+.
            $order->id = apply_filters( 'woo_ce_get_order_id', $order->id );
            if ( $order->id )
                $orders[] = $order->id;
            */

            // phpcs:enable Squiz.PHP.CommentedOutCode.Found

            $order_id = apply_filters( 'woo_ce_get_order_id', $order_id );
            if ( $order_id ) {
                $orders[] = $order_id;
            }

            // Mark this Order as exported if Since last export Date filter is used.
            if (
                'last_export' === $order_dates_filter &&
                ! empty( $order_id )
            ) {

                $order->update_meta_data( '_woo_cd_exported', 1 );
                $order->save_meta_data();

                $order_flag_notes = woo_ce_get_option( 'order_flag_notes', 0 );

                // Override if this is a Scheduled Export.
                $scheduled_export = ( $export->scheduled_export ? absint( get_transient( WOO_CE_PREFIX . '_scheduled_export_id' ) ) : 0 );
                if ( $scheduled_export ) {
                    $single_export_order_flag_notes = get_post_meta( $scheduled_export, '_filter_order_flag_notes', true );
                    if ( false !== $single_export_order_flag_notes ) {
                        $order_flag_notes = $single_export_order_flag_notes;
                    }
                    unset( $single_export_order_flag_notes );
                }

                $order_flag_notes = apply_filters( 'woo_ce_get_orders_order_flag_notes', $order_flag_notes, $order_id, $scheduled_export );

                // Allow Plugin/Theme authors to run additional tasks (e.g. change Order Status, etc.) when marking an Order as exported.
                do_action( 'woo_ce_get_orders_mark_order_exported', $order_id, $scheduled_export );

                if ( $order_flag_notes ) {
                    // Check if a $order instance is available.
                    if ( ! isset( $order ) ) {
                        // Get WooCommerce Order details.
                        $order = woo_ce_get_order_wc_data( $order_id );
                    }

                    // Add an Order Note.
                    $note = __( 'Order was exported successfully.', 'woocommerce-exporter' );
                    if ( method_exists( $order, 'add_order_note' ) ) {
                        $order->add_order_note( $note );
                    }
                    unset( $note );
                }
            }
        }

        // Only populate the $export Global if it is an export.
        if ( isset( $export ) ) {
            $export->total_rows = count( $orders );
            if ( ! empty( $order_ids ) ) {
                // Check if we're looking up a Sequential Order Number.
                if ( woo_ce_detect_export_plugin( 'seq' ) || woo_ce_detect_export_plugin( 'seq_pro' ) ) {
                    $export->order_ids_raw = $orders;
                }
            }
        }
    }

    if ( WOO_CE_LOGGING ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_orders(): after $order_ids->posts loop: ' . ( time() - $export->start_time ) ) );
    }

    switch ( $export_type ) {

        case 'order':
            if ( ! WOO_CE_DEBUG ) {
                if ( 'last_export' === $order_dates_filter ) {
                    // Save the Order ID's list to a WordPress Transient incase the export fails.
                    woo_ce_update_option( 'exported', $orders );
                }
            }
            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_orders', false ) ) {
                woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_orders(): $order_ids: ' . print_r( $orders, true ) ) );
            }
            return $orders;
            break; // phpcs:ignore Squiz.PHP.NonExecutableCode.Unreachable

        case 'customer':
            $customers = array();
            if ( ! empty( $orders ) ) {
                foreach ( $orders as $order_id ) {
                    $customers[ $order_id ] = new stdClass();
                    $wc_order               = wc_get_order( $order_id );
                    $order                  = woo_ce_get_order_data( $order_id, 'customer', $export->args );
                    $duplicate_key          = woo_ce_is_duplicate_customer( $customers, $order );
                    if ( $duplicate_key ) {
                        $customers[ $duplicate_key ]->total_spent = $customers[ $duplicate_key ]->total_spent + woo_ce_format_price( $wc_order->get_total() );
                        ++$customers[ $duplicate_key ]->total_orders;
                        if ( strtolower( $order['payment_status'] ) === 'completed' ) {
                            ++$customers[ $duplicate_key ]->completed_orders;
                        }
                    } else {
                        // Convert $order from array to object.
                        foreach ( $order as $key => $value ) {
                            $customers[ $order_id ]->{$key} = $value;
                        }
                        $customers[ $order_id ]->total_spent      = woo_ce_format_price( $wc_order->get_total() );
                        $customers[ $order_id ]->completed_orders = 0;
                        if ( strtolower( $order['payment_status'] ) === 'completed' ) {
                            $customers[ $order_id ]->completed_orders = 1;
                        }
                        $customers[ $order_id ]->total_orders = 1;
                    }
                }
            }
            return $customers;
            break; // phpcs:ignore Squiz.PHP.NonExecutableCode.Unreachable
    }
    //phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_print_r
}

/**
 * Override WordPress WP_Query WHERE clause to support WooCommerce Order Status.
 *
 * @param string $where The WHERE clause of the query.
 * @return string The WHERE clause of the query.
 */
function woo_ce_wp_query_order_where_override( $where ) {

    global $export, $wpdb;

    $order_status = ( isset( $export->args['order_status'] ) ? $export->args['order_status'] : false );

    // Skip this if we're dealing with stock WordPress Post Status.
    if ( count( array_intersect( array( 'trash', 'publish' ), $order_status ) ) ) {
        return $where;
    }

    // Let's add in our custom Post Status parameters.
    if ( ! empty( $order_status ) ) {
        foreach ( $order_status as $key => $status ) {
            if ( empty( $status ) ) {
                unset( $order_status[ $key ] );
                continue;
            }
            $order_status[ $key ] = ' ' . $wpdb->posts . '.post_status = "$status"';
        }
        if ( ! empty( $order_status ) ) {
            $where .= ' AND (' . join( ' OR ', $order_status ) . ')';
        }
    }

    return $where;
}

/**
 * Returns WooCommerce Order data associated to a specific Order.
 *
 * @param int $order_id Order ID.
 * @return array $order_wc_data Order WooCommerce data.
 */
function woo_ce_get_order_wc_data( $order_id = 0 ) {

    if ( ! empty( $order_id ) ) {
        if ( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
            $order                      = ( function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : get_post( $order_id ) );
            $order->status              = ( method_exists( $order, 'get_status' ) ? $order->get_status() : false );
            $order->post_status         = ( method_exists( $order, 'get_status' ) ? $order->get_status() : false );
            $order->order_date          = ( method_exists( $order, 'get_date_created' ) ? $order->get_date_created() : false );
            $order->order_modified_date = ( method_exists( $order, 'get_date_modified' ) ? $order->get_date_modified() : false );
            $order->customer_message    = ( method_exists( $order, 'get_customer_note' ) ? $order->get_customer_note() : false );
        } else {
            $order = ( class_exists( 'WC_Order' ) ? new WC_Order( $order_id ) : get_post( $order_id ) );
        }
        return $order;
    }
}

/**
 * Get data for an order.
 *
 * @param int    $order_id    The ID of the order.
 * @param string $export_type The type of export. Accepts 'order' or 'item'.
 * @param array  $args        An array of arguments.
 * @param array  $fields      An array of fields.
 * @return array              An array of order data.
 */
function woo_ce_get_order_data( $order_id = 0, $export_type = 'order', $args = array(), $fields = array() ) {
    global $export;

    // Check if this is a pre-WooCommerce 2.2 instance.
    $woocommerce_version = woo_get_woo_version();

    $defaults = array(
        'order_items'       => 'combined',
        'order_items_types' => array_keys( woo_ce_get_order_items_types() ),
    );
    $args     = wp_parse_args( $args, $defaults );

    // Get WooCommerce Order details.
    $order      = wc_get_order( $order_id );
    $order_data = array();

    $date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );

    $order_data['payment_status'] = $order->get_status();

    $order_data['post_status'] = woo_ce_format_post_status( $order->get_status() );
    $order_data['user_id']     = $order->get_user_id();
    if ( 0 === $order_data['user_id'] ) {
        $order_data['user_id'] = '';
    } else {
        $order_data['user_name'] = woo_ce_get_username( $order->get_user_id() );
        $order_data['user_role'] = woo_ce_format_user_role_label( woo_ce_get_user_role( $order->get_user_id() ) );
    }
    $order_data['purchase_total'] = $order->get_total();
    $order_data['refund_total']   = $order->get_total_refunded();
    $order_data['refund_tax']     = $order->get_type() === 'shop_subscription' ? 0 : $order->get_total_tax_refunded();
    $order_data['refund_date']    = ( ! empty( $order_data['refund_total'] ) ? woo_ce_get_order_assoc_refund_gmdate( $order ) : '' );
    $order_data['order_currency'] = $order->get_currency();

    // Order billing details.
    if ( ! apply_filters( 'woo_ce_get_order_data_legacy_billing_address', version_compare( $woocommerce_version, '3.0', '<' ) ) ) {
        // WC: 3.0+ Order billing address.
        $billing_address = ( method_exists( $order, 'get_address' ) ? $order->get_address( 'billing' ) : false );
        if ( ! empty( $billing_address ) ) {
            $order_data['billing_first_name'] = $billing_address['first_name'];
            $order_data['billing_last_name']  = $billing_address['last_name'];
            $order_data['billing_company']    = $billing_address['company'];
            $order_data['billing_address_1']  = $billing_address['address_1'];
            $order_data['billing_address_2']  = $billing_address['address_2'];
            $order_data['billing_city']       = $billing_address['city'];
            $order_data['billing_postcode']   = $billing_address['postcode'];
            $order_data['billing_state']      = $billing_address['state'];
            $order_data['billing_country']    = $billing_address['country'];
            $order_data['billing_email']      = $billing_address['email'];
            $order_data['billing_phone']      = $billing_address['phone'];
        }
        unset( $billing_address );
    } else {
        // WC: Pre-3.0 Order billing address.
        $order_data['billing_first_name'] = get_post_meta( $order_id, '_billing_first_name', true );
        $order_data['billing_last_name']  = get_post_meta( $order_id, '_billing_last_name', true );
        $order_data['billing_company']    = get_post_meta( $order_id, '_billing_company', true );
        $order_data['billing_address_1']  = get_post_meta( $order_id, '_billing_address_1', true );
        $order_data['billing_address_2']  = get_post_meta( $order_id, '_billing_address_2', true );
        $order_data['billing_city']       = get_post_meta( $order_id, '_billing_city', true );
        $order_data['billing_postcode']   = get_post_meta( $order_id, '_billing_postcode', true );
        $order_data['billing_state']      = get_post_meta( $order_id, '_billing_state', true );
        $order_data['billing_country']    = get_post_meta( $order_id, '_billing_country', true );
        $order_data['billing_phone']      = get_post_meta( $order_id, '_billing_phone', true );
        $order_data['billing_email']      = get_post_meta( $order_id, '_billing_email', true );
    }
    if ( ! empty( $order_data['billing_first_name'] ) && ! empty( $order_data['billing_first_name'] ) ) {
        $order_data['billing_full_name'] = $order_data['billing_first_name'] . ' ' . $order_data['billing_last_name'];
    }
    if ( ! empty( $order_data['billing_address_2'] ) ) {
        $order_data['billing_address'] = sprintf( apply_filters( 'woo_ce_get_order_data_billing_address', '%s %s' ), $order_data['billing_address_1'], $order_data['billing_address_2'] );
    } else {
        $order_data['billing_address'] = $order_data['billing_address_1'];
    }
    $order_data['billing_state_full']   = woo_ce_expand_state_name( $order_data['billing_country'], $order_data['billing_state'] );
    $order_data['billing_country_full'] = woo_ce_expand_country_name( $order_data['billing_country'] );

    // If the e-mail address is empty check if the Order has a User assigned to it.
    if ( empty( $order_data['billing_email'] ) ) {
        // Check if a User ID has been assigned.
        if ( ! empty( $order_data['user_id'] ) ) {
            $user = woo_ce_get_user_data( $order_data['user_id'] );
            // Check if the User is valid and e-mail assigned to User.
            if ( isset( $user->email ) ) {
                $order_data['billing_email'] = $user->email;
            }
        }
    }

    // Order shipping details.
    if ( ! apply_filters( 'woo_ce_get_order_data_legacy_shipping_address', version_compare( $woocommerce_version, '3.0', '<' ) ) ) {
        // WC: 3.0+ Order shipping address.
        $shipping_address = ( method_exists( $order, 'get_address' ) ? $order->get_address( 'shipping' ) : false );
        if ( ! empty( $shipping_address ) ) {
            $order_data['shipping_first_name'] = $shipping_address['first_name'];
            $order_data['shipping_last_name']  = $shipping_address['last_name'];
            $order_data['shipping_company']    = $shipping_address['company'];
            $order_data['shipping_address_1']  = $shipping_address['address_1'];
            $order_data['shipping_address_2']  = $shipping_address['address_2'];
            $order_data['shipping_city']       = $shipping_address['city'];
            $order_data['shipping_postcode']   = $shipping_address['postcode'];
            $order_data['shipping_state']      = $shipping_address['state'];
            $order_data['shipping_country']    = $shipping_address['country'];
        }
        unset( $shipping_address );
    } else {
        // WC: Pre-3.0 Order shipping address.
        $order_data['shipping_first_name'] = get_post_meta( $order_id, '_shipping_first_name', true );
        $order_data['shipping_last_name']  = get_post_meta( $order_id, '_shipping_last_name', true );
        $order_data['shipping_company']    = get_post_meta( $order_id, '_shipping_company', true );
        $order_data['shipping_address']    = '';
        $order_data['shipping_address_1']  = get_post_meta( $order_id, '_shipping_address_1', true );
        $order_data['shipping_address_2']  = get_post_meta( $order_id, '_shipping_address_2', true );
        $order_data['shipping_city']       = get_post_meta( $order_id, '_shipping_city', true );
        $order_data['shipping_postcode']   = get_post_meta( $order_id, '_shipping_postcode', true );
        $order_data['shipping_state']      = get_post_meta( $order_id, '_shipping_state', true );
        $order_data['shipping_country']    = get_post_meta( $order_id, '_shipping_country', true );
    }
    if ( ! empty( $order_data['shipping_first_name'] ) && ! empty( $order_data['shipping_last_name'] ) ) {
        $order_data['shipping_full_name'] = $order_data['shipping_first_name'] . ' ' . $order_data['shipping_last_name'];
    }
    if ( ! empty( $order_data['shipping_address_2'] ) ) {
        $order_data['shipping_address'] = sprintf( apply_filters( 'woo_ce_get_order_data_shipping_address', '%s %s' ), $order_data['shipping_address_1'], $order_data['shipping_address_2'] );
    } else {
        $order_data['shipping_address'] = $order_data['shipping_address_1'];
    }
    $order_data['shipping_state_full']   = woo_ce_expand_state_name( $order_data['shipping_country'], $order_data['shipping_state'] );
    $order_data['shipping_country_full'] = woo_ce_expand_country_name( $order_data['shipping_country'] );

    if ( 'order' === $export_type ) {

        $order_data['post_id']        = $order->get_id();
        $order_data['purchase_id']    = $order->get_id();
        $order_data['order_discount'] = $order->get_total_discount();
        $order_data['coupon_code']    = woo_ce_get_order_assoc_coupon( $order_id );
        if ( ! empty( $attributesorder_data['coupon_code'] ) ) {
            $coupon = wc_get_coupon_id_by_code( $order_data['coupon_code'] );
            if ( null !== $coupon ) {
                $order_data['coupon_description'] = $coupon->get_description();
                $order_data['coupon_expiry_date'] = woo_ce_format_date( $coupon->get_date_expires(), $date_format );
            }
        }
        $order_data['order_sales_tax']    = $order->get_total_tax();
        $order_data['order_shipping_tax'] = $order->get_shipping_tax();
        $order_data['shipping_cost']      = $order->get_shipping_total();
        $order_data['shipping_incl_tax']  = ( $order_data['shipping_cost'] + $order_data['order_shipping_tax'] );
        $order_data['shipping_excl_tax']  = ( $order_data['shipping_cost'] - $order_data['order_shipping_tax'] );
        $order_data['purchase_total_tax'] = ( $order_data['order_sales_tax'] + $order_data['order_shipping_tax'] - $order_data['refund_tax'] );

        $tax_rates = $order->get_taxes();
        if ( ! empty( $order_data['purchase_total_tax'] ) && ! empty( $tax_rates ) ) {
            foreach ( $tax_rates as $tax_rate ) {
                $order_data[ 'purchase_total_tax_rate_' . $tax_rate->get_rate_id() ] = woo_ce_format_price( $tax_rate->get_tax_total(), $order->get_currency() );
            }
        }
        $order_data['purchase_total']          = $order_data['purchase_total'] - $order_data['refund_total'];
        $order_data['order_subtotal_excl_tax'] = ( $order_data['purchase_total'] - $order_data['purchase_total_tax'] );
        $order_data['purchase_subtotal']       = $order_data['order_subtotal_excl_tax'] - $order_data['shipping_cost'];
        // Order Tax Percentage - Order Total - Total Tax / Total Tax.
        $order_data['order_tax_percentage'] = 0;
        if ( ! empty( $order_data['purchase_total_tax'] ) && ! empty( $order_data['purchase_total'] ) ) {
            $order_tax_percentage = apply_filters( 'woo_ce_override_order_tax_percentage_format', '%d%%' );

            if ( ! empty( $tax_rates ) ) {
                foreach ( $tax_rates as $tax_rate ) {
                    // Take the Rate ID and fetch the Rate % from the WooCommerce Tax Rates table.
                    $order_data['order_tax_percentage'] = sprintf( $order_tax_percentage, $tax_rate->get_rate_percent() );
                    break;
                }
            }
        }
        $order_data['purchase_total']          = woo_ce_format_price( $order_data['purchase_total'], $order_data['order_currency'] );
        $order_data['order_sales_tax']         = woo_ce_format_price( $order_data['order_sales_tax'], $order_data['order_currency'] );
        $order_data['order_shipping_tax']      = woo_ce_format_price( $order_data['order_shipping_tax'], $order_data['order_currency'] );
        $order_data['purchase_subtotal']       = woo_ce_format_price( $order_data['purchase_subtotal'], $order_data['order_currency'] );
        $order_data['order_discount']          = woo_ce_format_price( $order_data['order_discount'], $order_data['order_currency'] );
        $order_data['order_subtotal_excl_tax'] = woo_ce_format_price( $order_data['order_subtotal_excl_tax'], $order_data['order_currency'] );
        $order_data['refund_total']            = woo_ce_format_price( $order_data['refund_total'], $order_data['order_currency'] );
        $order_data['payment_status']          = woo_ce_format_order_status( $order_data['payment_status'] );
        $order_data['payment_gateway_id']      = $order->get_payment_method();
        $order_data['payment_gateway']         = woo_ce_format_order_payment_gateway( $order_data['payment_gateway_id'] );
        $order_data['payment_gateway']         = ! empty( $order_data['payment_gateway_id'] ) ? $order->get_payment_method_title() : __( 'N/A', 'woocommerce-exporter' );
        $order_data['shipping_method_id']      = woo_ce_get_order_assoc_shipping_method_meta( $order_id, $order );
        $order_data['shipping_instance_id']    = woo_ce_get_order_assoc_shipping_method_meta( $order_id, $order, 'instance_id' );
        $order_data['shipping_method']         = $order->get_shipping_method();
        $order_data['shipping_cost']           = woo_ce_format_price( $order_data['shipping_cost'], $order_data['order_currency'] );
        $order_data['shipping_excl_tax']       = woo_ce_format_price( $order_data['shipping_excl_tax'], $order_data['order_currency'] );
        $order_data['purchase_total_tax']      = woo_ce_format_price( $order_data['purchase_total_tax'], $order_data['order_currency'] );

        $order_data['shipping_weight_total'] = 0;
        $order_data['order_key']             = $order->get_order_key();
        $order_data['transaction_id']        = $order->get_transaction_id();
        $order_data['created_via']           = $order->get_created_via();
        $order_data['cart_hash']             = $order->get_cart_hash();
        $order_data['purchase_date']         = ( function_exists( 'wc_format_datetime' ) ? wc_format_datetime( $order->get_date_created(), $date_format ) : woo_ce_format_date( $order->get_date_created() ) );
        $order_data['modified_date']         = ( function_exists( 'wc_format_datetime' ) ? wc_format_datetime( $order->get_date_modified(), $date_format ) : woo_ce_format_date( $order->get_date_modified() ) );
        $order_data['purchase_time']         = ( function_exists( 'wc_format_datetime' ) ? wc_format_datetime( $order->get_date_created(), get_option( 'time_format' ) ) : mysql2gmdate( 'H:i:s', $order->get_date_created() ) );
        $order_data['ip_address']            = $order->get_customer_ip_address();
        $order_data['browser_agent']         = $order->get_customer_user_agent();
        $order_data['has_downloads']         = $order->has_downloadable_item();
        $order_data['has_downloaded']        = 0;

        // Order Downloads.
        $order_downloads = $order->get_downloadable_items();
        if ( ! empty( $order_downloads ) ) {
            foreach ( $order_downloads as $order_download ) {
                $download = new WC_Customer_Download( $order_download );
                if ( $download->get_download_count() > 0 ) {
                    $order_data['has_downloaded'] = 1;
                    break;
                }
            }
        }

        $order_data['has_downloads']     = woo_ce_format_switch( $order_data['has_downloads'] );
        $order_data['has_downloaded']    = woo_ce_format_switch( $order_data['has_downloaded'] );
        $order_data['customer_notes']    = '';
        $order_data['order_notes']       = '';
        $order_data['total_quantity']    = 0;
        $order_data['total_order_items'] = 0;

        // Order Notes.
        $order_notes = woo_ce_get_order_assoc_notes( $order_id );
        if ( $order_notes ) {
            if ( WOO_CE_DEBUG ) {
                $order_data['order_notes'] = implode( $export->category_separator, $order_notes );
            } else {
                $order_data['order_notes'] = implode( "\n", $order_notes );
            }
        }

        // Customer Notes.
        $order_notes = woo_ce_get_order_assoc_notes( $order_id, 'customer_note' );
        if ( $order_notes ) {
            if ( WOO_CE_DEBUG ) {
                $order_data['customer_notes'] = implode( $export->category_separator, $order_notes );
            } else {
                $order_data['customer_notes'] = implode( "\n", $order_notes );
            }
        }

        // PayPal.
        $order_data['paypal_payer_paypal_address'] = $order->get_meta( 'Payer PayPal address', true, 'edit' );
        $order_data['paypal_payer_first_name']     = $order->get_meta( 'Payer first name', true, 'edit' );
        $order_data['paypal_payer_last_name']      = $order->get_meta( 'Payer last name', true, 'edit' );
        $order_data['paypal_payment_type']         = $order->get_meta( 'Payment type', true, 'edit' );
        $order_data['paypal_payment_status']       = $order->get_meta( '_paypal_status', true, 'edit' );

        $order_items = woo_ce_get_order_items( $order, $args['order_items_types'] );
        if ( ! empty( $order_items ) ) {
            $order_data['total_order_items'] = count( $order_items );
            if ( 'combined' === $args['order_items'] ) {
                $order_items_data = array(
                    'order_items_id'                       => '',
                    'order_items_product_id'               => '',
                    'order_items_variation_id'             => '',
                    'order_items_sku'                      => '',
                    'order_items_name'                     => '',
                    'order_items_variation'                => '',
                    'order_items_image_embed'              => '',
                    'order_items_description'              => '',
                    'order_items_excerpt'                  => '',
                    'order_items_publish_date'             => '',
                    'order_items_modified_date'            => '',
                    'order_items_tax_class'                => '',
                    'order_items_quantity'                 => '',
                    'order_items_total'                    => '',
                    'order_items_subtotal'                 => '',
                    'order_items_rrp'                      => '',
                    'order_items_discount'                 => '',
                    'order_items_stock'                    => '',
                    'order_items_shipping_class'           => '',
                    'order_items_tax'                      => '',
                    'order_items_tax_percentage'           => '',
                    'order_items_tax_subtotal'             => '',
                    'order_items_refund_subtotal'          => '',
                    'order_items_refund_subtotal_incl_tax' => '',
                    'order_items_refund_quantity'          => '',
                    'order_items_type'                     => '',
                    'order_items_type_id'                  => '',
                    'order_items_category'                 => '',
                    'order_items_tag'                      => '',
                    'order_items_weight'                   => '',
                    'order_items_height'                   => '',
                    'order_items_width'                    => '',
                    'order_items_length'                   => '',
                    'order_items_total_sales'              => '',
                    'order_items_total_weight'             => '',
                    'refund_items_refunded_by'             => '',
                    'refund_items_refunded_payment'        => '',
                    'refund_items_refund_reason'           => '',
                    'refund_items_refund_amount'           => '',
                    'refund_items_prices_include_tax'      => '',
                );

                if ( ! empty( $order_items ) ) {
                    foreach ( $order_items as $order_item ) {
                        $order_item_discount = isset( $order_item['rrp'] ) && isset( $order_item['quantity'] ) && isset( $order_item['total'] ) ? round( ( (int) $order_item['rrp'] * (int) $order_item['quantity'] ) - (int) $order_item['total'], 2, PHP_ROUND_HALF_DOWN ) : '';

                        $order_items_data['order_items_id']                       .= isset( $order_item['id'] ) ? $order_item['id'] : '';
                        $order_items_data['order_items_product_id']               .= isset( $order_item['product_id'] ) ? $order_item['product_id'] : '';
                        $order_items_data['order_items_variation_id']             .= isset( $order_item['variation_id'] ) ? $order_item['variation_id'] : '';
                        $order_items_data['order_items_sku']                      .= isset( $order_item['sku'] ) ? $order_item['sku'] : '';
                        $order_items_data['order_items_name']                     .= isset( $order_item['name'] ) ? $order_item['name'] : '';
                        $order_items_data['order_items_variation']                .= isset( $order_item['variation'] ) ? $order_item['variation'] : '';
                        $order_items_data['order_items_image_embed']              .= isset( $order_item['image_embed'] ) ? $order_item['image_embed'] : '';
                        $order_items_data['order_items_description']              .= isset( $order_item['description'] ) ? woo_ce_format_description_excerpt( $order_item['description'] ) : '';
                        $order_items_data['order_items_excerpt']                  .= isset( $order_item['excerpt'] ) ? woo_ce_format_description_excerpt( $order_item['excerpt'] ) : '';
                        $order_items_data['order_items_publish_date']             .= isset( $order_item['publish_date'] ) ? $order_item['publish_date'] : '';
                        $order_items_data['order_items_modified_date']            .= isset( $order_item['modified_date'] ) ? $order_item['modified_date'] : '';
                        $order_items_data['order_items_tax_class']                .= isset( $order_item['tax_class'] ) ? $order_item['tax_class'] : '';
                        $order_items_data['order_items_quantity']                 .= isset( $order_item['quantity'] ) ? $order_item['quantity'] : '';
                        $order_items_data['order_items_total']                    .= isset( $order_item['total'] ) ? $order_item['total'] : '';
                        $order_items_data['order_items_subtotal']                 .= isset( $order_item['subtotal'] ) ? $order_item['subtotal'] : '';
                        $order_items_data['order_items_rrp']                      .= isset( $order_item['rrp'] ) ? $order_item['rrp'] : '';
                        $order_items_data['order_items_discount']                 .= $order_item_discount;
                        $order_items_data['order_items_stock']                    .= isset( $order_item['stock'] ) ? $order_item['stock'] : '';
                        $order_items_data['order_items_shipping_class']           .= isset( $order_item['shipping_class'] ) ? $order_item['shipping_class'] : '';
                        $order_items_data['order_items_tax']                      .= isset( $order_item['tax'] ) ? $order_item['tax'] : '';
                        $order_items_data['order_items_tax_percentage']           .= isset( $order_item['tax_percentage'] ) ? $order_item['tax_percentage'] : '';
                        $order_items_data['order_items_tax_subtotal']             .= isset( $order_item['tax_subtotal'] ) ? $order_item['tax_subtotal'] : '';
                        $order_items_data['order_items_refund_subtotal']          .= isset( $order_item['refund_subtotal'] ) ? $order_item['refund_subtotal'] : '';
                        $order_items_data['order_items_refund_subtotal_incl_tax'] .= isset( $order_item['refund_subtotal_incl_tax'] ) ? $order_item['refund_subtotal_incl_tax'] : '';
                        $order_items_data['order_items_refund_quantity']          .= isset( $order_item['refund_quantity'] ) ? $order_item['refund_quantity'] : '';
                        $order_items_data['order_items_type']                     .= isset( $order_item['type'] ) ? $order_item['type'] : '';
                        $order_items_data['order_items_type_id']                  .= isset( $order_item['type_id'] ) ? $order_item['type_id'] : '';
                        $order_items_data['order_items_category']                 .= isset( $order_item['category'] ) ? $order_item['category'] : '';
                        $order_items_data['order_items_tag']                      .= isset( $order_item['tag'] ) ? $order_item['tag'] : '';
                        $order_items_data['order_items_weight']                   .= isset( $order_item['weight'] ) ? $order_item['weight'] : '';
                        $order_items_data['order_items_height']                   .= isset( $order_item['height'] ) ? $order_item['height'] : '';
                        $order_items_data['order_items_width']                    .= isset( $order_item['width'] ) ? $order_item['width'] : '';
                        $order_items_data['order_items_length']                   .= isset( $order_item['length'] ) ? $order_item['length'] : '';
                        $order_items_data['order_items_total_sales']              .= isset( $order_item['total_sales'] ) ? $order_item['total_sales'] : '';
                        $order_items_data['order_items_total_weight']             .= isset( $order_item['total_weight'] ) ? $order_item['total_weight'] : '';
                        // Add Order Item weight to Shipping Weight.
                        if ( isset( $order_item['total_weight'] ) && '' !== $order_item['total_weight'] ) {
                            $order_data['shipping_weight_total'] += $order_item['total_weight'];
                        }
                        if ( in_array( 'refund', $args['order_items_types'], true ) ) {
                            $order_items_data['refund_items_refunded_by']        .= ( isset( $order_item['refunded_by'] ) ? $order_item['refunded_by'] : '' );
                            $order_items_data['refund_items_refunded_payment']   .= ( isset( $order_item['refunded_payment'] ) ? $order_item['refunded_payment'] : '' );
                            $order_items_data['refund_items_refund_reason']      .= ( isset( $order_item['refund_reason'] ) ? $order_item['refund_reason'] : '' );
                            $order_items_data['refund_items_refund_amount']      .= ( isset( $order_item['refund_amount'] ) ? $order_item['refund_amount'] : '' );
                            $order_items_data['refund_items_prices_include_tax'] .= ( isset( $order_item['prices_include_tax'] ) ? $order_item['prices_include_tax'] : '' );
                        }

                        // Add separator to each item.
                        foreach ( $order_items_data as $key => $order_item_data ) {
                            $order_items_data[ $key ] = $order_item_data . $export->category_separator;
                        }
                    }
                    $order_items_data['order_items_id']                       = substr( $order_items_data['order_items_id'], 0, -1 );
                    $order_items_data['order_items_product_id']               = substr( $order_items_data['order_items_product_id'], 0, -1 );
                    $order_items_data['order_items_variation_id']             = substr( $order_items_data['order_items_variation_id'], 0, -1 );
                    $order_items_data['order_items_sku']                      = substr( $order_items_data['order_items_sku'], 0, -1 );
                    $order_items_data['order_items_name']                     = substr( $order_items_data['order_items_name'], 0, -1 );
                    $order_items_data['order_items_variation']                = substr( $order_items_data['order_items_variation'], 0, -1 );
                    $order_items_data['order_items_image_embed']              = substr( $order_items_data['order_items_image_embed'], 0, -1 );
                    $order_items_data['order_items_description']              = substr( $order_items_data['order_items_description'], 0, -1 );
                    $order_items_data['order_items_excerpt']                  = substr( $order_items_data['order_items_excerpt'], 0, -1 );
                    $order_items_data['order_items_publish_date']             = substr( $order_items_data['order_items_publish_date'], 0, -1 );
                    $order_items_data['order_items_modified_date']            = substr( $order_items_data['order_items_modified_date'], 0, -1 );
                    $order_items_data['order_items_tax_class']                = substr( $order_items_data['order_items_tax_class'], 0, -1 );
                    $order_items_data['order_items_quantity']                 = substr( $order_items_data['order_items_quantity'], 0, -1 );
                    $order_items_data['order_items_total']                    = substr( $order_items_data['order_items_total'], 0, -1 );
                    $order_items_data['order_items_subtotal']                 = substr( $order_items_data['order_items_subtotal'], 0, -1 );
                    $order_items_data['order_items_rrp']                      = substr( $order_items_data['order_items_rrp'], 0, -1 );
                    $order_items_data['order_items_discount']                 = substr( $order_items_data['order_items_discount'], 0, -1 );
                    $order_items_data['order_items_stock']                    = substr( $order_items_data['order_items_stock'], 0, -1 );
                    $order_items_data['order_items_shipping_class']           = substr( $order_items_data['order_items_shipping_class'], 0, -1 );
                    $order_items_data['order_items_tax']                      = isset( $order_items_data['tax'] ) ? substr( $order_items_data['tax'], 0, -1 ) : '';
                    $order_items_data['order_items_tax_percentage']           = isset( $order_items_data['tax_percentage'] ) ? substr( $order_items_data['tax_percentage'], 0, -1 ) : '';
                    $order_items_data['order_items_tax_subtotal']             = isset( $order_items_data['tax_subtotal'] ) ? substr( $order_items_data['tax_subtotal'], 0, -1 ) : '';
                    $order_items_data['order_items_refund_subtotal']          = isset( $order_items_data['refund_subtotal'] ) ? substr( $order_items_data['refund_subtotal'], 0, -1 ) : '';
                    $order_items_data['order_items_refund_subtotal_incl_tax'] = isset( $order_items_data['refund_subtotal_incl_tax'] ) ? substr( $order_items_data['refund_subtotal_incl_tax'], 0, -1 ) : '';
                    $order_items_data['order_items_refund_quantity']          = isset( $order_items_data['refund_quantity'] ) ? substr( $order_items_data['refund_quantity'], 0, -1 ) : '';
                    $order_items_data['order_items_type']                     = substr( $order_items_data['order_items_type'], 0, -1 );
                    $order_items_data['order_items_type_id']                  = substr( $order_items_data['order_items_type_id'], 0, -1 );
                    $order_items_data['order_items_category']                 = substr( $order_items_data['order_items_category'], 0, -1 );
                    $order_items_data['order_items_tag']                      = substr( $order_items_data['order_items_tag'], 0, -1 );
                    $order_items_data['order_items_weight']                   = substr( $order_items_data['order_items_weight'], 0, -1 );
                    $order_items_data['order_items_height']                   = substr( $order_items_data['order_items_height'], 0, -1 );
                    $order_items_data['order_items_width']                    = substr( $order_items_data['order_items_width'], 0, -1 );
                    $order_items_data['order_items_length']                   = substr( $order_items_data['order_items_length'], 0, -1 );
                    $order_items_data['order_items_total_sales']              = substr( $order_items_data['order_items_total_sales'], 0, -1 );
                    $order_items_data['order_items_total_weight']             = substr( $order_items_data['order_items_total_weight'], 0, -1 );
                    if ( in_array( 'refund', $args['order_items_types'], true ) ) {
                        $order_items_data['refund_items_refunded_by']        = substr( $order_items_data['refund_items_refunded_by'], 0, -1 );
                        $order_items_data['refund_items_refunded_payment']   = substr( $order_items_data['refund_items_refunded_payment'], 0, -1 );
                        $order_items_data['refund_items_refund_reason']      = substr( $order_items_data['refund_items_refund_reason'], 0, -1 );
                        $order_items_data['refund_items_refund_amount']      = substr( $order_items_data['refund_items_refund_amount'], 0, -1 );
                        $order_items_data['refund_items_prices_include_tax'] = substr( $order_items_data['refund_items_prices_include_tax'], 0, -1 );
                    }
                }

                $order_data = array_merge( $order_data, $order_items_data );

                $order_data = apply_filters( 'woo_ce_order_items_combined', $order_data, $order_items, $order );

            } elseif ( 'unique' === $args['order_items'] ) {
                if ( ! empty( $order_items ) ) {
                    $i = 1;
                    foreach ( $order_items as $order_item ) {
                        $order_item_discount = isset( $order_item['rrp'] ) && isset( $order_item['quantity'] ) && isset( $order_item['total'] ) ? round( ( (int) $order_item['rrp'] * (int) $order_item['quantity'] ) - (int) $order_item['total'], 2, PHP_ROUND_HALF_DOWN ) : '';

                        $order_data[ 'order_item_' . $i . '_id' ]                       = isset( $order_item['id'] ) ? $order_item['id'] : '';
                        $order_data[ 'order_item_' . $i . '_product_id' ]               = isset( $order_item['product_id'] ) ? $order_item['product_id'] : '';
                        $order_data[ 'order_item_' . $i . '_variation_id' ]             = isset( $order_item['variation_id'] ) ? $order_item['variation_id'] : '';
                        $order_data[ 'order_item_' . $i . '_sku' ]                      = isset( $order_item['sku'] ) ? $order_item['sku'] : '';
                        $order_data[ 'order_item_' . $i . '_name' ]                     = isset( $order_item['name'] ) ? $order_item['name'] : '';
                        $order_data[ 'order_item_' . $i . '_variation' ]                = isset( $order_item['variation'] ) ? $order_item['variation'] : '';
                        $order_data[ 'order_item_' . $i . '_image_embed' ]              = isset( $order_item['image_embed'] ) ? $order_item['image_embed'] : '';
                        $order_data[ 'order_item_' . $i . '_description' ]              = isset( $order_item['description'] ) ? woo_ce_format_description_excerpt( $order_item['description'] ) : '';
                        $order_data[ 'order_item_' . $i . '_excerpt' ]                  = isset( $order_item['excerpt'] ) ? woo_ce_format_description_excerpt( $order_item['excerpt'] ) : '';
                        $order_data[ 'order_item_' . $i . '_publish_date' ]             = isset( $order_item['publish_date'] ) ? $order_item['publish_date'] : '';
                        $order_data[ 'order_item_' . $i . '_modified_date' ]            = isset( $order_item['modified_date'] ) ? $order_item['modified_date'] : '';
                        $order_data[ 'order_item_' . $i . '_tax_class' ]                = isset( $order_item['tax_class'] ) ? $order_item['tax_class'] : '';
                        $order_data[ 'order_item_' . $i . '_quantity' ]                 = isset( $order_item['quantity'] ) ? $order_item['quantity'] : '';
                        $order_data[ 'order_item_' . $i . '_total' ]                    = isset( $order_item['total'] ) ? $order_item['total'] : '';
                        $order_data[ 'order_item_' . $i . '_subtotal' ]                 = isset( $order_item['subtotal'] ) ? $order_item['subtotal'] : '';
                        $order_data[ 'order_item_' . $i . '_rrp' ]                      = isset( $order_item['rrp'] ) ? $order_item['rrp'] : '';
                        $order_data[ 'order_item_' . $i . '_discount' ]                 = $order_item_discount;
                        $order_data[ 'order_item_' . $i . '_stock' ]                    = isset( $order_item['stock'] ) ? $order_item['stock'] : '';
                        $order_data[ 'order_item_' . $i . '_shipping_class' ]           = isset( $order_item['shipping_class'] ) ? $order_item['shipping_class'] : '';
                        $order_data[ 'order_item_' . $i . '_tax' ]                      = isset( $order_item['tax'] ) ? $order_item['tax'] : '';
                        $order_data[ 'order_item_' . $i . '_tax_percentage' ]           = isset( $order_item['tax_percentage'] ) ? $order_item['tax_percentage'] : '';
                        $order_data[ 'order_item_' . $i . '_tax_subtotal' ]             = isset( $order_item['tax_subtotal'] ) ? $order_item['tax_subtotal'] : '';
                        $order_data[ 'order_item_' . $i . '_refund_subtotal' ]          = isset( $order_item['refund_subtotal'] ) ? $order_item['refund_subtotal'] : '';
                        $order_data[ 'order_item_' . $i . '_refund_subtotal_incl_tax' ] = isset( $order_item['refund_subtotal_incl_tax'] ) ? $order_item['refund_subtotal_incl_tax'] : '';
                        $order_data[ 'order_item_' . $i . '_refund_quantity' ]          = isset( $order_item['refund_quantity'] ) ? $order_item['refund_quantity'] : '';
                        $order_data[ 'order_item_' . $i . '_type' ]                     = isset( $order_item['type'] ) ? $order_item['type'] : '';
                        $order_data[ 'order_item_' . $i . '_type_id' ]                  = isset( $order_item['type_id'] ) ? $order_item['type_id'] : '';
                        $order_data[ 'order_item_' . $i . '_category' ]                 = isset( $order_item['category'] ) ? $order_item['category'] : '';
                        $order_data[ 'order_item_' . $i . '_tag' ]                      = isset( $order_item['tag'] ) ? $order_item['tag'] : '';
                        $order_data[ 'order_item_' . $i . '_weight' ]                   = isset( $order_item['weight'] ) ? $order_item['weight'] : '';
                        $order_data[ 'order_item_' . $i . '_height' ]                   = isset( $order_item['height'] ) ? $order_item['height'] : '';
                        $order_data[ 'order_item_' . $i . '_width' ]                    = isset( $order_item['width'] ) ? $order_item['width'] : '';
                        $order_data[ 'order_item_' . $i . '_length' ]                   = isset( $order_item['length'] ) ? $order_item['length'] : '';
                        $order_data[ 'order_item_' . $i . '_total_sales' ]              = isset( $order_item['total_sales'] ) ? $order_item['total_sales'] : '';
                        $order_data[ 'order_item_' . $i . '_total_weight' ]             = isset( $order_item['total_weight'] ) ? $order_item['total_weight'] : '';
                        // Add Order Item weight to Shipping Weight.
                        if ( isset( $order_item['total_weight'] ) && '' !== $order_item['total_weight'] ) {
                            if ( is_numeric( $order_item['total_weight'] ) ) {
                                $order_data['shipping_weight_total'] += $order_item['total_weight'];
                            }
                        }

                        $order_data = apply_filters( 'woo_ce_order_items_unique', $order_data, $i, $order_item );
                        ++$i;
                    }
                }
            }
            if ( ! empty( $order_items ) ) {
                foreach ( $order_items as $order_item ) {
                    $order_data['total_quantity'] += ( isset( $order_item['quantity'] ) ? absint( $order_item['quantity'] ) : 0 );
                }
            }
        }

        // Custom Order fields.
        $custom_orders = woo_ce_get_option( 'custom_orders', '' );
        if ( ! empty( $custom_orders ) ) {
            foreach ( $custom_orders as $custom_order ) {
                if ( ! empty( $custom_order ) ) {
                    $order_data[ $custom_order ] = woo_ce_format_custom_meta( $order->get_meta( $custom_order, true, 'edit' ) );
                }
            }
        }

        // Check if the Order has a User assigned to it.
        if ( ! empty( $order_data['user_id'] ) ) {
            // Custom User fields.
            $custom_users = woo_ce_get_option( 'custom_users', '' );
            if ( ! empty( $custom_users ) ) {
                foreach ( $custom_users as $custom_user ) {
                    if ( ! empty( $custom_user ) && ! isset( $order->{$custom_user} ) ) {
                        $order_data[ $custom_user ] = woo_ce_format_custom_meta( get_user_meta( $order->get_user_id(), $custom_user, true ) );
                    }
                }
            }
            unset( $custom_users, $custom_user );
        }
    } elseif ( 'customer' === $export_type ) {

        // Check if the Order has a User assigned to it.
        if ( ! empty( $order->get_user_id() ) ) {

            // Load up the User data as other Plugins will use it too.
            $user = woo_ce_get_user_data( $order->get_user_id() );

            // WooCommerce Follow-Up Emails - http://www.woothemes.com/products/follow-up-emails/.
            if ( woo_ce_detect_export_plugin( 'wc_followupemails' ) ) {

                global $wpdb;

                if ( isset( $user->email ) ) {
                    $followup_optout_sql    = $wpdb->prepare( 'SELECT `id` FROM `' . $wpdb->prefix . 'followup_email_excludes` WHERE `email` = %s LIMIT 1', $user->email );
                    $order->followup_optout = $wpdb->get_var( $followup_optout_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }
            }

            // Custom User fields.
            $custom_users = woo_ce_get_option( 'custom_users', '' );
            if ( ! empty( $custom_users ) ) {
                foreach ( $custom_users as $custom_user ) {
                    if ( ! empty( $custom_user ) && ! isset( $order->{$custom_user} ) ) {
                        $order->{$custom_user} = woo_ce_format_custom_meta( get_user_meta( $order->user_id, $custom_user, true ) );
                    }
                }
            }
            unset( $custom_users, $custom_user );

            // Clean up.
            unset( $user );

        }

        // Allow Plugin/Theme authors to add support for additional Customer columns.
        $order = apply_filters( 'woo_ce_customer', $order, $order_id );

        // Custom Order fields.
        $custom_orders = woo_ce_get_option( 'custom_orders', '' );
        if ( ! empty( $custom_orders ) ) {
            foreach ( $custom_orders as $custom_order ) {
                if ( ! empty( $custom_order ) ) {
                    $order->{$custom_order} = esc_attr( get_user_meta( $order_id, $custom_order, true ) );
                }
            }
        }

        // Custom Customer fields.
        $custom_customers = woo_ce_get_option( 'custom_customers', '' );
        if ( ! empty( $custom_customers ) ) {
            foreach ( $custom_customers as $custom_customer ) {
                if ( ! empty( $custom_customer ) ) {
                    $order->{$custom_customer} = esc_attr( get_user_meta( $order->user_id, $custom_customer, true ) );
                }
            }
        }
    }

    // Allow Plugin/Theme authors to add support for additional Order columns.
    $order_data = apply_filters( 'woo_ce_order', $order_data, $order, $order_id );

    if ( empty( $fields ) ) {
        return $order_data;
    }

    // Trim back the Order just to requested export fields.
    $fields[] = 'id';
    if (
        'individual' === $args['order_items'] ||
        apply_filters( 'woo_ce_get_order_data_return_order_items', false )
    ) {
        $fields[] = 'order_items';
    }

    if ( ! empty( $order_data ) ) {
        $order_output = array();
        foreach ( $order_data as $key => $data ) {
            if ( in_array( $key, $fields, true ) ) {
                $order_output[ $key ] = $data;
            }
        }
    }
    return $order_output;
}

if ( ! function_exists( 'woo_ce_export_dataset_override_order' ) ) {
    /**
     * Export a dataset for an order.
     *
     * @param string $output The output.
     * @param string $export_type The export type.
     * @return string The output.
     */
    function woo_ce_export_dataset_override_order( $output = null, $export_type = null ) {
        global $export;

        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'before woo_ce_get_orders(): ' . ( time() - $export->start_time ) ) );
        }
        $orders = woo_ce_get_orders( 'order', $export->args );
        if ( ! empty( $orders ) ) {
            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_orders', false ) ) {
                woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_export_dataset_override_order(): $order_ids: ' . print_r( $orders, true ) ) ); // phpcs:ignore
            }
            if ( WOO_CE_LOGGING ) {
                woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_get_orders(): ' . ( time() - $export->start_time ) ) );
            }
            $export->total_columns = count( $export->columns );
            $size                  = count( $export->columns );
            // XML, RSS and JSON export.
            if ( in_array( $export->export_format, array( 'xml', 'rss', 'json' ), true ) ) {
                if ( ! empty( $export->fields ) ) {
                    foreach ( $orders as $order_id ) {
                        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_orders', false ) ) {
                            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_export_dataset_override_order(): $order: ' . $order_id ) );
                        }

                        if ( in_array( $export->export_format, array( 'xml', 'json' ), true ) ) {
                            $child = $output->addChild( apply_filters( 'woo_ce_export_xml_order_node', sanitize_key( $export_type ) ) );
                        } elseif ( 'rss' === $export->export_format ) {
                            $child = $output->addChild( 'item' );
                        }
                        if (
                            'json' === $export->export_format &&
                            apply_filters( 'woo_ce_export_xml_order_node_id_attribute', true )
                        ) {
                            $child->addAttribute( 'id', $order_id );
                        }
                        $args = $export->args;
                        if ( 'unique' === $export->args['order_items'] ) {
                            $args['order_items'] = 'individual';
                        }
                        $order = woo_ce_get_order_data( $order_id, 'order', $args, array_keys( $export->fields ) );

                        if ( WOO_CE_LOGGING ) {
                            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) ) );
                        }
                        if ( in_array( $export->args['order_items'], array( 'combined', 'unique' ), true ) ) {
                            if ( 'unique' === $export->args['order_items'] ) {
                                // Order items formatting: SPECK-IPHONE.
                                foreach ( array_keys( $export->fields ) as $key => $field ) {
                                    if ( isset( $order[ $field ] ) && isset( $export->columns[ $key ] ) ) {
                                        if ( ! is_array( $field ) ) {
                                            if ( woo_ce_is_xml_cdata( $order[ $field ] ) ) {
                                                $child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                            } else {
                                                $child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                            }
                                        }
                                    }
                                }

                                if ( in_array( $export->export_format, array( 'xml', 'json' ), true ) ) {
                                    $order_items_child = $child->addChild( apply_filters( 'woo_ce_export_xml_order_items_node', 'order_items' ) );
                                } elseif ( 'rss' === $export->export_format ) {
                                    $order_items_child = $child->addChild( 'order_items' );
                                }

                                $order_items = woo_ce_get_order_items( wc_get_order( $order_id ), $export->args['order_items_types'] );
                                if ( ! empty( $order_items ) ) {
                                    foreach ( $order_items as $order_item ) {
                                        if ( in_array( $export->export_format, array( 'xml', 'json' ), true ) ) {
                                            $order_item_child = $order_items_child->addChild( apply_filters( 'woo_ce_export_xml_order_item_node', 'order_item' ) );
                                        } elseif ( 'rss' === $export->export_format ) {
                                            $order_item_child = $order_items_child->addChild( 'order_item' );
                                        }
                                        foreach ( array_keys( $export->fields ) as $key => $field ) {
                                            if ( strpos( $field, 'order_items_' ) !== false ) {
                                                $field = str_replace( 'order_items_', '', $field );
                                                if ( isset( $order_item[ $field ] ) && isset( $export->columns[ $key ] ) ) {
                                                    $export->columns[ $key ] = str_replace( __( 'Order Items: ', 'woocommerce-exporter' ), '', $export->columns[ $key ] );
                                                    if ( ! is_array( $field ) ) {
                                                        if ( woo_ce_is_xml_cdata( $order_item[ $field ] ) ) {
                                                            $order_item_child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order_item[ $field ] ) ) );
                                                        } else {
                                                            $order_item_child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $order_item[ $field ] ) ) );
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                // Order items formatting: SPECK-IPHONE|INCASE-NANO|-.
                                foreach ( array_keys( $export->fields ) as $key => $field ) {
                                    if ( isset( $order[ $field ] ) && isset( $export->columns[ $key ] ) ) {
                                        if ( ! is_array( $field ) ) {
                                            if ( woo_ce_is_xml_cdata( $order[ $field ] ) ) {
                                                $child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                            } else {
                                                $child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                            }
                                        }
                                    }

                                    // phpcs:disable Squiz.PHP.CommentedOutCode.Found

                                    /*
                                    If ( ! empty( $order->order_items ) ) {
                                        foreach ( $order->order_items as $order_item ) {
                                            if ( $export->export_format == 'xml' )
                                                $order_item_child = $child->addChild( apply_filters( 'woo_ce_export_xml_order_item_node', 'order_item' ) );
                                            else if ( $export->export_format == 'rss' )
                                                $order_item_child = $child->addChild( 'order_item' );
                                            $order_item_child->addAttribute( 'id', $order->order_items_id );
                                        }
                                    }
                                    */

                                    // phpcs:enable Squiz.PHP.CommentedOutCode.Found
                                }
                            }
                        } elseif ( 'individual' === $export->args['order_items'] ) {
                            // Order items formatting: SPECK-IPHONE<br />INCASE-NANO<br />-.
                            $order_items = woo_ce_get_order_items( wc_get_order( $order_id ), $export->args['order_items_types'] );
                            if ( ! empty( $order_items ) ) {
                                foreach ( $order_items as $order_item ) {
                                    $order = apply_filters( 'woo_ce_order_items_individual', $order, $order_item );
                                    foreach ( array_keys( $export->fields ) as $key => $field ) {
                                        if ( isset( $order[ $field ] ) && isset( $export->columns[ $key ] ) ) {
                                            if ( ! is_array( $field ) ) {
                                                if ( woo_ce_is_xml_cdata( $order[ $field ] ) ) {
                                                    $child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                                } else {
                                                    $child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                foreach ( array_keys( $export->fields ) as $key => $field ) {
                                    if ( isset( $order[ $field ] ) && isset( $export->columns[ $key ] ) ) {
                                        if ( ! is_array( $field ) ) {
                                            if ( woo_ce_is_xml_cdata( $order[ $field ] ) ) {
                                                $child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                            } else {
                                                $child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // Reset the time limit count.
                        if ( function_exists( 'set_time_limit' ) ) {
                            @set_time_limit( $export->time_limit ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                        }
                    }

                    // Allow Plugin/Theme authors to add support for sorting Orders.
                    $output = apply_filters( 'woo_ce_orders_output', $output, $orders );
                }
            } else {
                // PHPExcel export.
                if ( 'individual' === $export->args['order_items'] ) {
                    $output = array();
                }
                if ( WOO_CE_LOGGING ) {
                    woo_ce_error_log( sprintf( 'Debug: %s', 'foreach $orders...: ' . ( time() - $export->start_time ) ) );
                }
                $i = 0;
                foreach ( $orders as $order_id ) {
                    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_orders', false ) ) {
                        woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_export_dataset_override_order(): $order: ' . $order_id ) );
                    }

                    if ( in_array( $export->args['order_items'], array( 'combined', 'unique' ), true ) ) {
                        // Order items formatting: SPECK-IPHONE|INCASE-NANO|-.
                        $output[] = woo_ce_get_order_data( $order_id, 'order', $export->args, array_keys( $export->fields ) );
                        if ( WOO_CE_LOGGING ) {
                            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) ) );
                        }
                    }
                    if ( 'individual' === $export->args['order_items'] ) {
                        // Order items formatting: SPECK-IPHONE<br />INCASE-NANO<br />-.
                        $order       = woo_ce_get_order_data( $order_id, 'order', $export->args, array_keys( $export->fields ) );
                        $order['id'] = $orders[ $i ];
                        if ( WOO_CE_LOGGING ) {
                            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) ) );
                        }

                        $order_items = woo_ce_get_order_items( wc_get_order( $order_id ), $export->args['order_items_types'] );
                        if ( ! empty( $order_items ) ) {
                            foreach ( $order_items as $order_item ) {
                                $order = apply_filters( 'woo_ce_order_items_individual', $order, $order_item );
                                // This fixes the Order Items for this Order Items Formatting rule.
                                $output[] = (object) (array) $order;
                                $output   = apply_filters( 'woo_ce_order_items_individual_output', $output, $order, $order_item );
                            }
                            // Allow Plugin/Theme authors to add in blank rows between Orders.
                            $output = apply_filters( 'woo_ce_order_items_individual_output_end', $output, $order );
                        } else {
                            $output[] = $order;
                        }
                    }

                    // Reset the time limit count.
                    if ( function_exists( 'set_time_limit' ) ) {
                        @set_time_limit( $export->time_limit ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                    }
                    ++$i;
                }
                unset( $i );

                // convert output to object.
                foreach ( $output as $key => $value ) {
                    $output[ $key ] = (object) $value;
                }

                // Allow Plugin/Theme authors to add support for sorting Orders.
                $output = apply_filters( 'woo_ce_orders_output', $output, $orders );

            }
            unset( $orders, $order );
        }

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_orders', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_export_dataset_override_order(): $output: ' . print_r( $output, true ) ) ); // phpcs:ignore
        }

        return $output;
    }
}

/**
 * This function overrides the order of the export dataset for the multisite export types.
 *
 * @param string $output The output.
 * @param string $export_type The export type.
 * @return string The output.
 */
function woo_ce_export_dataset_multisite_override_order( $output = null, $export_type = null ) {

    global $export;

    $sites = get_sites();
    if ( ! empty( $sites ) ) {
        foreach ( $sites as $site ) {
            switch_to_blog( $site->blog_id );
            if ( WOO_CE_LOGGING ) {
                woo_ce_error_log( sprintf( 'Debug: %s', 'before woo_ce_get_orders(): ' . ( time() - $export->start_time ) ) );
            }
            $orders = woo_ce_get_orders( 'order', $export->args );
            if ( ! empty( $orders ) ) {
                if ( WOO_CE_LOGGING ) {
                    woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_get_orders(): ' . ( time() - $export->start_time ) ) );
                }
                $export->total_columns = count( $export->columns );
                $size                  = count( $export->columns );
                // XML, RSS and JSON export.
                if ( in_array( $export->export_format, array( 'xml', 'rss', 'json' ), true ) ) {
                    if ( ! empty( $export->fields ) ) {
                        foreach ( $orders as $order_id ) {

                            if ( in_array( $export->export_format, array( 'xml', 'json' ), true ) ) {
                                $child = $output->addChild( apply_filters( 'woo_ce_export_xml_order_node', sanitize_key( $export_type ) ) );
                            } elseif ( 'rss' === $export->export_format ) {
                                $child = $output->addChild( 'item' );
                            }
                            if (
                                'json' !== $export->export_format &&
                                apply_filters( 'woo_ce_export_xml_order_node_id_attribute', true )
                            ) {
                                $child->addAttribute( 'id', $order_id );
                            }
                            $order = woo_ce_get_order_data( $order_id, 'order', $export->args, array_keys( $export->fields ) );
                            if ( WOO_CE_LOGGING ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) ) );
                            }
                            if ( in_array( $export->args['order_items'], array( 'combined', 'unique' ), true ) ) {
                                // Order items formatting: SPECK-IPHONE|INCASE-NANO|-.
                                foreach ( array_keys( $export->fields ) as $key => $field ) {
                                    if ( isset( $order[ $field ] ) && isset( $export->columns[ $key ] ) ) {
                                        if ( ! is_array( $field ) ) {
                                            if ( woo_ce_is_xml_cdata( $order[ $field ] ) ) {
                                                $child->addChild( sanitize_key( $export->columns[ $key ] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                            } else {
                                                $child->addChild( sanitize_key( $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                            }
                                        }
                                    }
                                }
                            } elseif ( 'individual' === $export->args['order_items'] ) {
                                // Order items formatting: SPECK-IPHONE<br />INCASE-NANO<br />-.
                                $order_items = woo_ce_get_order_items( wc_get_order( $order_id ), $export->args['order_items_types'] );
                                if ( ! empty( $order_items ) ) {
                                    foreach ( $order_items as $order_item ) {
                                        $order = apply_filters( 'woo_ce_order_items_individual', $order, $order_item );
                                        foreach ( array_keys( $export->fields ) as $key => $field ) {
                                            if ( isset( $order[ $field ] ) && isset( $export->columns[ $key ] ) ) {
                                                if ( ! is_array( $field ) ) {
                                                    if ( woo_ce_is_xml_cdata( $order[ $field ] ) ) {
                                                        $child->addChild( sanitize_key( $export->columns[ $key ] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                                    } else {
                                                        $child->addChild( sanitize_key( $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    unset( $order->order_items );
                                } else {
                                    foreach ( array_keys( $export->fields ) as $key => $field ) {
                                        if ( isset( $order[ $field ] ) && isset( $export->columns[ $key ] ) ) {
                                            if ( ! is_array( $field ) ) {
                                                if ( woo_ce_is_xml_cdata( $order->$field ) ) {
                                                    $child->addChild( sanitize_key( $export->columns[ $key ] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                                } else {
                                                    $child->addChild( sanitize_key( $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $order[ $field ] ) ) );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // PHPExcel export.
                    if ( 'individual' === $export->args['order_items'] && ! isset( $output ) ) {
                        $output = array();
                    }
                    foreach ( $orders as $order_id ) {
                        if ( in_array( $export->args['order_items'], array( 'combined', 'unique' ), true ) ) {
                            // Order items formatting: SPECK-IPHONE|INCASE-NANO|-.
                            $output[] = woo_ce_get_order_data( $order_id, 'order', $export->args, array_keys( $export->fields ) );
                            if ( WOO_CE_LOGGING ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) ) );
                            }
                        } elseif ( 'individual' === $export->args['order_items'] ) {
                            // Order items formatting: SPECK-IPHONE<br />INCASE-NANO<br />-.
                            $order = woo_ce_get_order_data( $order_id, 'order', $export->args, array_keys( $export->fields ) );
                            if ( WOO_CE_LOGGING ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) ) );
                            }
                            $order_items = woo_ce_get_order_items( wc_get_order( $order_id ), $export->args['order_items_types'] );
                            if ( ! empty( $order_items ) ) {
                                foreach ( $order_items as $order_item ) {
                                    $discount = isset( $order_item['rrp'] ) && isset( $order_item['quantity'] ) && isset( $order_item['total'] ) ? round( ( (int) $order_item['rrp'] * (int) $order_item['quantity'] ) - (int) $order_item['total'], 2, PHP_ROUND_HALF_DOWN ) : '';

                                    $order['order_items_id']                       = $order_item['id'];
                                    $order['order_items_product_id']               = $order_item['product_id'];
                                    $order['order_items_variation_id']             = $order_item['variation_id'];
                                    $order['order_items_sku']                      = $order_item['sku'];
                                    $order['order_items_name']                     = $order_item['name'];
                                    $order['order_items_image_embed']              = $order_item['image_embed'];
                                    $order['order_items_variation']                = $order_item['variation'];
                                    $order['order_items_description']              = $order_item['description'];
                                    $order['order_items_excerpt']                  = $order_item['excerpt'];
                                    $order['order_items_publish_date']             = $order_item['publish_date'];
                                    $order['order_items_modified_date']            = $order_item['modified_date'];
                                    $order['order_items_tax_class']                = $order_item['tax_class'];
                                    $order['order_items_quantity']                 = $order_item['quantity'];
                                    $order['order_items_total']                    = $order_item['total'];
                                    $order['order_items_subtotal']                 = $order_item['subtotal'];
                                    $order['order_items_rrp']                      = $order_item['rrp'];
                                    $order['order_items_discount']                 = $discount;
                                    $order['order_items_stock']                    = $order_item['stock'];
                                    $order['order_items_shipping_class']           = $order_item['shipping_class'];
                                    $order['order_items_tax']                      = $order_item['tax'];
                                    $order['order_items_tax_percentage']           = $order_item['tax_percentage'];
                                    $order['order_items_tax_subtotal']             = $order_item['tax_subtotal'];
                                    $order['order_items_refund_subtotal']          = $order_item['refund_subtotal'];
                                    $order['order_items_refund_subtotal_incl_tax'] = $order_item['refund_subtotal_incl_tax'];
                                    $order['order_items_refund_quantity']          = $order_item['refund_quantity'];
                                    $order['order_items_type']                     = $order_item['type'];
                                    $order['order_items_type_id']                  = $order_item['type_id'];
                                    $order['order_items_category']                 = $order_item['category'];
                                    $order['order_items_tag']                      = $order_item['tag'];
                                    $order['order_items_weight']                   = $order_item['weight'];
                                    $order['order_items_width']                    = $order_item['width'];
                                    $order['order_items_length']                   = $order_item['length'];
                                    $order['order_items_height']                   = $order_item['height'];
                                    $order['order_items_total_sales']              = $order_item['total_sales'];
                                    $order['order_items_total_weight']             = $order_item['total_weight'];
                                    // Add Order Item weight to Shipping Weight.
                                    if ( '' !== $order_item['total_weight'] && is_numeric( $order_item['total_weight'] ) ) {
                                        $order['shipping_weight_total'] += $order_item['total_weight'];
                                    }
                                    // Add Refund Order Items.
                                    if ( in_array( 'refund', $export->args['order_items_types'], true ) ) {
                                        $order = woo_ce_get_refund_order_data( $order_item, $order );
                                    }
                                    $order = apply_filters( 'woo_ce_order_items_individual', $order, $order_item );
                                    // This fixes the Order Items for this Order Items Formatting rule.
                                    $output[] = (object) (array) $order;
                                    $output   = apply_filters( 'woo_ce_order_items_individual_output', $output, $order, $order_item );
                                }
                            } else {
                                $output[] = (object) (array) $order;
                            }
                        }
                    }
                }
                unset( $orders, $order );
            }
            restore_current_blog();
        }
    }

    // Convert output to object.
    if ( ! empty( $output ) && is_array( $output ) ) {
        foreach ( $output as $key => $value ) {
            $output[ $key ] = (object) $value;
        }
    }

    return $output;
}

/**
 * Returns a list of WooCommerce Tax Rates based on existing Orders.
 *
 * @since 5.3.6 Rewrote this incredibly inefficient function not query ALL orders to get tax rates. Now, it will fetch
 * the tax rates directly from WooCommerce's WC_Tax class unless an order_id is provided, in which case it will just use
 * the tax rates from that order.
 *
 * @param int $order_id Order ID.
 * @return array
 */
function woo_ce_get_order_tax_rates( $order_id = null ) {

    if ( apply_filters( 'woo_ce_enable_order_tax_rates', true ) ) {

        $tax_rates = array();

        // Fetch tax rates for a specific order if order_id is provided.
        if ( ! empty( $order_id ) ) {
            $order = wc_get_order( $order_id );
            if ( $order ) {
                $order_taxes = $order->get_taxes();
                if ( ! empty( $order_taxes ) ) {
                    foreach ( $order_taxes as $order_tax ) {
                        $rate_id               = $order_tax->get_rate_id();
                        $tax_rates[ $rate_id ] = array(
                            'rate_id'    => $order_tax->get_rate_id(),
                            'label'      => $order_tax->get_label(),
                            'tax_class'  => WC_Tax::get_tax_class_by( 'id', $rate_id ),
                            'percentage' => $order_tax->get_rate_percent(),
                        );
                    }
                }
            }
        } else {
            // Get all tax classes.
            $tax_classes   = WC_Tax::get_tax_classes();
            $tax_classes[] = ''; // Standard rate.

            foreach ( $tax_classes as $tax_class ) {
                // Get rates for the current tax class.
                $rates = WC_Tax::get_rates_for_tax_class( $tax_class );

                foreach ( $rates as $rate ) {
                    // Ensure the rate object has the required properties.
                    if ( isset( $rate->id ) && isset( $rate->label ) && isset( $rate->tax_rate ) ) {
                        $tax_rates[ $rate->id ] = array(
                            'rate_id'    => $rate->id,
                            'label'      => $rate->label,
                            'tax_class'  => $tax_class,
                            'percentage' => $rate->tax_rate,
                        );
                    }
                }
            }
        }

        /**
         * Filter to modify the Tax Rates.
         *
         * @param array $tax_rates Array of tax rates.
         * @param int|null $order_id Order ID.
         */
        return apply_filters( 'woo_ce_get_order_tax_rates', $tax_rates, $order_id );
    }

    return array();
}

/**
 * Get the Order Item ID of refunded Order Items.
 *
 * @param int $line_item_id Line item ID.
 * @return array
 */
function woo_ce_get_order_line_item_assoc_refunds( $line_item_id = 0 ) {

    global $wpdb;

    $order_item_type  = 'line_item';
    $meta_key         = '_refunded_item_id';
    $refund_items_sql = $wpdb->prepare( 'SELECT order_itemmeta.`order_item_id` FROM `' . $wpdb->prefix . 'woocommerce_order_items` as order_items, `' . $wpdb->prefix . 'woocommerce_order_itemmeta` as order_itemmeta WHERE order_items.`order_item_id` = order_itemmeta.`order_item_id` AND order_items.`order_item_type` = %s AND order_itemmeta.`meta_key` = %s AND order_itemmeta.`meta_value` = %d', $order_item_type, $meta_key, $line_item_id );
    $refund_items     = $wpdb->get_col( $refund_items_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    return $refund_items;
}

/**
 * Return the PHP date format for the requested Order Date filter.
 *
 * @param string $filter      Date filter.
 * @param string $format      From | To.
 * @param string $date_format Date format.
 *
 * @return string
 */
function woo_ce_get_order_date_filter( $filter = '', $format = '', $date_format = 'd-m-Y' ) {

    $output = false;
    if ( ! empty( $filter ) && ! empty( $format ) ) {
        switch ( $filter ) {

            // Tomorrow.
            case 'tomorrow':
                if ( 'from' === $format ) {
                    $output = gmdate( $date_format, strtotime( 'tomorrow' ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                } else {
                    $output = gmdate( $date_format, strtotime( 'tomorrow' ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                }
                break;

            // Today.
            case 'today':
                if ( 'from' === $format ) {
                    $output = gmdate( $date_format, strtotime( 'today' ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                } else {
                    $output = gmdate( $date_format, strtotime( 'tomorrow' ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                }
                break;

            // Yesterday.
            case 'yesterday':
                if ( 'from' === $format ) {
                    $output = gmdate( $date_format, strtotime( 'yesterday' ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                } else {
                    $output = gmdate( $date_format, strtotime( 'yesterday' ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                }
                break;

            // This week.
            case 'current_week':
                if ( 'from' === $format ) {
                    $output = gmdate( $date_format, strtotime( 'last Monday' ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                } else {
                    $output = gmdate( $date_format, strtotime( 'next Monday' ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                }
                break;

            // Last week.
            case 'last_week':
                if ( 'from' === $format ) {
                    $output = gmdate( $date_format, strtotime( '-2 weeks Monday' ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                } else {
                    $output = gmdate( $date_format, strtotime( '-1 weeks Monday' ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                }
                break;

            // This month.
            case 'current_month':
                if ( 'from' === $format ) {
                    $output = gmdate( $date_format, mktime( 0, 0, 0, gmdate( 'n' ), 1 ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                } else {
                    $output = gmdate( $date_format, mktime( 0, 0, 0, ( gmdate( 'n' ) + 1 ), 0 ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                }
                break;

            // Last month.
            case 'last_month':
                if ( 'from' === $format ) {
                    $output = gmdate( $date_format, mktime( 0, 0, 0, gmdate( 'n', strtotime( '-1 month' ) ), 1, gmdate( 'Y', strtotime( '-1 month' ) ) ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                } else {
                    $output = gmdate( $date_format, mktime( 0, 0, 0, gmdate( 'n' ), 1 ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                }
                break;

            // This year.
            case 'current_year':
                if ( 'from' === $format ) {
                    $output = gmdate( $date_format, mktime( 0, 0, 0, 1, 1 ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                } else {
                    $output = gmdate( $date_format, mktime( 0, 0, 0, ( gmdate( 'n' ) + 1 ), 0 ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                }
                break;

            // Last year.
            case 'last_year':
                if ( 'from' === $format ) {
                    $output = gmdate( $date_format, mktime( 0, 0, 0, 1, 1, gmdate( 'Y', strtotime( '-1 year' ) ) ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                } else {
                    $output = gmdate( $date_format, mktime( 0, 0, 0, 13, 0, gmdate( 'Y', strtotime( '-1 year' ) ) ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
                }
                break;

            // phpcs:disable Squiz.PHP.CommentedOutCode.Found

            /*
            Case '':
                if ( $format == 'from' )
                    $output = ;
                else
                    $output = ;
                    break;
            */

            // phpcs:enable Squiz.PHP.CommentedOutCode.Found

            default:
                // translators: %s: filter.
                woo_ce_error_log( sprintf( 'Warning: %s', sprintf( __( 'Unknown Order Date filter %s provided, defaulted to none', 'woocommerce-exporter' ), $filter ) ) );
                break;

        }
    }
    return $output;
}

/**
 * Returns date of first Order received, any status.
 *
 * @param string $date_format The date format to return.
 * @return string The first date the order was placed.
 */
function woo_ce_get_order_first_date( $date_format = 'd/m/Y' ) {

    $output = gmdate( $date_format, mktime( 0, 0, 0, gmdate( 'n' ), 1 ) );

    $post_type = 'shop_order';
    $args      = array(
        'post_type'   => $post_type,
        'orderby'     => 'post_date',
        'order'       => 'ASC',
        'numberposts' => 1,
        'post_status' => 'any',
    );
    $orders    = get_posts( $args );
    if ( ! empty( $orders ) ) {
        $output = gmdate( $date_format, strtotime( $orders[0]->post_date ) );
        unset( $orders );
    }
    return $output;
}

/**
 * Returns a list of WooCommerce Order statuses.
 *
 * @return array
 */
function woo_ce_get_order_statuses() {

    $terms = false;

    // Check if the existing Transient exists.
    $cached = get_transient( WOO_CE_PREFIX . '_order_statuses' );
    if ( false === $cached ) {

        // Check if this is a WooCommerce 2.2+ instance (new Post Status).
        $woocommerce_version = woo_get_woo_version();
        if ( version_compare( $woocommerce_version, '2.2' ) >= 0 ) {
            // Convert Order Status array into our magic sauce.
            $order_statuses = ( function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : false );
            if ( ! empty( $order_statuses ) ) {
                $terms     = array();
                $post_type = 'shop_order';
                if ( apply_filters( 'woo_ce_scheduled_export_allow_order_status_count', true ) ) {
                    $posts_count = wp_count_posts( $post_type );
                    foreach ( $order_statuses as $key => $order_status ) {
                        $terms[] = (object) array(
                            'name'  => $order_status,
                            'slug'  => $key,
                            'count' => ( isset( $posts_count->$key ) ? $posts_count->$key : 0 ),
                        );
                    }
                } else {
                    foreach ( $order_statuses as $key => $order_status ) {
                        $terms[] = (object) array(
                            'name' => $order_status,
                            'slug' => $key,
                        );
                    }
                }
            }
        } else {
            $args  = array(
                'taxonomy'   => 'shop_order_status',
                'hide_empty' => false,
            );
            $terms = get_terms( $args );
            if ( empty( $terms ) || ( is_wp_error( $terms ) === true ) ) {
                $terms = array();
            }
        }
        set_transient( WOO_CE_PREFIX . '_order_statuses', $terms, HOUR_IN_SECONDS );

    } else {
        $terms = $cached;
    }
    return $terms;
}

/**
 * Returns the Shipping Method ID associated to a specific Order.
 *
 * @param int      $order_id The order ID.
 * @param WC_Order $order    The order object.
 * @param string   $meta     The meta key to retrieve. Defaults to 'method_id'.
 * @return string
 */
function woo_ce_get_order_assoc_shipping_method_meta( $order_id = 0, &$order = null, $meta = 'method_id' ) {

    $output = '';

    if ( empty( $order_id ) ) {
        return false;
    }

    if ( ! class_exists( 'WC_Order' ) ) {
        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Warning: %s', 'woo_ce_get_order_assoc_shipping_method_meta() returned false, reason: WC_Order Class does not exist' ) );
        }

        return false;
    }

    if ( null === $order ) {
        $order = new WC_Order( $order_id );
    }

    if ( ! method_exists( 'WC_Order', 'get_shipping_methods' ) ) {
        $output = get_post_meta( $order_id, '_shipping_method', true );
        if ( is_array( $output ) ) {
            $output = ( isset( $output[0] ) ? $output[0] : false );
        }
        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_assoc_shipping_method_meta(), Post meta _shipping_method returned output, output: ' . $output ) );
        }

        return apply_filters( 'woo_ce_get_order_assoc_shipping_method_id', $output );
    }

    $shipping_methods = $order->get_shipping_methods();

    foreach ( $shipping_methods as $shipping_item_id => $shipping_item ) {
        if ( isset( $shipping_item[ $meta ] ) ) {
            $output = $shipping_item[ $meta ];
        }
        if ( empty( $output ) && isset( $shipping_item['item_meta'] ) ) {
            $output = ( isset( $shipping_item['item_meta'][ $meta ] ) ? $shipping_item['item_meta'][ $meta ] : false );
            if ( is_array( $output ) ) {
                $output = ( isset( $output[0] ) ? $output[0] : false );
            }
            if ( WOO_CE_LOGGING ) {
                woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_assoc_shipping_method_meta(), WC_Order->get_shipping_methods() returned output, output: ' . $output ) );
            }
            break;
        }
        // Check if a value has been set.
        if ( ! empty( $output ) ) {
            break;
        }
    }
    unset( $shipping_methods );

    return apply_filters( 'woo_ce_get_order_assoc_shipping_method_id', $output );
}

/**
 * Get the associated download IDs for an order.
 *
 * @param int $order_id The order ID.
 * @return array The associated download IDs.
 */
function woo_ce_get_order_assoc_downloads( $order_id = 0 ) {
    global $wpdb;

    if ( ! empty( $order_id ) ) {
        $order_downloads_sql = $wpdb->prepare( 'SELECT `download_id`, `download_count` FROM `' . $wpdb->prefix . 'woocommerce_downloadable_product_permissions` WHERE `order_id` = %d', $order_id );
        $order_downloads     = $wpdb->get_results( $order_downloads_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $output              = array();
        if ( ! empty( $order_downloads ) ) {
            $output = $order_downloads;
        }
        unset( $order_downloads );
        return $output;
    }
}

/**
 * Returns Order Notes associated to a specific Order.
 *
 * @param int    $order_id The order ID.
 * @param string $note_type The type of note to retrieve (order_note or customer_note).
 * @return array An array of order notes.
 */
function woo_ce_get_order_assoc_notes( $order_id = 0, $note_type = 'order_note' ) {

    global $wpdb;

    if ( ! empty( $order_id ) ) {

        $order_notes = wc_get_order_notes( array( 'order_id' => $order_id ) );
        $output      = array();
        if ( ! empty( $order_notes ) ) {
            foreach ( $order_notes as $order_note ) {
                // Check if we are returning an order or customer note.
                $order_note->date_created = sprintf( apply_filters( 'woo_ce_get_order_assoc_notes_date', '%s %s' ), woo_ce_format_date( $order_note->date_created ), ( function_exists( 'wc_format_datetime' ) ? wc_format_datetime( $order_note->date_created, get_option( 'time_format' ) ) : mysql2gmdate( 'H:i:s', $order_note->date_created ) ) );
                if ( 'customer_note' === $note_type ) {
                    // Check if the order note is a customer one.
                    if ( absint( get_comment_meta( $order_note->id, 'is_customer_note', true ) ) === 1 ) {
                        $output[] = sprintf( apply_filters( 'woo_ce_get_order_assoc_notes_customer', '%s: %s' ), $order_note->date_created, $order_note->content );
                    }
                } elseif ( absint( get_comment_meta( $order_note->id, 'is_customer_note', true ) ) === 0 ) {
                    // Check if the order note is a customer one.
                    $output[] = sprintf( apply_filters( 'woo_ce_get_order_assoc_notes_order', '%s: %s' ), $order_note->date_created, $order_note->content );
                }
            }
        }
        return $output;
    }
}

/**
 * Retrieves the date of the associated refund for a given order.
 *
 * @param WC_Order $order The order object.
 * @return string|null
 */
function woo_ce_get_order_assoc_refund_gmdate( $order ) {

    if ( ! empty( $order ) ) {

        $refunds = $order->get_refunds();
        if ( ! empty( $refunds ) ) {
            foreach ( $refunds as $refund ) {
                if ( apply_filters( 'woo_ce_override_get_order_assoc_refund_date_filter', false ) ) {
                    // This will return the latest partial refund regardless of whether it is fully refunded or not.
                    $output = woo_ce_format_date( $refund->get_date_created() );
                } else {
                    $output = woo_ce_format_date( $refund->get_date_created() );

                    /*
                    This will limit the refund date to only Orders fully refunded
                    if ( $refund->post_excerpt == __( 'Order Fully Refunded', 'woocommerce' ) ) {
                        $output = woo_ce_format_date( $refund->post_date );
                        break;
                    }
                    */

                }
            }
        }
        return $output;
    }
}

/**
 * Returns the Coupon Code associated to a specific Order.
 *
 * @param int      $order_id The order ID.
 * @param WC_Order $order    The order object.
 * @return WC_Coupon|null The coupon object, or null if no coupon is associated with the order.
 */
function woo_ce_get_order_assoc_coupon( $order_id = 0, &$order = null ) {

    global $export;

    $output = '';

    if ( null === $order ) {
        if ( empty( $order_id ) ) {
            return $output;
        }

        if ( ! class_exists( 'WC_Order' ) ) {
            return $output;
        }

        $order = new WC_Order( $order_id );

    }

    $order_item_type = 'coupon';

    // WooCommerce > 3.7.
    if ( defined( 'WC_VERSION' ) && WC_VERSION && version_compare( WC_VERSION, '3.7', '>=' ) ) {
        if ( method_exists( $order, 'get_coupon_codes' ) ) {
            $coupons = $order->get_coupon_codes();
        }
    } elseif ( method_exists( $order, 'get_used_coupons' ) ) {
            $coupons = $order->get_used_coupons();
    }

    if ( empty( $coupons ) ) {
        return $output;
    }

    $size = count( $coupons );
    // If more than a single Coupon is assigned to this order then separate them.
    if ( $size > 1 ) {
        $output = implode( $export->category_separator, $coupons );
    } else {
        $output = $coupons[0];
    }

    return $output;
}

/**
 * Returns a list of Order ID's where a Coupon is associated.
 *
 * @param string $post_name string The post_name of the coupon to check for.
 * @return array An array of order objects.
 */
function woo_ce_get_orders_by_coupon( $post_name = '' ) {

    if ( empty( $post_name ) ) {
        return;
    }

    global $wpdb;

    $order_item_type = 'coupon';
    $order_items_sql = $wpdb->prepare( 'SELECT order_items.`order_id` as order_id FROM `' . $wpdb->prefix . 'woocommerce_order_items` as order_items WHERE order_items.`order_item_name` = %s AND order_items.`order_item_type` = %s', $post_name, $order_item_type );
    $order_items     = $wpdb->get_col( $order_items_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    if ( $order_items ) {

        // Allow Plugin/Theme authors to extend the Order ID's returned.
        $order_items = apply_filters( 'woo_ce_extend_get_orders_by_coupon', $order_items );

        return $order_items;
    }
}


/**
 * This code will add a custom field to the checkout page.
 *
 * @param array $orders The list of orders.
 * @return array The list of orders with the custom field added.
 */
function woo_ce_max_order_items( $orders = array() ) {

    $output = 0;
    if ( $orders ) {
        foreach ( $orders as $order ) {
            if ( $order->order_items ) {
                $output = count( $order->order_items[0]->name );
            }
        }
    }
    return $output;
}

/**
 * Returns a list of Order Item ID's with the order_item_type of 'line item' for a specified Order.
 *
 * @param int $order_id The ID of the order.
 * @return array The item IDs.
 */
function woo_ce_get_order_item_ids( $order_id = 0 ) {

    global $wpdb;

    if ( ! empty( $order_id ) ) {
        $order_item_type = 'line_item';
        $order_items_sql = $wpdb->prepare( 'SELECT order_items.`order_item_id` as id, order_itemmeta.`meta_value` as product_id FROM `' . $wpdb->prefix . 'woocommerce_order_items` as order_items, `' . $wpdb->prefix . "woocommerce_order_itemmeta` as order_itemmeta WHERE order_items.`order_item_id` = order_itemmeta.`order_item_id` AND order_items.`order_id` = %d AND order_items.`order_item_type` = %s AND order_itemmeta.`meta_key` IN ('_product_id')", $order_id, $order_item_type );
        $order_items     = $wpdb->get_results( $order_items_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if ( $order_items ) {
            return $order_items;
        }
    }
}

/**
 * Returns a list of Order Items for a specified Order.
 *
 * @param WC_Order $order             Order ID.
 * @param array    $order_items_types Order item types.
 * @return array
 */
function woo_ce_get_order_items( $order, $order_items_types = array( 'line_item' ) ) {

    global $export, $wpdb;

    $upload_dir = wp_upload_dir();

    if ( ! empty( $order ) ) {
        $order_id         = $order->get_id();
        $order_items_data = array();

        // Allow Plugin/Theme authors to bolt-on additional Order Items and/or add support for sorting Order Items within an Order.
        $order_items = apply_filters( 'woo_ce_get_order_items_pre', $order->get_items( $order_items_types ), $order, $export );
        if ( ! empty( $order_items ) ) {

            foreach ( $order_items as $key => $order_item ) {
                $order_item_data    = array();
                $order_item_default = array(
                    'id'                       => '',
                    'product_id'               => '',
                    'variation_id'             => '',
                    'name'                     => '-',
                    'sku'                      => '',
                    'image_embed'              => '',
                    'description'              => '',
                    'excerpt'                  => '',
                    'publish_date'             => '',
                    'modified_date'            => '',
                    'variation'                => '',
                    'quantity'                 => '',
                    'total'                    => '',
                    'subtotal'                 => '',
                    'rrp'                      => '',
                    'stock'                    => '',
                    'shipping_class'           => '',
                    'tax'                      => '',
                    'tax_percentage'           => '',
                    'tax_subtotal'             => '',
                    'tax_class'                => '',
                    'category'                 => '',
                    'tag'                      => '',
                    'weight'                   => '',
                    'height'                   => '',
                    'width'                    => '',
                    'length'                   => '',
                    'total_sales'              => '',
                    'total_weight'             => '',
                    'refund_subtotal'          => 0,
                    'refund_subtotal_incl_tax' => 0,
                    'refund_quantity'          => 0,
                );

                $order_item_data['id']           = $order_item->get_id();
                $order_item_data['name']         = $order_item->get_name();
                $order_item_data['quantity']     = $order_item->get_quantity();
                $order_item_data['tax_class']    = $order_item->get_tax_class();
                $order_item_data['tax']          = method_exists( $order_item, 'get_total_tax' ) ? woo_ce_format_price( $order_item->get_total_tax() ) : '';
                $order_item_data['tax_subtotal'] = method_exists( $order_item, 'get_subtotal_tax' ) ? woo_ce_format_price( $order_item->get_subtotal_tax() ) : '';
                $order_item_data['subtotal']     = method_exists( $order_item, 'get_subtotal' ) ? woo_ce_format_price( $order_item->get_subtotal() ) : '';
                $order_item_data['total']        = method_exists( $order_item, 'get_total' ) ? woo_ce_format_price( $order_item->get_total() ) : '';

                if ( 'line_item' === $order_item->get_type() ) {
                    $product = $order_item->get_product();

                    if ( $product && $product instanceof WC_Product ) {

                        $product_id = $order_item->get_product_id();

                        $order_item_data['product_id']    = $product_id;
                        $order_item_data['description']   = woo_ce_format_description_excerpt( $product->get_description() );
                        $order_item_data['excerpt']       = woo_ce_format_description_excerpt( $product->get_short_description() );
                        $order_item_data['publish_date']  = woo_ce_format_date( $product->get_date_created() );
                        $order_item_data['modified_date'] = woo_ce_format_date( $product->get_date_modified() );

                        // Populate the Featured Image thumbnail.
                        if ( 'xlsx' === $export->export_format ) {
                            $image_id = woo_ce_get_product_assoc_featured_image( $product_id, false, 'image_id' );
                            $metadata = wp_get_attachment_metadata( $image_id );
                            if ( $metadata ) {
                                // Override for the image embed thumbnail size; use registered WordPress image size names.
                                $thumbnail_size = apply_filters( 'woo_ce_override_embed_thumbnail_size', 'woocommerce_thumbnail' );
                                if ( isset( $metadata['sizes'][ $thumbnail_size ] ) && $metadata['sizes'][ $thumbnail_size ]['file'] ) {
                                    $image_path                     = pathinfo( $metadata['file'] );
                                    $order_item_data['image_embed'] = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][ $thumbnail_size ]['file'];
                                    // Override for using relative image embed filepath.
                                    if ( ! file_exists( trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][ $thumbnail_size ]['file'] ) || apply_filters( 'woo_ce_override_image_embed_relative_path', false ) ) {
                                        $order_item_data['image_embed'] = trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][ $thumbnail_size ]['file'];
                                    }
                                }
                            }
                        } else {
                            $order_item_data['image_embed'] = woo_ce_get_product_assoc_featured_image( $product_id, true, 'full' );
                        }

                        $order_item_data['sku']            = $product->get_sku();
                        $order_item_data['category']       = woo_ce_get_product_assoc_categories( $product_id );
                        $order_item_data['tag']            = woo_ce_get_product_assoc_tags( $product_id );
                        $order_item_data['weight']         = $product->get_weight();
                        $order_item_data['height']         = $product->get_height();
                        $order_item_data['width']          = $product->get_width();
                        $order_item_data['length']         = $product->get_length();
                        $order_item_data['total_sales']    = $product->get_total_sales();
                        $order_item_data['rrp']            = woo_ce_format_price( $product->get_regular_price() );
                        $order_item_data['stock']          = $product->get_stock_quantity();
                        $order_item_data['shipping_class'] = woo_ce_get_product_assoc_shipping_class( $product_id );
                        $order_item_data['total_weight']   = ( '' !== $order_item_data['weight'] ? $order_item_data['weight'] * $order_item_data['quantity'] : '' );

                        // Override Variable Product Type with total stock quantity of all Variations.
                        if ( 'variable' === $product->get_type() ) {
                            if ( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
                                $order_item_data['stock'] = ( method_exists( $product, 'get_stock_quantity' ) ? $product->get_stock_quantity() : $order_item_data['stock'] );
                            } else {
                                $order_item_data['stock'] = ( method_exists( $product, 'get_total_stock' ) ? $product->get_total_stock() : $order_item_data['stock'] );
                            }
                        } elseif ( 'variation' === $product->get_type() ) {
                            $variation_id                    = $order_item->get_variation_id();
                            $order_item_data['variation']    = '';
                            $order_item_data['variation_id'] = $variation_id;

                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'populating Variation' ) );
                            }

                            $variations = $product->get_variation_attributes( false );
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'global attributes: ' . print_r( $variations, true ) ) ); // phpcs:ignore
                            }

                            // Check if the Variation has a Term Taxonomy.
                            if ( ! empty( $variations ) ) {
                                // Populate the Order Items: %Attribute% Attribute fields.
                                if ( apply_filters( 'woo_ce_enable_product_attributes', true ) ) {
                                    foreach ( $variations as $attribute_key => $attribute ) {
                                        $attribute_key = sanitize_key( rawurlencode( $attribute_key ) );
                                        if ( ! isset( $order_item_data[ sprintf( 'product_attribute_%s', $attribute_key ) ] ) ) {
                                            $order_item_data[ sprintf( 'product_attribute_%s', wc_attribute_label( $attribute_key ) ) ] = $attribute;
                                        }
                                    }
                                }

                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'Global Attributes found' ) );
                                }
                                foreach ( $variations as $attribute_key => $attribute ) {

                                    $order_item_data['variation'] .= sprintf(
                                        apply_filters( 'woo_ce_get_order_items_variation_taxonomy', '%s: %s' ),
                                        apply_filters( 'woo_ce_get_order_items_variation_taxonomy_label', wc_attribute_label( $attribute_key ), $variations ),
                                        apply_filters( 'woo_ce_get_order_items_variation_taxonomy_term', $attribute, $variations )
                                    ) . '|';

                                }
                                $order_item_data['variation'] = substr( $order_item_data['variation'], 0, -1 );
                            }
                        }
                    }
                }

                if (
                    ! empty( $order_item_data['tax_class'] ) ||
                    (
                        empty( $order_item_data['tax_class'] ) &&
                        ! empty( $order_item_data['tax'] )
                    )
                ) {
                    // Tax Rates.
                    $tax_rates = $order->get_taxes();
                    if ( empty( $order_item_data['tax_class'] ) ) {
                        $order_item_data['tax_class'] = 'Standard';
                    }

                    if ( ! empty( $tax_rates ) ) {
                        foreach ( $tax_rates as $tax_rate ) {
                            $tax_class = ! empty( $tax_rate->get_tax_class() ) ? $tax_rate->get_tax_class() : 'Standard';
                            if (
                                sanitize_title_with_dashes( $tax_class ) === sanitize_title_with_dashes( $order_item_data['tax_class'] )
                            ) {
                                $order_item_data[ sprintf( 'tax_rate_%d', $tax_rate['rate_id'] ) ] = $order_item_data['tax_subtotal'];
                                if ( ! empty( $order_item_data['tax'] ) ) {
                                    $order_tax_percentage              = apply_filters( 'woo_ce_override_order_tax_percentage_format', '%d%%' );
                                    $order_item_data['tax_percentage'] = sprintf( $order_tax_percentage, $tax_rate->get_rate_percent() );
                                }
                                break;
                            }
                        }
                    }
                    unset( $tax_rates );
                }

                // Default the quantity to 1 for the Fee Order Item Type.
                if ( 'fee' === $order_item->get_type() ) {
                    $order_item_data['quantity'] = 1;
                }

                $order_item_data['type_id'] = $order_item->get_type();

                // Check for the Refund Line Item.
                $order_item_data['refund_subtotal']          = woo_ce_format_price( $order->get_total_refunded_for_item( $order_item->get_id(), $order_item->get_type() ) );
                $order_item_data['refund_subtotal_incl_tax'] = $order->get_total_refunded_for_item( $order_item->get_id(), $order_item->get_type() ) + $order->get_tax_refunded_for_item( $order_item->get_id(), $order_item->get_tax_class(), $order_item->get_type() );
                $order_item_data['refund_quantity']          = $order->get_qty_refunded_for_item( $order_item->get_id(), $order_item->get_type() );

				$order_item_data = apply_filters( 'woo_ce_order_item', $order_item_data, $order_item, $order_id );

				$order_item_data['type'] = woo_ce_format_order_item_type( $order_item->get_type() );

                // get all order item meta.
                $order_item_meta = $order_item->get_meta_data();
                if ( ! empty( $order_item_meta ) ) {
                    foreach ( $order_item_meta as $meta ) {
                        $order_item_data = apply_filters( 'woo_ce_order_item_custom_meta', $order_item_data, $meta->value, $meta->key );
                    }
                }

                $order_items_data[ $key ] = wp_parse_args( $order_item_data, $order_item_default );
            }

            if ( in_array( 'refund', $order_items_types, true ) ) {
                $order_items_data = woo_ce_get_refund_order_item_data( $order_items_data, $order );
            }

            // Allow Plugin/Theme authors to add support for filtering Order Items.
            return apply_filters( 'woo_ce_get_order_items', $order_items_data, $order_id );

        }
    }
}

/**
 * Returns refund data as a order line item.
 *
 * @param array    $order_items_data The order item data.
 * @param WC_Order $order            The order object.
 *
 * @return array The order item data.
 */
function woo_ce_get_refund_order_item_data( $order_items_data, $order ) {

    // Get the Order refunds (array of refunds).
    $order_refunds = $order->get_refunds();

    if ( null === $order_refunds ) {
        return $order_items_data;
    }

    // $i = count( $order_items_data );
    // Loop through the order refunds array.
    foreach ( $order_refunds as $refund ) {

        if ( null === $refund ) {
            continue;
        }

        // Loop through the order refund line items.
        foreach ( $refund->get_items() as $key => $item ) {

            if ( null === $item ) {
                continue;
            }
            $order_items_data[ $key ]['refund_quantity']        = ( method_exists( $item, 'get_quantity' ) ? $item->get_quantity() : false );
            $order_items_data[ $key ]['refunded_line_subtotal'] = ( method_exists( $item, 'get_subtotal' ) ? $item->get_subtotal() : false );
            $order_items_data[ $key ]['id']                     = ( method_exists( $refund, 'get_id' ) ? $refund->get_id() : false );
            $order_items_data[ $key ]['type']                   = 'order_refund';
            $order_items_data[ $key ]['tax']                    = ( method_exists( $item, 'get_total_tax' ) ? $item->get_total_tax() : false );
            $order_items_data[ $key ]['total']                  = ( method_exists( $item, 'get_total' ) ? $item->get_total() : false );
            $order_items_data[ $key ]['prices_include_tax']     = ( method_exists( $item, 'get_prices_include_tax' ) ? $item->get_prices_include_tax() : false );
            $order_items_data[ $key ]['refund_amount']          = ( method_exists( $item, 'get_subtotal' ) ? $item->get_subtotal() : false );
            $order_items_data[ $key ]['refunded_by']            = ( method_exists( $item, 'get_refunded_by' ) ? $item->get_refunded_by() : false );
            $order_items_data[ $key ]['refunded_payment']       = ( method_exists( $item, 'get_refunded_payment' ) ? $item->get_refunded_payment() : false );
            $order_items_data[ $key ]['refund_reason']          = ( method_exists( $item, 'get_reason' ) ? $item->get_reason() : false );
            $product_data                                       = ( method_exists( $item, 'get_product' ) ? $item->get_product() : false );
            if ( false !== $product_data ) {
                $order_items_data[ $key ]['product_id'] = ( method_exists( $product_data, 'get_id' ) ? $product_data->get_id() : false );
                $order_items_data[ $key ]['sku']        = ( method_exists( $product_data, 'get_sku' ) ? $product_data->get_sku() : false );
                $order_items_data[ $key ]['name']       = ( method_exists( $product_data, 'get_name' ) ? $product_data->get_name() : false );
            }
        }
    }
    return $order_items_data;
}


/**
 * Returns a list of items that have been refunded for a given order.
 *
 * @param array    $order_items Array of items that have been refunded.
 * @param WC_Order $order       Order object.
 * @return array
 */
function woo_ce_get_refund_order_data( $order_items, $order ) {
    $order['refund_items_refunded_by']        = isset( $order_items['refunded_by'] ) ? $order_items['refunded_by'] : '';
    $order['refund_items_refunded_payment']   = isset( $order_items['refunded_payment'] ) ? $order_items['refunded_payment'] : '';
    $order['refund_items_refund_reason']      = isset( $order_items['refund_reason'] ) ? $order_items['refund_reason'] : '';
    $order['refund_items_refund_amount']      = isset( $order_items['refund_amount'] ) ? $order_items['refund_amount'] : '';
    $order['refund_items_prices_include_tax'] = isset( $order_items['prices_include_tax'] ) ? $order_items['prices_include_tax'] : '';
    return $order;
}

/**
 * Returns a list of WooCommerce Order Item Types.
 *
 * @return array
 */
function woo_ce_get_order_items_types() {

    $order_item_types = array(
        'line_item' => __( 'Line Item', 'woocommerce-exporter' ),
        'coupon'    => __( 'Coupon', 'woocommerce-exporter' ),
        'fee'       => __( 'Fee', 'woocommerce-exporter' ),
        'tax'       => __( 'Tax', 'woocommerce-exporter' ),
        'shipping'  => __( 'Shipping', 'woocommerce-exporter' ),
        'refund'    => __( 'Refund', 'woocommerce-exporter' ),
    );

    // Allow Plugin/Theme authors to add support for additional Order Item types.
    $order_item_types = apply_filters( 'woo_ce_order_item_types', $order_item_types );

    return $order_item_types;
}

/**
 * Return the Order Status for a specified Order.
 *
 * @param int $order_id The order ID.
 * @return string The order status.
 */
function woo_ce_get_order_status( $order_id = 0 ) {

    global $export;

    $output = '';
    // Check if this is a WooCommerce 2.2+ instance (new Post Status).
    $woocommerce_version = woo_get_woo_version();
    if ( version_compare( $woocommerce_version, '2.2' ) >= 0 ) {
        $output = get_post_status( $order_id );
        $terms  = ( function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array() );
        if ( isset( $terms[ $output ] ) ) {
            $output = $terms[ $output ];
        }
    } else {
        $term_taxonomy = 'shop_order_status';
        $status        = wp_get_object_terms( $order_id, $term_taxonomy );
        if ( ! empty( $status ) && is_wp_error( $status ) === false ) {
            $size = count( $status );
            for ( $i = 0; $i < $size; $i++ ) {
                $term = get_term( $status[ $i ]->term_id, $term_taxonomy );
                if ( $term ) {
                    $output .= $term->name . $export->category_separator;
                    unset( $term );
                }
            }
            $output = substr( $output, 0, -1 );
        }
    }
    return $output;
}

/**
 * Returns the payment gateways that are currently active.
 *
 * @return array
 */
function woo_ce_get_order_payment_gateways() {

    global $woocommerce;

    $output = false;

    if ( class_exists( 'WC_Payment_Gateways' ) ) {
        // Test that payment gateways exist with WooCommerce 1.6 compatibility.
        if ( version_compare( $woocommerce->version, '2.0.0', '<' ) ) {
            if ( $woocommerce->payment_gateways ) {
                $output = $woocommerce->payment_gateways->payment_gateways;
            }
        } elseif ( $woocommerce->payment_gateways() ) {
                $output = $woocommerce->payment_gateways()->payment_gateways();
        }
    }

    // Add Other to list of payment gateways.
    $output['other'] = (object) array(
        'id'           => 'other',
        'title'        => __( 'Other', 'woocommerce-exporter' ),
        'method_title' => __( 'Other', 'woocommerce-exporter' ),
    );

    return $output;
}

/**
 * Format the payment gateway text for the order
 *
 * @param string $payment_id The payment gateway ID.
 * @return string
 */
function woo_ce_format_order_payment_gateway( $payment_id = '' ) {

    $output           = $payment_id;
    $payment_gateways = woo_ce_get_order_payment_gateways();
    if ( ! empty( $payment_gateways ) ) {
        foreach ( $payment_gateways as $payment_gateway ) {
            if ( $payment_gateway->id === $payment_id ) {
                if ( method_exists( $payment_gateway, 'get_title' ) ) {
                    $output = $payment_gateway->get_title();
                } else {
                    $output = $payment_id;
                }
                break;
            }
        }
        unset( $payment_gateways, $payment_gateway );
    }
    if ( empty( $payment_id ) ) {
        $output = __( 'N/A', 'woocommerce-exporter' );
    }

    return $output;
}

/**
 * Get the number of orders for a specific payment gateway.
 *
 * @param string $payment_id The payment gateway ID.
 * @return int
 */
function woo_ce_get_order_payment_gateway_usage( $payment_id = '' ) {

    $output = 0;
    if ( ! empty( $payment_id ) ) {
        $post_type = 'shop_order';
        $args      = array(
            'post_type'   => $post_type,
            'numberposts' => 1,
            'post_status' => 'any',
            'meta_query'  => array(
                array(
                    'key'   => '_payment_method',
                    'value' => $payment_id,
                ),
            ),
            'fields'      => 'ids',
        );
        $order_ids = new WP_Query( $args );
        $output    = absint( $order_ids->found_posts );
        unset( $order_ids );
    }
    return $output;
}

/**
 * Get the shipping methods used by an order.
 *
 * @return array The shipping methods used by the order.
 */
function woo_ce_get_order_shipping_methods() {

    global $woocommerce;

    $output = false;

    // Test that payment gateways exist with WooCommerce 1.6 compatibility.
    if ( version_compare( $woocommerce->version, '2.0.0', '<' ) ) {
        if ( $woocommerce->shipping ) {
            $output = $woocommerce->shipping->shipping_methods;
        }
    } elseif ( $woocommerce->shipping() ) {
            $output = $woocommerce->shipping->load_shipping_methods();
    }

    // Allow Plugin/Theme authors to add support for additional Shipping Methods.
    $output = apply_filters( 'woo_ce_get_order_shipping_methods', $output );

    return $output;
}

/**
 * Format the shipping method on the order.
 *
 * @param string $shipping_id Shipping ID.
 * @return string
 */
function woo_ce_format_order_shipping_method( $shipping_id = '' ) {

    global $woocommerce;

    $output           = $shipping_id;
    $shipping_methods = woo_ce_get_order_shipping_methods();
    if ( ! empty( $shipping_methods ) ) {
        foreach ( $shipping_methods as $shipping_method ) {
            if ( $shipping_method->id === $shipping_id ) {
                if ( method_exists( $shipping_method, 'get_title' ) ) {
                    $output = $shipping_method->get_title();
                } elseif ( isset( $shipping_method->title ) ) {
                    $output = $shipping_method->title;
                } else {
                    $output = $shipping_id;
                }
                break;
            }
        }
        unset( $shipping_methods );
    }
    if ( empty( $shipping_id ) ) {
        $output = __( 'N/A', 'woocommerce-exporter' );
    }
    if ( empty( $output ) ) {
        $output = $shipping_id;
    }
    return $output;
}

/**
 * Format the order item type.
 *
 * @param string $line_type The order item type.
 * @return string
 */
function woo_ce_format_order_item_type( $line_type = '' ) {

    $output = $line_type;
    switch ( $line_type ) {

        case 'line_item':
            $output = __( 'Product', 'woocommerce-exporter' );
            break;

        case 'fee':
            $output = __( 'Fee', 'woocommerce-exporter' );
            break;

        case 'shipping':
            $output = __( 'Shipping', 'woocommerce-exporter' );
            break;

        case 'tax':
            $output = __( 'Tax', 'woocommerce-exporter' );
            break;

        case 'coupon':
            $output = __( 'Coupon', 'woocommerce-exporter' );
            break;

    }
    return $output;
}

/**
 * Format the order item tax class for display.
 *
 * @param string $tax_class The order item tax class.
 * @return string
 */
function woo_ce_format_order_item_tax_class( $tax_class = '' ) {

    $output = $tax_class;
    switch ( $tax_class ) {

        case 'zero-rate':
            $output = __( 'Zero Rate', 'woocommerce-exporter' );
            break;

        case 'reduced-rate':
            $output = __( 'Reduced Rate', 'woocommerce-exporter' );
            break;

        case '':
            $output = __( 'Standard', 'woocommerce-exporter' );
            break;

        case '0':
            $output = __( 'N/A', 'woocommerce-exporter' );
            break;

    }
    return $output;
}

/**
 * Format an order status ID into a human-readable string.
 *
 * @param  string $status_id The status ID to format.
 * @return string            The formatted status.
 */
function woo_ce_format_order_status( $status_id = '' ) {

    $output = $status_id;

    // Check if an empty Order Status has been provided.
    if ( empty( $status_id ) ) {
        return $output;
    }

    $order_statuses = woo_ce_get_order_statuses();
    if ( ! empty( $order_statuses ) ) {
        foreach ( $order_statuses as $order_status ) {
            if (
                $order_status->slug === $status_id ||
                strtolower( $order_status->name ) === $status_id ||
                strpos( $order_status->slug, $status_id ) !== false
            ) {
                $output = ucfirst( $order_status->name );
                break;
            }
        }
    }
    return $output;
}

/**
 * Helper function to get meta for an Order.
 *
 * @param \WC_Order $order the order object.
 * @param string    $meta_key the meta key.
 * @param bool      $single whether to get the meta as a single item. Defaults to `true`.
 * @param string    $context if 'view' then the value will be filtered.
 * @return mixed the order property
 */
function woo_ce_get_order_meta( $order, $meta_key = '', $single = true, $context = 'edit' ) {

    // WooCommerce > 3.0.
    if ( defined( 'WC_VERSION' ) && WC_VERSION && version_compare( WC_VERSION, '3.3', '>=' ) ) {
        $meta_value = $order->get_meta( $meta_key, $single, $context );
    } else {
        // have the $order->get_id() check here just in case the WC_VERSION isn't defined correctly.
        $order_id   = ( is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id );
        $meta_value = get_post_meta( $order_id, $meta_key, $single );
    }
    return $meta_value;
}
