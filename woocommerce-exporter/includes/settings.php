<?php
/**
 * Display the quicklinks settings.
 *
 * @since 2.7.3
 * @access public
 */
function woo_ce_export_settings_quicklinks() {

    ob_start(); ?>
    <li>| <a href="#xml-settings"><?php esc_html_e( 'XML Settings', 'woocommerce-exporter' ); ?></a> |</li>
    <li><a href="#rss-settings"><?php esc_html_e( 'RSS Settings', 'woocommerce-exporter' ); ?></a> |</li>
    <li><a href="#scheduled-exports"><?php esc_html_e( 'Scheduled Exports', 'woocommerce-exporter' ); ?></a> |</li>
    <li><a href="#cron-exports"><?php esc_html_e( 'CRON Exports', 'woocommerce-exporter' ); ?></a> |</li>
    <li><a href="#orders-screen"><?php esc_html_e( 'Orders Screen', 'woocommerce-exporter' ); ?></a> |</li>
    <li><a href="#export-triggers"><?php esc_html_e( 'Export Triggers', 'woocommerce-exporter' ); ?></a></li>
<?php
    ob_end_flush();
}

/**
 * Display the multisite settings.
 *
 * @since 2.7.3
 * @access public
 */
function woo_ce_export_settings_multisite() {

    if ( ! is_multisite() || ! is_super_admin() ) {
    return;
    }

    $sites = get_sites();

    ob_start();
?>
    <tr>
    <th>
        <label for="multisite"><?php esc_html_e( 'Multisite', 'woocommerce-exporter' ); ?></label>
    </th>
    <td>
        <?php if ( ! empty( $sites ) ) { ?>
        <?php foreach ( $sites as $site ) { ?>
            <p>
            <?php echo esc_html( $site['blog_id'] ); ?>: <?php echo esc_html( $site['domain'] ); ?>
            <?php if ( is_main_network( $site['blog_id'] ) ) { ?>
                (<?php esc_html_e( 'Network Admin', 'woocommerce-exporter' ); ?>)
            <?php } ?>
            </p>
        <?php } ?>
        <?php } else { ?>
        <p><?php esc_html_e( 'No sites were detected.', 'woocommerce-exporter' ); ?></p>
        <?php } ?>
        <p class="description"><?php esc_html_e( 'Choose whether Store Exporter Deluxe exports from the current site or specific sites in MultiSite networks.', 'woocommerce-exporter' ); ?></p>
    </td>
    </tr>
<?php
    ob_end_flush();
}

/**
 * Display the general settings.
 */
