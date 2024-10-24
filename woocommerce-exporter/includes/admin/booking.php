<?php
/**
 * HTML template for Booking Sorting widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Booking Sorting widget on the Store Exporter screen.
 * It displays a dropdown menu for selecting the sorting options for bookings within the exported file.
 */
function woo_ce_booking_sorting() {

    $booking_orderby = woo_ce_get_option( 'booking_orderby', 'ID' );
    $booking_order   = woo_ce_get_option( 'booking_order', 'ASC' );

    ob_start(); ?>
    <p><label><?php esc_html_e( 'Booking Sorting', 'woocommerce-exporter' ); ?></label></p>
    <div>
        <select name="booking_orderby">
            <option value="ID" <?php selected( 'ID', $booking_orderby ); ?>><?php esc_html_e( 'Booking Number', 'woocommerce-exporter' ); ?></option>
            <option value="date" <?php selected( 'date', $booking_orderby ); ?>><?php esc_html_e( 'Date Created', 'woocommerce-exporter' ); ?></option>
            <option value="modified" <?php selected( 'modified', $booking_orderby ); ?>><?php esc_html_e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
            <option value="rand" <?php selected( 'rand', $booking_orderby ); ?>><?php esc_html_e( 'Random', 'woocommerce-exporter' ); ?></option>
        </select>
        <select name="booking_order">
            <option value="ASC" <?php selected( 'ASC', $booking_order ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
            <option value="DESC" <?php selected( 'DESC', $booking_order ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Select the sorting of Bookings within the exported file. By default this is set to export Bookings by Booking ID in Desending order.', 'woocommerce-exporter' ); ?></p>
    </div>
<?php
    ob_end_flush();
}

/**
 * Export templates
 *
 * This function generates the export template fields for the booking post type.
 *
 * @param int $post_ID The ID of the booking post.
 * @return void
 */
function woo_ce_export_template_fields_booking( $post_ID = 0 ) {

    $export_type = 'booking';

    $fields = woo_ce_get_booking_fields( 'full', $post_ID );

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
                    <label><?php esc_html_e( 'Booking fields', 'woocommerce-exporter' ); ?></label>
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
                                            title="<?php echo esc_attr( $field['hover'] ); ?>" <?php } ?>
                                        >
                                            <input type="checkbox" name="<?php echo esc_attr( $export_type ); ?>_fields[<?php echo esc_attr( $field['name'] ); ?>]" class="<?php echo esc_attr( $export_type ); ?>_field" <?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo esc_attr( $field['label'] ); ?>
                                        </label>
                                        <input type="text" name="<?php echo esc_attr( $export_type ); ?>_fields_label[<?php echo esc_attr( $field['name'] ); ?>]" class="text" placeholder="<?php echo esc_attr( $field['label'] ); ?>" value="<?php echo ( array_key_exists( $field['name'], $labels ) ? esc_attr( $labels[ $field['name'] ] ) : '' ); ?>" />
                                        <input type="hidden" name="<?php echo esc_attr( $export_type ); ?>_fields_order[<?php echo esc_attr( $field['name'] ); ?>]" class="field_order" value="<?php echo esc_attr( $field['order'] ); ?>" />
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p><?php esc_html_e( 'No Booking fields were found.', 'woocommerce-exporter' ); ?></p>
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

/**
 * Add Export to... to Booking screen
 *
 * This function extends the WooCommerce admin booking actions by adding an option to export the booking data to CSV.
 *
 * @param array  $actions The array of booking actions.
 * @param object $booking The booking object.
 * @return array The modified array of booking actions.
 */
function woo_ce_extend_woocommerce_admin_booking_actions( $actions, $booking ) {

    /*
    $actions['export_csv'] = array(
        'url'       => admin_url( 'post.php?post=' . $post->ID . '&action=edit' ),
        'name'      => __( 'Export to CSV', 'woocommerce-bookings' ),
        'action'    => "export_booking_csv"
    );
    */
    return $actions;
}
add_filter( 'woocommerce_admin_booking_actions', 'woo_ce_extend_woocommerce_admin_booking_actions', 10, 2 );
?>
