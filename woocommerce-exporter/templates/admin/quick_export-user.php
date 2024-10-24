<?php if ( $user && $user_fields ) { ?>
    <div id="export-user" class="export-types">

        <div class="postbox">
            <h3 class="hndle">
                <?php esc_html_e( 'User Fields', 'woocommerce-exporter' ); ?>
            </h3>
            <div class="inside">
                <?php if ( $user ) { ?>
                    <p class="description"><?php woo_ce_export_fields_summary_text( $export_type ); ?></p>
                    <p>
                        <a href="javascript:void(0)" id="user-checkall" class="checkall"><?php esc_html_e( 'Check All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="user-uncheckall" class="uncheckall"><?php esc_html_e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> |
                        <a href="javascript:void(0)" id="user-resetsorting" class="resetsorting"><?php esc_html_e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> |
                    </p>
                    <table id="user-fields" class="ui-sortable striped">

                        <?php foreach ( $user_fields as $field ) { ?>
                            <tr id="user-<?php echo esc_attr( $field['reset'] ); ?>" data-export-type="user" data-field-name="<?php printf( '%s-%s', 'user', esc_attr( $field['name'] ) ); ?>">
                                <td>
                                    <label
                                    <?php
                                    if ( isset( $field['hover'] ) ) {
                                    ?>
                                    title="<?php echo esc_attr( $field['hover'] ); ?>" <?php } ?>>
                                        <input type="checkbox" name="user_fields[<?php echo esc_attr( $field['name'] ); ?>]" class="user_field" <?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
                                        <span class="field_title">
                                            <?php echo esc_attr( $field['label'] ); ?>
                                            <?php if ( $field['disabled'] ) { ?>
                                            <span class="description"> -
                                                <?php
                                                    echo wp_kses_post(
                                                        sprintf(
                                                            // translators: %s is the link to the Product Fields extension.
                                                            __( 'available in %s', 'woocommerce-exporter' ),
                                                            woo_ce_upsell_link( '?utm_source=wse&utm_medium=export&utm_campaign=userfields' . str_replace( '_', '', $field['name'] ) . 'link' )
                                                        )
                                                    );
                                                ?>
                                            </span>
                                            <?php } ?>
                                        </span>
                                        <?php if ( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'user' ) ) { ?>
                                            <span class="field_hover"><?php echo esc_attr( $field['hover'] ); ?></span>
                                        <?php } ?>
                                        <input type="hidden" name="user_fields_order[<?php echo esc_attr( $field['name'] ); ?>]" class="field_order" value="<?php echo esc_attr( $field['order'] ); ?>" />
                                        </label>
                                </td>
                            </tr>

                        <?php } ?>
                    </table>
                    <p class="submit">
                        <input type="submit" id="export_user" class="export_button button-primary" value="<?php esc_attr_e( 'Export Users', 'woocommerce-exporter' ); ?>" />
                    </p>
                    <p class="description"><?php echo wp_kses_post( sprintf( __( 'Can\'t find a particular User field in the above export list? You can export custom User meta as fields by scrolling down to <a href="#export-users-custom-fields">Custom User Fields</a>, if you get stuck <a href="%s" target="_blank">get in touch</a>.', 'woocommerce-exporter' ), $troubleshooting_url ) ); ?></p>
                <?php } else { ?>
                    <p><?php esc_html_e( 'No Users were found.', 'woocommerce-exporter' ); ?></p>
                <?php } ?>
            </div>
            <!-- .inside -->
        </div>
        <!-- .postbox -->

        <div id="export-users-filters" class="postbox">
            <h3 class="hndle"><?php esc_html_e( 'User Filters', 'woocommerce-exporter' ); ?></h3>
            <div class="inside">

                <?php do_action( 'woo_ce_export_user_options_before_table' ); ?>

                <table class="form-table">
                    <?php do_action( 'woo_ce_export_user_options_table' ); ?>
                </table>

                <?php do_action( 'woo_ce_export_user_options_after_table' ); ?>

            </div>
            <!-- .inside -->
        </div>
        <!-- .postbox -->

    </div>
    <!-- #export-user -->

<?php } ?>
