<?php
/**
 * HTML template for Filter Products by Product Category widget on Store Exporter screen
 */
function woo_ce_products_filter_by_product_category() {

    if ( apply_filters( 'woo_ce_override_products_filter_by_product_category', true ) == false ) {
        return;
    }

    $args = array(
        'hide_empty' => 1,
    );

    // Allow other developers to bake in their own filters.
    $args = apply_filters( 'woo_ce_products_filter_by_product_category_args', $args );

    $product_categories = woo_ce_get_product_categories( $args );
    $types              = woo_ce_get_option( 'product_category', array() );

    ob_start(); ?>
    <p><label><input type="checkbox" id="products-filters-categories" name="product_filter_category_include" <?php checked( ! empty( $types ), true ); ?> /> <?php esc_html_e( 'Filter Products by Product Category', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-categories" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $product_categories ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Product Category...', 'woocommerce-exporter' ); ?>" name="product_filter_category[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $product_categories as $product_category ) { ?>
                            <option value="<?php echo esc_attr( $product_category->term_id ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $product_category->term_id, $types, false ), true ) : '' ); ?><?php disabled( $product_category->count, 0 ); ?>><?php echo esc_html( woo_ce_format_product_category_label( $product_category->name, $product_category->parent_name ) ); ?> (<?php echo wp_kses_post( sprintf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_category->term_id ) ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Product Categories were found linked to Products.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Product Categories you want to filter exported Products by. Product Categories not assigned to Products are hidden from view. Default is to include all Product Categories.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-categories -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Product Tag widget on Store Exporter screen
 */
function woo_ce_products_filter_by_product_tag() {

    if ( apply_filters( 'woo_ce_override_products_filter_by_product_tag', true ) == false ) {
        return;
    }

    $args = array(
        'hide_empty' => 1,
    );

    // Allow other developers to bake in their own filters.
    $args = apply_filters( 'woo_ce_products_filter_by_product_tag_args', $args );

    $product_tags = woo_ce_get_product_tags( $args );
    $types        = woo_ce_get_option( 'product_tag', array() );

    $exclude = woo_ce_get_option( 'product_tag_exclude', false );

    ob_start();
    ?>
    <p><label><input type="checkbox" id="products-filters-tags" name="product_filter_tag_include" <?php checked( ! empty( $types ), true ); ?> /> <?php esc_html_e( 'Filter Products by Product Tag', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-tags" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $product_tags ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Product Tag...', 'woocommerce-exporter' ); ?>" name="product_filter_tag[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $product_tags as $product_tag ) { ?>
                            <option value="<?php echo esc_attr( $product_tag->term_id ); ?>" <?php echo esc_attr( $product_tag->term_id ); ?><?php disabled( $product_tag->count, 0 ); ?>><?php echo esc_html( $product_tag->name ); ?> (<?php echo wp_kses_post( sprintf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_tag->term_id ) ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Product Tags were found linked to Products.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Product Tags you want to filter exported Products by. Product Tags not assigned to Products are hidden from view. Default is to include all Product Tags.', 'woocommerce-exporter' ); ?></p>
        <ul>
            <li><label><input type="radio" name="product_filter_tag_exclude" value="0" <?php checked( $exclude, false ); ?> /> <?php esc_html_e( 'Include only Products these selected Product Tags', 'woocommerce-exporter' ); ?></label></li>
            <li><label><input type="radio" name="product_filter_tag_exclude" value="1" <?php checked( $exclude, 1 ); ?> /> <?php esc_html_e( 'Filter out Products with these selected Product Tags', 'woocommerce-exporter' ); ?></label></li>
        </ul>
        <p class="description"><?php esc_html_e( 'Choose whether Products not matching the selected Product Tags should be removed from the export. Default is to include all matched Products.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-tags -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Product Status widget on Store Exporter screen
 */
function woo_ce_products_filter_by_product_status() {

    $product_stati = get_post_statuses();
    if ( ! isset( $product_stati['trash'] ) ) {
        $product_stati['trash'] = __( 'Trash', 'woocommerce-exporter' );
    }

    // Allow Plugin/Theme authors to add support for custom Product Post Stati.
    $product_stati = apply_filters( 'woo_ce_products_filter_post_stati', $product_stati );

    $types = woo_ce_get_option( 'product_status', array() );

    ob_start();
    ?>
    <p><label><input type="checkbox" id="products-filters-status" name="product_filter_status_include" <?php checked( ! empty( $types ), true ); ?> /> <?php esc_html_e( 'Filter Products by Product Status', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-status" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $product_stati ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Product Status...', 'woocommerce-exporter' ); ?>" name="product_filter_status[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $product_stati as $key => $product_status ) { ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo esc_html( $product_status ); ?></option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Product Status were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Product Status options you want to filter exported Products by. Default is to include all Product Status options.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-status -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Product Type widget on Store Exporter screen
 */
function woo_ce_products_filter_by_product_type() {

    $product_types = woo_ce_get_product_types();
    $types         = woo_ce_get_option( 'product_type', array() );

    ob_start();
    ?>
    <p><label><input type="checkbox" id="products-filters-type" name="product_filter_type_include" <?php checked( ! empty( $types ), true ); ?> /> <?php esc_html_e( 'Filter Products by Product Type', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-type" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $product_types ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Product Type...', 'woocommerce-exporter' ); ?>" name="product_filter_type[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $product_types as $key => $product_type ) { ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?><?php disabled( $product_type['count'], 0 ); ?>><?php echo esc_html( woo_ce_format_product_type( $product_type['name'] ) ); ?> (<?php echo esc_html( $product_type['count'] ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Product Types were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Product Type\'s you want to filter exported Products by. Default is to include all Product Types except Variations.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-type -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Product widget on Store Exporter screen
 */
function woo_ce_products_filter_by_sku() {

    if ( apply_filters( 'woo_ce_override_products_filter_by_sku', true ) == false ) {
        return;
    }

    $post_status = array( 'publish', 'pending', 'future', 'private' );
    $args        = array(
        'product_status' => $post_status,
    );

    // Allow other developers to bake in their own filters.
    $args = apply_filters( 'woo_ce_products_filter_by_sku_args', $args );

    $products = woo_ce_get_products( $args );
    add_filter( 'the_title', 'woo_ce_get_product_title_sku', 10, 2 );

    $exclude = woo_ce_get_option( 'product_sku_exclude', false );

    ob_start();
    ?>
    <p><label><input type="checkbox" id="products-filters-sku" name="product_filter_sku_include" /> <?php esc_html_e( 'Filter Products by Product', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-sku" class="separator">
        <ul>
            <li>
                <?php if ( wp_script_is( 'wc-enhanced-select', 'enqueued' ) ) { ?>
                    <p>
                        <?php if ( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) { ?>
                            <select
                                data-placeholder="<?php esc_attr_e( 'Search for a Product&hellip;', 'woocommerce' ); ?>"
                                id="product_filter_sku"
                                name="product_filter_sku[]"
                                multiple="multiple"
                                class="multiselect wc-product-search"
                                style="width:95%;"
                                data-action="woocommerce_json_search_products_and_variations"></select>
                        <?php } else { ?>
                            <input
                                data-placeholder="<?php esc_attr_e( 'Search for a Product&hellip;', 'woocommerce-exporter' ); ?>"
                                type="hidden"
                                id="product_filter_sku"
                                name="product_filter_sku[]"
                                class="multiselect wc-product-search"
                                data-multiple="true"
                                style="width:95;"
                                data-action="woocommerce_json_search_products_and_variations" />
                        <?php } ?>
                    </p>
                <?php } else { ?>
                    <?php if ( ! empty( $products ) ) { ?>
                        <select data-placeholder="<?php esc_attr_e( 'Choose a Product...', 'woocommerce-exporter' ); ?>" name="product_filter_sku[]" multiple class="chzn-select" style="width:95%;">
                            <?php foreach ( $products as $product ) { ?>
                                <option value="<?php echo esc_attr( $product ); ?>"><?php echo esc_html( woo_ce_format_post_title( get_the_title( $product ) ) ); ?></option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <?php esc_html_e( 'No Products were found.', 'woocommerce-exporter' ); ?>
                    <?php } ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Products you want to filter exported Products by. Multiple Products can be selected. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
        <ul>
            <li><label><input type="radio" name="product_filter_sku_exclude" value="0" <?php checked( $exclude, false ); ?> /> <?php esc_html_e( 'Include only these selected Products', 'woocommerce-exporter' ); ?></label></li>
            <li><label><input type="radio" name="product_filter_sku_exclude" value="1" <?php checked( $exclude, 1 ); ?> /> <?php esc_html_e( 'Filter out these selected Products', 'woocommerce-exporter' ); ?></label></li>
        </ul>
        <p class="description"><?php esc_html_e( 'Choose whether Products not matching the selected Products should be removed from the export. Default is to include all matched Products.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-sku -->
<?php
    remove_filter( 'the_title', 'woo_ce_get_product_title_sku', 10, 2 );
    ob_end_flush();
}

/**
 * HTML template for Filter Products by User Role widget on Store Exporter screen
 */
function woo_ce_products_filter_by_user_role() {

    $user_roles = woo_ce_get_user_roles();
    $types      = woo_ce_get_option( 'product_user_role', array() );

    ob_start();
    ?>
    <p><label><input type="checkbox" id="products-filters-user_role" name="product_filter_user_role_include" <?php checked( ! empty( $types ), true ); ?> /> <?php esc_html_e( 'Filter Products by User Role', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-user_role" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $user_roles ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="product_filter_user_role[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $user_roles as $key => $user_role ) { ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo esc_html( ucfirst( $user_role['name'] ) ); ?></option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the User Roles you want to filter exported Products by. Default is to include all User Role options.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-user_role -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Stock Status widget on Store Exporter screen
 */
function woo_ce_products_filter_by_stock_status() {

    $types = woo_ce_get_option( 'product_stock', false );

    ob_start();
    ?>
    <p><label><input type="checkbox" id="products-filters-stock" name="product_filter_stock_include" <?php checked( ! empty( $types ), true ); ?> /> <?php esc_html_e( 'Filter Products by Stock Status', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-stock" class="separator">
        <ul>
            <li value=""><label><input type="radio" name="product_filter_stock" value="" <?php checked( $types, false ); ?> /><?php esc_html_e( 'Include both', 'woocommerce-exporter' ); ?></label></li>
            <li value="instock"><label><input type="radio" name="product_filter_stock" value="instock" <?php checked( $types, 'instock' ); ?> /><?php esc_html_e( 'In stock', 'woocommerce-exporter' ); ?></label></li>
            <li value="outofstock"><label><input type="radio" name="product_filter_stock" value="outofstock" <?php checked( $types, 'outofstock' ); ?> /><?php esc_html_e( 'Out of stock', 'woocommerce-exporter' ); ?></label></li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Stock Status\'s you want to filter exported Products by. Default is to include all Stock Status\'s.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-stock -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Stock Quantity widget on Store Exporter screen
 */
function woo_ce_products_filter_by_stock_quantity() {

    $quantity = false;
    $operator = false;
    $types    = woo_ce_get_option( 'product_quantity', false );
    // Separate the operator from the value.
    if ( $types !== false ) {
        $types    = htmlspecialchars_decode( $types );
        $quantity = str_replace( array( '!=', '=', '>', '>=', '<', '<=' ), '', $types );
        $operator = preg_replace( '/[0-9]+/', '', $types );
        if ( $quantity == '' ) {
            $types = false;
        }
    }

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Products by Stock Quantity', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=quickexportproductfilterlink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-products-filters-quantity" class="separator">
        <ul>
            <li>
                <input type="number" size="10" maxlength="10" id="product_quantity" name="product_filter_quantity" value="<?php echo esc_attr( $quantity ); ?>" class="text code" />
                <select id="product_quantity_operator" name="product_filter_quantity_operator">
                    <option value="=" <?php selected( $operator, '=' ); ?>><?php esc_html_e( 'Equal', 'woocommerce-exporter' ); ?></option>
                    <option value="!=" <?php selected( $operator, '!=' ); ?>><?php esc_html_e( 'Not equal', 'woocommerce-exporter' ); ?></option>
                    <option value=">" <?php selected( $operator, '>' ); ?>><?php esc_html_e( 'Greater than', 'woocommerce-exporter' ); ?></option>
                    <option value=">=" <?php selected( $operator, '>=' ); ?>><?php esc_html_e( 'Greater than or equal to', 'woocommerce-exporter' ); ?></option>
                    <option value="<" <?php selected( $operator, '<' ); ?>><?php esc_html_e( 'Less than', 'woocommerce-exporter' ); ?></option>
                    <option value="<=" <?php selected( $operator, '<=' ); ?>><?php esc_html_e( 'Less than or equal to', 'woocommerce-exporter' ); ?></option>
                </select>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Stock Quantity options you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-quantity -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Featured widget on Store Exporter screen
 */
function woo_ce_products_filter_by_featured() {

    $types = woo_ce_get_option( 'product_featured', false );

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Products by Featured', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=quickexportproductfilterlink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-products-filters-featured" class="separator">
        <ul>
            <li value=""><label><input type="radio" name="product_filter_featured" value="" <?php checked( $types, false ); ?> /><?php esc_html_e( 'Include both', 'woocommerce-exporter' ); ?></label></li>
            <li value="yes"><label><input type="radio" name="product_filter_featured" value="yes" <?php checked( $types, 'yes' ); ?> /><?php esc_html_e( 'Featured', 'woocommerce-exporter' ); ?></label></li>
            <li value="no"><label><input type="radio" name="product_filter_featured" value="no" <?php checked( $types, 'no' ); ?> /><?php esc_html_e( 'Not featured', 'woocommerce-exporter' ); ?></label></li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Featured state you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-featured -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Shipping Classes widget on Store Exporter screen
 */
function woo_ce_products_filter_by_shipping_class() {

    if ( apply_filters( 'woo_ce_override_products_filter_by_shipping_class', true ) == false ) {
        return;
    }

    $shipping_classes = woo_ce_get_shipping_classes();

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Products by Shipping Class', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=quickexportproductfilterlink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-products-filters-shipping_class" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $shipping_classes ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Shipping Class...', 'woocommerce-exporter' ); ?>" name="product_filter_shipping_class[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $shipping_classes as $shipping_class ) { ?>
                            <option value="<?php echo esc_attr( $shipping_class->term_id ); ?>" <?php disabled( $shipping_class->count, 0 ); ?>><?php echo esc_html( $shipping_class->name ); ?> (<?php echo esc_html( $shipping_class->count ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Shipping Classes were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Shipping Class you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-shipping_class -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Featured Image widget on Store Exporter screen
 */
function woo_ce_products_filter_by_featured_image() {

    $types = woo_ce_get_option( 'product_featured_image', false );

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Products by Featured Image', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=quickexportproductfilterlink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-products-filters-featured-image" class="separator">
        <ul>
            <li value=""><label><input type="radio" name="product_filter_featured_image" value="" <?php checked( $types, false ); ?> /><?php esc_html_e( 'Include both', 'woocommerce-exporter' ); ?></label></li>
            <li value="yes"><label><input type="radio" name="product_filter_featured_image" value="yes" <?php checked( $types, 'yes' ); ?> /><?php esc_html_e( 'With Featured Image', 'woocommerce-exporter' ); ?></label></li>
            <li value="no"><label><input type="radio" name="product_filter_featured_image" value="no" <?php checked( $types, 'no' ); ?> /><?php esc_html_e( 'Without Featured Image', 'woocommerce-exporter' ); ?></label></li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Featured Image state you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-featured-image -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Product Gallery widget on Store Exporter screen
 */
function woo_ce_products_filter_by_product_gallery() {

    $types = woo_ce_get_option( 'product_gallery', false );

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Products by Product Gallery', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=quickexportproductfilterlink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-products-filters-gallery" class="separator">
        <ul>
            <li value=""><label><input type="radio" name="product_filter_product_gallery" value="" <?php checked( $types, false ); ?> /><?php esc_html_e( 'Include both', 'woocommerce-exporter' ); ?></label></li>
            <li value="yes"><label><input type="radio" name="product_filter_product_gallery" value="yes" <?php checked( $types, 'yes' ); ?> /><?php esc_html_e( 'With Product Gallery images', 'woocommerce-exporter' ); ?></label></li>
            <li value="no"><label><input type="radio" name="product_filter_product_gallery" value="no" <?php checked( $types, 'no' ); ?> /><?php esc_html_e( 'Without Product Gallery images', 'woocommerce-exporter' ); ?></label></li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Product Gallery state you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-gallery -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Date Modified widget on Store Exporter screen
 */
function woo_ce_products_filter_by_date_modified() {

    $tomorrow                       = date( 'l', strtotime( 'tomorrow', current_time( 'timestamp' ) ) );
    $today                          = date( 'l', current_time( 'timestamp' ) );
    $yesterday                      = date( 'l', strtotime( '-1 days', current_time( 'timestamp' ) ) );
    $current_month                  = date( 'F', current_time( 'timestamp' ) );
    $last_month                     = date( 'F', mktime( 0, 0, 0, date( 'n', current_time( 'timestamp' ) ) - 1, 1, date( 'Y', current_time( 'timestamp' ) ) ) );
    $current_year                   = date( 'Y', current_time( 'timestamp' ) );
    $last_year                      = date( 'Y', strtotime( '-1 year', current_time( 'timestamp' ) ) );
    $modified_dates_variable        = woo_ce_get_option( 'modified_dates_filter_variable', '' );
    $modified_dates_variable_length = woo_ce_get_option( 'modified_dates_filter_variable_length', '' );
    $date_format                    = 'd/m/Y';
    $types                          = woo_ce_get_option( 'product_dates' );
    $product_dates_from             = woo_ce_get_option( 'product_dates_from' );
    $product_dates_to               = woo_ce_get_option( 'product_dates_to' );
    // Check if the Product Modified Date To/From have been saved.
    if ( empty( $product_dates_from ) || empty( $product_dates_to ) ) {
        if ( empty( $product_dates_from ) ) {
            $product_dates_from = woo_ce_get_product_first_date();
        }
        if ( empty( $product_dates_to ) ) {
            $product_dates_to = date( $date_format );
        }
    }

    ob_start();
    ?>
    <p><label><input type="checkbox" id="products-filters-date_modified" name="product_filter_dates_include" <?php checked( ! empty( $types ), true ); ?> /> <?php esc_html_e( 'Filter Products by Date Modified', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-date_modified" class="separator">
        <ul>
            <li>
                <label><input type="radio" name="product_filter_dates" value="" <?php checked( $types, false ); ?> /> <?php esc_html_e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $product_dates_from ); ?> - <?php echo esc_html( $product_dates_to ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_dates" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> /> <?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $tomorrow ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_dates" value="today" <?php checked( $types, 'today' ); ?> /> <?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $today ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_dates" value="yesterday" <?php checked( $types, 'yesterday' ); ?> /> <?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $yesterday ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_dates" value="current_week" <?php checked( $types, 'current_week' ); ?> /> <?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_dates" value="last_week" <?php checked( $types, 'last_week' ); ?> /> <?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_dates" value="current_month" <?php checked( $types, 'current_month' ); ?> /> <?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_month ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_dates" value="last_month" <?php checked( $types, 'last_month' ); ?> /> <?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_month ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_dates" value="current_year" <?php checked( $types, 'current_year' ); ?> /> <?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_dates" value="last_year" <?php checked( $types, 'last_year' ); ?> /> <?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_dates" value="variable" <?php checked( $types, 'variable' ); ?> /> <?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?>
                    <input type="text" name="modified_dates_filter_variable" class="text code" size="4" maxlength="4" value="<?php echo esc_attr( $modified_dates_variable ); ?>" />
                    <select name="modified_dates_filter_variable_length" style="vertical-align:top;">
                        <option value="" <?php selected( $modified_dates_variable_length, '' ); ?>>&nbsp;</option>
                        <option value="second" <?php selected( $modified_dates_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="minute" <?php selected( $modified_dates_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="hour" <?php selected( $modified_dates_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="day" <?php selected( $modified_dates_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="week" <?php selected( $modified_dates_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="month" <?php selected( $modified_dates_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="year" <?php selected( $modified_dates_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
                    </select>
                </div>
            </li>
            <li>
                <label><input type="radio" name="product_filter_dates" value="manual" <?php checked( $types, 'manual' ); ?> /> <?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <input type="text" size="10" maxlength="10" id="product_dates_from" name="product_dates_from" value="<?php echo esc_attr( $product_dates_from ); ?>" class="text code datepicker product_export" /> to <input type="text" size="10" maxlength="10" id="product_dates_to" name="product_dates_to" value="<?php echo esc_attr( $product_dates_to ); ?>" class="text code datepicker product_export" />
                    <p class="description"><?php esc_html_e( 'Filter the dates of Products to be included in the export. Default is the date of the first Product Modified to today in the date format <code>DD/MM/YYYY</code>.', 'woocommerce-exporter' ); ?></p>
                </div>
            </li>
        </ul>
    </div>
    <!-- #export-products-filters-date_modified -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Products by Date Published widget on Store Exporter screen
 */
function woo_ce_products_filter_by_date_published() {

    $tomorrow                        = date( 'l', strtotime( 'tomorrow', current_time( 'timestamp' ) ) );
    $today                           = date( 'l', current_time( 'timestamp' ) );
    $yesterday                       = date( 'l', strtotime( '-1 days', current_time( 'timestamp' ) ) );
    $current_month                   = date( 'F', current_time( 'timestamp' ) );
    $last_month                      = date( 'F', mktime( 0, 0, 0, date( 'n', current_time( 'timestamp' ) ) - 1, 1, date( 'Y', current_time( 'timestamp' ) ) ) );
    $current_year                    = date( 'Y', current_time( 'timestamp' ) );
    $last_year                       = date( 'Y', strtotime( '-1 year', current_time( 'timestamp' ) ) );
    $published_dates_variable        = woo_ce_get_option( 'published_dates_filter_variable', '' );
    $published_dates_variable_length = woo_ce_get_option( 'published_dates_filter_variable_length', '' );
    $date_format                     = 'd/m/Y';
    $types                           = woo_ce_get_option( 'product_published_dates' );
    $product_dates_from              = woo_ce_get_option( 'product_published_dates_from' );
    $product_dates_to                = woo_ce_get_option( 'product_published_dates_to' );
    // Check if the Product Published Date To/From have been saved.
    if ( empty( $product_dates_from ) || empty( $product_dates_to ) ) {
        if ( empty( $product_dates_from ) ) {
            $product_dates_from = woo_ce_get_product_first_date();
        }
        if ( empty( $product_dates_to ) ) {
            $product_dates_to = date( $date_format );
        }
    }

    ob_start();
    ?>
    <p><label><input type="checkbox" id="products-filters-date_published" name="product_filter_published_dates_include" <?php checked( ! empty( $types ), true ); ?> /> <?php esc_html_e( 'Filter Products by Date Published', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-date_published" class="separator">
        <ul>
            <li>
                <label><input type="radio" name="product_filter_published_dates" value="" <?php checked( $types, false ); ?> /> <?php esc_html_e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $product_dates_from ); ?> - <?php echo esc_html( $product_dates_to ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_published_dates" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> /> <?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $tomorrow ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_published_dates" value="today" <?php checked( $types, 'today' ); ?> /> <?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $today ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_published_dates" value="yesterday" <?php checked( $types, 'yesterday' ); ?> /> <?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $yesterday ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_published_dates" value="current_week" <?php checked( $types, 'current_week' ); ?> /> <?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_published_dates" value="last_week" <?php checked( $types, 'last_week' ); ?> /> <?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_published_dates" value="current_month" <?php checked( $types, 'current_month' ); ?> /> <?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_month ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_published_dates" value="last_month" <?php checked( $types, 'last_month' ); ?> /> <?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_month ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_published_dates" value="current_year" <?php checked( $types, 'current_year' ); ?> /> <?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_published_dates" value="last_year" <?php checked( $types, 'last_year' ); ?> /> <?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="product_filter_published_dates" value="variable" <?php checked( $types, 'variable' ); ?> /> <?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?>
                    <input type="text" name="published_dates_filter_variable" class="text code" size="4" maxlength="4" value="<?php echo esc_attr( $published_dates_variable ); ?>" />
                    <select name="published_dates_filter_variable_length" style="vertical-align:top;">
                        <option value="" <?php selected( $published_dates_variable_length, '' ); ?>>&nbsp;</option>
                        <option value="second" <?php selected( $published_dates_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="minute" <?php selected( $published_dates_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="hour" <?php selected( $published_dates_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="day" <?php selected( $published_dates_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="week" <?php selected( $published_dates_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="month" <?php selected( $published_dates_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="year" <?php selected( $published_dates_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
                    </select>
                </div>
            </li>
            <li>
                <label><input type="radio" name="product_filter_published_dates" value="manual" <?php checked( $types, 'manual' ); ?> /> <?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <input type="text" size="10" maxlength="10" id="product_published_dates_from" name="product_published_dates_from" value="<?php echo esc_attr( $product_dates_from ); ?>" class="text code datepicker product_export" /> to <input type="text" size="10" maxlength="10" id="product_published_dates_to" name="product_published_dates_to" value="<?php echo esc_attr( $product_dates_to ); ?>" class="text code datepicker product_export" />
                    <p class="description"><?php esc_html_e( 'Filter the dates of Products to be included in the export. Default is the date of the first Product Published to today in the date format <code>DD/MM/YYYY</code>.', 'woocommerce-exporter' ); ?></p>
                </div>
            </li>
        </ul>
    </div>
    <!-- #export-products-filters-date_published -->
<?php
    ob_end_flush();
}

/**
 * Filters products by product meta.
 *
 * This function retrieves the custom products from the options and checks if any of them have a corresponding custom meta option.
 * If a custom meta option is found, it retrieves the types associated with it.
 * It then generates HTML markup for displaying checkboxes and input fields for each custom product and its associated types.
 */
function woo_ce_products_filter_by_product_meta() {

    $custom_products = woo_ce_get_option( 'custom_products', '' );
    if ( empty( $custom_products ) ) {
        return;
    }
    foreach ( $custom_products as $custom_product ) {
        $custom_meta = sprintf( 'product_filter_custom_meta-%s', esc_attr( $custom_product ) );
        if ( woo_ce_get_option( $custom_meta ) ) {
            $types = woo_ce_get_option( $custom_meta );
            break;
        }
    }
    $size = count( $custom_products );
    if ( $size == 1 && empty( $custom_products[0] ) ) {
        return;
    }

    ob_start();
    ?>
    <p><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Products by Product meta', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-product_meta" class="separator">
        <ul>
            <?php foreach ( $custom_products as $custom_product ) { ?>
                <?php if ( ! empty( $custom_product ) ) { ?>
                    <?php $types = woo_ce_get_option( sprintf( 'product_filter_custom_meta-%s', esc_attr( $custom_product ) ) ); ?>
                    <li>
                        <?php echo esc_html( $custom_product ); ?>:<br />
                        <input type="text" id="product_filter_custom_meta-<?php echo esc_attr( $custom_product ); ?>" name="product_filter_custom_meta-<?php echo esc_attr( $custom_product ); ?>" value="<?php echo ( ! empty( $types ) ? esc_html( $types ) : '' ); ?>" class="text code" style="width:95%;">
                    </li>
                <?php } ?>
            <?php } ?>
        </ul>
    </div>
    <!-- #export-products-filters-product_meta -->
<?php
    ob_end_flush();
}

/**
 * HTML template for jump link to Custom Product Fields within Product Options on Store Exporter screen
 */
function woo_ce_products_custom_fields_link() {

    ob_start();
    ?>
    <div id="export-products-custom-fields-link">
        <p><a href="#export-products-custom-fields"><?php esc_html_e( 'Manage Custom Product Fields', 'woocommerce-exporter' ); ?></a></p>
    </div>
    <!-- #export-products-custom-fields-link -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Product Sorting widget on Store Exporter screen
 */
function woo_ce_product_sorting() {

    $product_orderby = woo_ce_get_option( 'product_orderby', 'ID' );
    $product_order   = woo_ce_get_option( 'product_order', 'ASC' );

    ob_start();
    ?>
    <p><label><?php esc_html_e( 'Product Sorting', 'woocommerce-exporter' ); ?></label></p>
    <div>
        <select name="product_orderby">
            <option value="ID" <?php selected( 'ID', $product_orderby ); ?>><?php esc_html_e( 'Product ID', 'woocommerce-exporter' ); ?></option>
            <option value="title" <?php selected( 'title', $product_orderby ); ?>><?php esc_html_e( 'Product Name', 'woocommerce-exporter' ); ?></option>
            <option value="sku" <?php selected( 'sku', $product_orderby ); ?>><?php esc_html_e( 'Product SKU', 'woocommerce-exporter' ); ?></option>
            <option value="date" <?php selected( 'date', $product_orderby ); ?>><?php esc_html_e( 'Date Published', 'woocommerce-exporter' ); ?></option>
            <option value="modified" <?php selected( 'modified', $product_orderby ); ?>><?php esc_html_e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
            <option value="rand" <?php selected( 'rand', $product_orderby ); ?>><?php esc_html_e( 'Random', 'woocommerce-exporter' ); ?></option>
            <option value="menu_order" <?php selected( 'menu_order', $product_orderby ); ?>><?php esc_html_e( 'Sort Order', 'woocommerce-exporter' ); ?></option>
        </select>
        <select name="product_order">
            <option value="ASC" <?php selected( 'ASC', $product_order ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
            <option value="DESC" <?php selected( 'DESC', $product_order ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Select the sorting of Products within the exported file. By default this is set to export Products by Product ID in Desending order.', 'woocommerce-exporter' ); ?></p>
    </div>
<?php
    ob_end_flush();
}

/**
 * HTML template for Product Sorting widget on Store Exporter screen
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_product_multi_level_sorting( $post_ID = 0 ) {

    $product_orderby_field1 = woo_ce_get_option( 'product_orderby_field1', '' );
    $product_orderby_field2 = woo_ce_get_option( 'product_orderby_field2', '' );
    $product_order_field1   = woo_ce_get_option( 'product_order_field1', '' );
    $product_order_field2   = woo_ce_get_option( 'product_order_field2', '' );
    $fields                 = woo_ce_get_product_fields( 'full', $post_ID );

    ob_start();
    ?>
    <p><label><input type="checkbox" id="products-filters-custom_field_sorting" <?php checked( ! empty( $product_orderby_field1 ), true ); ?> /> <?php esc_html_e( 'Custom Multi-Level Field Sorting - Replaces product sorting above (CSV, TSV, XLS, XLSX)', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-products-filters-custom_field_sorting">
        <p class="description"><?php esc_html_e( 'Choose field for first level of sorting.', 'woocommerce-exporter' ); ?></p>
        <select name="product_orderby_field1">
            <option value="" <?php selected( '', $product_orderby_field1 ); ?>></option>
            <?php
            foreach ( $fields as $field ) {
                $field_name  = $field['name'];
                $field_label = $field['label'];
                ?>
                <option value="<?php echo esc_attr( $field_name ); ?>" <?php selected( $field_name, $product_orderby_field1 ); ?> ><?php echo esc_html( $field_label ); ?></option>
            <?php
            }
            ?>
        </select>
        <select name="product_order_field1">
            <option value="ASC" <?php selected( 'ASC', $product_order_field1 ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
            <option value="DESC" <?php selected( 'DESC', $product_order_field1 ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Choose field for second level of sorting.', 'woocommerce-exporter' ); ?></p>
        <select name="product_orderby_field2">
            <option value="" <?php selected( '', $product_orderby_field2 ); ?>></option>
            <?php
            foreach ( $fields as $field ) {
                $field_name  = $field['name'];
                $field_label = $field['label'];
                ?>
                <option value="<?php echo esc_attr( $field_name ); ?>" <?php selected( $field_name, $product_orderby_field2 ); ?> ><?php echo esc_html( $field_label ); ?></option>
            <?php
            }
            ?>
        </select>
        <select name="product_order_field2">
            <option value="ASC" <?php selected( 'ASC', $product_order_field2 ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
            <option value="DESC" <?php selected( 'DESC', $product_order_field2 ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Select the sorting of Products within the exported file. By default this is set to export Products by Product ID in Desending order.', 'woocommerce-exporter' ); ?></p>
    </div>
<?php
    ob_end_flush();
}

/**
 * HTML template for Grouped Products formatting on Store Exporter screen
 */
function woo_ce_products_grouped_formatting() {

    $grouped_formatting = woo_ce_get_option( 'grouped_formatting', 1 );

    ob_start();
    ?>
    <tr class="export-options product-options">
        <th><label for=""><?php esc_html_e( 'Grouped Product formatting', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <label><input type="radio" name="product_grouped_formatting" value="0" checked="checked" />&nbsp;<?php esc_html_e( 'Export Grouped Products as Product ID', 'woocommerce-exporter' ); ?></label><br />
            <label>
                <input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Export Grouped Products as Product SKU', 'woocommerce-exporter' ); ?>
                <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=productoptionsgroupedformattinglink' ) ) ); ?></span>
            </label><br />
            <label>
                <input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Export Grouped Products as Product Name', 'woocommerce-exporter' ); ?>
                <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=productoptionsgroupedformattinglink' ) ) ); ?></span>
            </label>
            <p class="description"><?php esc_html_e( 'Choose the Grouped Product formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>

<?php
    ob_end_flush();
}

/**
 * HTML template for Up-sells formatting on Store Exporter screen
 */
function woo_ce_products_upsell_formatting() {

    $upsell_formatting = woo_ce_get_option( 'upsell_formatting', 1 );

    ob_start();
    ?>
    <tr class="export-options product-options">
        <th><label for=""><?php esc_html_e( 'Up-sells formatting', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <label><input type="radio" name="product_upsell_formatting" value="0" <?php checked( $upsell_formatting, 0 ); ?> />&nbsp;<?php esc_html_e( 'Export Up-Sells as Product ID', 'woocommerce-exporter' ); ?></label><br />
            <label><input type="radio" name="product_upsell_formatting" value="1" <?php checked( $upsell_formatting, 1 ); ?> />&nbsp;<?php esc_html_e( 'Export Up-Sells as Product SKU', 'woocommerce-exporter' ); ?></label>
            <p class="description"><?php esc_html_e( 'Choose the up-sell formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>

<?php
    ob_end_flush();
}

/**
 * HTML template for Cross-sells formatting on Store Exporter screen
 */
function woo_ce_products_crosssell_formatting() {

    $crosssell_formatting = woo_ce_get_option( 'crosssell_formatting', 1 );

    ob_start();
    ?>
    <tr class="export-options product-options">
        <th><label for=""><?php esc_html_e( 'Cross-sells formatting', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <label><input type="radio" name="product_crosssell_formatting" value="0" <?php checked( $crosssell_formatting, 0 ); ?> />&nbsp;<?php esc_html_e( 'Export Cross-Sells as Product ID', 'woocommerce-exporter' ); ?></label><br />
            <label><input type="radio" name="product_crosssell_formatting" value="1" <?php checked( $crosssell_formatting, 1 ); ?> />&nbsp;<?php esc_html_e( 'Export Cross-Sells as Product SKU', 'woocommerce-exporter' ); ?></label>
            <p class="description"><?php esc_html_e( 'Choose the cross-sell formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>

<?php
    ob_end_flush();
}

/**
 * HTML template for Variation formatting on Store Exporter screen
 */
function woo_ce_products_variation_formatting() {

    $variation_formatting = woo_ce_get_option( 'variation_formatting', 0 );

    ob_start();
    ?>
    <tr class="export-options product-options">
        <th><label for=""><?php esc_html_e( 'Variation formatting', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <label>
                <input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Leave empty Variant details intact', 'woocommerce-exporter' ); ?>
                <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=productoptionsvariationformattinglink' ) ) ); ?></span>
            </label><br />
            <label>
                <input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Default Variant details to Parent Product', 'woocommerce-exporter' ); ?>
                <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=productoptionsvariationformattinglink' ) ) ); ?></span>
            </label>
            <p class="description"><?php esc_html_e( 'Choose the default formatting rule that is applied to Product Variations.', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>

<?php
    ob_end_flush();
}

/**
 * Function to display the Description/Excerpt formatting options in the admin panel.
 *
 * This function outputs the HTML markup for the Description/Excerpt formatting options
 * in the WooCommerce Store Exporter Deluxe plugin settings page.
 */
function woo_ce_products_description_excerpt_formatting() {

    $description_excerpt_formatting = woo_ce_get_option( 'description_excerpt_formatting', 0 );

    ob_start();
    ?>
    <tr class="export-options product-options category-options tag-options order-options">
        <th><label for=""><?php esc_html_e( 'Description/Excerpt formatting', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <label>
                <input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Leave HTML tags from Description/Excerpt intact', 'woocommerce-exporter' ); ?>
                <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=productoptionsdescexcerptformattinglink' ) ) ); ?></span>
            </label><br />
            <label>
                <input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Strip HTML tags from Description/Excerpt', 'woocommerce-exporter' ); ?>
                <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=productoptionsdescexcerptformattinglink' ) ) ); ?></span>
            </label>
            <p class="description"><?php esc_html_e( 'Choose the HTML tag formatting rule that is applied to the Description/Excerpt within the Product, Category, Tag, Brand and Order export.', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>
<?php
    ob_end_flush();
}

/**
 * HTML template for Custom Products widget on Store Exporter screen
 */
function woo_ce_products_custom_fields() {

    if ( $custom_products = woo_ce_get_option( 'custom_products', '' ) ) {
        $custom_products = implode( "\n", $custom_products );
    }
    if ( $custom_attributes = woo_ce_get_option( 'custom_attributes', '' ) ) {
        $custom_attributes = implode( "\n", $custom_attributes );
    }

    $troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

    ob_start();
    ?>
    <form method="post" id="export-products-custom-fields" class="export-options product-options">
        <div id="poststuff">

            <div class="postbox" id="export-options product-options">
                <h3 class="hndle"><?php esc_html_e( 'Custom Product Fields', 'woocommerce-exporter' ); ?></h3>
                <div class="inside">
                    <p class="description"><?php esc_html_e( 'To include additional custom Product meta or custom Product Attributes in the list of available export fields above fill the meta text box then click Save Custom Fields. The saved custom fields will appear as export fields to be selected from the Product Fields list.', 'woocommerce-exporter' ); ?></p>
                    <table class="form-table">
                        <tr>
                            <th>
                                <label for="custom_products"><?php esc_html_e( 'Product meta', 'woocommerce-exporter' ); ?></label>
                            </th>
                            <td>
                                <textarea id="custom_products" name="custom_products" rows="5" cols="70"><?php echo esc_textarea( $custom_products ); ?></textarea>
                                <p class="description">
                                    <?php echo wp_kses_post( __( 'Include additional custom Product meta in your export file by adding each custom Product meta name to a new line above.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ) ); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label for="custom_attributes"><?php esc_html_e( 'Custom Product attribute', 'woocommerce-exporter' ); ?></label>
                            </th>
                            <td>
                                <textarea disabled="disabled" rows="5" cols="70"><?php echo esc_textarea( $custom_attributes ); ?></textarea>
                                <p class="description">
                                    <?php echo wp_kses_post( __( 'Include custom Product Attributes in your export file by adding each custom Product Attribute name to a new line above.', 'woocommerce-exporter' ) ); ?><br />
                                    <?php echo wp_kses_post( __( 'Enter each custom Product Attribute name in lowercase and replace spaces with dashes, e.g. Size becomes size or Sample Attribute becomes sample-attribute<br />For example: <code>condition</code> (new line) <code>colour</code>', 'woocommerce-exporter' ) ); ?>
                                    <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=productoptionsdescexcerptformattinglink' ) ) ); ?></span>
                                </p>
                            </td>
                        </tr>

                        <?php do_action( 'woo_ce_products_custom_fields' ); ?>

                    </table>
                    <p class="description"><?php echo wp_kses_post( sprintf( __( 'For more information on exporting custom Product meta and Attributes consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ) ); ?></p>
                    <p class="submit">
                        <input type="submit" value="<?php esc_attr_e( 'Save Custom Fields', 'woocommerce-exporter' ); ?>" class="button" />
                    </p>
                </div>
                <!-- .inside -->
            </div>
            <!-- .postbox -->

        </div>
        <!-- #poststuff -->
        <input type="hidden" name="action" value="update" />
    </form>
    <!-- #export-products-custom-fields -->
<?php
    ob_end_flush();
}

/**
 * Renders the export options for product image formatting.
 *
 * This function generates the HTML markup for the export options related to product image formatting.
 * It displays radio buttons for selecting the desired image formatting option.
 */
function woo_ce_export_options_featured_image_formatting() {

    $product_image_formatting = woo_ce_get_option( 'product_image_formatting', 1 );

    ob_start();
    ?>
    <tr class="export-options product-options">
        <th><label for=""><?php esc_html_e( 'Product image formatting', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <label><input type="radio" name="product_image_formatting" value="0" checked />&nbsp;<?php esc_html_e( 'Export Product Image as Attachment ID', 'woocommerce-exporter' ); ?></label><br />
            <label>
                <input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Export Product Image as Image URL', 'woocommerce-exporter' ); ?>
                <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=productoptionsimageformattinglink' ) ) ); ?></span>
            </label><br />
            <label>
                <input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Export Product Image as Image filepath', 'woocommerce-exporter' ); ?>
                <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=productoptionsimageformattinglink' ) ) ); ?></span>
            </label>
            <p class="description"><?php esc_html_e( 'Choose the featured image formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>
<?php
    ob_end_flush();
}

/**
 * Renders the export options for product gallery formatting and max unique product gallery images.
 *
 * This function generates the HTML markup for the export options related to product gallery formatting and max unique product gallery images.
 * It displays radio buttons for selecting the desired product gallery formatting option and an input field for managing the number of product gallery columns.
 */
function woo_ce_export_options_product_gallery_formatting() {

    $gallery_formatting = woo_ce_get_option( 'gallery_formatting', 1 );
    $gallery_unique     = woo_ce_get_option( 'gallery_unique', 0 );
    $max_size           = woo_ce_get_option( 'max_product_gallery', 3 );

    ob_start();
    ?>
    <tr class="export-options product-options">
        <th><label for=""><?php esc_html_e( 'Product gallery formatting', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <label><input type="radio" name="product_gallery_formatting" value="0" checked />&nbsp;<?php esc_html_e( 'Export Product Gallery as Attachment ID', 'woocommerce-exporter' ); ?></label><br />
            <label>
                <input type="radio" name="product_gallery_formatting" value="1" disabled="disabled" />&nbsp;<?php esc_html_e( 'Export Product Gallery as Image URL', 'woocommerce-exporter' ); ?>
                <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=productoptionsgalleryformattingurllink' ) ) ); ?></span>
            </label><br />
            <label>
                <input type="radio" name="product_gallery_formatting" value="2" disabled="disabled" />&nbsp;<?php esc_html_e( 'Export Product Gallery as Image filepath', 'woocommerce-exporter' ); ?>
                <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=productoptionsgalleryformattingurllink' ) ) ); ?></span>
            </label>
            <hr />
            <label><input type="radio" name="product_gallery_unique" value="0" <?php checked( $gallery_unique, 0 ); ?> />&nbsp;<?php esc_html_e( 'Export Product Gallery as a single combined image cell', 'woocommerce-exporter' ); ?></label><br />
            <label>
                <input type="radio" name="product_gallery_unique" value="1" disabled="disabled" />&nbsp;<?php esc_html_e( 'Export Product Gallery as individual image cells', 'woocommerce-exporter' ); ?>
                <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=productoptionsgalleryformattingurllink' ) ) ); ?></span>
            </label>
            <p class="description"><?php esc_html_e( 'Choose the product gallery formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>
    <tr id="max_product_gallery_option" class="export-options product-options">
        <th><label for=""><?php esc_html_e( 'Max unique Product Gallery images', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <input type="text" id="max_product_gallery" name="max_product_gallery" size="3" class="text" value="<?php echo esc_attr( $max_size ); ?>" />
            <p class="description"><?php esc_html_e( 'Manage the number of Product Gallery colums displayed when the \'Export Product Gallery as individual image cells\' Product gallery formatting option is selected.', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>
<?php
    ob_end_flush();
}

/**
 * Renders the export options for a product in the WooCommerce Store Exporter Deluxe plugin.
 *
 * @param int $post_ID The ID of the product post.
 */
function woo_ce_scheduled_export_filters_product( $post_ID = 0 ) {

    ob_start();
    ?>
    <div class="export-options product-options">

        <?php do_action( 'woo_ce_scheduled_export_filters_product', $post_ID ); ?>

    </div>
    <!-- .product-options -->

<?php
    ob_end_flush();
}

/**
 * Displays the form field for filtering products by product category in the scheduled export settings.
 *
 * @param int $post_ID The ID of the post being edited.
 */
function woo_ce_scheduled_export_product_filter_by_product_category( $post_ID = 0 ) {

    $args               = array(
        'hide_empty' => 1,
    );
    $product_categories = false;
    if ( apply_filters( 'woo_ce_override_products_filter_by_product_category', true ) ) {
        $product_categories = woo_ce_get_product_categories( $args );
    }
    $types = get_post_meta( $post_ID, '_filter_product_category', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="product_filter_category"><?php esc_html_e( 'Product category', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $product_categories ) ) { ?>
            <select id="product_filter_category" data-placeholder="<?php esc_attr_e( 'Choose a Product Category...', 'woocommerce-exporter' ); ?>" name="product_filter_category[]" multiple class="chzn-select select short" style="width:95%;">
                <?php foreach ( $product_categories as $product_category ) { ?>
                    <option value="<?php echo esc_attr( $product_category->term_id ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $product_category->term_id, $types ) : false ), true ); ?><?php disabled( $product_category->count, 0 ); ?>><?php echo esc_html( woo_ce_format_product_category_label( $product_category->name, $product_category->parent_name ) ); ?> (<?php echo wp_kses_post( sprintf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_category->term_id ) ); ?>)</option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_attr_e( 'Select the Product Categories you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        <?php } else { ?>
            <?php esc_html_e( 'No Product Categories were found linked to Products.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Displays the form field for filtering products by product tag in the scheduled export settings.
 *
 * @param int $post_ID The ID of the post being edited.
 */
function woo_ce_scheduled_export_product_filter_by_product_tag( $post_ID = 0 ) {

    $args         = array(
        'hide_empty' => 1,
    );
    $product_tags = false;
    if ( apply_filters( 'woo_ce_override_products_filter_by_product_tag', true ) ) {
        $product_tags = woo_ce_get_product_tags( $args );
    }
    $types = get_post_meta( $post_ID, '_filter_product_tag', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="product_filter_tag"><?php esc_html_e( 'Product tag', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $product_tags ) ) { ?>
            <select id="product_filter_tag" data-placeholder="<?php esc_attr_e( 'Choose a Product Tag...', 'woocommerce-exporter' ); ?>" name="product_filter_tag[]" multiple class="chzn-select select short" style="width:95%;">
                <?php foreach ( $product_tags as $product_tag ) { ?>
                    <option value="<?php echo esc_attr( $product_tag->term_id ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $product_tag->term_id, $types ) : false ), true ); ?><?php disabled( $product_tag->count, 0 ); ?>><?php echo esc_html( $product_tag->name ); ?> (<?php echo wp_kses_post( sprintf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_tag->term_id ) ); ?>)</option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_attr_e( 'Select the Product Tag\'s you want to filter exported Products by. Default is to include all Product Tags.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        <?php } else { ?>
            <?php esc_html_e( 'No Product Tags were found linked to Products.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Filter the product statuses for scheduled export.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_product_filter_by_product_status( $post_ID = 0 ) {

    $product_stati = get_post_statuses();
    // Add Trash if it not included in the list.
    if ( ! isset( $product_stati['trash'] ) ) {
        $product_stati['trash'] = __( 'Trash', 'woocommerce-exporter' );
    }

    // Allow Plugin/Theme authors to add support for custom Product Post Stati.
    $product_stati = apply_filters( 'woo_ce_products_filter_post_stati', $product_stati );

    $types = get_post_meta( $post_ID, '_filter_product_status', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="product_filter_status"><?php esc_html_e( 'Product status', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $product_stati ) ) { ?>
            <select id="product_filter_status" data-placeholder="<?php esc_attr_e( 'Choose a Product Status...', 'woocommerce-exporter' ); ?>" name="product_filter_status[]" multiple class="chzn-select" style="width:95%;">
                <?php foreach ( $product_stati as $key => $product_status ) { ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $key, $types ) : false ), true ); ?>><?php echo esc_html( $product_status ); ?></option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_attr_e( 'Select the Product Status\'s you want to filter exported Products by. Default is to include all Product Status\'s.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        <?php } else { ?>
            <?php esc_html_e( 'No Product Status were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Function to display the product type filter field in the admin panel.
 *
 * This function retrieves the list of product types and displays a select field
 * where the user can choose the product types to filter exported products by.
 * The selected product types are saved as post meta for the specified post ID.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_product_filter_by_product_type( $post_ID = 0 ) {

    $product_types = woo_ce_get_product_types();
    $types         = get_post_meta( $post_ID, '_filter_product_type', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="product_filter_type"><?php esc_html_e( 'Product type', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $product_types ) ) { ?>
            <select id="product_filter_type" data-placeholder="<?php esc_attr_e( 'Choose a Product Type...', 'woocommerce-exporter' ); ?>" name="product_filter_type[]" multiple class="chzn-select" style="width:95%;">
                <?php foreach ( $product_types as $key => $product_type ) { ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $key, $types ) : false ), true ); ?>><?php echo esc_html( woo_ce_format_product_type( $product_type['name'] ) ); ?> (<?php echo esc_html( $product_type['count'] ); ?>)</option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_attr_e( 'Select the Product Type\'s you want to filter exported Products by. Default is to include all Product Types except Variations.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        <?php } else { ?>
            <?php esc_html_e( 'No Product Types were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Filters the products for scheduled export by product.
 *
 * @param int $post_ID The post ID.
 */
function woo_ce_scheduled_export_product_filter_by_product( $post_ID = 0 ) {

    $products = false;
    if ( apply_filters( 'woo_ce_override_products_filter_by_sku', true ) ) {
        $post_status = array( 'publish', 'pending', 'future', 'private' );
        $args        = array(
            'product_status' => $post_status,
        );
        $products    = woo_ce_get_products( $args );
    }
    $types   = get_post_meta( $post_ID, '_filter_product_sku', true );
    $exclude = get_post_meta( $post_ID, '_filter_product_sku_exclude', true );
    if ( empty( $exclude ) ) {
        $exclude = false;
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="product_filter_sku"><?php esc_html_e( 'Product', 'woocommerce-exporter' ); ?></label>
        <?php if ( wp_script_is( 'wc-enhanced-select', 'enqueued' ) && apply_filters( 'woo_ce_override_products_filter_by_sku', true ) ) { ?>
            <?php
            $output   = '';
            $json_ids = array();
            if ( ! empty( $types ) ) {
                foreach ( $types as $product_id ) {
                    $product = wc_get_product( $product_id );
                    if ( is_object( $product ) ) {
                        $json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() );
                        $output                 .= '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
                    }
                }
            }
            ?>
            <?php if ( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) { ?>
                <select
                    data-placeholder="<?php esc_attr_e( 'Search for a Product&hellip;', 'woocommerce' ); ?>"
                    id="product_filter_sku"
                    name="product_filter_sku[]"
                    multiple="multiple"
                    class="multiselect wc-product-search"
                    style="width:95%;"
                    data-action="woocommerce_json_search_products_and_variations"
                    data-selected="<?php echo esc_attr( json_encode( $json_ids ) ); ?>"><?php echo esc_attr( $output ); ?></select>
            <?php } else { ?>
                <input
                    data-placeholder="<?php esc_attr_e( 'Search for a Product&hellip;', 'woocommerce-exporter' ); ?>"
                    type="hidden"
                    id="product_filter_sku"
                    name="product_filter_sku[]"
                    class="multiselect wc-product-search"
                    data-multiple="true"
                    style="width:95%;"
                    data-action="woocommerce_json_search_products_and_variations"
                    data-selected="<?php echo esc_attr( json_encode( $json_ids ) ); ?>"
                    value="<?php echo esc_attr( implode( ',', array_keys( $json_ids ) ) ); ?>" />
            <?php } ?>
        <?php } else { ?>
            <?php
            add_filter( 'the_title', 'woo_ce_get_product_title_sku', 10, 2 );
            ?>
            <?php if ( ! empty( $products ) ) { ?>
                <select id="product_filter_sku" data-placeholder="<?php esc_attr_e( 'Choose a Product...', 'woocommerce-exporter' ); ?>" name="product_filter_sku[]" multiple class="chzn-select" style="width:95%;">
                    <?php foreach ( $products as $product ) { ?>
                        <option value="<?php echo esc_attr( $product ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $product, $types ) : false ), true ); ?>><?php echo esc_html( woo_ce_format_post_title( get_the_title( $product ) ) ); ?></option>
                    <?php } ?>
                </select>
            <?php } else { ?>
                <?php esc_html_e( 'No Products were found.', 'woocommerce-exporter' ); ?>
            <?php } ?>
            <?php
            remove_filter( 'the_title', 'woo_ce_get_product_title_sku' );
            ?>
        <?php } ?>
        <img class="help_tip" data-tip="<?php esc_attr_e( 'Select the Product\'s you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
    </p>
    <p class="form-field discount_type_field">
        <label for="product_filter_sku_exclude"><?php esc_html_e( '(continued)', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="product_filter_sku_exclude" value="0" <?php checked( $exclude, false ); ?> /> <?php esc_html_e( 'Include only these selected Products', 'woocommerce-exporter' ); ?></label><br />
        <input type="radio" id="product_filter_sku_exclude" name="product_filter_sku_exclude" value="1" <?php checked( $exclude, 1 ); ?> /> <?php esc_html_e( 'Filter out these selected Products', 'woocommerce-exporter' ); ?></label>
    </p>
<?php
    ob_end_flush();
}

/**
 * Filters the product export by user role.
 *
 * @param int $post_ID The ID of the post being filtered.
 */
function woo_ce_scheduled_export_product_filter_by_user_role( $post_ID = 0 ) {

    $start_time = time();
    $debugging  = apply_filters( 'woo_ce_scheduled_export_filters_product_debugging', false );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_product() - before get_user_roles(): ' . ( time() - $start_time ) ) );
    }

    $user_roles = woo_ce_get_user_roles();
    $types      = get_post_meta( $post_ID, '_filter_product_user_role', true );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_product() - before rendering $user_roles: ' . ( time() - $start_time ) ) );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="product_filter_user_role"><?php esc_html_e( 'User role', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $user_roles ) ) { ?>
            <select id="product_filter_user_role" data-placeholder="<?php esc_attr_e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="product_filter_user_role[]" multiple class="chzn-select" style="width:95%;">
                <?php foreach ( $user_roles as $key => $user_role ) { ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo esc_html( ucfirst( $user_role['name'] ) ); ?></option>
                <?php } ?>
            </select>
        <?php } else { ?>
            <?php esc_html_e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Renders the HTML for the shipping class filter field in the product settings.
 *
 * @param int $post_ID The ID of the product post.
 */
function woo_ce_scheduled_export_product_filter_by_shipping_class( $post_ID = 0 ) {

    $shipping_classes = woo_ce_get_shipping_classes();
    $types            = get_post_meta( $post_ID, '_filter_product_shipping_class', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="product_filter_shipping_class"><?php esc_html_e( 'Shipping class', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $shipping_classes ) ) { ?>
            <select id="product_filter_shipping_class" data-placeholder="<?php esc_attr_e( 'Choose a Shipping Class...', 'woocommerce-exporter' ); ?>" name="product_filter_shipping_class[]" multiple class="chzn-select" style="width:95%;">
                <?php foreach ( $shipping_classes as $shipping_class ) { ?>
                    <option value="<?php echo esc_attr( $shipping_class->term_id ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $shipping_class->term_id, $types ) : false ), true ); ?><?php disabled( $shipping_class->count, 0 ); ?>><?php echo esc_html( $shipping_class->name ); ?> (<?php echo esc_html( $shipping_class->count ); ?>)</option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_attr_e( 'Select the Shipping Class\'s you want to filter exported Products by. Default is to include all Shipping Classes.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        <?php } else { ?>
            <?php esc_html_e( 'No Shipping Classes were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Displays the form field for filtering products by modified date.
 *
 * This function is responsible for rendering the HTML form field that allows users to filter products based on their modified date.
 *
 * @param int $post_ID The ID of the post being edited. Default is 0.
 */
function woo_ce_scheduled_export_product_filter_by_date_modified( $post_ID = 0 ) {

    $types                         = get_post_meta( $post_ID, '_filter_product_date', true );
    $product_filter_dates_from     = get_post_meta( $post_ID, '_filter_product_dates_from', true );
    $product_filter_dates_to       = get_post_meta( $post_ID, '_filter_product_dates_to', true );
    $modified_date_variable        = get_post_meta( $post_ID, '_modified_date_variable', true );
    $modified_date_variable_length = get_post_meta( $post_ID, '_modified_date_variable_length', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="product_filter_date"><?php esc_html_e( 'Date modified', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="product_filter_dates" value="" <?php checked( $types, false ); ?> />&nbsp;<?php esc_html_e( 'All', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_dates" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> />&nbsp;<?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_dates" value="today" <?php checked( $types, 'today' ); ?> />&nbsp;<?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_dates" value="yesterday" <?php checked( $types, 'yesterday' ); ?> />&nbsp;<?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_dates" value="current_week" <?php checked( $types, 'current_week' ); ?> />&nbsp;<?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_dates" value="last_week" <?php checked( $types, 'last_week' ); ?> />&nbsp;<?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_dates" value="current_month" <?php checked( $types, 'current_month' ); ?> />&nbsp;<?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_dates" value="last_month" <?php checked( $types, 'last_month' ); ?> />&nbsp;<?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_dates" value="current_year" <?php checked( $types, 'current_year' ); ?> />&nbsp;<?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_dates" value="last_year" <?php checked( $types, 'last_year' ); ?> />&nbsp;<?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_dates" value="variable" <?php checked( $types, 'variable' ); ?> />&nbsp;<?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?><br />
        <span style="float:left; margin-right:6px;"><?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?></span>
        <input type="text" name="modified_dates_filter_variable" class="sized" size="4" value="<?php echo esc_attr( $modified_date_variable ); ?>" />
        <select name="modified_dates_filter_variable_length">
            <option value="" <?php selected( $modified_date_variable_length, '' ); ?>>&nbsp;</option>
            <option value="second" <?php selected( $modified_date_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
            <option value="minute" <?php selected( $modified_date_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
            <option value="hour" <?php selected( $modified_date_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
            <option value="day" <?php selected( $modified_date_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
            <option value="week" <?php selected( $modified_date_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
            <option value="month" <?php selected( $modified_date_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
            <option value="year" <?php selected( $modified_date_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
        </select><br class="clear" />
        <input type="radio" name="product_filter_dates" value="manual" <?php checked( $types, 'manual' ); ?> />&nbsp;<?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?><br />
        <input type="text" name="product_filter_dates_from" value="<?php echo esc_attr( $product_filter_dates_from ); ?>" size="10" maxlength="10" class="sized datepicker product_export" /> <span style="float:left; margin-right:6px;"><?php esc_html_e( 'to', 'woocommerce-exporter' ); ?></span> <input type="text" name="product_filter_dates_to" value="<?php echo esc_attr( $product_filter_dates_to ); ?>" size="10" maxlength="10" class="sized datepicker product_export" />
    </p>
<?php
    ob_end_flush();
}

/**
 * Displays the form field for filtering products by published date.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_product_filter_by_date_published( $post_ID = 0 ) {

    $types                          = get_post_meta( $post_ID, '_filter_product_published_date', true );
    $product_filter_dates_from      = get_post_meta( $post_ID, '_filter_product_published_dates_from', true );
    $product_filter_dates_to        = get_post_meta( $post_ID, '_filter_product_published_dates_to', true );
    $published_date_variable        = get_post_meta( $post_ID, '_published_date_variable', true );
    $published_date_variable_length = get_post_meta( $post_ID, '_published_date_variable_length', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="product_filter_date"><?php esc_html_e( 'Date published', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="product_filter_published_dates" value="" <?php checked( $types, false ); ?> />&nbsp;<?php esc_html_e( 'All', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_published_dates" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> />&nbsp;<?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_published_dates" value="today" <?php checked( $types, 'today' ); ?> />&nbsp;<?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_published_dates" value="yesterday" <?php checked( $types, 'yesterday' ); ?> />&nbsp;<?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_published_dates" value="current_week" <?php checked( $types, 'current_week' ); ?> />&nbsp;<?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_published_dates" value="last_week" <?php checked( $types, 'last_week' ); ?> />&nbsp;<?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_published_dates" value="current_month" <?php checked( $types, 'current_month' ); ?> />&nbsp;<?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_published_dates" value="last_month" <?php checked( $types, 'last_month' ); ?> />&nbsp;<?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_published_dates" value="current_year" <?php checked( $types, 'current_year' ); ?> />&nbsp;<?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_published_dates" value="last_year" <?php checked( $types, 'last_year' ); ?> />&nbsp;<?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_published_dates" value="variable" <?php checked( $types, 'variable' ); ?> />&nbsp;<?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?><br />
        <span style="float:left; margin-right:6px;"><?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?></span>
        <input type="text" name="published_dates_filter_variable" class="sized" size="4" value="<?php echo esc_attr( $published_date_variable ); ?>" />
        <select name="published_dates_filter_variable_length">
            <option value="" <?php selected( $published_date_variable_length, '' ); ?>>&nbsp;</option>
            <option value="second" <?php selected( $published_date_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
            <option value="minute" <?php selected( $published_date_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
            <option value="hour" <?php selected( $published_date_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
            <option value="day" <?php selected( $published_date_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
            <option value="week" <?php selected( $published_date_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
            <option value="month" <?php selected( $published_date_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
            <option value="year" <?php selected( $published_date_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
        </select><br class="clear" />
        <input type="radio" name="product_filter_published_dates" value="manual" <?php checked( $types, 'manual' ); ?> />&nbsp;<?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?><br />
        <input type="text" name="product_filter_published_dates_from" value="<?php echo esc_attr( $product_filter_dates_from ); ?>" size="10" maxlength="10" class="sized datepicker product_export" /> <span style="float:left; margin-right:6px;"><?php esc_html_e( 'to', 'woocommerce-exporter' ); ?></span> <input type="text" name="product_filter_published_dates_to" value="<?php echo esc_attr( $product_filter_dates_to ); ?>" size="10" maxlength="10" class="sized datepicker product_export" />
    </p>
<?php
    ob_end_flush();
}

/**
 * Filters the product export by stock status.
 *
 * @param int $post_ID The ID of the post being filtered.
 */
function woo_ce_scheduled_export_product_filter_by_stock_status( $post_ID = 0 ) {

    $types = get_post_meta( $post_ID, '_filter_product_stock', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label><?php esc_html_e( 'Stock status', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="product_filter_stock" value="" <?php checked( $types, false ); ?> />&nbsp;<?php esc_html_e( 'Include both', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_stock" value="instock" <?php checked( $types, 'instock' ); ?> />&nbsp;<?php esc_html_e( 'In stock', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_stock" value="outofstock" <?php checked( $types, 'outofstock' ); ?> />&nbsp;<?php esc_html_e( 'Out of stock', 'woocommerce-exporter' ); ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Filters the product by stock quantity.
 *
 * @param int $post_ID The ID of the product.
 */
function woo_ce_scheduled_export_product_filter_by_stock_quantity( $post_ID = 0 ) {

    $quantity = false;
    $operator = false;
    $types    = get_post_meta( $post_ID, '_filter_product_quantity', true );
    // Separate the operator from the value.
    if ( $types !== false ) {
        $types    = htmlspecialchars_decode( $types );
        $quantity = str_replace( array( '!=', '=', '>', '>=', '<', '<=' ), '', $types );
        $operator = preg_replace( '/[0-9]+/', '', $types );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="product_filter_quantity"><?php esc_html_e( 'Stock quantity', 'woocommerce-exporter' ); ?></label>
        <input type="number" size="10" maxlength="10" id="product_filter_quantity" name="product_filter_quantity" value="<?php echo esc_attr( $quantity ); ?>" class="text code" />
        <select id="product_quantity_operator" name="product_filter_quantity_operator">
            <option value="=" <?php selected( $operator, '=' ); ?>><?php esc_html_e( 'Equal', 'woocommerce-exporter' ); ?></option>
            <option value="!=" <?php selected( $operator, '!=' ); ?>><?php esc_html_e( 'Not equal', 'woocommerce-exporter' ); ?></option>
            <option value=">" <?php selected( $operator, '>' ); ?>><?php esc_html_e( 'Greater than', 'woocommerce-exporter' ); ?></option>
            <option value=">=" <?php selected( $operator, '>=' ); ?>><?php esc_html_e( 'Greater than or equal to', 'woocommerce-exporter' ); ?></option>
            <option value="<" <?php selected( $operator, '<' ); ?>><?php esc_html_e( 'Less than', 'woocommerce-exporter' ); ?></option>
            <option value="<=" <?php selected( $operator, '<=' ); ?>><?php esc_html_e( 'Less than or equal to', 'woocommerce-exporter' ); ?></option>
        </select>
    </p>
<?php
    ob_end_flush();
}

/**
 * Displays a form field for filtering products by featured status.
 *
 * @param int $post_ID The ID of the product post.
 */
function woo_ce_scheduled_export_product_filter_by_featured( $post_ID = 0 ) {

    $types = get_post_meta( $post_ID, '_filter_product_featured', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="product_filter_featured"><?php esc_html_e( 'Featured', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="product_filter_featured" value="" <?php checked( $types, false ); ?> />&nbsp;<?php esc_html_e( 'Include both', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_featured" value="yes" <?php checked( $types, 'yes' ); ?> />&nbsp;<?php esc_html_e( 'Featured', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="product_filter_featured" value="no" <?php checked( $types, 'no' ); ?> />&nbsp;<?php esc_html_e( 'Not featured', 'woocommerce-exporter' ); ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Filters the product by custom meta for scheduled export.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_product_filter_by_product_meta( $post_ID = 0 ) {
    // Retrieve the custom products option.
    $custom_products = woo_ce_get_option( 'custom_products', '' );

    // If no custom products are set, return.
    if ( empty( $custom_products ) ) {
        return;
    }

    ob_start();
    ?>
    <?php foreach ( $custom_products as $custom_product ) { ?>
        <?php if ( ! empty( $custom_product ) ) { ?>
            <?php
            // Get the custom meta for the product.
            $types = get_post_meta( $post_ID, sprintf( '_filter_product_custom_meta-%s', esc_attr( $custom_product ) ), true );
            ?>
            <p class="form-field discount_type_field">
                <label for="product_filter_custom_meta-<?php echo esc_attr( $custom_product ); ?>"><?php echo esc_attr( $custom_product ); ?></label></label>
                <input type="text" id="product_filter_custom_meta-<?php echo esc_attr( $custom_product ); ?>" name="product_filter_custom_meta-<?php echo esc_attr( $custom_product ); ?>" value="<?php echo esc_attr( $types ); ?>" size="5" class="text" />
            </p>
        <?php } ?>
    <?php } ?>
<?php
    ob_end_flush();
}

/**
 * HTML template for Product Sorting filter on Edit Scheduled Export screen
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_product_filter_orderby( $post_ID ) {

    $orderby = get_post_meta( $post_ID, '_filter_product_orderby', true );
    // Default to Title.
    if ( $orderby == false ) {
        $orderby = 'title';
    }

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="product_filter_orderby"><?php esc_html_e( 'Product Sorting', 'woocommerce-exporter' ); ?></label>
            <select id="product_filter_orderby" name="product_filter_orderby">
                <option value="ID" <?php selected( 'ID', $orderby ); ?>><?php esc_html_e( 'Product ID', 'woocommerce-exporter' ); ?></option>
                <option value="title" <?php selected( 'title', $orderby ); ?>><?php esc_html_e( 'Product Name', 'woocommerce-exporter' ); ?></option>
                <option value="sku" <?php selected( 'sku', $orderby ); ?>><?php esc_html_e( 'Product SKU', 'woocommerce-exporter' ); ?></option>
                <option value="date" <?php selected( 'date', $orderby ); ?>><?php esc_html_e( 'Date Created', 'woocommerce-exporter' ); ?></option>
                <option value="modified" <?php selected( 'modified', $orderby ); ?>><?php esc_html_e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
                <option value="rand" <?php selected( 'rand', $orderby ); ?>><?php esc_html_e( 'Random', 'woocommerce-exporter' ); ?></option>
                <option value="menu_order" <?php selected( 'menu_order', $orderby ); ?>><?php esc_html_e( 'Sort Order', 'woocommerce-exporter' ); ?></option>
            </select>
        </p>
    </div>
    <!-- .options_group -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Product Multi Level Sorting filter on Edit Scheduled Export screen
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_product_multi_level_sorting( $post_ID ) {

    $product_orderby_field1 = get_post_meta( $post_ID, '_filter_product_orderby_field1', true );
    $product_orderby_field2 = get_post_meta( $post_ID, '_filter_product_orderby_field2', true );
    $product_order_field1   = get_post_meta( $post_ID, '_filter_product_order_field1', true );
    $product_order_field2   = get_post_meta( $post_ID, '_filter_product_order_field2', true );
    $fields                 = woo_ce_get_product_fields( 'full', $post_ID );

    ob_start();
    ?>
    <div class="options_group">
        <p><?php esc_html_e( 'Custom Multi-Level Field Sorting - Replaces product sorting above (CSV, TSV, XLS, XLSX)', 'woocommerce-exporter' ); ?></p>
        <p class="form-field discount_type_field">
            <label for="product_filter_orderby_field1"><?php esc_html_e( 'First field to sort', 'woocommerce-exporter' ); ?></label>
            <select id="product_filter_orderby_field1" name="product_filter_orderby_field1">
                <option value="" <?php selected( '', $product_orderby_field1 ); ?>></option>
                <?php
                foreach ( $fields as $field ) {
                    $field_name  = $field['name'];
                    $field_label = $field['label'];
                ?>
                    <option value="<?php echo esc_attr( $field_name ); ?>" <?php selected( $field_name, $product_orderby_field1 ); ?>><?php echo esc_html( $field_label ); ?></option>
                <?php
                }
                ?>
            </select>
            <select name="product_filter_order_field1">
                <option value="ASC" <?php selected( 'ASC', $product_order_field1 ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
                <option value="DESC" <?php selected( 'DESC', $product_order_field1 ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
            </select>
        </p>
        <p class="form-field discount_type_field">
            <label for="product_filter_orderby_field2"><?php esc_html_e( 'Second level field to sort', 'woocommerce-exporter' ); ?></label>
            <select name="product_filter_orderby_field2">
                <option value="" <?php selected( '', $product_orderby_field1 ); ?>></option>
                <?php
                foreach ( $fields as $field ) {
                    $field_name  = $field['name'];
                    $field_label = $field['label'];
                ?>
                    <option value="<?php echo esc_attr( $field_name ); ?>" <?php selected( $field_name, $product_orderby_field2 ); ?>><?php echo esc_html( $field_label ); ?></option>
                <?php
                }
                ?>
            </select>
            <select name="product_filter_order_field2">
                <option value="ASC" <?php selected( 'ASC', $product_order_field2 ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
                <option value="DESC" <?php selected( 'DESC', $product_order_field2 ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
            </select>
        </p>
    </div>
    <!-- .options_group -->
<?php
    ob_end_flush();
}

/**
 * Export templates
 *
 * This function generates the export template fields for a product.
 *
 * @param int $post_ID The ID of the product post. Default is 0.
 */
function woo_ce_export_template_fields_product( $post_ID = 0 ) {

    $export_type = 'product';

    $fields = woo_ce_get_product_fields( 'full', $post_ID );

    $labels = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );

    // Check if labels is empty.
    if ( ! $labels ) {
        $labels = array();
    }

    ob_start();
    ?>
    <div class="export-options <?php echo esc_attr( $export_type ); ?>-options">

        <div class="options_group">
            <div class="form-field discount_type_field">
                <p class="form-field discount_type_field ">
                    <label><?php esc_html_e( 'Product fields', 'woocommerce-exporter' ); ?></label>
                </p>
                <?php if ( ! empty( $fields ) ) { ?>
                    <table id="<?php echo esc_attr( $export_type ); ?>-fields" class="ui-sortable">
                        <tbody>
                            <?php foreach ( $fields as $field ) { ?>
                                <tr id="<?php echo esc_attr( $export_type ); ?>-<?php echo esc_attr( $field['reset'] ); ?>">
                                    <td>
                                        <label
                                            <?php
                                            if ( isset( $field['hover'] ) ) {
                                            ?>
                                            title="<?php echo esc_attr( $field['hover'] ); ?>" <?php } ?>>
                                            <input type="checkbox" name="<?php echo esc_attr( $export_type ); ?>_fields[<?php echo esc_attr( $field['name'] ); ?>]" class="<?php echo esc_attr( $export_type ); ?>_field" <?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo esc_attr( $field['label'] ); ?>
                                        </label>
                                            <input type="text" name="<?php echo esc_attr( $export_type ); ?>_fields_label[<?php echo esc_attr( $field['name'] ); ?>]" class="text" placeholder="<?php echo esc_attr( $field['label'] ); ?>" value="<?php echo ( array_key_exists( $field['name'], $labels ) ? esc_attr( $labels[ $field['name'] ] ) : '' ); ?>" />
                                            <input type="hidden" name="<?php echo esc_attr( $export_type ); ?>_fields_order[<?php echo esc_attr( $field['name'] ); ?>]" class="field_order" value="<?php echo esc_attr( $field['order'] ); ?>" />
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <!-- #<?php echo esc_attr( $export_type ); ?>-fields -->
                <?php } else { ?>
                    <p><?php esc_html_e( 'No Product fields were found.', 'woocommerce-exporter' ); ?></p>
                <?php } ?>
            </div>
            <!-- .form-field -->
        </div>
        <!-- .options_group -->

    </div>
    <!-- .export-options -->
<?php
    ob_end_flush();
}
?>
