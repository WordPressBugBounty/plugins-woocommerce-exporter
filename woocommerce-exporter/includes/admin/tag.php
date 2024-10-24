<?php
/**
 * HTML template for Filter Tags by Language widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Filter Tags by Language widget on the Store Exporter screen.
 * It checks if the WPML plugin is active and retrieves the list of languages using the icl_get_languages function.
 * It then generates the HTML markup for the widget, including a checkbox to enable/disable the filter and a dropdown
 * to select the languages to filter by. If no languages are found, a message is displayed indicating that no languages
 * were found. The function uses output buffering to capture the generated HTML and flushes it at the end.
 *
 * @since 1.0.0
 */
function woo_ce_tags_filter_by_language() {

    if ( ! woo_ce_detect_wpml() ) {
        return;
    }

    $languages = ( function_exists( 'icl_get_languages' ) ? icl_get_languages( 'skip_missing=N' ) : array() );

    ob_start(); ?>
    <p><label><input type="checkbox" id="tags-filters-language" /> <?php esc_html_e( 'Filter Tags by Language', 'woocommerce-exporter' ); ?></label></p>
    <div id="export-tags-filters-language" class="separator">
        <ul>
            <li>
                <?php if ( ! empty( $languages ) ) { ?>
                    <select data-placeholder="<?php esc_attr_e( 'Choose a Language...', 'woocommerce-exporter' ); ?>" name="tag_filter_language[]" multiple class="chzn-select" style="width:95%;">
                        <?php foreach ( $languages as $key => $language ) { ?>
                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $language['native_name'] ); ?> (<?php echo esc_html( $language['translated_name'] ); ?>)</option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php esc_html_e( 'No Languages were found.', 'woocommerce-exporter' ); ?>
                <?php } ?>
            </li>
        </ul>
        <p class="description"><?php esc_html_e( 'Select the Language\'s you want to filter exported Tags by. Default is to include all Language\'s.', 'woocommerce-exporter' ); ?></p>
    </div>
    <!-- #export-tags-filters-language -->

<?php
    ob_end_flush();
}

/**
 * Renders the HTML template for the Tag Sorting widget on the Store Exporter screen.
 *
 * This function retrieves the tag sorting options from the database and generates the HTML markup for the widget.
 * The widget allows the user to select the sorting criteria for product tags in the exported file.
 *
 * @since 1.0.0
 */
