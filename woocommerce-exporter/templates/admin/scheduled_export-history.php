<?php if ( ! empty( $recent_exports ) ) { ?>
    <ol>
        <?php foreach ( $recent_exports as $key => $recent_export ) { ?>
            <li id="recent-scheduled-export-<?php echo esc_attr( $key ); ?>" class="recent-scheduled-export scheduled-export-<?php echo ( ! empty( $recent_export['error'] ) ? 'error' : 'success' ); ?>">
                <p><?php echo esc_html( $recent_export['name'] ); ?>
                    <?php if ( ! empty( $recent_export['post_id'] ) && get_post_status( $recent_export['post_id'] ) !== false ) { ?>
                        -
                        <a href="<?php echo esc_url( wp_get_attachment_url( $recent_export['post_id'] ) ); ?>"><?php esc_html_e( 'Download', 'woocommerce-exporter' ); ?></a> |
                        <a href="<?php echo esc_url( get_edit_post_link( $recent_export['post_id'] ) ); ?>"><?php esc_html_e( 'Export Details', 'woocommerce-exporter' ); ?></a>
                    <?php } ?>
                </p>
                <p><span title="<?php echo esc_attr( date( 'd/m/Y h:i:s', $recent_export['date'] ) ); ?>"><?php echo esc_html( woo_ce_format_archive_date( $recent_export['post_id'], $recent_export['date'] ) ); ?></span>, <?php echo ( ! empty( $recent_export['error'] ) ? esc_html__( 'error', 'woocommerce-exporter' ) . ': <span class="error">' . esc_html( $recent_export['error'] ) . '</span>' : esc_html( woo_ce_format_archive_method( $recent_export['method'] ) ) . '.' ); ?></p>
            </li>

        <?php } ?>
    </ol>
<?php } else { ?>
    <?php if ( $enable_auto ) { ?>
        <p><?php esc_html_e( 'Ready for your first scheduled export, shouldn\'t be long now.', 'woocommerce-exporter' ); ?> <strong>:)</strong></p>
    <?php } else { ?>
        <p style="font-size:0.8em;">
        <div class="dashicons dashicons-no"></div>&nbsp;<strong><?php esc_html_e( 'Scheduled exports are disabled', 'woocommerce-exporter' ); ?></strong></p>
    <?php } ?>
<?php } ?>