function woo_ce_export_settings_general() {

    $troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

    $export_filename = woo_ce_get_option( 'export_filename', '' );
    // Strip file extension from export filename.
    if (
    ( strpos( $export_filename, '.csv' ) !== false ) ||
    ( strpos( $export_filename, '.tsv' ) !== false ) ||
    ( strpos( $export_filename, '.xml' ) !== false ) ||
    ( strpos( $export_filename, '.xls' ) !== false ) ||
    ( strpos( $export_filename, '.xlsx' ) !== false )
    ) {
    $export_filename = str_replace( array( '.csv', '.tsv', '.xml', '.xls', '.xlsx' ), '', $export_filename );
    }
    // Default export filename.
    if ( false === $export_filename ) {
    $export_filename = '%store_name%-export_%dataset%-%date%-%time%-%random%';
    }
    $delete_file    = woo_ce_get_option( 'delete_file', 1 );
    $file_encodings = ( function_exists( 'mb_list_encodings' ) ? mb_list_encodings() : false );
    $encoding       = woo_ce_get_option( 'encoding', 'UTF-8' );
    $date_format    = woo_ce_get_option( 'date_format', 'd/m/Y' );
    // Reset the Date Format if corrupted.
    if ( '1' === $date_format || '' === $date_format || false === $date_format ) {
    $date_format = 'd/m/Y';
    }
    $escape_formatting = woo_ce_get_option( 'escape_formatting', 'all' );
    $excel_formulas    = woo_ce_get_option( 'excel_formulas', 0 );
    $timeout           = woo_ce_get_option( 'timeout', 0 );
    $header_formatting = woo_ce_get_option( 'header_formatting', 1 );
    $flush_cache       = woo_ce_get_option( 'flush_cache', 0 );
    $bom               = woo_ce_get_option( 'bom', 1 );

    ob_start();
    ?>
    <tr valign="top">
    <th scope="row"><label for="export_filename"><?php esc_html_e( 'Export filename', 'woocommerce-exporter' ); ?></label></th>
    <td>
        <input type="text" name="export_filename" id="export_filename" value="<?php echo esc_attr( $export_filename ); ?>" class="large-text code" />
        <p class="description"><?php esc_html_e( 'The filename of the exported export type. It is not neccesary to add the filename extension (e.g. .csv, .tsv, .xls, .xlsx, .xml, etc.) as this is added at export time. Tags can be used: ', 'woocommerce-exporter' ); ?> <code>%dataset%</code>, <code>%date%</code>, <code>%time%</code>, <code>%year%</code>, <code>%month%</code>, <code>%day%</code>, <code>%hour%</code>, <code>%minute%</code>, <code>%random%</code>, <code>%store_name%</code>.</p>
    </td>
    </tr>

    <tr>
    <th>
        <label for="delete_file"><?php esc_html_e( 'Enable archives', 'woocommerce-exporter' ); ?></label>
    </th>
    <td>
        <select id="delete_file" name="delete_file">
        <option value="0" <?php selected( $delete_file, 0 ); ?>><?php esc_html_e( 'Yes', 'woocommerce-exporter' ); ?></option>
        <option value="1" <?php selected( $delete_file, 1 ); ?>><?php esc_html_e( 'No', 'woocommerce-exporter' ); ?></option>
        </select>
        <?php if ( woo_ce_get_option( 'hide_archives_tab', 0 ) ) { ?>
        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'restore_archives_tab' ), 'woo_ce_restore_archives_tab' ) ); ?>"><?php esc_html_e( 'Restore Archives tab', 'woocommerce-exporter' ); ?></a>
        <?php } ?>
        <?php if ( ! $delete_file ) { ?>
        <p class="warning"><?php echo wp_kses_post( __( 'Warning: Saving sensitve export files (e.g. Customers, Orders, etc.) to the WordPress Media directory will make export files accessible without restriction if the WordPress Media directory is allowed to be indexed.', 'woocommerce-exporter' ) . ' (<a href="' . esc_url( $troubleshooting_url ) . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)' ); ?></p>
        <?php } ?>
        <p class="description"><?php esc_html_e( 'Save copies of exports to the WordPress Media for later downloading. By default this option is turned off.', 'woocommerce-exporter' ); ?></p>
    </td>
    </tr>

    <tr>
    <th>
        <label for="encoding"><?php esc_html_e( 'Character encoding', 'woocommerce-exporter' ); ?></label>
    </th>
    <td>
        <?php if ( $file_encodings ) { ?>
        <select id="encoding" name="encoding">
            <option value=""><?php esc_html_e( 'System default', 'woocommerce-exporter' ); ?></option>
            <?php foreach ( $file_encodings as $key => $chr ) { ?>
            <option value="<?php echo esc_attr( $chr ); ?>" <?php selected( $chr, $encoding ); ?>><?php echo esc_html( $chr ); ?></option>
            <?php } ?>
        </select>
        <?php } else { ?>
        <?php if ( version_compare( phpversion(), '5', '<' ) ) { ?>
            <p class="description"><?php esc_html_e( 'Character encoding options are unavailable in PHP 4, contact your hosting provider to update your site install to use PHP 5 or higher.', 'woocommerce-exporter' ); ?></p>
        <?php } else { ?>
            <p class="description"><?php esc_html_e( 'Character encoding options are unavailable as the required mb_list_encodings() function is missing, contact your hosting provider to have the mbstring extension installed.', 'woocommerce-exporter' ); ?></p>
        <?php } ?>
        <?php } ?>
    </td>
    </tr>

    <tr>
    <th><?php esc_html_e( 'Date format', 'woocommerce-exporter' ); ?></th>
    <td>
        <ul style="margin-top:0.2em;">
        <li><label title="F j, Y"><input type="radio" name="date_format" value="F j, Y" <?php checked( $date_format, 'F j, Y' ); ?>> <span><?php echo esc_html( gmdate( 'F j, Y' ) ); ?></span></label></li>
        <li><label title="Y/m/d"><input type="radio" name="date_format" value="Y/m/d" <?php checked( $date_format, 'Y/m/d' ); ?>> <span><?php echo esc_html( gmdate( 'Y/m/d' ) ); ?></span></label></li>
        <li><label title="m/d/Y"><input type="radio" name="date_format" value="m/d/Y" <?php checked( $date_format, 'm/d/Y' ); ?>> <span><?php echo esc_html( gmdate( 'm/d/Y' ) ); ?></span></label></li>
        <li><label title="d/m/Y"><input type="radio" name="date_format" value="d/m/Y" <?php checked( $date_format, 'd/m/Y' ); ?>> <span><?php echo esc_html( gmdate( 'd/m/Y' ) ); ?></span></label></li>
        <li><label><input type="radio" name="date_format" value="custom" <?php checked( in_array( $date_format, array( 'F j, Y', 'Y/m/d', 'm/d/Y', 'd/m/Y' ), true ), false ); ?> /> <?php esc_html_e( 'Custom', 'woocommerce-exporter' ); ?>: </label><input type="text" name="date_format_custom" value="<?php echo esc_attr( $date_format ); ?>" class="text" /></li>
        <li><a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank"><?php esc_html_e( 'Documentation on date and time formatting', 'woocommerce-exporter' ); ?></a>.</li>
        </ul>
        <p class="description"><?php esc_html_e( 'The date format option affects how date\'s are presented within your export file. Default is set to DD/MM/YYYY.', 'woocommerce-exporter' ); ?></p>
    </td>
    </tr>

    <tr>
    <th>
        <?php esc_html_e( 'Field escape formatting', 'woocommerce-exporter' ); ?>
    </th>
    <td>
        <ul style="margin-top:0.2em;">
        <li><label><input type="radio" name="escape_formatting" value="all" <?php checked( $escape_formatting, 'all' ); ?> />&nbsp;<?php esc_html_e( 'Escape all cells', 'woocommerce-exporter' ); ?> - <span class="description"><?php esc_html_e( 'This will write field data exactly as it is saved in the database, regardless of the field type/format.', 'woocommerce-exporter' ); ?></span></label></li>
        <li><label><input type="radio" name="escape_formatting" value="excel" <?php checked( $escape_formatting, 'excel' ); ?> />&nbsp;<?php esc_html_e( 'Escape cells as Excel would', 'woocommerce-exporter' ); ?> - <span class="description"><?php esc_html_e( 'This will change field data when writing to export file, just the same way Excel changes field data when you import CSV into Excel (Caution: this may mess up phone numbers and postcodes with leading zeroes).', 'woocommerce-exporter' ); ?></span></label></li>
        <li><label><input type="radio" name="escape_formatting" value="none" <?php checked( $escape_formatting, 'none' ); ?> />&nbsp;<?php esc_html_e( 'Do not escape any cells', 'woocommerce-exporter' ); ?> - <span class="description"><?php esc_html_e( 'This will not change any field data when writing to export file (Caution: this may mess up text fields containing the delimiter character).', 'woocommerce-exporter' ); ?></span></label></li>
        </ul>
        <p class="description"><?php esc_html_e( 'Choose the field escape format that suits your spreadsheet software (e.g. Excel).', 'woocommerce-exporter' ); ?></p>
    </td>
    </tr>

    <tr>
    <th>
        <label for="excel_formulas"><?php esc_html_e( 'Excel formulas', 'woocommerce-exporter' ); ?></label>
    </th>
    <td>
        <select id="excel_formulas" disabled>
        <option><?php esc_html_e( 'Yes', 'woocommerce-exporter' ); ?></option>
        <option><?php esc_html_e( 'No', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description">
					<?php
						esc_html_e( 'Choose whether Excel formulas are allowed in export files. By default Excel formulas are stripped from all export files.', 'woocommerce-exporter' );

						// translators: %s: URL.
            echo ' - ' . wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=settings&utm_campaign=lineendingformattinglink' ) ) );
        	?>
				</p>
    </td>
    </tr>

    <?php if ( ! ini_get( 'safe_mode' ) ) { ?>
    <tr>
        <th>
        <label for="timeout"><?php esc_html_e( 'Script timeout', 'woocommerce-exporter' ); ?></label>
        </th>
        <td>
        <select id="timeout" name="timeout">
            <?php // translators: %s: seconds. ?>
            <option value="600" <?php selected( $timeout, 600 ); ?>><?php printf( esc_html__( '%s minutes', 'woocommerce-exporter' ), 10 ); ?></option>
            <?php // translators: %s: minutes. ?>
            <option value="1800" <?php selected( $timeout, 1800 ); ?>><?php printf( esc_html__( '%s minutes', 'woocommerce-exporter' ), 30 ); ?></option
            <?php // translators: %s: hour. ?>>
            <option value="3600" <?php selected( $timeout, 3600 ); ?>><?php printf( esc_html__( '%s hour', 'woocommerce-exporter' ), 1 ); ?></option>
            <option value="0" <?php selected( $timeout, 0 ); ?>><?php esc_html_e( 'Unlimited', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Script timeout defines how long Store Exporter is \'allowed\' to process your export file, once the time limit is reached the export process halts.', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>

    <?php } ?>
    <tr>
    <th>
        <?php esc_html_e( 'Header formatting', 'woocommerce-exporter' ); ?>
    </th>
    <td>
        <ul style="margin-top:0.2em;">
        <li><label><input type="radio" name="header_formatting" value="1" <?php checked( $header_formatting, '1' ); ?> />&nbsp;<?php esc_html_e( 'Include export field column headers', 'woocommerce-exporter' ); ?></label></li>
        <li>
            <label>
            <input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Do not include export field column headers', 'woocommerce-exporter' ); ?>
            <span class="description">
                - 
                <?php
                // translators: %s: URL.
                echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=settings&utm_campaign=headerformattinglink' ) ) );
                ?>
            </span>
            </label>
        </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Choose the header format that suits your spreadsheet software (e.g. Excel, OpenOffice, etc.). This rule applies to CSV, TSV, XLS and XLSX export types.', 'woocommerce-exporter' ); ?></p>
    </td>
    </tr>

    <tr>
    <th>
        <?php esc_html_e( 'WordPress Object Cache', 'woocommerce-exporter' ); ?>
    </th>
    <td>
        <ul style="margin-top:0.2em;">
        <li><label><input type="radio" name="flush_cache" value="1" <?php checked( $flush_cache, '1' ); ?> />&nbsp;<?php esc_html_e( 'Flush the WordPress Object Cache', 'woocommerce-exporter' ); ?></label></li>
        <li><label><input type="radio" name="flush_cache" value="0" <?php checked( $flush_cache, '0' ); ?> />&nbsp;<?php esc_html_e( 'Do not flush the WordPress Object Cache', 'woocommerce-exporter' ); ?></label></li>
        </ul>
        <p class="description"><?php esc_html_e( 'Choose if the WordPress Object Cache should be flushed before each export is run; recommended if caching Plugins for WordPress are in use (i.e. Redis Object Cache).', 'woocommerce-exporter' ); ?></p>
    </td>
    </tr>

    <tr>
    <th>
        <label for="bom"><?php esc_html_e( 'Add BOM character', 'woocommerce-exporter' ); ?></label>
    </th>
    <td>
        <select id="bom" name="bom">
        <option value="1" <?php selected( $bom, 1 ); ?>><?php esc_html_e( 'Yes', 'woocommerce-exporter' ); ?></option>
        <option value="0" <?php selected( $bom, 0 ); ?>><?php esc_html_e( 'No', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Mark the CSV file as UTF8 by adding a byte order mark (BOM) to the export, useful for non-English character sets.', 'woocommerce-exporter' ); ?></p>
    </td>
    </tr>
    <?php
    ob_end_flush();
}

/**
 * Extends the advanced settings in the general export settings.
 *
 * This function checks if the WooCommerce TM Extra Product Options plugin is active and adds a link to rebuild the TM Extra Product Options fields.
 *
 * @return void
 */
function woo_ce_export_settings_general_advanced_settings_extend() {

    // WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619.
    if ( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
    ?>
    <li><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'rebuild_tm_epo_fields' ), 'woo_ce_rebuild_tm_epo_fields' ) ); ?>"><?php esc_html_e( 'Rebuild WooCommerce TM Extra Product Options fields', 'woocommerce-exporter' ); ?></a></li>
    <?php
    }
}

