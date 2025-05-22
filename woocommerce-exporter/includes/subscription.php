<?php
if ( is_admin() ) {

    /* Start of: WordPress Administration */

    if ( ! function_exists( 'woo_ce_get_export_type_subscription_count' ) ) {
        /**
         * Get the count of subscriptions for export.
         *
         * @param int    $count       The current count.
         * @param string $export_type The export type.
         * @param array  $args        Additional arguments.
         * @return int
         */
        function woo_ce_get_export_type_subscription_count( $count, $export_type, $args ) {

            if ( 'subscription' !== $export_type ) {
                return $count;
            }

            $count = 0;

            // Override for WordPress MultiSite.
            if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && woo_ce_is_network_admin() ) {
                $sites = get_sites();
                foreach ( $sites as $site ) {
                    switch_to_blog( $site->blog_id );
                    if ( class_exists( 'WC_Subscriptions' ) ) {
                        $count += woo_ce_get_subscription_count();
                    }
                    restore_current_blog();
                }
            }

            // Check that WooCommerce Subscriptions exists.
            if ( class_exists( 'WC_Subscriptions' ) ) {
                $count = woo_ce_get_subscription_count();
            }
            return $count;
        }
        add_filter( 'woo_ce_get_export_type_count', 'woo_ce_get_export_type_subscription_count', 10, 3 );
    }

    /**
     * Get the total count of subscriptions.
     *
     * @return int
     */
    function woo_ce_get_subscription_count() {

        $count = 0;
        // Check if the existing Transient exists.
        $cached = get_transient( WOO_CE_PREFIX . '_subscription_count' );
        if ( false === $cached ) {

            // Allow store owners to force the Subscription count.
            $count = apply_filters( 'woo_ce_get_subscription_count', $count );

            if ( 0 === $count ) {
                $wcs_version = woo_ce_get_wc_subscriptions_version();
                if ( version_compare( $wcs_version, '2.0.1', '<' ) ) {
                    if ( method_exists( 'WC_Subscriptions', 'is_large_site' ) ) {
                        // Does this store have roughly more than 3000 Subscriptions.
                        if ( false === WC_Subscriptions::is_large_site() ) {
                            if ( class_exists( 'WC_Subscriptions_Manager' ) ) {
                                // Check that the get_all_users_subscriptions() function exists.
                                if ( method_exists( 'WC_Subscriptions_Manager', 'get_all_users_subscriptions' ) ) {
                                    $subscriptions = WC_Subscriptions_Manager::get_all_users_subscriptions();
                                    if ( $subscriptions ) {
                                        if ( version_compare( $wcs_version, '2.0.1', '<' ) ) {
                                            foreach ( $subscriptions as $key => $user_subscription ) {
                                                if ( ! empty( $user_subscription ) ) {
                                                    foreach ( $user_subscription as $subscription ) {
                                                        ++$count;
                                                    }
                                                }
                                            }
                                            unset( $subscriptions, $subscription, $user_subscription );
                                        }
                                    }
                                }
                            }
                        } elseif ( method_exists( 'WC_Subscriptions', 'get_total_subscription_count' ) ) {
                            $count = WC_Subscriptions::get_total_subscription_count();
                        } else {
                            $count = '~2500';
                        }
                    } elseif ( method_exists( 'WC_Subscriptions', 'get_subscription_count' ) ) {
                        $count = WC_Subscriptions::get_subscription_count();
                    }
                } elseif ( function_exists( 'wcs_get_subscriptions' ) ) {
                    $args                        = array(
                        'subscriptions_per_page' => -1,
                        'subscription_status'    => 'trash',
                    );
                    $count                      += count( wcs_get_subscriptions( $args ) );
                    $args['subscription_status'] = 'any';
                    $count                      += count( wcs_get_subscriptions( $args ) );
                }
            }
            set_transient( WOO_CE_PREFIX . '_subscription_count', $count, HOUR_IN_SECONDS );
        } else {
            $count = $cached;
        }
        return $count;
    }

    /**
     * Saves subscription-related data for scheduled exports.
     *
     * @param int $post_ID The post ID of the scheduled export.
     */
    function woo_ce_subscription_scheduled_export_save( $post_ID = 0 ) {
        if ( ! isset( $_POST['woo_ce_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['woo_ce_settings_nonce'] ), 'woo_ce_settings_action' ) ) {
            return;
        }

        $auto_subscription_date                 = isset( $_POST['subscription_dates_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['subscription_dates_filter'] ) ) : '';
        $auto_subscription_dates_from           = false;
        $auto_subscription_dates_to             = false;
        $auto_subscription_date_variable        = false;
        $auto_subscription_date_variable_length = false;
        if ( 'variable' === $auto_subscription_date ) {
            $auto_subscription_date_variable        = isset( $_POST['subscription_dates_filter_variable'] ) ? sanitize_text_field( wp_unslash( $_POST['subscription_dates_filter_variable'] ) ) : '';
            $auto_subscription_date_variable_length = isset( $_POST['subscription_dates_filter_variable_length'] ) ? sanitize_text_field( wp_unslash( $_POST['subscription_dates_filter_variable_length'] ) ) : '';
        } elseif ( 'manual' === $auto_subscription_date ) {
            $auto_subscription_dates_from = isset( $_POST['subscription_dates_from'] ) ? sanitize_text_field( wp_unslash( $_POST['subscription_dates_from'] ) ) : '';
            $auto_subscription_dates_to   = isset( $_POST['subscription_dates_to'] ) ? sanitize_text_field( wp_unslash( $_POST['subscription_dates_to'] ) ) : '';
        }
        update_post_meta( $post_ID, '_filter_subscription_date', $auto_subscription_date );
        update_post_meta( $post_ID, '_filter_subscription_dates_from', $auto_subscription_dates_from );
        update_post_meta( $post_ID, '_filter_subscription_dates_to', $auto_subscription_dates_to );
        update_post_meta( $post_ID, '_filter_subscription_date_variable', $auto_subscription_date_variable );
        update_post_meta( $post_ID, '_filter_subscription_date_variable_length', $auto_subscription_date_variable_length );

        update_post_meta( $post_ID, '_filter_subscription_orderby', isset( $_POST['subscription_filter_orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['subscription_filter_orderby'] ) ) : false );
        update_post_meta( $post_ID, '_filter_subscription_items', isset( $_POST['subscription_items_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['subscription_items_filter'] ) ) : false );
        // Select2 passes us a string whereas Chosen gives us an array.
        $auto_subscription_sku    = isset( $_POST['subscription_filter_sku'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['subscription_filter_sku'] ) ) : false;
        $auto_subscription_status = isset( $_POST['subscription_filter_status'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['subscription_filter_status'] ) ) : false;
        update_post_meta( $post_ID, '_filter_subscription_status', ! empty( $auto_subscription_status ) ? woo_ce_format_product_filters( $auto_subscription_status ) : false );
        if ( is_array( $auto_subscription_sku ) && 1 === count( $auto_subscription_sku ) ) {
            $auto_subscription_sku = explode( ',', $auto_subscription_sku[0] );
        }
        update_post_meta( $post_ID, '_filter_subscription_sku', ! empty( $auto_subscription_sku ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $auto_subscription_sku ) ) : false );
    }
    add_action( 'woo_ce_extend_scheduled_export_save', 'woo_ce_subscription_scheduled_export_save' );
    /**
     * Extends dataset arguments for subscription exports.
     *
     * @param array  $args        The existing arguments.
     * @param string $export_type The export type.
     * @param array  $form_data   The form data.
     * @return array The modified arguments.
     */
    function woo_ce_subscription_dataset_args( $args, $export_type = '', $form_data = array() ) {

        if ( empty( $form_data ) ) {
            $form_data = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        }

        // Check if we're dealing with the Subscription Export Type.
        if ( 'subscription' !== $export_type ) {
            return $args;
        }

        // Merge in the form data for this dataset.
        $defaults = array(
            'subscription_status'                       => ( isset( $form_data['subscription_filter_status'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', (array) $form_data['subscription_filter_status'] ) ) : false ),
            'subscription_dates_filter'                 => ( isset( $form_data['subscription_dates_filter'] ) ? sanitize_text_field( $form_data['subscription_dates_filter'] ) : false ),
            'subscription_dates_from'                   => ( isset( $form_data['subscription_dates_from'] ) ? woo_ce_format_order_date( sanitize_text_field( $form_data['subscription_dates_from'] ) ) : '' ),
            'subscription_dates_to'                     => ( isset( $form_data['subscription_dates_to'] ) ? woo_ce_format_order_date( sanitize_text_field( $form_data['subscription_dates_to'] ) ) : '' ),
            'subscription_dates_filter_variable'        => ( isset( $form_data['subscription_dates_filter_variable'] ) ? absint( $form_data['subscription_dates_filter_variable'] ) : false ),
            'subscription_dates_filter_variable_length' => ( isset( $form_data['subscription_dates_filter_variable_length'] ) ? sanitize_text_field( $form_data['subscription_dates_filter_variable_length'] ) : false ),
            'subscription_product'                      => ( isset( $form_data['subscription_filter_product'] ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $form_data['subscription_filter_product'] ) ) : false ),
            'subscription_source'                       => ( isset( $form_data['subscription_filter_source'] ) ? sanitize_text_field( $form_data['subscription_filter_source'] ) : false ),
            'subscription_orderby'                      => ( isset( $form_data['subscription_orderby'] ) ? sanitize_text_field( $form_data['subscription_orderby'] ) : false ),
            'subscription_order'                        => ( isset( $form_data['subscription_order'] ) ? sanitize_text_field( $form_data['subscription_order'] ) : false ),
            'subscription_items'                        => ( isset( $form_data['subscription_items'] ) ? sanitize_text_field( $form_data['subscription_items'] ) : false ),
        );
        $args     = wp_parse_args( $args, $defaults );

        // Save dataset export specific options.
        $options_to_update = array(
            'subscription_orderby',
            'subscription_order',
            'subscription_status',
            'subscription_product',
            'subscription_dates_filter',
            'subscription_dates_filter_variable',
            'subscription_dates_filter_variable_length',
        );

        foreach ( $options_to_update as $option ) {
            if ( $args[ $option ] !== woo_ce_get_option( $option ) ) {
                woo_ce_update_option( $option, $args[ $option ] );
            }
        }

        if ( $args['subscription_items'] !== woo_ce_get_option( 'subscription_items_formatting' ) ) {
            woo_ce_update_option( 'subscription_items_formatting', $args['subscription_items'] );
        }

        if ( $args['subscription_dates_from'] !== woo_ce_get_option( 'subscription_dates_from' ) ) {
            woo_ce_update_option( 'subscription_dates_from', woo_ce_format_order_date( $args['subscription_dates_from'], 'save' ) );
        }

        if ( $args['subscription_dates_to'] !== woo_ce_get_option( 'subscription_dates_to' ) ) {
            woo_ce_update_option( 'subscription_dates_to', woo_ce_format_order_date( $args['subscription_dates_to'], 'save' ) );
        }

        return $args;
    }
    add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_subscription_dataset_args', 10, 3 );

    /* End of: WordPress Administration */
}
/**
 * Filters the subscription dataset arguments for cron jobs.
 *
 * @param array  $args         The current arguments.
 * @param string $export_type  The export type.
 * @param int    $is_scheduled Whether this is a scheduled export.
 * @return array The filtered arguments.
 */
function woo_ce_cron_subscription_dataset_args( $args, $export_type = '', $is_scheduled = 0 ) {

    // Check if we're dealing with the Subscription Export Type.
    if ( 'subscription' !== $export_type ) {
        return $args;
    }

    $subscription_orderby                     = false;
    $subscription_filter_status               = false;
    $subscription_filter_sku                  = false;
    $subscription_dates_filter                = false;
    $subscription_filter_date_variable        = false;
    $subscription_filter_date_variable_length = false;
    $subscription_filter_dates_from           = false;
    $subscription_filter_dates_to             = false;

    if ( $is_scheduled ) {
        $scheduled_export = ( $is_scheduled ? absint( get_transient( WOO_CE_PREFIX . '_scheduled_export_id' ) ) : 0 );

        $subscription_orderby       = get_post_meta( $scheduled_export, '_filter_subscription_orderby', true );
        $subscription_filter_status = get_post_meta( $scheduled_export, '_filter_subscription_status', true );
        $subscription_filter_sku    = get_post_meta( $scheduled_export, '_filter_subscription_sku', true );
        $subscription_dates_filter  = get_post_meta( $scheduled_export, '_filter_subscription_date', true );
        if ( $subscription_dates_filter ) {
            switch ( $subscription_dates_filter ) {
                case 'manual':
                    $subscription_filter_dates_from = get_post_meta( $scheduled_export, '_filter_subscription_dates_from', true );
                    $subscription_filter_dates_to   = get_post_meta( $scheduled_export, '_filter_subscription_dates_to', true );
                    break;

                case 'variable':
                    $subscription_filter_date_variable        = get_post_meta( $scheduled_export, '_filter_subscription_date_variable', true );
                    $subscription_filter_date_variable_length = get_post_meta( $scheduled_export, '_filter_subscription_date_variable_length', true );
                    break;
            }
        }
    }

    // Merge in the form data for this dataset.
    $overrides = array(
        'subscription_orderby'                      => ( ! empty( $subscription_orderby ) ? sanitize_text_field( $subscription_orderby ) : false ),
        'subscription_status'                       => ( ! empty( $subscription_filter_status ) ? array_map( 'sanitize_text_field', (array) $subscription_filter_status ) : array() ),
        'subscription_product'                      => ( ! empty( $subscription_filter_sku ) ? array_map( 'sanitize_text_field', (array) $subscription_filter_sku ) : array() ),
        'subscription_dates_filter'                 => sanitize_text_field( $subscription_dates_filter ),
        'subscription_dates_filter_variable'        => ( ! empty( $subscription_filter_date_variable ) ? absint( $subscription_filter_date_variable ) : false ),
        'subscription_dates_filter_variable_length' => ( ! empty( $subscription_filter_date_variable_length ) ? sanitize_text_field( $subscription_filter_date_variable_length ) : false ),
        'subscription_dates_from'                   => ( ! empty( $subscription_filter_dates_from ) ? sanitize_text_field( $subscription_filter_dates_from ) : false ),
        'subscription_dates_to'                     => ( ! empty( $subscription_filter_dates_to ) ? sanitize_text_field( $subscription_filter_dates_to ) : false ),
    );

    $args = wp_parse_args( $overrides, $args );

    return $args;
}
add_filter( 'woo_ce_extend_cron_dataset_args', 'woo_ce_cron_subscription_dataset_args', 10, 3 );

/**
 * Returns a list of Subscription export columns.
 *
 * @param string $format The format of the returned fields ('full' or 'summary').
 * @param int    $post_ID The ID of the export template post, if any.
 * @return array The list of subscription export fields.
 */
function woo_ce_get_subscription_fields( $format = 'full', $post_ID = 0 ) {

    $export_type = 'subscription';

    $fields   = array();
    $fields[] = array(
        'name'  => 'subscription_id',
        'label' => __( 'Subscription ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_id',
        'label' => __( 'Order ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'status',
        'label' => __( 'Subscription Status', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'recurring',
        'label' => __( 'Recurring', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'user',
        'label' => __( 'User', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'user_id',
        'label' => __( 'User ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_status',
        'label' => __( 'Order Status', 'woocommerce-exporter' ),
    );
    // Check if this is a pre-WooCommerce 2.2 instance.
    $woocommerce_version = woo_get_woo_version();
    if ( version_compare( $woocommerce_version, '2.2', '<' ) ) {
        $fields[] = array(
            'name'  => 'post_status',
            'label' => __( 'Post Status', 'woocommerce-exporter' ),
        );
    }
    $fields[] = array(
        'name'  => 'transaction_id',
        'label' => __( 'Transaction ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'start_date',
        'label' => __( 'Start Date', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'end_date',
        'label' => __( 'End Date', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'trial_end_date',
        'label' => __( 'Trial End Date', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'last_payment',
        'label' => __( 'Last Payment', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'next_payment',
        'label' => __( 'Next Payment', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'payment_method',
        'label' => __( 'Payment Method', 'woocommerce-exporter' ),
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
        'name'  => 'recurring_total',
        'label' => __( 'Recurring Total', 'woocommerce-exporter' ),
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
        'name'  => 'shipping_cost',
        'label' => __( 'Shipping Cost', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'sign_up_fee',
        'label' => __( 'Sign-up Fee', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'trial_length',
        'label' => __( 'Trial Length', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'trial_period',
        'label' => __( 'Trial Period', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'coupon',
        'label' => __( 'Coupon Code', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'related_orders',
        'label' => __( 'Related Orders', 'woocommerce-exporter' ),
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
    $fields[] = array(
        'name'  => 'order_items_product_id',
        'label' => __( 'Subscription Items: Product ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_variation_id',
        'label' => __( 'Subscription Items: Variation ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_sku',
        'label' => __( 'Subscription Items: Product SKU', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_name',
        'label' => __( 'Subscription Items: Product Name', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_variation',
        'label' => __( 'Subscription Items: Product Variation', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'order_items_quantity',
        'label' => __( 'Subscription Items: Quantity', 'woocommerce-exporter' ),
    );

    // Drop in our content filters here.
    add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

    // Allow Plugin/Theme authors to add support for additional columns.
    $fields = apply_filters( sprintf( WOO_CE_PREFIX . '_%s_fields', $export_type ), $fields, $export_type );

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
            if ( version_compare( phpversion(), '5.3', '>=' ) ) {
                usort( $fields, woo_ce_sort_fields( 'order' ) );
            }
            return $fields;
    }
}

/**
 * Override field labels from the Field Editor.
 *
 * @param array $fields The fields to override.
 * @return array The modified fields.
 */
function woo_ce_override_subscription_field_labels( $fields = array() ) {

    global $export;

    $export_type = 'subscription';

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

    if ( ! empty( $labels ) ) {
        foreach ( $fields as $key => $field ) {
            if ( isset( $labels[ $field['name'] ] ) ) {
                $fields[ $key ]['label'] = sanitize_text_field( $labels[ $field['name'] ] );
            }
        }
    }
    return $fields;
}
add_filter( 'woo_ce_subscription_fields', 'woo_ce_override_subscription_field_labels', 11 );

/**
 * Returns the export column header label based on an export column slug.
 *
 * @param string|null $name   The name of the field.
 * @param string      $format The format of the output ('name' or 'full').
 * @return string|array The field label or full field data.
 */
function woo_ce_get_subscription_field( $name = null, $format = 'name' ) {

    $output = '';
    if ( $name ) {
        $fields = woo_ce_get_subscription_fields();
        $size   = count( $fields );
        for ( $i = 0; $i < $size; $i++ ) {
            if ( $fields[ $i ]['name'] === $name ) {
                switch ( $format ) {

                    case 'name':
                        $output = $fields[ $i ]['label'];
                        break;

                    case 'full':
                        $output = $fields[ $i ];
                        break;
                }
                break;
            }
        }
    }
    return $output;
}
/**
 * Returns a list of Subscription IDs based on the given arguments.
 *
 * @param array $args           Arguments to filter subscriptions.
 * @param array $export_settings Export settings.
 * @return array Array of subscription IDs.
 */
function woo_ce_get_subscriptions( $args = array(), $export_settings = null ) {
    global $export;
    if ( null !== $export_settings ) {
        $export = $export_settings;
    }

    $wcs_version = woo_ce_get_wc_subscriptions_version();

    $limit_volume         = -1;
    $offset               = 0;
    $subscription_status  = false;
    $subscription_product = false;
    $orderby              = 'start_date';
    $order                = 'DESC';
    if ( $args ) {
        $limit_volume              = ( ! empty( $args['limit_volume'] ) ? absint( $args['limit_volume'] ) : -1 );
        $offset                    = absint( $args['offset'] );
        $orderby                   = ( ! empty( $args['subscription_orderby'] ) ? sanitize_text_field( $args['subscription_orderby'] ) : 'start_date' );
        $order                     = ( ! empty( $args['subscription_order'] ) ? sanitize_text_field( $args['subscription_order'] ) : 'DESC' );
        $subscription_status       = ( ! empty( $args['subscription_status'] ) ? array_map( 'sanitize_text_field', $args['subscription_status'] ) : array() );
        $subscription_product      = ( ! empty( $args['subscription_product'] ) ? array_map( 'absint', $args['subscription_product'] ) : array() );
        $user_ids                  = ( ! empty( $args['subscription_customer'] ) ? array_map( 'absint', $args['subscription_customer'] ) : false );
        $source                    = ( ! empty( $args['subscription_source'] ) ? sanitize_text_field( $args['subscription_source'] ) : false );
        $subscription_dates_filter = ( ! empty( $args['subscription_dates_filter'] ) ? sanitize_text_field( $args['subscription_dates_filter'] ) : false );
    }

    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'subscription_dates_filter' ) );
        woo_ce_error_log( sprintf( 'Debug: %s', $subscription_dates_filter ) );
    }
    switch ( $subscription_dates_filter ) {
        case 'tomorrow':
            $subscription_dates_from = woo_ce_get_order_date_filter( 'tomorrow', 'from' );
            $subscription_dates_to   = woo_ce_get_order_date_filter( 'tomorrow', 'to' );
            break;

        case 'today':
            $subscription_dates_from = woo_ce_get_order_date_filter( 'today', 'from' );
            $subscription_dates_to   = woo_ce_get_order_date_filter( 'today', 'to' );
            break;

        case 'yesterday':
            $subscription_dates_from = woo_ce_get_order_date_filter( 'yesterday', 'from' );
            $subscription_dates_to   = woo_ce_get_order_date_filter( 'yesterday', 'to' );
            break;

        case 'current_week':
            $subscription_dates_from = woo_ce_get_order_date_filter( 'current_week', 'from' );
            $subscription_dates_to   = woo_ce_get_order_date_filter( 'current_week', 'to' );
            break;

        case 'last_week':
            $subscription_dates_from = woo_ce_get_order_date_filter( 'last_week', 'from' );
            $subscription_dates_to   = woo_ce_get_order_date_filter( 'last_week', 'to' );
            break;

        case 'current_month':
            $subscription_dates_from = woo_ce_get_order_date_filter( 'current_month', 'from' );
            $subscription_dates_to   = woo_ce_get_order_date_filter( 'current_month', 'to' );
            break;

        case 'last_month':
            $subscription_dates_from = woo_ce_get_order_date_filter( 'last_month', 'from' );
            $subscription_dates_to   = woo_ce_get_order_date_filter( 'last_month', 'to' );
            break;

        case 'current_year':
            $subscription_dates_from = woo_ce_get_order_date_filter( 'current_year', 'from' );
            $subscription_dates_to   = woo_ce_get_order_date_filter( 'current_year', 'to' );
            break;

        case 'last_year':
            $subscription_dates_from = woo_ce_get_order_date_filter( 'last_year', 'from' );
            $subscription_dates_to   = woo_ce_get_order_date_filter( 'last_year', 'to' );
            break;

        case 'manual':
            $date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );

            // Populate empty from or to dates.
            if ( ! empty( $args['subscription_dates_from'] ) ) {
                $subscription_dates_from = woo_ce_format_order_date( sanitize_text_field( $args['subscription_dates_from'] ) );
            } else {
                // Default From date to the first Order.
                $subscription_dates_from = woo_ce_get_order_first_date( $date_format );
            }
            if ( ! empty( $args['subscription_dates_to'] ) ) {
                $subscription_dates_to = woo_ce_format_order_date( sanitize_text_field( $args['subscription_dates_to'] ) );
                $subscription_dates_to = apply_filters( 'woo_ce_get_subscription_dates_to', $subscription_dates_to, $args['subscription_dates_to'] );
            } else {
                // Default To date to tomorrow.
                $subscription_dates_to = woo_ce_format_order_date( woo_ce_get_order_date_filter( 'today', 'to', $date_format ) );
            }

            // Check if the provided dates match the date format.
            $validate_from = woo_ce_validate_order_date( $subscription_dates_from, woo_ce_format_order_date( $date_format ) );
            $validate_to   = woo_ce_validate_order_date( $subscription_dates_to, woo_ce_format_order_date( $date_format ) );
            if ( ! $validate_from && ! $validate_to ) {
                $subscription_dates_from = woo_ce_format_order_date( date( $date_format, strtotime( $subscription_dates_from, current_time( 'timestamp', 0 ) ) ) );
                $subscription_dates_to   = woo_ce_format_order_date( date( $date_format, strtotime( $subscription_dates_to, current_time( 'timestamp', 0 ) ) ) );
            }

            // WP_Query only accepts D-m-Y so we must format dates to that, fun times...
            if ( $date_format !== 'd/m/Y' ) {
                $date_format = woo_ce_format_order_date( $date_format );
                if ( function_exists( 'date_create_from_format' ) && function_exists( 'date_format' ) ) {
                    // Check if we've been passed a mixed format.
                    if ( strpos( $subscription_dates_from, '-' ) !== false ) {
                        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                            woo_ce_error_log( sprintf( 'Debug: %s', 'subscription_dates_from' ) );
                            woo_ce_error_log( sprintf( 'Debug: %s', $subscription_dates_from ) );
                        }
                        $date_check = explode( '-', $subscription_dates_from );
                        if ( checkdate( $date_check[0], $date_check[1], $date_check[2] ) ) {
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'option 1' ) );
                            }
                            if ( $subscription_dates_from = date_create_from_format( 'm-d-Y', $subscription_dates_from ) ) {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 1: accepted' ) );
                                    woo_ce_error_log( sprintf( 'Debug: %s', print_r( $date_check, true ) ) );
                                }
                                $subscription_dates_from = date_format( $subscription_dates_from, 'd-m-Y' );
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', $subscription_dates_from ) );
                                }
                            }
                        } elseif ( checkdate( $date_check[1], $date_check[0], $date_check[2] ) ) {
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'option 2' ) );
                            }
                            if ( $subscription_dates_from = date_create_from_format( 'd-m-Y', $subscription_dates_from ) ) {
                                $subscription_dates_from = date_format( $subscription_dates_from, 'd-m-Y' );
                            }
                        } else {
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'option 3' ) );
                            }
                            if ( $subscription_dates_from = date_create_from_format( $date_format, $subscription_dates_from ) ) {
                                $subscription_dates_from = date_format( $subscription_dates_from, 'd-m-Y' );
                            }
                        }
                    } else {
                        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                            woo_ce_error_log( sprintf( 'Debug: %s', 'option 4' ) );
                        }
                        if ( $subscription_dates_from = date_create_from_format( $date_format, $subscription_dates_from ) ) {
                            $subscription_dates_from = date_format( $subscription_dates_from, 'd-m-Y' );
                        }
                    }
                    unset( $date_check );

                    if ( strpos( $subscription_dates_to, '-' ) !== false ) {
                        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                            woo_ce_error_log( sprintf( 'Debug: %s', 'subscription_dates_to' ) );
                            woo_ce_error_log( sprintf( 'Debug: %s', $subscription_dates_to ) );
                        }
                        $date_check = explode( '-', $subscription_dates_to );
                        if ( checkdate( $date_check[0], $date_check[1], $date_check[2] ) ) {
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'option 1' ) );
                            }
                            if ( $subscription_dates_to = date_create_from_format( 'm-d-Y', $subscription_dates_to ) ) {
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', 'option 1: accepted' ) );
                                    woo_ce_error_log( sprintf( 'Debug: %s', print_r( $date_check, true ) ) );
                                }
                                $subscription_dates_to = date_format( $subscription_dates_to, 'd-m-Y' );
                                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                                    woo_ce_error_log( sprintf( 'Debug: %s', $subscription_dates_to ) );
                                }
                            }
                        } elseif ( checkdate( $date_check[1], $date_check[0], $date_check[2] ) ) {
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'option 2' ) );
                            }
                            if ( $subscription_dates_to = date_create_from_format( 'd-m-Y', $subscription_dates_to ) ) {
                                $subscription_dates_to = date_format( $subscription_dates_to, 'd-m-Y' );
                            }
                        } else {
                            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'option 3' ) );
                            }
                            if ( $subscription_dates_to = date_create_from_format( $date_format, $subscription_dates_to ) ) {
                                $subscription_dates_to = date_format( $subscription_dates_to, 'd-m-Y' );
                            }
                        }
                    } elseif ( $subscription_dates_to = date_create_from_format( $date_format, $subscription_dates_to ) ) {
                        $subscription_dates_to = date_format( $subscription_dates_to, 'd-m-Y' );
                    }
                    unset( $date_check );
                }
            }
            break;

        case 'variable':
            $subscription_filter_date_variable        = sanitize_text_field( $args['subscription_dates_filter_variable'] );
            $subscription_filter_date_variable_length = sanitize_text_field( $args['subscription_dates_filter_variable_length'] );
            if ( $subscription_filter_date_variable !== false && $subscription_filter_date_variable_length !== false ) {
                $timestamp               = strtotime( sprintf( '-%d %s', absint( $subscription_filter_date_variable ), $subscription_filter_date_variable_length ), current_time( 'timestamp', 0 ) );
                $subscription_dates_from = date( 'd-m-Y-H-i-s', mktime( date( 'H', $timestamp ), date( 'i', $timestamp ), date( 's', $timestamp ), date( 'n', $timestamp ), date( 'd', $timestamp ), date( 'Y', $timestamp ) ) );
                $subscription_dates_to   = woo_ce_get_order_date_filter( 'today', 'to' );
                unset( $subscription_filter_date_variable, $subscription_filter_date_variable_length, $timestamp );
            }
            break;

        default:
            $subscription_dates_from = false;
            $subscription_dates_to   = false;
            break;
    }
    if ( ! empty( $subscription_dates_from ) && ! empty( $subscription_dates_to ) ) {
        // From.
        $subscription_dates_from = explode( '-', $subscription_dates_from );
        // Check that a valid date was provided.
        if ( isset( $subscription_dates_from[0] ) && isset( $subscription_dates_from[1] ) && isset( $subscription_dates_from[2] ) ) {
            $subscription_dates_from = array(
                'year'   => absint( $subscription_dates_from[2] ),
                'month'  => absint( $subscription_dates_from[1] ),
                'day'    => absint( $subscription_dates_from[0] ),
                'hour'   => ( isset( $subscription_dates_from[3] ) ? absint( $subscription_dates_from[3] ) : 0 ),
                'minute' => ( isset( $subscription_dates_from[4] ) ? absint( $subscription_dates_from[4] ) : 0 ),
                'second' => ( isset( $subscription_dates_from[5] ) ? absint( $subscription_dates_from[5] ) : 0 ),
            );
            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                woo_ce_error_log( sprintf( 'Debug: %s', 'subscription_dates_from' ) );
                woo_ce_error_log( sprintf( 'Debug: %s', print_r( $subscription_dates_from, true ) ) );
            }
        } else {
            $subscription_dates_from = false;
        }
        // To.
        $subscription_dates_to = explode( '-', $subscription_dates_to );
        // Check that a valid date was provided.
        if ( isset( $subscription_dates_to[0] ) && isset( $subscription_dates_to[1] ) && isset( $subscription_dates_to[2] ) ) {
            $subscription_dates_to = array(
                'year'   => absint( $subscription_dates_to[2] ),
                'month'  => absint( $subscription_dates_to[1] ),
                'day'    => absint( $subscription_dates_to[0] ),
                'hour'   => ( isset( $subscription_dates_to[3] ) ? absint( $subscription_dates_to[3] ) : 23 ),
                'minute' => ( isset( $subscription_dates_to[4] ) ? absint( $subscription_dates_to[4] ) : 59 ),
                'second' => ( isset( $subscription_dates_to[5] ) ? absint( $subscription_dates_to[5] ) : 59 ),
            );
            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                woo_ce_error_log( sprintf( 'Debug: %s', 'subscription_dates_to' ) );
                woo_ce_error_log( sprintf( 'Debug: %s', print_r( $subscription_dates_to, true ) ) );
            }
            // Check for bad values.
            switch ( $subscription_dates_filter ) {
                case 'last_month':
                    if ( $subscription_dates_from['month'] !== $subscription_dates_to['month'] ) {
                        $subscription_dates_to['hour']   = 0;
                        $subscription_dates_to['minute'] = 0;
                        $subscription_dates_to['second'] = 0;
                        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_subscription_filter_date', false ) ) {
                            woo_ce_error_log( sprintf( 'Debug: %s', 'subscription_dates_to, last_month override' ) );
                            woo_ce_error_log( sprintf( 'Debug: %s', print_r( $subscription_dates_to, true ) ) );
                        }
                    }
                    break;
            }
        } else {
            $subscription_dates_to = false;
        }
    }

    $troubleshooting_url = 'https://visser.com.au/knowledge-base/';

    $output = array();

    // Check that WooCommerce Subscriptions exists.
    if ( ! class_exists( 'WC_Subscriptions' ) || ! class_exists( 'WC_Subscriptions_Manager' ) ) {
        $message = __( 'The WooCommerce Subscriptions class <code>WC_Subscriptions</code> or <code>WC_Subscriptions_Manager</code> could not be found, this is required to export Subscriptions.', 'woocommerce-exporter' ) . ' (<a href="' . esc_url( $troubleshooting_url ) . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Warning: %s', $message . ': ' . ( time() - $export->start_time ) ) );
        }
        return;
    } else {
        // Check that the get_all_users_subscriptions() function exists.
        if ( ! method_exists( 'WC_Subscriptions_Manager', 'get_all_users_subscriptions' ) ) {
            $message = __( 'The WooCommerce Subscriptions method <code>WC_Subscriptions_Manager->get_all_users_subscriptions()</code> could not be found, this is required to export Subscriptions.', 'woocommerce-exporter' ) . ' (<a href="' . esc_url( $troubleshooting_url ) . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
            if ( WOO_CE_LOGGING ) {
                woo_ce_error_log( sprintf( 'Warning: %s', $message . ': ' . ( time() - $export->start_time ) ) );
            }
            return;
        }
    }

    if ( class_exists( 'WC_Subscriptions' ) ) {
        $args = array(
            'subscriptions_per_page' => $limit_volume,
            'offset'                 => $offset,
            'orderby'                => $orderby,
            'order'                  => $order,
            'fields'                 => 'ids',
        );

        // Filter Subscriptions by Subscription Status.
        if ( $subscription_status ) {
            if ( count( $subscription_status ) === 1 ) {
                $args['subscription_status'] = $subscription_status[0];
            } else {
                $args['subscription_status'] = $subscription_status;
            }
        }
        // Filter Subscriptions by Customer.
        if ( ! empty( $user_ids ) ) {
            // Check if we're dealing with a string or list of users.
            if ( is_string( $user_ids ) ) {
                $user_ids = explode( ',', $user_ids );
            }
        }

        // Allow other developers to bake in their own filters.
        $args = apply_filters( 'woo_ce_get_subscriptions_args', $args, $export );

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_subscriptions( $args ): ' . ( time() - $export->start_time ) ) );
            woo_ce_error_log( sprintf( 'Debug: %s', '$args: ' . print_r( $args, true ) ) );
        }

        if ( function_exists( 'wcs_get_subscriptions' ) ) {
            // Let's add some special sauce to override wcs_get_subscriptions() and only return the Post IDs.
            add_filter( 'woocommerce_get_subscriptions_query_args', 'woo_ce_woocommerce_get_subscriptions_query_args' );
            add_filter( 'woocommerce_got_subscriptions', 'woo_ce_woocommerce_got_subscriptions' );
            $subscription_ids = wcs_get_subscriptions( $args );

            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
                woo_ce_error_log( sprintf( 'Debug: %s', 'subscription_ids( $args ): ' . ( time() - $export->start_time ) ) );
                woo_ce_error_log( sprintf( 'Debug: %s', '$subscription_ids: ' . print_r( $subscription_ids, true ) ) );
            }

            // Filter Subscription dates - Avoids being overwritten by wcs_get_subscriptions above.
            if ( ! empty( $subscription_dates_from ) && ! empty( $subscription_dates_to ) ) {
                $args['date_query'] = array(
                    array(
                        'column'    => apply_filters( 'woo_ce_get_subscriptions_filter_subscription_dates_column', 'post_date' ),
                        'before'    => $subscription_dates_to,
                        'after'     => $subscription_dates_from,
                        'inclusive' => true,
                    ),
                );
            }

            // Check if we are filtering Subscriptions by Last Export.
            if ( 'last_export' === $subscription_dates_filter ) {
                $args['meta_query'][] = array(
                    'relation' => 'AND',
                    'key'      => '_woo_cd_exported',
                    'value'    => 1,
                    'compare'  => 'NOT EXISTS',
                );
            }

            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
                woo_ce_error_log( sprintf( 'Debug: %s', '$args (loaded from a Transient): ' . ( time() - $export->start_time ) ) );
                woo_ce_error_log( sprintf( 'Debug: %s', '$args: ' . print_r( $args, true ) ) );
            }
        } elseif ( version_compare( $wcs_version, '1.5.26', '<' ) ) {
            $subscription_ids = WC_Subscriptions::get_subscriptions( $args );
            if ( ! empty( $subscription_ids ) ) {
                $subscription_keys = array();
                foreach ( $subscription_ids as $subscription_id ) {
                    $subscription_keys[] = $subscription_id['subscription_key'];
                }
                return $subscription_keys;
            }
        }

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'subscription_ids( $args ): ' . ( time() - $export->start_time ) ) );
            woo_ce_error_log( sprintf( 'Debug: %s', '$subscription_ids: ' . print_r( $subscription_ids, true ) ) );
        }

        if ( ! empty( $args ) ) {
            $subscription_ids = array();
            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
                woo_ce_error_log( sprintf( 'Debug: %s', 'before WP_Query( $args ): ' . ( time() - $export->start_time ) ) );
                woo_ce_error_log( sprintf( 'Debug: %s', '$args: ' . print_r( $args, true ) ) );
            }
            $subscriptions = wcs_get_subscriptions( $args );
            if ( ! empty( $subscriptions ) ) {
                if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
                    woo_ce_error_log( sprintf( 'Debug: %s', 'after WP_Query( $args ): ' . ( time() - $export->start_time ) ) );
                    woo_ce_error_log( sprintf( 'Debug: %s', '$subscriptions->posts: ' . print_r( $subscriptions->posts, true ) ) );
                }
                foreach ( $subscriptions as $subscription_id ) {
                    $subscription_ids[] = $subscription_id;
                }
            } elseif ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
                $message = 'No Posts were returned by WP_Query: ' . ( time() - $export->start_time );
                woo_ce_error_log( sprintf( 'Debug: %s', $message ) );
            }
            unset( $subscriptions );
        } elseif ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            $message = 'No $args detected, skipping WP_Query for Subscriptions: ' . ( time() - $export->start_time );
            woo_ce_error_log( sprintf( 'Debug: %s', $message ) );
        }

        remove_filter( 'woocommerce_got_subscriptions', 'woo_ce_woocommerce_got_subscriptions' );
        remove_filter( 'woocommerce_get_subscriptions_query_args', 'woo_ce_woocommerce_get_subscriptions_query_args' );
        $subscriptions = array();

        if ( ! empty( $subscription_ids ) ) {
            foreach ( $subscription_ids as $subscription_id ) {
                $wcs_obj = wcs_get_subscription( $subscription_id );

                // Filter Subscriptions by Subscription Product.
                if ( $subscription_product ) {
                    $order_id = $wcs_obj->get_parent_id();
                    if ( ! empty( $order_id ) ) {
                        $order_ids = woo_ce_get_product_assoc_order_ids( $subscription_product );
                        if ( ! in_array( $order_id, (array) $order_ids, true ) ) {
                            unset( $subscription_id );
                        }
                        unset( $order_ids );
                    }
                    unset( $order_id );
                }
                // Filter Subscriptions by Customer.
                if ( ! empty( $user_ids ) ) {
                    $user_id = get_post_meta( $subscription_id, '_customer_user', true );
                    if ( ! in_array( $user_id, $user_ids ) ) {
                        unset( $subscription_id );
                    }
                }
                // Filter Subscriptions by Source.
                if ( ! empty( $source ) ) {
                    $order_id = $wcs_obj->get_parent_id();
                    switch ( $source ) {

                        case 'customer':
                            if ( empty( $order_id ) ) {
                                unset( $subscription_id );
                            }
                            break;

                        case 'manual':
                            if ( ! empty( $order_id ) ) {
                                unset( $subscription_id );
                            }
                            break;
                    }
                    unset( $order_id );
                }

                if ( isset( $subscription_id ) ) {
                    $subscriptions[] = $subscription_id;
                }

                // Mark this Subscription as exported if Since last export Date filter is used.
                if ( 'last_export' === $subscription_dates_filter ) {
                    $wcs_obj->update_meta_data( '_woo_cd_exported', 1 );
                    $wcs_obj->save_meta_data();
                }
            }
            unset( $subscription_ids, $subscription_id );
        }
    } elseif ( WOO_CE_LOGGING ) {
        $message = 'The Class WC_Subscriptions does not exist: ' . ( time() - $export->start_time );
        woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
    }
    return $subscriptions;
}

