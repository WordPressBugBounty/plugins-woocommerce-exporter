<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="wsed-export-progress-bar-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <?php esc_html_e( 'Your export is currently in progress.', 'woocommerce-exporter' ); ?>
                </h3>
                <p class="modal-title">
                    <?php esc_html_e( 'Please wait while we prepare your export file for download. This may take some time depending on the size of your data.', 'woocommerce-exporter' ); ?>
                <p>
            </div>
            <div class="modal-body" id="wsed-export-progress-bar-modal-body">
                <div class="progress-bar">
                    <div class="progress-value"></div>
                </div>
            </div>
            <div class="modal-footer">
                <p>
                    <small>
                        <em>
                            <?php esc_html_e( 'Do not close or refresh this page until the export has finished to ensure your download is successful.', 'woocommerce-exporter' ); ?>
                        </em>
                    </small>
                </p>
            </div>
        </div>
        <!-- Close button -->
        <span class="modal-close dashicons dashicons-no-alt"></span>
    </div>
</div>