/**
 * Renders the export settings form for CSV file format.
 *
 * This function outputs the HTML markup for the export settings form
 * used to configure the CSV file format. It includes fields for setting
 * the field delimiter, category separator, and line ending formatting.
 */
function woo_ce_export_settings_csv() {

    $delimiter              = woo_ce_get_option( 'delimiter', ',' );
    $category_separator     = woo_ce_get_option( 'category_separator', '|' );
    $line_ending_formatting = woo_ce_get_option( 'line_ending_formatting', 'windows' );

    ob_start();
    ?>
    <tr>
    <th>
        <label for="delimiter"><?php esc_html_e( 'Field delimiter', 'woocommerce-exporter' ); ?></label>
    </th>
    <td>
        <input type="text" size="3" id="delimiter" name="delimiter" value="<?php echo esc_attr( $delimiter ); ?>" maxlength="5" class="text" />
        <p class="description"><?php esc_html_e( 'The field delimiter is the character separating each cell in your CSV. This is typically the \',\' (comma) character. To use the TAB character as the delimiter enter <code>TAB</code>.', 'woocommerce-exporter' ); ?></p>
    </td>
    </tr>

    <tr>
    <th>
        <label for="category_separator"><?php esc_html_e( 'Category separator', 'woocommerce-exporter' ); ?></label>
    </th>
    <td>
        <input type="text" size="3" id="category_separator" name="category_separator" value="<?php echo esc_attr( $category_separator ); ?>" maxlength="5" class="text" />
        <p class="description"><?php echo wp_kses_post( __( 'The Product Category separator allows you to assign individual Products to multiple Product Categories/Tags/Images at a time. It is suggested to use the \'|\' (vertical pipe) character or \'LF\' for line breaks between each item. For instance: <code>Clothing|Mens|Shirts</code>.', 'woocommerce-exporter' ) ); ?></p>
    </td>
    </tr>

    <tr>
    <th>
        <label for="line_ending"><?php esc_html_e( 'Line ending formatting', 'woocommerce-exporter' ); ?></label>
    </th>
    <td>
        <select id="line_ending" name="line_ending">
        <option value="windows" <?php selected( $line_ending_formatting, 'windows' ); ?>><?php esc_html_e( 'Windows / DOS (CRLF)', 'woocommerce-exporter' ); ?></option>
        <option value="unix" disabled="disabled"><?php esc_html_e( 'Unix (LF)', 'woocommerce-exporter' ); ?></option>
        <option value="mac" disabled="disabled"><?php esc_html_e( 'Mac (CR)', 'woocommerce-exporter' ); ?></option>
        </select>
        <span class="description">
        -
        <?php
            // translators: %s: URL.
            echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=settings&utm_campaign=lineendingformattinglink' ) ) );
        ?>
        </span>
        <p class="description"><?php esc_html_e( 'Choose the line ending formatting that suits the Operating System you plan to use the export file with (e.g. a Windows desktop, Mac laptop, etc.). Default is Windows.', 'woocommerce-exporter' ); ?></p>
    </td>
    </tr>

    <?php
    ob_end_flush();
}

/**
 * Returns the HTML template for the CRON, scheduled exports, Secret Export Key and Export Trigger options for the Settings screen.
 **/