function woo_ce_woocommerce_get_subscriptions_query_args( $args ) {

    set_transient( WOO_CE_PREFIX . '_subscription_wcs_get_subscriptions', $args, HOUR_IN_SECONDS );
    return $args;
}

// Override wcs_get_subscriptions() to only return the Subscription Post ID
function woo_ce_woocommerce_got_subscriptions( $subscriptions ) {

    if ( ! empty( $subscriptions ) ) {
        $subscriptions = array_keys( $subscriptions );
    }
    return $subscriptions;
}

if ( ! function_exists( 'woo_ce_export_dataset_override_subscription' ) ) {
    function woo_ce_export_dataset_override_subscription( $output = null, $export_type = null ) {

        global $export;

        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'before woo_ce_export_dataset_override_subscription(): ' . ( time() - $export->start_time ) ) );
        }

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_subscriptions( $export->args ): ' . ( time() - $export->start_time ) ) );
            woo_ce_error_log( sprintf( 'Debug: %s', '$export->args: ' . print_r( $export->args, true ) ) );
        }

        $subscriptions = woo_ce_get_subscriptions( $export->args );
        if ( ! empty( $subscriptions ) ) {

            $export->total_rows = count( $subscriptions );

            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
                woo_ce_error_log( sprintf( 'Debug: %s', sprintf( '%d subscriptions detected: ' . ( time() - $export->start_time ), $export->total_rows ) ) );
            }

            // XML, RSS and JSON export
            if ( in_array( $export->export_format, array( 'xml', 'rss', 'json' ) ) ) {
                if ( ! empty( $export->fields ) ) {
                    foreach ( $subscriptions as $subscription ) {
                        if ( in_array( $export->export_format, array( 'xml', 'json' ) ) ) {
                            $child = $output->addChild( apply_filters( 'woo_ce_export_xml_subscription_node', sanitize_key( $export_type ) ) );
                        } elseif ( $export->export_format == 'rss' ) {
                            $child = $output->addChild( 'item' );
                        }
                        if (
                            $export->export_format <> 'json' &&
                            apply_filters( 'woo_ce_export_xml_subscription_id_attribute', true )
                        ) {
                            $child->addAttribute( 'id', $subscription );
                        }
                        $subscription = woo_ce_get_subscription_data( $subscription, $export->args, array_keys( $export->fields ) );
                        foreach ( array_keys( $export->fields ) as $key => $field ) {
                            if ( isset( $subscription->$field ) ) {
                                if ( ! is_array( $field ) ) {
                                    if ( woo_ce_is_xml_cdata( $subscription->$field ) ) {
                                        $child->addChild( apply_filters( 'woo_ce_export_xml_subscription_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $subscription->$field ) ) );
                                    } else {
                                        $child->addChild( apply_filters( 'woo_ce_export_xml_subscription_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $subscription->$field ) ) );
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $output = array();
                // PHPExcel export
                foreach ( $subscriptions as $key => $subscription_id ) {

                    if ( WOO_CE_LOGGING ) {
                        woo_ce_error_log( sprintf( 'Debug: %s', 'before woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
                    }

                    $subscription = woo_ce_get_subscription_data( $subscription_id, $export->args, array_keys( $export->fields ) );
                    if ( $export->args['subscription_items'] == 'individual' ) {
                        if ( ! empty( $subscription->order_items ) ) {
                            foreach ( $subscription->order_items as $order_item ) {
                                $order = apply_filters( 'woo_ce_order_items_individual', $subscription, $order_item );
                                // This fixes the Order Items for this Order Items Formatting rule
                                $output[] = (object) (array) $order;
                                $output   = apply_filters( 'woo_ce_order_items_individual_output', $output, $order, $order_item );
                            }
                            // Allow Plugin/Theme authors to add in blank rows between Orders
                            $output = apply_filters( 'woo_ce_order_items_individual_output_end', $output, $subscription );
                        } else {
                            $output[] = $subscription;
                        }
                    } else {
                        $output[] = $subscription;
                    }

                    if ( WOO_CE_LOGGING ) {
                        woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
                    }
                }
            }
            unset( $subscriptions, $subscription );
        } elseif ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {

            $message = 'No subscriptions to export returned by woo_ce_get_subscriptions(): ' . ( time() - $export->start_time );
            woo_ce_error_log( sprintf( 'Debug: %s', $message ) );
        }

        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'end woo_ce_export_dataset_override_subscription(): ' . ( time() - $export->start_time ) ) );
        }

        return $output;
    }
}

/**
 * Export dataset multisite override for subscriptions.
 *
 * @param mixed  $output      The output data.
 * @param string $export_type The export type.
 * @return mixed The modified output data.
 */
function woo_ce_export_dataset_multisite_override_subscription( $output = null, $export_type = null ) {
    global $export;

    $sites = get_sites();
    if ( ! empty( $sites ) ) {
        foreach ( $sites as $site ) {
            switch_to_blog( $site->blog_id );
            $subscriptions = woo_ce_get_subscriptions( $export->args );
            if ( $subscriptions ) {
                $export->total_rows = count( $subscriptions );
                // XML, RSS and JSON export.
                if ( in_array( $export->export_format, array( 'xml', 'rss', 'json' ), true ) ) {
                    if ( ! empty( $export->fields ) ) {
                        foreach ( $subscriptions as $subscription ) {
                            if ( in_array( $export->export_format, array( 'xml', 'json' ), true ) ) {
                                $child = $output->addChild( apply_filters( 'woo_ce_export_xml_subscription_node', sanitize_key( $export_type ) ) );
                            } elseif ( 'rss' === $export->export_format ) {
                                $child = $output->addChild( 'item' );
                            }
                            if (
                                'json' !== $export->export_format &&
                                apply_filters( 'woo_ce_export_xml_subscription_id_attribute', true )
                            ) {
                                $child->addAttribute( 'id', esc_attr( $subscription ) );
                            }
                            $subscription = woo_ce_get_subscription_data( $subscription, $export->args, array_keys( $export->fields ) );
                            foreach ( array_keys( $export->fields ) as $key => $field ) {
                                if ( isset( $subscription->$field ) ) {
                                    if ( ! is_array( $field ) ) {
                                        if ( woo_ce_is_xml_cdata( $subscription->$field ) ) {
                                            $child->addChild( sanitize_key( $export->columns[ $key ] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $subscription->$field ) ) );
                                        } else {
                                            $child->addChild( sanitize_key( $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $subscription->$field ) ) );
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // PHPExcel export.
                    foreach ( $subscriptions as $key => $subscription ) {
                        $subscriptions[ $key ] = woo_ce_get_subscription_data( $subscription, $export->args, array_keys( $export->fields ) );
                    }
                    if ( is_null( $output ) ) {
                        $output = $subscriptions;
                    } else {
                        $output = array_merge( $output, $subscriptions );
                    }
                }
                unset( $subscriptions, $subscription );
            }
            restore_current_blog();
        }
    }
    return $output;
}

/**
 * Get subscription data.
 *
 * @param int   $subscription_id  The subscription ID.
 * @param array $args             Additional arguments.
 * @param array $fields           Fields to include in the output.
 * @param mixed $export_settings  Export settings.
 * @return object The subscription data.
 */
function woo_ce_get_subscription_data( $subscription_id, $args = array(), $fields = array(), $export_settings = null ) {
    global $export;
    if ( null !== $export_settings ) {
        $export = $export_settings;
    }

    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'begin woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
    }

    $subscription_statuses = woo_ce_get_subscription_statuses();

    $subscription = get_post( $subscription_id );

    $wcs_version = woo_ce_get_wc_subscriptions_version();
    if ( function_exists( 'wcs_get_subscription' ) ) {
        $wcs_subscription = wcs_get_subscription( $subscription_id );
    } elseif ( version_compare( $wcs_version, '1.5.26', '<' ) ) {
        if ( class_exists( 'WC_Subscriptions_Manager' ) ) {
            if ( method_exists( 'WC_Subscriptions_Manager', 'get_subscription' ) ) {
                $wcs_subscription = WC_Subscriptions_Manager::get_subscription( $subscription_id );
            }
        }
    }

    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'before fetching Order linked to Subscription in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
    }

    $order_status = false;
    if ( ! empty( $wcs_subscription ) ) {
        // Check if an Order has been assigned to this Subscription.
        if ( method_exists( $wcs_subscription, 'get_parent' ) ) {
            if ( ! empty( $wcs_subscription->get_parent() ) ) {
                $order_status = $wcs_subscription->get_status();
            }
        }
    } elseif ( ! empty( $subscription->post_parent ) ) {
        $order = get_post( $subscription->post_parent );
        if ( ! empty( $order ) ) {
            $order_status = $order->post_status;
        }
        unset( $order );
    }

    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'after fetching Order linked to Subscription in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
    }

    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'before populating Subscription details in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
    }

    if ( function_exists( 'wcs_get_subscription' ) ) {
        $subscription->order_id        = $wcs_subscription->get_parent_id();
        $subscription->subscription_id = $subscription_id;
        if ( function_exists( 'wcs_get_subscription_status_name' ) ) {
            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
                woo_ce_error_log( sprintf( 'Debug: %s', 'before fetching Subscription status in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
            }

            if ( method_exists( $wcs_subscription, 'get_status' ) ) {
                $subscription->status = wcs_get_subscription_status_name( $wcs_subscription->get_status() );
            } else {
                if ( WOO_CE_LOGGING ) {
                    $message = 'The method $wcs_subscription->get_status() does not exist, defaulting to Post Status';
                    woo_ce_error_log( sprintf( 'Warning: %s', $message . ': ' . ( time() - $export->start_time ) ) );
                }
                $subscription->status = isset( $subscription_statuses[ $subscription->post_status ] ) ? $subscription_statuses[ $subscription->post_status ] : false;
            }

            if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
                woo_ce_error_log( sprintf( 'Debug: %s', 'after fetching Subscription status in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
            }
        } else {
            if ( WOO_CE_LOGGING ) {
                $message = 'The function wcs_get_subscription_status_name() does not exist, defaulting to Post Status';
                woo_ce_error_log( sprintf( 'Warning: %s', $message . ': ' . ( time() - $export->start_time ) ) );
            }
            $subscription->status = isset( $subscription_statuses[ $subscription->post_status ] ) ? $subscription_statuses[ $subscription->post_status ] : false;
        }
        $subscription->status         = woo_ce_format_subscription_status( $subscription->status );
        $subscription->user_id        = get_post_meta( $subscription_id, '_customer_user', true );
        $subscription->user           = woo_ce_get_username( $subscription->user_id );
        $subscription->order_status   = ! empty( $order_status ) ? woo_ce_format_order_status( $order_status ) : '-';
        $subscription->transaction_id = ! empty( $subscription->order_id ) ? get_post_meta( $subscription->order_id, '_transaction_id', true ) : false;

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'before fetching Subscription coupon in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
        }

        try {
            $subscription->coupon = woo_ce_get_order_assoc_coupon( $subscription->order_id );
        } catch ( Exception $e ) {
            // Handle exception.
        }

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'after fetching Subscription coupon in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
        }

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'before fetching Subscription payment method in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
        }

        $subscription->payment_method = method_exists( $wcs_subscription, 'get_payment_method_to_display' ) ? $wcs_subscription->get_payment_method_to_display() : false;

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'after fetching Subscription payment method in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
        }

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'before fetching Subscription recurring in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
        }

        if ( function_exists( 'wcs_get_subscription_period_interval_strings' ) && function_exists( 'wcs_get_subscription_period_strings' ) ) {
            $billing_interval = method_exists( $wcs_subscription, 'get_billing_interval' ) ? $wcs_subscription->get_billing_interval() : false;
            $billing_period   = method_exists( $wcs_subscription, 'get_billing_period' ) ? $wcs_subscription->get_billing_period() : false;
            if (
                ! empty( $billing_interval ) &&
                ! empty( $billing_period )
            ) {
                $subscription->recurring = sprintf( '%s %s', wcs_get_subscription_period_interval_strings( $billing_interval ), wcs_get_subscription_period_strings( 1, $billing_period ) );
            }
        }

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'after fetching Subscription recurring in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
        }

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'before fetching Subscription dates and times in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
        }

        if ( method_exists( $wcs_subscription, 'get_time' ) ) {
            $subscription->start_date     = ( 0 < $wcs_subscription->get_time( 'start' ) ) ? woo_ce_format_date( $wcs_subscription->get_date( 'start' ) ) : '-';
            $subscription->end_date       = ( 0 < $wcs_subscription->get_time( 'end' ) ) ? woo_ce_format_date( $wcs_subscription->get_date( 'end', 'site' ) ) : '-';
            $subscription->trial_end_date = ( 0 < $wcs_subscription->get_time( 'trial_end' ) ) ? woo_ce_format_date( $wcs_subscription->get_date( 'trial_end' ) ) : '-';
            $subscription->next_payment   = ( 0 < $wcs_subscription->get_time( 'next_payment' ) ) ? woo_ce_format_date( $wcs_subscription->get_date( 'next_payment' ) ) : '-';
            $subscription->last_payment   = ( 0 < $wcs_subscription->get_time( 'last_order_date_paid' ) ) ? woo_ce_format_date( $wcs_subscription->get_date( 'last_order_date_paid' ) ) : '-';
        }

        $subscription->sign_up_fee = method_exists( $wcs_subscription, 'get_sign_up_fee' ) ? $wcs_subscription->get_sign_up_fee() : false;

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'after fetching Subscription dates and times in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
        }

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'before fetching Subscription related orders in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
        }

        $subscription->related_orders = method_exists( $wcs_subscription, 'get_related_orders' ) ? count( $wcs_subscription->get_related_orders() ) : 0;

        if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'after fetching Subscription related orders in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
        }

        if ( method_exists( $wcs_subscription, 'get_formatted_order_total' ) ) {
            add_filter( 'wc_price', 'woo_ce_filter_wc_price', 10, 3 );
            add_filter( 'formatted_woocommerce_price', 'woo_ce_formatted_woocommerce_price', 10, 5 );
            add_filter( 'woocommerce_currency_symbol', 'woo_ce_woocommerce_currency_symbol', 10, 2 );
            $subscription->recurring_total = $wcs_subscription->get_formatted_order_total();
            $subscription->recurring_total = str_replace( array( '<span class="amount">', '</span>' ), '', $subscription->recurring_total );
            remove_filter( 'formatted_woocommerce_price', 'woo_ce_formatted_woocommerce_price' );
            remove_filter( 'wc_price', 'woo_ce_filter_wc_price' );
            remove_filter( 'woocommerce_currency_symbol', 'woo_ce_woocommerce_currency_symbol' );
        }
    } elseif ( version_compare( $wcs_version, '1.5.26', '<' ) ) {
        $subscription->order_status    = woo_ce_format_order_status( $subscription->post_status );
        $subscription->subscription_id = $subscription_id;
        $subscription_id               = $wcs_subscription['order_id'];
        $subscription->order_id        = $subscription_id;
        $subscription->recurring       = ! empty( $wcs_subscription['interval'] ) ? sprintf( '%s %s', woo_ce_format_product_subscription_period_interval( $wcs_subscription['interval'] ), $wcs_subscription['period'] ) : '';
        $subscription->start_date      = isset( $wcs_subscription['start_date'] ) ? date_i18n( woocommerce_date_format(), strtotime( $wcs_subscription['start_date'] ) ) : '';
        $subscription->end_date        = ! empty( $wcs_subscription['end_date'] ) ? date_i18n( woocommerce_date_format(), strtotime( $wcs_subscription['end_date'] ) ) : __( 'Not yet ended', 'woocommerce-subscriptions' );
        $subscription->status          = isset( $subscription_statuses[ $wcs_subscription['status'] ] ) ? $subscription_statuses[ $wcs_subscription['status'] ] : $wcs_subscription['status'];
        $subscription->expiration      = ! empty( $wcs_subscription['expiry_date'] ) ? woo_ce_format_subscription_date( $wcs_subscription['expiry_date'] ) : __( 'Never', 'woocommerce-subscriptions' );
    }

    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'after populating Subscription details in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
    }

    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'before merging Order details in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
    }

    // Merge in our Order details.
    $order_args = array(
        'order_items'       => $export->args['subscription_items'],
        'order_items_types' => woo_ce_get_option( 'order_items_types', array() ),
    );
    $order      = woo_ce_get_order_data( $subscription_id, 'order', $order_args, false );

    if ( ! empty( $order ) ) {
        $subscription = (object) array_merge( (array) $subscription, (array) $order );
    }

    if ( version_compare( $wcs_version, '1.5.26', '<' ) ) {
        $user                = woo_ce_get_user_data( $subscription->user_id );
        $subscription->email = isset( $user->email ) ? $user->email : '';
        unset( $user );
        $subscription->user = woo_ce_get_username( $subscription->user_id );
    }

    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'after merging Order details in woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
    }

    // Allow Plugin/Theme authors to add support for additional Product columns.
    $subscription = apply_filters( 'woo_ce_subscription', $subscription, $subscription_id );

    // Trim back the Subscription just to requested export fields.
    if ( ! empty( $fields ) ) {
        $fields = array_merge( $fields, array( 'id', 'ID', 'post_parent', 'filter' ) );
        if (
            'individual' === $args['subscription_items'] ||
            apply_filters( 'woo_ce_get_subscription_data_return_order_items', false )
        ) {
            $fields[] = 'order_items';
        }
        if ( ! empty( $subscription ) ) {
            foreach ( $subscription as $key => $data ) {
                if ( ! in_array( $key, $fields, true ) ) {
                    unset( $subscription->$key );
                }
            }
        }
    }

    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'end woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );
    }

    return $subscription;
}

