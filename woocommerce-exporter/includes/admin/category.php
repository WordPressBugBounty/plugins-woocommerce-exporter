<?php
/**
 * HTML template for Category Sorting widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Category Sorting widget on the Store Exporter screen.
 * It displays a form with dropdown menus to select the sorting options for categories in the exported file.
 */
function woo_ce_category_sorting() {

    $category_orderby = woo_ce_get_option( 'category_orderby', 'ID' );
    $category_order   = woo_ce_get_option( 'category_order', 'ASC' );

    ob_start(); ?>
<p><label><?php esc_html_e( 'Category Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
    <select name="category_orderby">
        <option value="id"<?php selected( 'id', $category_orderby ); ?>><?php esc_html_e( 'Term ID', 'woocommerce-exporter' ); ?></option>
        <option value="name"<?php selected( 'name', $category_orderby ); ?>><?php esc_html_e( 'Category Name', 'woocommerce-exporter' ); ?></option>
    </select>
    <select name="category_order">
        <option value="ASC"<?php selected( 'ASC', $category_order ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
        <option value="DESC"<?php selected( 'DESC', $category_order ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
    </select>
    <p class="description"><?php esc_html_e( 'Select the sorting of Categories within the exported file. By default this is set to export Categories by Term ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
    ob_end_flush();
}

/**
 * HTML template for Custom Categories widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Custom Categories widget on the Store Exporter screen.
 * It displays a form with options to include additional custom Category meta in the list of available export fields.
 * Users can fill in the meta text box with custom Category meta names, save the custom fields, and select them as export fields.
 */
function woo_ce_categories_custom_fields() {

    if ( $custom_terms = woo_ce_get_option( 'custom_categories', '' ) ) {
        $custom_terms = implode( "\n", $custom_terms );
    }

    $troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

    ob_start();
    ?>
<form method="post" id="export-categories-custom-fields" class="export-options category-options">
    <div id="poststuff">

        <div class="postbox" id="export-options category-options">
            <h3 class="hndle"><?php esc_html_e( 'Custom Category Fields', 'woocommerce-exporter' ); ?></h3>
            <div class="inside">
                <p class="description"><?php esc_html_e( 'To include additional custom Category meta in the list of available export fields above fill the meta text box then click Save Custom Fields. The saved custom fields will appear as export fields to be selected from the Category Fields list.', 'woocommerce-exporter' ); ?></p>
                <table class="form-table">

                    <tr>
                        <th>
                            <label for="custom_categories"><?php esc_html_e( 'Category meta', 'woocommerce-exporter' ); ?></label>
                        </th>
                        <td>
                            <textarea disabled="disabled" rows="5" cols="70"><?php echo esc_textarea( $custom_terms ); ?></textarea>
                            <p class="description">
                                <?php echo wp_kses_post( __( 'Include additional custom Category meta in your export file by adding each custom Category meta name to a new line above.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ) ); ?>
                                <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=categorycustommetalink' ) ) ); ?></span>
                            </p>
                        </td>
                    </tr>

                    <?php do_action( 'woo_ce_categories_custom_fields' ); ?>

                </table>
                <p class="description">
                    <?php
                    // translators: %s: URL to the plugin documentation.
                    echo wp_kses_post( sprintf( __( 'For more information on exporting custom Category meta and Attributes consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ) );
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
<!-- #export-categories-custom-fields -->
<?php
    ob_end_flush();
}

/**
 * Renders the export options for a category in the WooCommerce Store Exporter Deluxe plugin.
 *
 * This function is responsible for rendering the export options for a category in the plugin's admin area.
 * It uses output buffering to capture the HTML output and flushes it at the end.
 *
 * @param int $post_ID The ID of the category post. Default is 0.
 */
function woo_ce_scheduled_export_filters_category( $post_ID = 0 ) {

    ob_start();
    ?>
<div class="export-options category-options">

    <?php do_action( 'woo_ce_scheduled_export_filters_category', $post_ID ); ?>

</div>
<!-- .category-options -->

<?php
    ob_end_flush();
}
/**
 * HTML template for Category Sorting filter on Edit Scheduled Export screen.
 *
 * This function generates the HTML template for the Category Sorting filter on the Edit Scheduled Export screen.
 * It displays a select dropdown with options to sort categories by term ID or category name.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_category_filter_orderby( $post_ID ) {

    $orderby = get_post_meta( $post_ID, '_filter_category_orderby', true );
    // Default to Title.
    if ( ! $orderby ) {
        $orderby = 'name';
    }

    ob_start();
    ?>
<div class="options_group">
    <p class="form-field discount_type_field">
        <label for="category_filter_orderby"><?php esc_html_e( 'Category Sorting', 'woocommerce-exporter' ); ?></label>
        <select id="category_filter_orderby" name="category_filter_orderby">
            <option value="id"<?php selected( 'id', $orderby ); ?>><?php esc_html_e( 'Term ID', 'woocommerce-exporter' ); ?></option>
            <option value="name"<?php selected( 'name', $orderby ); ?>><?php esc_html_e( 'Category Name', 'woocommerce-exporter' ); ?></option>
        </select>
    </p>
</div>
<!-- .options_group -->
<?php
    ob_end_flush();
}

/**
 * This file contains the function woo_ce_export_template_fields_category(), which is responsible for generating the export options for the category fields in the WooCommerce Store Exporter Deluxe plugin.
 *
 * @param int $post_ID The ID of the post being edited. Default is 0.
 */
function woo_ce_export_template_fields_category( $post_ID = 0 ) {

    $export_type = 'category';

    $fields = woo_ce_get_category_fields( 'full', $post_ID );

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
                <label><?php esc_html_e( 'Category fields', 'woocommerce-exporter' ); ?></label>
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
                            title="<?php echo esc_attr( $field['hover'] ); ?>"<?php } ?>>
                                <input type="checkbox" name="<?php echo esc_attr( $export_type ); ?>_fields[<?php echo esc_attr( $field['name'] ); ?>]" class="<?php echo esc_attr( $export_type ); ?>_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo esc_attr( $field['label'] ); ?>
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
            <p><?php esc_html_e( 'No Category fields were found.', 'woocommerce-exporter' ); ?></p>
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
