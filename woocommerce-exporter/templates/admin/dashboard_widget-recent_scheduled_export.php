<?php if ( ! empty( $recent_exports ) ) { ?>
<ol>
    <?php foreach ( $recent_exports as $recent_export ) { ?>
    <li>
        <p>
            <?php echo esc_html( $recent_export['name'] ); ?>
            <?php
            if ( ! empty( $recent_export['post_id'] ) && get_post_status( $recent_export['post_id'] ) !== false ) {
            ?>
            <a href="<?php echo esc_url( get_edit_post_link( $recent_export['post_id'] ) ); ?>" title="<?php esc_html_e( 'Open Media details', 'woocommerce-exporter' ); ?>">#</a><?php } ?>
        </p>
        <p class="meta">
        <?php if ( current_user_can( $user_capability ) ) { ?>
            <?php echo ( isset( $recent_export['post_id'] ) ? wp_kses_post( sprintf( '<a href="' . get_edit_post_link( $recent_export['post_id'] ) . '">%s</a> - ', woo_ce_format_post_title( get_the_title( $recent_export['post_id'] ) ) ) ) : '' ); ?>
        <?php } else { ?>
            <?php echo ( isset( $recent_export['post_id'] ) ? esc_html( woo_ce_format_post_title( get_the_title( $recent_export['post_id'] ) ) ) . ' - ' : '' ); ?>
        <?php } ?>
            <span title="<?php echo esc_attr( date( 'd/m/Y h:i:s', $recent_export['date'] ) ); ?>"><?php echo esc_html( woo_ce_format_archive_date( $recent_export['post_id'], $recent_export['date'] ) ); ?></span>,
            <?php echo ( ! empty( $recent_export['error'] ) ? esc_html__( 'error', 'woocommerce-exporter' ) . ': <span class="error">' . esc_html( $recent_export['error'] ) . '</span>' : esc_html( woo_ce_format_archive_method( $recent_export['method'] ) ) . '.' ); ?>
        </p>
    </li>
    <?php } ?>
</ol>
<?php } else { ?>
    <?php if ( $enable_auto == 1 ) { ?>
<p><?php esc_html_e( 'Ready for your first scheduled export, shouldn\'t be long now.', 'woocommerce-exporter' ); ?>  <strong>:)</strong></p>
    <?php } else { ?>
<p style="font-size:0.8em;"><div class="dashicons dashicons-no"></div>&nbsp;<strong><?php esc_html_e( 'Scheduled exports are disabled', 'woocommerce-exporter' ); ?></strong></p>
    <?php } ?>
<?php } ?>
<?php if ( current_user_can( $user_capability ) ) { ?>
<p style="text-align:right;"><a href="
<?php
echo esc_url(
    add_query_arg(
        array(
			'page' => 'woo_ce',
			'tab'  => 'archive',
        ),
        'admin.php'
    )
);
?>
"><?php esc_html_e( 'View all archived exports', 'woocommerce-exporter' ); ?></a></p>
<?php } ?>