/**
 * Get subscription statuses.
 *
 * @return array An array of subscription statuses.
 */
function woo_ce_get_subscription_statuses() {
    if ( function_exists( 'wcs_get_subscription_statuses' ) ) {
        $subscription_statuses = wcs_get_subscription_statuses();
    } else {
        $subscription_statuses = array(
            'active'    => esc_html__( 'Active', 'woocommerce-subscriptions' ),
            'cancelled' => esc_html__( 'Cancelled', 'woocommerce-subscriptions' ),
            'suspended' => esc_html__( 'Suspended', 'woocommerce-subscriptions' ),
            'expired'   => esc_html__( 'Expired', 'woocommerce-subscriptions' ),
            'pending'   => esc_html__( 'Pending', 'woocommerce-subscriptions' ),
            'failed'    => esc_html__( 'Failed', 'woocommerce-subscriptions' ),
            'on-hold'   => esc_html__( 'On-hold', 'woocommerce-subscriptions' ),
            'trash'     => esc_html__( 'Deleted', 'woocommerce-exporter' ),
        );
    }
    return apply_filters( 'woo_ce_subscription_statuses', $subscription_statuses );
}

/**
 * Get WooCommerce Subscriptions version.
 *
 * @return string|null The WooCommerce Subscriptions version or null if not available.
 */