function woo_ce_export_settings_extend() {

    // XML settings.
    $xml_attribute_url     = woo_ce_get_option( 'xml_attribute_url', 1 );
    $xml_attribute_title   = woo_ce_get_option( 'xml_attribute_title', 1 );
    $xml_attribute_date    = woo_ce_get_option( 'xml_attribute_date', 1 );
    $xml_attribute_time    = woo_ce_get_option( 'xml_attribute_time', 0 );
    $xml_attribute_export  = woo_ce_get_option( 'xml_attribute_export', 1 );
    $xml_attribute_orderby = woo_ce_get_option( 'xml_attribute_orderby', 0 );
    $xml_attribute_order   = woo_ce_get_option( 'xml_attribute_order', 0 );
    $xml_attribute_limit   = woo_ce_get_option( 'xml_attribute_limit', 0 );
    $xml_attribute_offset  = woo_ce_get_option( 'xml_attribute_offset', 0 );

    // RSS settings.
    $rss_title       = woo_ce_get_option( 'rss_title', '' );
    $rss_link        = woo_ce_get_option( 'rss_link', '' );
    $rss_description = woo_ce_get_option( 'rss_description', '' );

    // Scheduled exports.
    $enable_auto = woo_ce_get_option( 'enable_auto', 0 );

    // Export templates.
    $args             = array(
		'post_status' => 'publish',
    );
    $export_templates = woo_ce_get_export_templates( $args );

    // CRON exports.
    $enable_cron          = woo_ce_get_option( 'enable_cron', 0 );
    $secret_key           = woo_ce_get_option( 'secret_key', '' );
    $cron_fields          = woo_ce_get_option( 'cron_fields', 'all' );
    $cron_export_template = woo_ce_get_option( 'cron_export_template', 'all' );

    // Orders Screen.
    $order_actions_csv                    = woo_ce_get_option( 'order_actions_csv', 1 );
    $order_actions_tsv                    = woo_ce_get_option( 'order_actions_tsv', 1 );
    $order_actions_xls                    = woo_ce_get_option( 'order_actions_xls', 1 );
    $order_actions_xlsx                   = woo_ce_get_option( 'order_actions_xlsx', 1 );
    $order_actions_xml                    = woo_ce_get_option( 'order_actions_xml', 0 );
    $order_actions_fields                 = woo_ce_get_option( 'order_actions_fields', 'all' );
    $args                                 = array(
		'post_status' => 'publish',
    );
    $export_templates                     = woo_ce_get_export_templates( $args );
    $order_actions_order_items_formatting = woo_ce_get_option( 'order_actions_order_items_formatting', 'unique' );
    $order_actions_export_template        = woo_ce_get_option( 'order_actions_export_template', 'all' );

    // Export Triggers.
    $enable_trigger_new_order                  = woo_ce_get_option( 'enable_trigger_new_order', 0 );
    $order_statuses                            = ( function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : false );
    $trigger_new_order_status                  = woo_ce_get_option( 'trigger_new_order_status', 'processing' );
    $export_formats                            = woo_ce_get_export_formats();
    $trigger_new_order_format                  = woo_ce_get_option( 'trigger_new_order_format', 'csv' );
    $trigger_new_order_method                  = woo_ce_get_option( 'trigger_new_order_method', 'archive' );
    $trigger_new_order_method_save_file_path   = woo_ce_get_option( 'trigger_new_order_method_save_file_path', '' );
    $trigger_new_order_method_save_filename    = woo_ce_get_option( 'trigger_new_order_method_save_filename', '' );
    $trigger_new_order_method_email_to         = woo_ce_get_option( 'trigger_new_order_method_email_to', '' );
    $trigger_new_order_method_email_subject    = woo_ce_get_option( 'trigger_new_order_method_email_subject', '' );
    $trigger_new_order_method_email_contents   = woo_ce_get_option( 'trigger_new_order_method_email_contents', '' );
    $trigger_new_order_method_post_to          = woo_ce_get_option( 'trigger_new_order_method_post_to', '' );
    $args                                      = array(
		'post_status' => 'publish',
    );
    $scheduled_exports                         = woo_ce_get_scheduled_exports( $args );
    $trigger_new_order_method_scheduled_export = woo_ce_get_option( 'trigger_new_order_method_scheduled_export', '' );
    // Fallback to the legacy FTP Scheduled Export value.
    if ( empty( $trigger_new_order_method_scheduled_export ) ) {
    $trigger_new_order_method_scheduled_export = woo_ce_get_option( 'trigger_new_order_method_ftp_scheduled_export', '' );
    }
    $trigger_new_order_items_formatting = woo_ce_get_option( 'trigger_new_order_items_formatting', 'unique' );
    $trigger_new_order_fields           = woo_ce_get_option( 'trigger_new_order_fields', 'all' );

    $troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

    ob_start();
    ?>
    <tr id="xml-settings">
    <td colspan="2" style="padding:0;">
        <hr />
        <h3>
        <div class="dashicons dashicons-media-code"></div>&nbsp;<?php esc_html_e( 'XML Settings', 'woocommerce-exporter' ); ?>
        </h3>
    </td>
    </tr>
    <tr>
    <td colspan="2">
        <div class="wse-upsell-content-overlayed">
        <table class="form-table">
            <tr>
            <th>
                <?php esc_html_e( 'Attribute display', 'woocommerce-exporter' ); ?>
            </th>
            <td>
                <ul>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Site Address', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Site Title', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Export Date', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Export Time', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Export Type', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Export Order By', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Export Order', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Limit Volume', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Volume Offset', 'woocommerce-exporter' ); ?></label></li>
                </ul>
                <p class="description">
                <?php esc_html_e( 'Control the visibility of different attributes in the XML export.', 'woocommerce-exporter' ); ?>
                </p>
            </td>
            </tr>
        </table>
        <?php
            do_action(
                'wse_show_upsell_overlay',
                'xmlsettings',
                __( 'XML Settings available in Store Exporter Deluxe', 'woocommerce-exporter' ),
                __( 'Upgrade to Store Exporter Deluxe for custom XML attributes visibility settings and premium support.', 'woocommerce-exporter' )
            );
        ?>
        </div>
    </td>
    </tr>
    <!-- #xml-settings -->

    <tr id="rss-settings">
    <td colspan="2" style="padding:0;">
        <hr />
        <h3>
        <div class="dashicons dashicons-media-code"></div>&nbsp;<?php esc_html_e( 'RSS Settings', 'woocommerce-exporter' ); ?>
        </h3>
    </td>
    </tr>
    <tr>
    <td colspan="2">
        <div class="wse-upsell-content-overlayed">
        <table class="form-table">
            <tr>
            <th>
                <label for="rss_title"><?php esc_html_e( 'Title element', 'woocommerce-exporter' ); ?></label>
            </th>
            <td>
                <input type="text" disabled="disabled" class="large-text" />
                <p class="description"><?php esc_html_e( 'Defines the title of the data feed (e.g. Product export for WordPress Shop).', 'woocommerce-exporter' ); ?></p>
            </td>
            </tr>
            <tr>
            <th>
                <label for="rss_link"><?php esc_html_e( 'Link element', 'woocommerce-exporter' ); ?></label>
            </th>
            <td>
                <input type="text" disabled="disabled" class="large-text" />
                <p class="description"><?php esc_html_e( 'A link to your website, this doesn\'t have to be the location of the RSS feed.', 'woocommerce-exporter' ); ?></p>
            </td>
            </tr>
            <tr>
            <th>
                <label for="rss_description"><?php esc_html_e( 'Description element', 'woocommerce-exporter' ); ?></label>
            </th>
            <td>
                <input type="text" disabled="disabled" class="large-text" />
                <p class="description"><?php esc_html_e( 'A description of your data feed.', 'woocommerce-exporter' ); ?></p>
            </td>
            </tr>
        </table>
        <?php
            do_action(
                'wse_show_upsell_overlay',
                'rsssettings',
                __( 'RSS Settings available in Store Exporter Deluxe', 'woocommerce-exporter' ),
                __( 'Upgrade to Store Exporter Deluxe for custom RSS attributes settings and premium support.', 'woocommerce-exporter' )
            );
        ?>
        </div>
    </td>
    </tr>
    <!-- #rss-settings -->

    <tr id="scheduled-exports">
    <td colspan="2" style="padding:0;">
        <hr />
        <h3>
        <div class="dashicons dashicons-calendar"></div>&nbsp;<?php esc_html_e( 'Scheduled Exports', 'woocommerce-exporter' ); ?>
        </h3>
        <p class="description"><?php echo wp_kses_post( __( 'Automatically generate exports and apply filters to export just what you need.<br />Adjusting options within the Scheduling sub-section will after clicking Save Changes refresh the scheduled export engine, editing filters, formats, methods, etc. will not affect the scheduling of the current scheduled export.', 'woocommerce-exporter' ) ); ?></p>
    </td>
    </tr>
    <tr>
    <td colspan="2">
        <div class="wse-upsell-content-overlayed">
        <table class="form-table">
            <tr>
            <th><label for="enable_auto"><?php esc_html_e( 'Enable scheduled exports', 'woocommerce-exporter' ); ?></label></th>
            <td>
                <select id="enable_auto" disabled="disabled">
                <option><?php esc_html_e( 'Yes', 'woocommerce-exporter' ); ?></option>
                <option selected="selected"><?php esc_html_e( 'No', 'woocommerce-exporter' ); ?></option>
                </select>
                <p class="description"><?php esc_html_e( 'Enabling Scheduled Exports will trigger automated exports at the intervals specified under Scheduling within each scheduled export. You can suspend individual scheduled exports by changing the Post Status.', 'woocommerce-exporter' ); ?></p>
            </td>
            </tr>
            <tr>
            <th>&nbsp;</th>
            <td>
                <p>
                <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'scheduled_export' ) ) ); ?>"><?php esc_html_e( 'View Scheduled Exports', 'woocommerce-exporter' ); ?></a>
                </p>
            </td>
            </tr>
        </table>
        <?php
            do_action(
                'wse_show_upsell_overlay',
                'scheduledexportsettings',
                __( 'Scheduled Exports available in Store Exporter Deluxe', 'woocommerce-exporter' ),
                __( 'Upgrade to Store Exporter Deluxe for advanced export scheduling and premium support.', 'woocommerce-exporter' )
            );
        ?>
        </div>
    </td>
    </tr>



    <tr id="cron-exports">
    <td colspan="2" style="padding:0;">
        <hr />
        <h3>
        <div class="dashicons dashicons-clock"></div>&nbsp;<?php esc_html_e( 'CRON Exports', 'woocommerce-exporter' ); ?>
        </h3>
        <?php if ( $enable_cron ) { ?>
        <p style="font-size:0.8em;">
        <div class="dashicons dashicons-yes"></div>&nbsp;<strong><?php esc_html_e( 'CRON Exports is enabled', 'woocommerce-exporter' ); ?></strong></p>
        <?php } ?>
        <p class="description">
        <?php
            // translators: %s: URL.
            echo wp_kses_post( sprintf( __( 'Store Exporter Deluxe supports exporting via a command line request, to do this you need to prepare a specific URL and pass it the following required inline parameters. For sample CRON requests and supported arguments consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), esc_url( $troubleshooting_url ) ) );
        ?>
        </p>
    </td>
    </tr>
    <tr>
    <td colspan="2">
        <div class="wse-upsell-content-overlayed">
        <table class="form-table">
            <tr>
            <th><label for="enable_cron"><?php esc_html_e( 'Enable CRON', 'woocommerce-exporter' ); ?></label></th>
            <td>
                <select id="enable_cron" name="enable_cron">
                <option value="1" <?php selected( $enable_cron, 1 ); ?>><?php esc_html_e( 'Yes', 'woocommerce-exporter' ); ?></option>
                <option value="0" <?php selected( $enable_cron, 0 ); ?>><?php esc_html_e( 'No', 'woocommerce-exporter' ); ?></option>
                </select>
                <p class="description"><?php esc_html_e( 'Enabling CRON allows developers to schedule automated exports and connect with Store Exporter Deluxe remotely.', 'woocommerce-exporter' ); ?></p>
            </td>
            </tr>
            <tr>
            <th>
                <label for="secret_key"><?php esc_html_e( 'Export secret key', 'woocommerce-exporter' ); ?></label>
            </th>
            <td>
                <input type="text" id="secret_key" disabled="disabled" class="large-text code" />
                <p class="description"><?php esc_html_e( 'This secret key (can be left empty to allow unrestricted access) limits access to authorised developers who provide a matching key when working with Store Exporter Deluxe.', 'woocommerce-exporter' ); ?></p>
            </td>
            </tr>
            <tr>
            <th>
                <?php esc_html_e( 'Export fields', 'woocommerce-exporter' ); ?>
            </th>
            <td>
                <ul style="margin-top:0.2em;">
                <li>
                    <label><input type="radio" disabled="disabled" /> <?php esc_html_e( 'Include all Export Fields for the requested Export Type', 'woocommerce-exporter' ); ?></label>
                </li>
                <li>
                    <label><input type="radio" disabled="disabled" /> <?php esc_html_e( 'Use the saved fields preferences from the following Export Template for the requested Export Type', 'woocommerce-exporter' ); ?></label><br />
                    <select disabled="disabled" class="select short">
                        <option><?php esc_html_e( 'Choose a Export Template...', 'woocommerce-exporter' ); ?></option>
                    </select>
                </li>
                <li>
                    <label><input type="radio" disabled="disabled" /> <?php esc_html_e( 'Use the saved Export Fields preference set on the Quick Export screen for the requested Export Type', 'woocommerce-exporter' ); ?></label>
                </li>
                </ul>
                <p class="description"><?php esc_html_e( 'Control whether all known export fields are included or only checked fields from the Export Fields section on the Quick Export screen for each Export Type. Default is to include all export fields.', 'woocommerce-exporter' ); ?></p>
            </td>
            </tr>
        </table>
        <?php
            do_action(
                'wse_show_upsell_overlay',
                'cronsettings',
                __( 'CRON Exports available in Store Exporter Deluxe', 'woocommerce-exporter' ),
                __( 'Upgrade to Store Exporter Deluxe to unlock automated CRON exports and premium support.', 'woocommerce-exporter' )
            );
        ?>
        </div>
    </td>
    </tr>
    <!-- #cron-exports -->

    <tr id="orders-screen">
    <td colspan="2" style="padding:0;">
        <hr />
        <h3>
        <div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php esc_html_e( 'Orders Screen', 'woocommerce-exporter' ); ?>
        </h3>
    </td>
    <tr>
    <td colspan="2">
        <div class="wse-upsell-content-overlayed">
        <table class="form-table">
            <tr>
            <tr>
            <th>
                <?php esc_html_e( 'Actions display', 'woocommerce-exporter' ); ?>
            </th>
            <td>
                <ul>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Export to CSV', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Export to TSV', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Export to XLS', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Export to XLSX', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="checkbox" disabled="disabled" /> <?php esc_html_e( 'Export to XML', 'woocommerce-exporter' ); ?></label></li>
                </ul>
                <p class="description"><?php esc_html_e( 'Control the visibility of different Order actions on the WooCommerce &raquo; Orders screen.', 'woocommerce-exporter' ); ?></p>
            </td>
            </tr>
            <tr>
            <th><?php esc_html_e( 'Order items formatting', 'woocommerce-exporter' ); ?></th>
            <td>
                <ul>
                <li><label><input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Place Order Items within a grouped single Order row', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Place Order Items on individual cells within a single Order row', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Place each Order Item within their own Order row', 'woocommerce-exporter' ); ?></label></li>
                </ul>
                <p class="description"><?php esc_html_e( 'Choose how you would like Order Items to be presented within Orders from the WooCommerce &raquo; Orders screen.', 'woocommerce-exporter' ); ?></p>
            </td>
            </tr>
            <tr>
            <th><?php esc_html_e( 'Export fields', 'woocommerce-exporter' ); ?></th>
            <td>
                <ul style="margin-top:0.2em;">
                <li>
                    <label><input type="radio" disabled="disabled" /> <?php esc_html_e( 'Include all fields for the requested Export Type', 'woocommerce-exporter' ); ?></label>
                </li>
                <li>
                    <label><input type="radio" disabled="disabled" /> <?php esc_html_e( 'Use the saved fields preferences from the following Export Template for the requested Export Type', 'woocommerce-exporter' ); ?></label><br />
                    <select id="export_template" disabled="disabled" class="select short">
                        <option><?php esc_html_e( 'Choose a Export Template...', 'woocommerce-exporter' ); ?></option>
                    </select>
                </li>
                <li>
                    <label><input type="radio" disabled="disabled" /> <?php esc_html_e( 'Use the saved fields preference set on the Quick Export screen for the requested Export Type', 'woocommerce-exporter' ); ?></label>
                </li>
                </ul>
                <p class="description"><?php esc_html_e( 'Control whether all known export fields are included or only checked fields from the Export Fields section on the Quick Export screen for each Export Type. Default is to include all export fields.', 'woocommerce-exporter' ); ?></p>
            </td>
            </tr>
        </table>
        <?php
            do_action(
                'wse_show_upsell_overlay',
                'orderscreensettings',
                __( 'Order Screen Settings available in Store Exporter Deluxe', 'woocommerce-exporter' ),
                __( 'Upgrade to Store Exporter Deluxe to unlock advanced Order screen export options and premium support.', 'woocommerce-exporter' )
            );
        ?>
        </div>
    </td>
    </tr>
    <!-- #orders-screen -->

    <tr id="export-triggers">
    <td colspan="2" style="padding:0;">
        <hr />
        <h3>
        <div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php esc_html_e( 'Export Triggers', 'woocommerce-exporter' ); ?>
        </h3>
        <p class="description"><?php esc_html_e( 'Run exports on specific triggers within your WooCommerce store.', 'woocommerce-exporter' ); ?></p>
    </td>
    </tr>
    <!-- #export-triggers -->

    <tr>
    <td colspan="2">
        <div class="wse-upsell-content-overlayed">
        <table class="form-table">
        <tr id="new-orders">
        <th><?php esc_html_e( 'New Order', 'woocommerce-exporter' ); ?></th>
        <td>
            <?php if ( $enable_trigger_new_order ) { ?>
            <p style="font-size:0.8em;">
            <div class="dashicons dashicons-yes"></div>&nbsp;<strong><?php esc_html_e( 'Export on New Order is enabled, this will run for each new Order received.', 'woocommerce-exporter' ); ?></strong></p>
            <?php } ?>
            <p class="description"><?php esc_html_e( 'Trigger an export of each new Order that is generated after successful Checkout.', 'woocommerce-exporter' ); ?></p>
            <ul>

            <li>
                <p>
                <label for="enable_trigger_new_order"><?php esc_html_e( 'Enable trigger', 'woocommerce-exporter' ); ?></label><br />
                <select id="enable_trigger_new_order" name="enable_trigger_new_order" disabled="disabled">
                    <option><?php esc_html_e( 'No', 'woocommerce-exporter' ); ?></option>
                    <option><?php esc_html_e( 'Yes', 'woocommerce-exporter' ); ?></option>
                </select>
                </p>
                <hr />
            </li>

            <li>
                <p>
                <label for="trigger_new_order_status"><?php esc_html_e( 'Order status', 'woocommerce-exporter' ); ?></label><br />
                <select id="trigger_new_order_status" disabled="disabled">
                    <option><?php esc_html_e( 'Any Order status', 'woocommerce-exporter' ); ?></option>
                </select>
                </p>
                <p class="description"><?php esc_html_e( 'Run the New Order export only on a specific Order status. Default is to run when the Order is created regardless of Order status.', 'woocommerce-exporter' ); ?></p>
                <hr />
            </li>

            <li>
                <p><label><?php esc_html_e( 'Export format', 'woocommerce-exporter' ); ?></label></p>
                <?php if ( ! empty( $export_formats ) ) { ?>
                <ul style="margin-top:0.2em;">
                    <?php foreach ( $export_formats as $key => $export_format ) { ?>
                    <li>
                        <label>
                        <input type="radio" disabled="disabled"/> <?php echo esc_html( $export_format['title'] ); ?>
                        <?php if ( ! empty( $export_format['description'] ) ) { ?>
                            <span class="description">(<?php echo wp_kses_post( $export_format['description'] ); ?>)</span><?php } ?>
                        </label>
                    </li>
                    <?php } ?>
                </ul>
                <?php } else { ?>
                <?php esc_html_e( 'No export formats were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
                <hr />
            </li>

            <li>
                <p><label for="trigger_new_order_method"><?php esc_html_e( 'Export method', 'woocommerce-exporter' ); ?></label></p>
                <select id="trigger_new_order_method" disabled="disabled">
                <option value="archive"><?php echo esc_html( woo_ce_format_export_method( 'archive' ) ); ?></option>
                <option value="save"><?php echo esc_html( woo_ce_format_export_method( 'save' ) ); ?></option>
                <option value="email"><?php echo esc_html( woo_ce_format_export_method( 'email' ) ); ?></option>
                <option value="post"><?php echo esc_html( woo_ce_format_export_method( 'post' ) ); ?></option>
                <option value="ftp"><?php echo esc_html( woo_ce_format_export_method( 'ftp' ) ); ?></option>
                </select>
                <hr />
            </li>

            <li class="export_method_options">
                <p style="margin-bottom:0.5em;">
                <label><?php esc_html_e( 'Export method options', 'woocommerce-exporter' ); ?></label>
                </p>
                <div>
                <ul style="margin-top:0.2em;">
                    <li>
                    <label for="trigger_new_method_scheduled_export">
                        <?php esc_html_e( 'Scheduled Export' ); ?>
                        <img class="help_tip" data-tip="<?php esc_attr_e( 'Use the export method details from the Scheduled Export.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                    </label>
                    </li>
                    <li>
                    <select id="trigger_new_method_scheduled_export" disabled="disabled">
                        <option value=""><?php esc_html_e( 'Use export method options from this screen', 'woocommerce-exporter' ); ?></option>
                        <optgroup label="Scheduled Exports">
                            <option><?php esc_html_e( 'Choose a Scheduled Export...', 'woocommerce-exporter' ); ?></option>
                        </optgroup>
                    </select>
                    </li>
                </ul>
                </div>

                <div class="export-options save-options">
                <ul style="margin-top:0.2em;">
                    <li>
                    <label for="trigger_new_method_save_file_path">
                        <?php esc_html_e( 'File path', 'woocommerce-exporter' ); ?>
                        <img class="help_tip" data-tip="<?php esc_attr_e( 'Do not provide the filename within File path as it will be generated for you or rely on the fixed filename entered below.<br /><br />For file path example: <code>wp-content/uploads/exports/</code>', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                    </label><br />
                    <code><?php echo esc_html( get_home_path() ); ?></code>
                    <input type="text" disabled="disabled" placeholder="<?php echo esc_attr( get_home_path() ); ?>" />
                    </li>
                    <li>
                    <label for="trigger_new_method_save_filename">
                        <?php esc_html_e( 'Fixed filename', 'woocommerce-exporter' ); ?>
                        <?php // translators: %1$dataset%, %2$date%, %time%, %year%, %month%, %3$day%, %4$hour%, %minute%, %random%, %5$store_name%. ?>
                        <img class="help_tip" data-tip="<?php esc_attr_e( 'The export filename can be set within the Fixed filename field otherwise it defaults to the Export filename provided within General Settings above.<br /><br />Tags can be used: %1$dataset%, %2$date%, %time%, %year%, %month%, %3$day%, %4$hour%, %minute%, %random%, %5$store_name%', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                    </label>
                    <input type="text" disabled="disabled" />
                    </li>
                </ul>
                </div>
                <!-- .save-options -->

                <div class="export-options email-options">
                <ul style="margin-top:0.2em;">
                    <li>
                    <label for="trigger_new_method_email_to">
                        <?php esc_html_e( 'E-mail recipient', 'woocommerce-exporter' ); ?>
                        <img class="help_tip" data-tip="<?php esc_attr_e( 'Set the recipient of Order export trigger e-mails, multiple recipients can be added using the comma separator.<br /><br />Default is the Blog Administrator e-mail address set on the WordPress &raquo; Settings screen.', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                    </label>
                    <input type="text" disabled="disabled" placeholder="big.bird@sesamestreet.org,oscar@sesamestreet.org" />
                    </li>
                    <li>
                    <label for="trigger_new_method_email_subject">
                        <?php esc_html_e( 'E-mail subject', 'woocommerce-exporter' ); ?>
                        <?php // translators: %1$store_name%, %2$export_type%, %3$export_filename%. ?>
                        <img class="help_tip" data-tip="<?php esc_attr_e( 'Set the subject of scheduled export e-mails.<br /><br />Tags can be used: %1$store_name%, %2$export_type%, %3$export_filename%', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                    </label>
                    <input type="text" disabled="disabled" placeholder="<?php esc_attr_e( 'Order export', 'woocommerce-exporter' ); ?>" />
                    </li>
                    <li>
                    <label for="trigger_new_method_email_contents">
                        <?php esc_html_e( 'E-mail contents', 'woocommerce-exporter' ); ?>
                        <?php // translators: %1$store_name%, %2$export_type%, %3$export_filename%. ?>
                        <img class="help_tip" data-tip="<?php esc_attr_e( 'Set the e-mail contents of scheduled export e-mails.<br /><br />Tags can be used: %1$store_name%, %2$export_type%, %3$export_filename%', 'woocommerce-exporter' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                    </label>
                    <textarea disabled="disabled" id="trigger_new_method_email_contents" placeholder="<?php esc_attr_e( 'Please find attached your export ready to review.', 'woocommerce-exporter' ); ?>" rows="2" cols="20" class="large-text" style="height:10em;"><?php echo esc_textarea( $trigger_new_order_method_email_contents ); ?></textarea>
                    </li>
                </ul>
                </div>
                <!-- .email-options -->

                <div class="export-options post-options">
                <ul style="margin-top:0.2em;">
                    <li>
                    <label for="trigger_new_method_post_to"><?php esc_html_e( 'Remote POST URL', 'woocommerce-exporter' ); ?></label>
                    <input type="text" id="trigger_new_method_post_to" disabled="disabled" class="large-text" placeholder="" />
                    </li>
                </ul>
                </div>
                <!-- .post-options -->

                <hr />
            </li>

            <li>
                <p><label><?php esc_html_e( 'Order items formatting', 'woocommerce-exporter' ); ?></label></p>
                <ul style="margin-top:0.2em;">
                <li><label><input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Place Order Items within a grouped single Order row', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Place Order Items on individual cells within a single Order row', 'woocommerce-exporter' ); ?></label></li>
                <li><label><input type="radio" disabled="disabled" />&nbsp;<?php esc_html_e( 'Place each Order Item within their own Order row', 'woocommerce-exporter' ); ?></label></li>
                </ul>
            </li>

            <li>
                <p><label><?php esc_html_e( 'Export fields', 'woocommerce-exporter' ); ?></label></p>
                <ul style="margin-top:0.2em;">
                <li>
                    <label><input type="radio" id="trigger_new_order_fields" disabled="disabled" /> <?php esc_html_e( 'Include all Order Fields', 'woocommerce-exporter' ); ?></label>
                </li>
                <li>
                    <label><input type="radio" disabled="disabled" /> <?php esc_html_e( 'Use the saved fields preference for Orders set on the Quick Export screen', 'woocommerce-exporter' ); ?></label>
                </li>
                </ul>
                <p class="description"><?php esc_html_e( 'Control whether all known export fields are included or only checked fields from the Export Fields section on the Quick Export screen for Orders. Default is to include all export fields.', 'woocommerce-exporter' ); ?></p>
            </li>

            </ul>
        </td>
        </tr>
    </table>
        <?php
        do_action(
            'wse_show_upsell_overlay',
            'exporttriggersettings',
            __( 'Order Export Triggers available in Store Exporter Deluxe', 'woocommerce-exporter' ),
            __( 'Upgrade to Store Exporter Deluxe for advanced auto order export triggers and premium support.', 'woocommerce-exporter' )
        );
        ?>
    </div>
    </td>
</tr>
    <!-- #new-orders -->

    <?php
    ob_end_flush();
}

/**
 * Save export settings.
 */
function woo_ce_export_settings_save() {
    // Verify nonce for security.
    if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'woo_ce_save_settings' ) ) {
        wp_die( 'Security check failed.' );
    }

    $export_filename = sanitize_file_name( $_POST['export_filename'] );
    // Strip file extension from export filename.
    $export_filename = preg_replace( '/\.(csv|tsv|xml|xls|xlsx)$/', '', $export_filename );
    woo_ce_update_option( 'export_filename', $export_filename );

    woo_ce_update_option( 'delete_file', isset( $_POST['delete_file'] ) ? absint( $_POST['delete_file'] ) : '' );
    woo_ce_update_option( 'encoding', isset( $_POST['encoding'] ) ? sanitize_text_field( $_POST['encoding'] ) : '' );
    woo_ce_update_option( 'delimiter', isset( $_POST['delimiter'] ) ? sanitize_text_field( $_POST['delimiter'] ) : '' );
    woo_ce_update_option( 'category_separator', isset( $_POST['category_separator'] ) ? sanitize_text_field( $_POST['category_separator'] ) : '' );
    woo_ce_update_option( 'line_ending_formatting', isset( $_POST['line_ending'] ) ? sanitize_text_field( $_POST['line_ending'] ) : '' );
    woo_ce_update_option( 'bom', isset( $_POST['bom'] ) ? absint( $_POST['bom'] ) : '' );
    woo_ce_update_option( 'escape_formatting', isset( $_POST['escape_formatting'] ) ? sanitize_text_field( $_POST['escape_formatting'] ) : '' );
    woo_ce_update_option( 'excel_formulas', isset( $_POST['excel_formulas'] ) ? absint( $_POST['excel_formulas'] ) : '' );
    woo_ce_update_option( 'header_formatting', isset( $_POST['header_formatting'] ) ? absint( $_POST['header_formatting'] ) : '' );
    woo_ce_update_option( 'flush_cache', isset( $_POST['flush_cache'] ) ? absint( $_POST['flush_cache'] ) : '' );
    woo_ce_update_option( 'timeout', isset( $_POST['timeout'] ) ? absint( $_POST['timeout'] ) : '' );

    $date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );
    if ( 'custom' === $_POST['date_format'] && ! empty( $_POST['date_format_custom'] ) ) {
        if ( $_POST['date_format'] !== $date_format ) {
            woo_ce_update_option( 'date_format', sanitize_text_field( $_POST['date_format_custom'] ) );
        }
    } elseif ( $_POST['date_format'] !== $date_format ) {
        // Update the date format on scheduled exports.
        $scheduled_exports = woo_ce_get_scheduled_exports();
        if ( ! empty( $scheduled_exports ) ) {
            foreach ( $scheduled_exports as $scheduled_export ) {
                $order_dates_from = get_post_meta( $scheduled_export, '_filter_order_dates_from', true );
                $order_dates_to   = get_post_meta( $scheduled_export, '_filter_order_dates_to', true );
                // Format date to new format.
                if ( ! empty( $order_dates_from ) ) {
                    update_post_meta( $scheduled_export, '_filter_order_dates_from', gmdate( sanitize_text_field( $_POST['date_format'] ), strtotime( $order_dates_from ) ) );
                }
                if ( ! empty( $order_dates_to ) ) {
                    update_post_meta( $scheduled_export, '_filter_order_dates_to', gmdate( sanitize_text_field( $_POST['date_format'] ), strtotime( $order_dates_to ) ) );
                }
            }
        }
        woo_ce_update_option( 'date_format', sanitize_text_field( $_POST['date_format'] ) );
    }

    // XML settings.
    $xml_options = array(
        'xml_attribute_url',
        'xml_attribute_title',
        'xml_attribute_date',
        'xml_attribute_time',
        'xml_attribute_export',
        'xml_attribute_orderby',
        'xml_attribute_order',
        'xml_attribute_limit',
        'xml_attribute_offset',
    );
    foreach ( $xml_options as $option ) {
        woo_ce_update_option( $option, isset( $_POST[ $option ] ) ? absint( $_POST[ $option ] ) : 0 );
    }

    // RSS settings.
    $rss_options = array( 'rss_title', 'rss_description' );
    foreach ( $rss_options as $option ) {
        woo_ce_update_option( $option, isset( $_POST[ $option ] ) ? sanitize_text_field( $_POST[ $option ] ) : '' );
    }
    woo_ce_update_option( 'rss_link', isset( $_POST['rss_link'] ) ? esc_url_raw( $_POST['rss_link'] ) : '' );

    // Scheduled export settings.
    $enable_auto = isset( $_POST['enable_auto'] ) ? absint( $_POST['enable_auto'] ) : 0;
    if ( woo_ce_get_option( 'enable_auto', 0 ) !== $enable_auto ) {
        woo_ce_update_option( 'enable_auto', $enable_auto );
    }

    // CRON settings.
    $enable_cron = isset( $_POST['enable_cron'] ) ? $_POST['enable_cron'] : '0';
    if ( woo_ce_get_option( 'enable_cron', '0' ) !== $enable_cron ) {
        $message = sprintf(
            /* translators: %s: enabled or disabled */
            __( 'CRON support has been %s.', 'woocommerce-exporter' ),
            $enable_cron ? __( 'enabled', 'woocommerce-exporter' ) : __( 'disabled', 'woocommerce-exporter' )
        );
        woo_cd_admin_notice( $message );
        woo_ce_update_option( 'enable_cron', absint( $enable_cron ) );
    }
    woo_ce_update_option( 'secret_key', isset( $_POST['secret_key'] ) ? sanitize_text_field( $_POST['secret_key'] ) : '' );
    woo_ce_update_option( 'cron_fields', isset( $_POST['cron_fields'] ) ? sanitize_text_field( $_POST['cron_fields'] ) : '' );
    woo_ce_update_option( 'cron_export_template', isset( $_POST['cron_export_template'] ) ? sanitize_text_field( $_POST['cron_export_template'] ) : '' );

    // Orders Screen.
    $order_actions = array( 'csv', 'tsv', 'xls', 'xlsx', 'xml' );
    foreach ( $order_actions as $action ) {
        woo_ce_update_option( "order_actions_$action", isset( $_POST[ "order_actions_$action" ] ) ? absint( $_POST[ "order_actions_$action" ] ) : 0 );
    }
    woo_ce_update_option( 'order_actions_fields', isset( $_POST['order_actions_fields'] ) ? sanitize_text_field( $_POST['order_actions_fields'] ) : '' );
    woo_ce_update_option( 'order_actions_order_items_formatting', isset( $_POST['order_actions_order_items'] ) ? sanitize_text_field( $_POST['order_actions_order_items'] ) : '' );
    woo_ce_update_option( 'order_actions_export_template', isset( $_POST['order_actions_export_template'] ) ? sanitize_text_field( $_POST['order_actions_export_template'] ) : '' );

    // Export Triggers.
    woo_ce_update_option( 'enable_trigger_new_order', isset( $_POST['enable_trigger_new_order'] ) ? absint( $_POST['enable_trigger_new_order'] ) : 0 );
    woo_ce_update_option( 'trigger_new_order_status', isset( $_POST['trigger_new_order_status'] ) ? sanitize_text_field( $_POST['trigger_new_order_status'] ) : '' );
    woo_ce_update_option( 'trigger_new_order_format', isset( $_POST['trigger_new_order_format'] ) ? sanitize_text_field( $_POST['trigger_new_order_format'] ) : '' );
    woo_ce_update_option( 'trigger_new_order_method', isset( $_POST['trigger_new_order_method'] ) ? sanitize_text_field( $_POST['trigger_new_order_method'] ) : '' );
    woo_ce_update_option( 'trigger_new_order_method_save_file_path', isset( $_POST['trigger_new_method_save_file_path'] ) ? sanitize_text_field( $_POST['trigger_new_method_save_file_path'] ) : '' );
    woo_ce_update_option( 'trigger_new_order_method_save_filename', isset( $_POST['trigger_new_method_save_filename'] ) ? sanitize_file_name( $_POST['trigger_new_method_save_filename'] ) : '' );

    $email_to = isset( $_POST['trigger_new_order_method'] ) ? sanitize_text_field( $_POST['trigger_new_method_email_to'] ) : '';
    $email_to = str_replace( ';', ',', $email_to );
    woo_ce_update_option( 'trigger_new_order_method_email_to', $email_to );

    woo_ce_update_option( 'trigger_new_order_method_email_subject', isset( $_POST['trigger_new_method_email_subject'] ) ? sanitize_text_field( $_POST['trigger_new_method_email_subject'] ) : '' );
    woo_ce_update_option( 'trigger_new_order_method_email_contents', isset( $_POST['trigger_new_method_email_contents'] ) ? wp_kses( $_POST['trigger_new_method_email_contents'], woo_ce_format_email_contents_allowed_html(), woo_ce_format_email_contents_allowed_protocols() ) : '' );
    woo_ce_update_option( 'trigger_new_order_method_post_to', isset( $_POST['trigger_new_method_post_to'] ) ? esc_url_raw( $_POST['trigger_new_method_post_to'] ) : '' );
    woo_ce_update_option( 'trigger_new_order_method_scheduled_export', isset( $_POST['trigger_new_method_scheduled_export'] ) ? sanitize_text_field( $_POST['trigger_new_method_scheduled_export'] ) : '' );
    woo_ce_update_option( 'trigger_new_order_items_formatting', isset( $_POST['trigger_new_order_order_items'] ) ? sanitize_text_field( $_POST['trigger_new_order_order_items'] ) : false );
    woo_ce_update_option( 'trigger_new_order_fields', isset( $_POST['trigger_new_order_fields'] ) ? sanitize_text_field( $_POST['trigger_new_order_fields'] ) : '' );

    // Allow Plugin/Theme authors to save custom Setting options.
    do_action( 'woo_ce_extend_export_settings_save' );

    $message = __( 'Changes have been saved.', 'woocommerce-exporter' );
    woo_cd_admin_notice( $message );
}
