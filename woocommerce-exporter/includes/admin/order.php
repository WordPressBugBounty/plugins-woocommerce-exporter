<?php
/**
 * HTML template for Filter Orders by Order Date widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Orders by Order Date widget on the Store Exporter screen.
 * It displays a set of radio buttons and input fields for filtering orders based on different date criteria.
 */
function woo_ce_orders_filter_by_date() {

    $tomorrow                    = date( 'l', strtotime( 'tomorrow', current_time( 'timestamp' ) ) );
    $today                       = date( 'l', current_time( 'timestamp' ) );
    $yesterday                   = date( 'l', strtotime( '-1 days', current_time( 'timestamp' ) ) );
    $current_month               = date( 'F', current_time( 'timestamp' ) );
    $last_month                  = date( 'F', mktime( 0, 0, 0, date( 'n', current_time( 'timestamp' ) ) - 1, 1, date( 'Y', current_time( 'timestamp' ) ) ) );
    $current_year                = date( 'Y', current_time( 'timestamp' ) );
    $last_year                   = date( 'Y', strtotime( '-1 year', current_time( 'timestamp' ) ) );
    $order_dates_variable        = woo_ce_get_option( 'order_dates_filter_variable', '' );
    $order_dates_variable_length = woo_ce_get_option( 'order_dates_filter_variable_length', '' );
    $date_format                 = woo_ce_get_option( 'date_format', 'd/m/Y' );
    $order_dates_first_order     = woo_ce_get_order_first_date( $date_format );
    $order_dates_last_order      = woo_ce_get_order_date_filter( 'today', 'from', $date_format );
    $types                       = woo_ce_get_option( 'order_dates_filter' );
    $order_dates_from            = woo_ce_get_option( 'order_dates_from' );
    $order_dates_to              = woo_ce_get_option( 'order_dates_to' );
    // Check if the Order Date To/From have been saved.
    if (
        empty( $order_dates_from ) ||
        empty( $order_dates_to )
    ) {
        if ( empty( $order_dates_from ) ) {
            $order_dates_from = $order_dates_first_order;
        }
        if ( empty( $order_dates_to ) ) {
            $order_dates_to = $order_dates_last_order;
        }
    }

    ob_start(); ?>
    <p><label><input type="checkbox" name="orders_filters[date]" id="orders-filters-date" <?php checked( ! empty( $types ), true ); ?> /> <?php esc_html_e( 'Filter Orders by Order Date', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-orders-filters-date" class="separator">
        <ul>
            <li>
                <label><input type="radio" name="order_dates_filter" value="" <?php checked( $types, false ); ?> /> <?php esc_html_e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $order_dates_first_order ); ?> - <?php echo esc_html( $order_dates_last_order ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_dates_filter" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> /> <?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $tomorrow ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_dates_filter" value="today" <?php checked( $types, 'today' ); ?> /> <?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $today ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_dates_filter" value="yesterday" <?php checked( $types, 'yesterday' ); ?> /> <?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $yesterday ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_dates_filter" value="current_week" <?php checked( $types, 'current_week' ); ?> /> <?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="order_dates_filter" value="last_week" <?php checked( $types, 'last_week' ); ?> /> <?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="order_dates_filter" value="current_month" <?php checked( $types, 'current_month' ); ?> /> <?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_month ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_dates_filter" value="last_month" <?php checked( $types, 'last_month' ); ?> /> <?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_month ); ?>)</label>
            </li>
            <!--
            <li>
                <label><input type="radio" name="order_dates_filter" value="last_quarter" /> <?php esc_html_e( 'Last quarter', 'woocommerce-exporter' ); ?> (Nov. - Jan.)</label>
            </li>
            -->
            <li>
                <label><input type="radio" name="order_dates_filter" value="current_year" <?php checked( $types, 'current_year' ); ?> /> <?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_dates_filter" value="last_year" <?php checked( $types, 'last_year' ); ?> /> <?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_dates_filter" value="variable" <?php checked( $types, 'variable' ); ?> /> <?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?>
                    <input type="text" name="order_dates_filter_variable" class="text code" size="4" maxlength="4" value="<?php echo esc_attr( $order_dates_variable ); ?>" />
                    <select name="order_dates_filter_variable_length" style="vertical-align:top;">
                        <option value="" <?php selected( $order_dates_variable_length, '' ); ?>>&nbsp;</option>
                        <option value="second" <?php selected( $order_dates_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="minute" <?php selected( $order_dates_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="hour" <?php selected( $order_dates_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="day" <?php selected( $order_dates_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="week" <?php selected( $order_dates_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="month" <?php selected( $order_dates_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="year" <?php selected( $order_dates_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
                    </select>
                </div>
            </li>
            <li>
                <label><input type="radio" name="order_dates_filter" value="manual" <?php checked( $types, 'manual' ); ?> /> <?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <input type="text" size="10" maxlength="10" id="order_dates_from" name="order_dates_from" value="<?php echo ( 'manual' === $types ? esc_attr( $order_dates_from ) : esc_attr( $order_dates_first_order ) ); ?>" class="text code datepicker order_export" /> <?php esc_html_e( 'to', 'woocommerce-exporter' ); ?> <input type="text" size="10" maxlength="10" id="order_dates_to" name="order_dates_to" value="<?php echo ( 'manual' === $types ? esc_attr( $order_dates_to ) : esc_attr( $order_dates_last_order ) ); ?>" class="text code datepicker order_export" />
                    <p class="description"><?php esc_html_e( 'Filter the dates of Orders to be included in the export. Default is the date of the first Order to today.', 'woocommerce-exporter' ); ?></p>
                </div>
            </li>
            <li>
                <label><input type="radio" name="order_dates_filter" value="last_export" <?php checked( $types, 'last_export' ); ?>s /> <?php esc_html_e( 'Since last export', 'woocommerce-exporter' ); ?></label>
                <p class="description"><?php esc_html_e( 'Export Orders which have not previously been included in an export. Decided by whether the <code>_woo_cd_exported</code> custom Post meta key has not been assigned to an Order.', 'woocommerce-exporter' ); ?></p>
            </li>
        </ul>
    </div>
    <!-- #export-orders-filters-date -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Orders by Order Modified Date widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Orders by Order Modified Date widget
 * on the Store Exporter screen. It displays a set of radio buttons and input fields for selecting
 * different date filters and options.
 */
function woo_ce_orders_filter_by_modified_date() {

    $tomorrow                    = date( 'l', strtotime( 'tomorrow', current_time( 'timestamp' ) ) );
    $today                       = date( 'l', current_time( 'timestamp' ) );
    $yesterday                   = date( 'l', strtotime( '-1 days', current_time( 'timestamp' ) ) );
    $current_month               = date( 'F', current_time( 'timestamp' ) );
    $last_month                  = date( 'F', mktime( 0, 0, 0, date( 'n', current_time( 'timestamp' ) ) - 1, 1, date( 'Y', current_time( 'timestamp' ) ) ) );
    $current_year                = date( 'Y', current_time( 'timestamp' ) );
    $last_year                   = date( 'Y', strtotime( '-1 year', current_time( 'timestamp' ) ) );
    $order_dates_variable        = woo_ce_get_option( 'order_modified_dates_filter_variable', '' );
    $order_dates_variable_length = woo_ce_get_option( 'order_modified_dates_filter_variable_length', '' );
    $date_format                 = woo_ce_get_option( 'date_format', 'd/m/Y' );
    $order_dates_first_order     = woo_ce_get_order_first_date( $date_format );
    $order_dates_last_order      = woo_ce_get_order_date_filter( 'today', 'from', $date_format );
    $types                       = woo_ce_get_option( 'order_modified_dates_filter' );
    $order_dates_from            = woo_ce_get_option( 'order_modified_dates_from' );
    $order_dates_to              = woo_ce_get_option( 'order_modified_dates_to' );
    // Check if the Order Date To/From have been saved.
    if (
        empty( $order_dates_from ) ||
        empty( $order_dates_to )
    ) {
        if ( empty( $order_dates_from ) ) {
            $order_dates_from = $order_dates_first_order;
        }
        if ( empty( $order_dates_to ) ) {
            $order_dates_to = $order_dates_last_order;
        }
    }

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Orders by Order Modified Date', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-modified-date" class="separator">
        <ul>
            <li>
                <label><input type="radio" name="order_modified_dates_filter" value="" <?php checked( $types, false ); ?> /> <?php esc_html_e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $order_dates_first_order ); ?> - <?php echo esc_html( $order_dates_last_order ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_modified_dates_filter" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> /> <?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $tomorrow ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_modified_dates_filter" value="today" <?php checked( $types, 'today' ); ?> /> <?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $today ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_modified_dates_filter" value="yesterday" <?php checked( $types, 'yesterday' ); ?> /> <?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $yesterday ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_modified_dates_filter" value="current_week" <?php checked( $types, 'current_week' ); ?> /> <?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="order_modified_dates_filter" value="last_week" <?php checked( $types, 'last_week' ); ?> /> <?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="order_modified_dates_filter" value="current_month" <?php checked( $types, 'current_month' ); ?> /> <?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_month ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_modified_dates_filter" value="last_month" <?php checked( $types, 'last_month' ); ?> /> <?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_month ); ?>)</label>
            </li>
            <!--
            <li>
                <label><input type="radio" name="order_modified_dates_filter" value="last_quarter" /> <?php esc_html_e( 'Last quarter', 'woocommerce-exporter' ); ?> (Nov. - Jan.)</label>
            </li>
            -->
            <li>
                <label><input type="radio" name="order_modified_dates_filter" value="current_year" <?php checked( $types, 'current_year' ); ?> /> <?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_modified_dates_filter" value="last_year" <?php checked( $types, 'last_year' ); ?> /> <?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="order_modified_dates_filter" value="variable" <?php checked( $types, 'variable' ); ?> /> <?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?>
                    <input type="text" name="order_modified_dates_filter_variable" class="text code" size="4" maxlength="4" value="<?php echo esc_attr( $order_dates_variable ); ?>" />
                    <select name="order_modified_dates_filter_variable_length" style="vertical-align:top;">
                        <option value="" <?php selected( $order_dates_variable_length, '' ); ?>>&nbsp;</option>
                        <option value="second" <?php selected( $order_dates_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="minute" <?php selected( $order_dates_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="hour" <?php selected( $order_dates_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="day" <?php selected( $order_dates_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="week" <?php selected( $order_dates_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="month" <?php selected( $order_dates_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="year" <?php selected( $order_dates_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
                    </select>
                </div>
            </li>
            <li>
                <label><input type="radio" name="order_modified_dates_filter" value="manual" <?php checked( $types, 'manual' ); ?> /> <?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <input type="text" size="10" maxlength="10" id="order_modified_dates_from" name="order_modified_dates_from" value="<?php echo ( 'manual' === $types ? esc_attr( $order_dates_from ) : esc_attr( $order_dates_first_order ) ); ?>" class="text code datepicker order_export" /> <?php esc_html_e( 'to', 'woocommerce-exporter' ); ?> <input type="text" size="10" maxlength="10" id="order_modified_dates_to" name="order_modified_dates_to" value="<?php echo ( 'manual' === $types ? esc_attr( $order_dates_to ) : esc_attr( $order_dates_last_order ) ); ?>" class="text code datepicker order_export" />
                    <p class="description"><?php esc_html_e( 'Filter the dates of Orders to be included in the export. Default is the date of the first Order to today.', 'woocommerce-exporter' ); ?></p>
                </div>
            </li>
        </ul>
    </div>
    <!-- #export-orders-filters-date -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Orders by Order Status widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Orders by Order Status widget
 * on the Store Exporter screen. It displays a checkbox to enable/disable the filter,
 * a dropdown to select the order statuses, and a description for the filter.
 */
function woo_ce_orders_filter_by_status() {

    $order_statuses = woo_ce_get_order_statuses();
    $types          = woo_ce_get_option( 'order_status', array() );
    if ( empty( $types ) ) {
        $types = array();
    }

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Orders by Order Status', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-status" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $order_statuses ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Order Status...', 'woocommerce-exporter' ); ?>" name="order_filter_status[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $order_statuses as $order_status ) { ?>
                            <option value="<?php echo esc_attr( $order_status->slug ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $order_status->slug, $types, false ), true ) : '' ); ?><?php disabled( 0, $order_status->count ); ?>><?php echo esc_html( ucfirst( $order_status->name ) ); ?> (<?php echo esc_html( $order_status->count ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Order Status\'s were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Order Status you want to filter exported Orders by. Default is to include all Order Status options.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-orders-filters-status -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Orders by Customer widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Orders by Customer widget on the Store Exporter screen.
 * It provides options to filter orders based on customer selection.
 */
function woo_ce_orders_filter_by_customer() {

    if ( apply_filters( 'woo_ce_override_orders_filter_by_customer', true ) == false ) {
        return;
    }

    $user_count = woo_ce_get_export_type_count( 'user' );
    $list_limit = apply_filters( 'woo_ce_order_filter_customer_list_limit', 100, $user_count );
    if ( $user_count < $list_limit ) {
        $customers = woo_ce_get_customers_list();
    }
    $types = woo_ce_get_option( 'order_customer', array() );
    if ( ! is_array( $types ) ) {
        $types = array();
    }
    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Orders by Customer', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-customer" class="separator">
        <ul>
            <li>
                <?php if ( $user_count < $list_limit ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Customer...', 'woocommerce-exporter' ); ?>" id="order_customer" name="order_filter_customer[]" multiple class="chzn-select" style="width:95%;">
                        <option value=""><?php esc_html_e( 'Show all customers', 'woocommerce-exporter' ); ?></option>
                        <?php if ( ! empty( $customers ) ) { ?>
                            <?php foreach ( $customers as $customer ) { ?>
                                <option value="<?php echo esc_attr( $customer->ID ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $customer->ID, $types ) : false ), true ); ?>><?php echo esc_html( sprintf( '%s (#%s - %s)', $customer->display_name, $customer->ID, $customer->user_email ) ); ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <input type="text" id="order_customer" name="order_filter_customer" value="<?php echo esc_attr( ( ! empty( $types ) ? implode( ',', $types ) : '' ) ); ?>" size="20" class="text" />
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Filter Orders by Customer (unique e-mail address) to be included in the export.', 'woocommerce-exporter' ); ?>
        <?php
        if ( $user_count > $list_limit ) {
            echo ' ' . esc_html__( 'Enter a list of User ID\'s separated by a comma character.', 'woocommerce-exporter' );
        }
        ?>
        <?php esc_html_e( 'Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-orders-filters-customer -->
<?php
    ob_end_flush();
}

/**
 * Renders the HTML for filtering orders by billing country.
 *
 * This function generates the HTML markup for a checkbox and a dropdown select
 * to filter orders by billing country. It uses the `woo_ce_allowed_countries()`
 * function to get the list of allowed countries and the `woo_ce_get_option()`
 * function to get the selected countries for filtering.
 */
function woo_ce_orders_filter_by_billing_country() {

    $countries = woo_ce_allowed_countries();
    $types     = woo_ce_get_option( 'order_billing_country', array() );

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Orders by Billing Country', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-billing_country" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $countries ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Billing Country...', 'woocommerce-exporter' ); ?>" id="order_billing_country" name="order_filter_billing_country[]" multiple class="chzn-select" style="width:95%;">
                        <option value=""><?php esc_html_e( 'Show all Countries', 'woocommerce-exporter' ); ?></option>
                        <?php if ( $countries ) { ?>
                            <?php foreach ( $countries as $country_prefix => $country ) { ?>
                                <option value="<?php echo esc_attr( $country_prefix ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $country_prefix, $types, false ), true ) : '' ); ?>><?php echo esc_html( sprintf( '%s (%s)', $country, $country_prefix ) ); ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Countries were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Filter Orders by Billing Country to be included in the export. Default is to include all Countries.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-orders-filters-customer -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Orders by Shipping Country widget on Store Exporter screen.
 *
 * This function generates the HTML markup for the Filter Orders by Shipping Country widget
 * on the Store Exporter screen. It displays a checkbox to enable/disable the filter and
 * a dropdown select field to choose the shipping countries to include in the export.
 */
function woo_ce_orders_filter_by_shipping_country() {

    $countries = woo_ce_allowed_countries();
    $types     = woo_ce_get_option( 'order_shipping_country', array() );

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Orders by Shipping Country', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-shipping_country" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $countries ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Shipping Country...', 'woocommerce-exporter' ); ?>" id="order_shipping_country" name="order_filter_shipping_country[]" multiple class="chzn-select" style="width:95%;">
                        <option value=""><?php esc_html_e( 'Show all Countries', 'woocommerce-exporter' ); ?></option>
                        <?php foreach ( $countries as $country_prefix => $country ) { ?>
                            <option value="<?php echo esc_attr( $country_prefix ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $country_prefix, $types, false ), true ) : '' ); ?>><?php echo esc_html( sprintf( '%s (%s)', $country, $country_prefix ) ); ?></option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Countries were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Filter Orders by Shipping Country to be included in the export. Default is to include all Countries.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-orders-filters-customer -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Orders by User Role widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Orders by User Role widget on the Store Exporter screen.
 * It displays a checkbox to enable/disable the filter and a dropdown to select the user roles to filter the exported orders.
 */
function woo_ce_orders_filter_by_user_role() {

    $user_roles = woo_ce_get_user_roles();
    // Add Guest Role to the User Roles list.
    if ( ! empty( $user_roles ) ) {
        $user_roles['guest'] = array(
            'name'  => __( 'Guest', 'woocommerce-exporter' ),
            'count' => 1,
        );
    }
    $types = woo_ce_get_option( 'order_user_roles', array() );

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Orders by User Role', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-user_role" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $user_roles ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="order_filter_user_role[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $user_roles as $key => $user_role ) { ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo esc_html( ucfirst( $user_role['name'] ) ); ?></option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the User Roles you want to filter exported Orders by. Default is to include all User Role options.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-orders-filters-user_role -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Orders by Coupon Code widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Orders by Coupon Code widget on the Store Exporter screen.
 * It allows users to filter exported orders based on coupon codes.
 */
function woo_ce_orders_filter_by_coupon() {

    if ( apply_filters( 'woo_ce_override_orders_filter_by_coupon', true ) == false ) {
        return;
    }

    $coupon_count = woo_ce_get_export_type_count( 'coupon' );
    $list_limit   = apply_filters( 'woo_ce_order_filter_coupon_list_limit', 500, $coupon_count );
    if ( $coupon_count < $list_limit ) {

        $args = array(
            'coupon_orderby' => 'ID',
            'coupon_order'   => 'DESC',
        );

        // Allow other developers to bake in their own filters.
        $args = apply_filters( 'woo_ce_orders_filter_by_coupon_args', $args );

        $coupons = woo_ce_get_coupons( $args );
    }
    $types = woo_ce_get_option( 'order_coupon', array() );

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Orders by Coupon Code', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-coupon" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $coupons ) ) { ?>
                    <?php if ( $coupon_count < $list_limit ) { ?>
                        <select data-placeholder="<?php esc_attr_e( 'Choose a Coupon...', 'woocommerce-exporter' ); ?>" name="order_filter_coupon[]" multiple class="chzn-select" style="width:95%;">
                            <?php foreach ( $coupons as $coupon ) { ?>
                                <option value="<?php echo esc_attr( $coupon ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $coupon, $types, false ), true ) : '' ); ?><?php disabled( 0, woo_ce_get_coupon_code_usage( get_the_title( $coupon ) ) ); ?>><?php echo esc_html( get_the_title( $coupon ) ); ?> (<?php echo esc_html( woo_ce_get_coupon_code_usage( get_the_title( $coupon ) ) ); ?>)</option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <input type="text" id="order_coupon" name="order_filter_coupon" size="20" class="text" />
                    <?php } ?>
                <?php } else { ?>
                    <?php esc_html_e( 'No Coupons were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Coupon Codes you want to filter exported Orders by. Default is to include all Orders with and without assigned Coupon Codes.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-orders-filters-coupon -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Orders by Order ID widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Orders by Order ID widget on the Store Exporter screen.
 * It displays a checkbox to enable/disable the filter, an input field to enter the order IDs, and a description of the filter.
 * The function also checks if the Sequential Order Number export plugin is active and updates the label and description accordingly.
 */
function woo_ce_orders_filter_by_order_id() {

    $types = woo_ce_get_option( 'order_order_ids' );

    $label       = __( 'Filter Orders by Order ID', 'woocommerce-exporter' );
    $description = __( 'Enter the Order ID\'s you want to filter exported Orders by. Multiple Order ID\'s can be entered separated by the \',\' (comma) character, Order ID ranges can be entered separated by the \'-\' (dash) character. Default is to include all Orders.', 'woocommerce-exporter' );

    // Check if we're looking up a Sequential Order Number.
    $has_seq = ( woo_ce_detect_export_plugin( 'seq' ) || woo_ce_detect_export_plugin( 'seq_pro' ) ? true : false );
    if ( $has_seq ) {
        $label       = __( 'Filter Orders by Invoice Number', 'woocommerce-exporter' );
        $description = __( 'Enter the Invoice Number\'s you want to filter exported Orders by. Multiple Invoice Number\'s can be entered separated by the \',\' (comma) character, Invoice Number ranges can be entered separated by the \'-\' (dash) character. Default is to include all Orders.', 'woocommerce-exporter' );
    }

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php echo esc_html( $label ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-id" class="separator">
        <ul>
            <li>
                <input type="text" id="order_filter_id" name="order_filter_id" placeholder="1000,1001,1002,1000-1002" value="<?php echo esc_attr( ( ! empty( $types ) ? $types : '' ) ); ?>" class="text code" style="width:95%;" />
            </li>
        </ul>
        <p class="description"><?php echo wp_kses_post( $description ); ?></p>
    </div>
    <!-- #export-orders-filters-id -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Orders by Payment Gateway widget on Store Exporter screen.
 *
 * This function generates the HTML markup for the Filter Orders by Payment Gateway widget on the Store Exporter screen.
 * It displays a checkbox to enable/disable the filter and a dropdown to select the payment gateways for filtering.
 */
function woo_ce_orders_filter_by_payment_gateway() {

    $payment_gateways = woo_ce_get_order_payment_gateways();
    $types            = woo_ce_get_option( 'order_payment_method', array() );

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Orders by Payment Gateway', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-payment_gateway" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $payment_gateways ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Payment Gateway...', 'woocommerce-exporter' ); ?>" name="order_filter_payment_gateway[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $payment_gateways as $payment_gateway ) { ?>
                            <option value="<?php echo esc_attr( $payment_gateway->id ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $payment_gateway->id, $types, false ), true ) : '' ); ?><?php disabled( 0, woo_ce_get_order_payment_gateway_usage( $payment_gateway->id ) ); ?>><?php echo esc_html( ucfirst( woo_ce_format_order_payment_gateway( $payment_gateway->id ) ) ); ?> (<?php echo esc_html( woo_ce_get_order_payment_gateway_usage( $payment_gateway->id ) ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Payment Gateways were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Payment Gateways you want to filter exported Orders by. Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-orders-filters-payment_gateway -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Orders by Shipping Gateway widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Orders by Shipping Gateway widget on the Store Exporter screen.
 * It displays a checkbox to enable/disable the filter and a dropdown to select the shipping methods to filter the exported orders.
 */
function woo_ce_orders_filter_by_shipping_method() {

    $shipping_methods = woo_ce_get_order_shipping_methods();
    $types            = woo_ce_get_option( 'order_shipping_method', array() );

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Orders by Shipping Method', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-shipping_method" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $shipping_methods ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Shipping Method...', 'woocommerce-exporter' ); ?>" name="order_filter_shipping_method[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $shipping_methods as $shipping_method ) { ?>
                            <option value="<?php echo esc_attr( $shipping_method->id ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $shipping_method->id, $types, false ), true ) : '' ); ?>><?php echo esc_html( woo_ce_format_order_shipping_method( $shipping_method->id ) ); ?></option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Shipping Methods were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Shipping Methods you want to filter exported Orders by. Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-orders-filters-shipping_method -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Digital Products on Store Exporter screen.
 *
 * This function generates the HTML template for the filter options related to digital products on the Store Exporter screen.
 * It includes checkboxes and radio buttons for filtering orders based on digital products.
 */
function woo_ce_orders_filter_by_digital_products() {

    $types = woo_ce_get_option( 'order_digital_products', false );

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Orders by Digital Products', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-digital_products" class="separator">
        <ul>
            <li>
                <label><input type="radio" name="order_filter_digital_products" value="" <?php checked( $types, false ); ?> /> <?php esc_html_e( 'Export Orders containing both Digital and Physical Products', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="order_filter_digital_products" value="include_digital" <?php checked( $types, 'include_digital' ); ?> /> <?php esc_html_e( 'Export Orders containing only Digital Products', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="order_filter_digital_products" value="exclude_digital" <?php checked( $types, 'exclude_digital' ); ?> /> <?php esc_html_e( 'Exclude Orders containing any Digital Products', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="order_filter_digital_products" value="exclude_digital_only" <?php checked( $types, 'exclude_digital_only' ); ?> /> <?php esc_html_e( 'Exclude Orders containing only Digital Products', 'woocommerce-exporter' ); ?></label>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Digital Products you want to filter exported Orders by. Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-orders-filters-date -->

<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Orders by Product widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Orders by Product widget on the Store Exporter screen.
 * It allows users to filter exported orders by selecting specific products.
 *
 * @since 2.4.0
 */
function woo_ce_orders_filter_by_product() {

    if ( apply_filters( 'woo_ce_override_orders_filter_by_product', true ) == false ) {
        return;
    }

    /*
    // @mod - Removed as the meta_query args are returning empty results. Marked for re-inclusion after re-work in 2.4+.
    $product_types = woo_ce_get_product_types();
    // Remove the Product Variation type.
    unset( $product_types['variation'] );
    $args = array(
        'product_type' => array_keys( $product_types )
    );
*/
    $args = array();

    // Allow other developers to bake in their own filters.
    $args = apply_filters( 'woo_ce_orders_filter_by_product_args', $args );

    $products = woo_ce_get_products( $args );
    add_filter( 'the_title', 'woo_ce_get_product_title_sku', 10, 2 );

    $types       = woo_ce_get_option( 'order_product_exclude', false );
    $product_ids = woo_ce_get_option( 'order_product', array() );

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Orders by Product', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-product" class="separator">
        <ul>
            <li>
                <?php if ( wp_script_is( 'wc-enhanced-select', 'enqueued' ) ) { ?>
                    <?php
                    $output   = '';
                    $json_ids = array();
                    if ( ! empty( $product_ids ) ) {
                        foreach ( $product_ids as $product_id ) {
                            $product = wc_get_product( $product_id );
                            if ( is_object( $product ) ) {
                                $json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() );
                                $output                 .= '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
                            }
                        }
                    }
                    ?>
                    <p></p>
                    <?php if ( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) { ?>
                        <select
                            data-placeholder="<?php esc_attr_e( 'Search for a Product&hellip;', 'woocommerce' ); ?>"
                            id="order_filter_product"
                            name="order_filter_product[]"
                            multiple="multiple"
                            class="multiselect wc-product-search"
                            style="width:95%;"
                            data-action="woocommerce_json_search_products_and_variations"
                            data-selected="<?php echo esc_attr( json_encode( $json_ids ) ); ?>"><?php echo esc_html( $output ); ?></select>
                    <?php } else { ?>
                        <input
                            data-placeholder="<?php esc_attr_e( 'Search for a Product&hellip;', 'woocommerce-exporter' ); ?>"
                            type="hidden"
                            id="order_filter_product" name="order_filter_product[]"
                            class="multiselect wc-product-search"
                            data-multiple="true"
                            style="width:95;"
                            data-action="woocommerce_json_search_products_and_variations"
                            data-selected="<?php echo esc_attr( json_encode( $json_ids ) ); ?>"
                            value="<?php echo esc_attr( implode( ',', array_keys( $json_ids ) ) ); ?>" />
                    <?php } ?>
                    </p>
                <?php } else { ?>
                    <?php if ( ! empty( $products ) ) { ?>
                        <select data-placeholder="<?php esc_attr_e( 'Choose a Product...', 'woocommerce-exporter' ); ?>" name="order_filter_product[]" multiple class="chzn-select" style="width:95%;">
                            <?php foreach ( $products as $product ) { ?>
                                <option value="<?php echo esc_attr( $product ); ?>" <?php selected( ( ! empty( $product_ids ) ? in_array( $product, $product_ids ) : false ), true ); ?>><?php echo esc_html( woo_ce_format_post_title( get_the_title( $product ) ) ); ?></option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <?php esc_html_e( 'No Products were found.', 'woocommerce-exporter' ); ?>
                    <?php } ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Products you want to filter exported Orders by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
        <ul>
            <li><label><input type="radio" name="order_filter_product_exclude" value="1" <?php checked( $types, 1 ); ?> /> <?php esc_html_e( 'Filter out Order Items from Orders not matching these selected Products', 'woocommerce-exporter' ); ?></label></li>
            <li><label><input type="radio" name="order_filter_product_exclude" value="0" <?php checked( $types, 0 ); ?> /> <?php esc_html_e( 'Include all Order Items from Orders matching these selected Products', 'woocommerce-exporter' ); ?></label></li>
        </ul>
        <p class="description"><?php esc_html_e( 'Choose whether Order Items not matching the selected Products should be removed from the export. Default is to include all Order Items.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-orders-filters-product -->
<?php
    remove_filter( 'the_title', 'woo_ce_get_product_title_sku', 10, 2 );
    ob_end_flush();
}

/**
 * HTML template for Filter Orders by Product Category widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Orders by Product Category widget
 * on the Store Exporter screen. It displays a checkbox to enable/disable the filter and a
 * dropdown select field to choose the product categories for filtering.
 */
function woo_ce_orders_filter_by_product_category() {

    if ( apply_filters( 'woo_ce_override_orders_filter_by_product_category', true ) == false ) {
        return;
    }

    $args = array(
        'hide_empty' => 1,
    );

    // Allow other developers to bake in their own filters.
    $args = apply_filters( 'woo_ce_orders_filter_by_product_category_args', $args );

    $product_categories = woo_ce_get_product_categories( $args );
    $types              = woo_ce_get_option( 'order_category', array() );

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Orders by Product Category', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-category" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $product_categories ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Product Category...', 'woocommerce-exporter' ); ?>" name="order_filter_category[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $product_categories as $product_category ) { ?>
                            <?php // translators: %s: Product Category Name. ?>
                            <option value="<?php echo esc_attr( $product_category->term_id ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $product_category->term_id, $types, false ), true ) : '' ); ?>><?php echo esc_html( woo_ce_format_product_category_label( $product_category->name, $product_category->parent_name ) ); ?> (<?php echo esc_html( sprintf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_category->term_id ) ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Product Categories were found linked to Products.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Product Categories you want to filter exported Orders by. Product Categories not assigned to Products are hidden from view. Default is to include all Product Categories.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-orders-filters-category -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Orders by Product Tag widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Orders by Product Tag widget on the Store Exporter screen.
 * It displays a checkbox to enable/disable the filter and a dropdown to select the product tags for filtering.
 */
function woo_ce_orders_filter_by_product_tag() {

    if ( apply_filters( 'woo_ce_override_orders_filter_by_product_tag', true ) == false ) {
        return;
    }

    $args = array(
        'hide_empty' => 1,
    );

    // Allow other developers to bake in their own filters.
    $args = apply_filters( 'woo_ce_orders_filter_by_product_tag_args', $args );

    $product_tags = woo_ce_get_product_tags( $args );
    $types        = woo_ce_get_option( 'order_tag', array() );

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Orders by Product Tag', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-orders-filters-tag" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $product_tags ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Product Tag...', 'woocommerce-exporter' ); ?>" name="order_filter_tag[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $product_tags as $product_tag ) { ?>
                            <?php // translators: %s: Product Tag Name. ?>
                            <option value="<?php echo esc_attr( $product_tag->term_id ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $product_tag->term_id, $types, false ), true ) : '' ); ?>><?php echo esc_html( $product_tag->name ); ?> (<?php echo esc_html( sprintf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_tag->term_id ) ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Product Tags were found linked to Products.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Product Tags you want to filter exported Orders by. Product Tags not assigned to Products are hidden from view. Default is to include all Product Tags.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-orders-filters-tag -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Order Sorting widget on Store Exporter screen.
 */
function woo_ce_order_sorting() {

    $orderby = woo_ce_get_option( 'order_orderby', 'ID' );
    $order   = woo_ce_get_option( 'order_order', 'ASC' );

    ob_start();
    ?>
    <p><label><?php esc_html_e( 'Order Sorting', 'woocommerce-exporter' ); ?></label></p>
    <div>
        <select name="order_orderby">
            <option value="ID" <?php selected( 'ID', $orderby ); ?>><?php esc_html_e( 'Order ID', 'woocommerce-exporter' ); ?></option>
            <option value="title" <?php selected( 'title', $orderby ); ?>><?php esc_html_e( 'Order Name', 'woocommerce-exporter' ); ?></option>
            <option value="date" <?php selected( 'date', $orderby ); ?>><?php esc_html_e( 'Date Created', 'woocommerce-exporter' ); ?></option>
            <option value="modified" <?php selected( 'modified', $orderby ); ?>><?php esc_html_e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
            <option value="product_name" <?php selected( 'modified', $orderby ); ?>><?php esc_html_e( 'Product Name', 'woocommerce-exporter' ); ?></option>
            <?php do_action( 'woo_ce_order_sorting', $orderby ); ?>
            <option value="rand" <?php selected( 'rand', $orderby ); ?>><?php esc_html_e( 'Random', 'woocommerce-exporter' ); ?></option>
        </select>
        <select name="order_order">
            <option value="ASC" <?php selected( 'ASC', $order ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
            <option value="DESC" <?php selected( 'DESC', $order ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Select the sorting of Orders within the exported file. By default this is set to export Orders by Product ID in Desending order.', 'woocommerce-exporter' ); ?></p>
    </div>
<?php
    ob_end_flush();
}

/**
 * HTML template for jump link to Custom Order Fields within Order Options on Store Exporter screen.
 *
 * This function generates the HTML code for a jump link that allows users to navigate to the section for managing custom order fields on the Store Exporter screen.
 */
function woo_ce_orders_custom_fields_link() {

    ob_start();
    ?>
    <div id="export-orders-custom-fields-link">
        <p><a href="#export-orders-custom-fields"><?php esc_html_e( 'Manage Custom Order Fields', 'woocommerce-exporter' ); ?></a></p>
    </div>
    <!-- #export-orders-custom-fields-link -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Order Items Formatting on Store Exporter screen.
 *
 * This function generates the HTML template for displaying the options related to order items formatting on the Store Exporter screen.
 */
function woo_ce_orders_items_formatting() {

    $order_items_formatting = woo_ce_get_option( 'order_items_formatting', 'unique' );

    ob_start();
    ?>
    <tr class="export-options order-options">
        <th><label for="order_items"><?php esc_html_e( 'Order items formatting', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <ul>
                <li>
                    <label><input type="radio" name="order_items" value="combined" checked="checked" />&nbsp;<?php esc_html_e( 'Place Order Items within a grouped single Order row', 'woocommerce-exporter' ); ?></label>
                    <p class="description"><?php echo wp_kses_post( __( 'For example: <code>Order Items: SKU</code> cell might contain <code>SPECK-IPHONE|INCASE-NANO|-</code> for 3 Order items within an Order', 'woocommerce-exporter' ) ); ?></p>
                </li>
                <li>
                    <label>
                        <input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Place Order Items on individual cells within a single Order row', 'woocommerce-exporter' ); ?>
                        <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderoptionsformattingcombinedlink' ) ) ); ?></span>
                    </label>
                    <p class="description"><?php echo wp_kses_post( __( 'For example: <code>Order Items: SKU</code> would become <code>Order Item #1: SKU</code> with <codeSPECK-IPHONE</code> for the first Order item within an Order', 'woocommerce-exporter' ) ); ?></p>
                </li>
                <li>
                    <label>
                        <input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Place each Order Item within their own Order row', 'woocommerce-exporter' ); ?>
                        <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderoptionsformattingindividuallink' ) ) ); ?></span>
                    </label>
                    <p class="description"><?php esc_html_e( 'For example: An Order with 3 Order items will display a single Order item on each row', 'woocommerce-exporter' ); ?></p>
                </li>
            </ul>
            <p class="description"><?php esc_html_e( 'Choose how you would like Order Items to be presented within Orders.', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>
<?php
    ob_end_flush();
}

/**
 * HTML template for Max Order Items widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Max Order Items widget on the Store Exporter screen.
 * It displays a form field where the user can set the maximum number of unique order items to be displayed.
 */
function woo_ce_orders_max_order_items() {

    $max_order_items = woo_ce_get_option( 'max_order_items', 10 );
    // Default to 10 if empty.
    if ( empty( $max_order_items ) ) {
        $max_order_items = 10;
    }

    ob_start();
    ?>
    <tr id="max_order_items_option" class="export-options order-options">
        <th>
            <label for="max_order_items"><?php esc_html_e( 'Max unique Order items', 'woocommerce-exporter' ); ?>: </label>
        </th>
        <td>
            <input type="text" id="max_order_items" name="max_order_items" size="3" class="text" value="<?php echo esc_attr( $max_order_items ); ?>" />
            <p class="description"><?php esc_html_e( 'Manage the number of Order Item colums displayed when the \'Place Order Items on individual cells within a single Order row\' Order items formatting option is selected.', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>
<?php
    ob_end_flush();
}

/**
 * HTML template for Order Items Types on Store Exporter screen.
 *
 * This function generates the HTML template for displaying the order item types
 * on the Store Exporter screen. It retrieves the order item types from the
 * woo_ce_get_order_items_types() function and checks the selected order item types
 * from the woo_ce_get_option() function. If no order item types are selected, it
 * defaults to 'line_item' and checks if the WooCommerce Checkout Add-ons plugin is
 * activated to include 'fee' as well. The generated HTML includes checkboxes for each
 * order item type and a description of the selection.
 */
function woo_ce_orders_items_types() {

    $types             = woo_ce_get_order_items_types();
    $order_items_types = woo_ce_get_option( 'order_items_types', array() );

    // Default to Line Item if not set.
    if ( empty( $order_items_types ) ) {
        $order_items_types = array( 'line_item' );
        // Check if WooCommerce Checkout Add-ons is activated.
        if ( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
            $order_items_types = array( 'line_item', 'fee' );
        }
    }

    ob_start();
    ?>
    <tr class="export-options order-options">
        <th><label><?php esc_html_e( 'Order item types', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <ul>
                <?php foreach ( $types as $key => $type ) { ?>
                    <li>
                        <label>
                            <input type="checkbox" disabled="disabled" /> <?php echo esc_html( ucfirst( $type ) ); ?>
                            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderoptionsitemtypeslinks' ) ) ); ?></span>
                        </label>
                    </li>
                <?php } ?>
            </ul>
            <p class="description"><?php esc_html_e( 'Choose what Order Item types are included within the Orders export. Default is to include all Order Item types.', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>
<?php
    ob_end_flush();
}

/**
 * HTML template for Add note for exported Order flag widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Add note for exported Order flag widget on the Store Exporter screen.
 * It displays radio buttons to choose whether to add private Order notes or not.
 */
function woo_ce_orders_flag_notes() {

    $order_flag_notes = woo_ce_get_option( 'order_flag_notes', 0 );

    ob_start();
    ?>
    <tr class="export-options order-options">
        <th><label><?php esc_html_e( 'Exported Order notes', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <label><input type="radio" name="order_flag_notes" value="0" <?php checked( $order_flag_notes, 0 ); ?>>&nbsp;<?php esc_html_e( 'Do not add private Order notes', 'woocommerce-exporter' ); ?></label><br />
            <label><input type="radio" name="order_flag_notes" value="1" <?php checked( $order_flag_notes, 1 ); ?>>&nbsp;<?php esc_html_e( 'Add private Order notes', 'woocommerce-exporter' ); ?></label>
            <p class="description"><?php esc_html_e( 'Choose whether Order notes - e.g. Order was exported successfully or Order export flag was cleared - are assigned to exported Orders when using the Since last export Order Filter. Default is not to add Order notes.', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>
<?php
    ob_end_flush();
}

/**
 * HTML template for Custom Orders widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Custom Orders widget on the Store Exporter screen.
 * It displays a form with text areas for adding custom order fields, order item fields, and order item product fields.
 * The form also includes a submit button to save the custom fields.
 */
function woo_ce_orders_custom_fields() {

    $custom_orders = woo_ce_get_option( 'custom_orders', '' );
    if ( $custom_orders ) {
        $custom_orders = implode( "\n", $custom_orders );
    }
    $custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
    if ( $custom_order_items ) {
        $custom_order_items = implode( "\n", $custom_order_items );
    }
    $custom_order_products = woo_ce_get_option( 'custom_order_products', '' );
    if ( $custom_order_products ) {
        $custom_order_products = implode( "\n", $custom_order_products );
    }

    $troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

    ob_start();
    ?>
    <form method="post" id="export-orders-custom-fields" class="export-options order-options">
        <div id="poststuff">

            <div class="postbox" id="export-options">
                <h3 class="hndle"><?php esc_html_e( 'Custom Order Fields', 'woocommerce-exporter' ); ?></h3>
                <div class="inside">
                    <p class="description"><?php esc_html_e( 'To include additional custom Order, Order Item or Product meta associated to Order Items in the Export Orders table above fill the appropriate text box then click <em>Save Custom Fields</em>. The saved meta will appear as new export fields to be selected from the Order Fields list.', 'woocommerce-exporter' ); ?></p>
                    <?php // translators: %s: Troubleshooting URL. ?>
                    <p class="description"><?php echo wp_kses_post( sprintf( __( 'For more information on exporting custom Order and Order Item meta consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ) ); ?></p>
                    <table class="form-table">

                        <tr>
                            <th>
                                <label for="custom_orders"><?php esc_html_e( 'Order meta', 'woocommerce-exporter' ); ?></label>
                            </th>
                            <td>
                                <textarea disabled="disabled" rows="5" cols="70"><?php echo esc_textarea( $custom_orders ); ?></textarea>
                                <p class="description">
                                    <?php echo wp_kses_post( __( 'Include additional custom Order meta in your export file by adding each custom Order meta name to a new line above. This is case sensitive.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ) ); ?>
                                    <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfcustomordermetalink' ) ) ); ?></span>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label for="custom_order_items"><?php esc_html_e( 'Order Item meta', 'woocommerce-exporter' ); ?></label>
                            </th>
                            <td>
                                <textarea disabled="disabled" name="custom_order_items" rows="5" cols="70"><?php echo esc_textarea( $custom_order_items ); ?></textarea>
                                <p class="description">
                                    <?php echo wp_kses_post( __( 'Include additional custom Order Item meta in your export file by adding each custom Order Item meta name to a new line above. This is case sensitive.<br />For example: <code>Personalized Message</code> (new line) <code>_line_total</code>', 'woocommerce-exporter' ) ); ?>
                                    <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=ordercustommetalink' ) ) ); ?></span>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label for="custom_order_products"><?php esc_html_e( 'Order Item Product meta', 'woocommerce-exporter' ); ?></label>
                            </th>
                            <td>
                                <textarea disabled="disabled" name="custom_order_products" rows="5" cols="70"><?php echo esc_textarea( $custom_order_products ); ?></textarea>
                                <p class="description">
                                    <?php echo wp_kses_post( __( 'Include additional custom Order Item Product meta in your export file by adding each custom Product meta name associated to Order Items to a new line above. This is case sensitive.<br />For example: <code>_sold_individually</code> (new line) <code>_manage_stock</code>', 'woocommerce-exporter' ) ); ?>
                                    <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfcustomitemmetalink' ) ) ); ?></span>
                                </p>
                            </td>
                        </tr>

                        <?php do_action( 'woo_ce_orders_custom_fields' ); ?>

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
    <!-- #export-orders-custom-fields -->
<?php
    ob_end_flush();
}

/**
 * Renders the export options for orders in the scheduled exports section.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_filters_order( $post_ID = 0 ) {

    ob_start();
    ?>
    <div class="export-options order-options">

        <?php do_action( 'woo_ce_scheduled_export_filters_order', $post_ID ); ?>

    </div>
    <!-- .order-options -->

<?php
    ob_end_flush();
}

/**
 * HTML template for Order Sorting filter on Edit Scheduled Export screen.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_filter_orderby( $post_ID ) {

    $orderby = get_post_meta( $post_ID, '_filter_order_orderby', true );

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="order_filter_orderby"><?php esc_html_e( 'Order Sorting', 'woocommerce-exporter' ); ?></label>
            <select id="order_filter_orderby" name="order_filter_orderby">
                <option value="ID" <?php selected( 'ID', $orderby ); ?>><?php esc_html_e( 'Order ID', 'woocommerce-exporter' ); ?></option>
                <option value="title" <?php selected( 'title', $orderby ); ?>><?php esc_html_e( 'Order Name', 'woocommerce-exporter' ); ?></option>
                <option value="date" <?php selected( 'date', $orderby ); ?>><?php esc_html_e( 'Date Created', 'woocommerce-exporter' ); ?></option>
                <option value="modified" <?php selected( 'modified', $orderby ); ?>><?php esc_html_e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
                <?php do_action( 'woo_ce_order_sorting', $orderby ); ?>
                <option value="rand" <?php selected( 'rand', $orderby ); ?>><?php esc_html_e( 'Random', 'woocommerce-exporter' ); ?></option>
            </select>
        </p>
    </div>
    <!-- .options_group -->
<?php
    ob_end_flush();
}

/**
 * Filters the orders by product for scheduled export.
 *
 * @param int $post_ID The post ID.
 */
function woo_ce_scheduled_export_order_filter_by_product( $post_ID = 0 ) {

    $start_time = time();
    $debugging  = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_products(): ' . ( time() - $start_time ) ) );
    }
    $products = false;
    if ( apply_filters( 'woo_ce_override_orders_filter_by_product', true ) ) {
        $post_status = array( 'publish', 'pending', 'future', 'private' );
        $args        = array(
            'product_status' => $post_status,
        );
        $products    = woo_ce_get_products( $args );
    }
    $types   = get_post_meta( $post_ID, '_filter_order_product', true );
    $exclude = get_post_meta( $post_ID, '_filter_order_product_exclude', true );
    if ( empty( $exclude ) ) {
        $exclude = false;
    }

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $products: ' . ( time() - $start_time ) ) );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_filter_product"><?php esc_html_e( 'Product', 'woocommerce-exporter' ); ?></label>
        <?php if ( wp_script_is( 'wc-enhanced-select', 'enqueued' ) && apply_filters( 'woo_ce_override_orders_filter_by_product', true ) ) { ?>
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
                    id="order_filter_product"
                    name="order_filter_product[]"
                    multiple="multiple"
                    class="multiselect wc-product-search"
                    style="width:95%;"
                    data-action="woocommerce_json_search_products_and_variations"
                    data-selected="<?php echo esc_attr( json_encode( $json_ids ) ); ?>"><?php echo esc_html( $output ); ?></select>
            <?php } else { ?>
                <input
                    type="hidden"
                    id="order_filter_product"
                    name="order_filter_product[]"
                    class="multiselect wc-product-search"
                    data-multiple="true"
                    style="width:95%;"
                    data-placeholder="<?php esc_attr_e( 'Search for a Product&hellip;', 'woocommerce-exporter' ); ?>"
                    data-action="woocommerce_json_search_products_and_variations"
                    data-action="woocommerce_json_search_products_and_variations"
                    data-selected="<?php echo esc_attr( json_encode( $json_ids ) ); ?>"
                    value="<?php echo esc_attr( implode( ',', array_keys( $json_ids ) ) ); ?>" />
            <?php } ?>
        <?php } else { ?>
            <?php
            add_filter( 'the_title', 'woo_ce_get_product_title_sku', 10, 2 );
            ?>
            <?php if ( ! empty( $products ) ) { ?>
                <select id="order_filter_product" data-placeholder="<?php esc_attr_e( 'Choose a Product...', 'woocommerce-exporter' ); ?>" name="order_filter_product[]" multiple class="chzn-select" style="width:95%;">
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
        <img class="help_tip" data-tip="<?php esc_html_e( 'Select the Products you want to filter exported Orders by. Default is to include all Products.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
    </p>
    <p class="form-field discount_type_field">
        <label for="order_filter_product_exclude"><?php esc_html_e( '(continued)', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="order_filter_product_exclude" value="1" <?php checked( $exclude, 1 ); ?> />&nbsp;<?php esc_html_e( 'Filter out Order Items from Orders not matching these selected Products', 'woocommerce-exporter' ); ?><br />
        <input type="radio" id="order_filter_product_exclude" name="order_filter_product_exclude" value="0" <?php checked( $exclude, false ); ?> />&nbsp;<?php esc_html_e( 'Include all Order Items from Orders matching these selected Products', 'woocommerce-exporter' ); ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Filters the order export by product category.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_filter_by_product_category( $post_ID = 0 ) {

    $start_time = time();
    $debugging  = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_product_categories(): ' . ( time() - $start_time ) ) );
    }

    $args               = array(
        'hide_empty' => 1,
    );
    $product_categories = woo_ce_get_product_categories( $args );
    $types              = get_post_meta( $post_ID, '_filter_order_category', true );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $product_categories: ' . ( time() - $start_time ) ) );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_filter_category"><?php esc_html_e( 'Product category', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $product_categories ) ) { ?>
            <select id="order_filter_category" data-placeholder="<?php esc_attr_e( 'Choose a Product Category...', 'woocommerce-exporter' ); ?>" name="order_filter_category[]" multiple class="chzn-select" style="width:95%;">
                <?php foreach ( $product_categories as $product_category ) { ?>
                    <?php // translators: %s: Product Category Name. ?>
                    <option value="<?php echo esc_attr( $product_category->term_id ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $product_category->term_id, $types ) : false ), true ); ?><?php disabled( $product_category->count, 0 ); ?>><?php echo esc_html( woo_ce_format_product_category_label( $product_category->name, $product_category->parent_name ) ); ?> (<?php echo esc_html( sprintf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_category->term_id ) ); ?>)</option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_html_e( 'Select the Product Categories you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        <?php } else { ?>
            <?php esc_html_e( 'No Product Categories were found linked to Products.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Renders the product tag filter field for the scheduled export order.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_filter_by_product_tag( $post_ID = 0 ) {

    $start_time = time();
    $debugging  = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_product_tags(): ' . ( time() - $start_time ) ) );
    }

    $args         = array(
        'hide_empty' => 1,
    );
    $product_tags = woo_ce_get_product_tags( $args );
    $types        = get_post_meta( $post_ID, '_filter_order_tag', true );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $product_tags: ' . ( time() - $start_time ) ) );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_filter_tag"><?php esc_html_e( 'Product tag', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $product_tags ) ) { ?>
            <select data-placeholder="<?php esc_attr_e( 'Choose a Product Tag...', 'woocommerce-exporter' ); ?>" name="order_filter_tag[]" multiple class="chzn-select" style="width:95%;">
                <?php foreach ( $product_tags as $product_tag ) { ?>
                    <?php // translators: %s: Product Tag Name. ?>
                    <option value="<?php echo esc_attr( $product_tag->term_id ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $product_tag->term_id, $types ) : false ), true ); ?>><?php echo esc_html( $product_tag->name ); ?> (<?php echo esc_html( sprintf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_tag->term_id ) ); ?>)</option>
                <?php } ?>
            </select>
        <?php } else { ?>
            <?php esc_html_e( 'No Product Tags were found linked to Products.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
<?php
    ob_end_flush();
}

/**
 * Renders the order filter by customer section in the admin panel.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_filter_by_customer( $post_ID = 0 ) {

    $start_time = time();
    $debugging  = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_customers(): ' . ( time() - $start_time ) ) );
    }

    $user_count      = woo_ce_get_export_type_count( 'user' );
    $user_list_limit = apply_filters( 'woo_ce_order_filter_customer_list_limit', 100, $user_count );
    if ( $user_count < $user_list_limit ) {
        $customers = woo_ce_get_customers_list();
    }
    $types = get_post_meta( $post_ID, '_filter_order_customer', true );
    if ( ! is_array( $types ) ) {
        $types = array();
    }

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $customers: ' . ( time() - $start_time ) ) );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_filter_customer"><?php esc_html_e( 'Customer', 'woocommerce-exporter' ); ?></label>
        <?php if ( $user_count < $user_list_limit ) { ?>
            <select id="order_filter_customer" data-placeholder="<?php esc_attr_e( 'Choose a Customer...', 'woocommerce-exporter' ); ?>" name="order_filter_customer[]" multiple class="chzn-select" style="width:95%;">
                <option value=""><?php esc_html_e( 'Show all customers', 'woocommerce-exporter' ); ?></option>
                <?php if ( ! empty( $customers ) ) { ?>
                    <?php foreach ( $customers as $customer ) { ?>
                        <option value="<?php echo esc_attr( $customer->ID ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $customer->ID, $types ) : false ), true ); ?>><?php echo esc_html( sprintf( '%s (#%s - %s)', $customer->display_name, $customer->ID, $customer->user_email ) ); ?></option>
                    <?php } ?>
                <?php } ?>
            </select>
        <?php } else { ?>
            <input type="text" id="order_customer" name="order_filter_customer" value="<?php echo esc_attr( ( ! empty( $types ) ? implode( ',', $types ) : '' ) ); ?>" size="20" class="text" />
        <?php } ?>
    </p>
    <?php
    ob_end_flush();
}

/**
 * Renders the order status filter field for scheduled exports.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_filter_by_order_status( $post_ID = 0 ) {

    $start_time = time();
    $debugging  = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_order_statuses(): ' . ( time() - $start_time ) ) );
    }

    $order_statuses = woo_ce_get_order_statuses();
    $types          = get_post_meta( $post_ID, '_filter_order_status', true );
    if ( empty( $types ) ) {
        $types = array();
    }

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $order_statuses: ' . ( time() - $start_time ) ) );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_filter_status"><?php esc_html_e( 'Order status', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $order_statuses ) ) { ?>
            <select id="order_filter_status" data-placeholder="<?php esc_attr_e( 'Choose a Order Status...', 'woocommerce-exporter' ); ?>" name="order_filter_status[]" multiple class="chzn-select" style="width:95%;">
                <?php foreach ( $order_statuses as $order_status ) { ?>
                    <option value="<?php echo esc_attr( $order_status->slug ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $order_status->slug, $types ) : false ), true ); ?>><?php echo esc_html( ucfirst( $order_status->name ) ); ?> (<?php echo esc_html( $order_status->count ); ?>)</option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_html_e( 'Select the Order Status you want to filter exported Orders by. Default is to include all Order Status options.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        <?php } else { ?>
            <?php esc_html_e( 'No Order Status were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
    <?php
    ob_end_flush();
}

/**
 * Renders the form field for filtering orders by billing country in the scheduled export settings.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_filter_by_billing_country( $post_ID = 0 ) {

    $start_time = time();
    $debugging  = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before allowed_countries(): ' . ( time() - $start_time ) ) );
    }

    $countries = woo_ce_allowed_countries();
    $types     = get_post_meta( $post_ID, '_filter_order_billing_country', true );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $billing_countries: ' . ( time() - $start_time ) ) );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_filter_billing_country"><?php esc_html_e( 'Billing country', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $countries ) ) { ?>
            <select id="order_filter_billing_country" data-placeholder="<?php esc_attr_e( 'Choose a Billing Country...', 'woocommerce-exporter' ); ?>" name="order_filter_billing_country[]" multiple class="chzn-select" style="width:95%;">
                <option value=""><?php esc_html_e( 'Show all Countries', 'woocommerce-exporter' ); ?></option>
                <?php foreach ( $countries as $country_prefix => $country ) { ?>
                    <option value="<?php echo esc_attr( $country_prefix ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $country_prefix, $types ) : false ), true ); ?>><?php echo esc_html( sprintf( '%s (%s)', $country, $country_prefix ) ); ?></option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_html_e( 'Filter Orders by Billing Country to be included in the export. Default is to include all Countries.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        <?php } else { ?>
            <?php esc_html_e( 'No Countries were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
    <?php
    ob_end_flush();
}

/**
 * Renders the filter for selecting shipping countries in the order export settings.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_filter_by_shipping_country( $post_ID = 0 ) {

    $start_time = time();
    $debugging  = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before allowed_countries(): ' . ( time() - $start_time ) ) );
    }

    $countries = woo_ce_allowed_countries();
    $types     = get_post_meta( $post_ID, '_filter_order_shipping_country', true );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $shipping_countries: ' . ( time() - $start_time ) ) );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_filter_shipping_country"><?php esc_html_e( 'Shipping country', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $countries ) ) { ?>
            <select id="order_filter_shipping_country" data-placeholder="<?php esc_attr_e( 'Choose a Shipping Country...', 'woocommerce-exporter' ); ?>" name="order_filter_shipping_country[]" multiple class="chzn-select" style="width:95%;">
                <option value=""><?php esc_html_e( 'Show all Countries', 'woocommerce-exporter' ); ?></option>
                <?php foreach ( $countries as $country_prefix => $country ) { ?>
                    <option value="<?php echo esc_attr( $country_prefix ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $country_prefix, $types ) : false ), true ); ?>><?php echo esc_html( sprintf( '%s (%s)', $country, $country_prefix ) ); ?></option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_html_e( 'Filter Orders by Shipping Country to be included in the export. Default is to include all Countries.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        <?php } else { ?>
            <?php esc_html_e( 'No Countries were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
    <?php
    ob_end_flush();
}

/**
 * Displays the order date filter options in the admin panel.
 *
 * This function is responsible for displaying the order date filter options in the admin panel.
 * It retrieves the filter options and values from the post meta and generates the HTML markup for the filter options.
 * The generated HTML markup includes radio buttons, input fields, and select dropdowns for selecting different filter options.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_filter_by_order_date( $post_ID = 0 ) {

    $types                             = get_post_meta( $post_ID, '_filter_order_date', true );
    $order_filter_dates_from           = get_post_meta( $post_ID, '_filter_order_dates_from', true );
    $order_filter_dates_to             = get_post_meta( $post_ID, '_filter_order_dates_to', true );
    $order_filter_date_variable        = get_post_meta( $post_ID, '_filter_order_date_variable', true );
    $order_filter_date_variable_length = get_post_meta( $post_ID, '_filter_order_date_variable_length', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_dates_filter"><?php esc_html_e( 'Order date', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="order_dates_filter" value="" <?php checked( $types, false ); ?> />&nbsp;<?php esc_html_e( 'All', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_dates_filter" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> />&nbsp;<?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_dates_filter" value="today" <?php checked( $types, 'today' ); ?> />&nbsp;<?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_dates_filter" value="yesterday" <?php checked( $types, 'yesterday' ); ?> />&nbsp;<?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_dates_filter" value="current_week" <?php checked( $types, 'current_week' ); ?> />&nbsp;<?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_dates_filter" value="last_week" <?php checked( $types, 'last_week' ); ?> />&nbsp;<?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_dates_filter" value="current_month" <?php checked( $types, 'current_month' ); ?> />&nbsp;<?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_dates_filter" value="last_month" <?php checked( $types, 'last_month' ); ?> />&nbsp;<?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_dates_filter" value="current_year" <?php checked( $types, 'current_year' ); ?> />&nbsp;<?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_dates_filter" value="last_year" <?php checked( $types, 'last_year' ); ?> />&nbsp;<?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_dates_filter" value="variable" <?php checked( $types, 'variable' ); ?> />&nbsp;<?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?><br />
        <span style="float:left; margin-right:6px;"><?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?></span>
        <input type="text" name="order_dates_filter_variable" class="sized" size="4" value="<?php echo esc_attr( $order_filter_date_variable ); ?>" />
        <select name="order_dates_filter_variable_length">
            <option value="" <?php selected( $order_filter_date_variable_length, '' ); ?>>&nbsp;</option>
            <option value="second" <?php selected( $order_filter_date_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
            <option value="minute" <?php selected( $order_filter_date_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
            <option value="hour" <?php selected( $order_filter_date_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
            <option value="day" <?php selected( $order_filter_date_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
            <option value="week" <?php selected( $order_filter_date_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
            <option value="month" <?php selected( $order_filter_date_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
            <option value="year" <?php selected( $order_filter_date_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
        </select><br class="clear" />
        <input type="radio" name="order_dates_filter" value="manual" <?php checked( $types, 'manual' ); ?> />&nbsp;<?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?><br />
        <input type="text" name="order_dates_from" value="<?php echo esc_attr( $order_filter_dates_from ); ?>" size="10" maxlength="10" class="sized datepicker order_export" /> <span style="float:left; margin-right:6px;"><?php esc_html_e( 'to', 'woocommerce-exporter' ); ?></span> <input type="text" name="order_dates_to" value="<?php echo esc_attr( $order_filter_dates_to ); ?>" size="10" maxlength="10" class="sized datepicker order_export" /><br class="clear" />
        <input type="radio" name="order_dates_filter" value="last_export" <?php checked( $types, 'last_export' ); ?> />&nbsp;<?php esc_html_e( 'Since last export', 'woocommerce-exporter' ); ?>
        <img class="help_tip" data-tip="<?php esc_html_e( 'Export Orders which have not previously been included in an export. Decided by whether the <code>_woo_cd_exported</code> custom Post meta key has not been assigned to an Order.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
    </p>
    <?php
    ob_end_flush();
}

/**
 * Displays the order modified date filter options in the admin panel.
 *
 * This function is responsible for rendering the HTML markup for the order modified date filter options
 * in the WooCommerce Store Exporter Deluxe plugin's admin panel.
 *
 * @param int $post_ID The ID of the post being edited.
 */
function woo_ce_scheduled_export_order_filter_by_order_modified_date( $post_ID = 0 ) {

    $types                             = get_post_meta( $post_ID, '_filter_order_modified_date', true );
    $order_filter_dates_from           = get_post_meta( $post_ID, '_filter_order_modified_dates_from', true );
    $order_filter_dates_to             = get_post_meta( $post_ID, '_filter_order_modified_dates_to', true );
    $order_filter_date_variable        = get_post_meta( $post_ID, '_filter_order_modified_date_variable', true );
    $order_filter_date_variable_length = get_post_meta( $post_ID, '_filter_order_modified_date_variable_length', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_modified_dates_filter"><?php esc_html_e( 'Order Modified date', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="order_modified_dates_filter" value="" <?php checked( $types, false ); ?> />&nbsp;<?php esc_html_e( 'All', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_modified_dates_filter" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> />&nbsp;<?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_modified_dates_filter" value="today" <?php checked( $types, 'today' ); ?> />&nbsp;<?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_modified_dates_filter" value="yesterday" <?php checked( $types, 'yesterday' ); ?> />&nbsp;<?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_modified_dates_filter" value="current_week" <?php checked( $types, 'current_week' ); ?> />&nbsp;<?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_modified_dates_filter" value="last_week" <?php checked( $types, 'last_week' ); ?> />&nbsp;<?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_modified_dates_filter" value="current_month" <?php checked( $types, 'current_month' ); ?> />&nbsp;<?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_modified_dates_filter" value="last_month" <?php checked( $types, 'last_month' ); ?> />&nbsp;<?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_modified_dates_filter" value="current_year" <?php checked( $types, 'current_year' ); ?> />&nbsp;<?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_modified_dates_filter" value="last_year" <?php checked( $types, 'last_year' ); ?> />&nbsp;<?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_modified_dates_filter" value="variable" <?php checked( $types, 'variable' ); ?> />&nbsp;<?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?><br />
        <span style="float:left; margin-right:6px;"><?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?></span>
        <input type="text" name="order_modified_dates_filter_variable" class="sized" size="4" value="<?php echo esc_attr( $order_filter_date_variable ); ?>" />
        <select name="order_modified_dates_filter_variable_length">
            <option value="" <?php selected( $order_filter_date_variable_length, '' ); ?>>&nbsp;</option>
            <option value="second" <?php selected( $order_filter_date_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
            <option value="minute" <?php selected( $order_filter_date_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
            <option value="hour" <?php selected( $order_filter_date_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
            <option value="day" <?php selected( $order_filter_date_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
            <option value="week" <?php selected( $order_filter_date_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
            <option value="month" <?php selected( $order_filter_date_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
            <option value="year" <?php selected( $order_filter_date_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
        </select><br class="clear" />
        <input type="radio" name="order_modified_dates_filter" value="manual" <?php checked( $types, 'manual' ); ?> />&nbsp;<?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?><br />
        <input type="text" name="order_modified_dates_from" value="<?php echo esc_attr( $order_filter_dates_from ); ?>" size="10" maxlength="10" class="sized datepicker order_export" /> <span style="float:left; margin-right:6px;"><?php esc_html_e( 'to', 'woocommerce-exporter' ); ?></span> <input type="text" name="order_modified_dates_to" value="<?php echo esc_attr( $order_filter_dates_to ); ?>" size="10" maxlength="10" class="sized datepicker order_export" /><br class="clear" />
    </p>
    <?php
    ob_end_flush();
}

/**
 * Renders the user role filter field for the scheduled export order.
 *
 * This function is responsible for rendering the user role filter field in the admin area
 * for the scheduled export order. It retrieves the user roles, adds the "Guest" role to the list,
 * and displays a select dropdown with the available user roles. If no user roles are found,
 * it displays a message indicating that no user roles were found.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_filter_by_user_role( $post_ID = 0 ) {

    $start_time = time();
    $debugging  = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_user_roles(): ' . ( time() - $start_time ) ) );
    }

    $user_roles = woo_ce_get_user_roles();
    // Add Guest Role to the User Roles list.
    if ( ! empty( $user_roles ) ) {
        $user_roles['guest'] = array(
            'name'  => __( 'Guest', 'woocommerce-exporter' ),
            'count' => 1,
        );
    }
    $types = get_post_meta( $post_ID, '_filter_order_user_role', true );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $user_roles: ' . ( time() - $start_time ) ) );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_filter_user_role"><?php esc_html_e( 'User role', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $user_roles ) ) { ?>
            <select id="order_filter_user_role" data-placeholder="<?php esc_attr_e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="order_filter_user_role[]" multiple class="chzn-select" style="width:95%;">
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
 * Filters the order export by coupon code.
 *
 * @param int $post_ID The post ID.
 */
function woo_ce_scheduled_export_order_filter_by_coupon( $post_ID = 0 ) {

    $start_time = time();
    $debugging  = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_coupons(): ' . ( time() - $start_time ) ) );
    }

    $coupons = false;
    if ( apply_filters( 'woo_ce_override_orders_filter_by_coupon', true ) ) {

        $coupon_count      = woo_ce_get_export_type_count( 'coupon' );
        $coupon_list_limit = apply_filters( 'woo_ce_order_filter_coupon_list_limit', 500, $coupon_count );
        if ( $coupon_count < $coupon_list_limit ) {
            $args    = array(
                'coupon_orderby' => 'ID',
                'coupon_order'   => 'DESC',
            );
            $coupons = woo_ce_get_coupons( $args );
        }

        $types = get_post_meta( $post_ID, '_filter_order_coupon', true );
    }

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $coupon_codes: ' . ( time() - $start_time ) ) );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_filter_coupon"><?php esc_html_e( 'Coupon code', 'woocommerce-exporter' ); ?></label>
        <?php if ( $coupon_count < $coupon_list_limit ) { ?>
            <?php if ( ! empty( $coupons ) ) { ?>
                <select id="order_filter_coupon" data-placeholder="<?php esc_attr_e( 'Choose a Coupon...', 'woocommerce-exporter' ); ?>" name="order_filter_coupon[]" multiple class="chzn-select" style="width:95%;">
                    <?php foreach ( $coupons as $coupon ) { ?>
                        <option value="<?php echo esc_attr( $coupon ); ?>" <?php echo ( is_array( $types ) ? selected( in_array( $coupon, $types, false ), true ) : '' ); ?><?php disabled( 0, woo_ce_get_coupon_code_usage( get_the_title( $coupon ) ) ); ?>><?php echo esc_html( get_the_title( $coupon ) ); ?> (<?php echo esc_html( woo_ce_get_coupon_code_usage( get_the_title( $coupon ) ) ); ?>)</option>
                    <?php } ?>
                </select>
            <?php } else { ?>
                <?php esc_html_e( 'No Coupons were found.', 'woocommerce-exporter' ); ?>
            <?php } ?>
        <?php } else { ?>
            <input type="text" id="order_coupon" name="order_filter_coupon" size="20" class="text" value="<?php echo esc_attr( ( is_array( $types ) ? implode( ',', $types ) : $types ) ); ?>" />
        <?php } ?>
    </p>
    <?php
    ob_end_flush();
}

/**
 * Renders the payment gateway filter for scheduled order exports.
 *
 * This function is responsible for rendering the payment gateway filter in the admin area
 * for scheduled order exports. It retrieves the available payment gateways, checks if any
 * payment gateways are selected, and generates the HTML markup for the filter dropdown.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_filter_by_payment_gateway( $post_ID = 0 ) {

    $start_time = time();
    $debugging  = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_order_payment_gateways(): ' . ( time() - $start_time ) ) );
    }

    $payment_gateways = woo_ce_get_order_payment_gateways();
    $types            = get_post_meta( $post_ID, '_filter_order_payment', true );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $payment_gateways: ' . ( time() - $start_time ) ) );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_filter_payment"><?php esc_html_e( 'Payment gateway', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $payment_gateways ) ) { ?>
            <select id="order_filter_payment" data-placeholder="<?php esc_attr_e( 'Choose a Payment Gateway...', 'woocommerce-exporter' ); ?>" name="order_filter_payment[]" multiple class="chzn-select" style="width:95%;">
                <?php foreach ( $payment_gateways as $payment_gateway ) { ?>
                    <option value="<?php echo esc_attr( $payment_gateway->id ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $payment_gateway->id, $types ) : false ), true ); ?>><?php echo esc_html( ucfirst( woo_ce_format_order_payment_gateway( $payment_gateway->id ) ) ); ?></option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_html_e( 'Select the Payment Gateways you want to filter exported Orders by. Default is to include all Payment Gateways.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        <?php } else { ?>
            <?php esc_html_e( 'No Payment Gateways were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
    <?php
    ob_end_flush();
}

/**
 * Renders the shipping method filter field for the scheduled export order page.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_filter_by_shipping_method( $post_ID = 0 ) {

    $start_time = time();
    $debugging  = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_order_shipping_methods(): ' . ( time() - $start_time ) ) );
    }

    $shipping_methods = woo_ce_get_order_shipping_methods();
    $types            = get_post_meta( $post_ID, '_filter_order_shipping', true );

    if ( WOO_CE_LOGGING && $debugging ) {
        woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $shipping_methods: ' . ( time() - $start_time ) ) );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_filter_shipping"><?php esc_html_e( 'Shipping method', 'woocommerce-exporter' ); ?></label>
        <?php if ( ! empty( $shipping_methods ) ) { ?>
            <select id="order_filter_shipping" data-placeholder="<?php esc_attr_e( 'Choose a Shipping Method...', 'woocommerce-exporter' ); ?>" name="order_filter_shipping[]" multiple class="chzn-select" style="width:95%;">
                <?php foreach ( $shipping_methods as $shipping_method ) { ?>
                    <option value="<?php echo esc_attr( $shipping_method->id ); ?>" <?php selected( ( ! empty( $types ) ? in_array( $shipping_method->id, $types ) : false ), true ); ?>><?php echo esc_html( ucfirst( woo_ce_format_order_shipping_method( $shipping_method->id ) ) ); ?></option>
                <?php } ?>
            </select>
            <img class="help_tip" data-tip="<?php esc_html_e( 'Select the Shipping Methods you want to filter exported Orders by. Default is to include all Shipping Methods.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        <?php } else { ?>
            <?php esc_html_e( 'No Shipping Methods were found.', 'woocommerce-exporter' ); ?>
        <?php } ?>
    </p>
    <?php
    ob_end_flush();
}

/**
 * Function to display the order items formatting options in the admin panel.
 *
 * This function retrieves the order items formatting option from the post meta of the given post ID.
 * If the option is not set, it retrieves the default option from the plugin settings.
 * It then displays the radio buttons for selecting the order items formatting option.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_items_formatting( $post_ID = 0 ) {

    $types = get_post_meta( $post_ID, '_filter_order_items', true );
    // Default to Quick Export > Order items formatting.
    if ( empty( $types ) ) {
        $types = woo_ce_get_option( 'order_items_formatting', 'unique' );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_items_filter"><?php esc_html_e( 'Order items formatting', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="order_items_filter" value="combined" <?php checked( $types, 'combined' ); ?> />&nbsp;<?php esc_html_e( 'Place Order Items within a grouped single Order row', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_items_filter" value="unique" <?php checked( $types, 'unique' ); ?> />&nbsp;<?php esc_html_e( 'Place Order Items on individual cells within a single Order row', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_items_filter" value="individual" <?php checked( $types, 'individual' ); ?> />&nbsp;<?php esc_html_e( 'Place each Order Item within their own Order row', 'woocommerce-exporter' ); ?>
    </p>
    <?php
    ob_end_flush();
}

/**
 * Function to retrieve the maximum number of unique order items for a scheduled export.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_max_order_items( $post_ID = 0 ) {

    $types = get_post_meta( $post_ID, '_filter_order_max_order_items', true );
    // Default to Quick Export > Max unique Order items.
    if ( empty( $types ) ) {
        $types = woo_ce_get_option( 'max_order_items', 10 );
    }

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="max_order_items"><?php esc_html_e( 'Max unique Order items', 'woocommerce-exporter' ); ?></label>
        <input type="text" id="max_order_items" name="order_max_order_items" size="4" class="sized" value="<?php echo esc_attr( sanitize_text_field( $types ) ); ?>" style="margin-right:6px;" />
        <img class="help_tip" data-tip="<?php esc_html_e( 'Manage the number of Order Item colums displayed when the \'Place Order Items on individual cells within a single Order row\' Order items formatting option is selected.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
    </p>
    <?php
    ob_end_flush();
}

/**
 * Displays the form field for selecting whether to add private order notes during scheduled order export.
 *
 * This function is responsible for rendering the form field in the WooCommerce Store Exporter Deluxe plugin settings page.
 * The form field allows the user to choose whether to add private order notes to exported orders when using the "Since last export" order filter.
 *
 * @param int $post_ID The ID of the post being edited. Default is 0.
 */
function woo_ce_scheduled_export_order_export_order_notes( $post_ID = 0 ) {

    $types = absint( get_post_meta( $post_ID, '_filter_order_flag_notes', true ) );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_filter_flag_notes"><?php esc_html_e( 'Exported order notes', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="order_flag_notes" value="0" <?php checked( $types, 0 ); ?>>&nbsp;<?php esc_html_e( 'Do not add private Order notes', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_flag_notes" value="1" <?php checked( $types, 1 ); ?>>&nbsp;<?php esc_html_e( 'Add private Order notes', 'woocommerce-exporter' ); ?>
        <img class="help_tip" data-tip="<?php esc_html_e( 'Choose whether Order notes - e.g. Order was exported successfully or Order export flag was cleared - are assigned to exported Orders when using the Since last export Order Filter. Default is not to add Order notes.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
    </p>
    <?php
    ob_end_flush();
}

/**
 * Filters the order items by digital products for scheduled export.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_scheduled_export_order_filter_by_digital_products( $post_ID = 0 ) {

    $types = get_post_meta( $post_ID, '_filter_order_items_digital', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="order_dates_filter"><?php esc_html_e( 'Digital products', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="order_items_digital_filter" value="" <?php checked( $types, false ); ?> />&nbsp;<?php esc_html_e( 'Export Orders containing both Digital and Physical Products', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_items_digital_filter" value="include_digital" <?php checked( $types, 'include_digital' ); ?> />&nbsp;<?php esc_html_e( 'Export Orders containing only Digital Products', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_items_digital_filter" value="exclude_digital" <?php checked( $types, 'exclude_digital' ); ?> />&nbsp;<?php esc_html_e( 'Exclude Orders containing any Digital Products', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="order_items_digital_filter" value="exclude_digital_only" <?php checked( $types, 'exclude_digital_only' ); ?> />&nbsp;<?php esc_html_e( 'Exclude Orders containing only Digital Products', 'woocommerce-exporter' ); ?><br />
    </p>
    <?php
    ob_end_flush();
}

/**
 * HTML template for Order Item Types filter on Edit Scheduled Export screen.
 *
 * This function generates the HTML template for the Order Item Types filter on the Edit Scheduled Export screen.
 * It retrieves the order item types from the database and checks if the WooCommerce Checkout Add-ons plugin is activated.
 * Based on the plugin activation, it sets the default order item types accordingly.
 * It then generates the HTML checkboxes for each order item type and displays them on the screen.
 *
 * @param int $post_ID The ID of the post being edited.
 */
function woo_ce_scheduled_export_order_order_item_types( $post_ID ) {

    $types            = woo_ce_get_order_items_types();
    $order_item_types = get_post_meta( $post_ID, '_filter_order_item_types', true );
    // Default to line_item.
    if ( ! $order_item_types ) {
        $order_item_types = array( 'line_item' );
        // Check if WooCommerce Checkout Add-ons is activated.
        if ( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
            $order_item_types = array( 'line_item', 'fee' );
        }
    }

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="order_filter_order_items_types"><?php esc_html_e( 'Order item types', 'woocommerce-exporter' ); ?></label>
            <?php foreach ( $types as $key => $type ) { ?>
                <input type="checkbox" name="order_filter_order_items_types[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $order_item_types ), true ); ?> />&nbsp;<?php echo esc_html( ucfirst( $type ) ); ?><br />
            <?php } ?>
        </p>
    </div>
    <!-- .options_group -->
    <?php
    ob_end_flush();
}

/**
 * Export templates
 *
 * This function generates the export template fields for orders.
 *
 * @param int $post_ID The ID of the post.
 */
function woo_ce_export_template_fields_order( $post_ID = 0 ) {

    $export_type = 'order';

    $export_order_class = new VisserLabs\WSE\Classes\Export\Order();
    $fields             = $export_order_class->get_default_fields( $post_ID );

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
                    <label><?php esc_html_e( 'Order fields', 'woocommerce-exporter' ); ?></label>
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
                    <p><?php esc_html_e( 'No Order fields were found.', 'woocommerce-exporter' ); ?></p>
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
