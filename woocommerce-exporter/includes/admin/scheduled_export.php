<?php
/**
 * Displays a banner with a link to return to the Scheduled Exports page.
 *
 * @param WP_Post $post The Post object.
 */
function woo_ce_scheduled_export_banner( $post ) {

    // Check the Post object exists.
    if ( isset( $post->post_type ) == false ) {
        return;
    }

    // Limit to the scheduled_export Post Type.
    $post_type = 'scheduled_export';
    if ( $post->post_type !== $post_type ) {
        return;
    }

    if ( apply_filters( 'woo_ce_scheduled_export_banner_save_prompt', true ) ) {
        echo wp_kses_post(
            '<a href="' . esc_url(
                add_query_arg(
                    array(
                        'page' => 'woo_ce',
                        'tab'  => 'scheduled_export',
                    ),
                    'admin.php'
                )
            ) . '" id="return-button" class="button confirm-button" data-confirm="' . __( 'The changes you made will be lost if you navigate away from this page before saving.', 'woocommerce-exporter' ) . '" data-validate="yes">' . __( 'Return to Scheduled Exports', 'woocommerce-exporter' ) . '</a>'
        );
    } else {
        echo wp_kses_post(
            '<a href="' . esc_url(
                add_query_arg(
                    array(
                        'page' => 'woo_ce',
                        'tab'  => 'scheduled_export',
                    ),
                    'admin.php'
                )
            ) . '" id="return-button" class="button">' . __( 'Return to Scheduled Exports', 'woocommerce-exporter' ) . '</a>'
        );
    }
}

/**
 * Displays the meta box for scheduled export filters.
 *
 * This function is responsible for rendering the meta box that contains the options for scheduled export filters.
 * It checks if the "Enable scheduled export" option is disabled and displays a notice if it is.
 * It also adds various actions for different sections of the meta box, such as general options, filters, method, and scheduling.
 * Additionally, it allows plugin/theme authors to add custom fields to the Export Filters meta box.
 *
 * @global WP_Post $post The current post object.
 */
function woo_ce_scheduled_export_filters_meta_box() {

    global $post;

    $post_ID = ( $post ? $post->ID : 0 );

    woo_ce_load_export_types();

    // General.
    add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_export_type' );
    add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_export_format' );
    add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_export_method' );
    add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_export_fields' );
    add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_excel_formulas' );
    add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_header_formatting' );
    add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_grouped_product_formatting' );
    add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_product_image_formatting' );
    add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_order' );
    add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_volume_limit_offset' );

    // Filters.
    add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_product' );
    add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_category' );
    add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_tag' );
    add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_brand' );
    add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_order' );
    add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_user' );
    add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_review' );
    add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_coupon' );
    add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_subscription' );
    add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_commission' );
    add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_shipping_class' );

    // Method.
    add_action( 'woo_ce_before_scheduled_export_method_options', 'woo_ce_scheduled_export_method_archive' );
    add_action( 'woo_ce_before_scheduled_export_method_options', 'woo_ce_scheduled_export_method_save' );
    add_action( 'woo_ce_before_scheduled_export_method_options', 'woo_ce_scheduled_export_method_email' );
    add_action( 'woo_ce_before_scheduled_export_method_options', 'woo_ce_scheduled_export_method_post' );
    add_action( 'woo_ce_before_scheduled_export_method_options', 'woo_ce_scheduled_export_method_ftp' );
    if ( apply_filters( 'woo_ce_scheduled_export_enable_google_sheets', false ) ) {
        add_action( 'woo_ce_before_scheduled_export_method_options', 'woo_ce_scheduled_export_method_google_sheets' );
    }
    if ( apply_filters( 'woo_ce_scheduled_export_enable_google_sheets_legacy', false ) ) {
        add_action( 'woo_ce_before_scheduled_export_method_options', 'woo_ce_scheduled_export_method_google_sheets_legacy' );
    }

    // Scheduling.
    add_action( 'woo_ce_before_scheduled_export_frequency_options', 'woo_ce_scheduled_export_frequency_schedule' );
    add_action( 'woo_ce_before_scheduled_export_frequency_options', 'woo_ce_scheduled_export_frequency_commence' );
    add_action( 'woo_ce_before_scheduled_export_frequency_options', 'woo_ce_scheduled_export_frequency_days' );

    // Allow Plugin/Theme authors to add custom fields to the Export Filters meta box.
    do_action( 'woo_ce_extend_scheduled_export_options', $post_ID );

    $troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

?>
    <div id="scheduled_export_options" class="panel-wrap scheduled_export_data">
        <div class="wc-tabs-back"></div>
        <ul class="coupon_data_tabs wc-tabs" style="display:none;">
            <?php
            $coupon_data_tabs = apply_filters(
                'woo_ce_scheduled_export_data_tabs',
                array(
					'general'    => array(
						'label'  => __( 'General', 'woocommerce' ),
						'target' => 'general_coupon_data',
						'class'  => 'general_coupon_data',
					),
					'filters'    => array(
						'label'  => __( 'Filters', 'woocommerce' ),
						'target' => 'usage_restriction_coupon_data',
						'class'  => '',
					),
					'method'     => array(
						'label'  => __( 'Method', 'woocommerce' ),
						'target' => 'method_coupon_data',
						'class'  => '',
					),
					'scheduling' => array(
						'label'  => __( 'Scheduling', 'woocommerce' ),
						'target' => 'scheduling_coupon_data',
						'class'  => '',
					),
                )
            );

            foreach ( $coupon_data_tabs as $key => $tab ) {
            ?>
                <li class="<?php echo esc_attr( $key ); ?>_options <?php echo esc_attr( $key ); ?>_tab <?php echo esc_attr( implode( ' ', (array) $tab['class'] ) ); ?>">
                    <a href="#<?php echo esc_attr( $tab['target'] ); ?>"><?php echo esc_html( $tab['label'] ); ?></a>
                </li>
            <?php
            }
            ?>
        </ul>
        <?php do_action( 'woo_ce_before_scheduled_export_options', $post_ID ); ?>
        <div id="general_coupon_data" class="panel woocommerce_options_panel export_general_options">
            <?php do_action( 'woo_ce_before_scheduled_export_general_options', $post_ID ); ?>
            <?php do_action( 'woo_ce_after_scheduled_export_general_options', $post_ID ); ?>
        </div>
        <!-- #general_coupon_data -->

        <div id="usage_restriction_coupon_data" class="panel woocommerce_options_panel export_type_options">
            <?php do_action( 'woo_ce_before_scheduled_export_type_options', $post_ID ); ?>
            <div class="export-options customer-options product_vendor-options ticket-options">
                <p><?php esc_html_e( 'No filter options are available for this export type.', 'woocommerce-exporter' ); ?></p>
            </div>
            <?php do_action( 'woo_ce_after_scheduled_export_type_options', $post_ID ); ?>
        </div>
        <!-- #usage_restriction_coupon_data -->

        <div id="method_coupon_data" class="panel woocommerce_options_panel export_method_options">
            <?php do_action( 'woo_ce_before_scheduled_export_method_options', $post_ID ); ?>
            <div class="export-options">
                <p><?php esc_html_e( 'No export method options are available for this export method.', 'woocommerce-exporter' ); ?></p>
            </div>
            <?php do_action( 'woo_ce_after_scheduled_export_method_options', $post_ID ); ?>
        </div>
        <!-- #method_coupon_data -->

        <div id="scheduling_coupon_data" class="panel woocommerce_options_panel export_frequency_options">
            <?php do_action( 'woo_ce_before_scheduled_export_frequency_options', $post_ID ); ?>
            <?php do_action( 'woo_ce_after_scheduled_export_frequency_options', $post_ID ); ?>
        </div>
        <!-- #scheduling_coupon_data -->

        <?php do_action( 'woo_ce_after_scheduled_export_options', $post_ID ); ?>
        <div class="clear"></div>
    </div>
    <!-- #scheduled_export_options -->
<?php
    wp_nonce_field( 'scheduled_export', 'woo_ce_export' );
}