function woo_ce_get_wc_subscriptions_version() {
    if ( class_exists( 'WC_Subscriptions' ) ) {
        return WC_Subscriptions::$version;
    }
    return null;
}

/**
 * Get subscription order item.
 *
 * @param int $order_id   The order ID.
 * @param int $product_id The product ID.
 * @return mixed The order item or null if not found.
 */
function woo_ce_get_subscription_order_item( $order_id = 0, $product_id = 0 ) {
    $order_item = null;
    if ( method_exists( 'WC_Subscriptions_Order', 'get_item_by_product_id' ) ) {
        $order_item = WC_Subscriptions_Order::get_item_by_product_id( $order_id, $product_id );
    }
    return $order_item;
}

/**
 * Get subscription product.
 *
 * @param WC_Order|false $order      The order object or false.
 * @param array|false    $order_item The order item or false.
 * @return WC_Product|null The product object or null if not found.
 */
function woo_ce_get_subscription_product( $order = false, $order_item = false ) {
    $product = null;
    // Check that get_product_from_item() exists within the WC_Order class.
    if ( method_exists( 'WC_Order', 'get_product_from_item' ) ) {
        // Check that $order and $order_item aren't empty.
        if ( ! empty( $order ) && ! empty( $order_item ) ) {
            $product = $order->get_product_from_item( $order_item );
        }
    }
    return $product;
}

