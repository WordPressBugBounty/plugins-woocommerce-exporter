<?php if ( $category && $category_fields ) { ?>
    <div id="export-category" class="export-types">

        <div class="postbox">
            <h3 class="hndle">
                <?php esc_html_e( 'Category Fields', 'woocommerce-exporter' ); ?>
            </h3>
            <div class="inside">
                <?php if ( $category ) { ?>
                    <p class="description"><?php woo_ce_export_fields_summary_text( $export_type ); ?></p>
                    <p>
                        <a href="javascript:void(0)" id="category-checkall" class="checkall"><?php esc_html_e( 'Check All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="category-uncheckall" class="uncheckall"><?php esc_html_e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="category-resetsorting" class="resetsorting"><?php esc_html_e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> |
                    </p>
                    <table id="category-fields" class="ui-sortable striped">

                        <?php foreach ( $category_fields as $field ) { ?>
                            <tr id="category-<?php echo esc_attr( $field['reset'] ); ?>" data-export-type="category" data-field-name="<?php printf( '%s-%s', 'category', esc_attr( $field['name'] ) ); ?>">
                                <td>
                                    <label
                                    <?php
                                    if ( isset( $field['hover'] ) ) {
                            ?>
                            title="<?php echo esc_attr( $field['hover'] ); ?>" <?php } ?>>
                                        <input type="checkbox" name="category_fields[<?php echo esc_attr( $field['name'] ); ?>]" class="category_field" <?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
                                        <span class="field_title">
                                            <?php echo esc_attr( $field['label'] ); ?>
                                            <?php if ( $field['disabled'] ) { ?>
                                            <span class="description"> -
                                                <?php
                                                    echo wp_kses_post(
                                                        sprintf(
                                                            // translators: %s is the link to the Product Fields extension.
                                                            __( 'available in %s', 'woocommerce-exporter' ),
                                                            woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=categoryfields' . str_replace( '_', '', $field['name'] ) . 'link' )
                                                        )
                                                    );
                                                ?>
                                            </span>
                                            <?php } ?>
                                        </span>
                                        <?php if ( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'category' ) ) { ?>
                                            <span class="field_hover"><?php echo esc_attr( $field['hover'] ); ?></span>
                                        <?php } ?>
                                        <input type="hidden" name="category_fields_order[<?php echo esc_attr( $field['name'] ); ?>]" class="field_order" value="<?php echo esc_attr( $field['order'] ); ?>" />
                                        </label>
                                </td>
                            </tr>

                        <?php } ?>
                    </table>
                    <p class="submit">
                        <input type="submit" id="export_category" value="<?php esc_attr_e( 'Export Categories', 'woocommerce-exporter' ); ?> " class="export_button button-primary" />
                    </p>
                    <p class="description"><?php esc_html_e( 'Can\'t find a particular Category field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo esc_url( $troubleshooting_url ); ?>" target="_blank"><?php esc_html_e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
                <?php } else { ?>
                    <p><?php esc_html_e( 'No Categories were found.', 'woocommerce-exporter' ); ?></p>
                <?php } ?>
            </div>
            <!-- .inside -->
        </div>
        <!-- .postbox -->

        <div id="export-categories-filters" class="postbox">
            <h3 class="hndle"><?php esc_html_e( 'Category Filters', 'woocommerce-exporter' ); ?></h3>
            <div class="inside">

                <?php do_action( 'woo_ce_export_category_options_before_table' ); ?>

                <table class="form-table">
                    <?php do_action( 'woo_ce_export_category_options_table' ); ?>
                </table>

                <?php do_action( 'woo_ce_export_category_options_after_table' ); ?>

            </div>
            <!-- .inside -->
        </div>
        <!-- #export-categories-filters -->

    </div>
    <!-- #export-category -->
<?php } ?>
