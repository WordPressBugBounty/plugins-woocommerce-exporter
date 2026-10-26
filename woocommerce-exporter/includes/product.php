<?php
// phpcs:disable
if ( is_admin() ) {

    /* Start of: WordPress Administration */

    if ( ! function_exists( 'woo_ce_get_export_type_product_count' ) ) {
        function woo_ce_get_export_type_product_count() {

            $count     = 0;
            $post_type = apply_filters( 'woo_ce_get_export_type_product_count_post_types', array( 'product', 'product_variation' ) );

            // Override for WordPress MultiSite.
            if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && woo_ce_is_network_admin() ) {
                $sites = get_sites();
                foreach ( $sites as $site ) {
                    switch_to_blog( $site->blog_id );
                    $args        = array(
                        'post_type'        => $post_type,
                        'posts_per_page'   => 1,
                        'fields'           => 'ids',
                        'suppress_filters' => true,
                    );
                    $count_query = new WP_Query( $args );
                    $count      += $count_query->found_posts;
                    restore_current_blog();
                }
                return $count;
            }

            // Check if the existing Transient exists.
            $cached = get_transient( WOO_CE_PREFIX . '_product_count' );
            if ( $cached == false ) {
                $args        = array(
                    'post_type'        => $post_type,
                    'posts_per_page'   => 1,
                    'fields'           => 'ids',
                    'suppress_filters' => true,
                );
                $count_query = new WP_Query( $args );
                $count       = $count_query->found_posts;
                set_transient( WOO_CE_PREFIX . '_product_count', $count, HOUR_IN_SECONDS );
            } else {
                $count = $cached;
            }
            return $count;
        }
    }

    function woo_ce_product_scheduled_export_save( $post_ID = 0 ) {

        update_post_meta( $post_ID, '_filter_product_orderby', ( isset( $_POST['product_filter_orderby'] ) ? sanitize_text_field( $_POST['product_filter_orderby'] ) : false ) );
        update_post_meta( $post_ID, '_filter_product_orderby_field1', ( isset( $_POST['product_filter_orderby_field1'] ) ? sanitize_text_field( $_POST['product_filter_orderby_field1'] ) : false ) );
        update_post_meta( $post_ID, '_filter_product_order_field1', ( isset( $_POST['product_filter_order_field1'] ) ? sanitize_text_field( $_POST['product_filter_order_field1'] ) : false ) );
        update_post_meta( $post_ID, '_filter_product_orderby_field2', ( isset( $_POST['product_filter_orderby_field2'] ) ? sanitize_text_field( $_POST['product_filter_orderby_field2'] ) : false ) );
        update_post_meta( $post_ID, '_filter_product_order_field2', ( isset( $_POST['product_filter_order_field2'] ) ? sanitize_text_field( $_POST['product_filter_order_field2'] ) : false ) );
        update_post_meta( $post_ID, '_filter_product_category', ( isset( $_POST['product_filter_category'] ) ? array_map( 'absint', (array) $_POST['product_filter_category'] ) : false ) );
        update_post_meta( $post_ID, '_filter_product_category_exclude', ( isset( $_POST['product_filter_category_exclude'] ) ? absint( $_POST['product_filter_category_exclude'] ) : false ) );
        update_post_meta( $post_ID, '_filter_product_tag', ( isset( $_POST['product_filter_tag'] ) ? array_map( 'absint', (array) $_POST['product_filter_tag'] ) : false ) );
        update_post_meta( $post_ID, '_filter_product_tag_exclude', ( isset( $_POST['product_filter_tag_exclude'] ) ? absint( $_POST['product_filter_tag_exclude'] ) : false ) );
        update_post_meta( $post_ID, '_filter_product_user_role', ( isset( $_POST['product_filter_user_role'] ) ? array_map( 'sanitize_text_field', (array) $_POST['product_filter_user_role'] ) : false ) );
        update_post_meta( $post_ID, '_filter_product_status', ( isset( $_POST['product_filter_status'] ) ? array_map( 'sanitize_text_field', (array) $_POST['product_filter_status'] ) : false ) );
        update_post_meta( $post_ID, '_filter_product_type', ( isset( $_POST['product_filter_type'] ) ? array_map( 'sanitize_text_field', (array) $_POST['product_filter_type'] ) : false ) );
        update_post_meta( $post_ID, '_filter_product_stock', sanitize_text_field( $_POST['product_filter_stock'] ) );

        $auto_product_quantity  = ( isset( $_POST['product_filter_quantity_operator'] ) ? sanitize_text_field( $_POST['product_filter_quantity_operator'] ) : false );
        $auto_product_quantity .= ( isset( $_POST['product_filter_quantity'] ) ? sanitize_text_field( $_POST['product_filter_quantity'] ) : false );
        update_post_meta( $post_ID, '_filter_product_quantity', $auto_product_quantity );
        update_post_meta( $post_ID, '_filter_product_featured', sanitize_text_field( $_POST['product_filter_featured'] ) );
        update_post_meta( $post_ID, '_filter_product_shipping_class', ( isset( $_POST['product_filter_shipping_class'] ) ? array_map( 'absint', (array) $_POST['product_filter_shipping_class'] ) : false ) );
        // Modified date.
        $auto_product_date                          = sanitize_text_field( $_POST['product_filter_dates'] );
        $auto_product_dates_from                    = false;
        $auto_product_dates_to                      = false;
        $auto_product_modified_date_variable        = false;
        $auto_product_modified_date_variable_length = false;
        if ( $auto_product_date == 'variable' ) {
            $auto_product_modified_date_variable        = sanitize_text_field( $_POST['modified_dates_filter_variable'] );
            $auto_product_modified_date_variable_length = sanitize_text_field( $_POST['modified_dates_filter_variable_length'] );
        } elseif ( $auto_product_date == 'manual' ) {
            $auto_product_dates_from = sanitize_text_field( $_POST['product_filter_dates_from'] );
            $auto_product_dates_to   = sanitize_text_field( $_POST['product_filter_dates_to'] );
        }
        update_post_meta( $post_ID, '_filter_product_date', $auto_product_date );
        update_post_meta( $post_ID, '_filter_product_dates_from', $auto_product_dates_from );
        update_post_meta( $post_ID, '_filter_product_dates_to', $auto_product_dates_to );
        update_post_meta( $post_ID, '_modified_date_variable', $auto_product_modified_date_variable );
        update_post_meta( $post_ID, '_modified_date_variable_length', $auto_product_modified_date_variable_length );
        // Published date.
        $auto_product_published_date                 = sanitize_text_field( $_POST['product_filter_published_dates'] );
        $auto_product_published_dates_from           = false;
        $auto_product_published_dates_to             = false;
        $auto_product_published_date_variable        = false;
        $auto_product_published_date_variable_length = false;
        if ( $auto_product_published_date == 'variable' ) {
            $auto_product_published_date_variable        = sanitize_text_field( $_POST['published_dates_filter_variable'] );
            $auto_product_published_date_variable_length = sanitize_text_field( $_POST['published_dates_filter_variable_length'] );
        } elseif ( $auto_product_published_date == 'manual' ) {
            $auto_product_published_dates_from = sanitize_text_field( $_POST['product_filter_published_dates_from'] );
            $auto_product_published_dates_to   = sanitize_text_field( $_POST['product_filter_published_dates_to'] );
        }
        update_post_meta( $post_ID, '_filter_product_published_date', $auto_product_published_date );
        update_post_meta( $post_ID, '_filter_product_published_dates_from', $auto_product_published_dates_from );
        update_post_meta( $post_ID, '_filter_product_published_dates_to', $auto_product_published_dates_to );
        update_post_meta( $post_ID, '_published_date_variable', $auto_product_published_date_variable );
        update_post_meta( $post_ID, '_published_date_variable_length', $auto_product_published_date_variable_length );
        $auto_product_sku = ( isset( $_POST['product_filter_sku'] ) ? $_POST['product_filter_sku'] : false );
        // Select2 passes us a string whereas Chosen gives us an array.
        if (
            is_array( $auto_product_sku ) &&
            count( $auto_product_sku ) == 1
        ) {
            $auto_product_sku = explode( ',', $auto_product_sku[0] );
        }
        update_post_meta( $post_ID, '_filter_product_sku', ( ! empty( $auto_product_sku ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $auto_product_sku ) ) : false ) );
        update_post_meta( $post_ID, '_filter_product_sku_exclude', ( isset( $_POST['product_filter_sku_exclude'] ) ? absint( $_POST['product_filter_sku_exclude'] ) : false ) );
    }
    add_action( 'woo_ce_extend_scheduled_export_save', 'woo_ce_product_scheduled_export_save' );

    function woo_ce_product_dataset_args( $args, $export_type = '', $form_data = array() ) {
        if ( empty( $form_data ) ) {
            $form_data = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing
        }

        // Check if we're dealing with the Product Export Type.
        if ( ! in_array( $export_type, array( 'product', 'booking' ), true ) ) {
            return $args;
        }

        // The Quick Export SPA names four filters differently to the classic UI:
        // it submits 'product_filter_product_status', 'product_filter_product_type',
        // 'product_filter_stock_status' and 'product_filter_stock_quantity' where
        // this function - and the $defaults below - speak 'product_filter_status',
        // '_type', '_stock' and '_quantity'. Without this mapping the submitted
        // value never reaches $defaults, so the filter is dropped even when its
        // gate passes. Canonical keys win, so a caller that already speaks them
        // is never overwritten.
        $spa_filter_aliases = array(
            'product_filter_product_status'          => 'product_filter_status',
            'product_filter_product_status_enabled'  => 'product_filter_status_enabled',
            'product_filter_product_type'            => 'product_filter_type',
            'product_filter_product_type_enabled'    => 'product_filter_type_enabled',
            'product_filter_stock_status'            => 'product_filter_stock',
            'product_filter_stock_status_enabled'    => 'product_filter_stock_enabled',
            'product_filter_stock_quantity'          => 'product_filter_quantity',
            'product_filter_stock_quantity_operator' => 'product_filter_quantity_operator',
            'product_filter_stock_quantity_enabled'  => 'product_filter_quantity_enabled',
        );
        foreach ( $spa_filter_aliases as $spa_key => $canonical_key ) {
            if ( isset( $form_data[ $spa_key ] ) && ! isset( $form_data[ $canonical_key ] ) ) {
                $form_data[ $canonical_key ] = $form_data[ $spa_key ];
            }
        }

        // Check the state of Filter tick boxes.
        // The classic UI submitted a '{filter}_include' checkbox, but the current
        // Quick Export SPA submits '{filter}_enabled' instead. Accept either so a
        // filter's value survives when the SPA has it toggled on. Both keys
        // are validated with FILTER_VALIDATE_BOOLEAN so an explicitly-off toggle
        // ('0', '', 'false', 'off', 'no') is treated the same as an absent one;
        // a checked box submits '1'/'on'/'true'. Toggles must be scalar: a
        // non-scalar value (e.g. an array-shaped '{filter}_enabled[]') validates
        // to false, so callers such as a REST API or CLI would see the filter
        // silently treated as off.
        if ( ! filter_var( $form_data['product_filter_category_include'] ?? false, FILTER_VALIDATE_BOOLEAN ) && ! filter_var( $form_data['product_filter_category_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
            unset( $form_data['product_filter_category'] );
            unset( $form_data['product_filter_category_exclude'] );
        }
        if ( ! filter_var( $form_data['product_filter_status_include'] ?? false, FILTER_VALIDATE_BOOLEAN ) && ! filter_var( $form_data['product_filter_status_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
            unset( $form_data['product_filter_status'] );
        }
        if ( ! filter_var( $form_data['product_filter_tag_include'] ?? false, FILTER_VALIDATE_BOOLEAN ) && ! filter_var( $form_data['product_filter_tag_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
            unset( $form_data['product_filter_tag'] );
            unset( $form_data['product_filter_tag_exclude'] );
        }
        // These two gates unset 'product_filter_brand'/'product_filter_vendor', the
        // same keys $defaults below reads, so an off toggle correctly drops the
        // filter value.
        if ( ! filter_var( $form_data['product_filter_brand_include'] ?? false, FILTER_VALIDATE_BOOLEAN ) && ! filter_var( $form_data['product_filter_brand_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
            unset( $form_data['product_filter_brand'] );
        }
        if ( ! filter_var( $form_data['product_filter_vendor_include'] ?? false, FILTER_VALIDATE_BOOLEAN ) && ! filter_var( $form_data['product_filter_vendor_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
            unset( $form_data['product_filter_vendor'] );
        }
        if ( ! filter_var( $form_data['product_filter_type_include'] ?? false, FILTER_VALIDATE_BOOLEAN ) && ! filter_var( $form_data['product_filter_type_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
            unset( $form_data['product_filter_type'] );
        }
        if ( ! isset( $form_data['product_filter_sku_include'] ) ) {
            unset( $form_data['product_filter_sku'] );
            unset( $form_data['product_filter_sku_exclude'] );
        }
        if ( ! filter_var( $form_data['product_filter_user_role_include'] ?? false, FILTER_VALIDATE_BOOLEAN ) && ! filter_var( $form_data['product_filter_user_role_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
            unset( $form_data['product_filter_user_role'] );
        }
        if ( ! filter_var( $form_data['product_filter_stock_include'] ?? false, FILTER_VALIDATE_BOOLEAN ) && ! filter_var( $form_data['product_filter_stock_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
            unset( $form_data['product_filter_stock'] );
        }
        if ( ! filter_var( $form_data['product_filter_quantity_include'] ?? false, FILTER_VALIDATE_BOOLEAN ) && ! filter_var( $form_data['product_filter_quantity_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
            unset( $form_data['product_filter_quantity'], $form_data['product_filter_quantity_operator'] );
        }
        if ( ! filter_var( $form_data['product_filter_shipping_class_include'] ?? false, FILTER_VALIDATE_BOOLEAN ) && ! filter_var( $form_data['product_filter_shipping_class_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
            unset( $form_data['product_filter_shipping_class'] );
        }
        if ( ! isset( $form_data['product_filter_featured_image_include'] ) ) {
            unset( $form_data['product_filter_featured_image'] );
        }
        if ( ! isset( $form_data['product_filter_product_gallery_include'] ) ) {
            unset( $form_data['product_filter_product_gallery'] );
        }
        if ( ! isset( $form_data['product_filter_language_include'] ) ) {
            unset( $form_data['product_filter_language'] );
        }
        // The SPA's Modified Date toggle is named after its own control
        // ('product_modified_dates_filter_enabled'), not derived from the legacy
        // 'product_filter_dates_include' key, so it has to be named explicitly here.
        // The bounds unset alongside 'product_filter_dates' must be
        // 'product_dates_from'/'_to' - the keys $defaults below actually reads -
        // not 'product_filter_dates_from'/'_to', which nothing here reads (the
        // classic form's own '_filter_dates_from'/'_to' keys instead reach
        // woo_ce_cron_product_dataset_args()). Unsetting the wrong pair left the
        // real bounds sitting in $args behind an off toggle. They were inert
        // there - the preset is unset too, so the reader's switch falls through
        // and blanks them - but the gate was not doing what it reads as doing,
        // which is how the sibling brand/vendor gates above became live bugs.
        if ( ! filter_var( $form_data['product_filter_dates_include'] ?? false, FILTER_VALIDATE_BOOLEAN ) && ! filter_var( $form_data['product_modified_dates_filter_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
            unset( $form_data['product_filter_dates'], $form_data['product_dates_from'], $form_data['product_dates_to'] );
        }

        // Merge in the form data for this dataset.
        $defaults                     = array(
            'product_category'                       => ( isset( $form_data['product_filter_category'] ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $form_data['product_filter_category'] ) ) : false ),
            'product_category_exclude'               => ( isset( $form_data['product_filter_category_exclude'] ) ? absint( $form_data['product_filter_category_exclude'] ) : false ),
            'product_tag'                            => ( isset( $form_data['product_filter_tag'] ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $form_data['product_filter_tag'] ) ) : false ),
            'product_tag_exclude'                    => ( isset( $form_data['product_filter_tag_exclude'] ) ? absint( $form_data['product_filter_tag_exclude'] ) : false ),
            'product_brand'                          => ( isset( $form_data['product_filter_brand'] ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $form_data['product_filter_brand'] ) ) : false ),
            'product_vendor'                         => ( isset( $form_data['product_filter_vendor'] ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $form_data['product_filter_vendor'] ) ) : false ),
            'product_status'                         => ( isset( $form_data['product_filter_status'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', (array) $form_data['product_filter_status'] ) ) : false ),
            'product_type'                           => ( isset( $form_data['product_filter_type'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', (array) $form_data['product_filter_type'] ) ) : false ),
            'product_sku'                            => ( isset( $form_data['product_filter_sku'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', (array) $form_data['product_filter_sku'] ) ) : false ),
            'product_sku_exclude'                    => ( isset( $form_data['product_filter_sku_exclude'] ) ? absint( $form_data['product_filter_sku_exclude'] ) : false ),
            'product_user_role'                      => ( isset( $form_data['product_filter_user_role'] ) ? woo_ce_format_user_role_filters( array_map( 'sanitize_text_field', (array) $form_data['product_filter_user_role'] ) ) : false ),
            'product_stock'                          => ( isset( $form_data['product_filter_stock'] ) ? sanitize_text_field( $form_data['product_filter_stock'] ) : false ),
            'product_featured'                       => ( isset( $form_data['product_filter_featured'] ) ? sanitize_text_field( $form_data['product_filter_featured'] ) : false ),
            'product_shipping_class'                 => ( isset( $form_data['product_filter_shipping_class'] ) ? woo_ce_format_product_filters( array_map( 'absint', (array) $form_data['product_filter_shipping_class'] ) ) : false ),
            'product_featured_image'                 => ( isset( $form_data['product_filter_featured_image'] ) ? sanitize_text_field( $form_data['product_filter_featured_image'] ) : false ),
            'product_gallery'                        => ( isset( $form_data['product_filter_product_gallery'] ) ? sanitize_text_field( $form_data['product_filter_product_gallery'] ) : false ),
            'product_language'                       => ( isset( $form_data['product_filter_language'] ) ? array_map( 'sanitize_text_field', (array) $form_data['product_filter_language'] ) : false ),
            'product_published_dates'                => ( isset( $form_data['product_filter_published_dates'] ) ? sanitize_text_field( $form_data['product_filter_published_dates'] ) : false ),
            'product_published_dates_from'           => ( isset( $form_data['product_published_dates_from'] ) ? woo_ce_format_order_date( sanitize_text_field( $form_data['product_published_dates_from'] ) ) : '' ),
            'product_published_dates_to'             => ( isset( $form_data['product_published_dates_to'] ) ? woo_ce_format_order_date( sanitize_text_field( $form_data['product_published_dates_to'] ) ) : '' ),
            'published_dates_filter_variable'        => ( isset( $form_data['published_dates_filter_variable'] ) ? absint( $form_data['published_dates_filter_variable'] ) : false ),
            'published_dates_filter_variable_length' => ( isset( $form_data['published_dates_filter_variable_length'] ) ? sanitize_text_field( $form_data['published_dates_filter_variable_length'] ) : false ),
            'product_dates'                          => ( isset( $form_data['product_filter_dates'] ) ? sanitize_text_field( $form_data['product_filter_dates'] ) : false ),
            'product_dates_from'                     => ( isset( $form_data['product_dates_from'] ) ? woo_ce_format_order_date( sanitize_text_field( $form_data['product_dates_from'] ) ) : '' ),
            'product_dates_to'                       => ( isset( $form_data['product_dates_to'] ) ? woo_ce_format_order_date( sanitize_text_field( $form_data['product_dates_to'] ) ) : '' ),
            'modified_dates_filter_variable'         => ( isset( $form_data['modified_dates_filter_variable'] ) ? absint( $form_data['modified_dates_filter_variable'] ) : false ),
            'modified_dates_filter_variable_length'  => ( isset( $form_data['modified_dates_filter_variable_length'] ) ? sanitize_text_field( $form_data['modified_dates_filter_variable_length'] ) : false ),
            'grouped_formatting'                     => ( isset( $form_data['product_grouped_formatting'] ) ? absint( $form_data['product_grouped_formatting'] ) : false ),
            'upsell_formatting'                      => ( isset( $form_data['product_upsell_formatting'] ) ? absint( $form_data['product_upsell_formatting'] ) : false ),
            'crosssell_formatting'                   => ( isset( $form_data['product_crosssell_formatting'] ) ? absint( $form_data['product_crosssell_formatting'] ) : false ),
            'variation_formatting'                   => ( isset( $form_data['variation_formatting'] ) ? absint( $form_data['variation_formatting'] ) : false ),
            'product_image_formatting'               => ( isset( $form_data['product_image_formatting'] ) ? absint( $form_data['product_image_formatting'] ) : false ),
            'gallery_formatting'                     => ( isset( $form_data['product_gallery_formatting'] ) ? absint( $form_data['product_gallery_formatting'] ) : false ),
            'gallery_unique'                         => ( isset( $form_data['product_gallery_unique'] ) ? absint( $form_data['product_gallery_unique'] ) : false ),
            'max_product_gallery'                    => ( isset( $form_data['max_product_gallery'] ) ? absint( $form_data['max_product_gallery'] ) : false ),
            'product_orderby'                        => ( isset( $form_data['product_orderby'] ) ? sanitize_text_field( $form_data['product_orderby'] ) : false ),
            'product_order'                          => ( isset( $form_data['product_order'] ) ? sanitize_text_field( $form_data['product_order'] ) : false ),
            'product_orderby_field1'                 => ( isset( $form_data['product_orderby_field1'] ) ? sanitize_text_field( $form_data['product_orderby_field1'] ) : false ),
            'product_order_field1'                   => ( isset( $form_data['product_order_field1'] ) ? sanitize_text_field( $form_data['product_order_field1'] ) : false ),
            'product_orderby_field2'                 => ( isset( $form_data['product_orderby_field2'] ) ? sanitize_text_field( $form_data['product_orderby_field2'] ) : false ),
            'product_order_field2'                   => ( isset( $form_data['product_order_field2'] ) ? sanitize_text_field( $form_data['product_order_field2'] ) : false ),
        );
        $defaults['product_quantity'] = ( isset( $form_data['product_filter_quantity_operator'] ) ? sanitize_text_field( $form_data['product_filter_quantity_operator'] ) : false );
        if ( ! empty( $defaults['product_quantity'] ) ) {
            $defaults['product_quantity'] .= ( isset( $form_data['product_filter_quantity'] ) ? sanitize_text_field( $form_data['product_filter_quantity'] ) : false );
            $defaults['product_quantity']  = htmlspecialchars_decode( $defaults['product_quantity'] );
        }
        $custom_products = woo_ce_get_option( 'custom_products', '' );
        if ( ! empty( $custom_products ) ) {
            foreach ( $custom_products as $custom_product ) {
                $custom_meta              = sprintf( 'product_filter_custom_meta-%s', esc_attr( $custom_product ) );
                $defaults[ $custom_meta ] = ( isset( $form_data[ $custom_meta ] ) ? sanitize_text_field( $form_data[ $custom_meta ] ) : false );
            }
        }
        $args = wp_parse_args( $args, $defaults );

        // Save dataset export specific options.
        if ( woo_ce_get_option( 'product_category' ) !== $args['product_category'] ) {
            woo_ce_update_option( 'product_category', $args['product_category'] );
        }
        if ( woo_ce_get_option( 'product_category_exclude' ) !== $args['product_category_exclude'] ) {
            woo_ce_update_option( 'product_category_exclude', $args['product_category_exclude'] );
        }
        if ( woo_ce_get_option( 'product_tag' ) !== $args['product_tag'] ) {
            woo_ce_update_option( 'product_tag', $args['product_tag'] );
        }
        if ( woo_ce_get_option( 'product_tag_exclude' ) !== $args['product_tag_exclude'] ) {
            woo_ce_update_option( 'product_tag_exclude', $args['product_tag_exclude'] );
        }
        if ( woo_ce_get_option( 'product_brand' ) !== $args['product_brand'] ) {
            woo_ce_update_option( 'product_brand', $args['product_brand'] );
        }
        // Vendor.
        if ( woo_ce_get_option( 'product_status' ) !== $args['product_status'] ) {
            woo_ce_update_option( 'product_status', $args['product_status'] );
        }
        if ( woo_ce_get_option( 'product_type' ) !== $args['product_type'] ) {
            woo_ce_update_option( 'product_type', $args['product_type'] );
        }
        // SKU.
        if ( woo_ce_get_option( 'product_stock' ) !== $args['product_stock'] ) {
            woo_ce_update_option( 'product_stock', $args['product_stock'] );
        }
        if ( woo_ce_get_option( 'product_quantity' ) !== $args['product_quantity'] ) {
            woo_ce_update_option( 'product_quantity', $args['product_quantity'] );
        }
        if ( woo_ce_get_option( 'product_user_role' ) !== $args['product_user_role'] ) {
            woo_ce_update_option( 'product_user_role', $args['product_user_role'] );
        }
        if ( woo_ce_get_option( 'product_featured' ) !== $args['product_featured'] ) {
            woo_ce_update_option( 'product_featured', $args['product_featured'] );
        }
        // Shipping Class.
        if ( woo_ce_get_option( 'product_featured_image' ) !== $args['product_featured_image'] ) {
            woo_ce_update_option( 'product_featured_image', $args['product_featured_image'] );
        }
        if ( woo_ce_get_option( 'product_gallery' ) !== $args['product_gallery'] ) {
            woo_ce_update_option( 'product_gallery', $args['product_gallery'] );
        }
        // Language.
        // Modified date.
        if ( woo_ce_get_option( 'product_dates' ) !== $args['product_dates'] ) {
            woo_ce_update_option( 'product_dates', $args['product_dates'] );
        }
        if ( woo_ce_get_option( 'product_dates_from' ) !== $args['product_dates_from'] ) {
            woo_ce_update_option( 'product_dates_from', woo_ce_format_order_date( $args['product_dates_from'], 'save' ) );
        }
        if ( woo_ce_get_option( 'product_dates_to' ) !== $args['product_dates_to'] ) {
            woo_ce_update_option( 'product_dates_to', woo_ce_format_order_date( $args['product_dates_to'], 'save' ) );
        }
        if ( woo_ce_get_option( 'modified_dates_filter_variable' ) !== $args['modified_dates_filter_variable'] ) {
            woo_ce_update_option( 'modified_dates_filter_variable', $args['modified_dates_filter_variable'] );
        }
        if ( woo_ce_get_option( 'modified_dates_filter_variable_length' ) !== $args['modified_dates_filter_variable_length'] ) {
            woo_ce_update_option( 'modified_dates_filter_variable_length', $args['modified_dates_filter_variable_length'] );
        }
        // Published date.
        if ( woo_ce_get_option( 'product_published_dates' ) !== $args['product_published_dates'] ) {
            woo_ce_update_option( 'product_published_dates', $args['product_published_dates'] );
        }
        if ( woo_ce_get_option( 'product_published_dates_from' ) !== $args['product_published_dates_from'] ) {
            woo_ce_update_option( 'product_published_dates_from', woo_ce_format_order_date( $args['product_published_dates_from'], 'save' ) );
        }
        if ( woo_ce_get_option( 'product_published_dates_to' ) !== $args['product_published_dates_to'] ) {
            woo_ce_update_option( 'product_published_dates_to', woo_ce_format_order_date( $args['product_published_dates_to'], 'save' ) );
        }
        if ( woo_ce_get_option( 'published_dates_filter_variable' ) !== $args['published_dates_filter_variable'] ) {
            woo_ce_update_option( 'published_dates_filter_variable', $args['published_dates_filter_variable'] );
        }
        if ( woo_ce_get_option( 'published_dates_filter_variable_length' ) !== $args['published_dates_filter_variable_length'] ) {
            woo_ce_update_option( 'published_dates_filter_variable_length', $args['published_dates_filter_variable_length'] );
        }
        if ( woo_ce_get_option( 'grouped_formatting' ) !== $args['grouped_formatting'] ) {
            woo_ce_update_option( 'grouped_formatting', $args['grouped_formatting'] );
        }
        if ( woo_ce_get_option( 'upsell_formatting' ) !== $args['upsell_formatting'] ) {
            woo_ce_update_option( 'upsell_formatting', $args['upsell_formatting'] );
        }
        if ( woo_ce_get_option( 'crosssell_formatting' ) !== $args['crosssell_formatting'] ) {
            woo_ce_update_option( 'crosssell_formatting', $args['crosssell_formatting'] );
        }
        if ( woo_ce_get_option( 'variation_formatting' ) !== $args['variation_formatting'] ) {
            woo_ce_update_option( 'variation_formatting', $args['variation_formatting'] );
        }
        if ( woo_ce_get_option( 'product_image_formatting' ) !== $args['product_image_formatting'] ) {
            woo_ce_update_option( 'product_image_formatting', $args['product_image_formatting'] );
        }
        if ( woo_ce_get_option( 'gallery_formatting' ) !== $args['gallery_formatting'] ) {
            woo_ce_update_option( 'gallery_formatting', $args['gallery_formatting'] );
        }
        if ( woo_ce_get_option( 'gallery_unique' ) !== $args['gallery_unique'] ) {
            woo_ce_update_option( 'gallery_unique', $args['gallery_unique'] );
        }
        if ( woo_ce_get_option( 'max_product_gallery' ) !== $args['max_product_gallery'] ) {
            woo_ce_update_option( 'max_product_gallery', $args['max_product_gallery'] );
        }
        if ( woo_ce_get_option( 'product_orderby' ) !== $args['product_orderby'] ) {
            woo_ce_update_option( 'product_orderby', $args['product_orderby'] );
        }
        if ( woo_ce_get_option( 'product_order' ) !== $args['product_order'] ) {
            woo_ce_update_option( 'product_order', $args['product_order'] );
        }
        if ( woo_ce_get_option( 'product_orderby_field1' ) !== $args['product_orderby_field1'] ) {
            woo_ce_update_option( 'product_orderby_field1', $args['product_orderby_field1'] );
        }
        if ( woo_ce_get_option( 'product_order_field1' ) !== $args['product_order_field1'] ) {
            woo_ce_update_option( 'product_order_field1', $args['product_order_field1'] );
        }
        if ( woo_ce_get_option( 'product_orderby_field2' ) !== $args['product_orderby_field2'] ) {
            woo_ce_update_option( 'product_orderby_field2', $args['product_orderby_field2'] );
        }
        if ( woo_ce_get_option( 'product_order_field2' ) !== $args['product_order_field2'] ) {
            woo_ce_update_option( 'product_order_field2', $args['product_order_field2'] );
        }
        // Product meta.
        if ( ! empty( $custom_products ) ) {
            foreach ( $custom_products as $custom_product ) {
                $custom_meta = sprintf( 'product_filter_custom_meta-%s', esc_attr( $custom_product ) );
                if ( woo_ce_get_option( $custom_meta ) !== $args[ $custom_meta ] ) {
                    woo_ce_update_option( $custom_meta, $args[ $custom_meta ] );
                }
            }
        }

        return $args;
    }
    add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_product_dataset_args', 10, 3 );

	/* End of: WordPress Administration */
	/* End of: WordPress Administration */

    /* End of: WordPress Administration */

}

function woo_ce_cron_product_dataset_args( $args, $post_id, $export_type = '', $is_scheduled = 0 ) {

    // Check if we're dealing with the Product Export Type.
    if ( 'product' !== $export_type ) {
        return $args;
    }

    $product_orderby                               = false;
    $product_filter_category                       = false;
    $product_filter_category_exclude               = false;
    $product_filter_tag                            = false;
    $product_filter_tag_exclude                    = false;
    $product_filter_status                         = false;
    $product_filter_type                           = false;
    $product_filter_sku                            = false;
    $product_filter_sku_exclude                    = false;
    $product_filter_user_role                      = false;
    $product_filter_stock                          = false;
    $product_filter_quantity                       = false;
    $product_filter_featured                       = false;
    $product_filter_shipping_class                 = false;
    $product_filter_date                           = false;
    $product_filter_dates_from                     = false;
    $product_filter_dates_to                       = false;
    $product_filter_modified_date_variable         = false;
    $product_filter_modified_date_variable_length  = false;
    $product_filter_published_date                 = false;
    $product_filter_published_dates_from           = false;
    $product_filter_published_dates_to             = false;
    $product_filter_published_date_variable        = false;
    $product_filter_published_date_variable_length = false;
    $product_format_grouped_formatting             = woo_ce_get_option( 'grouped_formatting', 1 );
    $product_format_upsell_formatting              = woo_ce_get_option( 'upsell_formatting', 1 );
    $product_format_crosssell_formatting           = woo_ce_get_option( 'crosssell_formatting', 1 );
    $product_format_image_formatting               = woo_ce_get_option( 'product_image_formatting', 1 );
    $product_format_gallery_formatting             = woo_ce_get_option( 'gallery_formatting', 1 );
    $product_format_max_product_gallery            = woo_ce_get_option( 'max_product_gallery', 3 );

    if ( $is_scheduled ) {

        $scheduled_export = $post_id;

        $product_orderby                 = get_post_meta( $scheduled_export, '_filter_product_orderby', true );
        $product_filter_category         = get_post_meta( $scheduled_export, '_filter_product_category', true );
        $product_filter_category_exclude = get_post_meta( $scheduled_export, '_filter_product_category_exclude', true );
        $product_filter_tag              = get_post_meta( $scheduled_export, '_filter_product_tag', true );
        $product_filter_tag_exclude      = get_post_meta( $scheduled_export, '_filter_product_tag_exclude', true );
        $product_filter_status           = get_post_meta( $scheduled_export, '_filter_product_status', true );
        $product_filter_type             = get_post_meta( $scheduled_export, '_filter_product_type', true );
        $product_filter_sku              = get_post_meta( $scheduled_export, '_filter_product_sku', true );
        $product_filter_sku_exclude      = get_post_meta( $scheduled_export, '_filter_product_sku_exclude', true );
        $product_filter_user_role        = get_post_meta( $scheduled_export, '_filter_product_user_role', true );
        $product_filter_stock            = get_post_meta( $scheduled_export, '_filter_product_stock', true );
        $product_filter_quantity         = get_post_meta( $scheduled_export, '_filter_product_quantity', true );
        $product_filter_quantity         = htmlspecialchars_decode( $product_filter_quantity );
        $product_filter_featured         = get_post_meta( $scheduled_export, '_filter_product_featured', true );
        $product_filter_shipping_class   = get_post_meta( $scheduled_export, '_filter_product_shipping_class', true );
        // Modified date.
        $product_filter_date = get_post_meta( $scheduled_export, '_filter_product_date', true );
        if ( $product_filter_date ) {
            switch ( $product_filter_date ) {

                case 'variable':
                    $product_filter_modified_date_variable        = get_post_meta( $scheduled_export, '_modified_date_variable', true );
                    $product_filter_modified_date_variable_length = get_post_meta( $scheduled_export, '_modified_date_variable_length', true );
                    break;

                case 'manual':
                    $product_filter_dates_from = get_post_meta( $scheduled_export, '_filter_product_dates_from', true );
                    $product_filter_dates_to   = get_post_meta( $scheduled_export, '_filter_product_dates_to', true );
                    $product_filter_dates_from = ( ! empty( $product_filter_dates_from ) ? sanitize_text_field( $product_filter_dates_from ) : false );
                    $product_filter_dates_to   = ( ! empty( $product_filter_dates_to ) ? sanitize_text_field( $product_filter_dates_to ) : false );
                    break;
            }
        }
        // Published date.
        $product_filter_published_date = get_post_meta( $scheduled_export, '_filter_product_published_date', true );
        if ( $product_filter_published_date ) {
            switch ( $product_filter_published_date ) {

                case 'variable':
                    $product_filter_published_date_variable        = get_post_meta( $scheduled_export, '_published_date_variable', true );
                    $product_filter_published_date_variable_length = get_post_meta( $scheduled_export, '_published_date_variable_length', true );
                    break;

                case 'manual':
                    $product_filter_published_dates_from = get_post_meta( $scheduled_export, '_filter_product_published_dates_from', true );
                    $product_filter_published_dates_to   = get_post_meta( $scheduled_export, '_filter_product_published_dates_to', true );
                    $product_filter_published_dates_from = ( ! empty( $product_filter_published_dates_from ) ? sanitize_text_field( $product_filter_published_dates_from ) : false );
                    $product_filter_published_dates_to   = ( ! empty( $product_filter_published_dates_to ) ? sanitize_text_field( $product_filter_published_dates_to ) : false );
                    break;
            }
        }
        $product_format_grouped_formatting = get_post_meta( $scheduled_export, '_grouped_formatting', true );
        $product_format_image_formatting   = get_post_meta( $scheduled_export, '_product_image_formatting', true );
        $product_format_gallery_formatting = get_post_meta( $scheduled_export, '_gallery_formatting', true );
    } else {

        if ( isset( $_GET['product_category'] ) ) {
            $product_filter_category = sanitize_text_field( $_GET['product_category'] );
            if ( ! empty( $product_filter_category ) ) {
                $product_filter_category = explode( ',', $product_filter_category );
                $product_filter_category = array_map( 'absint', (array) $product_filter_category );
            }
        }

        if ( isset( $_GET['product_tag'] ) ) {
            $product_filter_tag = sanitize_text_field( $_GET['product_tag'] );
            if ( ! empty( $product_filter_tag ) ) {
                $product_filter_tag = explode( ',', $product_filter_tag );
                $product_filter_tag = array_map( 'absint', (array) $product_filter_tag );
            }
        }

        $args['product_status'] = ( isset( $_GET['product_status'] ) ? sanitize_text_field( $_GET['product_status'] ) : null );
        // Override Filter Products by Status if a single Product transient is set.
        $single_export_status = get_transient( WOO_CE_PREFIX . '_single_export_product_status' );
        if ( $single_export_status != false ) {
            $args['product_status'] = $single_export_status;
            delete_transient( WOO_CE_PREFIX . '_single_export_product_status' );
        }
        unset( $single_export_status );

        if ( isset( $_GET['product_type'] ) ) {
            $product_filter_type = sanitize_text_field( $_GET['product_type'] );
            $product_filter_type = explode( ',', $product_filter_type );
        }

        if ( isset( $_GET['product_sku'] ) ) {
            $product_filter_sku = sanitize_text_field( $_GET['product_sku'] );
            $product_filter_sku = explode( ',', $product_filter_sku );
        }

        if ( isset( $_GET['product_sku_exclude'] ) ) {
            $product_filter_sku_exclude = absint( $_GET['product_sku_exclude'] );
        }

        $product_filter_stock    = ( isset( $_GET['stock_status'] ) ? sanitize_text_field( $_GET['stock_status'] ) : null );
        $product_filter_quantity = ( isset( $_GET['product_quantity'] ) ? sanitize_text_field( $_GET['product_quantity'] ) : null );
        $product_filter_quantity = htmlspecialchars_decode( $product_filter_quantity );
        $product_filter_featured = ( isset( $_GET['product_featured'] ) ? sanitize_text_field( $_GET['product_featured'] ) : null );

        if ( isset( $_GET['shipping_class'] ) ) {
            $product_filter_shipping_class = sanitize_text_field( $_GET['shipping_class'] );
            if ( ! empty( $product_filter_shipping_class ) ) {
                $product_filter_shipping_class = explode( ',', $product_filter_shipping_class );
            }
        }

        if ( isset( $_GET['grouped_formatting'] ) ) {
            $product_format_grouped_formatting = absint( $_GET['grouped_formatting'] );
        }

        if ( isset( $_GET['upsell_formatting'] ) ) {
            $product_format_upsell_formatting = absint( $_GET['upsell_formatting'] );
        }

        if ( isset( $_GET['crosssell_formatting'] ) ) {
            $product_format_crosssell_formatting = absint( $_GET['crosssell_formatting'] );
        }

        if ( isset( $_GET['product_image_formatting'] ) ) {
            $product_format_image_formatting = absint( $_GET['product_image_formatting'] );
        }

        if ( isset( $_GET['gallery_formatting'] ) ) {
            $product_format_gallery_formatting = absint( $_GET['gallery_formatting'] );
        }

        if ( isset( $_GET['max_product_gallery'] ) ) {
            $product_format_max_product_gallery = absint( $_GET['max_product_gallery'] );
        }

        $product_filter_post_ids = ( isset( $_GET['product_ids'] ) ? sanitize_text_field( $_GET['product_ids'] ) : null );
        // Override Filter Products by Product ID if a single Product transient is set.
        $single_export_product_ids = get_transient( WOO_CE_PREFIX . '_single_export_post_ids' );
        if ( $single_export_product_ids != false ) {
            $product_filter_post_ids = sanitize_text_field( $single_export_product_ids );
            // Reset the Product Type filter to all Product Types, including Variations.
            $product_filter_type = array_keys( (array) woo_ce_get_product_types() );
        }
        unset( $single_export_product_ids );
    }

    // Merge in the form data for this dataset.
    $overrides = array(
        'product_orderby'                        => ( ! empty( $product_orderby ) ? $product_orderby : false ),
        'product_category'                       => ( ! empty( $product_filter_category ) ? $product_filter_category : false ),
        'product_category_exclude'               => ( ! empty( $product_filter_category_exclude ) ? $product_filter_category_exclude : false ),
        'product_tag'                            => ( ! empty( $product_filter_tag ) ? $product_filter_tag : false ),
        'product_tag_exclude'                    => ( ! empty( $product_filter_tag_exclude ) ? $product_filter_tag_exclude : false ),
        'product_vendor'                         => ( ! empty( $product_filter_vendor ) ? $product_filter_vendor : false ),
        'product_status'                         => ( ! empty( $product_filter_status ) ? $product_filter_status : false ),
        'product_type'                           => ( ! empty( $product_filter_type ) ? $product_filter_type : false ),
        'product_sku'                            => ( ! empty( $product_filter_sku ) ? (array) $product_filter_sku : array() ),
        'product_sku_exclude'                    => ( ! empty( $product_filter_sku_exclude ) ? $product_filter_sku_exclude : false ),
        'product_user_role'                      => ( ! empty( $product_filter_user_role ) ? array_map( 'sanitize_text_field', (array) $product_filter_user_role ) : false ),
        'product_stock'                          => ( ! empty( $product_filter_stock ) ? $product_filter_stock : false ),
        'product_quantity'                       => ( ! empty( $product_filter_quantity ) ? $product_filter_quantity : false ),
        'product_featured'                       => ( ! empty( $product_filter_featured ) ? $product_filter_featured : false ),
        'product_ids'                            => ( ! empty( $product_filter_post_ids ) ? $product_filter_post_ids : false ),
        'product_shipping_class'                 => ( ! empty( $product_filter_shipping_class ) ? $product_filter_shipping_class : false ),
        'product_dates'                          => ( ! empty( $product_filter_date ) ? $product_filter_date : false ),
        'product_dates_from'                     => ( ! empty( $product_filter_dates_from ) ? $product_filter_dates_from : false ),
        'product_dates_to'                       => ( ! empty( $product_filter_dates_to ) ? $product_filter_dates_to : false ),
        'modified_dates_filter_variable'         => ( ! empty( $product_filter_modified_date_variable ) ? $product_filter_modified_date_variable : false ),
        'modified_dates_filter_variable_length'  => ( ! empty( $product_filter_modified_date_variable_length ) ? $product_filter_modified_date_variable_length : false ),
        'product_published_dates'                => ( ! empty( $product_filter_published_date ) ? $product_filter_published_date : false ),
        'product_published_dates_from'           => ( ! empty( $product_filter_published_dates_from ) ? $product_filter_published_dates_from : false ),
        'product_published_dates_to'             => ( ! empty( $product_filter_published_dates_to ) ? $product_filter_published_dates_to : false ),
        'published_dates_filter_variable'        => ( ! empty( $product_filter_published_date_variable ) ? $product_filter_published_date_variable : false ),
        'published_dates_filter_variable_length' => ( ! empty( $product_filter_published_date_variable_length ) ? $product_filter_published_date_variable_length : false ),
        'product_image_formatting'               => $product_format_image_formatting,
        'gallery_formatting'                     => $product_format_gallery_formatting,
        'gallery_unique'                         => woo_ce_get_option( 'gallery_unique', 0 ),
        'grouped_formatting'                     => $product_format_grouped_formatting,
        'upsell_formatting'                      => $product_format_upsell_formatting,
        'crosssell_formatting'                   => $product_format_crosssell_formatting,
        'max_product_gallery'                    => $product_format_max_product_gallery,
    );

    $args = wp_parse_args( $overrides, $args );

    return $args;
}
add_filter( 'woo_ce_extend_cron_dataset_args', 'woo_ce_cron_product_dataset_args', 10, 4 );

// Returns a list of Product export columns
function woo_ce_get_product_fields( $format = 'full' ) {

	$export_type = 'product';

	$fields = array();
	$fields[] = array(
		'name' => 'parent_id',
		'label' => __( 'Parent ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'parent_sku',
		'label' => __( 'Parent SKU', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_id',
		'label' => __( 'Product ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'sku',
		'label' => __( 'Product SKU', 'woocommerce-exporter' )
	);
    $fields[] = array(
        'name'  => 'global_unique_id',
        'label' => __( 'GTIN, UPC, EAN, or ISBN', 'woocommerce-exporter' ),
    );
	$fields[] = array(
		'name' => 'name',
		'label' => __( 'Product Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_title',
		'label' => __( 'Post Title', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'slug',
		'label' => __( 'Slug', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'permalink',
		'label' => __( 'Permalink', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_url',
		'label' => __( 'Product URI', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'description',
		'label' => __( 'Description', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'excerpt',
		'label' => __( 'Excerpt', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_date',
		'label' => __( 'Product Published', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_modified',
		'label' => __( 'Product Modified', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'type',
		'label' => __( 'Type', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'visibility',
		'label' => __( 'Visibility', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'featured',
		'label' => __( 'Featured', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'virtual',
		'label' => __( 'Virtual', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'downloadable',
		'label' => __( 'Downloadable', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'price',
		'label' => __( 'Price', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'sale_price',
		'label' => __( 'Sale Price', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'net_price',
		'label' => __( 'Net Price', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'sale_price_dates_from',
		'label' => __( 'Sale Price Dates From', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'sale_price_dates_to',
		'label' => __( 'Sale Price Dates To', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'weight',
		'label' => __( 'Weight', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'weight_unit',
		'label' => __( 'Weight Unit', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'height',
		'label' => __( 'Height', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'height_unit',
		'label' => __( 'Height Unit', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'width',
		'label' => __( 'Width', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'width_unit',
		'label' => __( 'Width Unit', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'length',
		'label' => __( 'Length', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'length_unit',
		'label' => __( 'Length Unit', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category',
		'label' => __( 'Category', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category_level_1',
		'label' => __( 'Category: Level 1', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'category_level_2',
		'label' => __( 'Category: Level 2', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'category_level_3',
		'label' => __( 'Category: Level 3', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'tag',
		'label' => __( 'Tag', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'image',
		'label' => __( 'Featured Image', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'image_thumbnail',
		'label' => __( 'Featured Image Thumbnail', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'image_embed',
		'label' => __( 'Featured Image (Embed)', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'image_title',
		'label' => __( 'Featured Image Title', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'image_caption',
		'label' => __( 'Featured Image Caption', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'image_alt',
		'label' => __( 'Featured Image Alternative Text', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'image_description',
		'label' => __( 'Featured Image Description', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'product_gallery',
		'label' => __( 'Product Gallery', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_gallery_thumbnail',
		'label' => __( 'Product Gallery Thumbnail', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'product_gallery_embed',
		'label' => __( 'Product Gallery (Embed)', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'tax_status',
		'label' => __( 'Tax Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'tax_class',
		'label' => __( 'Tax Class', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_class',
		'label' => __( 'Shipping Class', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'download_file_name',
		'label' => __( 'Download File Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'download_file_path',
		'label' => __( 'Download File URL Path', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'download_limit',
		'label' => __( 'Download Limit', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'download_expiry',
		'label' => __( 'Download Expiry', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'download_type',
		'label' => __( 'Download Type', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'manage_stock',
		'label' => __( 'Manage Stock', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'quantity',
		'label' => __( 'Quantity', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'stock_status',
		'label' => __( 'Stock Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'allow_backorders',
		'label' => __( 'Allow Backorders', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'sold_individually',
		'label' => __( 'Sold Individually', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'total_sales',
		'label' => __( 'Total Sales', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'grouped_products',
		'label' => __( 'Grouped Products', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'upsell_ids',
		'label' => __( 'Up-Sells', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'crosssell_ids',
		'label' => __( 'Cross-Sells', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'external_url',
		'label' => __( 'External URL', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'button_text',
		'label' => __( 'Button Text', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_note',
		'label' => __( 'Purchase Note', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_status',
		'label' => __( 'Product Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'enable_reviews',
		'label' => __( 'Enable Reviews', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'review_count',
		'label' => __( 'Review Count', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'rating_count',
		'label' => __( 'Rating Count', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'average_rating',
		'label' => __( 'Average Rating', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'menu_order',
		'label' => __( 'Sort Order', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_author',
		'label' => __( 'Post Author', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_name',
		'label' => __( 'Username', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_role',
		'label' => __( 'User Role', 'woocommerce-exporter' )
	);

    /*
    $fields[] = array(
    'name' => '',
    'label' => __( '', 'woocommerce-exporter' )
    );
*/

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
            break;

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
            // Check if we are using PHP 5.3 and above
            if ( version_compare( phpversion(), '5.3' ) >= 0 ) {
                usort( $fields, woo_ce_sort_fields( 'order' ) );
            }
            return $fields;
            break;
    }
}

// Check if we should override field labels from the Field Editor.
function woo_ce_override_product_field_labels( $fields = array() ) {

    global $export;

    $export_type = 'product';

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
        if ( $export_fields == 'template' ) {
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
                $fields[ $key ]['label'] = $labels[ $field['name'] ];
            }
        }
    }
    return $fields;
}
add_filter( 'woo_ce_product_fields', 'woo_ce_override_product_field_labels', 11 );

// Returns the export column header label based on an export column slug.
function woo_ce_get_product_field( $name = null, $format = 'name' ) {

    global $export;

    $output = '';
    if ( $name ) {
        $fields = woo_ce_get_product_fields();
        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_product_field() > woo_ce_get_product_fields(): ' . ( time() - $export->start_time ) ) );
        }
        $size = count( $fields );
        for ( $i = 0; $i < $size; $i++ ) {
            if ( $fields[ $i ]['name'] == $name ) {
                switch ( $format ) {

                    case 'name':
                        $output = $fields[ $i ]['label'];

                        // Allow Plugin/Theme authors to easily override export field labels.
                        $output = apply_filters( 'woo_ce_get_product_field_label', $output );
                        break;

                    case 'full':
                        $output = $fields[ $i ];
                        break;
                }
                $i = $size;
            }
        }
    }
    return $output;
}

/**
 * Checks whether a Product query is exporting Variations and nothing else.
 *
 * Variation rows hold no product_cat or product_tag terms of their own, so a
 * term filter on such a query is resolved from the parent Product by the
 * post-query filter in woo_ce_get_products() rather than by the tax_query, and
 * the tax_query clause is skipped entirely.
 *
 * @since 2.8.2
 *
 * @param array $args WP_Query arguments built by woo_ce_get_products().
 * @return bool True when the query asks for Variations only.
 */
function woo_ce_is_variation_only_export( $args = array() ) {

    $post_type = ( isset( $args['post_type'] ) ? (array) $args['post_type'] : array() );

    return array( 'product_variation' ) === array_values( $post_type );
}

/**
 * Sorts a Product export query by SKU.
 *
 * Applies to a query carrying the 'wse_sku_order' query var whose orderby is
 * still the 'ID' set alongside it, so an orderby a woo_ce_get_products_args
 * consumer sets in its place is not overridden. The SKU is joined with its key
 * in the join condition, so Products and Variations with no SKU of their own
 * are kept and sort as an empty SKU. Sorting on 'meta_key' instead
 * drops them, and a meta_query clause joins every meta row of each of them.
 *
 * @since 2.8.2
 *
 * @param array    $clauses The query's SQL clauses.
 * @param WP_Query $query   The query being run.
 * @return array The clauses, sorted by SKU when the query asks for it.
 */
function woo_ce_products_sku_sort_clauses( $clauses, $query ) {

    global $wpdb;

    // A woo_ce_get_products_args consumer that replaced the 'ID' orderby set
    // with the 'wse_sku_order' query var keeps its own sort.
    $sku_order = $query->get( 'wse_sku_order' );
    if ( ! in_array( $sku_order, array( 'ASC', 'DESC' ), true ) || 'ID' !== $query->get( 'orderby' ) ) {
        return $clauses;
    }

    $clauses['join']   .= " LEFT JOIN {$wpdb->postmeta} AS wse_sku ON ( wse_sku.post_id = {$wpdb->posts}.ID AND wse_sku.meta_key = '_sku' )";
    $clauses['orderby'] = "wse_sku.meta_value {$sku_order}, {$wpdb->posts}.ID ASC";
    // A Product with more than one SKU row is still returned once.
    if ( empty( $clauses['groupby'] ) ) {
        $clauses['groupby'] = "{$wpdb->posts}.ID";
    }

    return $clauses;
}

/**
 * Returns the Term IDs the Parent Product of a Variation carries, memoized for the request.
 *
 * A Variation-only export resolves its term filter against the Parent, and every
 * Variation of a Variable Product shares one Parent, so each sibling row would
 * otherwise repeat the identical uncached wp_get_object_terms() query - up to
 * twice per row, once per taxonomy, on every batch.
 *
 * @since 2.8.2
 *
 * @param int    $parent_id     Parent Product ID.
 * @param string $term_taxonomy Taxonomy to read, product_cat or product_tag.
 * @return array Term IDs assigned to the Parent Product.
 */
function woo_ce_get_variation_parent_term_ids( $parent_id = 0, $term_taxonomy = 'product_cat' ) {

    static $parent_terms = array();

    $parent_id = absint( $parent_id );

    if ( ! isset( $parent_terms[ $parent_id ][ $term_taxonomy ] ) ) {
        $parent_terms[ $parent_id ][ $term_taxonomy ] = (
            'product_cat' === $term_taxonomy
            ? (array) woo_ce_get_product_assoc_categories( $parent_id, false, $term_taxonomy, 'ids' )
            : (array) woo_ce_get_product_assoc_tags( $parent_id, $term_taxonomy, 'ids' )
        );
    }

    return $parent_terms[ $parent_id ][ $term_taxonomy ];
}

/**
 * Returns the given Product Category Term IDs with every descendant Term added, memoized for the request.
 *
 * The Products path never needs this: its tax_query clause names the 'id' field,
 * and WP_Query applies include_children to such a clause, so excluding a Parent
 * Category also drops the Products in its children. A Variation-only export has
 * no clause to lean on - it matches the Parent Product's directly assigned
 * Categories after the query - so the selected Categories are expanded here and
 * both paths honour the same hierarchy. Product Tags are flat and need
 * no expansion.
 *
 * @since 2.8.2
 *
 * @param array  $term_ids      Selected Product Category Term IDs.
 * @param string $term_taxonomy Category taxonomy.
 * @return array The Term IDs plus every descendant Term ID.
 */
function woo_ce_get_product_category_ids_with_children( $term_ids = array(), $term_taxonomy = 'product_cat' ) {

    static $expanded = array();

    $term_ids  = array_map( 'absint', (array) $term_ids );
    $cache_key = $term_taxonomy . ':' . implode( ',', $term_ids );

    if ( ! isset( $expanded[ $cache_key ] ) ) {
        $descendants = array();
        foreach ( $term_ids as $term_id ) {
            $children = get_term_children( $term_id, $term_taxonomy );
            if ( ! is_wp_error( $children ) ) {
                $descendants = array_merge( $descendants, array_map( 'absint', $children ) );
            }
        }
        $expanded[ $cache_key ] = array_values( array_unique( array_merge( $term_ids, $descendants ) ) );
    }

    return $expanded[ $cache_key ];
}

/**
 * Returns a list of WooCommerce Products.
 *
 * Builds a WP_Query from the Product export filters and returns the matching
 * Product IDs. When Variation is selected alongside other term-based Product
 * Types (Simple, Variable, Grouped, External), the Variations of the matched
 * Variable and Variable Subscription Parents are fetched in one batched query,
 * with the main query's post statuses and sorting, and placed straight after
 * their Parent. The filters on a Product's own meta and dates then apply to each
 * Product and each Variation on its own. Downloadable / Virtual narrow the
 * selected Products and Variations, or the whole catalog when selected alone.
 *
 * @since 2.8.2 Variations combined with other Product Types are fetched in one query for every matched Variable Parent and filtered on their own meta and dates, Downloadable / Virtual narrow Variations and apply when selected alone, and a sort by SKU keeps Products without an SKU.
 *
 * @param array        $args   Product export arguments, including filters, Limit Volume and Volume Offset.
 * @param object|array $export The export object, or an empty array to use the $export global.
 *
 * @return array The matching Product IDs.
 */
function woo_ce_get_products( $args = array(), $export = array() ) {
    if ( empty( $export ) ) {
        global $export;
    }

    $limit_volume = -1;
    $offset       = 0;

    $product_ids              = false;
    $product_category         = false;
    $product_category_exclude = false;
    $product_tag              = false;
    $product_tag_exclude      = false;
    $product_brand            = false;
    $product_vendor           = false;
    $product_status           = false;
    $product_type             = false;
    $product_sku              = false;
    $product_sku_exclude      = false;
    $product_user_role        = false;
    $product_stock            = false;
    $product_quantity         = false;
    $product_featured         = false;
    $product_featured_image   = false;
    $product_gallery          = false;
    $product_shipping_class   = false;
    $product_language         = false;
    $orderby                  = 'ID';
    $order                    = 'ASC';
    if ( $args ) {
        $product_ids  = ( ! empty( $args['product_ids'] ) ? $args['product_ids'] : false );
        $limit_volume = ( isset( $args['limit_volume'] ) ? $args['limit_volume'] : false );
        $offset       = ( isset( $args['offset'] ) ? $args['offset'] : false );
        if ( ! empty( $args['product_category'] ) ) {
            $product_category         = $args['product_category'];
            $product_category_exclude = ! empty( $args['product_category_exclude'] );
        }
        if ( ! empty( $args['product_tag'] ) ) {
            $product_tag         = $args['product_tag'];
            $product_tag_exclude = ! empty( $args['product_tag_exclude'] );
        }
        $product_brand = ( ! empty( $args['product_brand'] ) ? $args['product_brand'] : false );
        if ( ! empty( $args['product_vendor'] ) ) {
            $product_vendor = $args['product_vendor'];
        }
        if ( ! empty( $args['product_status'] ) ) {
            $product_status = $args['product_status'];
        }
        if ( ! empty( $args['product_type'] ) ) {
            $product_type = $args['product_type'];
        }
        if ( ! empty( $args['product_sku'] ) ) {
            $product_sku         = $args['product_sku'];
            $product_sku_exclude = ! empty( $args['product_sku_exclude'] );
        }
        // Add support for filtering by GTIN
        $product_global_unique_id = ( ! empty( $args['product_global_unique_id'] ) ? $args['product_global_unique_id'] : false );
        if ( ! empty( $args['product_user_role'] ) ) {
            $product_user_role = $args['product_user_role'];
        }
        if ( ! empty( $args['product_stock'] ) ) {
            $product_stock = $args['product_stock'];
        }
        if ( ! empty( $args['product_quantity'] ) ) {
            $product_quantity = $args['product_quantity'];
        }
        if ( ! empty( $args['product_featured'] ) ) {
            $product_featured = $args['product_featured'];
        }
        if ( ! empty( $args['product_featured_image'] ) ) {
            $product_featured_image = $args['product_featured_image'];
        }
        if ( ! empty( $args['product_gallery'] ) ) {
            $product_gallery = $args['product_gallery'];
        }
        if ( ! empty( $args['product_shipping_class'] ) ) {
            $product_shipping_class = $args['product_shipping_class'];
        }
        if ( ! empty( $args['product_language'] ) ) {
            $product_language = $args['product_language'];
        }
        if ( isset( $args['product_orderby'] ) ) {
            $orderby = $args['product_orderby'];
        }
        if ( isset( $args['product_order'] ) ) {
            $order = $args['product_order'];
        }
        // Modified date.
        $product_dates = ( isset( $args['product_dates'] ) ? $args['product_dates'] : false );
        switch ( $product_dates ) {

            case 'tomorrow':
                $product_dates_from = woo_ce_get_order_date_filter( 'tomorrow', 'from' );
                $product_dates_to   = woo_ce_get_order_date_filter( 'tomorrow', 'to' );
                break;

            case 'today':
                $product_dates_from = woo_ce_get_order_date_filter( 'today', 'from' );
                $product_dates_to   = woo_ce_get_order_date_filter( 'today', 'to' );
                break;

            case 'yesterday':
                $product_dates_from = woo_ce_get_order_date_filter( 'yesterday', 'from' );
                $product_dates_to   = woo_ce_get_order_date_filter( 'yesterday', 'to' );
                break;

            case 'current_week':
                $product_dates_from = woo_ce_get_order_date_filter( 'current_week', 'from' );
                $product_dates_to   = woo_ce_get_order_date_filter( 'current_week', 'to' );
                break;

            case 'last_week':
                $product_dates_from = woo_ce_get_order_date_filter( 'last_week', 'from' );
                $product_dates_to   = woo_ce_get_order_date_filter( 'last_week', 'to' );
                break;

            case 'current_month':
                $product_dates_from = woo_ce_get_order_date_filter( 'current_month', 'from' );
                $product_dates_to   = woo_ce_get_order_date_filter( 'current_month', 'to' );
                break;

            case 'last_month':
                $product_dates_from = woo_ce_get_order_date_filter( 'last_month', 'from' );
                $product_dates_to   = woo_ce_get_order_date_filter( 'last_month', 'to' );
                break;

            case 'current_year':
                $product_dates_from = woo_ce_get_order_date_filter( 'current_year', 'from' );
                $product_dates_to   = woo_ce_get_order_date_filter( 'current_year', 'to' );
                break;

            case 'last_year':
                $product_dates_from = woo_ce_get_order_date_filter( 'last_year', 'from' );
                $product_dates_to   = woo_ce_get_order_date_filter( 'last_year', 'to' );
                break;

            case 'manual':
                $date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );

                // Populate empty from or to dates.
                if ( ! empty( $args['product_dates_from'] ) ) {
                    $product_dates_from = woo_ce_format_order_date( $args['product_dates_from'] );
                } else {
                    $product_dates_from = woo_ce_get_product_first_date( $date_format );
                }
                if ( ! empty( $args['product_dates_to'] ) ) {
                    $product_dates_to = woo_ce_format_order_date( $args['product_dates_to'] );
                } else {
                    $product_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n' ), date( 'd' ) ) );
                }

                // WP_Query only accepts D-m-Y so we must format dates to that.
                if ( $date_format !== 'd/m/Y' ) {
                    $date_format = woo_ce_format_order_date( $date_format );
                    if ( function_exists( 'date_create_from_format' ) && function_exists( 'date_format' ) ) {
                        if ( $product_dates_from = date_create_from_format( $date_format, $product_dates_from ) ) {
                            $product_dates_from = date_format( $product_dates_from, 'd-m-Y' );
                        }
                        if ( $product_dates_to = date_create_from_format( $date_format, $product_dates_to ) ) {
                            $product_dates_to = date_format( $product_dates_to, 'd-m-Y' );
                        }
                    }
                }
                break;

            case 'variable':
                $modified_filter_date_variable        = $args['modified_dates_filter_variable'];
                $modified_filter_date_variable_length = $args['modified_dates_filter_variable_length'];
                if ( $modified_filter_date_variable !== false && $modified_filter_date_variable_length !== false ) {
                    $timestamp          = strtotime( sprintf( '-%d %s', $modified_filter_date_variable, $modified_filter_date_variable_length ), current_time( 'timestamp', 0 ) );
                    $product_dates_from = date( 'd-m-Y-H-i-s', mktime( date( 'H', $timestamp ), date( 'i', $timestamp ), date( 's', $timestamp ), date( 'n', $timestamp ), date( 'd', $timestamp ), date( 'Y', $timestamp ) ) );
                    $product_dates_to   = woo_ce_get_order_date_filter( 'today', 'to' );
                    unset( $modified_filter_date_variable, $modified_filter_date_variable_length, $timestamp );
                }
                break;

            case 'specific':
                // SPA quick export submits ISO (Y-m-d) bounds; downstream explode expects d-m-Y.
                list( $product_dates_from, $product_dates_to ) = \VisserLabs\WSE\Helpers\Export::parse_iso_range(
                    $args['product_dates_from'] ?? '',
                    $args['product_dates_to'] ?? '',
                    'd-m-Y'
                );
                break;

            default:
                $product_dates_from = false;
                $product_dates_to   = false;
                break;
        }
        if (
            ! empty( $product_dates_from ) &&
            ! empty( $product_dates_to )
        ) {
            $product_dates_from = explode( '-', $product_dates_from );
            // Check that a valid date was provided.
            if ( isset( $product_dates_from[0] ) && isset( $product_dates_from[1] ) && isset( $product_dates_from[2] ) ) {
                $product_dates_from = array(
                    'year'   => $product_dates_from[2],
                    'month'  => $product_dates_from[1],
                    'day'    => $product_dates_from[0],
                    'hour'   => 0,
                    'minute' => 0,
                    'second' => 0,
                );
            } else {
                $product_dates_from = false;
            }
            $product_dates_to = explode( '-', $product_dates_to );
            // Check that a valid date was provided.
            if ( isset( $product_dates_to[0] ) && isset( $product_dates_to[1] ) && isset( $product_dates_to[2] ) ) {
                $product_dates_to = array(
                    'year'   => $product_dates_to[2],
                    'month'  => $product_dates_to[1],
                    'day'    => $product_dates_to[0],
                    'hour'   => 23,
                    'minute' => 59,
                    'second' => 59,
                );
            } else {
                $product_dates_to = false;
            }
        }
        // Published date.
        $product_published_dates = ( isset( $args['product_published_dates'] ) ? $args['product_published_dates'] : false );
        switch ( $product_published_dates ) {

            case 'tomorrow':
                $product_published_dates_from = woo_ce_get_order_date_filter( 'tomorrow', 'from' );
                $product_published_dates_to   = woo_ce_get_order_date_filter( 'tomorrow', 'to' );
                break;

            case 'today':
                $product_published_dates_from = woo_ce_get_order_date_filter( 'today', 'from' );
                $product_published_dates_to   = woo_ce_get_order_date_filter( 'today', 'to' );
                break;

            case 'yesterday':
                $product_published_dates_from = woo_ce_get_order_date_filter( 'yesterday', 'from' );
                $product_published_dates_to   = woo_ce_get_order_date_filter( 'yesterday', 'to' );
                break;

            case 'current_week':
                $product_published_dates_from = woo_ce_get_order_date_filter( 'current_week', 'from' );
                $product_published_dates_to   = woo_ce_get_order_date_filter( 'current_week', 'to' );
                break;

            case 'last_week':
                $product_published_dates_from = woo_ce_get_order_date_filter( 'last_week', 'from' );
                $product_published_dates_to   = woo_ce_get_order_date_filter( 'last_week', 'to' );
                break;

            case 'current_month':
                $product_published_dates_from = woo_ce_get_order_date_filter( 'current_month', 'from' );
                $product_published_dates_to   = woo_ce_get_order_date_filter( 'current_month', 'to' );
                break;

            case 'last_month':
                $product_published_dates_from = woo_ce_get_order_date_filter( 'last_month', 'from' );
                $product_published_dates_to   = woo_ce_get_order_date_filter( 'last_month', 'to' );
                break;

            case 'current_year':
                $product_published_dates_from = woo_ce_get_order_date_filter( 'current_year', 'from' );
                $product_published_dates_to   = woo_ce_get_order_date_filter( 'current_year', 'to' );
                break;

            case 'last_year':
                $product_published_dates_from = woo_ce_get_order_date_filter( 'last_year', 'from' );
                $product_published_dates_to   = woo_ce_get_order_date_filter( 'last_year', 'to' );
                break;

            case 'manual':
                $date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );

                // Populate empty from or to dates.
                if ( ! empty( $args['product_published_dates_from'] ) ) {
                    $product_published_dates_from = woo_ce_format_order_date( $args['product_published_dates_from'] );
                } else {
                    $product_published_dates_from = woo_ce_get_product_first_date( $date_format );
                }
                if ( ! empty( $args['product_published_dates_to'] ) ) {
                    $product_published_dates_to = woo_ce_format_order_date( $args['product_published_dates_to'] );
                } else {
                    $product_published_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n' ), date( 'd' ) ) );
                }

                // WP_Query only accepts D-m-Y so we must format dates to that.
                if ( $date_format !== 'd/m/Y' ) {
                    $date_format = woo_ce_format_order_date( $date_format );
                    if (
                        function_exists( 'date_create_from_format' ) &&
                        function_exists( 'date_format' )
                    ) {
                        if ( $product_published_dates_from = date_create_from_format( $date_format, $product_published_dates_from ) ) {
                            $product_published_dates_from = date_format( $product_published_dates_from, 'd-m-Y' );
                        }
                        if ( $product_published_dates_to = date_create_from_format( $date_format, $product_published_dates_to ) ) {
                            $product_published_dates_to = date_format( $product_published_dates_to, 'd-m-Y' );
                        }
                    }
                }
                break;

            case 'variable':
                $published_filter_date_variable        = $args['published_dates_filter_variable'];
                $published_filter_date_variable_length = $args['published_dates_filter_variable_length'];
                if ( $published_filter_date_variable !== false && $published_filter_date_variable_length !== false ) {
                    $published_timestamp          = strtotime( sprintf( '-%d %s', $published_filter_date_variable, $published_filter_date_variable_length ), current_time( 'timestamp', 0 ) );
                    $product_published_dates_from = date( 'd-m-Y-H-i-s', mktime( date( 'H', $published_timestamp ), date( 'i', $published_timestamp ), date( 's', $published_timestamp ), date( 'n', $published_timestamp ), date( 'd', $published_timestamp ), date( 'Y', $published_timestamp ) ) );
                    $product_published_dates_to   = woo_ce_get_order_date_filter( 'today', 'to' );
                    unset( $published_filter_date_variable, $published_filter_date_variable_length, $timestamp );
                }
                break;

            case 'specific':
                // SPA quick export submits ISO (Y-m-d) bounds; downstream explode expects d-m-Y.
                list( $product_published_dates_from, $product_published_dates_to ) = \VisserLabs\WSE\Helpers\Export::parse_iso_range(
                    $args['product_published_dates_from'] ?? '',
                    $args['product_published_dates_to'] ?? '',
                    'd-m-Y'
                );
                break;

            default:
                $product_published_dates_from = false;
                $product_published_dates_to   = false;
                break;
        }
        if (
            ! empty( $product_published_dates_from ) &&
            ! empty( $product_published_dates_to )
        ) {
            $product_published_dates_from = explode( '-', $product_published_dates_from );
            // Check that a valid date was provided.
            if ( isset( $product_published_dates_from[0] ) && isset( $product_published_dates_from[1] ) && isset( $product_published_dates_from[2] ) ) {
                $product_published_dates_from = array(
                    'year'   => $product_published_dates_from[2],
                    'month'  => $product_published_dates_from[1],
                    'day'    => $product_published_dates_from[0],
                    'hour'   => 0,
                    'minute' => 0,
                    'second' => 0,
                );
            } else {
                $product_published_dates_from = false;
            }
            $product_published_dates_to = explode( '-', $product_published_dates_to );
            // Check that a valid date was provided.
            if ( isset( $product_published_dates_to[0] ) && isset( $product_published_dates_to[1] ) && isset( $product_published_dates_to[2] ) ) {
                $product_published_dates_to = array(
                    'year'   => $product_published_dates_to[2],
                    'month'  => $product_published_dates_to[1],
                    'day'    => $product_published_dates_to[0],
                    'hour'   => 23,
                    'minute' => 59,
                    'second' => 59,
                );
            } else {
                $product_published_dates_to = false;
            }
        }
    }

    $post_type   = apply_filters( 'woo_ce_get_products_post_type', array( 'product' ) );
    $post_status = apply_filters( 'woo_ce_get_products_status', array( 'publish', 'pending', 'draft', 'future' ) );

    $args = array(
        'post_type'        => $post_type,
        'orderby'          => $orderby,
        'order'            => $order,
        'offset'           => $offset,
        'posts_per_page'   => $limit_volume,
        'post_status'      => woo_ce_post_statuses( $post_status, true ),
        'fields'           => 'ids',
        'suppress_filters' => false,
    );

    // The Product Types whose Products are Variable Parents, and whether Variation
    // is combined with other Product Types. See the Product Type filter below.
    $variable_parent_types      = array( 'variable', 'variable-subscription' );
    $variation_with_other_types = false;

    // Filter Products by Product Type.
    if (
        is_array( $product_type ) &&
        ! empty( $product_type )
    ) {
        // Downloadable and Virtual are meta flags rather than product_type terms;
        // a Product carrying either selected flag matches.
        $type_flag_meta_query = array(
            'relation' => 'OR',
        );
        if ( in_array( 'downloadable', $product_type ) ) {
            $type_flag_meta_query[] = array(
                'key'     => '_downloadable',
                'value'   => 'yes',
                'compare' => 'EXISTS',
            );
        }
        if ( in_array( 'virtual', $product_type ) ) {
            $type_flag_meta_query[] = array(
                'key'   => '_virtual',
                'value' => 'yes',
            );
        }
        if ( 1 === count( $type_flag_meta_query ) ) {
            $type_flag_meta_query = array();
        }

        // Remove non-Term based Product Types before we tack on our tax_query.
        $term_product_type = $product_type;
        foreach ( $term_product_type as $key => $type ) {
            if ( in_array( $type, array( 'downloadable', 'virtual', 'variation' ) ) ) {
                unset( $term_product_type[ $key ] );
            }
        }

        // Variations are collected from their Variable Parents after the query,
        // so a Variation selected alongside other Product Types needs every kind
        // of Variable Parent matched too. The Parents of a type that was not
        // selected are removed again below.
        $variation_with_other_types = (
            ! empty( $term_product_type ) &&
            in_array( 'variation', $product_type, true )
        );
        if ( $variation_with_other_types ) {
            foreach ( $variable_parent_types as $variable_parent_type ) {
                if ( ! in_array( $variable_parent_type, $term_product_type, true ) ) {
                    $term_product_type[] = $variable_parent_type;
                }
            }
        }

        // Nested as one clause so the meta filters added further down narrow the
        // export instead of joining the Downloadable / Virtual OR.
        if ( ! empty( $type_flag_meta_query ) ) {
            $args['meta_query'] = array( $type_flag_meta_query );
        }

        if ( ! empty( $term_product_type ) ) {
            $term_taxonomy = 'product_type';
            // Check if it's an empty unkeyed array that has snuck through.
            if ( ( count( $term_product_type ) == 1 && isset( $term_product_type[0] ) && $term_product_type[0] == '' ) == false ) {
                $args['tax_query'][] = array(
                    array(
                        'taxonomy' => $term_taxonomy,
                        'field'    => 'slug',
                        'terms'    => $term_product_type,
                    ),
                );
            }
        } elseif ( in_array( 'variation', $product_type, true ) ) {
            // Without a term based Product Type, Variation means a Variation-only
            // export, narrowed by Downloadable / Virtual on the Variations' own meta
            // when either is selected.
            $args['post_type'] = array( 'product_variation' );
        }
        unset( $term_product_type, $type_flag_meta_query );
    }

    // Check if we are doing a Variation export.
    if ( ! in_array( 'product_variation', $args['post_type'] ) ) {
    }

    // Filter Products by Product Category.
    if ( $product_category ) {
        $term_taxonomy = 'product_cat';
        // Check if tax_query has been created.
        if ( ! isset( $args['tax_query'] ) ) {
            $args['tax_query'] = array();
        }
        // A Variation-only query gets no clause at all. Its post_type is
        // 'product_variation', and variation rows carry no product_cat or
        // product_tag terms - those sit on the parent - so the clause could only
        // ever match nothing. The post-query filter in the results loop below
        // resolves the parent's terms instead, and it is the sole authority for
        // that case.
        if ( ! woo_ce_is_variation_only_export( $args ) ) {
            // The 'Filter out' choice sets $product_category_exclude, so the
            // clause needs 'NOT IN'. Without an explicit operator WP_Query falls
            // back to 'IN' and the exclude choice was dropped right before the
            // query ran.
            $args['tax_query'][] = array(
                array(
                    'taxonomy' => $term_taxonomy,
                    'field'    => 'id',
                    'terms'    => $product_category,
                    'operator' => ( $product_category_exclude ? 'NOT IN' : 'IN' ),
                ),
            );
        }
    }

    // Filter Products by Product Tag.
    if ( $product_tag ) {
        $term_taxonomy = 'product_tag';
        // Check if tax_query has been created.
        if ( ! isset( $args['tax_query'] ) ) {
            $args['tax_query'] = array();
        }
        // See the Product Category clause above - 'Filter out' needs 'NOT IN', and
        // a Variation-only query is left to the post-query filter.
        if ( ! woo_ce_is_variation_only_export( $args ) ) {
            $args['tax_query'][] = array(
                array(
                    'taxonomy' => $term_taxonomy,
                    'field'    => 'id',
                    'terms'    => $product_tag,
                    'operator' => ( $product_tag_exclude ? 'NOT IN' : 'IN' ),
                ),
            );
        }
    }

    // WooCommerce Brands Addon - http://woothemes.com/woocommerce/
    if ( $product_brand ) {
        // Match the saved Brand term IDs against every registered brand taxonomy,
        // not just the one woo_ce_brand_term_taxonomy currently resolves to. Term
        // IDs are unique in wp_terms, so a term ID saved before Perfect WooCommerce
        // Brands detection changed the resolved taxonomy still matches here.
        $term_taxonomies = woo_ce_get_brand_term_taxonomies( true );
        if ( ! empty( $term_taxonomies ) ) {
            // Check if tax_query has been created.
            if ( ! isset( $args['tax_query'] ) ) {
                $args['tax_query'] = array();
            }
            $brand_tax_query = array( 'relation' => 'OR' );
            foreach ( $term_taxonomies as $term_taxonomy ) {
                $brand_tax_query[] = array(
                    'taxonomy' => $term_taxonomy,
                    'field'    => 'id',
                    'terms'    => $product_brand,
                );
            }
            $args['tax_query'][] = $brand_tax_query;
        }
    }

    // Product Vendors - http://www.woothemes.com/products/product-vendors/
    // YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
    if ( $product_vendor ) {
        $term_taxonomy = apply_filters( 'woo_ce_product_vendor_term_taxonomy', 'wcpv_product_vendors' );
        // Check if tax_query has been created.
        if ( ! isset( $args['tax_query'] ) ) {
            $args['tax_query'] = array();
        }
        $args['tax_query'][] = array(
            array(
                'taxonomy' => $term_taxonomy,
                'field'    => 'id',
                'terms'    => $product_vendor,
            ),
        );
    }

    // Filter Products by Shipping Class.
    if ( $product_shipping_class ) {
        $term_taxonomy = 'product_shipping_class';
        // Check if tax_query has been created.
        if ( ! isset( $args['tax_query'] ) ) {
            $args['tax_query'] = array();
        }
        $args['tax_query'][] = array(
            array(
                'taxonomy' => $term_taxonomy,
                'field'    => 'id',
                'terms'    => $product_shipping_class,
            ),
        );
    }

    // Filter Products by Language.
    if ( $product_language ) {

        global $sitepress;

        if ( method_exists( $sitepress, 'posts_where_filter' ) ) {
            remove_filter( 'posts_where', array( $sitepress, 'posts_where_filter' ), 10 );
        }
		add_filter( 'posts_where', 'woo_ce_wp_query_product_where_override_language' );
		add_filter( 'posts_where', 'woo_ce_wp_query_product_where_override_language' );

        add_filter( 'posts_where', 'woo_ce_wp_query_product_where_override_language' );

    }

    // Filter Products by Post Status.
    if ( $product_status ) {
        $args['post_status'] = woo_ce_post_statuses( $product_status, true );
    }

    // Filter Products by Featured.
    if ( $product_featured ) {
        if ( version_compare( woo_get_woo_version(), '3.0', '>=' ) ) {
            // Check if tax_query has been created.
            if ( ! isset( $args['tax_query'] ) ) {
                $args['tax_query'] = array();
            }
            $term_taxonomy = 'product_visibility';
            $term_operator = 'EXISTS';
            switch ( $product_featured ) {

                case 'yes':
                default:
                    $term_operator = 'IN';
                    break;

                case 'no':
                    $term_operator = 'NOT IN';
                    break;
            }
            $args['tax_query'][] = array(
                array(
                    'taxonomy' => $term_taxonomy,
                    'field'    => 'slug',
                    'terms'    => 'featured',
                    'operator' => $term_operator,
                ),
            );
        } else {
            $args['meta_query'][] = array(
                'key'   => '_featured',
                'value' => $product_featured,
            );
        }
    }

    // Filter Products by Featured Image.
    if ( $product_featured_image ) {
        $meta_key = '_thumbnail_id';
        switch ( $product_featured_image ) {

            case 'yes':
                $args['meta_query'][] = array(
                    array(
                        'key'     => $meta_key,
                        'compare' => 'EXISTS',
                    ),
                );
                break;

            case 'no':
                $args['meta_query'][] = array(
                    'relation' => 'OR',
                    array(
                        'key'     => $meta_key,
                        'compare' => 'NOT EXISTS',
                    ),
                    array(
                        'key'   => $meta_key,
                        'value' => false,
                    ),
                );
                break;
        }
    }

    // Filter Products by Product Gallery.
    if ( $product_gallery ) {
        $meta_key = '_product_image_gallery';
        switch ( $product_gallery ) {

            case 'yes':
                $args['meta_query'][] = array(
                    array(
                        'key'     => $meta_key,
                        'compare' => 'EXISTS',
                    ),
                );
                break;

            case 'no':
                $args['meta_query'][] = array(
                    'relation' => 'OR',
                    array(
                        'key'     => $meta_key,
                        'compare' => 'NOT EXISTS',
                    ),
                    array(
                        'key'   => $meta_key,
                        'value' => false,
                    ),
                );
                break;
        }
    }

    // Filter Products by Stock Quantity.
    if ( $product_quantity ) {
        // Separate the operator from the value.
        $quantity = str_replace( array( '!=', '=', '>', '>=', '<', '<=' ), '', $product_quantity );
        $operator = preg_replace( '/[0-9]+/', '', $product_quantity );
        // Default to equals.
        if ( empty( $operator ) ) {
            $operator = '=';
        }
        // A zero threshold is meaningful - "greater than 0" is how a store owner
        // asks for in-stock products and "exactly 0" for sold-out ones - but
        // empty( '0' ) is true, so ! empty() dropped the meta_query entirely and
        // returned an unfiltered export while the form reported a filter.
        // Only a genuinely absent value (operator picked, number left blank)
        // should skip the query. Note this admits any non-empty string, not just
        // digits: both the Quick Export number input and the classic UI submit
        // numeric values, and 'type' => 'NUMERIC' below casts anything else to 0.
        // Tighten this to is_numeric() if a non-form caller is ever supported.
        if ( '' !== $quantity ) {
            $args['meta_query'][] = array(
                'key'     => '_stock',
                'value'   => $quantity,
                'compare' => $operator,
                'type'    => 'NUMERIC',
            );
        }
        unset( $quantity, $operator );
    }

    // Filter Products by SKU.
    if ( $product_sku ) {
        if ( ! $product_sku_exclude ) {
            $args['post__in'] = array_map( 'absint', (array) $product_sku );
        } else {
            $args['post__not_in'] = array_map( 'absint', (array) $product_sku );
        }
    }

    // Filter Products by Post ID.
    if ( ! empty( $product_ids ) ) {
        $product_ids = explode( ',', $product_ids );
        $size        = count( $product_ids );
        if ( $size > 1 ) {
            $args['post__in'] = array_map( 'absint', (array) $product_ids );
        } else {
            $args['p'] = absint( $product_ids[0] );
        }
    }

    // Filter Products by Date.

    // Modified date.
    if (
        ! empty( $product_dates_from ) &&
        ! empty( $product_dates_to )
    ) {
        if ( ! isset( $args['date_query'] ) ) {
            $args['date_query'] = array();
        }
        $args['date_query'][] = array(
            array(
                'column'    => 'post_modified',
                'before'    => $product_dates_to,
                'after'     => $product_dates_from,
                'inclusive' => true,
            ),
        );
    }
    // Published date.
    if (
        ! empty( $product_published_dates_from ) &&
        ! empty( $product_published_dates_to )
    ) {
        if ( ! isset( $args['date_query'] ) ) {
            $args['date_query'] = array();
        }
        if ( apply_filters( 'woo_ce_date_filter_or_relation', false ) ) {
            $args['date_query']['relation'] = 'OR';
        }
        $args['date_query'][] = array(
            array(
                'column'    => 'post_date',
                'before'    => $product_published_dates_to,
                'after'     => $product_published_dates_from,
                'inclusive' => true,
            ),
        );
    }

    // Filter Product User Roles.
    if ( ! empty( $product_user_role ) ) {
        $user_ids = array();
        $size     = count( $export->args['product_user_role'] );
        for ( $i = 0; $i < $size; $i++ ) {
            $role_args = array(
                'role'   => $export->args['product_user_role'][ $i ],
                'fields' => 'ID',
            );
            $user_id   = get_users( $role_args );
            $user_ids  = array_merge( $user_ids, $user_id );
        }
        unset( $role_args );
        if ( ! empty( $user_ids ) ) {
            $args['author__in'] = $user_ids;
        }
    }

    // Sort Products by SKU, through woo_ce_products_sku_sort_clauses(). The
    // Variations of a combined export are sorted the same way below.
    if ( $orderby == 'sku' ) {
        $args['orderby']       = 'ID';
        $args['wse_sku_order'] = ( 'ASC' === strtoupper( (string) $order ) ? 'ASC' : 'DESC' );
    }

    $products = array();

    /**
     * Filters the WP_Query arguments of a Product export.
     *
     * @since 2.8.2 A sort by SKU arrives as an 'ID' orderby plus the 'wse_sku_order'
     *              query var, which woo_ce_products_sku_sort_clauses() turns into the
     *              SKU sort; it needs 'suppress_filters' left false. It used to
     *              arrive as a 'meta_value' orderby plus a '_sku' meta_key. An
     *              orderby other than 'ID' set here replaces the SKU sort. With Variation
     *              combined with other Product Types, the
     *              meta_query and date_query are applied after the main query rather
     *              than by it, so an orderby naming a meta_query clause does not apply.
     *
     * @param array $args The WP_Query arguments.
     */
    $args = apply_filters( 'woo_ce_get_products_args', $args );

    if ( WOO_CE_LOGGING && apply_filters( 'woo_ce_debug_get_products_args', false ) ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_products(), args: ' . print_r( $args, true ) ) );
    }

    // With Variation combined with other Product Types, the filters on a Product's
    // own meta and dates (Downloadable / Virtual, Stock quantity, Featured Image,
    // Gallery, custom meta, Published / Modified date) apply to each Product and
    // each Variation on its own. On the query they would also drop every Variable
    // Parent that fails them, and with it all of that Parent's Variations, so the
    // query matches without them: the other Products are checked against them in
    // one follow-up query, and the Variations in their own query below.
    $own_filters = array();
    if ( $variation_with_other_types ) {
        foreach ( array( 'meta_query', 'date_query' ) as $own_filter ) {
            if ( ! empty( $args[ $own_filter ] ) ) {
                $own_filters[ $own_filter ] = $args[ $own_filter ];
                unset( $args[ $own_filter ] );
            }
        }
    }

    // Limit Volume and Volume Offset are then applied after those filters too, to
    // the Products and Parents left, so the Limit is not taken up by Products the
    // filters remove.
    $window_after_query = false;
    if ( ! empty( $own_filters ) && (int) $args['posts_per_page'] > 0 ) {
        $window_after_query     = array( absint( $args['offset'] ), absint( $args['posts_per_page'] ) );
        $args['posts_per_page'] = -1;
        $args['offset']         = 0;
    }

    add_filter( 'posts_clauses', 'woo_ce_products_sku_sort_clauses', 10, 2 );

    $product_ids = new WP_Query( $args );
    if ( $product_ids->posts ) {
        // Check that a WP_Post didn't sneak through, then read every queried ID.
        $all_ids = array();
        foreach ( $product_ids->posts as $product_id ) {
            $all_ids[] = ( is_object( $product_id ) && isset( $product_id->ID ) ? absint( $product_id->ID ) : absint( $product_id ) );
        }
        $queried_map = array_flip( $all_ids );

        // Variable Parents skip the Stock filter, which is applied to their
        // Variations instead. That holds for Variable Subscription Parents only
        // while their Variations are being exported.
        $stock_exempt_types = ( is_array( $product_type ) && in_array( 'variation', $product_type, true ) ? $variable_parent_types : 'variable' );

        // The Variable Parent types that were not selected themselves, and are
        // only matched to reach their Variations.
        $unselected_parent_types = ( $variation_with_other_types ? array_values( array_diff( $variable_parent_types, $product_type ) ) : array() );

        // With the Limit Volume held back from the query (see above), no Limit
        // bounds the main query. So the Products are checked in chunks, and the
        // checks stop once the chunks hold enough Products and Parents to fill
        // the window. Without it, all the Products are one chunk.
        $window_end   = ( false !== $window_after_query ? $window_after_query[0] + $window_after_query[1] : 0 );
        $id_chunks    = ( $window_end > 0 ? array_chunk( $all_ids, max( 2 * $window_end, 200 ) ) : array( $all_ids ) );
        $all_products = array();
        $row_anchors  = array();
        $anchors      = array();
        foreach ( $id_chunks as $chunk_ids ) {
            // The Products that pass the held-back own meta and date filters: the main
            // query run again with them, for this chunk only, without the Limit or
            // the SKU sort.
            $passing_ids = false;
            if ( ! empty( $own_filters ) ) {
                // Load this chunk's Products, their Product Types and, for the Stock
                // filter, their meta in one query each, not one per Product in the loop.
                _prime_post_caches( $chunk_ids, false, (bool) $product_stock );
                update_object_term_cache( $chunk_ids, 'product' );
                $passing_args = array_merge(
                    $args,
                    $own_filters,
                    array(
                        'posts_per_page' => -1,
                        'offset'         => 0,
                        'orderby'        => 'none',
                        'fields'         => 'ids',
                        'no_found_rows'  => true,
                    )
                );
                unset( $passing_args['wse_sku_order'] );
                // Check only this chunk when there is more than one.
                if ( $window_end > 0 ) {
                    $passing_args['post__in'] = $chunk_ids;
                }
                $passing_query = new WP_Query( $passing_args );
                $passing_ids   = array_flip( $passing_query->posts );
                unset( $passing_args, $passing_query );
            }

            $products            = array();
            $queried_ids         = array();
            $variable_parent_ids = array();
            foreach ( $chunk_ids as $product_id ) {

                // Check that a WP_Post didn't sneak through...
                if ( is_object( $product_id ) ) {
                    $product_id = ( isset( $product_id->ID ) ? absint( $product_id->ID ) : $product_id );
                }
                $queried_ids[] = $product_id;

                // Get Product details.
                $product = get_post( $product_id );

                // Filter out Variations that don't have a Parent Product that exists.
                if (
                    isset( $product->post_type ) &&
                    $product->post_type == 'product_variation'
                ) {

                    // Filter out Variations that don't have a Parent Product that exists.
                    if ( $product->post_parent ) {
                        // Check if Parent exists.
                        if ( get_post( $product->post_parent ) == false ) {
                            if ( WOO_CE_LOGGING ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'Filter out Variation #%d as its Parent Product does not exist', $product_id ) ) );
                            }
                            unset( $product_id, $product );
                            continue;
                        }
                    }

                    // Allow filtering Variations by parent Variable filters.
                    if (
                        is_array( $product_type ) &&
                        ! empty( $product_type )
                    ) {
                        // Check if we are just exporting Variations.
                        if ( woo_ce_is_variation_only_export( $args ) ) {

                            // Filter Variations by Product Category.
                            //
                            // The Parent carries the terms, so this is where a
                            // Variation-only export applies them - the tax_query
                            // clause is skipped for that case. Dropping the Variation
                            // when the Parent matches is what 'Filter out' means, so
                            // the test is against the exclude flag rather than a bare
                            // "no match". The selected Categories are expanded to
                            // their descendants first, because the clause this path
                            // replaces carried include_children.
                            if ( $product_category ) {
                                $term_taxonomy = 'product_cat';
                                $matched       = ! empty( array_intersect( woo_ce_get_variation_parent_term_ids( $product->post_parent, $term_taxonomy ), woo_ce_get_product_category_ids_with_children( $product_category, $term_taxonomy ) ) );
                                if ( $matched === (bool) $product_category_exclude ) {
                                    unset( $product_id, $product, $matched );
                                    continue;
                                }
                                unset( $matched );
                            }

                            // Filter Variations by Product Tag.
                            if ( $product_tag ) {
                                $term_taxonomy = 'product_tag';
                                $matched       = ! empty( array_intersect( woo_ce_get_variation_parent_term_ids( $product->post_parent, $term_taxonomy ), $product_tag ) );
                                if ( $matched === (bool) $product_tag_exclude ) {
                                    unset( $product_id, $product, $matched );
                                    continue;
                                }
                                unset( $matched );
                            }
                        }
                    }
                }

                // Filter out Products based on the Stock Status and Quantity.
                $term_taxonomy = 'product_type';
                if (
                    $product_stock &&
                    has_term( $stock_exempt_types, $term_taxonomy, $product_id ) !== true
                ) {
                    $manage_stock = get_post_meta( $product_id, '_manage_stock', true );
                    $stock_status = get_post_meta( $product_id, '_stock_status', true );
                    $quantity     = get_post_meta( $product_id, '_stock', true );
                    $quantity     = ( function_exists( 'wc_stock_amount' ) ? wc_stock_amount( $quantity ) : absint( $quantity ) );
                    switch ( $product_stock ) {

                        case 'outofstock':
                            if (
                                ( $manage_stock == 'yes' && $quantity > 0 ) ||
                                $stock_status !== 'outofstock'
                            ) {
                                unset( $product_id, $product );
                                continue ( 2 );
                            }
                            break;

                        case 'instock':
                            if (
                                ( $manage_stock == 'yes' && $quantity == 0 ) ||
                                $stock_status !== 'instock'
                            ) {
                                unset( $product_id, $product );
                                continue ( 2 );
                            }
                            break;
                    }
                    unset( $stock_status, $quantity );
                }

                // Filter Products by Language.
                if ( $product_language ) {
                    // Check for corrupt Products.
                    if ( $product == false ) {
                        unset( $product_id, $product );
                        continue;
                    } elseif ( ! in_array( $product->post_type, array( 'product', 'product_variation' ) ) ) {
                        unset( $product_id, $product );
                        continue;
                    }
                }

                // A Variable Parent of a type that was not selected is matched only to
                // reach its Variations, so it is collected below but not exported. The
                // other Products are exported when they pass the own meta and date
                // filters held back from the query, if any. A Parent that fails them is
                // still collected, so its Variations are checked on their own terms.
                if (
                    isset( $product_id ) &&
                    ! (
                        ! empty( $unselected_parent_types ) &&
                        'product' === $product->post_type &&
                        has_term( $unselected_parent_types, 'product_type', $product_id )
                    ) &&
                    ( false === $passing_ids || isset( $passing_ids[ $product_id ] ) )
                ) {
                    $products[] = $product_id;
                }

                // Collect the Variable Parents, Variable Subscriptions included, whose
                // Variations are included after the loop.
                if (
                    ( isset( $args['tax_query'] ) || woo_ce_detect_wpml() ) &&
                    isset( $product_id )
                ) {
                    $term_taxonomy = 'product_type';
                    if (
                        has_term( $variable_parent_types, $term_taxonomy, $product_id ) &&
                        ( $product_type !== false && in_array( 'variation', $product_type ) )
                    ) {
                        $variable_parent_ids[] = $product_id;
                    }
                }
            }
            // Include the Variations of every collected Variable Parent with a single
            // query rather than one query per Parent, then place each Parent's
            // Variations straight after it. Limit Volume and Volume Offset pick the
            // Parents, in the main query or, with the own meta and date filters held
            // back from it, further down, so every Variation of those Parents is
            // included instead of that window being applied again to the Variations of
            // each Parent.
            //
            // The Variations take the main query's post statuses and sorting, SKU sort
            // included, and are narrowed by the own meta and date filters held back
            // from it, so they match what a Variation-only export returns. Only the
            // sort itself carries over: a meta sort added through the
            // woo_ce_get_products_args filter would need its meta_key, whose join drops
            // every Variation without that key, so such a sort is not applied here.
            if ( ! empty( $variable_parent_ids ) ) {
                $variable_args = array(
                    'post_type'       => 'product_variation',
                    'orderby'         => $args['orderby'],
                    'order'           => $args['order'],
                    'posts_per_page'  => -1,
                    'post_parent__in' => $variable_parent_ids,
                    'post_status'     => $args['post_status'],
                    'fields'          => 'id=>parent',
                    'no_found_rows'   => true,
                );
                if ( ! empty( $own_filters['meta_query'] ) ) {
                    $variable_args['meta_query'] = array( $own_filters['meta_query'] );
                }
                if ( ! empty( $own_filters['date_query'] ) ) {
                    $variable_args['date_query'] = $own_filters['date_query'];
                }
                if ( isset( $args['wse_sku_order'] ) ) {
                    $variable_args['wse_sku_order'] = $args['wse_sku_order'];
                }
                $variables    = array();
                $variable_ids = new WP_Query( $variable_args );
                // The Stock filter reads each Variation's meta, so load it in one query
                // rather than one query per Variation.
                if (
                    $product_stock &&
                    ! empty( $variable_ids->posts )
                ) {
                    update_meta_cache( 'post', wp_list_pluck( $variable_ids->posts, 'ID' ) );
                }
                // WP_Query can leave posts null for an 'id=>parent' query that matches nothing.
                foreach ( (array) $variable_ids->posts as $variable ) {
                    $variable_id = absint( $variable->ID );

                    // Filter out Products based on the Stock Status and Quantity.
                    if ( $product_stock ) {
                        $manage_stock = get_post_meta( $variable_id, '_manage_stock', true );
                        $stock_status = get_post_meta( $variable_id, '_stock_status', true );
                        $quantity     = get_post_meta( $variable_id, '_stock', true );
                        $quantity     = ( function_exists( 'wc_stock_amount' ) ? wc_stock_amount( $quantity ) : absint( $quantity ) );
                        switch ( $product_stock ) {

                            case 'outofstock':
                                if (
                                    ( $manage_stock == 'yes' && $quantity > 0 ) ||
                                    $stock_status !== 'outofstock'
                                ) {
                                    unset( $variable_id );
                                    continue ( 2 );
                                }
                                break;

                            case 'instock':
                                if (
                                    ( $manage_stock == 'yes' && $quantity == 0 ) ||
                                    $stock_status !== 'instock'
                                ) {
                                    unset( $variable_id );
                                    continue ( 2 );
                                }
                                break;
                        }
                        unset( $stock_status, $quantity );
                    }

                    // Check we're not including a duplicate Product ID.
                    if ( ! isset( $queried_map[ $variable_id ] ) ) {
                        $variables[ absint( $variable->post_parent ) ][] = $variable_id;
                    }
                }

                // Rebuild the list in the main query order, keeping the Products that
                // passed the filters above and slotting in each Parent's Variations.
                // A Parent removed because Variable was not selected still anchors
                // where its Variations go.
                $included_ids = array_flip( $products );
                $products     = array();
                foreach ( $queried_ids as $product_id ) {
                    if ( isset( $included_ids[ $product_id ] ) ) {
                        $products[] = $product_id;
                    }
                    if ( isset( $variables[ $product_id ] ) ) {
                        array_push( $products, ...$variables[ $product_id ] );
                        foreach ( $variables[ $product_id ] as $variable_id ) {
                            $row_anchors[ $variable_id ] = $product_id;
                        }
                    }
                }
                unset( $variables, $variable_ids, $variable_args, $variable, $variable_id, $included_ids );
            }

            // Count the Products and Parents this chunk adds to the window, and stop
            // when the window is full.
            foreach ( $products as $row_id ) {
                $anchors[ $row_anchors[ $row_id ] ?? $row_id ] = true;
            }
            array_push( $all_products, ...$products );
            if ( $window_end > 0 && count( $anchors ) >= $window_end ) {
                break;
            }
        }
        $products = $all_products;
        unset( $all_products, $all_ids, $id_chunks, $chunk_ids, $anchors, $queried_map );

        // Apply the Limit Volume and Volume Offset held back from the query. They
        // count Products and Parents, the same as on the query, and a Parent's
        // Variations go with it.
        if ( false !== $window_after_query ) {
            $window_anchors = array();
            foreach ( $products as $row_id ) {
                $window_anchors[ $row_anchors[ $row_id ] ?? $row_id ] = true;
            }
            $window_anchors = array_flip( array_slice( array_keys( $window_anchors ), $window_after_query[0], $window_after_query[1] ) );
            $windowed_rows  = array();
            foreach ( $products as $row_id ) {
                if ( isset( $window_anchors[ $row_anchors[ $row_id ] ?? $row_id ] ) ) {
                    $windowed_rows[] = $row_id;
                }
            }
            $products = $windowed_rows;
            unset( $window_anchors, $windowed_rows, $row_id );
        }

        // Only populate the $export Global if it is an export.
        if ( isset( $export ) ) {
            $export->total_rows = count( $products );
        }
        unset( $product_ids, $product_id, $queried_ids, $variable_parent_ids, $row_anchors, $stock_exempt_types, $unselected_parent_types, $passing_ids );
    }

    remove_filter( 'posts_clauses', 'woo_ce_products_sku_sort_clauses', 10 );

    // Filter Products by Language.
    if ( $product_language ) {

        global $sitepress;

        if ( method_exists( $sitepress, 'posts_where_filter' ) ) {
            add_filter( 'posts_where', array( $sitepress, 'posts_where_filter' ), 10, 2 );
        }
        remove_filter( 'posts_where', 'woo_ce_wp_query_product_where_override_language' );
    }

    return $products;
}

function woo_ce_get_product_data( $product_id = 0, $args = array(), $fields = array(), $export_settings = array() ) {

    global $export;
    if ( empty( $export ) ) {
        $export = $export_settings;
    }

    $upload_dir = wp_upload_dir();

    // Get Product defaults.
    $weight_unit    = get_option( 'woocommerce_weight_unit' );
    $dimension_unit = get_option( 'woocommerce_dimension_unit' );
    $height_unit    = $dimension_unit;
    $width_unit     = $dimension_unit;
    $length_unit    = $dimension_unit;

    $product  = get_post( $product_id );
    $_product = ( function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : false );
    // Check for corrupt Products, and old school WooCommerce...
    if ( ! version_compare( woo_get_woo_version(), '2.2', '<' ) && $_product == false ) {
        return false;
    }

    $product->parent_id  = '';
    $product->parent_sku = '';
    if ( $product->post_type == 'product_variation' ) {
        // Assign Parent ID for Variants then check if Parent exists.
        if ( $product->parent_id = $product->post_parent ) {
            $product->parent_sku = get_post_meta( $product->post_parent, '_sku', true );

            // Get the variation's global_unique_id
            $variation_global_unique_id = get_post_meta( $product_id, '_global_unique_id', true );
            // If the variation doesn't have a GTIN, try to get it from the parent
            if ( empty( $variation_global_unique_id ) ) {
                $product->global_unique_id = get_post_meta( $product->post_parent, '_global_unique_id', true );
            } else {
                $product->global_unique_id = $variation_global_unique_id;
            }
        } else {
            $product->parent_id = '';
        }
    }
    $product->product_id = $product_id;
    $product->sku        = get_post_meta( $product_id, '_sku', true );
    // For regular products (not variations), set the global_unique_id
    // For variations, we already handle this in the code above
    if ($product->post_type !== 'product_variation') {
        $product->global_unique_id = get_post_meta( $product_id, '_global_unique_id', true );
    }
    add_filter( 'the_title', 'woo_ce_get_product_title', 10, 2 );
    $product->name = woo_ce_format_post_title( get_the_title( $product_id ) );
    remove_filter( 'the_title', 'woo_ce_get_product_title' );
    $product->permalink   = get_permalink( $product_id );
    $product->product_url = ( method_exists( $_product, 'get_permalink' ) ? $_product->get_permalink() : get_permalink( $product_id ) );
    $product->slug        = $product->post_name;
    $product->user_name   = woo_ce_get_username( $product->post_author );
    $product->user_role   = woo_ce_format_user_role_label( woo_ce_get_user_role( $product->post_author ) );
    $product->description = woo_ce_format_description_excerpt( $product->post_content );
    $product->excerpt     = woo_ce_format_description_excerpt( $product->post_excerpt );
    // Check if we're dealing with a Variable Product Type.
    $term_taxonomy = 'product_type';
    if ( has_term( 'variable', $term_taxonomy, $product_id ) ) {
        $product->price = get_post_meta( $product_id, '_price', true );
        if (
            method_exists( $_product, 'get_variation_regular_price' ) &&
            method_exists( $_product, 'get_variation_sale_price' )
        ) {
            // Control whether the back-end or storefront price (after taxes) is displayed.
            $display      = apply_filters( 'woo_ce_product_variable_price_display', false );
            $pricing_args = array(
                'min_price'      => ( method_exists( $_product, 'get_variation_regular_price' ) ? $_product->get_variation_regular_price( 'min', $display ) : 0 ),
                'max_price'      => ( method_exists( $_product, 'get_variation_regular_price' ) ? $_product->get_variation_regular_price( 'max', $display ) : 0 ),
                'min_sale_price' => ( method_exists( $_product, 'get_variation_sale_price' ) ? $_product->get_variation_sale_price( 'min', $display ) : 0 ),
                'max_sale_price' => ( method_exists( $_product, 'get_variation_sale_price' ) ? $_product->get_variation_sale_price( 'max', $display ) : 0 ),
            );
            unset( $display );
            if ( $pricing_args['min_price'] == $pricing_args['max_price'] ) {
                $product->price      = woo_ce_format_price( $pricing_args['min_price'] );
                $product->sale_price = woo_ce_format_price( $pricing_args['min_sale_price'] );
            } else {
                $variable_price_format = apply_filters( 'woo_ce_product_variable_price', '%s-%s' );
                if ( $variable_price_format == '%s' ) {
                    $product->price = sprintf( $variable_price_format, woo_ce_format_price( $pricing_args['min_price'] ), woo_ce_format_price( $pricing_args['max_price'] ) );
                } else {
                    $product->price = sprintf( $variable_price_format, woo_ce_format_price( $pricing_args['min_price'] ), woo_ce_format_price( $pricing_args['max_price'] ) );
                }
                $variable_sale_price_format = apply_filters( 'woo_ce_product_variable_sale_price', '%s-%s' );
                if ( $variable_sale_price_format == '%s' ) {
                    $product->sale_price = sprintf( $variable_sale_price_format, woo_ce_format_price( $pricing_args['min_sale_price'] ), woo_ce_format_price( $pricing_args['max_sale_price'] ) );
                } else {
                    $product->sale_price = sprintf( $variable_sale_price_format, woo_ce_format_price( $pricing_args['min_sale_price'] ), woo_ce_format_price( $pricing_args['max_sale_price'] ) );
                }
            }
            $product = apply_filters( 'woo_ce_product_variation_pricing', $product, $pricing_args );
            unset( $pricing_args );
        }
    } else {
        $product->price      = get_post_meta( $product_id, '_regular_price', true );
        $product->sale_price = get_post_meta( $product_id, '_sale_price', true );
        if ( $product->price != '' ) {
            $product->price = woo_ce_format_price( $product->price );
        }
        if ( $product->sale_price != '' ) {
            $product->sale_price = woo_ce_format_price( $product->sale_price );
        }
    }
    $product->net_price             = ( $product->sale_price != '' ? $product->sale_price : $product->price );
    $product->sale_price_dates_from = woo_ce_format_product_sale_price_dates( get_post_meta( $product_id, '_sale_price_dates_from', true ) );
    $product->sale_price_dates_to   = woo_ce_format_product_sale_price_dates( get_post_meta( $product_id, '_sale_price_dates_to', true ) );
    $product->post_date             = woo_ce_format_date( $product->post_date );
    $product->post_modified         = woo_ce_format_date( $product->post_modified );
    $product->type                  = woo_ce_get_product_assoc_type( $product_id );
    if ( $product->post_type == 'product_variation' ) {
        $product->description = woo_ce_format_description_excerpt( get_post_meta( $product_id, '_variation_description', true ) );
        // Override the Product Type for Variations.
        $product->type = __( 'Variation', 'woocommerce-exporter' );
        // Override the Description and Excerpt if Variation Formatting is enabled.
        if ( woo_ce_get_option( 'variation_formatting', 0 ) ) {
            $parent = get_post( $product->parent_id );
            if ( empty( $product->description ) ) {
                $product->description = $parent->post_content;
            }
            if ( empty( $product->excerpt ) ) {
                $product->excerpt = $parent->post_excerpt;
            }
            unset( $parent );
        }
    }
    if ( version_compare( woo_get_woo_version(), '3.0', '>=' ) ) {
        $product->visibility = woo_ce_format_product_visibility( $product_id );
        $term_taxonomy       = 'product_visibility';
        $product->featured   = woo_ce_format_switch( has_term( 'featured', $term_taxonomy, $product_id ) );
    } else {
        $product->visibility = woo_ce_format_product_visibility( $product_id, get_post_meta( $product_id, '_visibility', true ) );
        $product->featured   = woo_ce_format_switch( get_post_meta( $product_id, '_featured', true ) );
    }
    $product->virtual      = woo_ce_format_switch( get_post_meta( $product_id, '_virtual', true ) );
    $product->downloadable = woo_ce_format_switch( get_post_meta( $product_id, '_downloadable', true ) );
    $product->weight       = get_post_meta( $product_id, '_weight', true );
    $product->weight_unit  = ( $product->weight != '' ? $weight_unit : '' );
    $product->height       = get_post_meta( $product_id, '_height', true );
    $product->height_unit  = ( $product->height != '' ? $height_unit : '' );
    $product->width        = get_post_meta( $product_id, '_width', true );
    $product->width_unit   = ( $product->width != '' ? $width_unit : '' );
    $product->length       = get_post_meta( $product_id, '_length', true );
    $product->length_unit  = ( $product->length != '' ? $length_unit : '' );
    $product->category     = woo_ce_get_product_assoc_categories( $product_id, $product->parent_id );
    if ( $product->post_type == 'product_variation' ) {
        // Override the Category if Variation Formatting is enabled.
        if ( woo_ce_get_option( 'variation_formatting', 0 ) ) {
            $product->category = woo_ce_get_product_assoc_categories( $product->parent_id, false );
        }
    }
    if ( ! empty( $product->category ) && apply_filters( 'woo_ce_product_enable_category_levels', false ) ) {
        $product->category_level_1 = '';
        $product->category_level_2 = '';
        $product->category_level_3 = '';
        // Separate the Category by Category separator.
        $category_levels = explode( $export->category_separator, $product->category );
        if ( ! empty( $category_levels ) ) {
            // Limitation to only do one tree of categories. If two parents, second wont export
            foreach ( $category_levels as $category ) {
                $category = explode( '>', $category );
                if ( ! empty( $category[0] ) && '' === $product->category_level_1 ) {
                    $product->category_level_1 = $category[0];
                }
                if ( ! empty( $category[1] ) && '' === $product->category_level_2 ) {
                    $product->category_level_2 = $category[1];
                }
                if ( ! empty( $category[2] ) && '' === $product->category_level_3 ) {
                    $product->category_level_3 = $category[2];
                }
            }
        }
    }
    $product->tag = woo_ce_get_product_assoc_tags( $product_id );
    if ( $product->post_type == 'product_variation' ) {
        // Override the Tag if Variation Formatting is enabled.
        if ( woo_ce_get_option( 'variation_formatting', 0 ) ) {
            $product->tag = woo_ce_get_product_assoc_tags( $product->parent_id );
        }
    }
    $product->manage_stock      = get_post_meta( $product_id, '_manage_stock', true );
    $product->allow_backorders  = woo_ce_format_product_allow_backorders( get_post_meta( $product_id, '_backorders', true ) );
    $product->sold_individually = woo_ce_format_switch( get_post_meta( $product_id, '_sold_individually', true ) );
    $product->total_sales       = get_post_meta( $product_id, 'total_sales', true );
    $product->grouped_products  = woo_ce_get_product_assoc_grouped_products( $product_id );
    $product->upsell_ids        = woo_ce_get_product_assoc_upsell_ids( $product_id );
    $product->crosssell_ids     = woo_ce_get_product_assoc_crosssell_ids( $product_id );
    $product->quantity          = get_post_meta( $product_id, '_stock', true );
    // Override Variable with total stock quantity.
    $term_taxonomy = 'product_type';
    if ( has_term( 'variable', $term_taxonomy, $product_id ) ) {
        if (
            version_compare( woo_get_woo_version(), '3.0', '>=' ) &&
            $product->manage_stock == 'no'
        ) {
            $product_variations = ( method_exists( $_product, 'get_available_variations' ) ? $_product->get_available_variations() : array() );
            if ( ! empty( $product_variations ) ) {
                $quantity = 0;
                foreach ( $product_variations as $variation ) {
                    if ( isset( $variation['max_qty'] ) ) {
                        $quantity += (int) $variation['max_qty'];
                    }
                }
                $product->quantity = $quantity;
                unset( $variation, $quantity );
            }
            unset( $product_variations );
        } elseif ( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
            $product->quantity = ( method_exists( $_product, 'get_stock_quantity' ) ? $_product->get_stock_quantity() : $product->quantity );
        } else {
            $product->quantity = ( method_exists( $_product, 'get_total_stock' ) ? $_product->get_total_stock() : $product->quantity );
        }
    }
    $product->quantity = ( function_exists( 'wc_stock_amount' ) ? wc_stock_amount( $product->quantity ) : $product->quantity );
    if (
        $product->manage_stock == 'no' &&
        ! $product->quantity
    ) {
        $product->quantity = '';
    }
    $product->manage_stock    = woo_ce_format_switch( $product->manage_stock );
    $product->stock_status    = woo_ce_format_product_stock_status( get_post_meta( $product_id, '_stock_status', true ), $product->quantity );
    $product->image           = woo_ce_get_product_assoc_featured_image( $product_id, $product->parent_id );
    $product->image_thumbnail = woo_ce_get_product_assoc_featured_image( $product_id, $product->parent_id, 'thumbnail' );
    $product->image_embed     = '';
    if ( ! empty( $product->image ) ) {
        $image_id                   = woo_ce_get_product_assoc_featured_image( $product_id, $product->parent_id, 'image_id' );
        $product->image_title       = get_the_title( $image_id );
        $product->image_caption     = get_post_field( 'post_excerpt', $image_id );
        $product->image_alt         = get_post_field( '_wp_attachment_image_alt', $image_id );
        $product->image_description = get_post_field( 'post_content', $image_id );
        if ( isset( $export->export_format ) && $export->export_format == 'xlsx' ) {
            if ( $metadata = wp_get_attachment_metadata( $image_id ) ) {
                // Override for the image embed thumbnail size; use registered WordPress image size names.
                $thumbnail_size = apply_filters( 'woo_ce_override_embed_thumbnail_size', 'shop_thumbnail' );
                if ( isset( $metadata['sizes'][ $thumbnail_size ] ) && $metadata['sizes'][ $thumbnail_size ]['file'] ) {
                    $image_path           = pathinfo( $metadata['file'] );
                    $product->image_embed = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][ $thumbnail_size ]['file'];
                    // Override for using relative image embed filepath.
                    if ( ! file_exists( trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][ $thumbnail_size ]['file'] ) || apply_filters( 'woo_ce_override_image_embed_relative_path', false ) ) {
                        $product->image_embed = trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][ $thumbnail_size ]['file'];
                    }
                }
            }
            unset( $image_id, $metadata, $thumbnail_size, $image_path );
        }
    }
    $product->product_gallery             = woo_ce_get_product_assoc_product_gallery( $product_id );
    $product->product_gallery_thumbnail   = woo_ce_get_product_assoc_product_gallery( $product_id, 'thumbnail' );
    $product->product_gallery_embed       = '';
    $product->product_gallery_title       = '';
    $product->product_gallery_caption     = '';
    $product->product_gallery_alt         = '';
    $product->product_gallery_description = '';
    if ( ! empty( $product->product_gallery ) ) {
        $image_ids = woo_ce_get_product_assoc_product_gallery( $product_id, 'image_id' );
        if ( ! empty( $image_ids ) ) {
            $image_ids                            = explode( $export->category_separator, $image_ids );
            $product->product_gallery_embed       = array();
            $product->product_gallery_title       = array();
            $product->product_gallery_caption     = array();
            $product->product_gallery_alt         = array();
            $product->product_gallery_description = array();
            foreach ( $image_ids as $image_id ) {
                $product->product_gallery_title[]       = get_the_title( $image_id );
                $product->product_gallery_caption[]     = get_post_field( 'post_excerpt', $image_id );
                $product->product_gallery_alt[]         = get_post_field( '_wp_attachment_image_alt', $image_id );
                $product->product_gallery_description[] = get_post_field( 'post_content', $image_id );
                if ( isset( $export->export_format ) && $export->export_format == 'xlsx' ) {
                    if ( $metadata = wp_get_attachment_metadata( $image_id ) ) {
                        // Override for the image embed thumbnail size; use registered WordPress image size names.
                        $thumbnail_size = apply_filters( 'woo_ce_override_embed_thumbnail_size', 'shop_thumbnail' );
                        if ( isset( $metadata['sizes'][ $thumbnail_size ] ) && $metadata['sizes'][ $thumbnail_size ]['file'] ) {
                            $image_path = pathinfo( $metadata['file'] );
                            // Override for using relative image embed filepath.
                            if ( ! file_exists( trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][ $thumbnail_size ]['file'] ) || apply_filters( 'woo_ce_override_image_embed_relative_path', false ) ) {
                                $product->product_gallery_embed[] = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][ $thumbnail_size ]['file'];
                            } else {
                                $product->product_gallery_embed[] = trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][ $thumbnail_size ]['file'];
                            }
                        }
                    }
                }
            }
            $product->product_gallery_embed       = implode( $export->category_separator, $product->product_gallery_embed );
            $product->product_gallery_title       = implode( $export->category_separator, $product->product_gallery_title );
            $product->product_gallery_caption     = implode( $export->category_separator, $product->product_gallery_caption );
            $product->product_gallery_alt         = implode( $export->category_separator, $product->product_gallery_alt );
            $product->product_gallery_description = implode( $export->category_separator, $product->product_gallery_description );
            unset( $image_ids, $image_id, $metadata, $thumbnail_size, $image_path );
        }
    }
    $product->tax_status         = woo_ce_format_product_tax_status( get_post_meta( $product_id, '_tax_status', true ) );
    $product->tax_class          = woo_ce_format_product_tax_class( get_post_meta( $product_id, '_tax_class', true ) );
    $product->shipping_class     = woo_ce_get_product_assoc_shipping_class( $product_id );
    $product->external_url       = get_post_meta( $product_id, '_product_url', true );
    $product->button_text        = get_post_meta( $product_id, '_button_text', true );
    $product->download_file_path = woo_ce_get_product_assoc_download_files( $product_id, 'url' );
    $product->download_file_name = woo_ce_get_product_assoc_download_files( $product_id, 'name' );
    $product->download_limit     = get_post_meta( $product_id, '_download_limit', true );
    $product->download_expiry    = get_post_meta( $product_id, '_download_expiry', true );
    $product->download_type      = woo_ce_format_product_download_type( get_post_meta( $product_id, '_download_type', true ) );
    $product->purchase_note      = get_post_meta( $product_id, '_purchase_note', true );
    $product->product_status     = woo_ce_format_post_status( $product->post_status );
    $product->enable_reviews     = woo_ce_format_comment_status( $product->comment_status );
    $product->review_count       = get_post_meta( $product_id, '_wc_review_count', true );
    $rating_count                = get_post_meta( $product_id, '_wc_rating_count', true );
    if ( $product->post_type == 'product' ) {
        $product->rating_count = ( is_array( $rating_count ) ? count( $rating_count ) : '' );
    }
    $product->average_rating = get_post_meta( $product_id, '_wc_average_rating', true );

    // Allow Plugin/Theme authors to add support for additional Product columns.
    $product = apply_filters( 'woo_ce_product_item', $product, $product_id, $_product );

    unset( $_product );

    // Trim back the Product just to requested export fields.
    if ( ! empty( $fields ) ) {
        $fields = array_merge( $fields, array( 'id', 'ID', 'post_parent', 'filter' ) );
        if ( ! empty( $product ) ) {
            foreach ( $product as $key => $data ) {
                if ( ! in_array( $key, $fields ) ) {
                    unset( $product->$key );
                }
            }
        }
    }

    return $product;
}

function woo_ce_wp_query_product_where_override_language( $where ) {

    global $export, $wpdb;

    $condition = '';
    if ( ! empty( $export->args ) && ! empty( $export->args['product_language'] ) ) {
        $languages = array_values( array_filter( array_map( 'sanitize_key', (array) $export->args['product_language'] ) ) );
        if ( ! empty( $languages ) ) {
            $placeholders = implode( ', ', array_fill( 0, count( $languages ), '%s' ) );
            // Appended to, never replacing, the existing clause so the post type, status and ID constraints survive.
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
            $condition = $wpdb->prepare( " AND language_code IN ( {$placeholders} )", $languages );
        }
    }

    return $where . $condition;
}

if ( ! function_exists( 'woo_ce_export_dataset_override_product' ) ) {
    function woo_ce_export_dataset_override_product( $output = null, $export_type = null ) {

        global $export;

        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_export_dataset_override_product() before woo_ce_get_products(): ' . ( time() - $export->start_time ) ) );
        }
        if ( $products = woo_ce_get_products( $export->args ) ) {
            if ( WOO_CE_LOGGING ) {
                woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_export_dataset_override_product() after woo_ce_get_products(): ' . ( time() - $export->start_time ) ) );
            }
            $export->total_rows = count( $products );
            // XML, JSON export.
            if ( in_array( $export->export_format, array( 'xml', 'json' ) ) ) {
                if ( ! empty( $export->fields ) ) {
                    foreach ( $products as $product ) {
                        if ( WOO_CE_LOGGING ) {
                            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_product_data(): ' . ( time() - $export->start_time ) ) );
                        }
                        $child   = $output->addChild( apply_filters( 'woo_ce_export_xml_product_node', sanitize_key( $export_type ) ) );
                        $product = woo_ce_get_product_data( $product, $export->args, array_keys( $export->fields ) );
                        if (
                            $export->export_format !== 'json' &&
                            apply_filters( 'woo_ce_export_xml_product_node_attribute_id', true )
                        ) {
                            $child->addAttribute( 'id', ( isset( $product->product_id ) ? $product->product_id : '' ) );
                        }
                        foreach ( array_keys( $export->fields ) as $key => $field ) {
                            if ( isset( $product->$field ) ) {
                                if ( ! is_array( $field ) ) {
                                    if ( woo_ce_is_xml_cdata( $product->$field, $export_type, $field ) ) {
                                        $child->addChild( apply_filters( 'woo_ce_export_xml_product_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ) )->addCData( woo_ce_sanitize_xml_string( $product->$field ) );
                                    } else {
                                        $child->addChild( apply_filters( 'woo_ce_export_xml_product_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $product->$field ) ) );
                                    }
                                }
                            }
                        }
                    }
                }
            } elseif ( $export->export_format == 'rss' ) {
                // RSS export.
                if ( ! empty( $export->fields ) ) {
                    foreach ( $products as $product ) {
                        if ( WOO_CE_LOGGING ) {
                            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_product_data(): ' . ( time() - $export->start_time ) ) );
                        }
                        $child   = $output->addChild( 'item' );
                        $product = woo_ce_get_product_data( $product, $export->args, array_keys( $export->fields ) );
                        foreach ( array_keys( $export->fields ) as $field ) {
                            if ( isset( $product->$field ) ) {
                                if ( ! is_array( $field ) ) {
                                    if ( woo_ce_is_xml_cdata( $product->$field ) ) {
                                        $child->addChild( sanitize_key( $field ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $product->$field ) ) );
                                    } else {
                                        $child->addChild( sanitize_key( $field ), esc_html( woo_ce_sanitize_xml_string( $product->$field ) ) );
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // PHPExcel export.
                foreach ( $products as $key => $product ) {
                    if ( WOO_CE_LOGGING ) {
                        woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_product_data(): ' . ( time() - $export->start_time ) ) );
                    }
                    $products[ $key ] = woo_ce_get_product_data( $product, $export->args, array_keys( $export->fields ) );
                }
                $output = $products;
            }
            unset( $products, $product );
        }

        return $output;
    }
}

function woo_ce_export_dataset_multisite_override_product( $output = null, $export_type = null ) {

    global $export;

    if ( WOO_CE_LOGGING ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_export_dataset_multisite_override_product(): ' . ( time() - $export->start_time ) ) );
    }
    $sites = get_sites();
    if ( ! empty( $sites ) ) {
        foreach ( $sites as $site ) {
            switch_to_blog( $site->blog_id );
            if ( WOO_CE_LOGGING ) {
                woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_export_dataset_multisite_override_product() before woo_ce_get_products(): ' . ( time() - $export->start_time ) ) );
            }
            if ( $products = woo_ce_get_products( $export->args ) ) {
                if ( WOO_CE_LOGGING ) {
                    woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_export_dataset_multisite_override_product() after woo_ce_get_products(): ' . ( time() - $export->start_time ) ) );
                }
                $export->total_rows = count( $products );
                // XML, JSON export.
                if ( in_array( $export->export_format, array( 'xml', 'json' ) ) ) {
                    if ( ! empty( $export->fields ) ) {
                        foreach ( $products as $product ) {
                            if ( WOO_CE_LOGGING ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_product_data(): ' . ( time() - $export->start_time ) ) );
                            }
                            $child   = $output->addChild( apply_filters( 'woo_ce_export_xml_product_node', sanitize_key( $export_type ) ) );
                            $product = woo_ce_get_product_data( $product, $export->args, array_keys( $export->fields ) );
                            if (
                                $export->export_format !== 'json' &&
                                apply_filters( 'woo_ce_export_xml_product_node_attribute_id', true )
                            ) {
                                $child->addAttribute( 'id', ( isset( $product->product_id ) ? $product->product_id : '' ) );
                            }
                            foreach ( array_keys( $export->fields ) as $key => $field ) {
                                if ( isset( $product->$field ) ) {
                                    if ( ! is_array( $field ) ) {
                                        if ( woo_ce_is_xml_cdata( $product->$field, $export_type, $field ) ) {
                                            $child->addChild( apply_filters( 'woo_ce_export_xml_product_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ) )->addCData( woo_ce_sanitize_xml_string( $product->$field ) );
                                        } else {
                                            $child->addChild( apply_filters( 'woo_ce_export_xml_product_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $product->$field ) ) );
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ( $export->export_format == 'rss' ) {
                    // RSS export.
                    if ( ! empty( $export->fields ) ) {
                        foreach ( $products as $product ) {
                            if ( WOO_CE_LOGGING ) {
                                woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_product_data(): ' . ( time() - $export->start_time ) ) );
                            }
                            $child   = $output->addChild( 'item' );
                            $product = woo_ce_get_product_data( $product, $export->args, array_keys( $export->fields ) );
                            foreach ( array_keys( $export->fields ) as $field ) {
                                if ( isset( $product->$field ) ) {
                                    if ( ! is_array( $field ) ) {
                                        if ( woo_ce_is_xml_cdata( $product->$field ) ) {
                                            $child->addChild( sanitize_key( $field ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $product->$field ) ) );
                                        } else {
                                            $child->addChild( sanitize_key( $field ), esc_html( woo_ce_sanitize_xml_string( $product->$field ) ) );
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // PHPExcel export.
                    foreach ( $products as $key => $product ) {
                        if ( WOO_CE_LOGGING ) {
                            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_product_data(): ' . ( time() - $export->start_time ) ) );
                        }
                        $products[ $key ] = woo_ce_get_product_data( $product, $export->args, array_keys( $export->fields ) );
                    }
                    if ( is_null( $output ) ) {
                        $output = $products;
                    } else {
                        $output = array_merge( $output, $products );
                    }
                }
                unset( $products, $product );
            }
            restore_current_blog();
        }
    }

    return $output;
}

// Filters the get_the_title() function and adds friendly Variation information.
function woo_ce_get_product_title( $title = '', $post_ID = '' ) {

    if ( ! empty( $post_ID ) ) {

        remove_filter( 'the_title', 'woo_ce_get_product_title' );
        $_product = ( function_exists( 'wc_get_product' ) ? wc_get_product( $post_ID ) : false );

        if ( ! empty( $_product ) ) {
            if ( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
                $title = ( method_exists( $_product, 'get_name' ) ? $_product->get_name() : $title );
            } else {
                $title = ( method_exists( $_product, 'get_title' ) ? $_product->get_title() : $title );
            }
            // Check if we're dealing with a Variation.
            $type = ( method_exists( $_product, 'is_type' ) ? $_product->is_type( 'variation' ) : false );
            if ( $type ) {
                $list_attributes = array();
                $attributes      = ( method_exists( $_product, 'get_variation_attributes' ) ? $_product->get_variation_attributes() : array() );
                if ( ! empty( $attributes ) ) {
                    $format = apply_filters( 'woo_ce_get_product_title_attribute_formatting', 'slug', $post_ID );
                    foreach ( $attributes as $name => $attribute ) {
                        switch ( $format ) {

                            case 'title':
                                $list_attributes[] = wc_attribute_label( str_replace( 'attribute_', '', $name ) ) . ': ' . woo_ce_get_product_attribute_name_by_slug( $attribute, str_replace( 'attribute_', '', $name ) );
                                break;

                            case 'slug':
                            default:
                                $list_attributes[] = wc_attribute_label( str_replace( 'attribute_', '', $name ) ) . ': ' . $attribute;
                                break;

                            case 'empty':
                                // Do nothing...
                                break;
                        }
                    }
                    if ( ! empty( $list_attributes ) ) {
                        $title .= ' - ' . implode( ', ', $list_attributes );
                    }
                }
                unset( $attributes );
            }
        }
    }

    return $title;
}

// Filters the get_the_title() function and adds friendly Variation information suffixed with SKU.
function woo_ce_get_product_title_sku( $title = '', $post_ID = '' ) {

    if ( ! empty( $post_ID ) ) {

        $product = ( function_exists( 'wc_get_product' ) ? wc_get_product( $post_ID ) : false );
        if ( ! empty( $product ) ) {
            // Check if we're dealing with a Variation.
            $title = $product->get_title();
            if ( $product->is_type( 'variation' ) ) {
                $list_attributes = array();
                $attributes      = $product->get_variation_attributes();
                if ( ! empty( $attributes ) ) {
                    foreach ( $attributes as $name => $attribute ) {
                        $list_attributes[] = wc_attribute_label( str_replace( 'attribute_', '', $name ) ) . ': ' . $attribute;
                    }
                    $title .= ' - ' . implode( ', ', $list_attributes );
                }
                unset( $attributes );
            }
            $sku = $product->get_sku();
            if ( ! empty( $sku ) ) {
                $title .= ' (' . sprintf( __( 'SKU: %s', 'woocommerce-exporter' ), $sku ) . ')';
            }
            unset( $sku );
        }
    }

    return $title;
}

// Returns date of first Product Date Modified, any status.
function woo_ce_get_product_first_date( $date_format = 'd/m/Y' ) {

    $output = date( $date_format, mktime( 0, 0, 0, date( 'n' ), 1 ) );

    $post_type = 'product';
    $args      = array(
        'post_type'   => $post_type,
        'orderby'     => 'post_date',
        'order'       => 'ASC',
        'numberposts' => 1,
        'post_status' => 'any',
    );
    $products  = get_posts( $args );
    if ( ! empty( $products ) ) {
        $output = date( $date_format, strtotime( $products[0]->post_date ) );
        unset( $products );
    }

    return $output;
}

// Returns Product Categories or optionally other hierarchical Term Taxonomies associated to a specific Product.
function woo_ce_get_product_assoc_categories( $product_id = 0, $parent_id = 0, $term_taxonomy = 'product_cat', $format = 'default' ) {

    global $export;

    $category_separator = apply_filters( 'woo_ce_get_product_assoc_categories_separator', $export->category_separator );

    $args = array();
    if ( $format == 'ids' ) {
        $output         = array();
        $args['fields'] = 'ids';
    } else {
        $output = '';
    }

    // Allow other developers to bake in their own filters.
    $args = apply_filters( 'woo_ce_get_product_assoc_categories_args', $args, $product_id, $parent_id, $term_taxonomy, $format );

    // Return Product Categories of Parent if this is a Variation.
    if ( ! empty( $parent_id ) ) {
        $product_id = $parent_id;
    }
    $categories = ( ! empty( $product_id ) ? wp_get_object_terms( $product_id, $term_taxonomy, $args ) : false );
    if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
        if ( $format == 'ids' ) {
            $output = $categories;
        } else {
            $size = apply_filters( 'woo_ce_get_product_assoc_categories_size', count( $categories ) );
            for ( $i = 0; $i < $size; $i++ ) {
                if ( $categories[ $i ]->parent == '0' ) {
                    $output .= $categories[ $i ]->name . $category_separator;
                } else {
                    // Check if Parent -> Child.
                    $category_1 = get_term( $categories[ $i ]->parent, $term_taxonomy );
                    // Check if Parent -> Child -> Subchild.
                    if ( $category_1->parent == '0' ) {
                        $output .= $category_1->name . '>' . $categories[ $i ]->name . $category_separator;
                    } else {
                        // Check if Parent -> Child -> Subchild.
                        $category_2 = get_term( $category_1->parent, $term_taxonomy );
                        if ( $category_2->parent == '0' ) {
                            $output .= $category_2->name . '>' . $category_1->name . '>' . $categories[ $i ]->name . $category_separator;
                        } else {
                            // Check if Parent -> Child -> Child -> Subchild.
                            $category_3 = get_term( $category_2->parent, $term_taxonomy );
                            $output    .= $category_3->name . '>' . $category_2->name . '>' . $category_1->name . '>' . $categories[ $i ]->name . $category_separator;
                        }
                    }
                    unset( $category_1, $category_2, $category_3 );
                }
            }
            $output = substr( $output, 0, -1 );
            // Sort Categories.
            if ( $size > 1 && apply_filters( 'woo_ce_get_product_assoc_categories_sort', true ) ) {
                $output = explode( $category_separator, $output );
                sort( $output );
                $output = implode( $category_separator, $output );
            }
        }
    } elseif ( $format == 'ids' ) {
        $output = array();
    } elseif ( $term_taxonomy == 'product_cat' ) {
        $output .= __( 'Uncategorized', 'woocommerce-exporter' );
    }

    return $output;
}

// Returns Product Tags or optionally other single level Term Taxonomies associated to a specific Product.
function woo_ce_get_product_assoc_tags( $product_id = 0, $term_taxonomy = 'product_tag', $format = 'default' ) {

    global $export;

    $category_separator = apply_filters( 'woo_ce_get_product_assoc_tags_separator', $export->category_separator );

    // The 'ids' format is consumed as an array by the Variation term filter in
    // woo_ce_get_products(). It used to be imploded like the 'default' format,
    // which handed array_intersect() a string; that branch was unreachable while
    // the tax_query clause emptied every Variation-only query, so the breakage
    // only surfaced once the clause was skipped for that case.
    // woo_ce_get_variation_parent_term_ids() is the only 'ids' caller - every
    // other caller takes the default format.
    $output = ( 'ids' === $format ? array() : '' );
    $args   = array();
    if ( $format == 'ids' ) {
        $args['fields'] = 'ids';
    } else {
        $args['fields'] = 'names';
    }

    // Allow other developers to bake in their own filters.
    $args = apply_filters( 'woo_ce_get_product_assoc_tags_args', $args, $product_id, $term_taxonomy );

    $terms = wp_get_object_terms( $product_id, $term_taxonomy, $args );
    if ( ! empty( $terms ) && is_wp_error( $terms ) == false ) {
        $output = ( 'ids' === $format ? array_map( 'intval', $terms ) : implode( $category_separator, $terms ) );
        unset( $terms );
    }

    return $output;
}

// Returns the Featured Image associated to a specific Product.
function woo_ce_get_product_assoc_featured_image( $product_id = 0, $parent_id = 0, $image_format = 'full' ) {

    global $export;

    $output = '';
    if ( ! empty( $product_id ) ) {
        $thumbnail_id = get_post_meta( $product_id, '_thumbnail_id', true );
        // Default empty value.
        if ( isset( $export->args['product_image_formatting'] ) == false ) {
            $export->args['product_image_formatting'] = woo_ce_get_option( 'product_image_formatting', 1 );
        }
        if ( ! empty( $thumbnail_id ) ) {
            // Check if we're returning ID's or URL's.
            if ( $export->args['product_image_formatting'] == '0' || $image_format == 'image_id' ) {
                $output = $thumbnail_id;
            } elseif ( in_array( $export->args['product_image_formatting'], array( '1', '2' ) ) ) {
                switch ( $export->args['product_image_formatting'] ) {

                    case '1':
                        // Media URL.
                        if ( $image_format == 'full' ) {
                            $output = wp_get_attachment_url( $thumbnail_id );
                        } elseif ( $image_format == 'thumbnail' ) {
                            $output = wp_get_attachment_thumb_url( $thumbnail_id );
                        }
                        break;

                    case '2':
                        // Media filename.
                        if ( $image_format == 'full' ) {
                            $output = get_attached_file( $thumbnail_id );
                        } elseif ( $image_format == 'thumbnail' ) {
                            $output = wp_get_attachment_thumb_file( $thumbnail_id );
                            // Media don't have a 'thumb' size assigned.
                            if ( $output == false ) {
                                $file           = get_attached_file( $thumbnail_id );
                                $imagedata      = wp_get_attachment_metadata( $thumbnail_id );
                                $thumbnail_size = apply_filters( 'woo_ce_override_image_thumbnail_size', 'thumbnail' );
                                if ( ! empty( $imagedata['sizes'][ $thumbnail_size ]['file'] ) && ( $thumbfile = str_replace( basename( $file ), $imagedata['sizes'][ $thumbnail_size ]['file'], $file ) ) && file_exists( $thumbfile ) ) {
                                    $output = $thumbfile;
                                }
                                unset( $file, $imagedata, $thumbnail_size, $thumbfile );
                            }
                        }
                        break;
                }
            }
        } elseif ( ! empty( $parent_id ) && woo_ce_get_option( 'variation_formatting', 0 ) ) {
            // Return Feature Image of Parent if this is a Variation.
            $thumbnail_id = get_post_meta( $parent_id, '_thumbnail_id', true );
            if ( ! empty( $thumbnail_id ) ) {
                if ( $export->args['product_image_formatting'] == '0' || $image_format == 'image_id' ) {
                    $output = $thumbnail_id;
                } elseif ( in_array( $export->args['product_image_formatting'], array( '1', '2' ) ) ) {
                    switch ( $export->args['product_image_formatting'] ) {

                        case '1':
                            // Media URL.
                            if ( $image_format == 'full' ) {
                                $output = wp_get_attachment_url( $thumbnail_id );
                            } elseif ( $image_format == 'thumbnail' ) {
                                $output = wp_get_attachment_thumb_url( $thumbnail_id );
                            }
                            break;

                        case '2':
                            // Media filename.
                            if ( $image_format == 'full' ) {
                                $output = get_attached_file( $thumbnail_id );
                            } elseif ( $image_format == 'thumbnail' ) {
                                $output = wp_get_attachment_thumb_file( $thumbnail_id );
                                // Media don't have a 'thumb' size assigned.
                                if ( $output == false ) {
                                    $file           = get_attached_file( $thumbnail_id );
                                    $imagedata      = wp_get_attachment_metadata( $thumbnail_id );
                                    $thumbnail_size = apply_filters( 'woo_ce_override_image_thumbnail_size', 'thumbnail' );
                                    if ( ! empty( $imagedata['sizes'][ $thumbnail_size ]['file'] ) && ( $thumbfile = str_replace( basename( $file ), $imagedata['sizes'][ $thumbnail_size ]['file'], $file ) ) && file_exists( $thumbfile ) ) {
                                        $output = $thumbfile;
                                    }
                                    unset( $file, $imagedata, $thumbnail_size, $thumbfile );
                                }
                            }
                            break;
                    }
                }
            }
        }
    }
    return $output;
}

// Returns the Product Galleries associated to a specific Product.
function woo_ce_get_product_assoc_product_gallery( $product_id = 0, $image_format = 'full' ) {

    global $export;

    if ( ! empty( $product_id ) ) {
        $images = get_post_meta( $product_id, '_product_image_gallery', true );
        if ( ! empty( $images ) ) {
            $output = '';
            // Default empty value.
            if ( isset( $export->args['gallery_formatting'] ) == false ) {
                $export->args['gallery_formatting'] = woo_ce_get_option( 'gallery_formatting', 1 );
            }
            // Check if we're returning ID's or URL's.
            if ( $export->args['gallery_formatting'] == '0' || $image_format == 'image_id' ) {
                $images = explode( ',', $images );
                $output = implode( $export->category_separator, $images );
            } elseif ( in_array( $export->args['gallery_formatting'], array( '1', '2' ) ) ) {
                $images = explode( ',', $images );
                $size   = count( $images );
                for ( $i = 0; $i < $size; $i++ ) {
                    switch ( $export->args['gallery_formatting'] ) {

                        case '1':
                            // Media URL.
                            if ( $image_format == 'full' ) {
                                $images[ $i ] = wp_get_attachment_url( $images[ $i ] );
                            } elseif ( $image_format == 'thumbnail' ) {
                                $images[ $i ] = wp_get_attachment_thumb_url( $images[ $i ] );
                            }
                            break;

                        case '2':
                            // Media filename.
                            if ( $image_format == 'full' ) {
                                $images[ $i ] = get_attached_file( $images[ $i ] );
                            } elseif ( $image_format == 'thumbnail' ) {
                                $image_thumbnail = $images[ $i ];
                                $images[ $i ]    = wp_get_attachment_thumb_file( $images[ $i ] );
                                // Media don't have a 'thumb' size assigned.
                                if ( $images[ $i ] == false ) {
                                    $file           = get_attached_file( $image_thumbnail );
                                    $imagedata      = wp_get_attachment_metadata( $image_thumbnail );
                                    $thumbnail_size = apply_filters( 'woo_ce_override_image_thumbnail_size', 'thumbnail' );
                                    if ( ! empty( $imagedata['sizes'][ $thumbnail_size ]['file'] ) && ( $thumbfile = str_replace( basename( $file ), $imagedata['sizes'][ $thumbnail_size ]['file'], $file ) ) && file_exists( $thumbfile ) ) {
                                        $images[ $i ] = $thumbfile;
                                    }
                                    unset( $file, $imagedata, $thumbnail_size, $thumbfile );
                                }
                                unset( $image_thumbnail );
                            }
                            break;
                    }
                }
                $output = implode( $export->category_separator, $images );
            }
            return $output;
        }
    }
}

// Returns the Product Type of a specific Product.
function woo_ce_get_product_assoc_type( $product_id = 0 ) {

    global $export;

    $output        = '';
    $term_taxonomy = 'product_type';
    $types         = wp_get_object_terms( $product_id, $term_taxonomy );
    if ( empty( $types ) ) {
        $types = array( get_term_by( 'name', 'simple', $term_taxonomy ) );
    }
    if ( $types ) {
        $size = count( $types );
        for ( $i = 0; $i < $size; $i++ ) {
            $type    = get_term( $types[ $i ]->term_id, $term_taxonomy );
            $output .= woo_ce_format_product_type( $type->name ) . $export->category_separator;
        }
        $output = substr( $output, 0, -1 );
    }

    return $output;
}

// Returns the Shipping Class of a specific Product.
function woo_ce_get_product_assoc_shipping_class( $product_id = 0 ) {

    $shipping_class = 'No Shipping Class';
    $product        = wc_get_product( $product_id );

    if ( ! empty( $product ) ) {
        $shipping_class = $product->get_shipping_class();

        if ( ! empty( $shipping_class ) ) {
            $shipping_class = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );

            if ( ! empty( $shipping_class ) ) {
                $shipping_class = $shipping_class->name;
            }
        }
    }

    return $shipping_class;
}

// Returns the Grouped Products associated to a specific Grouped Product.
function woo_ce_get_product_assoc_grouped_products( $product_id = 0 ) {

    global $export;

    $output = '';
    if ( ! empty( $product_id ) ) {
        $grouped_formatting = $export->args['grouped_formatting'];
        $grouped_products   = get_post_meta( $product_id, '_children', true );
        // Convert Product ID to Product SKU or Product Name.
        if (
            in_array( $grouped_formatting, array( 1, 2 ) ) &&
            ! empty( $grouped_products )
        ) {
            $size = count( $grouped_products );
            for ( $i = 0; $i < $size; $i++ ) {
                switch ( $grouped_formatting ) {

                    case '1':
                        $grouped_products[ $i ] = get_post_meta( $grouped_products[ $i ], '_sku', true );
                        break;

                    case '2':
                        $grouped_products[ $i ] = woo_ce_format_post_title( get_the_title( $grouped_products[ $i ] ) );
                        break;
                }
                if ( empty( $grouped_products[ $i ] ) ) {
                    unset( $grouped_products[ $i ] );
                }
            }
            $grouped_products = array_values( $grouped_products );
        }
        $output = woo_ce_convert_product_ids( $grouped_products );
    }

    return $output;
}

// Returns the Up-Sell associated to a specific Product.
function woo_ce_get_product_assoc_upsell_ids( $product_id = 0 ) {

    global $export;

    $output = '';
    if ( $product_id ) {
        $upsell_ids = get_post_meta( $product_id, '_upsell_ids', true );
        // Convert Product ID to Product SKU as per Up-Sell Formatting.
        if ( $export->args['upsell_formatting'] == 1 && ! empty( $upsell_ids ) ) {
            $size = count( $upsell_ids );
            for ( $i = 0; $i < $size; $i++ ) {
                $upsell_ids[ $i ] = get_post_meta( $upsell_ids[ $i ], '_sku', true );
                if ( empty( $upsell_ids[ $i ] ) ) {
                    unset( $upsell_ids[ $i ] );
                }
            }
            // 'reindex' array.
            $upsell_ids = array_values( $upsell_ids );
        }
        $output = woo_ce_convert_product_ids( $upsell_ids );
    }

    return $output;
}

// Returns the Cross-Sell associated to a specific Product.
function woo_ce_get_product_assoc_crosssell_ids( $product_id = 0 ) {

    global $export;

    $output = '';
    if ( $product_id ) {
        $crosssell_ids = get_post_meta( $product_id, '_crosssell_ids', true );
        // Convert Product ID to Product SKU as per Cross-Sell Formatting.
        if ( $export->args['crosssell_formatting'] == 1 && ! empty( $crosssell_ids ) ) {
            $size = count( $crosssell_ids );
            for ( $i = 0; $i < $size; $i++ ) {
                $crosssell_ids[ $i ] = get_post_meta( $crosssell_ids[ $i ], '_sku', true );
                // Remove Cross-Sell if SKU is empty.
                if ( empty( $crosssell_ids[ $i ] ) ) {
                    unset( $crosssell_ids[ $i ] );
                }
            }
            // 'reindex' array.
            $crosssell_ids = array_values( $crosssell_ids );
        }
        $output = woo_ce_convert_product_ids( $crosssell_ids );
    }

    return $output;
}

// Returns Product Attributes associated to a specific Product.
function woo_ce_get_product_assoc_attributes( $product_id = 0, $args = array() ) {

    global $export;

    $defaults = array(
        'attribute'     => array(),
        'type'          => 'product',
        'fields'        => 'names',
        'term_taxonomy' => false,
    );
    $args     = wp_parse_args( $args, $defaults );

    extract( $args );

    $output = '';
    if ( ! empty( $product_id ) && ! empty( $attribute ) ) {
        $terms = array();
        if ( $type == 'product' ) {
            if ( $attribute['is_taxonomy'] == 1 ) {
                $term_taxonomy = $attribute['name'];
            }
        } elseif ( $type == 'global' ) {
            $term_taxonomy = sprintf( 'pa_%s', $attribute->attribute_name );
        }
        if ( ! empty( $term_taxonomy ) ) {
            // We are dealing with a Taxonomy-based Attribute.
            $args = array(
                'fields' => $fields,
            );

            // WPML - https://wpml.org/
            // WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
            if ( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
                $post_type    = 'product';
                $product_trid = ( function_exists( 'icl_object_id' ) ? icl_object_id( $product_id, $post_type, false, ICL_LANGUAGE_CODE ) : false );
                if ( ! empty( $product_trid ) ) {
                    $product_id = $product_trid;
                }
            }

            $terms = wp_get_object_terms( $product_id, $term_taxonomy, $args );

            // This filter will output only those attributes which are used for variations.
            if ( apply_filters( 'woo_ce_get_product_assoc_attributes_variations_only', false ) ) {
                $_product = wc_get_product( $product_id );
                $att      = $_product->get_variation_attributes();
                if ( apply_filters( 'woo_ce_get_product_assoc_attributes_variations_only_specific', false ) ) {
                    if ( $term_taxonomy === apply_filters( 'woo_ce_get_product_assoc_attributes_variations_only_specific_chosen', false ) ) {
                        $terms = $att[ $term_taxonomy ];
                    }
                } else {
                    $terms = $att[ $term_taxonomy ];
                }
                unset( $att, $_product );
            }

            if ( ! empty( $terms ) && is_wp_error( $terms ) == false ) {
                $output = implode( $export->category_separator, $terms );
                unset( $terms );
            }
        } else {
            // We are dealing with a custom Attribute; fingers crossed.
            if ( ! empty( $attribute['value'] ) ) {
                $output = $attribute['value'];
            }
        }
    }

    return $output;
}

// Returns the Variation quantities linked to Attributes of a given Variable Product.
function woo_ce_get_product_assoc_attribute_quantities( $product_id = 0, $args = array() ) {

    global $export;

    $defaults = array(
        'attribute' => array(),
        'type'      => 'product',
        'fields'    => 'ids',
    );
    $args     = wp_parse_args( $args, $defaults );
    extract( $args );

    $output    = '';
    $post_type = 'product_variation';
    if ( ! empty( $product_id ) && ! empty( $attribute ) ) {
        // Get the Term slugs linked to this Product.
        $terms = woo_ce_get_product_assoc_attributes( $product_id, $args );
        if ( ! empty( $terms ) ) {
            $output = array();
            $terms  = explode( $export->category_separator, $terms );
            foreach ( $terms as $term ) {
                // Return the Post ID of the Product linked to each Attribute.
                if ( $type == 'product' ) {
                    if ( $attribute['is_taxonomy'] == 1 ) {
                        $term_taxonomy = $attribute['name'];
                    }
                } elseif ( $type == 'global' ) {
                    $term_taxonomy = sprintf( 'pa_%s', $attribute->attribute_name );
                }
                $args        = array(
                    'post_type'      => $post_type,
                    'post_parent'    => $product_id,
                    'tax_query'      => array(
                        array(
                            'taxonomy' => $term_taxonomy,
                            'field'    => 'term_id',
                            'terms'    => $term,
                        ),
                    ),
                    'fields'         => 'ids',
                    'posts_per_page' => 1,
                );
                $product_ids = new WP_Query( $args );
                if ( $product_ids->posts ) {
                    $quantity = get_post_meta( $product_ids->posts[0], '_stock', true );
                    $quantity = ( function_exists( 'wc_stock_amount' ) ? wc_stock_amount( $quantity ) : absint( $quantity ) );
                    $output[] = $quantity;
                }
            }
            $output = implode( $export->category_separator, $output );
        }
    }

    return $output;
}

// Returns the Attribute Name when passed a Attribute Slug.
function woo_ce_get_product_attribute_name_by_slug( $slug = '', $term_taxonomy = '' ) {

    $output = $slug;
    if ( ! empty( $term_taxonomy ) ) {
        $term = get_term_by( 'slug', $slug, $term_taxonomy );
        if ( ! empty( $term ) ) {
            $output = $term->name;
        }
        unset( $term );
    }

    return $output;
}

// Returns File Downloads associated to a specific Product.
function woo_ce_get_product_assoc_download_files( $product_id = 0, $type = 'url' ) {

    global $export;

    $output = '';
    if ( $product_id ) {
        if ( version_compare( woo_get_woo_version(), '2.0', '>=' ) ) {
            // If WooCommerce 2.0+ is installed then use new _downloadable_files Post meta key
            if ( $file_downloads = maybe_unserialize( get_post_meta( $product_id, '_downloadable_files', true ) ) ) {
                foreach ( $file_downloads as $file_download ) {
                    if ( $type == 'url' ) {
                        $output .= $file_download['file'] . $export->category_separator;
                    } elseif ( $type == 'name' ) {
                        $output .= $file_download['name'] . $export->category_separator;
                    }
                }
                unset( $file_download, $file_downloads );
            }
            $output = substr( $output, 0, -1 );
        } else {
            // If WooCommerce -2.0 is installed then use legacy _file_paths Post meta key
            if ( $file_downloads = maybe_unserialize( get_post_meta( $product_id, '_file_paths', true ) ) ) {
                foreach ( $file_downloads as $file_download ) {
                    if ( $type == 'url' ) {
                        $output .= $file_download . $export->category_separator;
                    }
                }
                unset( $file_download, $file_downloads );
            }
            $output = substr( $output, 0, -1 );
        }
    }

    return $output;
}

function woo_ce_get_product_assoc_order_ids( $products = array() ) {

    // Save database processing.
    if ( count( $products ) == 0 ) {
        return;
    }

    global $wpdb;

	$output = false;
	if ( apply_filters( 'woo_ce_get_product_assoc_order_ids_alternate_query', false ) ) {
        $order_ids_sql = $wpdb->prepare(
            "SELECT `order_id` FROM `{$wpdb->prefix}woocommerce_order_items` as order_items, `{$wpdb->prefix}woocommerce_order_itemmeta` as order_itemmeta WHERE `order_items`.order_item_id = `order_itemmeta`.order_item_id AND `order_itemmeta`.meta_key IN ( '_product_id', '_variation_id' ) AND `order_itemmeta`.meta_value IN ( " . implode( ',', array_fill(0, count($products), '%d') ) . ' )',
            ...$products
        );
    } else {
        $order_ids_sql = $wpdb->prepare(
            "SELECT `order_id` FROM `{$wpdb->prefix}woocommerce_order_items` as order_items, `{$wpdb->prefix}woocommerce_order_itemmeta` as order_itemmeta WHERE `order_items`.order_item_id = `order_itemmeta`.order_item_id AND `order_itemmeta`.meta_key = '_product_id' AND `order_itemmeta`.meta_value IN ( " . implode( ',', array_fill(0, count($products), '%d') ) . ' )',
            ...$products
        );
    }
	$order_ids = $wpdb->get_col( $order_ids_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$wpdb->flush();
	if ( ! empty( $order_ids ) ) {
		$output = $order_ids;
		unset( $order_ids );
	}

    return $output;
}

function woo_ce_format_product_visibility( $product_id = 0, $visibility = '' ) {

    $output = '';
    // Check for empty default for Visibility.
    if ( empty( $visibility ) ) {
        $visibility = 'visible';
        if ( ! empty( $product_id ) ) {
            // Fall back to checking Term Taxonomy.
            $term_taxonomy = 'product_visibility';
            $args          = array(
                'fields' => 'names',
            );
            $terms         = wp_get_object_terms( $product_id, $term_taxonomy, $args );
            if ( ! empty( $terms ) && is_wp_error( $terms ) == false ) {
                // Just for fun we have to combine Terms to decipher the Visibility.
                if ( in_array( 'exclude-from-search', $terms ) && in_array( 'exclude-from-catalog', $terms ) ) {
                    $visibility = 'hidden';
                } elseif ( in_array( 'exclude-from-search', $terms ) ) {
                    $visibility = 'catalog';
                } elseif ( in_array( 'exclude-from-catalog', $terms ) ) {
                    $visibility = 'search';
                }
            }
        }
    }
    switch ( $visibility ) {

        default:
        case 'visible':
            if ( apply_filters( 'woo_ce_format_product_visibility_legacy_labels', false ) ) {
                $output = __( 'Catalog & Search', 'woocommerce-exporter' );
            } else {
                $output = __( 'Shop and search results', 'woocommerce-exporter' );
            }
            break;

        case 'catalog':
            if ( apply_filters( 'woo_ce_format_product_visibility_legacy_labels', false ) ) {
                $output = __( 'Catalog', 'woocommerce-exporter' );
            } else {
                $output = __( 'Shop only', 'woocommerce-exporter' );
            }
            break;

        case 'search':
            if ( apply_filters( 'woo_ce_format_product_visibility_legacy_labels', false ) ) {
                $output = __( 'Search', 'woocommerce-exporter' );
            } else {
                $output = __( 'Search results only', 'woocommerce-exporter' );
            }
            break;

        case 'hidden':
            $output = __( 'Hidden', 'woocommerce-exporter' );
            break;
    }

    return $output;
}

function woo_ce_format_product_allow_backorders( $allow_backorders = '' ) {

    $output = '';
    if ( ! empty( $allow_backorders ) ) {
        switch ( $allow_backorders ) {

            case 'yes':
            case 'no':
                $output = woo_ce_format_switch( $allow_backorders );
                break;

            case 'notify':
                $output = __( 'Notify', 'woocommerce-exporter' );
                break;
        }
    }

    return $output;
}

function woo_ce_format_product_download_type( $download_type = '' ) {

    $output = __( 'Standard', 'woocommerce-exporter' );
    if ( ! empty( $download_type ) ) {
        switch ( $download_type ) {

            case 'application':
                $output = __( 'Application', 'woocommerce-exporter' );
                break;

            case 'music':
                $output = __( 'Music', 'woocommerce-exporter' );
                break;
        }
    }

    return $output;
}

function woo_ce_format_product_stock_status( $stock_status = '', $stock = '' ) {

    $output = '';
    if ( empty( $stock_status ) && ! empty( $stock ) ) {
        if ( $stock ) {
            $stock_status = 'instock';
        } else {
            $stock_status = 'outofstock';
        }
    }
    if ( $stock_status ) {
        switch ( $stock_status ) {

            case 'instock':
                $output = __( 'In Stock', 'woocommerce-exporter' );
                break;

            case 'outofstock':
                $output = __( 'Out of Stock', 'woocommerce-exporter' );
                break;

            case 'onbackorder':
                $output = __( 'On Backorder', 'woocommerce-exporter' );
                break;
        }
    }

    return $output;
}

function woo_ce_format_product_tax_status( $tax_status = null ) {

    $output = '';
    if ( ! empty( $tax_status ) ) {
        switch ( $tax_status ) {

            case 'taxable':
                $output = __( 'Taxable', 'woocommerce-exporter' );
                break;

            case 'shipping':
                $output = __( 'Shipping Only', 'woocommerce-exporter' );
                break;

            case 'none':
                $output = __( 'None', 'woocommerce-exporter' );
                break;
        }
    }

    return $output;
}

function woo_ce_format_product_tax_class( $tax_class = '' ) {

    global $export;

    $output = '';
    if ( $tax_class ) {
        switch ( $tax_class ) {

            case '*':
                $tax_class = __( 'Standard', 'woocommerce-exporter' );
                break;

            case 'reduced-rate':
                $tax_class = __( 'Reduced Rate', 'woocommerce-exporter' );
                break;

            case 'zero-rate':
                $tax_class = __( 'Zero Rate', 'woocommerce-exporter' );
                break;
        }
        $output = $tax_class;
    }

    return $output;
}

function woo_ce_format_product_type( $type_id = '' ) {

    $output = $type_id;
    if ( $output ) {
        $product_types = apply_filters(
            'woo_ce_format_product_types',
            array(
                'simple'                => __( 'Simple Product', 'woocommerce' ),
                'downloadable'          => __( 'Downloadable', 'woocommerce' ),
                'grouped'               => __( 'Grouped Product', 'woocommerce' ),
                'virtual'               => __( 'Virtual', 'woocommerce' ),
                'variable'              => __( 'Variable', 'woocommerce' ),
                'external'              => __( 'External/Affiliate Product', 'woocommerce' ),
                'variation'             => __( 'Variation', 'woocommerce-exporter' ),
                'subscription'          => __( 'Simple Subscription', 'woocommerce-exporter' ),
                'variable-subscription' => __( 'Variable Subscription', 'woocommerce-exporter' ),
            )
        );
        if ( isset( $product_types[ $type_id ] ) ) {
            $output = $product_types[ $type_id ];
        }
    }

    return $output;
}

// Returns a list of WooCommerce Product Types to export process.
function woo_ce_get_product_types() {

    $term_taxonomy = 'product_type';
    $args          = array(
        'hide_empty' => 0,
    );

    // Allow other developers to bake in their own filters.
    $args = apply_filters( 'woo_ce_get_product_types_args', $args );

    $types = get_terms( $term_taxonomy, $args );
    if (
        ! empty( $types ) &&
        is_wp_error( $types ) == false
    ) {
        $output = array();
        $size   = count( $types );
        for ( $i = 0; $i < $size; $i++ ) {
            $output[ $types[ $i ]->slug ] = array(
                'name'  => ucfirst( $types[ $i ]->name ),
                'count' => $types[ $i ]->count,
            );
            // Override the Product Type count for Downloadable and Virtual.
            if ( in_array( $types[ $i ]->slug, array( 'downloadable', 'virtual' ) ) ) {
                if ( $types[ $i ]->slug == 'downloadable' ) {
                    $args = array(
                        'meta_key'   => '_downloadable',
                        'meta_value' => 'yes',
                    );
                } elseif ( $types[ $i ]->slug == 'virtual' ) {
                    $args = array(
                        'meta_key'   => '_virtual',
                        'meta_value' => 'yes',
                    );
                }
                $output[ $types[ $i ]->slug ]['count'] = woo_ce_get_product_type_count( 'product', $args );
            }
        }
        $output['variation'] = array(
            'name'  => __( 'Variation', 'woocommerce-exporter' ),
            'count' => woo_ce_get_product_type_count( 'product_variation' ),
        );

        // Allow Plugin/Theme authors to add support for additional Product Types.
        $output = apply_filters( 'woo_ce_get_product_types_output', $output );

        asort( $output );
        return $output;
    }
}

function woo_ce_get_product_type_count( $post_type = 'product', $args = array() ) {

    $defaults    = array(
        'post_type'      => $post_type,
        'posts_per_page' => 1,
        'fields'         => 'ids',
    );
    $args        = wp_parse_args( $args, $defaults );
    $product_ids = new WP_Query( $args );
    $size        = $product_ids->found_posts;

    // Allow Plugin/Theme authors to override Product Type counts as needed.
    $size = apply_filters( 'woo_ce_get_product_type_count', $size, $post_type );

    return $size;
}

// Returns a list of WooCommerce Product Attributes to export process.
function woo_ce_get_product_attributes( $slice = '' ) {

    if ( apply_filters( 'woo_ce_enable_product_attributes', true ) == false ) {
        return false;
    }

    global $export;

    if ( false === ( $output = get_transient( 'wc_attribute_taxonomies' ) ) ) {
        $output = ( function_exists( 'wc_get_attribute_taxonomies' ) ? wc_get_attribute_taxonomies() : array() );
        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'wc_get_attribute_taxonomies(): ' . ( time() - $export->start_time ) ) );
        }
    }

    // Fallback when wc_get_attribute_taxonomies() fails.
    if ( empty( $output ) ) {

        global $wpdb;

        $output = array();
        // Check if there are any records in wp_woocommerce_attribute_taxonomies.
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'woocommerce_attribute_taxonomies' ) ) ) {
            $attributes = $wpdb->get_results( 'SELECT * FROM `' . esc_sql( $wpdb->prefix ) . 'woocommerce_attribute_taxonomies`' );
            $wpdb->flush();
            if ( WOO_CE_LOGGING ) {
                if ( isset( $export->start_time ) ) {
                    woo_ce_error_log( sprintf( 'Debug: %s', 'attributes_sql: ' . ( time() - $export->start_time ) ) );
                }
            }
            unset( $attributes );
        }
    }

    // Splice in our custom Attributes.
    $custom_attributes = woo_ce_get_option( 'custom_attributes', '' );
    if ( empty( $output ) ) {
        $output = array();
    }
    if ( ! empty( $custom_attributes ) ) {
        foreach ( $custom_attributes as $custom_attribute ) {
            if ( ! empty( $custom_attribute ) ) {
                $output[] = (object) array(
                    'attribute_id'      => 0,
                    'attribute_name'    => remove_accents( sanitize_key( $custom_attribute ) ),
                    'attribute_label'   => $custom_attribute,
                    'attribute_type'    => 'select',
                    'attribute_orderby' => 'menu_order',
                    'attribute_public'  => 0,
                );
            }
        }
        unset( $custom_attributes, $custom_attribute );
    }

    // Trim back the response.
    if ( ! empty( $output ) && ! empty( $slice ) ) {
        $attributes = $output;
        $output     = array();
        foreach ( $attributes as $attribute ) {
            $output[] = ( isset( $attribute->$slice ) ? $attribute->$slice : '' );
        }
    }

    return $output;
}

function woo_ce_get_product_assoc_brands( $product_id = 0, $parent_id = 0 ) {

    global $export;

    $output = '';
    // Return Product Brands of Parent if this is a Variation.
    if ( $parent_id ) {
        $product_id = $parent_id;
    }
    // Read brands from every registered brand taxonomy, not just the one
    // woo_ce_brand_term_taxonomy resolves to, so a product matched on a second
    // Brands plugin's taxonomy still shows its brand names.
    // One query for all brand taxonomies, not one for each taxonomy.
    $brands          = array();
    $brand_taxonomies = woo_ce_get_brand_term_taxonomies();
    if ( $product_id && ! empty( $brand_taxonomies ) ) {
        $brands = wp_get_object_terms( $product_id, $brand_taxonomies );
        if ( is_wp_error( $brands ) ) {
            $brands = array();
        }
    }
    if ( ! empty( $brands ) ) {
        $size = count( $brands );
        for ( $i = 0; $i < $size; $i++ ) {
            $term_taxonomy = $brands[ $i ]->taxonomy;
            if ( $brands[ $i ]->parent == '0' ) {
                $output .= $brands[ $i ]->name . $export->category_separator;
            } else {
                // Check if Parent -> Child.
                $parent_brand = get_term( $brands[ $i ]->parent, $term_taxonomy );
                // Check if Parent -> Child -> Subchild.
                if ( $parent_brand->parent == '0' ) {
                    $output .= $parent_brand->name . '>' . $brands[ $i ]->name . $export->category_separator;
                    $output  = str_replace( $parent_brand->name . $export->category_separator, '', $output );
                } else {
                    $root_brand = get_term( $parent_brand->parent, $term_taxonomy );
                    $output    .= $root_brand->name . '>' . $parent_brand->name . '>' . $brands[ $i ]->name . $export->category_separator;
                    $output     = str_replace(
                        array(
                            $root_brand->name . '>' . $parent_brand->name . $export->category_separator,
                            $parent_brand->name . $export->category_separator,
                        ),
                        '',
                        $output
                    );
                }
                unset( $root_brand, $parent_brand );
            }
        }
        $output = substr( $output, 0, -1 );
    }

    return $output;
}

function woo_ce_get_product_assoc_per_product_shipping_rules( $product_id ) {

    // Per-Product Shipping - http://www.woothemes.com/products/per-product-shipping/
    if ( woo_ce_detect_export_plugin( 'per_product_shipping' ) ) {

        global $wpdb, $export;

        $output         = array();
        $shipping_rules = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT rule_country as `country`,
                    rule_state as `state`,
                    rule_postcode as `postcode`,
                    rule_cost as `cost`,
                    rule_item_cost as `item_cost`,
                    rule_order as `order`
                FROM `' . $wpdb->prefix . 'woocommerce_per_product_shipping_rules` WHERE `product_id` = %d',
                $product_id
            )
        );
        $wpdb->flush();
        if ( ! empty( $shipping_rules ) ) {

            $output = array(
                'country'   => '',
                'state'     => '',
                'postcode'  => '',
                'cost'      => '',
                'item_cost' => '',
                'order'     => '',
            );

            foreach ( $shipping_rules as $shipping_rule ) {
                $output['country']   .= ( ! empty( $shipping_rule->country ) ? $shipping_rule->country : '*' ) . $export->category_separator;
                $output['state']     .= ( ! empty( $shipping_rule->state ) ? $shipping_rule->state : '*' ) . $export->category_separator;
                $output['postcode']  .= ( ! empty( $shipping_rule->postcode ) ? $shipping_rule->postcode : '*' ) . $export->category_separator;
                $output['cost']      .= ( ! empty( $shipping_rule->cost ) ? $shipping_rule->cost : '*' ) . $export->category_separator;
                $output['item_cost'] .= ( ! empty( $shipping_rule->item_cost ) ? $shipping_rule->item_cost : '*' ) . $export->category_separator;
                $output['order']     .= ( ! empty( $shipping_rule->order ) ? $shipping_rule->order : '0' ) . $export->category_separator;
            }

            $output['country']   = substr( $output['country'], 0, -1 );
            $output['state']     = substr( $output['state'], 0, -1 );
            $output['postcode']  = substr( $output['postcode'], 0, -1 );
            $output['cost']      = substr( $output['cost'], 0, -1 );
            $output['item_cost'] = substr( $output['item_cost'], 0, -1 );
            $output['order']     = substr( $output['order'], 0, -1 );
        }
        return $output;
    }
}

function woo_ce_format_product_sale_price_dates( $sale_date = '' ) {

    $output = $sale_date;
    if ( $sale_date ) {
        $output = woo_ce_format_date( date( 'Y-m-d H:i:s', $sale_date ) );
    }

    return $output;
}

function woo_ce_unique_product_gallery_fields( $fields = array() ) {

    $max_size = woo_ce_get_option( 'max_product_gallery', 3 );
    if ( ! empty( $fields ) ) {
        // Tack on a extra digit to max_size so we get the correct number of columns.
        ++$max_size;
        for ( $i = 1; $i < $max_size; $i++ ) {
            if ( isset( $fields['product_gallery'] ) ) {
                $fields[ sprintf( 'product_gallery_%d', $i ) ] = 'on';
            }
        }
    }

    return $fields;
}

function woo_ce_unique_product_gallery_columns( $columns = array(), $fields = array() ) {

    $max_size = woo_ce_get_option( 'max_product_gallery', 3 );
    if ( ! empty( $columns ) ) {
        // Tack on a extra digit to max_size so we get the correct number of columns.
        ++$max_size;
        for ( $i = 1; $i < $max_size; $i++ ) {
            if ( isset( $fields[ sprintf( 'product_gallery_%d', $i ) ] ) ) {
                $columns[] = sprintf( apply_filters( 'woo_ce_unique_product_gallery_column', __( '%1$s #%2$d', 'woocommerce-exporter' ) ), woo_ce_get_product_field( 'product_gallery' ), $i );
            }
        }
    }

    return $columns;
}

// Make GTIN, UPC, EAN, or ISBN searchable in the admin interface
function woo_ce_product_search_by_global_unique_id( $search_fields ) {
    $search_fields[] = '_global_unique_id';
    return $search_fields;
}
add_filter( 'woocommerce_product_search_fields', 'woo_ce_product_search_by_global_unique_id' );