/**
 * Extends the scheduled export options by adding filters for products, categories, tags, brands, and orders.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_extend_scheduled_export_options( $post_ID = 0 ) {

    // Product.
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_orderby' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_orderby' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_multi_level_sorting' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_multi_level_sorting' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_product_category' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_product_category' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_product_tag' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_product_tag' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_product_status' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_product_status' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_product_type' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_product_type' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_product' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_product' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_user_role' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_user_role' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_shipping_class' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_shipping_class' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_date_published' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_date_published' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_date_modified' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_date_modified' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_stock_status' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_stock_status' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_stock_quantity' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_stock_quantity' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_featured' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_featured' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_product_brand' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_product_brand' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_language' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_language' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_product_vendor' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_product_vendor' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_product_filter_by_product_meta' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_product_meta' );
    }

    // Category.
    if ( function_exists( 'woo_ce_scheduled_export_category_filter_orderby' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_category', 'woo_ce_scheduled_export_category_filter_orderby' );
    }

    // Tag.
    if ( function_exists( 'woo_ce_scheduled_export_tag_filter_orderby' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_tag', 'woo_ce_scheduled_export_tag_filter_orderby' );
    }

    // Brand.
    if ( function_exists( 'woo_ce_scheduled_export_brand_filter_orderby' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_brand', 'woo_ce_scheduled_export_brand_filter_orderby' );
    }

    // Order.
    if ( function_exists( 'woo_ce_extend_order_sorting' ) ) {
        add_action( 'woo_ce_order_sorting', 'woo_ce_extend_order_sorting' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_orderby' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_orderby' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_order_status' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_order_status' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_billing_country' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_billing_country' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_shipping_country' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_shipping_country' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_order_date' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_order_date' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_order_modified_date' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_order_modified_date' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_product' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_product' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_product_category' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_product_category' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_product_tag' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_product_tag' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_product_brand' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_product_brand' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_user_role' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_user_role' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_customer' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_customer' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_coupon' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_coupon' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_payment_gateway' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_payment_gateway' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_shipping_method' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_shipping_method' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_items_formatting' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_items_formatting' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_max_order_items' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_max_order_items' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_digital_products' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_digital_products' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_export_order_notes' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_export_order_notes' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_order_type' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_order_type' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_order_item_types' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_order_item_types' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_order_meta' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_order_meta' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_booking_start_date' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_booking_start_date' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_order_filter_by_delivery_date' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_delivery_date' );
    }

    // User.
    if ( function_exists( 'woo_ce_scheduled_export_user_filter_orderby' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_user', 'woo_ce_scheduled_export_user_filter_orderby' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_user_filter_by_date_registered' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_user', 'woo_ce_scheduled_export_user_filter_by_date_registered' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_user_filter_by_date_last_updated' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_user', 'woo_ce_scheduled_export_user_filter_by_date_last_updated' );
    }

    // Review.
    if ( function_exists( 'woo_ce_scheduled_export_review_filter_orderby' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_review', 'woo_ce_scheduled_export_review_filter_orderby' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_review_filter_by_review_date' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_review', 'woo_ce_scheduled_export_review_filter_by_review_date' );
    }

    // Coupon.
    if ( function_exists( 'woo_ce_scheduled_export_coupon_filter_orderby' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_coupon', 'woo_ce_scheduled_export_coupon_filter_orderby' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_coupon_filter_by_discount_type' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_coupon', 'woo_ce_scheduled_export_coupon_filter_by_discount_type' );
    }

    // Subscription.
    if ( function_exists( 'woo_ce_scheduled_export_subscription_filter_orderby' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_subscription', 'woo_ce_scheduled_export_subscription_filter_orderby' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_subscription_filter_by_subscription_date' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_subscription', 'woo_ce_scheduled_export_subscription_filter_by_subscription_date' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_subscription_filter_by_subscription_status' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_subscription', 'woo_ce_scheduled_export_subscription_filter_by_subscription_status' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_subscription_filter_by_subscription_product' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_subscription', 'woo_ce_scheduled_export_subscription_filter_by_subscription_product' );
    }
    if ( function_exists( 'woo_ce_scheduled_export_subscription_items_formatting' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_subscription', 'woo_ce_scheduled_export_subscription_items_formatting' );
    }

    // Commission.
    if ( function_exists( 'woo_ce_scheduled_export_commission_filter_orderby' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_commission', 'woo_ce_scheduled_export_commission_filter_orderby' );
    }

    // Shipping Class.
    if ( function_exists( 'woo_ce_scheduled_export_shipping_class_filter_orderby' ) ) {
        add_action( 'woo_ce_scheduled_export_filters_shipping_class', 'woo_ce_scheduled_export_shipping_class_filter_orderby' );
    }
}
add_action( 'woo_ce_extend_scheduled_export_options', 'woo_ce_extend_scheduled_export_options' );

/**
 * Displays the export type field in the scheduled export settings.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_general_export_type( $post_ID = 0 ) {

    $export_type  = get_post_meta( $post_ID, '_export_type', true );
    $export_types = woo_ce_get_export_types();

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field ">
            <label for="export_type"><?php esc_html_e( 'Export type', 'woocommerce-exporter' ); ?> </label>
            <?php if ( ! empty( $export_types ) ) { ?>
                <select id="export_type" name="export_type" class="select short">
                    <?php foreach ( $export_types as $key => $type ) { ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $export_type, $key ); ?>><?php echo esc_html( $type ); ?></option>
                    <?php } ?>
                </select>
                <img class="help_tip" data-tip="<?php esc_attr_e( 'Select the export type you want to export.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
            <?php } else { ?>
                <?php esc_html_e( 'No export types were found.', 'woocommerce-exporter' ); ?>
            <?php } ?>
        </p>
    </div>
    <!-- .options_group -->

<?php
    ob_end_flush();
}

/**
 * Renders the export format options for the scheduled export settings.
 *
 * This function generates a dropdown select field with export format options
 * based on the available export formats retrieved from the `woo_ce_get_export_formats()` function.
 * The selected export format is determined by the value stored in the `_export_format` meta field of the given post.
 *
 * @param int $post_ID The ID of the post for which the export format options are being rendered.
 */
function woo_ce_scheduled_export_general_export_format( $post_ID = 0 ) {

    $export_formats = woo_ce_get_export_formats();
    $type           = get_post_meta( $post_ID, '_export_format', true );

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field ">
            <label for="export_format"><?php esc_html_e( 'Export format', 'woocommerce-exporter' ); ?> </label>
            <?php if ( ! empty( $export_formats ) ) { ?>
                <select id="export_format" name="export_format" class="select short">
                    <?php foreach ( $export_formats as $key => $export_format ) { ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $export_format['title'] ); ?>
                                                    <?php
                        if ( ! empty( $export_format['description'] ) ) {
?>
- <?php echo esc_html( $export_format['description'] ); ?><?php } ?></option>
                    <?php } ?>
                </select>
            <?php } else { ?>
                <?php esc_html_e( 'No export formats were found.', 'woocommerce-exporter' ); ?>
            <?php } ?>
            <img class="help_tip" data-tip="<?php esc_attr_e( 'Adjust the export format to generate different export file formats. Default is CSV.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        </p>
    </div>
    <!-- .options_group -->

<?php
    ob_end_flush();
}

/**
 * Renders the HTML for the export method field in the scheduled export settings.
 *
 * @param int $post_ID The ID of the post being edited.
 */
