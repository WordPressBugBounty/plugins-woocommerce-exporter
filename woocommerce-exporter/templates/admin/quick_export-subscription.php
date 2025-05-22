<?php if ( $subscription && $subscription_fields ) { ?>
    <div id="export-subscription" class="export-types">

        <div class="postbox">
            <h3 class="hndle">
                <?php esc_html_e( 'Subscription Fields', 'woocommerce-exporter' ); ?>
            </h3>
            <div class="inside">
                <?php if ( $subscription ) { ?>
                    <p class="description"><?php woo_ce_export_fields_summary_text( $export_type ); ?></p>
                    <p>
                        <a href="javascript:void(0)" id="subscription-checkall" class="checkall"><?php esc_html_e( 'Check All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="subscription-uncheckall" class="uncheckall"><?php esc_html_e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="subscription-resetsorting" class="resetsorting"><?php esc_html_e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> |
                    </p>
                    <table id="subscription-fields" class="ui-sortable striped">

                        <?php foreach ( $subscription_fields as $field ) { ?>
                            <tr id="subscription-<?php echo esc_attr( $field['reset'] ); ?>" data-export-type="subscription" data-field-name="<?php printf( '%s-%s', 'subscription', esc_attr( $field['name'] ) ); ?>">
                                <td>
                                    <label
                                    <?php
                                    if ( isset( $field['hover'] ) ) {
                                    ?>
                                    title="<?php echo esc_attr( $field['hover'] ); ?>" <?php } ?>>
                                        <input type="checkbox" name="subscription_fields[<?php echo esc_attr( $field['name'] ); ?>]" class="subscription_field" disabled="disabled" />
                                        <span class="field_title">
                                            <?php echo esc_attr( $field['label'] ); ?>
                                            <?php if ( $field['disabled'] ) { ?>
                                            <span class="description"> -
                                                <?php
                                                    echo wp_kses_post(
                                                        sprintf(
                                                            // translators: %s is the link to the Product Fields extension.
                                                            __( 'available in %s', 'woocommerce-exporter' ),
                                                            woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=subscriptionfields' . str_replace( '_', '', $field['name'] ) . 'link' )
                                                        )
                                                    );
                                                ?>
                                            </span>
                                            <?php } ?>
                                        </span>
                                        <?php if ( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'subscription' ) ) { ?>
                                            <span class="field_hover"><?php echo esc_attr( $field['hover'] ); ?></span>
                                        <?php } ?>
                                        <input type="hidden" name="subscription_fields_order[<?php echo esc_attr( $field['name'] ); ?>]" class="field_order" value="<?php echo esc_attr( $field['order'] ); ?>" />
                                        </label>
                                </td>
                            </tr>

                        <?php } ?>
                    </table>
                    <p class="submit">
                        <input type="button" class="button button-disabled" value="<?php esc_attr_e( 'Export Subscriptions', 'woocommerce-exporter' ); ?>" />
                    </p>
                    <p class="description"><?php esc_html_e( 'Can\'t find a particular Subscription field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo esc_url( $troubleshooting_url ); ?>" target="_blank"><?php esc_html_e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
                <?php } else { ?>
                    <p><?php esc_html_e( 'No Subscriptions were found.', 'woocommerce-exporter' ); ?></p>
                <?php } ?>
            </div>
            <!-- .inside -->
        </div>
        <!-- .postbox -->

        <div id="export-subscriptions-filters" class="postbox">
            <h3 class="hndle"><?php esc_html_e( 'Subscription Filters', 'woocommerce-exporter' ); ?></h3>
            <div class="inside">

                <?php do_action( 'woo_ce_export_subscription_options_before_table' ); ?>

                <table class="form-table">
                    <?php do_action( 'woo_ce_export_subscription_options_table' ); ?>
                </table>

                <?php do_action( 'woo_ce_export_subscription_options_after_table' ); ?>

            </div>
            <!-- .inside -->
        </div>
        <!-- .postbox -->

    </div>
    <!-- #export-subscription -->

<?php } ?>
