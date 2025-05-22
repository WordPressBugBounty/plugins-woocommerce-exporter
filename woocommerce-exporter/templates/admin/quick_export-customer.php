<?php if ( $customer && $customer_fields ) { ?>
    <div id="export-customer" class="export-types">

        <div class="postbox">
            <h3 class="hndle">
                <?php esc_html_e( 'Customer Fields', 'woocommerce-exporter' ); ?>
            </h3>
            <div class="inside">
                <?php if ( $customer ) { ?>
                    <p class="description"><?php woo_ce_export_fields_summary_text( $export_type ); ?></p>
                    <p>
                        <a href="javascript:void(0)" id="customer-checkall" class="checkall"><?php esc_html_e( 'Check All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="customer-uncheckall" class="uncheckall"><?php esc_html_e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="customer-resetsorting" class="resetsorting"><?php esc_html_e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> |
                    </p>
                    <table id="customer-fields" class="ui-sortable striped">

                        <?php foreach ( $customer_fields as $field ) { ?>
                            <tr id="customer-<?php echo esc_attr( $field['reset'] ); ?>" data-export-type="customer" data-field-name="<?php printf( '%s-%s', 'customer', esc_attr( $field['name'] ) ); ?>">
                                <td>
                                    <label
                                    <?php
                                    if ( isset( $field['hover'] ) ) {
                                ?>
                                title="<?php echo esc_attr( $field['hover'] ); ?>" <?php } ?>>
                                        <input type="checkbox" name="customer_fields[<?php echo esc_attr( $field['name'] ); ?>]" class="customer_field" disabled="disabled" />
                                        <span class="field_title">
                                            <?php echo esc_attr( $field['label'] ); ?>
                                            <?php if ( $field['disabled'] ) { ?>
                                            <span class="description"> -
                                                <?php
                                                    echo wp_kses_post(
                                                        sprintf(
                                                            // translators: %s is the link to the Product Fields extension.
                                                            __( 'available in %s', 'woocommerce-exporter' ),
                                                            woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=customerfields' . str_replace( '_', '', $field['name'] ) . 'link' )
                                                        )
                                                    );
                                                ?>
                                            </span>
                                            <?php } ?>
                                        </span>
                                        <?php if ( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'customer' ) ) { ?>
                                            <span class="field_hover"><?php echo esc_attr( $field['hover'] ); ?></span>
                                        <?php } ?>
                                        <input type="hidden" name="customer_fields_order[<?php echo esc_attr( $field['name'] ); ?>]" class="field_order" value="<?php echo esc_attr( $field['order'] ); ?>" />
                                        </label>
                                </td>
                            </tr>

                        <?php } ?>
                    </table>
                    <p class="submit">
                    <input type="button" class="button button-disabled" value="<?php esc_attr_e( 'Export Customers', 'woocommerce-exporter' ); ?>" />
                    </p>
                    <p class="description"><?php echo wp_kses_post( sprintf( __( 'Can\'t find a particular Customer field in the above export list? You can export custom Customer meta as fields by scrolling down to <a href="#export-customers-custom-fields">Custom Customer Fields</a>, if you get stuck <a href="%s" target="_blank">get in touch</a>.', 'woocommerce-exporter' ), $troubleshooting_url ) ); ?></p>
                <?php } else { ?>
                    <p><?php esc_html_e( 'No Customers were found.', 'woocommerce-exporter' ); ?></p>
                <?php } ?>
            </div>
            <!-- .inside -->
        </div>
        <!-- .postbox -->

        <div id="export-customers-filters" class="postbox">
            <h3 class="hndle"><?php esc_html_e( 'Customer Filters', 'woocommerce-exporter' ); ?></h3>
            <div class="inside">

                <?php do_action( 'woo_ce_export_customer_options_before_table' ); ?>

                <table class="form-table">
                    <?php do_action( 'woo_ce_export_customer_options_table' ); ?>
                </table>

                <?php do_action( 'woo_ce_export_customer_options_after_table' ); ?>

            </div>
            <!-- .inside -->
        </div>
        <!-- .postbox -->

    </div>
    <!-- #export-customer -->

<?php } ?>
