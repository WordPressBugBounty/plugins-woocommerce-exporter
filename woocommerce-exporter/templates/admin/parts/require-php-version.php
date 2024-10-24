<?php
/**
 * Markup for displaying required PHP version to run the plugin.
 *
 * @package VisserLabs\WSE
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="error" id="message">
    <p>
        <?php
        printf( /* translators: %s: current server PHP version */
            esc_html__(
                'WooCommerce Store Exporter plugin requires at least PHP 7.4 to work properly. Your server is currently using PHP %s.',
                'woocommerce-exporter'
            ),
            PHP_VERSION
        );
        ?>
    </p>
</div>