function woo_ce_tag_sorting() {

    $tag_orderby = woo_ce_get_option( 'tag_orderby', 'ID' );
    $tag_order   = woo_ce_get_option( 'tag_order', 'ASC' );

    ob_start();
    ?>
    <p><label><?php esc_html_e( 'Product Tag Sorting', 'woocommerce-exporter' ); ?></label></p>
    <div>
        <select name="tag_orderby">
            <option value="id" <?php selected( 'id', $tag_orderby ); ?>><?php esc_html_e( 'Term ID', 'woocommerce-exporter' ); ?></option>
            <option value="name" <?php selected( 'name', $tag_orderby ); ?>><?php esc_html_e( 'Tag Name', 'woocommerce-exporter' ); ?></option>
        </select>
        <select name="tag_order">
            <option value="ASC" <?php selected( 'ASC', $tag_order ); ?>><?php esc_html_e( 'Ascending', 'woocommerce-exporter' ); ?></option>
            <option value="DESC" <?php selected( 'DESC', $tag_order ); ?>><?php esc_html_e( 'Descending', 'woocommerce-exporter' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Select the sorting of Product Tags within the exported file. By default this is set to export Product Tags by Term ID in Desending order.', 'woocommerce-exporter' ); ?></p>
    </div>
<?php
    ob_end_flush();
}

/**
 * HTML template for Custom Tags widget on Store Exporter screen.
 *
 * This function generates the HTML template for the Custom Tags widget on the Store Exporter screen.
 * It displays a form with options to include additional custom Tag meta in the list of available export fields.
 * Users can fill in the meta text box with custom Tag meta names, save the custom fields, and select them as export fields.
 * The function also provides a link to the online documentation for more information on exporting custom Tag meta.
 *
 * @return void
 */
function woo_ce_tags_custom_fields() {

    $custom_terms = woo_ce_get_option( 'custom_tags', '' );
    if ( $custom_terms ) {
        $custom_terms = implode( "\n", $custom_terms );
    }

    $troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

    ob_start();
    ?>
    <form method="post" id="export-tags-custom-fields" class="export-options tag-options">
        <div id="poststuff">

            <div class="postbox" id="export-options tag-options">
                <h3 class="hndle"><?php esc_html_e( 'Custom Tag Fields', 'woocommerce-exporter' ); ?></h3>
                <div class="inside">
                    <p class="description"><?php esc_html_e( 'To include additional custom Tag meta in the list of available export fields above fill the meta text box then click Save Custom Fields. The saved custom fields will appear as export fields to be selected from the Tag Fields list.', 'woocommerce-exporter' ); ?></p>
                    <table class="form-table">

                        <tr>
                            <th>
                                <label for="custom_tags"><?php esc_html_e( 'Tag meta', 'woocommerce-exporter' ); ?></label>
                            </th>
                            <td>
                                <textarea disabled="disabled" rows="5" cols="70"><?php echo esc_textarea( $custom_terms ); ?></textarea>
                                <p class="description">
                                    <?php echo wp_kses_post( __( 'Include additional custom Tag meta in your export file by adding each custom Tag meta name to a new line above.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ) ); ?>
                                    <span class="description"> - <?php echo wp_kses_post( sprintf( __( 'available in %s', 'woocommerce-exporter' ), woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=tagcustommetalink' ) ) ); ?></span>
                                </p>
                            </td>
                        </tr>

                        <?php do_action( 'woo_ce_tags_custom_fields' ); ?>

                    </table>
                    <p class="description">
                        <?php
                        // Translators: %s is a placeholder for the troubleshooting URL.
                        echo wp_kses_post( sprintf( __( 'For more information on exporting custom Tag meta consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ) );
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
    <!-- #export-tags-custom-fields -->
<?php
    ob_end_flush();
}

/**
 * Renders the scheduled export filters tag.
 *
 * This function is responsible for rendering the scheduled export filters tag HTML markup.
 * It takes an optional parameter $post_ID which represents the ID of the post.
 *
 * @param int $post_ID The ID of the post. Default is 0.
 * @return void
 */
function woo_ce_scheduled_export_filters_tag( $post_ID = 0 ) {

    ob_start();
    ?>
    <div class="export-options tag-options">

        <?php do_action( 'woo_ce_scheduled_export_filters_tag', $post_ID ); ?>

    </div>
    <!-- .tag-options -->

<?php
    ob_end_flush();
}

/**
 * HTML template for Tag Sorting filter on Edit Scheduled Export screen.
 *
 * @param int $post_ID The ID of the post.
 * @return void
 */
function woo_ce_scheduled_export_tag_filter_orderby( $post_ID ) {

    $orderby = get_post_meta( $post_ID, '_filter_tag_orderby', true );
    // Default to Title.
    if ( ! $orderby ) {
        $orderby = 'name';
    }

    ob_start();
    ?>
    <div class="options_group">
        <p class="form-field discount_type_field">
            <label for="tag_filter_orderby"><?php esc_html_e( 'Tag Sorting', 'woocommerce-exporter' ); ?></label>
            <select id="tag_filter_orderby" name="tag_filter_orderby">
                <option value="id" <?php selected( 'id', $orderby ); ?>><?php esc_html_e( 'Term ID', 'woocommerce-exporter' ); ?></option>
                <option value="name" <?php selected( 'name', $orderby ); ?>><?php esc_html_e( 'Tag Name', 'woocommerce-exporter' ); ?></option>
            </select>
        </p>
    </div>
    <!-- .options_group -->
<?php
    ob_end_flush();
}

/**
 * Renders the export template fields for tags.
 *
 * @param int $post_ID The ID of the post.
 * @return void
 */
function woo_ce_export_template_fields_tag( $post_ID = 0 ) {

    $export_type = 'tag';

    $fields = woo_ce_get_tag_fields( 'full', $post_ID );

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
                    <label><?php esc_html_e( 'Tag fields', 'woocommerce-exporter' ); ?></label>
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
                    <p><?php esc_html_e( 'No Tag fields were found.', 'woocommerce-exporter' ); ?></p>
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
