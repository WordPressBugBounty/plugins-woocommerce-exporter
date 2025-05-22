<?php
/**
 * Renders the export format options in the admin panel.
 *
 * This function generates a table row with radio buttons for selecting the export format.
 * It retrieves the available export formats from the `woo_ce_get_export_formats()` function.
 * The selected export format is determined by the `export_format` option in the WooCommerce Exporter Deluxe plugin settings.
 */
function woo_ce_export_options_export_format() {

    $export_formats = woo_ce_get_export_formats();
    $type           = woo_ce_get_option( 'export_format', 'csv' );

    ob_start(); ?>
    <tr id="export-format">
        <th>
            <label><?php esc_html_e( 'Export format', 'woocommerce-exporter' ); ?></label>
        </th>
        <td>
            <?php if ( ! empty( $export_formats ) ) { ?>
                <ul>
                    <?php foreach ( $export_formats as $key => $export_format ) { ?>
                        <li>
                            <label><input type="radio" name="export_format" value="<?php echo esc_attr( $key ); ?>" <?php checked( $type, $key ); ?> <?php echo isset( $export_format['disabled'] ) && $export_format['disabled'] ? 'disabled="disabled"' : ''; ?> /> <?php echo esc_html( $export_format['title'] ); ?>
                            <span class="description">
                                <?php echo ! empty( $export_format['description'] ) ? '(' . wp_kses_post( $export_format['description'] ) . ')' : ''; ?>
                                <?php
                                if ( ! empty( $export_format['disabled'] ) && true === $export_format['disabled'] ) :
                                    echo ' - ' . wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=optionsformat' . $key . 'link' ) ) );
                                endif;
                                ?>
                            </span>
                            </label>
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <?php esc_html_e( 'No export formats were found.', 'woocommerce-exporter' ); ?>
            <?php } ?>
            <p class="description"><?php esc_html_e( 'Adjust the export format to generate different export file formats.', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>
<?php
    ob_end_flush();
}

/**
 * Renders the export options for the export template.
 *
 * This function generates the HTML markup for the export template dropdown
 * and displays it on the admin page.
 */
function woo_ce_export_options_export_template() {

    $args             = array(
        'post_status' => 'publish',
    );
    $export_templates = woo_ce_get_export_templates( $args );

    ob_start();
    ?>
    <tr id="export-template">
        <th>
            <label for="export_template"><?php esc_html_e( 'Export template', 'woocommerce-exporter' ); ?></label>
        </th>
        <td>
            <select id="export_template" name="export_template" <?php disabled( empty( $export_templates ), true ); ?> class="select short">
                <option><?php esc_html_e( 'Choose a Export Template...', 'woocommerce-exporter' ); ?></option>
            </select>
            <img src="<?php echo esc_url( WOO_CE_PLUGINPATH ); ?>/templates/admin/images/loading.gif" class="loading" />
            <span class="description">
                <?php echo ' - ' . wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=optionsexporttemplatelink' ) ) ); ?>
            </span>    
        </td>
    </tr>
<?php
    ob_end_flush();
}

/**
 * Displays the troubleshooting information for export options.
 *
 * This function outputs HTML markup that displays troubleshooting information for exporting options in the WooCommerce Store Exporter Deluxe plugin.
 * It provides instructions on how to use the batch export function to create smaller exports by limiting the volume and volume offset.
 */
function woo_ce_export_options_troubleshooting() {

    ob_start();
    ?>
    <tr>
        <th>&nbsp;</th>
        <td>
            <p class="description">
                <?php esc_html_e( 'Having difficulty downloading your exports in one go? Use our batch export function - Limit Volume and Volume Offset - to create smaller exports.', 'woocommerce-exporter' ); ?><br />
                <?php esc_html_e( 'Set the first text field (Volume limit) to the number of records to export each batch (e.g. 200), set the second field (Volume offset) to the starting record (e.g. 0). After each successful export increment only the Volume offset field (e.g. 201, 401, 601, 801, etc.) to export the next batch of records.', 'woocommerce-exporter' ); ?>
            </p>
        </td>
    </tr>
<?php
    ob_end_flush();
}

/**
 * Displays the export options for limiting the volume of records to be exported.
 *
 * This function is responsible for rendering the HTML markup for the export options form field
 * that allows the user to specify a limit on the number of records to be exported.
 */
function woo_ce_export_options_limit_volume() {

    $limit_volume = woo_ce_get_option( 'limit_volume' );

    ob_start();
    ?>
    <tr>
        <th><label for="limit_volume"><?php esc_html_e( 'Limit volume', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <input type="text" size="3" id="limit_volume" name="limit_volume" value="<?php echo esc_attr( $limit_volume ); ?>" size="5" class="text" title="<?php esc_html_e( 'Limit volume', 'woocommerce-exporter' ); ?>" />
            <p class="description"><?php esc_html_e( 'Limit the number of records to be exported. By default this is not used and is left empty.', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>
<?php
    ob_end_flush();
}

/**
 * Renders the volume offset export option in the WooCommerce Store Exporter Deluxe plugin.
 *
 * This function is responsible for rendering the HTML markup for the volume offset export option in the admin panel.
 * The volume offset allows the user to specify the number of records to be skipped in the export.
 */
function woo_ce_export_options_volume_offset() {

    $offset = woo_ce_get_option( 'offset' );

    ob_start();
    ?>
    <tr>
        <th><label for="offset"><?php esc_html_e( 'Volume offset', 'woocommerce-exporter' ); ?></label></th>
        <td>
            <input type="text" size="3" id="offset" name="offset" value="<?php echo esc_attr( $offset ); ?>" size="5" class="text" title="<?php esc_html_e( 'Volume offset', 'woocommerce-exporter' ); ?>" />
            <p class="description"><?php esc_html_e( 'Set the number of records to be skipped in this export. By default this is not used and is left empty.', 'woocommerce-exporter' ); ?></p>
        </td>
    </tr>
<?php
    ob_end_flush();
}
?>
