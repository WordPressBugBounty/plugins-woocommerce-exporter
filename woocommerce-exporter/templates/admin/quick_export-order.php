<?php if ( $order && $order_fields ) { ?>
    <div id="export-order" class="export-types">

        <div class="postbox">
            <h3 class="hndle">
                <?php esc_html_e( 'Order Fields', 'woocommerce-exporter' ); ?>
            </h3>
            <div class="inside">
                <?php if ( $order ) { ?>
                    <p class="description"><?php woo_ce_export_fields_summary_text( $export_type ); ?></p>
                    <p>
                        <a href="javascript:void(0)" id="order-checkall" class="checkall"><?php esc_html_e( 'Check All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="order-uncheckall" class="uncheckall"><?php esc_html_e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="order-resetsorting" class="resetsorting"><?php esc_html_e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> |
                    </p>
                    <table id="order-fields" class="ui-sortable striped">

                        <?php foreach ( $order_fields as $field ) { ?>
                            <tr id="order-<?php echo esc_attr( $field['reset'] ); ?>" data-export-type="order" data-field-name="<?php printf( '%s-%s', 'order', esc_attr( $field['name'] ) ); ?>">
                                <td>
                                    <label
                                    <?php
                                    if ( isset( $field['hover'] ) ) {
                                    ?>
                                    title="<?php echo esc_attr( $field['hover'] ); ?>" <?php } ?>>
                                        <input type="checkbox" name="order_fields[<?php echo esc_attr( $field['name'] ); ?>]" class="order_field" <?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
                                        <span class="field_title">
                                            <?php echo esc_attr( $field['label'] ); ?>
                                            <?php if ( $field['disabled'] ) { ?>
                                            <span class="description"> -
                                                <?php
                                                    echo wp_kses_post(
                                                        sprintf(
                                                            // translators: %s is the link to the Product Fields extension.
                                                            __( 'available in %s', 'woocommerce-exporter' ),
                                                            woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=orderfields' . str_replace( '_', '', $field['name'] ) . 'link' )
                                                        )
                                                    );
                                                ?>
                                            </span>
                                            <?php } ?>
                                        </span>
                                        <?php if ( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'order' ) ) { ?>
                                            <span class="field_hover"><?php echo esc_attr( $field['hover'] ); ?></span>
                                        <?php } ?>
                                        <input type="hidden" name="order_fields_order[<?php echo esc_attr( $field['name'] ); ?>]" class="field_order" value="<?php echo esc_attr( $field['order'] ); ?>" />
                                        </label>
                                </td>
                            </tr>

                        <?php } ?>
                    </table>
                    <p class="submit">
                        <input type="submit" id="export_order" class="export_button button-primary" value="<?php esc_attr_e( 'Export Orders', 'woocommerce-exporter' ); ?>" />
                    </p>
                    <p class="description"><?php echo wp_kses_post( sprintf( __( 'Can\'t find a particular Order field in the above export list? You can export custom Order meta, Order Item meta and Order Item Product meta as fields by scrolling down to <a href="#export-orders-custom-fields">Custom Order Fields</a>, if you get stuck <a href="%s" target="_blank">get in touch</a>.', 'woocommerce-exporter' ), $troubleshooting_url ) ); ?></p>
                <?php } else { ?>
                    <p><?php esc_html_e( 'No Orders were found.', 'woocommerce-exporter' ); ?></p>
                <?php } ?>
            </div>
        </div>
        <!-- .postbox -->

        <div id="export-orders-filters" class="postbox">
            <h3 class="hndle"><?php esc_html_e( 'Order Filters', 'woocommerce-exporter' ); ?></h3>
            <div class="inside">

                <?php do_action( 'woo_ce_export_order_options_before_table' ); ?>

                <table class="form-table">
                    <?php do_action( 'woo_ce_export_order_options_table' ); ?>
                </table>

                <?php do_action( 'woo_ce_export_order_options_after_table' ); ?>

            </div>
            <!-- .inside -->

        </div>
        <!-- .postbox -->

    </div>
    <!-- #export-order -->

<?php } ?>
