<ul class="subsubsub">
    <li><strong><?php esc_html_e( 'Quick Export', 'woocommerce-exporter' ); ?></strong> |</li>
    <li><a href="#export-type"><?php esc_html_e( 'Export Types', 'woocommerce-exporter' ); ?></a> |</li>
    <li><a href="#export-options"><?php esc_html_e( 'Export Options', 'woocommerce-exporter' ); ?></a></li>
    <?php do_action( 'woo_ce_export_quicklinks' ); ?>
</ul>
<!-- .subsubsub -->
<br class="clear" />

<div id="poststuff">
    <form method="post" action="
    <?php
    echo esc_url(
        add_query_arg(
            array(
				'failed'  => null,
				'empty'   => null,
				'message' => null,
            )
        )
    );
?>
" id="postform">
        <?php do_action( 'woo_ce_export_before_options' ); ?>

        <div class="export-types">
            <div class="postbox">
                <h3 class="hndle"><?php esc_html_e( 'Loading...', 'woocommerce-exporter' ); ?></h3>
                <div class="inside">
                    <p><strong><?php esc_html_e( 'The Quick Export screen is loading elements in the background.', 'woocommerce-exporter' ); ?></strong></p>
                    <p>
                    <?php
                    printf(
                        /* translators: %1$s: opening link tag to the knowledge base, %2$s: opening link tag to the knowledge base, %3$s: opening link tag to the support page, %4$s: closing link tag. */
                        wp_kses_post( __( 'If this notice does not disappear once the browser has finished loading then something has gone wrong. This could be due to a %1$sJavaScript error%4$s or %2$smemory/timeout limitation%4$s whilst loading the Export screen, please open a %3$sSupport ticket%4$s with us to look at this with you. :)', 'woocommerce-exporter' ) ),
                        '<a href="' . esc_url( woo_ce_get_kb_url() ) . '" target="_blank">',
                        '<a href="' . esc_url( woo_ce_get_kb_url() ) . '" target="_blank">',
                        '<a href="' . esc_url( 'https://visser.com.au/premium-support/' ) . '" target="_blank">',
                        '</a>'
                    );
                    ?>
                    </p>
                </div>
            </div>
            <!-- .postbox -->
        </div>

        <?php
        foreach ( $export_types as $export_type ) {
            $template = sprintf( 'quick_export-%s.php', $export_type );
            if ( file_exists( WOO_CE_PATH . 'templates/admin/' . $template ) ) {

                include_once WOO_CE_PATH . 'templates/admin/' . $template;
            } else {

                // Skip for custom export types extended by Plugin/Theme developers.
                if ( ! in_array( $export_type, $default_export_types ) ) {
                    continue;
                }
                // translators: %1$s is the template file name, %2$s is the path to the template file.
                $message = sprintf( __( 'We couldn\'t load the template file <code>%1$s</code> within <code>%2$s</code>, this file should be present.', 'woocommerce-exporter' ), $template, WOO_CE_PATH . 'templates/admin/...' );
        ?>
                <p><strong><?php echo wp_kses_post( $message ); ?></strong></p>
                <p><?php esc_html_e( 'You can see this error for one of a few common reasons', 'woocommerce-exporter' ); ?>:</p>
                <ul class="ul-disc">
                    <li><?php esc_html_e( 'WordPress was unable to create this file when the Plugin was installed or updated', 'woocommerce-exporter' ); ?></li>
                    <li><?php esc_html_e( 'The Plugin files have been recently changed and there has been a file conflict', 'woocommerce-exporter' ); ?></li>
                    <li><?php esc_html_e( 'The Plugin file has been locked and cannot be opened by WordPress', 'woocommerce-exporter' ); ?></li>
                </ul>
                <p><?php esc_html_e( 'Jump onto our website and download a fresh copy of this Plugin as it might be enough to fix this issue. If this persists get in touch with us.', 'woocommerce-exporter' ); ?></p>
        <?php

            }
        }
        ?>

        <?php do_action( 'woo_ce_export_after_options' ); ?>

        <input type="hidden" name="action" value="export" />
        <?php wp_nonce_field( 'manual_export', 'woo_ce_export' ); ?>

    </form>

    <?php do_action( 'woo_ce_export_after_form' ); ?>

</div>
<!-- #poststuff -->
