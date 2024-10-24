<?php
/**
 * HTML template for Filter Products by Brand widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Products by Brand widget on the Store Exporter screen.
 * It displays a checkbox to enable/disable the filter and a dropdown to select the product brands to filter by.
 */
function woo_ce_products_filter_by_product_brand() {

    // WooCommerce Brands Addon - http://woothemes.com/woocommerce/.
    // WooCommerce Brands - http://proword.net/Woocommerce_Brands/.
    if ( woo_ce_detect_product_brands() == false ) {
        return;
    }

    $args           = array(
        'hide_empty' => 1,
        'orderby'    => 'term_group',
    );
    $product_brands = woo_ce_get_product_brands( $args );
    $types          = woo_ce_get_option( 'product_brands', array() );

    ob_start(); ?>
    <p><label><input type="checkbox" id="products-filters-brands" name="product_filter_brand_include" <?php checked( ! empty( $types ), true ); ?> /> <?php esc_html_e( 'Filter Products by Product Brand', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-brands" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $product_brands ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Product Brand...', 'woocommerce-exporter' ); ?>" name="product_filter_brand[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $product_brands as $product_brand ) { ?>
                            <?php // translators: %1$s is the product brand name, %2$d is the term ID. ?>
                            <option value="<?php echo esc_attr( $product_brand->term_id ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $product_brand->term_id, $types, false ), true ) : '' ); ?><?php disabled( $product_brand->count, 0 ); ?>><?php echo esc_html( woo_ce_format_product_category_label( $product_brand->name, $product_brand->parent_name ) ); ?> (<?php echo esc_html( sprintf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_brand->term_id ) ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Product Brands were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Product Brands you want to filter exported Products by. Product Brands not assigned to Products are hidden from view. Default is to include all Product Brands.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-brands -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Product Vendor widget on Store Exporter screen.
 */
function woo_ce_products_filter_by_product_vendor() {

    // Product Vendors - http://www.woothemes.com/products/product-vendors/.
    // YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/.
    if ( woo_ce_detect_export_plugin( 'vendors' ) == false && woo_ce_detect_export_plugin( 'yith_vendor' ) == false ) {
        return;
    }

    $args            = array(
        'hide_empty' => 1,
    );
    $product_vendors = woo_ce_get_product_vendors( $args, 'full' );

    ob_start();
    ?>
    <p><label><input type="checkbox" id="products-filters-vendors" name="product_filter_vendor_include" /> <?php esc_html_e( 'Filter Products by Product Vendor', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-vendors" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $product_vendors ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Product Vendor...', 'woocommerce-exporter' ); ?>" name="product_filter_vendor[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $product_vendors as $product_vendor ) { ?>
                            <?php // translators: %1$s is the product brand name, %2$d is the term ID. ?>
                            <option value="<?php echo esc_attr( $product_vendor->term_id ); ?>" <?php disabled( $product_vendor->count, 0 ); ?>><?php echo esc_html( $product_vendor->name ); ?> (<?php echo esc_html( sprintf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_vendor->term_id ) ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Product Vendors were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Product Vendors you want to filter exported Products by. Product Vendors not assigned to Products are hidden from view. Default is to include all Product Vendors.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-vendors -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Language widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Products by Language widget on the Store Exporter screen.
 * It checks if the WPML and WooCommerce Multilingual plugins are active, and if not, it returns early.
 * It then retrieves the list of languages using the icl_get_languages() function from the WPML plugin.
 * The function then outputs the HTML template for the widget, including a checkbox to enable/disable the filter,
 * a dropdown select field to choose the languages, and a description explaining the purpose of the filter.
 */
function woo_ce_products_filter_by_language() {

    // WPML - https://wpml.org/.
    // WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/.
    if ( ! woo_ce_detect_wpml() || ! woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
        return;
    }

    $languages = ( function_exists( 'icl_get_languages' ) ? icl_get_languages( 'skip_missing=N' ) : array() );

    ob_start();
    ?>
    <p><label><input type="checkbox" id="products-filters-language" name="product_filter_language_include" /> <?php esc_html_e( 'Filter Products by Language', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-language" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $languages ) ) { ?>
                    <select id="products-filters-language" data-placeholder="<?php esc_attr_e( 'Choose a Language...', 'woocommerce-exporter' ); ?>" name="product_filter_language[]" multiple style="width:95%;">
                        <option value=""><?php esc_html_e( 'Default', 'woocommerce-exporter' ); ?></option>
                        <?php foreach ( $languages as $key => $language ) { ?>
                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $language['native_name'] ); ?> (<?php echo esc_html( $language['translated_name'] ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Languages were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Language\'s you want to filter exported Products by. Default is to include all Language\'s.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-language -->
<?php
    ob_end_flush();
}

/**
 * FILEPATH: /c:/Users/digid/Local Sites/visser/app/public/wp-content/plugins/woocommerce-store-exporter-deluxe/includes/admin/product-extend.php
 *
 * Scheduled Export.
 *
 * This function is used to filter products by brand for scheduled exports.
 * It checks if the WooCommerce Brands Addon is active and retrieves the list of product brands.
 * It then displays a select dropdown to choose the product brands for filtering.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_product_filter_by_product_brand( $post_ID = 0 ) {

    // WooCommerce Brands Addon - http://woothemes.com/woocommerce/.
    // WooCommerce Brands - http://proword.net/Woocommerce_Brands/.
    if ( woo_ce_detect_product_brands() == false ) {
        return;
    }

    $args           = array(
        'hide_empty' => 1,
        'orderby'    => 'term_group',
    );
    $product_brands = woo_ce_get_product_brands( $args );
    $types          = get_post_meta( $post_ID, '_filter_product_brand', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="product_filter_brand"><?php esc_html_e( 'Product brand', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $product_brands ) ) { ?>
            <select id="product_filter_brand" data-placeholder="<?php esc_attr_e( 'Choose a Product Brand...', 'woocommerce-exporter' ); ?>" name="product_filter_brand[]" multiple class="chzn-select select short" style="width:95%;">
                <?php foreach ( $product_brands as $product_brand ) { ?>
                    <?php // translators: %1$s is the product brand name, %2$d is the term ID. ?>
                    <option value="<?php echo esc_attr( $product_brand->term_id ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $product_brand->term_id, $types ) : false ), true ); ?><?php disabled( $product_brand->count, 0 ); ?>><?php echo esc_html( $product_brand->name ); ?> (<?php echo esc_html( sprintf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_brand->term_id ) ); ?>)</option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_html_e( 'Select the Product Brand\'s you want to filter exported Products by. Default is to include all Product Brands.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        <?php } else { ?>
            <?php esc_html_e( 'No Product Brands were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Filters the product by language for scheduled export.
 *
 * This function is used to filter the products by language for scheduled export.
 * It checks if the WPML and WooCommerce Multilingual plugins are active.
 * If they are not active, the function returns early.
 * It retrieves the available languages and the selected language types for the product.
 * Then, it generates the HTML markup for the language filter dropdown.
 *
 * @param int $post_ID The ID of the product post.
 */
function woo_ce_scheduled_export_product_filter_by_language( $post_ID = 0 ) {

    // WPML - https://wpml.org/.
    // WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/.
    if ( ! woo_ce_detect_wpml() || ! woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
        return;
    }

    $languages = ( function_exists( 'icl_get_languages' ) ? icl_get_languages( 'skip_missing=N' ) : array() );
    $types     = get_post_meta( $post_ID, '_filter_product_language', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="product_filter_language"><?php esc_html_e( 'Language', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $languages ) ) { ?>
            <select id="product_filter_language" data-placeholder="<?php esc_attr_e( 'Choose a Language...', 'woocommerce-exporter' ); ?>" name="product_filter_language[]" multiple style="width:95%;">
                <option value=""><?php esc_html_e( 'Default', 'woocommerce-exporter' ); ?></option>
                <?php foreach ( $languages as $key => $language ) { ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $key, $types ) : false ), true ); ?>><?php echo esc_html( $language['native_name'] ); ?> (<?php echo esc_html( $language['translated_name'] ); ?>)</option>
                <?php } ?>
            </select>
        <?php } else { ?>
            <?php esc_html_e( 'No Languages were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Filters the product by vendor in the scheduled export.
 *
 * @param int $post_ID The ID of the post being filtered.
 */
function woo_ce_scheduled_export_product_filter_by_product_vendor( $post_ID = 0 ) {

    if ( woo_ce_detect_export_plugin( 'vendors' ) == false && woo_ce_detect_export_plugin( 'yith_vendor' ) == false ) {
        return;
    }

    $args            = array(
        'hide_empty' => 1,
    );
    $product_vendors = woo_ce_get_product_vendors( $args, 'full' );
    $types           = get_post_meta( $post_ID, '_filter_product_vendor', true );

    ob_start();
    ?>
    <?php if ( ! empty( $product_vendors ) ) { ?>
        <p class="form-field discount_type_field">
            <label for="product_filter_vendor"><?php esc_html_e( 'Product vendor', 'woocommerce-exporter' ); ?></label>
            <select data-placeholder="<?php esc_attr_e( 'Choose a Product Vendor...', 'woocommerce-exporter' ); ?>" name="product_filter_vendor[]" multiple class="chzn-select" style="width:95%;">
                <?php foreach ( $product_vendors as $product_vendor ) { ?>
                    <?php // translators: %1$s is the product brand name, %2$d is the term ID. ?>
                    <option value="<?php echo esc_attr( $product_vendor->term_id ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $product_vendor->term_id, $types ) : false ), true ); ?><?php disabled( $product_vendor->count, 0 ); ?>><?php echo esc_html( $product_vendor->name ); ?> (<?php echo esc_html( sprintf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_vendor->term_id ) ); ?>)</option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_html_e( 'Select the Product Vendor\'s you want to filter exported Products by. Default is to include all Product Vendors.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        <?php } else { ?>
            <?php esc_html_e( 'No Product Vendors were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
        </p>

    <?php
    ob_end_flush();
}
?>
