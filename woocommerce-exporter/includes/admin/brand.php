<?php
/**
 * HTML template for Coupon Sorting widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Coupon Sorting widget on the Store Exporter screen.
 * It displays a form with dropdown menus for selecting the sorting options for brands in the exported file.
 */
function woo_ce_brand_sorting() {

    $orderby = woo_ce_get_option( 'brand_orderby', 'ID' );
    $order   = woo_ce_get_option( 'brand_order', 'ASC' );

    ob_start(); ?>
    <p><label><?php esc_html_e( 'Brand Sorting', 'woocommerce-exporter' ); ?></label></p>
    <div>
        <select name="brand_orderby">
            <option value="id" <?php selected( 'id', $orderby ); ?>><?php esc_html_e( 'Term ID', 'woocommerce-exporter' ); ?></option>
            <option value="name" <?php selected( 'name', $orderby ); ?>><?php esc_html_e( 'Brand Name', 'woocommerce-exporter' ); ?></option>
        </select>
        <select name="brand_order">
            <option value="ASC" <?php selected( 'ASC', $order ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
            <option value="DESC" <?php selected( 'DESC', $order ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Select the sorting of Brands within the exported file. By default this is set to export Product Brands by Term ID in Desending order.', 'woocommerce-exporter' ); ?></p>
    </div>
<?php
    ob_end_flush();
}

/**
 * HTML template for Custom Brands widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Custom Brands widget on the Store Exporter screen.
 * It displays a form with options to include additional custom Brand meta in the list of available export fields.
 * Users can fill in the meta text box with custom Brand meta names, save the custom fields, and select them from the Brand Fields list.
 * The function also provides a link to the online documentation for exporting custom Brand meta.
 */
function woo_ce_brands_custom_fields() {
    $custom_terms = woo_ce_get_option( 'custom_brands', '' );
    if ( $custom_terms ) {
        $custom_terms = implode( "\n", $custom_terms );
    }

    $troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

    ob_start();
    ?>
    <form method="post" id="export-brands-custom-fields" class="export-options brand-options">
        <div id="poststuff">

            <div class="postbox" id="export-options brand-options">
                <h3 class="hndle"><?php esc_html_e( 'Custom Brand Fields', 'woocommerce-exporter' ); ?></h3>
                <div class="inside">
                    <p class="description"><?php esc_html_e( 'To include additional custom Brand meta in the list of available export fields above fill the meta text box then click Save Custom Fields. The saved custom fields will appear as export fields to be selected from the Brand Fields list.', 'woocommerce-exporter' ); ?></p>
                    <table class="form-table">

                        <tr>
                            <th>
                                <label for="custom_brands"><?php esc_html_e( 'Brand meta', 'woocommerce-exporter' ); ?></label>
                            </th>
                            <td>
                                <textarea disabled="disabled" rows="5" cols="70"><?php echo esc_textarea( $custom_terms ); ?></textarea>
                                <p class="description">
                                    <?php echo wp_kses_post( __( 'Include additional custom Brand meta in your export file by adding each custom Brand meta name to a new line above.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ) ); ?>
                                    <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=brandcustommetalink' ) ) ); ?></span>
                                </p>
                            </td>
                        </tr>

                        <?php do_action( 'woo_ce_brands_custom_fields' ); ?>

                    </table>
                    <p class="description">
                        <?php
                        // Translators: %s is the URL to the online documentation for exporting custom Brand meta.
                        echo wp_kses_post( sprintf( __( 'For more information on exporting custom Brand meta consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ) );
                        ?>
                    </p>
                    <p class="submit">
                        <input type="button" class="button button-disabled" value="<?php esc_html_e( 'Save Custom Fields', 'woocommerce-exporter' ); ?>"/>
                    </p>
                </div>
                <!-- .inside -->
            </div>
            <!-- .postbox -->

        </div>
        <!-- #poststuff -->
        <input type="hidden" name="action" value="update" />
    </form>
    <!-- #export-brands-custom-fields -->
<?php
    ob_end_flush();
}

/**
 * Displays the export options for brand filters on the Edit Scheduled Export screen.
 *
 * @param int $post_ID The ID of the scheduled export post.
 */
function woo_ce_scheduled_export_filters_brand( $post_ID = 0 ) {

    ob_start();
    ?>
    <div class="export-options brand-options">

        <?php do_action( 'woo_ce_scheduled_export_filters_brand', $post_ID ); ?>

    </div>
    <!-- .brand-options -->

<?php
    ob_end_flush();
}

/**
 * Displays the HTML template for the Brand Sorting filter on the Edit Scheduled Export screen.
 *
 * @param int $post_ID The ID of the scheduled export post.
 */
function woo_ce_scheduled_export_brand_filter_orderby( $post_ID ) {

    $orderby = get_post_meta( $post_ID, '_filter_brand_orderby', true );
    // Default to Title.
    if ( false === $orderby ) {
        $orderby = 'name';
    }

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="brand_filter_orderby"><?php esc_html_e( 'Brand Sorting', 'woocommerce-exporter' ); ?></label>
            <select id="brand_filter_orderby" name="brand_filter_orderby">
                <option value="id" <?php selected( 'id', $orderby ); ?>><?php esc_html_e( 'Term ID', 'woocommerce-exporter' ); ?></option>
                <option value="name" <?php selected( 'name', $orderby ); ?>><?php esc_html_e( 'Brand Name', 'woocommerce-exporter' ); ?></option>
            </select>
        </p>
    </div>
    <!-- .options_group -->
<?php
    ob_end_flush();
}

/**
 * Displays the export options for brand fields on the Edit Scheduled Export screen.
 *
 * @param int $post_ID The ID of the scheduled export post.
 */
function woo_ce_export_template_fields_brand( $post_ID = 0 ) {

    $export_type = 'brand';

    $fields = woo_ce_get_brand_fields( 'full', $post_ID );

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
                    <label><?php esc_html_e( 'Brand fields', 'woocommerce-exporter' ); ?></label>
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
                    <p><?php esc_html_e( 'No Brand fields were found.', 'woocommerce-exporter' ); ?></p>
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
