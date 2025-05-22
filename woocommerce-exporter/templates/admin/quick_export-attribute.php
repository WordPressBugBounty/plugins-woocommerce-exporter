<?php if ( $attribute && $attribute_fields ) { ?>
    <div id="export-attribute" class="export-types">

        <div class="postbox">
            <h3 class="hndle">
                <?php esc_html_e( 'Attribute Fields', 'woocommerce-exporter' ); ?>
            </h3>
            <div class="inside">
                <?php if ( $attribute ) { ?>
                    <p class="description"><?php woo_ce_export_fields_summary_text( $export_type ); ?></p>
                    <p>
                        <a href="javascript:void(0)" id="attribute-checkall" class="checkall"><?php esc_html_e( 'Check All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="attribute-uncheckall" class="uncheckall"><?php esc_html_e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="attribute-resetsorting" class="resetsorting"><?php esc_html_e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> |
                    </p>
                    <table id="attribute-fields" class="ui-sortable striped">

                        <?php foreach ( $attribute_fields as $field ) { ?>
                            <tr id="attribute-<?php echo esc_attr( $field['reset'] ); ?>" data-export-type="attribute" data-field-name="<?php printf( '%s-%s', 'attribute', esc_attr( $field['name'] ) ); ?>">
                                <td>
                                    <label
                                    <?php
                                    if ( isset( $field['hover'] ) ) {
                                    ?>
                                    title="<?php echo esc_attr( $field['hover'] ); ?>" <?php } ?>>
                                        <input type="checkbox" name="attribute_fields[<?php echo esc_attr( $field['name'] ); ?>]" class="attribute_field" disabled="disabled" />
                                        <span class="field_title">
                                            <?php echo esc_attr( $field['label'] ); ?>
                                            <?php if ( $field['disabled'] ) { ?>
                                            <span class="description"> -
                                                <?php
                                                    echo wp_kses_post(
                                                        sprintf(
                                                            // translators: %s is the link to the Product Fields extension.
                                                            __( 'available in %s', 'woocommerce-exporter' ),
                                                            woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=attributefields' . str_replace( '_', '', $field['name'] ) . 'link' )
                                                        )
                                                    );
                                                ?>
                                            </span>
                                            <?php } ?>
                                        </span>
                                        <?php if ( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'attribute' ) ) { ?>
                                            <span class="field_hover"><?php echo esc_attr( $field['hover'] ); ?></span>
                                        <?php } ?>
                                        <input type="hidden" name="attribute_fields_order[<?php echo esc_attr( $field['name'] ); ?>]" class="field_order" value="<?php echo esc_attr( $field['order'] ); ?>" />
                                        </label>
                                </td>
                            </tr>

                        <?php } ?>
                    </table>
                    <p class="submit">
                        <input type="button" class="button button-disabled" value="<?php esc_attr_e( 'Export Attributes', 'woocommerce-exporter' ); ?>" />
                    </p>
                    <p class="description"><?php esc_html_e( 'Can\'t find a particular Attribute field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo esc_url( $troubleshooting_url ); ?>" target="_blank"><?php esc_html_e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
                <?php } else { ?>
                    <p><?php esc_html_e( 'No Attributes were found.', 'woocommerce-exporter' ); ?></p>
                <?php } ?>
            </div>
            <!-- .inside -->
        </div>
        <!-- .postbox -->
    </div>
    <!-- #export-attributes -->
<?php } ?>
