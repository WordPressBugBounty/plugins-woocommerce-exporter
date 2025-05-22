<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wse-upsell-overlay">
    <div class="wse-upsell-overlay-content">
        <h2>
            <?php echo ( ! empty( $title ) ) ? esc_html( $title ) : esc_html__( 'Upgrade to Store Exporter Deluxe', 'woocommerce-exporter' ); ?>
        </h2>
        <p><?php echo ( ! empty( $desc ) ) ? esc_html( $desc ) : esc_html__( 'Unlock advanced features and premium support with Store Exporter Deluxe.', 'woocommerce-exporter' ); ?></p>
        <div class="wse-upsell-button">
            <a href="https://visser.com.au/plugins/woocommerce-export/?utm_source=wse&utm_medium=settings&utm_campaign=overlaycta<?php echo esc_attr( $id ) ?>"  class="button button-primary" target="_blank">
                <?php esc_html_e( 'Upgrade to Deluxe and Unlock feature', 'woocommerce-exporter' ); ?>
            </a>
        </div>
        <a href="https://visser.com.au/plugins/woocommerce-export/?utm_source=wse&utm_medium=settings&utm_campaign=overlaylink<?php echo esc_attr( $id ) ?>" class="wse-upsell-footer-link">
            <?php esc_html_e( 'Learn more about all features', 'woocommerce-exporter' ); ?>
        </a>
    </div>
</div>
