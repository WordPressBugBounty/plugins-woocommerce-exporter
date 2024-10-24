<?php
/**
 * FILEPATH: /c:/Users/digid/Local Sites/visser/app/public/wp-content/plugins/woocommerce-store-exporter-deluxe/includes/admin/shipping_class.php
 *
 * This file contains functions related to shipping class exports in the WooCommerce Store Exporter Deluxe plugin.
 */

/**
 * Displays the export options for shipping class filters on the Edit Scheduled Export screen.
 *
 * @param int $post_ID The ID of the scheduled export post.
 */
function woo_ce_scheduled_export_filters_shipping_class( $post_ID = 0 ) {
    ob_start(); ?>
    <div class="export-options shipping_class-options">
        <?php do_action( 'woo_ce_scheduled_export_filters_shipping_class', $post_ID ); ?>
    </div>
    <!-- .shipping_class-options -->
<?php
    ob_end_flush();
}

/**
 * Displays the HTML template for the Shipping Class Sorting filter on the Edit Scheduled Export screen.
 *
 * @param int $post_ID The ID of the scheduled export post.
 */
function woo_ce_scheduled_export_shipping_class_filter_orderby( $post_ID ) {
    $orderby = get_post_meta( $post_ID, '_filter_shipping_class_orderby', true );
    // Default to Title.
    if ( ! $orderby ) {
        $orderby = 'name';
    }

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="shipping_class_filter_orderby"><?php esc_html_e( 'Shipping Class Sorting', 'woocommerce-exporter' ); ?></label>
            <select id="shipping_class_filter_orderby" name="shipping_class_filter_orderby">
                <option value="id" <?php selected( 'id', $orderby ); ?>><?php esc_html_e( 'Term ID', 'woocommerce-exporter' ); ?></option>
                <option value="name" <?php selected( 'name', $orderby ); ?>><?php esc_html_e( 'Shipping Class Name', 'woocommerce-exporter' ); ?></option>
            </select>
        </p>
    </div>
    <!-- .options_group -->
<?php
    ob_end_flush();
}

/**
 * Displays the HTML template for the Shipping Class Sorting widget on the Store Exporter screen.
 */
function woo_ce_shipping_class_sorting() {
    $orderby = woo_ce_get_option( 'shipping_class_orderby', 'ID' );
    $order   = woo_ce_get_option( 'shipping_class_order', 'ASC' );

    ob_start();
    ?>
    <p><label><?php esc_html_e( 'Shipping Class Sorting', 'woocommerce-exporter' ); ?></label></p>
    <div>
        <select name="shipping_class_orderby">
            <option value="id" <?php selected( 'id', $orderby ); ?>><?php esc_html_e( 'Term ID', 'woocommerce-exporter' ); ?></option>
            <option value="name" <?php selected( 'name', $orderby ); ?>><?php esc_html_e( 'Shipping Class Name', 'woocommerce-exporter' ); ?></option>
        </select>
        <select name="shipping_class_order">
            <option value="ASC" <?php selected( 'ASC', $order ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
            <option value="DESC" <?php selected( 'DESC', $order ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Select the sorting of Shipping Classes within the exported file. By default this is set to export Shipping Classes by Term ID in Desending order.', 'woocommerce-exporter' ); ?></p>
    </div>
<?php
    ob_end_flush();
}

/**
 * Displays the export options for shipping class fields on the Edit Scheduled Export screen.
 *
 * @param int $post_ID The ID of the scheduled export post.
 */
function woo_ce_export_template_fields_shipping_class( $post_ID = 0 ) {
    $export_type = 'shipping_class';
    $fields      = woo_ce_get_shipping_class_fields( 'full', $post_ID );
    $labels      = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );

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
                    <label><?php esc_html_e( 'Shipping Class fields', 'woocommerce-exporter' ); ?></label>
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
                    <p><?php esc_html_e( 'No Shipping Class fields were found.', 'woocommerce-exporter' ); ?></p>
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
