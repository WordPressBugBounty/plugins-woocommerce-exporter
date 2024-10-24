<?php if ( $product && $product_fields ) { ?>
    <div id="export-product" class="export-types">

        <div class="postbox">
            <h3 class="hndle">
                <?php esc_html_e( 'Product Fields', 'woocommerce-exporter' ); ?>
            </h3>
            <div class="inside">
                <?php if ( $product ) { ?>
                    <p class="description"><?php woo_ce_export_fields_summary_text( $export_type ); ?></p>
                    <p>
                        <a href="javascript:void(0)" id="product-checkall" class="checkall"><?php esc_html_e( 'Check All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="product-uncheckall" class="uncheckall"><?php esc_html_e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="product-resetsorting" class="resetsorting"><?php esc_html_e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> |
                    </p>
                    <table id="product-fields" class="ui-sortable striped">

                        <?php foreach ( $product_fields as $field ) { ?>
                            <tr id="product-<?php echo esc_attr( $field['reset'] ); ?>" data-export-type="product" data-field-name="<?php printf( '%s-%s', 'product', esc_attr( $field['name'] ) ); ?>">
                                <td>
                                    <label
                                    <?php
                                    if ( isset( $field['hover'] ) ) {
                                    ?>
                                    title="<?php echo esc_attr( $field['hover'] ); ?>" <?php } ?>>
                                        <input type="checkbox" name="product_fields[<?php echo esc_attr( $field['name'] ); ?>]" class="product_field" <?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
                                        <span class="field_title">
                                            <?php echo esc_attr( $field['label'] ); ?>
                                            <?php if ( $field['disabled'] ) { ?>
                                            <span class="description"> -
                                                <?php
                                                    echo wp_kses_post(
                                                        sprintf(
                                                            // translators: %s is the link to the Product Fields extension.
                                                            __( 'available in %s', 'woocommerce-exporter' ),
                                                            woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=productfields' . str_replace( '_', '', $field['name'] ) . 'link' )
                                                        )
                                                    );
                                                ?>
                                            </span>
                                            <?php } ?>
                                        </span>
                                        <?php if ( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'product' ) ) { ?>
                                            <span class="field_hover"><?php echo esc_attr( $field['hover'] ); ?></span>
                                        <?php } ?>
                                        <input type="hidden" name="product_fields_order[<?php echo esc_attr( $field['name'] ); ?>]" class="field_order" value="<?php echo esc_attr( $field['order'] ); ?>" />
                                        </label>
                                </td>
                            </tr>

                        <?php } ?>
                    </table>
                    <p class="submit">
                        <input type="submit" id="export_product" value="<?php esc_attr_e( 'Export Products', 'woocommerce-exporter' ); ?> " class="export_button button-primary" />
                    </p>
                    <p class="description"><?php echo wp_kses_post( sprintf( __( 'Can\'t find a particular Product field in the above export list? You can export custom Product meta and custom Attributes as fields by scrolling down to <a href="#export-products-custom-fields">Custom Product Fields</a>, if you get stuck <a href="%s" target="_blank">get in touch</a>.', 'woocommerce-exporter' ), $troubleshooting_url ) ); ?></p>
                <?php } else { ?>
                    <p><?php esc_html_e( 'No Products were found.', 'woocommerce-exporter' ); ?></p>
                <?php } ?>
            </div>
        </div>
        <!-- .postbox -->

        <div id="export-products-filters" class="postbox">
            <h3 class="hndle"><?php esc_html_e( 'Product Filters', 'woocommerce-exporter' ); ?></h3>
            <div class="inside">

                <?php do_action( 'woo_ce_export_product_options_before_table' ); ?>

                <table class="form-table">
                    <?php do_action( 'woo_ce_export_product_options_table' ); ?>
                </table>

                <?php do_action( 'woo_ce_export_product_options_after_table' ); ?>

            </div>
            <!-- .inside -->

        </div>
        <!-- .postbox -->

    </div>
    <!-- #export-product -->

<?php } ?>
