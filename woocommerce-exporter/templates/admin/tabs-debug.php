<h1 class="wp-heading-inline"><?php esc_html_e( 'Debugging', 'woocommerce-exporter' ); ?></h1>
<?php
// Gravity Forms - http://woothemes.com/woocommerce.
if ( woo_ce_detect_export_plugin( 'gravity_forms' ) ) {
    echo '<h2 class="wp-heading-inline"">';
    echo esc_html__( 'Gravity Forms', 'woocommerce-exporter' );
    echo '<a href="' . esc_url(
        add_query_arg(
            array(
				'action'   => 'refresh_export_type_counts',
				'_wpnonce' => wp_create_nonce( 'woo_ce_refresh_export_type_counts' ),
            )
        )
    ) . '" class="page-title-action">' . esc_html__( 'Refresh', 'woocommerce-exporter' ) . '</a>';
    echo '</h2>';

    echo '<h3>' . esc_html__( 'Products', 'woocommerce-exporter' ) . '</h3>';
    $products = get_transient( WOO_CE_PREFIX . '_gravity_forms_products' );
    if ( false !== $products ) {
        print_r( $products );
    } else {
        // translators: %s is the Transient name.
        printf( esc_html__( 'The Gravity Forms Products Transient %s is not populated.', 'woocommerce-exporter' ), '<code>' . esc_html( WOO_CE_PREFIX ) . '_gravity_forms_products</code>' );
    }

    echo '<h3>' . esc_html__( 'Fields', 'woocommerce-exporter' ) . '</h3>';
    $fields = get_transient( WOO_CE_PREFIX . '_gravity_forms_fields' );
    if ( false !== $fields ) {
        print_r( $fields );
    } else {
        // translators: %s is the Transient name.
        printf( esc_html__( 'The Gravity Forms fields Transient %s is not populated.', 'woocommerce-exporter' ), '<code>' . esc_html( WOO_CE_PREFIX ) . '_gravity_forms_fields</code>' );
    }
    echo '<hr />';
}

// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619.
if ( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
    echo '<h2 class="wp-heading-inline"">';
    echo esc_html__( 'Extra Product Options', 'woocommerce-exporter' );
    echo '<a href="' . esc_url(
        add_query_arg(
            array(
				'action'   => 'refresh_export_type_counts',
				'_wpnonce' => wp_create_nonce( 'woo_ce_refresh_export_type_counts' ),
            )
        )
    ) . '" class="page-title-action">' . esc_html__( 'Refresh', 'woocommerce-exporter' ) . '</a>';
    echo '</h2>';

    echo '<h3>' . esc_html__( 'Fields', 'woocommerce-exporter' ) . '</h3>';
    $fields = get_transient( WOO_CE_PREFIX . '_extra_product_option_fields' );
    if ( false !== $fields ) {
        print_r( $fields );
    } else {
        // translators: %s is the Transient name.
        printf( esc_html__( 'The EPO Transient %s is not populated.', 'woocommerce-exporter' ), '<code>' . esc_html( WOO_CE_PREFIX ) . '_extra_product_option_fields</code>' );
    }
    echo '<hr />';
}
?>
