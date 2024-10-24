<div id="poststuff" class="recent-scheduled-exports">

    <p class="pagination">
        <span class="displaying-num"><?php echo esc_html( sprintf( __( '%d items', 'woocommerce-exporter' ), $size ) ); ?></span>
        <span class="pagination-links"><?php echo wp_kses_post( $pagination_links ); ?></span>
    </p>

    <div id="recent-scheduled-exports" class="postbox">
        <h3 class="hndle"><?php esc_html_e( 'Recent Scheduled Exports' ); ?></h3>
        <div class="inside">

            <?php if ( ! empty( $recent_exports ) ) { ?>
                <ol>
                    <?php foreach ( $recent_exports as $key => $recent_export ) { ?>
                        <li id="recent-scheduled-export-<?php echo esc_attr( $key ); ?>" class="recent-scheduled-export scheduled-export-<?php echo ( ! empty( $recent_export['error'] ) ? 'error' : 'success' ); ?>">
                            <p><?php echo esc_html( $recent_export['name'] ); ?></p>
                            <?php if ( ! empty( $recent_export['post_id'] ) && get_post_status( $recent_export['post_id'] ) !== false ) { ?>
                                <p>
                                    <a href="<?php echo esc_url( wp_get_attachment_url( $recent_export['post_id'] ) ); ?>"><?php esc_html_e( 'Download', 'woocommerce-exporter' ); ?></a> |
                                    <a href="<?php echo esc_url( get_edit_post_link( $recent_export['post_id'] ) ); ?>"><?php esc_html_e( 'Export Details', 'woocommerce-exporter' ); ?></a>
                                </p>
                            <?php } ?>
                            <p>
                                <?php echo ( isset( $recent_export['post_id'] ) ? sprintf( '<a href="' . esc_url( get_edit_post_link( $recent_export['post_id'] ) ) . '">%s</a> - ', esc_html( woo_ce_format_post_title( get_the_title( $recent_export['post_id'] ) ) ) ) : '' ); ?>
                                <span title="<?php echo esc_attr( woo_ce_format_date( date( 'd/m/Y h:i:s', $recent_export['date'] ), 'd/m/y h:i:s' ) ); ?>">
                                    <?php echo esc_html( woo_ce_format_archive_date( $recent_export['post_id'], $recent_export['date'] ) ); ?>
                                </span>,
                                <?php echo ( ! empty( $recent_export['error'] ) ? esc_html_e( 'error', 'woocommerce-exporter' ) . ': <span class="error">' . esc_html( $recent_export['error'] ) . '</span>' : esc_html( woo_ce_format_archive_method( $recent_export['method'] ) . '.' ) ); ?>
                            </p>
                        </li>

                    <?php } ?>
                </ol>
                <hr />

                <p class="pagination">
                    <span class="displaying-num"><?php echo esc_html( sprintf( __( '%d items', 'woocommerce-exporter' ), $size ) ); ?></span>
                    <span class="pagination-links"><?php echo wp_kses_post( $pagination_links ); ?></span>
                </p>

                <p style="text-align:right;">
                    <a href="
                    <?php
                    echo esc_url(
                        add_query_arg(
                            array(
								'action'   => 'nuke_recent_scheduled_exports',
								'_wpnonce' => wp_create_nonce( 'woo_ce_nuke_recent_scheduled_exports' ),
                            )
                        )
                    );
                    ?>
                    " class="button action confirm-button" data-confirm="<?php esc_attr_e( 'This will permanently clear the contents of the Recent Scheduled Exports list. Are you sure you want to proceed?', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Delete All', 'woocommerce-exporter' ); ?></a>
                </p>
            <?php } else { ?>
                <?php if ( $enable_auto ) { ?>
                    <p><?php esc_html_e( 'Ready for your first scheduled export, shouldn\'t be long now.', 'woocommerce-exporter' ); ?> <strong>:)</strong></p>
                <?php } else { ?>
                    <p style="font-size:0.8em;">
                    <div class="dashicons dashicons-no"></div>&nbsp;<strong><?php esc_html_e( 'Scheduled exports are disabled', 'woocommerce-exporter' ); ?></strong></p>
                <?php } ?>
            <?php } ?>

        </div>
        <!-- .inside -->
        <br class="clear" />
    </div>
    <!-- #recent-scheduled-exports -->

</div>
<!-- #poststuff -->
