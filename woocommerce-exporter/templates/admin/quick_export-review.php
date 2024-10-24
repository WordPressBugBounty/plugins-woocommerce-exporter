<?php if ( $review && $review_fields ) { ?>
    <div id="export-review" class="export-types">

        <div class="postbox">
            <h3 class="hndle">
                <?php esc_html_e( 'Review Fields', 'woocommerce-exporter' ); ?>
            </h3>
            <div class="inside">
                <?php if ( $review ) { ?>
                    <p class="description"><?php woo_ce_export_fields_summary_text( $export_type ); ?></p>
                    <p>
                        <a href="javascript:void(0)" id="review-checkall" class="checkall"><?php esc_html_e( 'Check All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="review-uncheckall" class="uncheckall"><?php esc_html_e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="review-resetsorting" class="resetsorting"><?php esc_html_e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> |
                    </p>
                    <table id="review-fields" class="ui-sortable striped">

                        <?php foreach ( $review_fields as $field ) { ?>
                            <tr id="review-<?php echo esc_attr( $field['reset'] ); ?>" data-export-type="review" data-field-name="<?php printf( '%s-%s', 'review', esc_attr( $field['name'] ) ); ?>">
                                <td>
                                    <label
                                    <?php
                                    if ( isset( $field['hover'] ) ) {
                                    ?>
                                    title="<?php echo esc_attr( $field['hover'] ); ?>" <?php } ?>>
                                        <input type="checkbox" name="review_fields[<?php echo esc_attr( $field['name'] ); ?>]" class="review_field" disabled="disabled" />
                                        <span class="field_title">
                                            <?php echo esc_attr( $field['label'] ); ?>
                                            <?php if ( $field['disabled'] ) { ?>
                                            <span class="description"> -
                                                <?php
                                                    echo wp_kses_post(
                                                        sprintf(
                                                            // translators: %s is the link to the Product Fields extension.
                                                            __( 'available in %s', 'woocommerce-exporter' ),
                                                            woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=reviewfields' . str_replace( '_', '', $field['name'] ) . 'link' )
                                                        )
                                                    );
                                                ?>
                                            </span>
                                            <?php } ?>
                                        </span>
                                        <?php if ( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'review' ) ) { ?>
                                            <span class="field_hover"><?php echo esc_attr( $field['hover'] ); ?></span>
                                        <?php } ?>
                                        <input type="hidden" name="review_fields_order[<?php echo esc_attr( $field['name'] ); ?>]" class="field_order" value="<?php echo esc_attr( $field['order'] ); ?>" />
                                        </label>
                                </td>
                            </tr>

                        <?php } ?>
                    </table>
                    <p class="submit">
                        <input type="button" class="button button-disabled" value="<?php esc_attr_e( 'Export Reviews', 'woocommerce-exporter' ); ?> " class="export_button button-primary" />
                    </p>
                    <p class="description"><?php esc_html_e( 'Can\'t find a particular Review field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo esc_url( $troubleshooting_url ); ?>" target="_blank"><?php esc_html_e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
                <?php } else { ?>
                    <p><?php esc_html_e( 'No Reviews were found.', 'woocommerce-exporter' ); ?></p>
                <?php } ?>
            </div>
        </div>
        <!-- .postbox -->

        <div id="export-reviews-filters" class="postbox">
            <h3 class="hndle"><?php esc_html_e( 'Review Filters', 'woocommerce-exporter' ); ?></h3>
            <div class="inside">

                <?php do_action( 'woo_ce_export_review_options_before_table' ); ?>

                <table class="form-table">
                    <?php do_action( 'woo_ce_export_review_options_table' ); ?>
                </table>

                <?php do_action( 'woo_ce_export_review_options_after_table' ); ?>

            </div>
            <!-- .inside -->

        </div>
        <!-- .postbox -->

    </div>
    <!-- #export-review -->

<?php } ?>