/**
 * Format subscription date.
 *
 * @param string $end_date The end date to format.
 * @return string The formatted date.
 */
function woo_ce_format_subscription_date( $end_date = '' ) {
    // Date formatting is provided by WooCommerce Subscriptions.
    $current_gmt_time   = gmdate( 'U' );
    $end_date_timestamp = strtotime( $end_date );
    $time_diff          = $current_gmt_time - $end_date_timestamp;
    if ( $time_diff > 0 && $time_diff < 7 * 24 * 60 * 60 ) {
        // Translators: %s: Human-readable time difference.
        $end_date = sprintf( esc_html__( '%s ago', 'woocommerce-subscriptions' ), human_time_diff( $end_date_timestamp, $current_gmt_time ) );
    } else {
        $end_date = date_i18n( woocommerce_date_format(), $end_date_timestamp + get_option( 'gmt_offset' ) * 3600 );
    }
    return $end_date;
}

/**
 * Get subscription products.
 *
 * @return array An array of subscription product IDs.
 */
function woo_ce_get_subscription_products() {
    $term_taxonomy = 'product_type';
    $args          = array(
        'post_type'        => array( 'product', 'product_variation' ),
        'posts_per_page'   => -1,
        'fields'           => 'ids',
        'suppress_filters' => false,
        'tax_query'        => array(
            array(
                'taxonomy' => $term_taxonomy,
                'field'    => 'slug',
                'terms'    => array( 'subscription', 'variable-subscription' ),
            ),
        ),
    );
    $products      = array();
    $product_ids   = new WP_Query( $args );
    if ( $product_ids->posts ) {
        foreach ( $product_ids->posts as $product_id ) {
            $products[] = $product_id;
        }
    }
    return $products;
}