function woo_ce_scheduled_export_general_export_method( $post_ID = 0 ) {

    $export_method = get_post_meta( $post_ID, '_export_method', true );

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field ">
            <label for="export_method"><?php esc_html_e( 'Export method', 'woocommerce-exporter' ); ?> </label>
            <select id="export_method" name="export_method" class="select short">
                <option value="archive" <?php selected( $export_method, 'archive' ); ?>><?php echo esc_html( woo_ce_format_export_method( 'archive' ) ); ?></option>
                <option value="save" <?php selected( $export_method, 'save' ); ?>><?php echo esc_html( woo_ce_format_export_method( 'save' ) ); ?></option>
                <option value="email" <?php selected( $export_method, 'email' ); ?>><?php echo esc_html( woo_ce_format_export_method( 'email' ) ); ?></option>
                <option value="post" <?php selected( $export_method, 'post' ); ?>><?php echo esc_html( woo_ce_format_export_method( 'post' ) ); ?></option>
                <option value="ftp" <?php selected( $export_method, 'ftp' ); ?>><?php echo esc_html( woo_ce_format_export_method( 'ftp' ) ); ?></option>
                <?php if ( apply_filters( 'woo_ce_scheduled_export_enable_google_sheets', false ) ) { ?>
                    <option value="google_sheets" <?php selected( $export_method, 'google_sheets' ); ?>><?php echo esc_html( woo_ce_format_export_method( 'google_sheets' ) ); ?></option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_attr_e( 'Choose what Store Exporter Deluxe does with the generated export. Default is to archive the export to the WordPress Media for archival purposes.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        </p>
    </div>
    <!-- .options_group -->

<?php
    ob_end_flush();
}

/**
 * Renders the export fields options for the scheduled export settings.
 *
 * @param int $post_ID The ID of the post being edited.
 */
function woo_ce_scheduled_export_general_export_fields( $post_ID = 0 ) {

    $export_fields    = get_post_meta( $post_ID, '_export_fields', true );
    $args             = array(
        'post_status' => 'publish',
    );
    $export_templates = woo_ce_get_export_templates( $args );
    $export_template  = get_post_meta( $post_ID, '_export_template', true );

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="export_fields"><?php esc_html_e( 'Export fields', 'woocommerce-exporter' ); ?></label>
            <input type="radio" name="export_fields" value="all" <?php checked( in_array( $export_fields, array( false, 'all' ) ), true ); ?> />&nbsp;<?php esc_html_e( 'Include all Export Fields for the requested Export Type', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="export_fields" value="template" <?php checked( $export_fields, 'template' ); ?><?php disabled( empty( $export_templates ), true ); ?> />&nbsp;<?php esc_html_e( 'Use the saved Export Fields preference from the following Export Template for the requested Export Type', 'woocommerce-exporter' ); ?><br />
            <select id="export_template" name="export_template" <?php disabled( empty( $export_templates ), true ); ?> class="select short">
                <?php if ( ! empty( $export_templates ) ) { ?>
                    <?php foreach ( $export_templates as $template ) { ?>
                        <option value="<?php echo esc_attr( $template ); ?>" <?php selected( $export_template, $template ); ?>><?php echo esc_html( woo_ce_format_post_title( get_the_title( $template ) ) ); ?></option>
                    <?php } ?>
                <?php } else { ?>
                    <option><?php esc_html_e( 'Choose a Export Template...', 'woocommerce-exporter' ); ?></option>
                <?php } ?>
            </select>
            <br class="clear" />
            <input type="radio" name="export_fields" value="saved" <?php checked( $export_fields, 'saved' ); ?> />&nbsp;<?php esc_html_e( 'Use the saved Export Fields preference set on the Quick Export screen for the requested Export Type', 'woocommerce-exporter' ); ?>
        </p>
        <p class="description"><?php esc_html_e( 'Control whether all known export fields are included, field preferences from a specific Export Template or only checked fields from the Export Fields section on the Quick Export screen. Default is to include all export fields.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- .options_group -->

<?php
    ob_end_flush();
}

/**
 * Renders the form for selecting whether to allow Excel formulas in export files.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_general_excel_formulas( $post_ID = 0 ) {

    $excel_formulas = get_post_meta( $post_ID, '_excel_formulas', true );
    $excel_formulas = absint( $excel_formulas );

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="excel_formulas"><?php esc_html_e( 'Excel formulas', 'woocommerce-exporter' ); ?></label>
            <input type="radio" name="excel_formulas" value="1" <?php checked( $excel_formulas, 1 ); ?> />&nbsp;<?php esc_html_e( 'Allow Excel formulas', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="excel_formulas" value="0" <?php checked( $excel_formulas, 0 ); ?> />&nbsp;<?php esc_html_e( 'Do not allow Excel formulas', 'woocommerce-exporter' ); ?><br />
        </p>
        <p class="description"><?php esc_html_e( 'Choose whether Excel formulas are allowed in export files. By default Excel formulas are stripped from all export files.', 'woocommerce-exporter' ); ?></p>
    </div>

<?php
    ob_end_flush();
}

/**
 * Renders the header formatting options for the scheduled export settings.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_general_header_formatting( $post_ID = 0 ) {

    $header_formatting = get_post_meta( $post_ID, '_header_formatting', true );

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="header_formatting"><?php esc_html_e( 'Header formatting', 'woocommerce-exporter' ); ?></label>
            <input type="radio" name="header_formatting" value="1" <?php checked( in_array( $header_formatting, array( false, '1' ) ), true ); ?> />&nbsp;<?php esc_html_e( 'Include export field column headers', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="header_formatting" value="0" <?php checked( $header_formatting, '0' ); ?> />&nbsp;<?php esc_html_e( 'Do not include export field column headers', 'woocommerce-exporter' ); ?><br />
        </p>
        <p class="description"><?php esc_html_e( 'Choose the header format that suits your spreadsheet software (e.g. Excel, OpenOffice, etc.). This rule applies to CSV, TSV, XLS and XLSX export types.', 'woocommerce-exporter' ); ?></p>
    </div>

<?php
    ob_end_flush();
}

/**
 * Renders the grouped product formatting options in the admin panel.
 *
 * @param int $post_ID The ID of the post being edited.
 */
function woo_ce_scheduled_export_general_grouped_product_formatting( $post_ID = 0 ) {

    $grouped_formatting = get_post_meta( $post_ID, '_grouped_formatting', true );

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="header_formatting"><?php esc_html_e( 'Grouped Product formatting', 'woocommerce-exporter' ); ?></label>
            <input type="radio" name="product_grouped_formatting" value="0" <?php checked( in_array( $grouped_formatting, array( false, '0' ) ), true ); ?> />&nbsp;<?php esc_html_e( 'Export Grouped Products as Product ID', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="product_grouped_formatting" value="1" <?php checked( $grouped_formatting, '1' ); ?> />&nbsp;<?php esc_html_e( 'Export Grouped Products as Product SKU', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="product_grouped_formatting" value="2" <?php checked( $grouped_formatting, '2' ); ?> />&nbsp;<?php esc_html_e( 'Export Grouped Products as Product Name', 'woocommerce-exporter' ); ?><br />
        </p>
        <p class="description"><?php esc_html_e( 'Choose the header format that suits your spreadsheet software (e.g. Excel, OpenOffice, etc.). This rule applies to CSV, TSV, XLS and XLSX export types.', 'woocommerce-exporter' ); ?></p>
    </div>
<?php
    ob_end_flush();
}

/**
 * Generates the HTML for the general order field in the scheduled export settings.
 *
 * @param int $post_ID The ID of the post being edited.
 */
function woo_ce_scheduled_export_general_order( $post_ID = 0 ) {

    $order = get_post_meta( $post_ID, '_order', true );
    // Default to Ascending.
    if ( $order == false ) {
        $order = 'ASC';
    }

    ob_start();
    ?>
    <div class="options_group">

        <p class="form-field discount_type_field">
            <label for="order"><?php esc_html_e( 'Order', 'woocommerce-exporter' ); ?></label>
            <select id="order" name="order">
                <option value="ASC" <?php selected( 'ASC', $order ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
                <option value="DESC" <?php selected( 'DESC', $order ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
            </select>
            <img class="help_tip" data-tip="<?php esc_attr_e( 'Select the sorting of records within the exported file.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        </p>

    </div>
    <!-- .options_group -->

<?php
    ob_end_flush();
}

/**
 * Renders the HTML form fields for the general volume limit and offset settings in the scheduled export page.
 *
 * @param int $post_ID The ID of the post being edited.
 */
function woo_ce_scheduled_export_general_volume_limit_offset( $post_ID = 0 ) {

    $delimiter    = get_post_meta( $post_ID, '_delimiter', true );
    $limit_volume = get_post_meta( $post_ID, '_limit_volume', true );
    $offset       = get_post_meta( $post_ID, '_offset', true );

    ob_start();
    ?>
    <div class="options_group">

        <p class="form-field discount_type_field">
            <label for="delimiter"><?php esc_html_e( 'Delimiter', 'woocommerce-exporter' ); ?></label>
            <input type="text" size="3" id="delimiter" name="delimiter" value="<?php echo esc_attr( $delimiter ); ?>" maxlength="5" class="text sized" />
        </p>
        <p class="form-field discount_type_field">
            <label for="limit_volume"><?php esc_html_e( 'Limit volume', 'woocommerce-exporter' ); ?></label>
            <input type="text" size="3" id="limit_volume" name="limit_volume" value="<?php echo esc_attr( $limit_volume ); ?>" size="5" class="text sized" title="<?php esc_attr_e( 'Limit volume', 'woocommerce-exporter' ); ?>" />
            <img class="help_tip" data-tip="<?php esc_attr_e( 'Limit the number of records to be exported. By default this is not used and is left empty.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        </p>
        <p class="form-field discount_type_field">
            <label for="offset"><?php esc_html_e( 'Volume offset', 'woocommerce-exporter' ); ?></label>
            <input type="text" size="3" id="offset" name="offset" value="<?php echo esc_attr( $offset ); ?>" size="5" class="text sized" title="<?php esc_attr_e( 'Volume offset', 'woocommerce-exporter' ); ?>" />
            <img class="help_tip" data-tip="<?php esc_attr_e( 'Set the number of records to be skipped in this export. By default this is not used and is left empty.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        </p>
        <p class="description"><?php esc_html_e( 'Having difficulty downloading your exports in one go? Use our batch export function - Limit Volume and Volume Offset - to create smaller exports.', 'woocommerce-exporter' ); ?></p>

    </div>
    <!-- .options_group -->

<?php
    ob_end_flush();
}

/**
 * Sets the product image and gallery formatting options for scheduled exports.
 *
 * @param int $post_ID The ID of the post being processed.
 */
function woo_ce_scheduled_export_general_product_image_formatting( $post_ID = 0 ) {

    $product_image_formatting = get_post_meta( $post_ID, '_product_image_formatting', true );
    $gallery_formatting       = get_post_meta( $post_ID, '_gallery_formatting', true );

    if ( $product_image_formatting == false ) {
        $product_image_formatting = 0;
    }
    if ( $gallery_formatting == false ) {
        $gallery_formatting = 0;
    }

    ob_start();
    ?>
    <div class="options_group">

        <p class="form-field discount_type_field">
            <label for="export_fields"><?php esc_html_e( 'Product image formatting', 'woocommerce-exporter' ); ?></label>
            <input type="radio" name="product_image_formatting" value="0" <?php checked( $product_image_formatting, 0 ); ?> />&nbsp;<?php esc_html_e( 'Export Product Image as Attachment ID', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="product_image_formatting" value="1" <?php checked( $product_image_formatting, 1 ); ?> />&nbsp;<?php esc_html_e( 'Export Product Image as Image URL', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="product_image_formatting" value="2" <?php checked( $product_image_formatting, 2 ); ?> />&nbsp;<?php esc_html_e( 'Export Product Image as Image filepath', 'woocommerce-exporter' ); ?>
        </p>
        <p class="description"><?php esc_html_e( 'Choose the featured image formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>

        <p class="form-field discount_type_field">
            <label for="export_fields"><?php esc_html_e( 'Product gallery formatting', 'woocommerce-exporter' ); ?></label>
            <input type="radio" name="gallery_formatting" value="0" <?php checked( $gallery_formatting, 0 ); ?> />&nbsp;<?php esc_html_e( 'Export Product Gallery as Attachment ID', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="gallery_formatting" value="1" <?php checked( $gallery_formatting, 1 ); ?> />&nbsp;<?php esc_html_e( 'Export Product Gallery as Image URL', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="gallery_formatting" value="2" <?php checked( $gallery_formatting, 2 ); ?> />&nbsp;<?php esc_html_e( 'Export Product Gallery as Image filepath', 'woocommerce-exporter' ); ?>
        </p>
        <p class="description"><?php esc_html_e( 'Choose the product gallery formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>

    </div>
    <!-- .options_group -->
<?php
    ob_end_flush();
}

/**
 * Save to WordPress Media
 *
 * This function is responsible for displaying the export options for the "archive" export method in the WooCommerce Store Exporter Deluxe plugin.
 * It retrieves the parent post ID from the post meta and displays a form field for the user to enter the parent post ID.
 * The function also includes a help tip image that provides additional information about the parent post ID field.
 *
 * @param int $post_ID The ID of the post being edited.
 */
function woo_ce_scheduled_export_method_archive( $post_ID = 0 ) {

    $parent_post_id = get_post_meta( $post_ID, '_method_archive_parent_post', true );

    ob_start();
    ?>
    <div class="export-options archive-options">

        <div class="options_group">
            <p class="form-field discount_type_field">
                <label for="archive_method_parent_post"><?php esc_html_e( 'Parent post', 'woocommerce-exporter' ); ?></label> <input type="text" id="archive_method_parent_post" name="archive_method_parent_post" size="5" class="short code" value="<?php echo esc_attr( $parent_post_id ); ?>" style="float:none;" />
                <img class="help_tip" data-tip="<?php esc_attr_e( 'The Parent Post ID that Scheduled Export files should be associated with.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
            </p>
        </div>
        <!-- .options_group -->

    </div>
    <!-- .archive-options -->

<?php
    ob_end_flush();
}

/**
 * Save method for scheduled export.
 *
 * This function is responsible for rendering the save options for a scheduled export.
 * It retrieves the save path and save filename from post meta and displays the input fields for them.
 * It also displays an option to append to an existing export file, if enabled.
 *
 * @param int $post_ID The ID of the scheduled export post.
 */
function woo_ce_scheduled_export_method_save( $post_ID = 0 ) {

    $save_path     = get_post_meta( $post_ID, '_method_save_path', true );
    $save_filename = get_post_meta( $post_ID, '_method_save_filename', true );

    $export_filename = woo_ce_get_option( 'export_filename', '' );

    ob_start();
    ?>
    <div class="export-options save-options">

        <div class="options_group">
            <p class="form-field discount_type_field">
                <label for="save_method_file_path"><?php esc_html_e( 'File path', 'woocommerce-exporter' ); ?></label> <code><?php echo esc_url( get_home_path() ); ?></code> <input type="text" id="save_method_file_path" name="save_method_path" size="25" class="short code" value="<?php echo esc_attr( sanitize_text_field( $save_path ) ); ?>" style="float:none;" />
                <img class="help_tip" data-tip="<?php esc_attr_e( 'Do not provide the filename within File path as it will be generated for you or rely on the fixed filename entered below.<br /><br />For file path example: <code>wp-content/uploads/exports/</code>', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
            </p>
        </div>
        <!-- .options_group -->

        <div class="options_group">
            <p class="form-field discount_type_field">
                <label for="save_method_filename"><?php esc_html_e( 'Fixed filename', 'woocommerce-exporter' ); ?></label> <input type="text" id="save_method_filename" name="save_method_filename" size="25" class="short code" value="<?php echo esc_attr( $save_filename ); ?>" placeholder="<?php echo esc_attr( $export_filename ); ?>" />
                <img class="help_tip" data-tip="<?php esc_attr_e( 'The export filename can be set within the Fixed filename field otherwise it defaults to the Export filename provided within General Settings above.<br /><br />Tags can be used: ', 'woocommerce-exporter' ); ?> <code>%dataset%</code>, <code>%date%</code>, <code>%time%</code>, <code>%year%</code>, <code>%month%</code>, <code>%day%</code>, <code>%hour%</code>, <code>%minute%</code>, <code>%random%</code>, <code>%store_name%</code>, <code>%order_id%</code>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
            </p>
        </div>
        <!-- .options_group -->

        <?php if ( apply_filters( 'woo_ce_scheduled_export_enable_save_method_append', false ) ) { ?>
            <div class="options_group">
                <p class="form-field discount_type_field">
                    <label for="save_method_append"><input type="checkbox" id="save_method_append" name="save_method_append" value="1" /> Append to existing export file</label>
                </p>
            </div>
        <?php } ?>

    </div>
    <!-- .save-options -->

<?php
    ob_end_flush();
}

/**
 * Sends the scheduled export as an email.
 *
 * This function is responsible for generating the HTML form for configuring the email options
 * for the scheduled export. It includes fields for setting the recipient(s), CC, BCC, subject,
 * heading, and contents of the email. It also allows the user to specify the filename of the
 * exported file and whether to encrypt the export file in a password-protected ZIP archive.
 *
 * @param int $post_ID The ID of the scheduled export post.
 */
function woo_ce_scheduled_export_method_email( $post_ID = 0 ) {

    $email_filename  = get_post_meta( $post_ID, '_method_email_filename', true );
    $export_filename = woo_ce_get_option( 'export_filename', '' );
    $encrypt_export  = get_post_meta( $post_ID, '_method_email_encrypt_export', true );
    $encrypt_export  = absint( $encrypt_export );

    ob_start();
    ?>
    <div class="export-options email-options">

        <?php
        echo '<div class="options_group">';
        woocommerce_wp_text_input(
            array(
                'id'          => '_method_email_to',
                'label'       => __( 'E-mail recipient(s)', 'woocommerce' ),
                'desc_tip'    => 'true',
                'description' => __( 'Set the recipient(s) of scheduled export e-mails, multiple recipients can be added using the <code><attr title="comma">,</attr></code> separator.<br /><br />Default is the Blog Administrator e-mail address set on the WordPress &raquo; Settings screen.', 'woocommerce-exporter' ),
                'placeholder' => 'big.bird@sesamestreet.org,oscar@sesamestreet.org',
            )
        );
        echo '</div>';
        echo '<div class="options_group">';
        woocommerce_wp_text_input(
            array(
                'id'          => '_method_email_cc',
                'label'       => __( 'E-mail CC', 'woocommerce' ),
                'desc_tip'    => 'true',
                'description' => __( 'Set the CC recipient(s) of scheduled export e-mails, multiple recipients can be added using the <code><attr title="comma">,</attr></code> separator.<br /><br />Default is empty.', 'woocommerce-exporter' ),
                'placeholder' => 'elmo@sesamestreet.org,mr.snuffleupagus@sesamestreet.org',
            )
        );
        echo '</div>';
        echo '<div class="options_group">';
        woocommerce_wp_text_input(
            array(
                'id'          => '_method_email_bcc',
                'label'       => __( 'E-mail BCC', 'woocommerce' ),
                'desc_tip'    => 'true',
                'description' => __( 'Set the BCC recipient(s) of scheduled export e-mails, multiple recipients can be added using the <code><attr title="comma">,</attr></code> separator.<br /><br />Default is empty.', 'woocommerce-exporter' ),
                'placeholder' => 'zoe@sesamestreet.org,cookie.monster@sesamestreet.org',
            )
        );
        echo '</div>';
        echo '<div class="options_group">';
        woocommerce_wp_text_input(
            array(
                'id'          => '_method_email_subject',
                'label'       => __( 'E-mail subject', 'woocommerce' ),
                'desc_tip'    => 'true',
                'description' => __( 'Set the subject of scheduled export e-mails.<br /><br />Tags can be used: <code>%1$store_name%</code>, <code>%2$export_type%</code>, <code>%3$export_filename%</code>', 'woocommerce-exporter' ),
                'placeholder' => __( 'Daily Product stock levels', 'woocommerce-exporter' ),
            )
        );
        echo '</div>';
        echo '<div class="options_group">';
        woocommerce_wp_text_input(
            array(
                'id'          => '_method_email_heading',
                'label'       => __( 'E-mail heading', 'woocommerce' ),
                'desc_tip'    => 'true',
                'description' => __( 'Set the header text of scheduled export e-mails.<br /><br />Tags can be used: <code>%1$store_name%</code>, <code>%2$export_type%</code>, <code>%3$export_filename%</code>', 'woocommerce-exporter' ),
                'placeholder' => __( 'Daily Product stock levels', 'woocommerce-exporter' ),
            )
        );
        echo '</div>';
        echo '<div class="options_group">';
        woocommerce_wp_textarea_input(
            array(
                'id'          => '_method_email_contents',
                'label'       => __( 'E-mail contents', 'woocommerce-exporter' ),
                'desc_tip'    => 'true',
                'description' => __( 'Set the e-mail contents of scheduled export e-mails.<br /><br />Tags can be used: <code>%1$store_name%</code>, <code>%2$export_type%</code>, <code>%3$export_filename%</code>', 'woocommerce-exporter' ),
                'placeholder' => __( 'Please find attached your export ready to review.', 'woocommerce-exporter' ),
                'style'       => apply_filters( 'woo_ce_scheduled_export_method_email_contents_style', 'height:10em;' ),
            )
        );
        echo '</div>';
        ?>
        <div class="options_group">
            <p class="form-field discount_type_field">
                <label for="email_method_filename"><?php esc_html_e( 'E-mail filename', 'woocommerce-exporter' ); ?></label> <input type="text" id="email_method_filename" name="_email_method_filename" size="25" class="short code" value="<?php echo esc_attr( $email_filename ); ?>" placeholder="<?php echo esc_attr( $export_filename ); ?>" />
                <img class="help_tip" data-tip="<?php esc_attr_e( 'The export filename can be set within the E-mail filename field otherwise it defaults to the Export filename provided within General Settings above.<br /><br />Tags can be used: ', 'woocommerce-exporter' ); ?> <code>%dataset%</code>, <code>%date%</code>, <code>%time%</code>, <code>%year%</code>, <code>%month%</code>, <code>%day%</code>, <code>%hour%</code>, <code>%minute%</code>, <code>%random%</code>, <code>%store_name%</code>, <code>%order_id%</code>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
            </p>
        </div>
        <!-- .options_group -->
        <div class="options_group">
            <p class="form-field discount_type_field">
                <label for="excel_formulas"><?php esc_html_e( 'Encrypt export', 'woocommerce-exporter' ); ?></label>
                <input type="radio" name="_method_email_encrypt_export" value="0" <?php checked( $encrypt_export, 0 ); ?> />&nbsp;<?php esc_html_e( 'Return original file type, do not encrypt export file', 'woocommerce-exporter' ); ?><br />
                <input type="radio" name="_method_email_encrypt_export" value="1" <?php checked( $encrypt_export, 1 ); ?> />&nbsp;<?php esc_html_e( 'Encrypt export file in a password protected ZIP archive', 'woocommerce-exporter' ); ?><br />
            </p>
            <?php
            woocommerce_wp_text_input(
                array(
                    'id'          => '_method_email_encrypt_password',
                    'label'       => __( 'Password', 'woocommerce-exporter' ),
                    'desc_tip'    => 'true',
                    'description' => __( 'Choose whether the export file should be encrypted by a password within a ZIP archive. By default the export file is returned in the selected file type and not protected in a password protected ZIP archive.', 'woocommerce-exporter' ),
                )
            );
            ?>
        </div>
        <!-- .options_group -->

    </div>
    <!-- .email-options -->

<?php
    ob_end_flush();
}

/**
 * FILEPATH: /c:/Users/digid/Local Sites/visser/app/public/wp-content/plugins/woocommerce-store-exporter-deluxe/includes/admin/scheduled_export.php
 *
 * Post to remote URL
 *
 * This function is responsible for displaying the export options for scheduled exports in the WooCommerce Store Exporter Deluxe plugin.
 * It outputs a form field for setting the remote POST URL for integration with web applications that accept a remote form POST.
 *
 * @param int $post_ID The ID of the post being edited. Default is 0.
 */
function woo_ce_scheduled_export_method_post( $post_ID = 0 ) {

    ob_start();
    ?>
    <div class="export-options post-options">

        <?php
        echo '<div class="options_group">';
        woocommerce_wp_text_input(
            array(
                'id'          => '_method_post_to',
                'label'       => __( 'Remote POST URL', 'woocommerce' ),
                'desc_tip'    => 'true',
                'description' => __( 'Set the remote POST address for scheduled exports, this is for integration with web applications that accept a remote form POST. Default is empty.', 'woocommerce-exporter' ),
            )
        );
        echo '</div>';
        ?>

    </div>
    <!-- .post-options -->
<?php
    ob_end_flush();
}

/**
 * Uploads the file to remote FTP/SFTP.
 *
 * @param int $post_ID The post ID.
 */
function woo_ce_scheduled_export_method_ftp( $post_ID = 0 ) {

    $ftp_host               = get_post_meta( $post_ID, '_method_ftp_host', true );
    $ftp_port               = get_post_meta( $post_ID, '_method_ftp_port', true );
    $ftp_protocol           = get_post_meta( $post_ID, '_method_ftp_protocol', true );
    $ftp_encryption         = get_post_meta( $post_ID, '_method_ftp_encryption', true );
    $ftp_authentication     = get_post_meta( $post_ID, '_method_ftp_authentication', true );
    $ftp_user               = get_post_meta( $post_ID, '_method_ftp_user', true );
    $ftp_pass               = get_post_meta( $post_ID, '_method_ftp_pass', true );
    $ftp_public_key         = get_post_meta( $post_ID, '_method_ftp_public_key', true );
    $ftp_private_key        = get_post_meta( $post_ID, '_method_ftp_private_key', true );
    $ftp_private_key_secret = get_post_meta( $post_ID, '_method_ftp_private_key_secret', true );
    $ftp_path               = get_post_meta( $post_ID, '_method_ftp_path', true );
    $ftp_filename           = get_post_meta( $post_ID, '_method_ftp_filename', true );
    $ftp_passive            = get_post_meta( $post_ID, '_method_ftp_passive', true );
    $ftp_mode               = get_post_meta( $post_ID, '_method_ftp_mode', true );
    $ftp_timeout            = get_post_meta( $post_ID, '_method_ftp_timeout', true );

    $export_filename = woo_ce_get_option( 'export_filename', '' );

    ob_start();
    ?>
    <div class="export-options ftp-options">

        <div class="options_group">
            <p class="form-field coupon_amount_field ">
                <label for="ftp_method_host"><?php esc_html_e( 'Host', 'woocommerce-exporter' ); ?></label>
                <input type="text" id="ftp_method_host" name="ftp_method_host" size="15" class="short code" value="<?php echo esc_attr( sanitize_text_field( $ftp_host ) ); ?>" style="margin-right:6px;" />
                <img class="help_tip" data-tip="<?php esc_attr_e( 'Enter the Host minus <code>ftp://</code> or <code>ftps://</code>', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                <span style="float:left; margin-right:6px;"><?php esc_html_e( 'Port', 'woocommerce-exporter' ); ?></span>
                <input type="text" id="ftp_method_port" name="ftp_method_port" size="5" class="short code sized" value="<?php echo esc_attr( sanitize_text_field( $ftp_port ) ); ?>" maxlength="5" />
            </p>

            <p class="form-field coupon_amount_field ">
                <label for="ftp_method_protocol"><?php esc_html_e( 'Protocol', 'woocommerce-exporter' ); ?></label>
                <select id="ftp_method_protocol" name="ftp_method_protocol" class="select short">
                    <option value="ftp" <?php selected( $ftp_protocol, 'ftp' ); ?>><?php esc_html_e( 'FTP - File Transfer Protocol', 'woocommerce-exporter' ); ?></option>
                    <option value="sftp" <?php selected( $ftp_protocol, 'sftp' ); ?><?php disabled( ( ! function_exists( 'ssh2_connect' ) ? true : false ), true ); ?>><?php esc_html_e( 'SFTP - SSH File Transfer Protocol', 'woocommerce-exporter' ); ?></option>
                </select>
                <?php if ( ! function_exists( 'ssh2_connect' ) ) { ?>
                    <img class="help_tip" data-tip="<?php esc_attr_e( 'The SFTP - SSH File Transfer Protocol option is not available as the required function ssh2_connect() is disabled within your WordPress site.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                <?php } ?>
            </p>

            <p class="form-field coupon_amount_field ">
                <label for="ftp_method_encryption"><?php esc_html_e( 'Encryption', 'woocommerce-exporter' ); ?></label>
                <select id="ftp_method_encryption" name="ftp_method_encryption" class="select short">
                    <option value="" <?php selected( $ftp_encryption, false ); ?>><?php esc_html_e( 'Only use plain FTP (insecure)', 'woocommerce-exporter' ); ?></option>
                    <option value="implicit" <?php selected( $ftp_encryption, 'implicit' ); ?><?php disabled( ( ! function_exists( 'curl_init' ) ? true : false ), true ); ?>><?php esc_html_e( 'Require implicit FTP over TLS', 'woocommerce-exporter' ); ?></option>
                    <option value="explicit" <?php selected( $ftp_encryption, 'explicit' ); ?>><?php esc_html_e( 'Require explicit FTP over TLS', 'woocommerce-exporter' ); ?></option>
                </select>
                <?php if ( ! function_exists( 'curl_init' ) ) { ?>
                    <img class="help_tip" data-tip="<?php esc_attr_e( 'The Require implicit FTP over TLS option is not available as the required function curl_init() is disabled within your WordPress site.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                <?php } ?>
            </p>

            <p class="form-field coupon_amount_field ">
                <label for="ftp_method_authentication"><?php esc_html_e( 'Authentication type', 'woocommerce-exporter' ); ?></label>
                <select id="ftp_method_authentication" name="ftp_method_authentication" class="select short">
                    <option value="" <?php selected( $ftp_authentication, false ); ?>><?php esc_html_e( 'Normal (Username and Password)', 'woocommerce-exporter' ); ?></option>
                    <option value="key_file" <?php selected( $ftp_authentication, 'key_file' ); ?><?php disabled( ( ! function_exists( 'curl_init' ) ? true : false ), true ); ?>><?php esc_html_e( 'Key file', 'woocommerce-exporter' ); ?></option>
                </select>
                <?php if ( ! function_exists( 'curl_init' ) ) { ?>
                    <img class="help_tip" data-tip="<?php esc_attr_e( 'The Key file authentication type is not available as the required function curl_init() is disabled within your WordPress site.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                <?php } ?>
            </p>

            <p class="form-field coupon_amount_field ">
                <label for="ftp_method_user"><?php esc_html_e( 'Username', 'woocommerce-exporter' ); ?></label>
                <input type="text" id="ftp_method_user" name="ftp_method_user" size="15" class="short code" value="<?php echo esc_attr( sanitize_text_field( $ftp_user ) ); ?>" />
            </p>

            <p class="form-field coupon_amount_field ">
                <label for="ftp_method_pass"><?php esc_html_e( 'Password', 'woocommerce-exporter' ); ?></label> <input type="text" id="ftp_method_pass" name="ftp_method_pass" size="15" class="short code password" value="" placeholder="<?php echo esc_attr( str_repeat( '*', strlen( $ftp_pass ) ) ); ?>" />
                <?php
                if ( ! empty( $ftp_pass ) ) {
                    echo ' ' . esc_html__( '(password is saved, fill this field to change it)', 'woocommerce-exporter' );
                }
                ?>
                <br />
            </p>

            <p class="form-field coupon_amount_field _method_ftp_authentication_key_file">
                <label for="ftp_method_public_key"><?php esc_html_e( 'Public key file path', 'woocommerce-exporter' ); ?></label> <textarea id="ftp_method_public_key" name="ftp_method_public_key" rows="3" class="short code"><?php echo esc_html( $ftp_public_key ); ?></textarea><br />
                <img class="help_tip" data-tip="<?php esc_attr_e( 'This absolute public key file path must be accessible to WordPress.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
            </p>

            <p class="form-field coupon_amount_field _method_ftp_authentication_key_file">
                <label for="ftp_method_private_key"><?php esc_html_e( 'Private key file path', 'woocommerce-exporter' ); ?></label> <textarea id="ftp_method_private_key" name="ftp_method_private_key" rows="3" class="short code"><?php echo esc_html( $ftp_private_key ); ?></textarea><br />
                <img class="help_tip" data-tip="<?php esc_attr_e( 'This absolute private key file path must be accessible to WordPress.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
            </p>

            <p class="form-field coupon_amount_field _method_ftp_authentication_key_file">
                <label for="ftp_method_private_key_secret"><?php esc_html_e( 'Prviate key passphrase', 'woocommerce-exporter' ); ?></label> <input type="text" id="ftp_method_private_key_secret" name="ftp_method_private_key_secret" size="15" class="short code password" value="" placeholder="<?php echo esc_attr( str_repeat( '*', strlen( $ftp_private_key_secret ) ) ); ?>" />
                <?php
                if ( ! empty( $ftp_private_key_secret ) ) {
                    echo ' ' . esc_html__( '(passphrase is saved, fill this field to change it)', 'woocommerce-exporter' );
                }
                ?>
                <br />
            </p>

            <p class="form-field coupon_amount_field ">
                <label for="ftp_method_file_path"><?php esc_html_e( 'File path', 'woocommerce-exporter' ); ?></label> <input type="text" id="ftp_method_file_path" name="ftp_method_path" size="25" class="short code" value="<?php echo esc_attr( sanitize_text_field( $ftp_path ) ); ?>" />
                <img class="help_tip" data-tip="<?php esc_attr_e( 'Do not provide the filename within File path as it will be generated for you or rely on the fixed filename entered below.<br /><br />For file path example: <code>wp-content/uploads/exports/</code>', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
            </p>

            <p class="form-field coupon_amount_field ">
                <label for="ftp_method_filename"><?php esc_html_e( 'Fixed filename', 'woocommerce-exporter' ); ?></label> <input type="text" id="ftp_method_filename" name="ftp_method_filename" size="25" class="short code" value="<?php echo esc_attr( $ftp_filename ); ?>" placeholder="<?php echo esc_attr( $export_filename ); ?>" />
                <img class="help_tip" data-tip="<?php esc_attr_e( 'The export filename can be set within the Fixed filename field otherwise it defaults to the Export filename provided within General Settings above.<br /><br />Tags can be used: ', 'woocommerce-exporter' ); ?> <code>%dataset%</code>, <code>%date%</code>, <code>%time%</code>, <code>%year%</code>, <code>%month%</code>, <code>%day%</code>, <code>%hour%</code>, <code>%minute%</code>, <code>%random%</code>, <code>%store_name%</code>, <code>%order_id%</code>." src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
            </p>

        </div>

        <div class="options_group">
            <p class="form-field coupon_amount_field ">
                <label for="ftp_method_passive"><?php esc_html_e( 'Connection mode', 'woocommerce-exporter' ); ?></label>
                <select id="ftp_method_passive" name="ftp_method_passive" class="select short">
                    <option value="auto" <?php selected( $ftp_passive, '' ); ?>><?php esc_html_e( 'Auto', 'woocommerce-exporter' ); ?></option>
                    <option value="active" <?php selected( $ftp_passive, 'active' ); ?>><?php esc_html_e( 'Active', 'woocommerce-exporter' ); ?></option>
                    <option value="passive" <?php selected( $ftp_passive, 'passive' ); ?>><?php esc_html_e( 'Passive', 'woocommerce-exporter' ); ?></option>
                </select>
                <img class="help_tip" data-tip="<?php esc_attr_e( 'Adjust the Connection mode where your FTP server requires an explicit Active or Passive connection mode.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
            </p>

            <p class="form-field coupon_amount_field ">
                <label for="ftp_method_mode"><?php esc_html_e( 'Transfer mode', 'woocommerce-exporter' ); ?></label>
                <select id="ftp_method_mode" name="ftp_method_mode" class="select short">
                    <option value="ASCII" <?php selected( $ftp_mode, 'ASCII' ); ?>><?php esc_html_e( 'ASCII', 'woocommerce-exporter' ); ?></option>
                    <option value="BINARY" <?php selected( $ftp_mode, 'BINARY' ); ?>><?php esc_html_e( 'BINARY', 'woocommerce-exporter' ); ?></option>
                </select>
                <img class="help_tip" data-tip="<?php esc_attr_e( 'Adjust the Transfer mode where your FTP server requires an explicit FTP_ASCII or FTP_BINARY transfer mode.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
            </p>

            <p class="form-field coupon_amount_field ">
                <label for="ftp_method_timeout"><?php esc_html_e( 'Timeout', 'woocommerce-exporter' ); ?></label> <input type="text" id="ftp_method_timeout" name="ftp_method_timeout" size="5" class="sized code" value="<?php echo esc_attr( sanitize_text_field( $ftp_timeout ) ); ?>" />
            </p>

        </div>

    </div>
    <!-- .ftp-options -->
<?php
    ob_end_flush();
}

/**
 * Save to Google Sheets
 *
 * This function is responsible for saving the scheduled export to Google Sheets.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_method_google_sheets( $post_ID = 0 ) {

    $access_code  = get_post_meta( $post_ID, '_method_google_sheets_access_code', true );
    $access_token = get_post_meta( $post_ID, '_method_google_sheets_access_token', true );

    if ( $access_code == false ) {
        $oauth_url = 'https://accounts.google.com/o/oauth2/auth?access_type=offline&approval_prompt=force&client_id=921518827300-a69e94dof5f31vilr4sddgq93t37ufad.apps.googleusercontent.com&redirect_uri=urn%3Aietf%3Awg%3Aoauth%3A2.0%3Aoob&response_type=code&scope=https%3A%2F%2Fspreadsheets.google.com%2Ffeeds%2F';
        // $oauth_url = 'https://accounts.google.com/o/oauth2/auth?access_type=offline&approval_prompt=force&client_id=921518827300-a69e94dof5f31vilr4sddgq93t37ufad.apps.googleusercontent.com&redirect_uri=urn%3Aietf%3Awg%3Aoauth%3A2.0%3Aoob&response_type=code&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fspreadsheets https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fdrive';.
    } else {
        $test_url  = add_query_arg(
            array(
				'page'     => 'woo_ce',
				'tab'      => 'scheduled_export',
				'action'   => 'test_google_sheets',
				'post'     => $post_ID,
				'_wpnonce' => wp_create_nonce( 'woo_ce_test_google_sheets' ),
            ),
            'admin.php'
        );
        $oauth_url = add_query_arg(
            array(
				'page'     => 'woo_ce',
				'tab'      => 'scheduled_export',
				'action'   => 'deauthorize_google_sheets',
				'post'     => $post_ID,
				'_wpnonce' => wp_create_nonce( 'woo_ce_deauthorize_google_sheets' ),
            ),
            'admin.php'
        );
    }
    $google_url = 'https://myaccount.google.com/security';

    ob_start();
    ?>
    <div class="export-options google_sheets-options">
        <div class="options_group">
            <p class="description">
                access_code: <?php echo esc_html( $access_code ); ?><br />
                access_token: <?php print_r( $access_token ); ?>
            </p>
            <?php if ( $access_code == false ) { ?>
                <p class="form-field discount_type_field">
                    <label><?php esc_html_e( 'Google Sheets Access', 'woocommerce-exporter' ); ?></label>
                    <?php esc_html_e( '<strong>Store Exporter Deluxe does not have permission</strong> to save this Scheduled Export to Google Sheets.', 'woocommerce-exporter' ); ?>
                </p>
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'          => '_method_google_sheets_access_code',
                        'label'       => __( 'Access code', 'woocommerce' ),
                        'desc_tip'    => 'true',
                        'description' => __( 'Paste the access code generated by Google to enable saving to Google Sheets.', 'woocommerce-exporter' ),
                    )
                );
                ?>
                <p id="authorize-field" class="form-field discount_type_field">
                    <a href="<?php echo esc_url( $oauth_url ); ?>" id="authorize-button" class="button" target="_blank"><?php esc_html_e( 'Authorize Google Sheets', 'woocommerce-exporter' ); ?></a>
                </p>
                <p class="description"><?php esc_html_e( 'For Store Exporter Deluxe to save Scheduled Exports to Google Sheets, you will first need to <strong>give Store Exporter Deluxe permission</strong>.', 'woocommerce-exporter' ); ?></p>
                <p class="description"><?php esc_html_e( 'Clicking the Authorize Google Sheets button above will open an OAuth 2.0 dialog linking this Scheduled Export within Store Exporter Deluxe to Google Sheets, paste the generated access code into the Access code field and click Update to see additional Google Sheets options.', 'woocommerce-exporter' ); ?></p>
            <?php } else { ?>
                <p class="form-field discount_type_field">
                    <label><?php esc_html_e( 'Google Sheets Access', 'woocommerce-exporter' ); ?></label>
                    <?php esc_html_e( '<strong>Store Exporter Deluxe has permission</strong> to save this Scheduled Export to Google Sheets.', 'woocommerce-exporter' ); ?>
                </p>
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'          => '_method_google_sheets_sheet_name',
                        'label'       => __( 'Sheet Name', 'woocommerce' ),
                        'desc_tip'    => 'true',
                        'description' => __( 'Paste the Sheet name from Google Sheets.', 'woocommerce-exporter' ),
                    )
                );
                woocommerce_wp_text_input(
                    array(
                        'id'          => '_method_google_sheets_tab_name',
                        'label'       => __( 'Sheet Tab Name', 'woocommerce' ),
                        'placeholder' => 'Sheet1',
                        'desc_tip'    => 'true',
                        'description' => __( 'Paste the Sheet tab name from Google Sheets.', 'woocommerce-exporter' ),
                    )
                );
                ?>
                <p id="authorize-field" class="form-field discount_type_field">
                    <a href="<?php echo esc_url( $test_url ); ?>" target="_blank" class="button" disabled="disabled">Validate access code</a>
                    <a href="<?php echo esc_url( $oauth_url ); ?>" class="button"><?php esc_html_e( 'De-authorize Google Sheets', 'woocommerce-exporter' ); ?></a>
                </p>
                <p class="description"><?php echo wp_kses_post( sprintf( __( 'You can remoke permission at any time by clicking the De-authorize Google Sheets link on this screen or from <a href="%s" target="_blank">Google &raquo; My Account &raquo; Sign-in & security</a>.', 'woocommerce-exporter' ), $google_url ) ); ?></p>
            <?php } ?>
        </div>
    </div>
    <!-- .save-options -->

<?php
    ob_end_flush();
}

/**
 * Save to Google Sheets (legacy)
 *
 * This function is responsible for displaying the export options for saving scheduled exports to Google Sheets.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_method_google_sheets_legacy( $post_ID = 0 ) {

    ob_start();
    ?>
    <div class="export-options google_sheets-options">

        <?php
        $client_id = get_post_meta( $post_ID, '_method_google_sheets_client_id', true );
        if ( $client_id == false ) {
        ?>
            <div class="options_group">
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'          => '_method_google_sheets_client_id',
                        'label'       => __( 'Client ID', 'woocommerce' ),
                        'desc_tip'    => 'true',
                        'description' => __( 'Your Client ID can be retrieved from your project in Google Developer Console', 'woocommerce-exporter' ),
                    )
                );
                ?>
            </div>
        <?php } else { ?>
            <div id="google-sheets-authorize-div" class="options_group" style="display:none">
                <p class="form-field discount_type_field">
                    <label><?php esc_html_e( 'Google Sheets Access', 'woocommerce-exporter' ); ?></label>
                    <?php esc_html_e( '<strong>Store Exporter Deluxe does not have permission</strong> to save Scheduled Exports to Google Sheets.', 'woocommerce-exporter' ); ?>
                    <a id="google-sheets-change-device-id" href="#" style="float:right;"><?php esc_html_e( 'Change Client ID', 'woocommerce-exporter' ); ?></a>
                </p>
                <p id="authorize-field" class="form-field discount_type_field">
                    <button id="authorize-button" onclick="handleAuthClick(event)" class="button"><?php esc_html_e( 'Authorize', 'woocommerce-exporter' ); ?></button>
                </p>
                <p class="description"><?php esc_html_e( 'For Store Exporter Deluxe to save Scheduled Exports to Google Sheets, you will first need to <strong>give Store Exporter Deluxe permission</strong>.', 'woocommerce-exporter' ); ?></p>
                <p class="description"><?php esc_html_e( 'Clicking the Authorize button above will open an OAuth 2.0 dialog linking Store Exporter Deluxe to Google Sheets, you can remoke permission at any time from Google > My Account > Sign-in & security.', 'woocommerce-exporter' ); ?></p>
            </div>
            <div id="google-sheets-authorized-div" class="options_group">
                <p class="form-field discount_type_field">
                    <label><?php esc_html_e( 'Google Sheets Access', 'woocommerce-exporter' ); ?></label>
                    <?php esc_html_e( '<strong>Store Exporter Deluxe has permission</strong> to save Scheduled Exports to Google Sheets.', 'woocommerce-exporter' ); ?>
                </p>
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'          => '_method_google_sheets_title',
                        'label'       => __( 'Spreadsheet Title', 'woocommerce' ),
                        'desc_tip'    => 'true',
                        'description' => __( 'The Title of your Spreadsheet', 'woocommerce-exporter' ),
                    )
                );
                ?>
            </div>

            <script type="text/javascript">
                // Your Client ID can be retrieved from your project in the Google.
                // Developer Console, https://console.developers.google.com.
                var CLIENT_ID = '<?php echo esc_attr( $client_id ); ?>';
                var SCOPES = ["https://www.googleapis.com/auth/spreadsheets"];

                /**
                 * Check if current user has authorized this application.
                 */
                function checkAuth() {
                    gapi.auth.authorize({
                        'client_id': CLIENT_ID,
                        'scope': SCOPES.join(' '),
                        'immediate': true
                    }, handleAuthResult);
                }

                /**
                 * Handle response from authorization server.
                 *
                 * @param {Object} authResult Authorization result.
                 */
                function handleAuthResult(authResult) {
                    var authorizeDiv = document.getElementById('google-sheets-authorize-div');
                    var authorizedDiv = document.getElementById('google-sheets-authorized-div');
                    if (authResult && !authResult.error) {
                        // Hide auth UI, then load client library.
                        authorizeDiv.style.display = 'none';
                        authorizedDiv.style.display = 'inline';
                        loadSheetsApi();
                    } else {
                        // Show auth UI, allowing the user to initiate authorization by.
                        // clicking authorize button.
                        authorizeDiv.style.display = 'inline';
                        authorizedDiv.style.display = 'none';
                    }
                }

                /**
                 * Initiate auth flow in response to user clicking authorize button.
                 *
                 * @param {Event} event Button click event.
                 */
                function handleAuthClick(event) {
                    event.preventDefault();
                    gapi.auth.authorize({
                            client_id: CLIENT_ID,
                            scope: SCOPES,
                            immediate: false
                        },
                        handleAuthResult);
                    return false;
                }

                /**
                 * Load Sheets API client library.
                 */
                function loadSheetsApi() {
                    var discoveryUrl = 'https://sheets.googleapis.com/$discovery/rest?version=v4';
                }
            </script>
            <script src="https://apis.google.com/js/client.js?onload=checkAuth"></script>
        <?php } ?>
    </div>
    <!-- .save-options -->

<?php
    ob_end_flush();
}

/**
 * Displays the frequency schedule options for a scheduled export.
 *
 * This function is responsible for rendering the HTML markup for the frequency schedule options
 * in the admin area of the WooCommerce Store Exporter Deluxe plugin.
 *
 * @param int $post_ID The ID of the post being edited.
 */
function woo_ce_scheduled_export_frequency_schedule( $post_ID = 0 ) {

    $auto_schedule = get_post_meta( $post_ID, '_auto_schedule', true );
    if ( $auto_schedule == false ) {
        $auto_schedule = 'daily';
    }
    $auto_interval = get_post_meta( $post_ID, '_auto_interval', true );

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field coupon_amount_field ">
            <label for="auto_schedule"><?php esc_html_e( 'Frequency', 'woocommerce-exporter' ); ?></label>
            <input type="radio" name="auto_schedule" value="hourly" <?php checked( $auto_schedule, 'hourly' ); ?> /> <?php esc_html_e( 'Hourly', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="auto_schedule" value="daily" <?php checked( $auto_schedule, 'daily' ); ?> /> <?php esc_html_e( 'Daily', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="auto_schedule" value="twicedaily" <?php checked( $auto_schedule, 'twicedaily' ); ?> /> <?php esc_html_e( 'Twice Daily', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="auto_schedule" value="weekly" <?php checked( $auto_schedule, 'weekly' ); ?> /> <?php esc_html_e( 'Weekly', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="auto_schedule" value="monthly" <?php checked( $auto_schedule, 'monthly' ); ?> /> <?php esc_html_e( 'Monthly', 'woocommerce-exporter' ); ?><br />
            <input type="radio" name="auto_schedule" value="yearly" <?php checked( $auto_schedule, 'yearly' ); ?> /> <?php esc_html_e( 'Yearly', 'woocommerce-exporter' ); ?><br />
            <span style="float:left; margin-right:6px;"><input type="radio" name="auto_schedule" value="custom" <?php checked( $auto_schedule, 'custom' ); ?> />&nbsp;<?php esc_html_e( 'Every ', 'woocommerce-exporter' ); ?></span>
            <input name="auto_interval" type="text" id="auto_interval" value="<?php echo esc_attr( $auto_interval ); ?>" size="6" maxlength="6" class="text sized" />
            <span style="float:left; margin-right:6px;"><?php esc_html_e( 'minutes', 'woocommerce-exporter' ); ?></span><br class="clear" />
            <input type="radio" name="auto_schedule" value="one-time" <?php checked( $auto_schedule, 'one-time' ); ?> /> <?php esc_html_e( 'One time', 'woocommerce-exporter' ); ?><br class="clear" />
            <input type="radio" name="auto_schedule" value="manual" <?php checked( $auto_schedule, 'manual' ); ?> /> <?php esc_html_e( 'Run manually only', 'woocommerce-exporter' ); ?>
        </p>
    </div>
    <!-- .options_group -->
<?php
    ob_end_flush();
}

/**
 * Displays the scheduled export frequency days options in the admin panel.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_frequency_days( $post_ID = 0 ) {

    $auto_days = get_post_meta( $post_ID, '_auto_days', true );
    // Default to all days.
    if ( empty( $auto_days ) ) {
        $auto_days = array( 0, 1, 2, 3, 4, 5, 6 );
    }

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field coupon_amount_field ">
            <label for="auto_days"><?php esc_html_e( 'Days', 'woocommerce-exporter' ); ?></label>
            <input type="checkbox" name="auto_days[]" value="1" <?php checked( in_array( 1, $auto_days ), true ); ?> /> <?php esc_html_e( 'Monday', 'woocommerce-exporter' ); ?><br />
            <input type="checkbox" name="auto_days[]" value="2" <?php checked( in_array( 2, $auto_days ), true ); ?> /> <?php esc_html_e( 'Tuesday', 'woocommerce-exporter' ); ?><br />
            <input type="checkbox" name="auto_days[]" value="3" <?php checked( in_array( 3, $auto_days ), true ); ?> /> <?php esc_html_e( 'Wednesday', 'woocommerce-exporter' ); ?><br />
            <input type="checkbox" name="auto_days[]" value="4" <?php checked( in_array( 4, $auto_days ), true ); ?> /> <?php esc_html_e( 'Thursday', 'woocommerce-exporter' ); ?><br />
            <input type="checkbox" name="auto_days[]" value="5" <?php checked( in_array( 5, $auto_days ), true ); ?> /> <?php esc_html_e( 'Friday', 'woocommerce-exporter' ); ?><br />
            <input type="checkbox" name="auto_days[]" value="6" <?php checked( in_array( 6, $auto_days ), true ); ?> /> <?php esc_html_e( 'Saturday', 'woocommerce-exporter' ); ?><br />
            <input type="checkbox" name="auto_days[]" value="0" <?php checked( in_array( 0, $auto_days ), true ); ?> /> <?php esc_html_e( 'Sunday', 'woocommerce-exporter' ); ?>
        </p>
    </div>
    <!-- .options_group -->
<?php
}

/**
 * Displays the HTML form for selecting the scheduled export frequency commencement.
 *
 * This function is responsible for rendering the HTML form that allows users to select the commencement
 * frequency for a scheduled export. It retrieves the necessary data from the database and generates the
 * appropriate HTML markup.
 *
 * @param int $post_ID The ID of the post being edited. Default is 0.
 */
function woo_ce_scheduled_export_frequency_commence( $post_ID = 0 ) {

    $auto_commence      = get_post_meta( $post_ID, '_auto_commence', true );
    $auto_commence_date = get_post_meta( $post_ID, '_auto_commence_date', true );
    $timezone_format    = _x( 'Y-m-d H:i:s', 'timezone date format' );

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field coupon_amount_field ">
            <label for="auto_commence"><?php esc_html_e( 'Commence', 'woocommerce-exporter' ); ?></label>
            <input type="radio" name="auto_commence" value="now" <?php checked( ( $auto_commence == false ? 'now' : $auto_commence ), 'now' ); ?> /> <?php esc_html_e( 'From now', 'woocommerce-exporter' ); ?><br />
            <span style="float:left; margin-right:6px;"><input type="radio" name="auto_commence" value="future" <?php checked( $auto_commence, 'future' ); ?> /> <?php esc_html_e( 'From', 'woocommerce-exporter' ); ?></span><input type="text" name="auto_commence_date" size="20" maxlength="20" class="sized datetimepicker" value="<?php echo esc_attr( $auto_commence_date ); ?>" autocomplete="off" />
            <!--, <?php esc_html_e( 'at this time', 'woocommerce-exporter' ); ?>: <input type="text" name="auto_interval_time" size="10" maxlength="10" class="text timepicker" />-->
            <span style="float:left; margin-right:6px;"><?php echo wp_kses_post( sprintf( __( 'Local time is: <code>%s</code>', 'woocommerce-exporter' ), date_i18n( $timezone_format ) ) ); ?></span>
        </p>
    </div>
    <!-- .options_group -->
    <?php
    ob_end_flush();
}

/**
 * Displays the meta box for scheduled export details.
 *
 * This function is responsible for displaying the meta box on the post edit screen
 * that shows the details of a scheduled export. It retrieves the necessary data
 * from the post meta and includes the template file for rendering the meta box.
 */
function woo_ce_scheduled_export_details_meta_box() {

    global $post;

    $post_ID = ( $post ? $post->ID : 0 );

    $exports     = get_post_meta( $post_ID, '_total_exports', true );
    $exports     = absint( $exports );
    $last_export = get_post_meta( $post_ID, '_last_export', true );
    $last_export = ( $last_export == false ? 'No exports yet' : woo_ce_format_archive_date( 0, $last_export ) );

    $template = 'scheduled_export-export_details.php';
    include_once WOO_CE_PATH . 'templates/admin/' . $template;
}

/**
 * Displays the meta box for the scheduled export history.
 *
 * This function is responsible for rendering the meta box that displays the scheduled export history
 * for a specific post. It retrieves the necessary data from the options and filters it based on the
 * current post ID. The filtered data is then displayed using a template file.
 *
 * @global WP_Post $post The current post object.
 */
function woo_ce_scheduled_export_history_meta_box() {

    global $post;

    $post_ID = ( $post ? $post->ID : 0 );

    $enable_auto    = woo_ce_get_option( 'enable_auto', 0 );
    $recent_exports = woo_ce_get_option( 'recent_scheduled_exports', array() );
    if ( empty( $recent_exports ) ) {
        $recent_exports = array();
    }
    $size = count( $recent_exports );

    // Array filter.
    $recent_exports = array_filter(
        $recent_exports,
        function ( $export ) use ( $post_ID ) {
        return ( $export['post_id'] == $post_ID );
        }
    );

    $recent_exports = array_reverse( $recent_exports );

    $template = 'scheduled_export-history.php';
    include_once WOO_CE_PATH . 'templates/admin/' . $template;
}

/**
 * Executes the meta box for running a scheduled export.
 *
 * This function displays a meta box with options to run a scheduled export immediately or abort a running export.
 */
function woo_ce_scheduled_export_execute_meta_box() {

    global $post;

    $post_ID = ( $post ? $post->ID : 0 );
    if ( 0 !== $post_ID ) {
        $action_status       = as_has_scheduled_action( WSED_AS_HOOK, array( 'id' => $post_ID ), WSED_AS_GROUP );
        $action_async_status = as_has_scheduled_action( WSED_AS_HOOK, array( 'id' => $post_ID ), WSED_AS_ASYNC_GROUP );

        // If true, means the manual export is executing.
        // or the scheduled export is in progress.
        $running = false;
        if ( true === $action_async_status || ( 'in-progress' === VisserLabs\WSE\Helpers\Export::get_action_status( $post_ID ) ) ) {
            $running = true;
        }

        echo '<p class="description">';
        echo 'Run this scheduled export now.';
        echo '</p>';

        if ( true === $running ) {
            $nonce = wp_create_nonce( 'wsed_cancel_scheduled_export' );
            ?>
            <a href="
            <?php
            echo esc_url(
                add_query_arg(
                    array(
                        'action'   => 'wsed_cancel_scheduled_export',
                        'post'     => $post_ID,
                        '_wpnonce' => $nonce,
                    ),
                    'admin-ajax.php'
                )
            );
?>
" class="button"><span class="spinner is-active"></span> <?php esc_html_e( 'Abort', 'woocommerce-exporter' ); ?></a>
        <?php
        } else {
            $nonce = wp_create_nonce( 'wsed_execute_scheduled_export' );
            ?>
            <a href="
            <?php
            echo esc_url(
                add_query_arg(
                    array(
                        'action'   => 'wsed_execute_scheduled_export',
                        'post'     => $post_ID,
                        '_wpnonce' => $nonce,
                    ),
                    'admin-ajax.php'
                )
            );
?>
" class="button execute_now"> <?php esc_html_e( 'Execute', 'woocommerce-exporter' ); ?></a>
    <?php
    }
    }
}

/**
 * Adds footer JavaScript code for the scheduled export functionality in the WooCommerce Store Exporter Deluxe plugin.
 *
 * This function hides certain options and elements in the export settings page using jQuery.
 * It also handles the logic for encrypting the export and FTP authentication.
 */
function woo_ce_admin_scheduled_export_footer_javascript() {

    // In-line javascript.
    ob_start();
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {

            // Hide Post Status.
            jQuery('#post_status option[value="pending"]').remove();
            jQuery('#post_status option[value="private"]').remove();
            jQuery('.misc-pub-curtime').hide();

            // Encrypt export.
            $j("input:radio[name=_method_email_encrypt_export]").change(function() {
                var encrypt_export = $j('input:radio[name=_method_email_encrypt_export]:checked').val();
                if (encrypt_export == '1')
                    $j('._method_email_encrypt_password_field').show();
                else
                    $j('._method_email_encrypt_password_field').hide();
            });
            $j("input:radio[name=_method_email_encrypt_export]").trigger('change');

            // FTP authentication.
            $j("select[name=ftp_method_authentication]").change(function() {
                var ftp_authentication = $j('select[name=ftp_method_authentication]').val();
                if (ftp_authentication == 'key_file') {
                    $j('._method_ftp_authentication_key_file').show();
                } else {
                    $j('._method_ftp_authentication_key_file').hide();
                }
            });
            $j("select[name=ftp_method_authentication]").trigger('change');

        });
    </script>
    <?php
    ob_end_flush();
}

/**
 * Deletes a scheduled export.
 *
 * This function is responsible for deleting a scheduled export post and removing any recent entries linked to it.
 *
 * @param int|false $post_ID The ID of the scheduled export post to be deleted. Defaults to false.
 */
function woo_ce_scheduled_export_delete( $post_ID = false ) {

    global $post_type;

    if ( $post_type != 'scheduled_export' ) {
        return;
    }

    // Remove any recent entries linked to this Scheduled Export.
    $recent_exports = woo_ce_get_option( 'recent_scheduled_exports', array() );
    if ( ! empty( $recent_exports ) ) {
        $updated = false;
        foreach ( $recent_exports as $key => $recent_export ) {
            if ( isset( $recent_export['scheduled_id'] ) ) {
                if ( $recent_export['scheduled_id'] == $post_ID ) {
                    unset( $recent_exports[ $key ] );
                    $updated = true;
                }
            }
        }
        if ( $updated ) {
            woo_ce_update_option( 'recent_scheduled_exports', $recent_exports );
        }
    }
}
add_action( 'before_delete_post', 'woo_ce_scheduled_export_delete' );

/**
 * Updates the post meta for scheduled export filters.
 *
 * @param int $post_ID The ID of the post being saved.
 */
function woo_ce_extend_scheduled_export_save( $post_ID = 0 ) {

    // Filters.

    // WooCommerce Brands Addon - http://woothemes.com/woocommerce/.
    // WooCommerce Brands - http://proword.net/Woocommerce_Brands/.
    if ( woo_ce_detect_product_brands() ) {
        update_post_meta( $post_ID, '_filter_product_brand', ( isset( $_POST['product_filter_brand'] ) ? array_map( 'absint', (array) $_POST['product_filter_brand'] ) : false ) );
        update_post_meta( $post_ID, '_filter_order_brand', ( isset( $_POST['order_filter_brand'] ) ? array_map( 'absint', (array) $_POST['order_filter_brand'] ) : false ) );
    }

    // Product Vendors - http://www.woothemes.com/products/product-vendors/.
    // WC Vendors - http://wcvendors.com.
    // YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/.
    if ( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
        update_post_meta( $post_ID, '_filter_product_vendor', ( isset( $_POST['product_filter_vendor'] ) ? array_map( 'absint', (array) $_POST['product_filter_vendor'] ) : false ) );
    }

    // WPML - https://wpml.org/.
    // WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/.
    if ( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
        update_post_meta( $post_ID, '_filter_product_language', ( isset( $_POST['product_filter_language'] ) ? array_map( 'sanitize_text_field', (array) $_POST['product_filter_language'] ) : false ) );
    }

    // WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/.
    if ( woo_ce_detect_export_plugin( 'subscriptions' ) ) {
        update_post_meta( $post_ID, '_filter_order_type', ( isset( $_POST['order_filter_order_type'] ) ? sanitize_text_field( $_POST['order_filter_order_type'] ) : false ) );
    }

    // WooCommerce Easy Booking - https://wordpress.org/plugins/woocommerce-easy-booking-system/.
    if ( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
        update_post_meta( $post_ID, '_filter_order_booking_start_date_filter', ( isset( $_POST['order_booking_start_dates_filter'] ) ? sanitize_text_field( $_POST['order_booking_start_dates_filter'] ) : false ) );
        update_post_meta( $post_ID, '_filter_order_booking_start_date_from', ( isset( $_POST['order_booking_start_dates_from'] ) ? sanitize_text_field( $_POST['order_booking_start_dates_from'] ) : false ) );
        update_post_meta( $post_ID, '_filter_order_booking_start_date_to', ( isset( $_POST['order_booking_start_dates_to'] ) ? sanitize_text_field( $_POST['order_booking_start_dates_to'] ) : false ) );
    }

    // Product meta.
    $custom_products = woo_ce_get_option( 'custom_products', '' );
    if ( ! empty( $custom_products ) ) {
        foreach ( $custom_products as $custom_product ) {
            update_post_meta( $post_ID, sprintf( '_filter_product_custom_meta-%s', esc_attr( $custom_product ) ), ( isset( $_POST[ sprintf( 'product_filter_custom_meta-%s', esc_attr( $custom_product ) ) ] ) ? sanitize_text_field( $_POST[ sprintf( 'product_filter_custom_meta-%s', esc_attr( $custom_product ) ) ] ) : false ) );
        }
    }

    // Order meta.
    $custom_orders = woo_ce_get_option( 'custom_orders', '' );
    if ( ! empty( $custom_orders ) ) {
        foreach ( $custom_orders as $custom_order ) {
            update_post_meta( $post_ID, sprintf( '_filter_order_custom_meta-%s', esc_attr( $custom_order ) ), ( isset( $_POST[ sprintf( 'order_filter_custom_meta-%s', esc_attr( $custom_order ) ) ] ) ? sanitize_text_field( $_POST[ sprintf( 'order_filter_custom_meta-%s', esc_attr( $custom_order ) ) ] ) : false ) );
        }
    }
}
add_action( 'woo_ce_extend_scheduled_export_save', 'woo_ce_extend_scheduled_export_save' );

/**
 * Displays the recent scheduled exports in the admin panel.
 *
 * This function retrieves the recent scheduled exports from the options and displays them in a paginated manner.
 * It also handles the pagination links and includes the template file for displaying the exports.
 */
function woo_ce_admin_scheduled_exports_recent_scheduled_exports() {

    $enable_auto    = woo_ce_get_option( 'enable_auto', 0 );
    $recent_exports = woo_ce_get_option( 'recent_scheduled_exports', array() );
    if ( empty( $recent_exports ) ) {
        $recent_exports = array();
    }
    $size           = count( $recent_exports );
    $recent_exports = array_reverse( $recent_exports );

    // Pagination time!.
    $per_page         = apply_filters( 'woo_ce_admin_scheduled_exports_recent_scheduled_exports_per_page', 20 );
    $offset           = ( isset( $_GET['paged'] ) ? ( absint( $_GET['paged'] ) * $per_page ) : 0 );
    $pagination_links = '';
    if ( $size > $per_page ) {
        $pages          = absint( $size / $per_page );
        $recent_exports = array_slice( $recent_exports, $offset, $per_page );

        if ( function_exists( 'paginate_links' ) ) {
            $paginations = paginate_links(
                array(
					'base'      => '?paged=%#%',
					'format'    => '?paged=%#%',
					'type'      => 'array',
					'current'   => max( 1, ( isset( $_GET['paged'] ) ? ( absint( $_GET['paged'] ) ) : false ) ),
					'total'     => $pages,
					// 'mid_size' => 0,.
					// 'end_size' => 0,.
					'prev_text' => '&laquo;',
					'next_text' => '&raquo;',
                )
            );
            if ( ! empty( $paginations ) ) {
                foreach ( $paginations as $pagination ) {
                    $pagination_output = $pagination;
                    if ( strpos( $pagination, '<a class' ) !== false ) {
                        $pagination_output = str_replace( 'page-numbers', 'button', $pagination_output );
                    }
                    // $pagination_output = str_replace( array( __( 'Previous' ), __( 'Next' ) ), '', $pagination_output );.
                    $pagination_links .= $pagination_output;
                }
            }
            unset( $paginations, $pagination );
        }
    }

    $template = 'scheduled_exports-recent_scheduled_exports.php';
    if ( file_exists( WOO_CE_PATH . 'templates/admin/' . $template ) ) {
        include_once WOO_CE_PATH . 'templates/admin/' . $template;
    } else {
        $message = sprintf( __( 'We couldn\'t load the widget template file <code>%1$s</code> within <code>%2$s</code>, this file should be present.', 'woocommerce-exporter' ), $template, WOO_CE_PATH . 'templates/admin/...' );

        ob_start();
        ?>
        <p><strong><?php echo wp_kses_post( $message ); ?></strong></p>
        <p><?php esc_html_e( 'You can see this error for one of a few common reasons', 'woocommerce-exporter' ); ?>:</p>
        <ul class="ul-disc">
            <li><?php esc_html_e( 'WordPress was unable to create this file when the Plugin was installed or updated', 'woocommerce-exporter' ); ?></li>
            <li><?php esc_html_e( 'The Plugin files have been recently changed and there has been a file conflict', 'woocommerce-exporter' ); ?></li>
            <li><?php esc_html_e( 'The Plugin file has been locked and cannot be opened by WordPress', 'woocommerce-exporter' ); ?></li>
        </ul>
        <p><?php esc_html_e( 'Jump onto our website and download a fresh copy of this Plugin as it might be enough to fix this issue. If this persists get in touch with us.', 'woocommerce-exporter' ); ?></p>
<?php
        ob_end_flush();
    }
}
add_action( 'woo_ce_after_scheduled_exports', 'woo_ce_admin_scheduled_exports_recent_scheduled_exports' );
?>
