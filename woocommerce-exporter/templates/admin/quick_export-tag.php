<?php if ( $tag && $tag_fields ) { ?>
    <div id="export-tag" class="export-types">

        <div class="postbox">
            <h3 class="hndle">
                <?php esc_html_e( 'Tag Fields', 'woocommerce-exporter' ); ?>
            </h3>
            <div class="inside">
                <?php if ( $tag ) { ?>
                    <p class="description"><?php woo_ce_export_fields_summary_text( $export_type ); ?></p>
                    <p>
                        <a href="javascript:void(0)" id="tag-checkall" class="checkall"><?php esc_html_e( 'Check All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="tag-uncheckall" class="uncheckall"><?php esc_html_e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="tag-resetsorting" class="resetsorting"><?php esc_html_e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a>|
                    </p>
                    <table id="tag-fields" class="ui-sortable striped">

                        <?php foreach ( $tag_fields as $field ) { ?>
                            <tr id="tag-<?php echo esc_attr( $field['reset'] ); ?>" data-export-type="tag" data-field-name="<?php printf( '%s-%s', 'tag', esc_attr( $field['name'] ) ); ?>">
                                <td>
                                    <label
                                    <?php
                                    if ( isset( $field['hover'] ) ) {
                                    ?>
                                    title="<?php echo esc_attr( $field['hover'] ); ?>" <?php } ?>>
                                        <input type="checkbox" name="tag_fields[<?php echo esc_attr( $field['name'] ); ?>]" class="tag_field" <?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
                                        <span class="field_title">
                                            <?php echo esc_attr( $field['label'] ); ?>
                                            <?php if ( $field['disabled'] ) { ?>
                                            <span class="description"> -
                                                <?php
                                                    echo wp_kses_post(
                                                        sprintf(
                                                            // translators: %s is the link to the Product Fields extension.
                                                            __( 'available in %s', 'woocommerce-exporter' ),
                                                            woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=tagfields' . str_replace( '_', '', $field['name'] ) . 'link' )
                                                        )
                                                    );
                                                ?>
                                            </span>
                                            <?php } ?>
                                        </span>
                                        <?php if ( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'tag' ) ) { ?>
                                            <span class="field_hover"><?php echo esc_attr( $field['hover'] ); ?></span>
                                        <?php } ?>
                                        <input type="hidden" name="tag_fields_order[<?php echo esc_attr( $field['name'] ); ?>]" class="field_order" value="<?php echo esc_attr( $field['order'] ); ?>" />
                                        </label>
                                </td>
                            </tr>

                        <?php } ?>
                    </table>
                    <p class="submit">
                        <input type="submit" id="export_tag" value="<?php esc_attr_e( 'Export Tags', 'woocommerce-exporter' ); ?> " class="export_button button-primary" />
                    </p>
                    <p class="description"><?php esc_html_e( 'Can\'t find a particular Tag field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo esc_url( $troubleshooting_url ); ?>" target="_blank"><?php esc_html_e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
                <?php } else { ?>
                    <p><?php esc_html_e( 'No Tags were found.', 'woocommerce-exporter' ); ?></p>
                <?php } ?>
            </div>
            <!-- .inside -->
        </div>
        <!-- .postbox -->

        <div id="export-tags-filters" class="postbox">
            <h3 class="hndle"><?php esc_html_e( 'Product Tag Filters', 'woocommerce-exporter' ); ?></h3>
            <div class="inside">

                <?php do_action( 'woo_ce_export_tag_options_before_table' ); ?>

                <table class="form-table">
                    <?php do_action( 'woo_ce_export_tag_options_table' ); ?>
                </table>

                <?php do_action( 'woo_ce_export_tag_options_after_table' ); ?>

            </div>
            <!-- .inside -->
        </div>
        <!-- #export-tags-filters -->

    </div>
    <!-- #export-tag -->
<?php } ?>
