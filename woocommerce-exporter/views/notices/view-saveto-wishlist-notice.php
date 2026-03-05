<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly.
?>

<div id="woo-ce-saveto-notice" class="woo-ce-saveto-notice">
    <style>
        .woo-ce-saveto-notice {
            display: flex;
            background: #fff;
            border: 1px solid #c3c4c7;
            border-left: 4px solid #6E3FF3;
            margin: 15px 0;
            padding: 0;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }
        .woo-ce-saveto-notice-sidebar {
            background: #F3F0FF;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 25px;
            min-width: 80px;
        }
        .woo-ce-saveto-notice-sidebar img {
            width: 110px;
            height: auto;
        }
        .woo-ce-saveto-notice-content {
            padding: 15px 40px 15px 20px;
            flex: 1;
        }
        .woo-ce-saveto-notice-content h3 {
            margin: 0 0 8px;
            font-size: 14px;
            font-weight: 600;
            color: #1d2327;
        }
        .woo-ce-saveto-notice-content p {
            margin: 0 0 12px;
            font-size: 13px;
            color: #50575e;
            line-height: 1.5;
        }
        .woo-ce-saveto-notice-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .woo-ce-saveto-notice-actions .button-primary {
            background: #6E3FF3;
            border-color: #6E3FF3;
            color: #fff;
            padding: 4px 16px;
            font-size: 13px;
            line-height: 1.8;
            height: auto;
            text-decoration: none;
        }
        .woo-ce-saveto-notice-actions .button-primary:hover,
        .woo-ce-saveto-notice-actions .button-primary:focus {
            background: #5B2FD9;
            border-color: #5B2FD9;
            color: #fff;
        }
        .woo-ce-saveto-notice-actions .button-primary:disabled {
            background: #6E3FF3 !important;
            border-color: #6E3FF3 !important;
            color: #fff !important;
            opacity: 0.7;
        }
        .woo-ce-saveto-notice-actions a.woo-ce-saveto-learn-more {
            color: #6E3FF3;
            text-decoration: none;
            font-size: 13px;
        }
        .woo-ce-saveto-notice-actions a.woo-ce-saveto-learn-more:hover {
            text-decoration: underline;
        }
        .woo-ce-saveto-notice-dismiss {
            position: absolute;
            top: 8px;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            color: #787c82;
            font-size: 16px;
            line-height: 1;
            padding: 4px;
        }
        .woo-ce-saveto-notice-dismiss:hover {
            color: #d63638;
        }
        .woocommerce-layout__notice-list-hide ~ .woo-ce-saveto-notice {
            margin-left: 20px;
            margin-right: 20px;
        }
    </style>

    <div class="woo-ce-saveto-notice-sidebar">
        <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'SaveTo Wishlist', 'woocommerce-exporter' ); ?>" />
    </div>

    <div class="woo-ce-saveto-notice-content">
        <h3><?php esc_html_e( 'Boost Your Sales with SaveTo Wishlist', 'woocommerce-exporter' ); ?></h3>
        <p>
            <?php
            esc_html_e(
                'Let customers save their favorite products and come back to buy later. Our sister plugin, SaveTo Wishlist, adds beautiful wishlist functionality to your WooCommerce store — helping you increase conversions and customer engagement.',
                'woocommerce-exporter'
            );
            ?>
        </p>
        <div class="woo-ce-saveto-notice-actions">
            <button type="button" class="button button-primary woo-ce-saveto-install-btn" data-nonce="<?php echo esc_attr( $install_nonce ); ?>">
                <?php echo esc_html( $button_text ); ?>
            </button>
            <a href="<?php echo esc_url( 'https://savetowishlist.com/?utm_source=wse&utm_medium=admin_notice&utm_campaign=saveto_cross_promo' ); ?>" class="woo-ce-saveto-learn-more" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e( 'Learn More', 'woocommerce-exporter' ); ?>
            </a>
        </div>
    </div>

    <button type="button" class="woo-ce-saveto-notice-dismiss" data-nonce="<?php echo esc_attr( $dismiss_nonce ); ?>" title="<?php esc_attr_e( 'Dismiss', 'woocommerce-exporter' ); ?>">
        <span class="dashicons dashicons-no-alt"></span>
    </button>

    <script>
    jQuery(document).ready(function($) {
        // Dismiss notice.
        $('#woo-ce-saveto-notice .woo-ce-saveto-notice-dismiss').on('click', function() {
            var $notice = $('#woo-ce-saveto-notice');
            $.post(ajaxurl, {
                action: 'woo_ce_dismiss_saveto_notice',
                nonce: $(this).data('nonce')
            });
            $notice.fadeOut(300, function() {
                $notice.remove();
            });
        });

        // Install & Activate.
        $('#woo-ce-saveto-notice .woo-ce-saveto-install-btn').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true).text('<?php echo esc_js( __( 'Installing...', 'woocommerce-exporter' ) ); ?>');

            $.post(ajaxurl, {
                action: 'woo_ce_install_plugin',
                nonce: $btn.data('nonce'),
                plugin_slug: 'saveto-wishlist-lite-for-woocommerce'
            })
            .done(function(response) {
                if (response.success) {
                    // Dismiss the notice so it won't show again.
                    $.post(ajaxurl, {
                        action: 'woo_ce_dismiss_saveto_notice',
                        nonce: $('#woo-ce-saveto-notice .woo-ce-saveto-notice-dismiss').data('nonce')
                    });
                    $btn.text('<?php echo esc_js( __( 'Activated! Reloading...', 'woocommerce-exporter' ) ); ?>');
                    location.reload();
                } else {
                    $btn.prop('disabled', false).text('<?php echo esc_js( $button_text ); ?>');
                }
            })
            .fail(function() {
                $btn.prop('disabled', false).text('<?php echo esc_js( $button_text ); ?>');
            });
        });
    });
    </script>
</div>
