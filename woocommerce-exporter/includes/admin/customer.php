<?php
/**
 * HTML template for Filter Customers by Order Status widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Customers by Order Status widget
 * on the Store Exporter screen. It displays a checkbox to enable/disable the filter and a
 * dropdown menu to select the order statuses to filter by.
 */
function woo_ce_customers_filter_by_status() {

    $order_statuses = woo_ce_get_order_statuses();

    ob_start(); ?>
    <p><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Customers by Order Status', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-customers-filters-status" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $order_statuses ) ) { ?>
                    <select data-placeholder="<?php esc_html_e( 'Choose a Order Status...', 'woocommerce-exporter' ); ?>" name="customer_filter_status[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $order_statuses as $order_status ) { ?>
                            <option value="<?php echo esc_attr( $order_status->slug ); ?>"><?php echo esc_html( ucfirst( $order_status->name ) ); ?></option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Order Status\'s were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Order Status you want to filter exported Customers by. Default is to include all Order Status options.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-customers-filters-status -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Customers by User Role widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Customers by User Role widget on the Store Exporter screen.
 * It displays a checkbox to enable/disable the filter and a dropdown to select the user roles to filter by.
 */
function woo_ce_customers_filter_by_user_role() {

    $user_roles = woo_ce_get_user_roles();
    // Add Guest Role to the User Roles list.
    if ( ! empty( $user_roles ) ) {
        $user_roles['guest'] = array(
            'name'  => __( 'Guest', 'woocommerce-exporter' ),
            'count' => 1,
        );
    }

    ob_start();
    ?>
    <p><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Customers by User Role', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-customers-filters-user_role" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $user_roles ) ) { ?>
                    <select data-placeholder="<?php esc_html_e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="customer_filter_user_role[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $user_roles as $key => $user_role ) { ?>
                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( ucfirst( $user_role['name'] ) ); ?></option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the User Roles you want to filter exported Customers by. Default is to include all User Role options.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-customers-filters-user_role -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Customer Sorting widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Customer Sorting widget on the Store Exporter screen.
 * It displays a dropdown select field for choosing the sorting order of customers within the exported file.
 */
function woo_ce_customer_sorting() {

    $order = woo_ce_get_option( 'customer_order', 'ASC' );

    ob_start();
    ?>
    <p><label><?php esc_html_e( 'Customer Sorting', 'woocommerce-exporter' ); ?></label></p>
    <div>
        <select name="customer_order">
            <option value="ASC" <?php selected( 'ASC', $order ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
            <option value="DESC" <?php selected( 'DESC', $order ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Select the sorting of Customers within the exported file. By default this is set to export Customers by Order ID in Desending order.', 'woocommerce-exporter' ); ?></p>
    </div>
<?php
    ob_end_flush();
}

/**
 * HTML template for jump link to Custom Customer Fields within Order Options on Store Exporter screen.
 *
 * This function generates an HTML template for a jump link that allows users to navigate to the section
 * where they can manage custom customer fields on the Store Exporter screen.
 */
function woo_ce_customers_custom_fields_link() {

    ob_start();
    ?>
    <div id="export-customers-custom-fields-link">
        <p><a href="#export-customers-custom-fields"><?php esc_html_e( 'Manage Custom Customer Fields', 'woocommerce-exporter' ); ?></a></p>
    </div>
    <!-- #export-customers-custom-fields-link -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Custom Customers widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Custom Customers widget on the Store Exporter screen.
 * It displays a form with options to include additional custom Customer meta in the Export Customers table.
 * The saved meta will appear as new export fields to be selected from the Customer Fields list.
 */
function woo_ce_customers_custom_fields() {
    $custom_customers = woo_ce_get_option( 'custom_customers', '' );
    if ( $custom_customers ) {
        $custom_customers = implode( "\n", $custom_customers );
    }

    $troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

    ob_start();
    ?>
    <form method="post" id="export-customers-custom-fields" class="export-options customer-options">
        <div id="poststuff">

            <div class="postbox" id="export-options customer-options">
                <h3 class="hndle"><?php esc_html_e( 'Custom Customer Fields', 'woocommerce-exporter' ); ?></h3>
                <div class="inside">
                    <p class="description"><?php esc_html_e( 'To include additional custom Customer meta in the Export Customers table above fill the Customers text box then click Save Custom Fields. The saved meta will appear as new export fields to be selected from the Customer Fields list.', 'woocommerce-exporter' ); ?></p>
                    <p class="description">
                        <?php
                        // translators: %s: URL to the troubleshooting documentation.
                        wp_kses_post( sprintf( __( 'For more information on exporting custom Customer meta consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ) );
                        ?>
                    </p>
                    <table class="form-table">

                        <tr>
                            <th>
                                <label for="custom_customers"><?php esc_html_e( 'Customer meta', 'woocommerce-exporter' ); ?></label>
                            </th>
                            <td>
                                <textarea disabled="disabled" rows="5" cols="70"><?php echo esc_textarea( $custom_customers ); ?></textarea>
                                <p class="description">
                                    <?php echo wp_kses_post( __( 'Include additional custom Customer meta in your export file by adding each custom Customer meta name to a new line above.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ) ); ?>
                                    <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=customercustommetalink' ) ) ); ?></span>
                                </p>
                            </td>
                        </tr>

                    </table>
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
    <!-- #export-customers-custom-fields -->
<?php
    ob_end_flush();
}

/**
 * Renders the export template fields for customers.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_export_template_fields_customer( $post_ID = 0 ) {

    $export_type = 'customer';

    $fields = woo_ce_get_customer_fields( 'full', $post_ID );

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
                    <label><?php esc_html_e( 'Customer fields', 'woocommerce-exporter' ); ?></label>
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
                    <p><?php esc_html_e( 'No Customer fields were found.', 'woocommerce-exporter' ); ?></p>
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
