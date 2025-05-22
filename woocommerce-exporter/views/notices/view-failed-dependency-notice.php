<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<style type="text/css">
    .wse-failed-dependency-notice p {
        max-width: 100%;
    }

    .wse-failed-dependency-notice p:after {
        content: '';
        display: table;
        clear: both;
    }

    .wse-failed-dependency-notice .heading {
        display: flex;
        align-items: center;
    }

    .wse-failed-dependency-notice .heading img {
        float: left;
        margin-right: 15px;
        max-width: 120px;
        width: 100%;
        height: auto;
    }

    .wse-failed-dependency-notice .heading span {
        display: inline-flex;
        margin-top: 6px;
        font-size: 16px;
        font-weight: bold;
        text-transform: capitalize;
        color: #ce1508;
        letter-spacing: -0.2px;
        font-family: "Lato", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }

    .wse-failed-dependency-notice .heading span:before {
        content: "\f534";
        font-family: dashicons;
        margin-right: 4px;
    }

    .wse-failed-dependency-notice .action-wrap {
        margin-bottom: 15px;
    }

    .wse-failed-dependency-notice .action-wrap .action-button {
        display: inline-block;
        padding: 8px 23px;
        margin-right: 10px;
        background: #C6CD2E;
        font-weight: bold;
        font-size: 16px;
        text-decoration: none;
        color: #000000;
    }

    .wse-failed-dependency-notice .action-wrap .action-button.disabled {
        opacity: 0.7 !important;
        pointer-events: none;
    }

    .wse-failed-dependency-notice .action-wrap .action-button.gray {
        background: #cccccc;
    }

    .wse-failed-dependency-notice .action-wrap .action-button:hover {
        opacity: 0.8;
    }

    .wse-failed-dependency-notice .action-wrap span {
        color: #035E6B;
    }
</style>
<div class="notice notice-error wse-failed-dependency-notice">
    <p class="heading">
        <img src="<?php echo esc_url( WOO_CE_IMAGES_URL . 'logo.png' ); ?>" alt="Store Exporter for WooCommerce" />
        <span><?php esc_html_e( 'Action required', 'woocommerce-exporter' ); ?></span>
    </p>
    <?php echo wp_kses_post( $message ); ?>
    <?php echo wp_kses_post( $failed_dependencies ); ?>
</div>
