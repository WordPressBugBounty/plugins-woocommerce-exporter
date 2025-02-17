<?php
/**
 * HTML template for Review Sorting widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Review Sorting widget on the Store Exporter screen.
 * It displays a dropdown menu for selecting the sorting criteria and order of the reviews to be exported.
 */
function woo_ce_review_sorting() {

    $orderby = woo_ce_get_option( 'review_orderby', 'ID' );
    $order   = woo_ce_get_option( 'review_order', 'ASC' );

    ob_start(); ?>
    <p><label><?php esc_html_e( 'Review Sorting', 'woocommerce-exporter' ); ?></label></p>
    <div>
        <select name="review_orderby">
            <option value="ID" <?php selected( 'ID', $orderby ); ?>><?php esc_html_e( 'Review ID', 'woocommerce-exporter' ); ?></option>
        </select>
        <select name="review_order">
            <option value="ASC" <?php selected( 'ASC', $order ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
            <option value="DESC" <?php selected( 'DESC', $order ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Select the sorting of Reviews within the exported file. By default this is set to export Review by Review ID in Desending order.', 'woocommerce-exporter' ); ?></p>
    </div>
<?php
    ob_end_flush();
}

/**
 * Renders the review options for scheduled exports.
 *
 * This function is responsible for rendering the review options for scheduled exports.
 * It calls the 'woo_ce_scheduled_export_filters_review' action hook to allow other plugins
 * or themes to add their own custom review options.
 *
 * @param int $post_ID The ID of the post being reviewed.
 */
function woo_ce_scheduled_export_filters_review( $post_ID = 0 ) {

    ob_start();
    ?>
    <div class="export-options review-options">

        <?php do_action( 'woo_ce_scheduled_export_filters_review', $post_ID ); ?>

    </div>
    <!-- .review-options -->

<?php
    ob_end_flush();
}

/**
 * HTML template for Review Sorting filter on Edit Scheduled Export screen.
 *
 * This function generates the HTML template for the Review Sorting filter on the Edit Scheduled Export screen.
 * It retrieves the current sorting option from the post meta and sets a default value if none is found.
 * The generated HTML includes a select element with options for different sorting options.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_review_filter_orderby( $post_ID ) {

    $orderby = get_post_meta( $post_ID, '_filter_review_orderby', true );
    // Default to Title.
    if ( ! $orderby ) {
        $orderby = 'name';
    }

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="review_filter_orderby"><?php esc_html_e( 'Review Sorting', 'woocommerce-exporter' ); ?></label>
            <select id="review_filter_orderby" name="review_filter_orderby">
                <option value="id" <?php selected( 'id', $orderby ); ?>><?php esc_html_e( 'Review ID', 'woocommerce-exporter' ); ?></option>
            </select>
        </p>
    </div>
    <!-- .options_group -->
<?php
    ob_end_flush();
}

/**
 * Filters the review export by review date.
 *
 * This function is responsible for generating the HTML markup for the review date filter options.
 *
 * @param int $post_ID The ID of the post being filtered.
 */
function woo_ce_scheduled_export_review_filter_by_review_date( $post_ID ) {

    $types = get_post_meta( $post_ID, '_filter_review_date', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="review_dates_filter"><?php esc_html_e( 'Review date', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="review_dates_filter" value="" <?php checked( $types, false ); ?> />&nbsp;<?php esc_html_e( 'All', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="review_dates_filter" value="last_export" <?php checked( $types, 'last_export' ); ?> />&nbsp;<?php esc_html_e( 'Since last export', 'woocommerce-exporter' ); ?>
        <img class="help_tip" data-tip="<?php esc_html_e( 'Export Reviews which have not previously been included in an export. Decided by whether the <code>_woo_cd_exported</code> custom Post meta key has not been assigned to an Review.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
    </p>
<?php
    ob_end_flush();
}

/**
 * Export templates
 *
 * This function generates the export template fields for the review export type.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_export_template_fields_review( $post_ID = 0 ) {

    $export_type = 'review';

    $fields = woo_ce_get_review_fields( 'full', $post_ID );

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
                    <label><?php esc_html_e( 'Review fields', 'woocommerce-exporter' ); ?></label>
                </p>
                <?php if ( ! empty( $fields ) ) { ?>
                    <table id="<?php echo esc_attr( $export_type ); ?>-fields" class="ui-sortable">
                        <tbody>
                            <?php foreach ( $fields as $field ) { ?>
                                <tr id="<?php echo esc_attr( $export_type ); ?>-<?php echo esc_attr( $field['reset'] ); ?>">
                                    <td>
                                        <label
                                            <?php if ( isset( $field['hover'] ) ) { ?>
                                            title="<?php echo esc_attr( $field['hover'] ); ?>"
                                            <?php } ?>
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
                    <!-- #<?php echo esc_attr( $export_type ); ?>-fields -->
                <?php } else { ?>
                    <p><?php esc_html_e( 'No Review fields were found.', 'woocommerce-exporter' ); ?></p>
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