/**
 * Format subscription status.
 *
 * @param string $subscription_status The subscription status to format.
 * @return string The formatted subscription status.
 */
function woo_ce_format_subscription_status( $subscription_status = '' ) {
    $output = $subscription_status;
    switch ( $subscription_status ) {
        case 'active':
            $output = esc_html__( 'Active', 'woocommerce-exporter' );
            break;
        case 'switched':
            $output = esc_html__( 'Switched', 'woocommerce-exporter' );
            break;
        case 'on-hold':
            $output = esc_html__( 'On hold', 'woocommerce-exporter' );
            break;
        case 'pending':
            $output = esc_html__( 'Pending Payment', 'woocommerce-exporter' );
            break;
        case 'pending-cancel':
            $output = esc_html__( 'Pending Cancellation', 'woocommerce-exporter' );
            break;
        case 'cancelled':
            $output = esc_html__( 'Cancelled', 'woocommerce-exporter' );
            break;
        case 'expired':
            $output = esc_html__( 'Expired', 'woocommerce-exporter' );
            break;
        case 'trash':
            $output = esc_html__( 'Trash', 'woocommerce-exporter' );
            break;
    }
    $output = apply_filters( 'woo_ce_format_subscription_status', $output, $subscription_status );
    return $output;
}

/**
 * Format product subscription period interval.
 *
 * @param string $interval The interval to format.
 * @return string The formatted interval.
 */
