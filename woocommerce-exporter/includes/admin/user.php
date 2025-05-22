<?php
/**
 * HTML template for Filter Users by User Role widget on Store Exporter screen.
 */
function woo_ce_users_filter_by_user_role() {

    $user_roles = woo_ce_get_user_roles();

    ob_start(); ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Users by User Role', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-users-filters-user_role" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $user_roles ) ) { ?>
                    <select data-placeholder="<?php esc_html_e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="user_filter_user_role[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $user_roles as $key => $user_role ) { ?>
                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( ucfirst( $user_role['name'] ) ); ?></option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the User Roles you want to filter exported Users by. Default is to include all User Role options.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-users-filters-user_role -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Users by Date Registered widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Users by Date Registered widget on the Store Exporter screen.
 * It displays a set of radio buttons and input fields to filter users based on their registration date.
 */
function woo_ce_users_filter_by_date_registered() {

    $tomorrow                   = date( 'l', strtotime( 'tomorrow', current_time( 'timestamp' ) ) );
    $today                      = date( 'l', current_time( 'timestamp' ) );
    $yesterday                  = date( 'l', strtotime( '-1 days', current_time( 'timestamp' ) ) );
    $current_month              = date( 'F', current_time( 'timestamp' ) );
    $last_month                 = date( 'F', mktime( 0, 0, 0, date( 'n', current_time( 'timestamp' ) ) - 1, 1, date( 'Y', current_time( 'timestamp' ) ) ) );
    $current_year               = date( 'Y', current_time( 'timestamp' ) );
    $last_year                  = date( 'Y', strtotime( '-1 year', current_time( 'timestamp' ) ) );
    $user_dates_variable        = woo_ce_get_option( 'user_dates_filter_variable', '' );
    $user_dates_variable_length = woo_ce_get_option( 'user_dates_filter_variable_length', '' );
    $date_format                = woo_ce_get_option( 'date_format', 'd/m/Y' );
    $user_dates_first_user      = woo_ce_get_user_first_date( $date_format );
    $user_dates_last_user       = date( $date_format );
    $types                      = woo_ce_get_option( 'user_dates_filter' );
    $user_dates_from            = woo_ce_get_option( 'user_dates_from' );
    $user_dates_to              = woo_ce_get_option( 'user_dates_to' );

    // Check if the User Date To/From have been saved.
    if (
        empty( $user_dates_from ) ||
        empty( $user_dates_to )
    ) {
        if ( empty( $user_dates_from ) ) {
            $user_dates_from = $user_dates_first_user;
        }
        if ( empty( $user_dates_to ) ) {
            $user_dates_to = $user_dates_last_user;
        }
    }

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Users by User Date Registered', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-users-filters-date_registered" class="separator">
        <ul>
            <li>
                <label><input type="radio" name="user_dates_filter" value="" <?php checked( $types, false ); ?> /> <?php esc_html_e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $user_dates_first_user ); ?> - <?php echo esc_html( $user_dates_last_user ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_dates_filter" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> /> <?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $tomorrow ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_dates_filter" value="today" <?php checked( $types, 'today' ); ?> /> <?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $today ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_dates_filter" value="yesterday" <?php checked( $types, 'yesterday' ); ?> /> <?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $yesterday ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_dates_filter" value="current_week" <?php checked( $types, 'current_week' ); ?> /> <?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="user_dates_filter" value="last_week" <?php checked( $types, 'last_week' ); ?> /> <?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="user_dates_filter" value="current_month" <?php checked( $types, 'current_month' ); ?> /> <?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_month ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_dates_filter" value="last_month" <?php checked( $types, 'last_month' ); ?> /> <?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_month ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_dates_filter" value="current_year" <?php checked( $types, 'current_year' ); ?> /> <?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_dates_filter" value="last_year" <?php checked( $types, 'last_year' ); ?> /> <?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_dates_filter" value="variable" <?php checked( $types, 'variable' ); ?> /> <?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?>
                    <input type="text" name="user_dates_filter_variable" class="text code" size="4" maxlength="4" value="<?php echo esc_attr( $user_dates_variable ); ?>" />
                    <select name="user_dates_filter_variable_length" style="vertical-align:top;">
                        <option value="" <?php selected( $user_dates_variable_length, '' ); ?>>&nbsp;</option>
                        <option value="second" <?php selected( $user_dates_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="minute" <?php selected( $user_dates_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="hour" <?php selected( $user_dates_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="day" <?php selected( $user_dates_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="week" <?php selected( $user_dates_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="month" <?php selected( $user_dates_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="year" <?php selected( $user_dates_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
                    </select>
                </div>
            </li>
            <li>
                <label><input type="radio" name="user_dates_filter" value="manual" /> <?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <input type="text" size="10" maxlength="10" id="user_dates_from" name="user_dates_from" value="<?php echo ( $types == 'manual' ? esc_attr( $user_dates_from ) : esc_attr( $user_dates_first_user ) ); ?>" class="text code datepicker user_export" /> to <input type="text" size="10" maxlength="10" id="user_dates_to" name="user_dates_to" value="<?php echo ( $types == 'manual' ? esc_attr( $user_dates_to ) : esc_attr( $user_dates_last_user ) ); ?>" class="text code datepicker user_export" />
                    <p class="description"><?php esc_html_e( 'Filter the dates of Users to be included in the export. Default is the date of the first User registered to today in the date format <code>DD/MM/YYYY</code>.', 'woocommerce-exporter' ); ?></p>
                </div>
            </li>
        </ul>
    </div>
    <!-- #export-users-filters-date_registered -->
<?php
    ob_end_flush();
}

/**
 * HTML template for Filter Orders by Users Date Last Updated widget on Store Exporter screen.
 *
 * This function generates the HTML template for the widget that allows filtering orders by the date they were last updated.
 * The template includes checkboxes and radio buttons for selecting different date options.
 */
function woo_ce_users_filter_by_date_last_updated() {

    $tomorrow                   = date( 'l', strtotime( 'tomorrow', current_time( 'timestamp' ) ) );
    $today                      = date( 'l', current_time( 'timestamp' ) );
    $yesterday                  = date( 'l', strtotime( '-1 days', current_time( 'timestamp' ) ) );
    $current_month              = date( 'F', current_time( 'timestamp' ) );
    $last_month                 = date( 'F', mktime( 0, 0, 0, date( 'n', current_time( 'timestamp' ) ) - 1, 1, date( 'Y', current_time( 'timestamp' ) ) ) );
    $current_year               = date( 'Y', current_time( 'timestamp' ) );
    $last_year                  = date( 'Y', strtotime( '-1 year', current_time( 'timestamp' ) ) );
    $user_dates_variable        = woo_ce_get_option( 'user_modified_dates_filter_variable', '' );
    $user_dates_variable_length = woo_ce_get_option( 'user_modified_dates_filter_variable_length', '' );
    $date_format                = woo_ce_get_option( 'date_format', 'd/m/Y' );
    $user_dates_first_user      = woo_ce_get_order_first_date( $date_format );
    $user_dates_last_user       = woo_ce_get_order_date_filter( 'today', 'from', $date_format );
    $types                      = woo_ce_get_option( 'user_modified_dates_filter' );
    $user_dates_from            = woo_ce_get_option( 'user_modified_dates_from' );
    $user_dates_to              = woo_ce_get_option( 'user_modified_dates_to' );
    // Check if the User Date To/From have been saved.
    if (
        empty( $user_dates_from ) ||
        empty( $user_dates_to )
    ) {
        if ( empty( $user_dates_from ) ) {
            $user_dates_from = $user_dates_first_user;
        }
        if ( empty( $user_dates_to ) ) {
            $user_dates_to = $user_dates_last_user;
        }
    }

    ob_start();
    ?>
    <p>
        <label>
            <input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Filter Users by Date Last Updated', 'woocommerce-exporter' ); ?>
            <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfilterslink' ) ) ); ?></span>
        </label>
    </p>
    <div id="export-users-filters-modified-date" class="separator">
        <ul>
            <li>
                <label><input type="radio" name="user_modified_dates_filter" value="" <?php checked( $types, false ); ?> /> <?php esc_html_e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $user_dates_first_user ); ?> - <?php echo esc_html( $user_dates_last_user ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_modified_dates_filter" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> /> <?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $tomorrow ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_modified_dates_filter" value="today" <?php checked( $types, 'today' ); ?> /> <?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $today ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_modified_dates_filter" value="yesterday" <?php checked( $types, 'yesterday' ); ?> /> <?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $yesterday ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_modified_dates_filter" value="current_week" <?php checked( $types, 'current_week' ); ?> /> <?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="user_modified_dates_filter" value="last_week" <?php checked( $types, 'last_week' ); ?> /> <?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?></label>
            </li>
            <li>
                <label><input type="radio" name="user_modified_dates_filter" value="current_month" <?php checked( $types, 'current_month' ); ?> /> <?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_month ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_modified_dates_filter" value="last_month" <?php checked( $types, 'last_month' ); ?> /> <?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_month ); ?>)</label>
            </li>
            <!--
        <li>
            <label><input type="radio" name="user_modified_dates_filter" value="last_quarter" /> <?php esc_html_e( 'Last quarter', 'woocommerce-exporter' ); ?> (Nov. - Jan.)</label>
        </li>
-->
            <li>
                <label><input type="radio" name="user_modified_dates_filter" value="current_year" <?php checked( $types, 'current_year' ); ?> /> <?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $current_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_modified_dates_filter" value="last_year" <?php checked( $types, 'last_year' ); ?> /> <?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?> (<?php echo esc_html( $last_year ); ?>)</label>
            </li>
            <li>
                <label><input type="radio" name="user_modified_dates_filter" value="variable" <?php checked( $types, 'variable' ); ?> /> <?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?>
                    <input type="text" name="user_modified_dates_filter_variable" class="text code" size="4" maxlength="4" value="<?php echo esc_attr( $user_dates_variable ); ?>" />
                    <select name="user_modified_dates_filter_variable_length" style="vertical-align:top;">
                        <option value="" <?php selected( $user_dates_variable_length, '' ); ?>>&nbsp;</option>
                        <option value="second" <?php selected( $user_dates_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="minute" <?php selected( $user_dates_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="hour" <?php selected( $user_dates_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="day" <?php selected( $user_dates_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="week" <?php selected( $user_dates_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="month" <?php selected( $user_dates_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
                        <option value="year" <?php selected( $user_dates_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
                    </select>
                </div>
            </li>
            <li>
                <label><input type="radio" name="user_modified_dates_filter" value="manual" <?php checked( $types, 'manual' ); ?> /> <?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
                <div style="margin-top:0.2em;">
                    <input type="text" size="10" maxlength="10" id="user_modified_dates_from" name="user_modified_dates_from" value="<?php echo ( $types == 'manual' ? esc_attr( $user_dates_from ) : esc_attr( $user_dates_first_user ) ); ?>" class="text code datepicker order_export" /> <?php esc_html_e( 'to', 'woocommerce-exporter' ); ?> <input type="text" size="10" maxlength="10" id="user_modified_dates_to" name="user_modified_dates_to" value="<?php echo ( $types == 'manual' ? esc_attr( $user_dates_to ) : esc_attr( $user_dates_last_user ) ); ?>" class="text code datepicker order_export" />
                    <p class="description"><?php esc_html_e( 'Filter the dates of Users to be included in the export. Default is the date of the first User to today.', 'woocommerce-exporter' ); ?></p>
                </div>
            </li>
        </ul>
    </div>
    <!-- #export-orders-filters-date -->
<?php
    ob_end_flush();
}

/**
 * HTML template for jump link to Store Exporter screen.
 *
 * This function generates an HTML template for a jump link to the Store Exporter screen.
 * It displays a link that allows users to manage custom user fields.
 */
function woo_ce_users_custom_fields_link() {

    ob_start();
    ?>
    <div id="export-users-custom-fields-link">
        <p><a href="#export-users-custom-fields"><?php esc_html_e( 'Manage Custom User Fields', 'woocommerce-exporter' ); ?></a></p>
    </div>
    <!-- #export-users-custom-fields-link -->
<?php
    ob_end_flush();
}

/**
 * HTML template for User Sorting widget on Store Exporter screen.
 *
 * This function generates the HTML template for the User Sorting widget on the Store Exporter screen.
 * It displays a dropdown menu for selecting the sorting options for exporting users.
 */
function woo_ce_user_sorting() {

    $orderby = woo_ce_get_option( 'user_orderby', 'ID' );
    $order   = woo_ce_get_option( 'user_order', 'ASC' );

    ob_start();
    ?>
    <p><label><?php esc_html_e( 'User Sorting', 'woocommerce-exporter' ); ?></label></p>
    <div>
        <select name="user_orderby">
            <option value="ID" <?php selected( 'ID', $orderby ); ?>><?php esc_html_e( 'User ID', 'woocommerce-exporter' ); ?></option>
            <option value="display_name" <?php selected( 'display_name', $orderby ); ?>><?php esc_html_e( 'Display Name', 'woocommerce-exporter' ); ?></option>
            <option value="user_name" <?php selected( 'user_name', $orderby ); ?>><?php esc_html_e( 'Name', 'woocommerce-exporter' ); ?></option>
            <option value="user_login" <?php selected( 'user_login', $orderby ); ?>><?php esc_html_e( 'Username', 'woocommerce-exporter' ); ?></option>
            <option value="nicename" <?php selected( 'nicename', $orderby ); ?>><?php esc_html_e( 'Nickname', 'woocommerce-exporter' ); ?></option>
            <option value="email" <?php selected( 'email', $orderby ); ?>><?php esc_html_e( 'E-mail', 'woocommerce-exporter' ); ?></option>
            <option value="url" <?php selected( 'url', $orderby ); ?>><?php esc_html_e( 'Website', 'woocommerce-exporter' ); ?></option>
            <option value="registered" <?php selected( 'registered', $orderby ); ?>><?php esc_html_e( 'Date Registered', 'woocommerce-exporter' ); ?></option>
            <option value="rand" <?php selected( 'rand', $orderby ); ?>><?php esc_html_e( 'Random', 'woocommerce-exporter' ); ?></option>
        </select>
        <select name="user_order">
            <option value="ASC" <?php selected( 'ASC', $order ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
            <option value="DESC" <?php selected( 'DESC', $order ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Select the sorting of Users within the exported file. By default this is set to export User by User ID in Desending order.', 'woocommerce-exporter' ); ?></p>
    </div>
<?php
    ob_end_flush();
}

/**
 * HTML template for Custom Users widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Custom Users widget on the Store Exporter screen.
 * It displays a form with options to include additional custom User meta in the Export Users table.
 * The saved meta will appear as new export fields to be selected from the User Fields list.
 *
 * @return void
 */
function woo_ce_users_custom_fields() {

    if ( $custom_users = woo_ce_get_option( 'custom_users', '' ) ) {
        $custom_users = implode( "\n", $custom_users );
    }

    $troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

    ob_start();
    ?>
    <form method="post" id="export-users-custom-fields" class="export-options user-options">
        <div id="poststuff">

            <div class="postbox" id="export-options user-options">
                <h3 class="hndle"><?php esc_html_e( 'Custom User Fields', 'woocommerce-exporter' ); ?></h3>
                <div class="inside">
                    <p class="description"><?php esc_html_e( 'To include additional custom User meta in the Export Users table above fill the Users text box then click Save Custom Fields. The saved meta will appear as new export fields to be selected from the User Fields list.', 'woocommerce-exporter' ); ?></p>
                    <p class="description"><?php wp_kses_post( sprintf( __( 'For more information on exporting custom User meta consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ) ); ?></p>
                    <table class="form-table">

                        <tr>
                            <th>
                                <label for="custom_users"><?php esc_html_e( 'User meta', 'woocommerce-exporter' ); ?></label>
                            </th>
                            <td>
                                <textarea disabled="disabled" rows="5" cols="70"><?php echo esc_textarea( $custom_users ); ?></textarea>
                                <p class="description">
                                    <?php echo wp_kses_post( __( 'Include additional custom User meta in your export file by adding each custom User meta name to a new line above.<br />For example: <code>Customer UA (new line) Customer IP Address</code>', 'woocommerce-exporter' ) ); ?>
                                    <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=usercustommetalink' ) ) ); ?></span>
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
    <!-- #export-users-custom-fields -->
<?php
    ob_end_flush();
}

/**
 * Renders the user filter options for the scheduled export.
 *
 * This function is responsible for rendering the user filter options in the scheduled export settings page.
 * It displays a dropdown select field to choose user roles for filtering exported users.
 *
 * @param int $post_ID The ID of the scheduled export post.
 * @return void
 */
function woo_ce_scheduled_export_filters_user( $post_ID = 0 ) {

    $user_roles       = woo_ce_get_user_roles();
    $user_filter_role = get_post_meta( $post_ID, '_filter_user_role', true );

    ob_start();
    ?>
    <div class="export-options user-options">

        <?php do_action( 'woo_ce_scheduled_export_filters_user', $post_ID ); ?>

        <div class="options_group">
            <p class="form-field discount_type_field">
                <label for="user_filter_role"><?php esc_html_e( 'User role', 'woocommerce-exporter' ); ?></label>

                <?php if ( ! empty( $user_roles ) ) { ?>
                    <select id="user_filter_role" data-placeholder="<?php esc_html_e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="user_filter_role[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $user_roles as $key => $user_role ) { ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( ( ! empty( $user_filter_role ) ? in_array( $key, $user_filter_role ) : false ), true ); ?>><?php echo esc_html( ucfirst( $user_role['name'] ) ); ?> (<?php echo esc_html( $user_role['count'] ); ?>)</option>
                        <?php } ?>
                    </select>
                    <img class="help_tip" data-tip="<?php esc_html_e( 'Select the User Roles you want to filter exported Users by. Default is to include all User Roles.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                <?php } else { ?>
                    <?php esc_html_e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </p>
        </div>
        <!-- .options_group -->

    </div>
    <!-- .user-options -->

<?php
    ob_end_flush();
}

/**
 * Renders the user filter by date registered section in the admin panel.
 *
 * @param int $post_ID The ID of the post.
 * @return void
 */
function woo_ce_scheduled_export_user_filter_by_date_registered( $post_ID = 0 ) {

    $types                            = get_post_meta( $post_ID, '_filter_user_date', true );
    $user_filter_dates_from           = get_post_meta( $post_ID, '_filter_user_dates_from', true );
    $user_filter_dates_to             = get_post_meta( $post_ID, '_filter_user_dates_to', true );
    $user_filter_date_variable        = get_post_meta( $post_ID, '_filter_user_date_variable', true );
    $user_filter_date_variable_length = get_post_meta( $post_ID, '_filter_user_date_variable_length', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="user_filter_date"><?php esc_html_e( 'Date registered', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="user_filter_dates" value="" <?php checked( $types, false ); ?> />&nbsp;<?php esc_html_e( 'All', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_filter_dates" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> />&nbsp;<?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_filter_dates" value="today" <?php checked( $types, 'today' ); ?> />&nbsp;<?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_filter_dates" value="yesterday" <?php checked( $types, 'yesterday' ); ?> />&nbsp;<?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_filter_dates" value="current_week" <?php checked( $types, 'current_week' ); ?> />&nbsp;<?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_filter_dates" value="last_week" <?php checked( $types, 'last_week' ); ?> />&nbsp;<?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_filter_dates" value="current_month" <?php checked( $types, 'current_month' ); ?> />&nbsp;<?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_filter_dates" value="last_month" <?php checked( $types, 'last_month' ); ?> />&nbsp;<?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_filter_dates" value="current_year" <?php checked( $types, 'current_year' ); ?> />&nbsp;<?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_filter_dates" value="last_year" <?php checked( $types, 'last_year' ); ?> />&nbsp;<?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_filter_dates" value="variable" <?php checked( $types, 'variable' ); ?> />&nbsp;<?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?><br />
        <span style="float:left; margin-right:6px;"><?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?></span>
        <input type="text" name="user_filter_dates_variable" class="sized" size="4" value="<?php echo esc_attr( $user_filter_date_variable ); ?>" />
        <select name="user_filter_dates_variable_length">
            <option value="" <?php selected( $user_filter_date_variable_length, '' ); ?>>&nbsp;</option>
            <option value="second" <?php selected( $user_filter_date_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
            <option value="minute" <?php selected( $user_filter_date_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
            <option value="hour" <?php selected( $user_filter_date_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
            <option value="day" <?php selected( $user_filter_date_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
            <option value="week" <?php selected( $user_filter_date_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
            <option value="month" <?php selected( $user_filter_date_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
            <option value="year" <?php selected( $user_filter_date_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
        </select><br class="clear" />
        <input type="radio" name="user_filter_dates" value="manual" <?php checked( $types, 'manual' ); ?> />&nbsp;<?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?><br />
        <input type="text" name="user_filter_dates_from" value="<?php echo esc_attr( $user_filter_dates_from ); ?>" size="10" maxlength="10" class="sized datepicker user_export" /> <span style="float:left; margin-right:6px;"><?php esc_html_e( 'to', 'woocommerce-exporter' ); ?></span> <input type="text" name="user_filter_dates_to" value="<?php echo esc_attr( $user_filter_dates_to ); ?>" size="10" maxlength="10" class="sized datepicker user_export" />
    </p>
<?php
    ob_end_flush();
}

/**
 * Displays the user filter by date last updated in the WooCommerce Store Exporter Deluxe plugin.
 *
 * This function is responsible for rendering the user filter by date last updated section in the plugin's admin area.
 * It retrieves the necessary data from the post meta and generates the HTML markup for the filter options.
 *
 * @param int $post_ID The ID of the post.
 * @return void
 */
function woo_ce_scheduled_export_user_filter_by_date_last_updated( $post_ID = 0 ) {

    $types                            = get_post_meta( $post_ID, '_filter_user_modified_date', true );
    $user_filter_dates_from           = get_post_meta( $post_ID, '_filter_user_modified_dates_from', true );
    $user_filter_dates_to             = get_post_meta( $post_ID, '_filter_user_modified_dates_to', true );
    $user_filter_date_variable        = get_post_meta( $post_ID, '_filter_user_modified_date_variable', true );
    $user_filter_date_variable_length = get_post_meta( $post_ID, '_filter_user_modified_date_variable_length', true );

    ob_start();
    ?>
    <p class="form-field discount_type_field">
        <label for="user_modified_dates_filter"><?php esc_html_e( 'Date Last Updated', 'woocommerce-exporter' ); ?></label>
        <input type="radio" name="user_modified_dates_filter" value="" <?php checked( $types, false ); ?> />&nbsp;<?php esc_html_e( 'All', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_modified_dates_filter" value="tomorrow" <?php checked( $types, 'tomorrow' ); ?> />&nbsp;<?php esc_html_e( 'Tomorrow', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_modified_dates_filter" value="today" <?php checked( $types, 'today' ); ?> />&nbsp;<?php esc_html_e( 'Today', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_modified_dates_filter" value="yesterday" <?php checked( $types, 'yesterday' ); ?> />&nbsp;<?php esc_html_e( 'Yesterday', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_modified_dates_filter" value="current_week" <?php checked( $types, 'current_week' ); ?> />&nbsp;<?php esc_html_e( 'Current week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_modified_dates_filter" value="last_week" <?php checked( $types, 'last_week' ); ?> />&nbsp;<?php esc_html_e( 'Last week', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_modified_dates_filter" value="current_month" <?php checked( $types, 'current_month' ); ?> />&nbsp;<?php esc_html_e( 'Current month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_modified_dates_filter" value="last_month" <?php checked( $types, 'last_month' ); ?> />&nbsp;<?php esc_html_e( 'Last month', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_modified_dates_filter" value="current_year" <?php checked( $types, 'current_year' ); ?> />&nbsp;<?php esc_html_e( 'Current year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_modified_dates_filter" value="last_year" <?php checked( $types, 'last_year' ); ?> />&nbsp;<?php esc_html_e( 'Last year', 'woocommerce-exporter' ); ?><br />
        <input type="radio" name="user_modified_dates_filter" value="variable" <?php checked( $types, 'variable' ); ?> />&nbsp;<?php esc_html_e( 'Variable date', 'woocommerce-exporter' ); ?><br />
        <span style="float:left; margin-right:6px;"><?php esc_html_e( 'Last', 'woocommerce-exporter' ); ?></span>
        <input type="text" name="user_modified_dates_filter_variable" class="sized" size="4" value="<?php echo esc_attr( $user_filter_date_variable ); ?>" />
        <select name="user_modified_dates_filter_variable_length">
            <option value="" <?php selected( $user_filter_date_variable_length, '' ); ?>>&nbsp;</option>
            <option value="second" <?php selected( $user_filter_date_variable_length, 'second' ); ?>><?php esc_html_e( 'second(s)', 'woocommerce-exporter' ); ?></option>
            <option value="minute" <?php selected( $user_filter_date_variable_length, 'minute' ); ?>><?php esc_html_e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
            <option value="hour" <?php selected( $user_filter_date_variable_length, 'hour' ); ?>><?php esc_html_e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
            <option value="day" <?php selected( $user_filter_date_variable_length, 'day' ); ?>><?php esc_html_e( 'day(s)', 'woocommerce-exporter' ); ?></option>
            <option value="week" <?php selected( $user_filter_date_variable_length, 'week' ); ?>><?php esc_html_e( 'week(s)', 'woocommerce-exporter' ); ?></option>
            <option value="month" <?php selected( $user_filter_date_variable_length, 'month' ); ?>><?php esc_html_e( 'month(s)', 'woocommerce-exporter' ); ?></option>
            <option value="year" <?php selected( $user_filter_date_variable_length, 'year' ); ?>><?php esc_html_e( 'year(s)', 'woocommerce-exporter' ); ?></option>
        </select><br class="clear" />
        <input type="radio" name="user_modified_dates_filter" value="manual" <?php checked( $types, 'manual' ); ?> />&nbsp;<?php esc_html_e( 'Fixed date', 'woocommerce-exporter' ); ?><br />
        <input type="text" name="user_modified_dates_from" value="<?php echo esc_attr( $user_filter_dates_from ); ?>" size="10" maxlength="10" class="sized datepicker order_export" /> <span style="float:left; margin-right:6px;"><?php esc_html_e( 'to', 'woocommerce-exporter' ); ?></span> <input type="text" name="user_modified_dates_to" value="<?php echo esc_attr( $user_filter_dates_to ); ?>" size="10" maxlength="10" class="sized datepicker order_export" /><br class="clear" />
    </p>
<?php
    ob_end_flush();
}

/**
 * HTML template for User Sorting filter on Edit Scheduled Export screen.
 *
 * @param int $post_ID The ID of the post.
 * @return void
 */
function woo_ce_scheduled_export_user_filter_orderby( $post_ID ) {

    $orderby = get_post_meta( $post_ID, '_filter_user_orderby', true );
    // Default to ID.
    if ( ! $orderby ) {
        $orderby = 'ID';
    }

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="user_filter_orderby"><?php esc_html_e( 'User Sorting', 'woocommerce-exporter' ); ?></label>
            <select id="user_filter_orderby" name="user_filter_orderby">
                <option value="ID" <?php selected( 'ID', $orderby ); ?>><?php esc_html_e( 'User ID', 'woocommerce-exporter' ); ?></option>
                <option value="display_name" <?php selected( 'display_name', $orderby ); ?>><?php esc_html_e( 'Display Name', 'woocommerce-exporter' ); ?></option>
                <option value="user_name" <?php selected( 'user_name', $orderby ); ?>><?php esc_html_e( 'Name', 'woocommerce-exporter' ); ?></option>
                <option value="user_login" <?php selected( 'user_login', $orderby ); ?>><?php esc_html_e( 'Username', 'woocommerce-exporter' ); ?></option>
                <option value="nicename" <?php selected( 'nicename', $orderby ); ?>><?php esc_html_e( 'Nickname', 'woocommerce-exporter' ); ?></option>
                <option value="email" <?php selected( 'email', $orderby ); ?>><?php esc_html_e( 'E-mail', 'woocommerce-exporter' ); ?></option>
                <option value="url" <?php selected( 'url', $orderby ); ?>><?php esc_html_e( 'Website', 'woocommerce-exporter' ); ?></option>
                <option value="registered" <?php selected( 'registered', $orderby ); ?>><?php esc_html_e( 'Date Registered', 'woocommerce-exporter' ); ?></option>
                <option value="rand" <?php selected( 'rand', $orderby ); ?>><?php esc_html_e( 'Random', 'woocommerce-exporter' ); ?></option>
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
 * This function generates the export template fields for the user export type.
 *
 * @param int $post_ID The ID of the post.
 * @return void
 */
function woo_ce_export_template_fields_user( $post_ID = 0 ) {

    $export_type = 'user';

    $fields = woo_ce_get_user_fields( 'full', $post_ID );

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
                    <label><?php esc_html_e( 'User fields', 'woocommerce-exporter' ); ?></label>
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
                    <p><?php esc_html_e( 'No User fields were found.', 'woocommerce-exporter' ); ?></p>
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
