<?php
/**
 * HTML template for Filter Subscriptions by Subscription Date widget on Store Exporter screen.
 *
 * This function generates the HTML template for the filter subscriptions by subscription date widget.
 * It displays a set of radio buttons and input fields to filter subscriptions based on different date ranges.
 *
 * @since 1.0.0
 */
function woo_ce_subscriptions_filter_by_date() {

    $tomorrow                              = date( 'l', strtotime( 'tomorrow', current_time( 'timestamp' ) ) );
    $today                                 = date( 'l', current_time( 'timestamp' ) );
    $yesterday                             = date( 'l', strtotime( '-1 days', current_time( 'timestamp' ) ) );
    $current_month                         = date( 'F', current_time( 'timestamp' ) );
    $last_month                            = date( 'F', mktime( 0, 0, 0, date( 'n', current_time( 'timestamp' ) ) - 1, 1, date( 'Y', current_time( 'timestamp' ) ) ) );
    $current_year                          = date( 'Y', current_time( 'timestamp' ) );
    $last_year                             = date( 'Y', strtotime( '-1 year', current_time( 'timestamp' ) ) );
    $subscription_dates_variable           = woo_ce_get_option( 'subscription_dates_filter_variable', '' );
    $subscription_dates_variable_length    = woo_ce_get_option( 'subscription_dates_filter_variable_length', '' );
    $date_format                           = woo_ce_get_option( 'date_format', 'd/m/Y' );
    $subscription_dates_first_subscription = woo_ce_get_order_first_date( $date_format );
    $subscription_dates_last_subscription  = woo_ce_get_order_date_filter( 'today', 'from', $date_format );
    $types                                 = woo_ce_get_option( 'subscription_dates_filter' );
    $subscription_dates_from               = woo_ce_get_option( 'subscription_dates_from' );
    $subscription_dates_to                 = woo_ce_get_option( 'subscription_dates_to' );
    // Check if the Subscription Date To/From have been saved.
    if (
        empty( $subscription_dates_from ) ||
        empty( $subscription_dates_to )
    ) {
        if ( empty( $subscription_dates_from ) ) {
            $subscription_dates_from = $subscription_dates_first_subscription;
        }
        if ( empty( $subscription_dates_to ) ) {
            $subscription_dates_to = $subscription_dates_last_subscription;
        }
    }

    ob_start(); ?>
    <p><label><input type="checkbox" disabled="disabled" <?php checked( ! empty( $types ), true ); ?> /> <?php esc_html_e( 'Filter Subscriptions by Subscription Date', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-subscriptions-filters-date" class="separator">
        <ul>
            <li>
                <label><input type="radio" name="subscription_dates_filter" value="" <?php checked( $types, false ); ?> /> <?php esc_html_e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $subscription_dates_first_subscription ); ?> - <?php echo esc_html( $subscription_dates_last_subscription ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="subscription_dates_filter" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> /> <?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $tomorrow ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="subscription_dates_filter" value="today" <?php checked( $types, 'today' ); ?> /> <?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $today ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="subscription_dates_filter" value="yesterday" <?php checked( $types, 'yesterday' ); ?> /> <?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $yesterday ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="subscription_dates_filter" value="current_week" <?php checked( $types, 'current_week' ); ?> /> <?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="subscription_dates_filter" value="last_week" <?php checked( $types, 'last_week' ); ?> /> <?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="subscription_dates_filter" value="current_month" <?php checked( $types, 'current_month' ); ?> /> <?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_month ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="subscription_dates_filter" value="last_month" <?php checked( $types, 'last_month' ); ?> /> <?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_month ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="subscription_dates_filter" value="current_year" <?php checked( $types, 'current_year' ); ?> /> <?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="subscription_dates_filter" value="last_year" <?php checked( $types, 'last_year' ); ?> /> <?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="subscription_dates_filter" value="variable" <?php checked( $types, 'variable' ); ?> /> <?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?>
                    <input type="text" name="subscription_dates_filter_variable" class="text code" size="4" maxlength="4" value="<?php echo esc_attr( $subscription_dates_variable ); ?>" />
                    <select name="subscription_dates_filter_variable_length" style="vertical-align:top;">
                        <option value="" <?php selected( $subscription_dates_variable_length, '' ); ?>>&nbsp;</option>
                        <option value="second" <?php selected( $subscription_dates_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="minute" <?php selected( $subscription_dates_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="hour" <?php selected( $subscription_dates_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="day" <?php selected( $subscription_dates_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="week" <?php selected( $subscription_dates_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="month" <?php selected( $subscription_dates_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="year" <?php selected( $subscription_dates_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
                    </select>
                </div>
            </li>
            <li>
                <label><input type="radio" name="subscription_dates_filter" value="manual" <?php checked( $types, 'manual' ); ?> /> <?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <input type="text" size="10" maxlength="10" id="subscription_dates_from" name="subscription_dates_from" value="<?php echo ( $types == 'manual' ? esc_attr( $subscription_dates_from ) : esc_attr( $subscription_dates_first_subscription ) ); ?>" class="text code datepicker subscription_export" /> <?php esc_html_e( 'to', 'woocommerce-exporter' ); ?> <input type="text" size="10" maxlength="10" id="subscription_dates_to" name="subscription_dates_to" value="<?php echo ( $types == 'manual' ? esc_attr( $subscription_dates_to ) : esc_attr( $subscription_dates_last_subscription ) ); ?>" class="text code datepicker subscription_export" />
                    <p class="description"><?php esc_html_e( 'Filter the dates of Subscriptions to be included in the export. Default is the date of the first Subscription to today.', 'woocommerce-exporter' ); ?></p>
                </div>
            </li>
            <li>
                <label><input type="radio" name="subscription_dates_filter" value="last_export" <?php checked( $types, 'last_export' ); ?>s /> <?php esc_html_e( 'Since last export', 'woocommerce-exporter' ); ?></label>
                <p class="description"><?php esc_html_e( 'Export Subscriptions which have not previously been included in an export. Decided by whether the <code>_woo_cd_exported</code> custom Post meta key has not been assigned to an Subscription.', 'woocommerce-exporter' ); ?></p>
            </li>
        </ul>
    </div>
    <!-- #export-subscriptions-filters-date -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Subscriptions by Subscription Status widget on Store Exporter screen.
 *
 * This function generates the HTML template for the filter subscriptions by subscription status widget.
 * It displays a set of checkboxes to filter subscriptions based on different subscription statuses.
 *
 * @since 1.0.0
 */
function woo_ce_subscriptions_filter_by_subscription_status() {

    $subscription_statuses = woo_ce_get_subscription_statuses();
    $types                 = woo_ce_get_option( 'subscription_status' );

    ob_start();
    ?>
    <p><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Subscriptions by Subscription Status', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-subscriptions-filters-status" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $subscription_statuses ) ) { ?>
                    <select data-placeholder="<?php esc_html_e( 'Choose a Subscription Status...', 'woocommerce-exporter' ); ?>" name="subscription_filter_status[]" multiple class="chzn-select" style="width:95%;">
                        <option value=""></option>
                        <?php foreach ( $subscription_statuses as $key => $subscription_status ) { ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $key, $types ) : false ), true ); ?>><?php echo esc_html( $subscription_status ); ?></option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Subscription Status\'s have been found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Subscription Status options you want to filter exported Subscriptions by. Due to a limitation in WooCommerce Subscriptions you can only filter by a single Subscription Status. Default is to include all Subscription Status options.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-subscriptions-filters-status -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Subscriptions by Subscription Product widget on Store Exporter screen.
 *
 * This function generates the HTML template for the widget that allows filtering subscriptions by subscription product.
 *
 * @since 1.0.0
 */
function woo_ce_subscriptions_filter_by_subscription_product() {

    $products = woo_ce_get_subscription_products();
    $types    = woo_ce_get_option( 'subscription_product' );

    ob_start();
    ?>
    <p><label><input type="checkbox" disabled="disabled" <?php checked( ! empty( $types ), true ); ?> /> <?php esc_html_e( 'Filter Subscriptions by Subscription Product', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-subscriptions-filters-product" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $products ) ) { ?>
                    <select data-placeholder="<?php esc_html_e( 'Choose a Subscription Product...', 'woocommerce-exporter' ); ?>" name="subscription_filter_product[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $products as $product ) { ?>
                            <option value="<?php echo esc_attr( $product ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $product, $types ) : false ), true ); ?>><?php echo esc_html( woo_ce_format_post_title( get_the_title( $product ) ) ); ?> (<?php echo wp_kses_post( sprintf( __( 'SKU: %s', 'woocommerce-exporter' ), get_post_meta( $product, '_sku', true ) ) ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Subscription Products were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Subscription Product you want to filter exported Subscriptions by. Default is to include all Subscription Products.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-subscriptions-filters-status -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Subscriptions by Customer widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Subscriptions by Customer widget on the Store Exporter screen.
 * It displays a checkbox to enable/disable the filter and a dropdown/select box to choose the customer(s) to filter the subscriptions.
 * The template also includes a description and additional instructions based on the number of customers available.
 *
 * @since Unknown
 *
 * @return void
 */
function woo_ce_subscriptions_filter_by_customer() {

    $user_count = woo_ce_get_export_type_count( 'user' );
    $list_limit = apply_filters( 'woo_ce_subscription_filter_customer_list_limit', 100, $user_count );
    if ( $user_count < $list_limit ) {
        $customers = woo_ce_get_customers_list();
    }

    ob_start();
    ?>
    <p><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Subscriptions by Customer', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-subscriptions-filters-customer" class="separator">
        <ul>
            <li>
                <?php if ( $user_count < $list_limit ) { ?>
                    <select data-placeholder="<?php esc_html_e( 'Choose a Customer...', 'woocommerce-exporter' ); ?>" id="subscription_customer" name="subscription_filter_customer[]" multiple class="chzn-select" style="width:95%;">
                        <option value=""></option>
                        <?php if ( ! empty( $customers ) ) { ?>
                            <?php foreach ( $customers as $customer ) { ?>
                                <option value="<?php echo esc_attr( $customer->ID ); ?>"><?php echo wp_kses_post( sprintf( '%s (#%s - %s)', $customer->display_name, $customer->ID, $customer->user_email ) ); ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <input type="text" id="subscription_customer" name="subscription_filter_customer" size="20" class="text" />
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Filter Subscriptions by Customer (unique e-mail address) to be included in the export.', 'woocommerce-exporter' ); ?>
        <?php
        if ( $user_count > $list_limit ) {
            echo esc_html( ' ' . __( 'Enter a list of User ID\'s separated by a comma character.', 'woocommerce-exporter' ) );
        }
        ?>
        <?php esc_html_e( 'Default is to include all Subscriptions.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-subscriptions-filters-customer -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Subscriptions by Source widget on Store Exporter screen.
 */
function woo_ce_subscriptions_filter_by_source() {

    $types = false;

    ob_start();
    ?>
    <p><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Subscriptions by Source', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-subscriptions-filters-source" class="separator">
        <ul>
            <li value=""><label><input type="radio" name="subscription_filter_source" value="" <?php checked( $types, false ); ?> /><?php esc_html_e( 'Include both', 'woocommerce-exporter' ); ?></label></li>
            <li value="customer"><label><input type="radio" name="subscription_filter_source" value="customer" /><?php esc_html_e( 'Customer Subscriptions', 'woocommerce-exporter' ); ?></label></li>
            <li value="manual"><label><input type="radio" name="subscription_filter_source" value="manual" /><?php esc_html_e( 'Added via WordPress Administration', 'woocommerce-exporter' ); ?></label></li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Subscription Source you want to filter exported Subscriptions by. Default is to include all Subscription Sources.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-subscriptions-filters-source -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Subscription Sorting widget on Store Exporter screen.
 */
function woo_ce_subscription_sorting() {

    $orderby = woo_ce_get_option( 'subscription_orderby', 'ID' );
    $order   = woo_ce_get_option( 'subscription_order', 'ASC' );

    ob_start();
    ?>
    <p><label><?php esc_html_e( 'Subscription Sorting', 'woocommerce-exporter' ); ?></label></p>
    <div>
        <select name="subscription_orderby">
            <option value="ID" <?php selected( 'ID', $orderby ); ?>><?php esc_html_e( 'Subscription ID', 'woocommerce-exporter' ); ?></option>
            <option value="start_date" <?php selected( 'start_date', $orderby ); ?>><?php esc_html_e( 'Start date', 'woocommerce-exporter' ); ?></option>
            <option value="expiry_date" <?php selected( 'expiry_date', $orderby ); ?>><?php esc_html_e( 'Expiry date', 'woocommerce-exporter' ); ?></option>
            <option value="end_date" <?php selected( 'end_date', $orderby ); ?>><?php esc_html_e( 'End date', 'woocommerce-exporter' ); ?></option>
            <option value="status" <?php selected( 'status', $orderby ); ?>><?php esc_html_e( 'Status', 'woocommerce-exporter' ); ?></option>
            <option value="name" <?php selected( 'name', $orderby ); ?>><?php esc_html_e( 'Name', 'woocommerce-exporter' ); ?></option>
            <option value="order_id" <?php selected( 'order_id', $orderby ); ?>><?php esc_html_e( 'Order ID', 'woocommerce-exporter' ); ?></option>
        </select>
        <select name="subscription_order">
            <option value="ASC" <?php selected( 'ASC', $order ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
            <option value="DESC" <?php selected( 'DESC', $order ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Select the sorting of Subscriptions within the exported file. By default this is set to export Subscriptions by Start date in Desending order.', 'woocommerce-exporter' ); ?></p>
    </div>
<?php
    ob_end_flush();
}

/**
 * HTML template for Subscription Items Formatting on Store Exporter screen.
 */
function woo_ce_subscriptions_items_formatting() {

    $subscription_items_formatting = woo_ce_get_option( 'subscription_items_formatting', 'combined' );

    ob_start();
    ?>
    <tr class="export-options subscription-options">
        <th><label for="subscription_items"><?php esc_html_e( 'Subscription items formatting', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <ul>
                <li>
                    <label><input type="radio" name="subscription_items" value="combined" <?php checked( $subscription_items_formatting, 'combined' ); ?> />&nbsp;<?php esc_html_e( 'Place Subscription Items within a grouped single Subscription row', 'woocommerce-exporter' ); ?></label>
                    <p class="description"><?php echo wp_kses_post( __( 'For example: <code>Subscription Items: SKU</code> cell might contain <code>SPECK-IPHONE|INCASE-NANO|-</code> for 3 Subscription items within an Subscription', 'woocommerce-exporter' ) ); ?></p>
                </li>
                <li>
                    <label><input type="radio" name="subscription_items" value="individual" <?php checked( $subscription_items_formatting, 'individual' ); ?> />&nbsp;<?php esc_html_e( 'Place each Subscription Item within their own Subscription row', 'woocommerce-exporter' ); ?></label>
                    <p class="description"><?php esc_html_e( 'For example: An Subscription with 3 Subscription items will display a single Subscription item on each row', 'woocommerce-exporter' ); ?></p>
                </li>
            </ul>
            <p class="description"><?php esc_html_e( 'Choose how you would like Subscription Items to be presented within Subscriptions.', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>
<?php
    ob_end_flush();
}

/**
 * HTML template for jump link to Custom Subscription Fields within Subscription Options on Store Exporter screen.
 */
function woo_ce_subscriptions_custom_fields_link() {

    ob_start();
    ?>
    <div id="export-subscriptions-custom-fields-link">
        <p><a href="#export-subscriptions-custom-fields"><?php esc_html_e( 'Manage Custom Subscription Fields', 'woocommerce-exporter' ); ?></a></p>
    </div>
    <!-- #export-subscriptions-custom-fields-link -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Custom Subscriptions widget on Store Exporter screen.
 */
function woo_ce_subscriptions_custom_fields() {

    if ( $custom_subscriptions = woo_ce_get_option( 'custom_subscriptions', '' ) ) {
        $custom_subscriptions = implode( "\n", $custom_subscriptions );
    }

    $troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

    ob_start();
    ?>
    <form method="post" id="export-subscriptions-custom-fields" class="export-options subscription-options">
        <div id="poststuff">

            <div class="postbox" id="export-options">
                <h3 class="hndle"><?php esc_html_e( 'Custom Subscription Fields', 'woocommerce-exporter' ); ?></h3>
                <div class="inside">
                    <p class="description"><?php esc_html_e( 'To include additional custom Subscription meta in the Export Subscriptions table above fill the appropriate text box then click <em>Save Custom Fields</em>. The saved meta will appear as new export fields to be selected from the Subscription Fields list.', 'woocommerce-exporter' ); ?></p>
                    <p class="description"><?php echo wp_kses_post( sprintf( __( 'For more information on exporting custom Subscription meta consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ) ); ?></p>
                    <table class="form-table">

                        <tr>
                            <th>
                                <label for="custom_subscriptions"><?php esc_html_e( 'Subscription meta', 'woocommerce-exporter' ); ?></label>
                            </th>
                            <td>
                                <textarea disabled="disabled" rows="5" cols="70"><?php echo esc_textarea( $custom_subscriptions ); ?></textarea>
                                <p class="description">
                                    <?php echo wp_kses_post( __( 'Include additional custom Subscription meta in your export file by adding each custom Subscription meta name to a new line above. This is case sensitive.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ) ); ?>
                                </p>
                            </td>
                        </tr>
                        <?php do_action( 'woo_ce_subscriptions_custom_fields' ); ?>

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
    <!-- #export-subscriptions-custom-fields -->
<?php
    ob_end_flush();
}

/**
 * Renders the export options for subscriptions.
 *
 * This function is responsible for rendering the export options for subscriptions.
 * It takes an optional parameter $post_ID, which represents the ID of the post being processed.
 * The function uses output buffering to capture the HTML output and flushes it at the end.
 *
 * @param int $post_ID The ID of the post being processed. Default is 0.
 * @return void
 */
function woo_ce_scheduled_export_filters_subscription( $post_ID = 0 ) {

    ob_start();
    ?>
    <div class="export-options subscription-options">

        <?php do_action( 'woo_ce_scheduled_export_filters_subscription', $post_ID ); ?>

    </div>
    <!-- .subscription-options -->

<?php
    ob_end_flush();
}

/**
 * Renders the subscription filter form field.
 *
 * This function is responsible for rendering the subscription filter form field in the admin area.
 * It displays a set of radio buttons and input fields for selecting different subscription date filters.
 *
 * @param int $post_ID The ID of the post.
 * @return void
 */
function woo_ce_scheduled_export_subscription_filter_by_subscription_date( $post_ID = 0 ) {

    $types                                    = get_post_meta( $post_ID, '_filter_subscription_date', true );
    $subscription_filter_dates_from           = get_post_meta( $post_ID, '_filter_subscription_dates_from', true );
    $subscription_filter_dates_to             = get_post_meta( $post_ID, '_filter_subscription_dates_to', true );
    $subscription_filter_date_variable        = get_post_meta( $post_ID, '_filter_subscription_date_variable', true );
    $subscription_filter_date_variable_length = get_post_meta( $post_ID, '_filter_subscription_date_variable_length', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="subscription_dates_filter"><?php esc_html_e( 'Subscription date', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="subscription_dates_filter" value="" <?php checked( $types, false ); ?> />&nbsp;<?php esc_html_e( 'All', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="subscription_dates_filter" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> />&nbsp;<?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="subscription_dates_filter" value="today" <?php checked( $types, 'today' ); ?> />&nbsp;<?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="subscription_dates_filter" value="yesterday" <?php checked( $types, 'yesterday' ); ?> />&nbsp;<?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="subscription_dates_filter" value="current_week" <?php checked( $types, 'current_week' ); ?> />&nbsp;<?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="subscription_dates_filter" value="last_week" <?php checked( $types, 'last_week' ); ?> />&nbsp;<?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="subscription_dates_filter" value="current_month" <?php checked( $types, 'current_month' ); ?> />&nbsp;<?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="subscription_dates_filter" value="last_month" <?php checked( $types, 'last_month' ); ?> />&nbsp;<?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="subscription_dates_filter" value="current_year" <?php checked( $types, 'current_year' ); ?> />&nbsp;<?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="subscription_dates_filter" value="last_year" <?php checked( $types, 'last_year' ); ?> />&nbsp;<?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="subscription_dates_filter" value="variable" <?php checked( $types, 'variable' ); ?> />&nbsp;<?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?><br />
        <span style="float:left; margin-right:6px;"><?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?></span>
        <input type="text" name="subscription_dates_filter_variable" class="sized" size="4" value="<?php echo esc_attr( $subscription_filter_date_variable ); ?>" />
        <select name="subscription_dates_filter_variable_length">
            <option value="" <?php selected( $subscription_filter_date_variable_length, '' ); ?>>&nbsp;</option>
            <option value="second" <?php selected( $subscription_filter_date_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
            <option value="minute" <?php selected( $subscription_filter_date_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
            <option value="hour" <?php selected( $subscription_filter_date_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
            <option value="day" <?php selected( $subscription_filter_date_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
            <option value="week" <?php selected( $subscription_filter_date_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
            <option value="month" <?php selected( $subscription_filter_date_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
            <option value="year" <?php selected( $subscription_filter_date_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
        </select><br class="clear" />
        <input type="radio" name="subscription_dates_filter" value="manual" <?php checked( $types, 'manual' ); ?> />&nbsp;<?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?><br />
        <input type="text" name="subscription_dates_from" value="<?php echo esc_attr( $subscription_filter_dates_from ); ?>" size="10" maxlength="10" class="sized datepicker subscription_export" /> <span style="float:left; margin-right:6px;"><?php esc_html_e( 'to', 'woocommerce-exporter' ); ?></span> <input type="text" name="subscription_dates_to" value="<?php echo esc_attr( $subscription_filter_dates_to ); ?>" size="10" maxlength="10" class="sized datepicker subscription_export" /><br class="clear" />
        <input type="radio" name="subscription_dates_filter" value="last_export" <?php checked( $types, 'last_export' ); ?> />&nbsp;<?php esc_html_e( 'Since last export', 'woocommerce-exporter' ); ?>
        <img class="help_tip" data-tip="<?php esc_html_e( 'Export Subscriptions which have not previously been included in an export. Decided by whether the <code>_woo_cd_exported</code> custom Post meta key has not been assigned to a Subscription.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
    </p>
<?php
    ob_end_flush();
}

/**
 * HTML template for Subscription Status filter on Edit Scheduled Export screen.
 *
 * This function generates the HTML template for the Subscription Status filter on the Edit Scheduled Export screen.
 * It displays a dropdown select field with the available subscription statuses and allows the user to select one or more statuses to filter the exported subscriptions.
 *
 * @param int $post_ID The ID of the post being edited.
 * @return void
 */
function woo_ce_scheduled_export_subscription_filter_by_subscription_status( $post_ID ) {

    $subscription_statuses = woo_ce_get_subscription_statuses();
    $types                 = get_post_meta( $post_ID, '_filter_subscription_status', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="subscription_filter_status"><?php esc_html_e( 'Subscription Status', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $subscription_statuses ) ) { ?>
            <select data-placeholder="<?php esc_html_e( 'Choose a Subscription Status...', 'woocommerce-exporter' ); ?>" name="subscription_filter_status[]" multiple class="chzn-select" style="width:95%;">
                <option value=""></option>
                <?php foreach ( $subscription_statuses as $key => $subscription_status ) { ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $key, $types ) : false ), true ); ?>><?php echo esc_html( $subscription_status ); ?></option>
                <?php } ?>
            </select>
        <?php } else { ?>
            <?php esc_html_e( 'No Subscription Status\'s have been found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
        <img class="help_tip" data-tip="<?php esc_html_e( 'Select the Subscription Status options you want to filter exported Subscriptions by. Due to a limitation in WooCommerce Subscriptions you can only filter by a single Subscription Status. Default is to include all Subscription Status options.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
    </p>
<?php
    ob_end_flush();
}

/**
 * HTML template for Subscription Sorting filter on Edit Scheduled Export screen.
 *
 * @param int $post_ID The ID of the post.
 * @return void
 */
function woo_ce_scheduled_export_subscription_filter_orderby( $post_ID ) {

    $orderby = get_post_meta( $post_ID, '_filter_subscription_orderby', true );
    // Default to Subscription ID.
    if ( $orderby == false ) {
        $orderby = 'ID';
    }

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="subscription_filter_orderby"><?php esc_html_e( 'Subscription Sorting', 'woocommerce-exporter' ); ?></label>
            <select id="subscription_filter_orderby" name="subscription_filter_orderby">
                <option value="ID" <?php selected( 'ID', $orderby ); ?>><?php esc_html_e( 'Subscription ID', 'woocommerce-exporter' ); ?></option>
                <option value="start_date" <?php selected( 'start_date', $orderby ); ?>><?php esc_html_e( 'Start date', 'woocommerce-exporter' ); ?></option>
                <option value="expiry_date" <?php selected( 'expiry_date', $orderby ); ?>><?php esc_html_e( 'Expiry date', 'woocommerce-exporter' ); ?></option>
                <option value="end_date" <?php selected( 'end_date', $orderby ); ?>><?php esc_html_e( 'End date', 'woocommerce-exporter' ); ?></option>
                <option value="status" <?php selected( 'status', $orderby ); ?>><?php esc_html_e( 'Status', 'woocommerce-exporter' ); ?></option>
                <option value="name" <?php selected( 'name', $orderby ); ?>><?php esc_html_e( 'Name', 'woocommerce-exporter' ); ?></option>
                <option value="order_id" <?php selected( 'order_id', $orderby ); ?>><?php esc_html_e( 'Order ID', 'woocommerce-exporter' ); ?></option>
            </select>
        </p>
    </div>
    <!-- .options_group -->
<?php
    ob_end_flush();
}

/**
 * Renders the HTML for the subscription filter by subscription product field.
 *
 * @param int $post_ID The ID of the post being edited.
 */
function woo_ce_scheduled_export_subscription_filter_by_subscription_product( $post_ID ) {

    $products = woo_ce_get_subscription_products();
    $types    = get_post_meta( $post_ID, '_filter_subscription_sku', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="subscription_filter_sku"><?php esc_html_e( 'Subscription Product', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $products ) ) { ?>
            <select data-placeholder="<?php esc_html_e( 'Choose a Subscription Product...', 'woocommerce-exporter' ); ?>" name="subscription_filter_sku[]" multiple class="chzn-select" style="width:95%;">
                <?php foreach ( $products as $product ) { ?>
                    <option value="<?php echo esc_attr( $product ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $product, $types ) : false ), true ); ?>><?php echo esc_html( woo_ce_format_post_title( get_the_title( $product ) ) ); ?> (<?php echo wp_kses_post( sprintf( __( 'SKU: %s', 'woocommerce-exporter' ), get_post_meta( $product, '_sku', true ) ) ); ?>)</option>
                <?php } ?>
            </select>
        <?php } else { ?>
            <?php esc_html_e( 'No Subscription Products were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
        <img class="help_tip" data-tip="<?php esc_html_e( 'Select the Subscription Product you want to filter exported Subscriptions by. Default is to include all Subscription Products.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
    </p>
<?php
    ob_end_flush();
}

/**
 * Formats the subscription items for scheduled export.
 *
 * This function retrieves the subscription items formatting option from the post meta.
 * If the option is not set, it defaults to the value set in the WooCommerce Store Exporter Deluxe settings.
 * It then outputs a form field with radio buttons for selecting the subscription items formatting.
 *
 * @param int $post_ID The ID of the post.
 * @return void
 */
function woo_ce_scheduled_export_subscription_items_formatting( $post_ID = 0 ) {

    $types = get_post_meta( $post_ID, '_filter_subscription_items', true );
    // Default to Quick Export > Subscription items formatting.
    if ( empty( $types ) ) {
        $types = woo_ce_get_option( 'subscription_items_formatting', 'combined' );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="subscription_items_filter"><?php esc_html_e( 'Subscription items formatting', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="subscription_items_filter" value="combined" <?php checked( $types, 'combined' ); ?> />&nbsp;<?php esc_html_e( 'Place Subscription Items within a grouped single Subscription row', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="subscription_items_filter" value="individual" <?php checked( $types, 'individual' ); ?> />&nbsp;<?php esc_html_e( 'Place each Subscription Item within their own Subscription row', 'woocommerce-exporter' ); ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Export templates
 *
 * This function generates the export template fields for subscriptions.
 *
 * @param int $post_ID The ID of the post.
 * @return void
 */
function woo_ce_export_template_fields_subscription( $post_ID = 0 ) {

    $export_type = 'subscription';

    $fields = woo_ce_get_subscription_fields( 'full', $post_ID );
    $labels = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );
    // Check if labels is empty.
    if ( $labels == false ) {
        $labels = array();
    }

    ob_start();
    ?>
    <div class="export-options <?php echo esc_attr( $export_type ); ?>-options">

        <div class="options_group">
            <div class="form-field discount_type_field">
                <p class="form-field discount_type_field ">
                    <label><?php esc_html_e( 'Subscription fields', 'woocommerce-exporter' ); ?></label>
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
                    <p><?php esc_html_e( 'No Subscription fields were found.', 'woocommerce-exporter' ); ?></p>
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