function woo_ce_format_product_subscription_period_interval( $interval ) {
    $output = $interval;
    if ( ! empty( $interval ) ) {
        switch ( $interval ) {
            case '1':
                $output = esc_html__( 'per', 'woocommerce-exporter' );
                break;
            case '2':
                $output = esc_html__( 'every 2nd', 'woocommerce-exporter' );
                break;
            case '3':
                $output = esc_html__( 'every 3rd', 'woocommerce-exporter' );
                break;
            case '4':
                $output = esc_html__( 'every 4th', 'woocommerce-exporter' );
                break;
            case '5':
                $output = esc_html__( 'every 5th', 'woocommerce-exporter' );
                break;
            case '6':
                $output = esc_html__( 'every 6th', 'woocommerce-exporter' );
                break;
        }
    }
    return $output;
}

/**
 * Format product subscription length.
 *
 * @param string $length The length to format.
 * @param string $period The subscription period (optional).
 * @return string The formatted length.
 */
function woo_ce_format_product_subscripion_length( $length, $period = '' ) {
    $output = $length;
    if ( '0' === $length ) {
        $output = esc_html__( 'all time', 'woocommerce-exporter' );
    }
    return $output;
}

/**
 * Format product subscription limit.
 *
 * @param string $limit The limit to format.
 * @return string The formatted limit.
 */
function woo_ce_format_product_subscription_limit( $limit ) {
    $output = $limit;
    if ( ! empty( $limit ) ) {
        $limit = strtolower( $limit );
        switch ( $limit ) {
            case 'active':
                $output = esc_html__( 'Active Subscription', 'woocommerce-exporter' );
                break;
            case 'any':
                $output = esc_html__( 'Any Subscription', 'woocommerce-exporter' );
                break;
            case 'no':
                $output = esc_html__( 'Do not limit', 'woocommerce-exporter' );
                break;
        }
    }
    return $output;
}
