<?php
/**
 * HTML template for Filter Coupons by Discount Type on Store Exporter screen.
 *
 * This function generates the HTML template for filtering coupons by discount type on the Store Exporter screen.
 * It displays a checkbox to enable/disable the filter and a dropdown to select the discount types to filter by.
 */
function woo_ce_coupons_filter_by_discount_type() {

    $discount_types = woo_ce_get_coupon_discount_types();
    $types          = woo_ce_get_option( 'coupon_discount_type', array() );

    ob_start(); ?>
    <p><label><input type="checkbox" disabled="disabled" <?php checked( ! empty( $types ), true ); ?> /> <?php esc_html_e( 'Filter Coupons by Discount Type', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-coupons-filters-discount_types" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $discount_types ) ) { ?>
                    <select data-placeholder="<?php esc_html_e( 'Choose a Discount Type...', 'woocommerce-exporter' ); ?>" name="coupon_filter_discount_type[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $discount_types as $key => $discount_type ) { ?>
                            <?php // translators: %s: Post meta name. ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo esc_html( $discount_type ); ?> (<?php echo esc_html( sprintf( __( 'Post meta name: %s', 'woocommerce-exporter' ), $key ) ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Discount Types were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Discount Types you want to filter exported Coupons by. Default is to include all Coupons.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-products-filters-discount_types -->

<?php
    ob_end_flush();
}

/**
 * HTML template for Coupon Sorting widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Coupon Sorting widget on the Store Exporter screen.
 * It displays a dropdown menu for selecting the sorting options for coupons in the exported file.
 */
function woo_ce_coupon_sorting() {

    $orderby = woo_ce_get_option( 'coupon_orderby', 'ID' );
    $order   = woo_ce_get_option( 'coupon_order', 'ASC' );

    ob_start();
    ?>
    <p><label><?php esc_html_e( 'Coupon Sorting', 'woocommerce-exporter' ); ?></label></p>
    <div>
        <select name="coupon_orderby">
            <option value="ID" <?php selected( 'ID', $orderby ); ?>><?php esc_html_e( 'Coupon ID', 'woocommerce-exporter' ); ?></option>
            <option value="title" <?php selected( 'title', $orderby ); ?>><?php esc_html_e( 'Coupon Code', 'woocommerce-exporter' ); ?></option>
            <option value="date" <?php selected( 'date', $orderby ); ?>><?php esc_html_e( 'Date Created', 'woocommerce-exporter' ); ?></option>
            <option value="modified" <?php selected( 'modified', $orderby ); ?>><?php esc_html_e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
            <option value="rand" <?php selected( 'rand', $orderby ); ?>><?php esc_html_e( 'Random', 'woocommerce-exporter' ); ?></option>
        </select>
        <select name="coupon_order">
            <option value="ASC" <?php selected( 'ASC', $order ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
            <option value="DESC" <?php selected( 'DESC', $order ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Select the sorting of Coupons within the exported file. By default this is set to export Coupons by Coupon ID in Desending order.', 'woocommerce-exporter' ); ?></p>
    </div>
<?php
    ob_end_flush();
}

/**
 * Renders the export options for coupons in the scheduled exports section.
 *
 * @param int $post_ID The ID of the coupon post.
 */
function woo_ce_scheduled_export_filters_coupon( $post_ID = 0 ) {
    ob_start();
    ?>
    <div class="export-options coupon-options">

        <?php do_action( 'woo_ce_scheduled_export_filters_coupon', $post_ID ); ?>

    </div>
    <!-- .coupon-options -->

<?php
    ob_end_flush();
}

/**
 * HTML template for Coupon Sorting filter on Edit Scheduled Export screen.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_coupon_filter_orderby( $post_ID ) {

    $orderby = get_post_meta( $post_ID, '_filter_coupon_orderby', true );
    // Default to ID.
    if ( ! $orderby ) {
        $orderby = 'ID';
    }

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="coupon_filter_orderby"><?php esc_html_e( 'Coupon Sorting', 'woocommerce-exporter' ); ?></label>
            <select id="coupon_filter_orderby" name="coupon_filter_orderby">
                <option value="ID" <?php selected( 'ID', $orderby ); ?>><?php esc_html_e( 'Coupon ID', 'woocommerce-exporter' ); ?></option>
                <option value="title" <?php selected( 'title', $orderby ); ?>><?php esc_html_e( 'Coupon Code', 'woocommerce-exporter' ); ?></option>
                <option value="date" <?php selected( 'date', $orderby ); ?>><?php esc_html_e( 'Date Created', 'woocommerce-exporter' ); ?></option>
                <option value="modified" <?php selected( 'modified', $orderby ); ?>><?php esc_html_e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
                <option value="rand" <?php selected( 'rand', $orderby ); ?>><?php esc_html_e( 'Random', 'woocommerce-exporter' ); ?></option>
            </select>
        </p>
    </div>
    <!-- .options_group -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Coupons by Discount Type widget on Scheduled Export screen.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_coupon_filter_by_discount_type( $post_ID ) {

    $discount_types = woo_ce_get_coupon_discount_types();
    $types          = get_post_meta( $post_ID, '_filter_coupon_discount_type', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="coupon_filter_discount_type"><?php esc_html_e( 'Discount type', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $discount_types ) ) { ?>
            <select data-placeholder="<?php esc_html_e( 'Choose a Discount Type...', 'woocommerce-exporter' ); ?>" name="coupon_filter_discount_type[]" multiple class="chzn-select" style="width:95%;">
                <?php foreach ( $discount_types as $key => $discount_type ) { ?>
                    <?php // translators: %s: Post meta name. ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo esc_html( $discount_type ); ?> (<?php echo esc_html( sprintf( __( 'Post meta name: %s', 'woocommerce-exporter' ), $key ) ); ?>)</option>
                <?php } ?>
            </select>
        <?php } else { ?>
            <?php esc_html_e( 'No Discount Types were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>

<?php
    ob_end_flush();
}

/**
 * Export templates
 *
 * This function generates the export template fields for coupons.
 *
 * @param int $post_ID The ID of the coupon post. Default is 0.
 */
function woo_ce_export_template_fields_coupon( $post_ID = 0 ) {

    $export_type = 'coupon';

    $fields = woo_ce_get_coupon_fields( 'full', $post_ID );

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
                    <label><?php esc_html_e( 'Coupon fields', 'woocommerce-exporter' ); ?></label>
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
                    <p><?php esc_html_e( 'No Coupon fields were found.', 'woocommerce-exporter' ); ?></p>
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
