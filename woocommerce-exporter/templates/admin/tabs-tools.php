<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<h2><?php esc_html_e( 'Growth Tools', 'woocommerce-store-toolkit' ); ?></h2>

<div class="growth-tools-container">

    <?php
    // Nonce for installing plugins woo_ce_install_plugin.
    wp_nonce_field( 'woo_ce_install_plugin', 'woo_ce_install_plugin' );
    ?>

    <div class="growth-tools-left-menu">
        <ul>
            <li><a href="#by-our-team"><?php esc_html_e( 'By Our Team', 'woocommerce-store-toolkit' ); ?></a></li>
            <li><a href="#payments"><?php esc_html_e( 'Payments', 'woocommerce-store-toolkit' ); ?></a></li>
            <li><a href="#coupon-marketing"><?php esc_html_e( 'Coupon Marketing', 'woocommerce-store-toolkit' ); ?></a></li>
            <li><a href="#analytics"><?php esc_html_e( 'Analytics', 'woocommerce-store-toolkit' ); ?></a></li>
            <li><a href="#email-marketing"><?php esc_html_e( 'Email Marketing', 'woocommerce-store-toolkit' ); ?></a></li>
            <li><a href="#form-builders"><?php esc_html_e( 'Form Builders', 'woocommerce-store-toolkit' ); ?></a></li>
            <li><a href="#offers-discounts"><?php esc_html_e( 'Offers & Discounts', 'woocommerce-store-toolkit' ); ?></a></li>
            <li><a href="#bulk-sales"><?php esc_html_e( 'Bulk Sales', 'woocommerce-store-toolkit' ); ?></a></li>
            <li><a href="#automation"><?php esc_html_e( 'Automation', 'woocommerce-store-toolkit' ); ?></a></li>
            <li><a href="#data-management"><?php esc_html_e( 'Data Management', 'woocommerce-store-toolkit' ); ?></a></li>
            <li><a href="#reviews"><?php esc_html_e( 'Reviews', 'woocommerce-store-toolkit' ); ?></a></li>
            <li><a href="#giveaways"><?php esc_html_e( 'Giveaways', 'woocommerce-store-toolkit' ); ?></a></li>
            <li><a href="#seo"><?php esc_html_e( 'SEO', 'woocommerce-store-toolkit' ); ?></a></li>
        </ul>
    </div>

    <div class="growth-tools-right-content">
        <div class="growth-tools-card" data-tags="by-our-team,coupon-marketing,offers-discounts">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/advanced-coupons-for-woocommerce-free/assets/icon-128x128.png' ); ?>" alt="<?php esc_attr_e( 'Advanced Coupons', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'Advanced Coupons for WooCommerce (Free Plugin)', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Extends your coupon features so you can market your store better. Adds cart conditions (coupon rules), buy one get one (BOGO) deals, url coupons, coupon categories and loads more.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'advanced-coupons-for-woocommerce-free/advanced-coupons-for-woocommerce-free.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'advanced-coupons-for-woocommerce-free/advanced-coupons-for-woocommerce-free.php' ) ) : ?>
                        <a href="#" data-plugin-slug="advanced-coupons-for-woocommerce-free" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="by-our-team,bulk-sales,payments">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/wc-vendors/assets/icon.svg' ); ?>" alt="<?php esc_attr_e( 'WC Vendors', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'WC Vendors (Free Plugin)', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Easiest way to create your multivendor marketplace and earn commission from every sale. Create a WooCommerce marketplace with multi-seller, product vendor & multi vendor commissions.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <?php echo woo_ce_is_plugin_installed( 'wc-vendors/class-wc-vendors.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'wc-vendors/class-wc-vendors.php' ) ) : ?>
                        <a href="#" data-plugin-slug="wc-vendors" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="by-our-team,bulk-sales,payments">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/woocommerce-wholesale-prices/assets/icon-128x128.jpg' ); ?>" alt="<?php esc_attr_e( 'WooCommerce Wholesale Prices', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'WooCommerce Wholesale Prices (Free Plugin)', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'The #1 WooCommerce wholesale plugin for adding wholesale prices & managing B2B customers. Trusted by over 25k store owners for managing wholesale orders, pricing, visibility, user roles, and more.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php' ) ) : ?>
                        <a href="#" data-plugin-slug="woocommerce-wholesale-prices" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="by-our-team,bulk-sales,payments">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/invoice-gateway-for-woocommerce/assets/icon-128x128.jpg' ); ?>" alt="<?php esc_attr_e( 'Invoice Gateway for WooCommerce', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'Invoice Gateway for WooCommerce (Free Plugin)', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Accept orders via a special invoice payment gateway method which lets your customer enter their order without upfront payment. Then just issue an invoice from your accounting system and paste in the number.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'invoice-gateway-for-woocommerce/invoice-gateway-for-woocommerce.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'invoice-gateway-for-woocommerce/invoice-gateway-for-woocommerce.php' ) ) : ?>
                        <a href="#" data-plugin-slug="invoice-gateway-for-woocommerce" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="by-our-team,data-management">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/woocommerce-exporter/assets/icon-128x128.png' ); ?>" alt="<?php esc_attr_e( 'Store Exporter for WooCommerce', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'Store Exporter for WooCommerce (Free Plugin)', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Easily export Orders, Subscriptions, Coupons, Products, Categories, Tags to a variety of formats. The deluxe version also adds scheduled exporting for easy reporting and syncing with other systems.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'woocommerce-store-exporter/exporter.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'woocommerce-store-exporter/exporter.php' ) ) : ?>
                        <a href="#" data-plugin-slug="woocommerce-store-exporter" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="by-our-team,data-management,automation">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/woo-product-feed-pro/assets/icon-128x128.png' ); ?>" alt="<?php esc_attr_e( 'Product Feed Pro by AdTribes', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'Product Feed Pro (Free Plugin)', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Helps you generate and manage product feeds for various marketing channels, such as Google Shopping, Facebook, and more, to optimize your eCommerce store\'s visibility and sales.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'woo-product-feed-pro/woocommerce-sea.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'woo-product-feed-pro/woocommerce-sea.php' ) ) : ?>
                        <a href="#" data-plugin-slug="woo-product-feed-pro" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="payments">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/funnelkit-stripe-woo-payment-gateway/assets/icon-128x128.png' ); ?>" alt="<?php esc_attr_e( 'FunnelKit Stripe Woo Payment Gateway', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'FunnelKit Stripe Woo Payment Gateway', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Enable secure and efficient payment processing on your WooCommerce store with Stripe integration.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'funnelkit-stripe-woo-payment-gateway/funnelkit-stripe-woo-payment-gateway.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'funnelkit-stripe-woo-payment-gateway/funnelkit-stripe-woo-payment-gateway.php' ) ) : ?>
                        <a href="#" data-plugin-slug="funnelkit-stripe-woo-payment-gateway" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="form-builders,offers-discounts">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/funnel-builder/assets/icon-128x128.jpg' ); ?>" alt="<?php esc_attr_e( 'Funnel Builder by FunnelKit', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'Funnel Builder by FunnelKit', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Create high-converting sales funnels to optimize your WooCommerce store’s revenue.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'funnel-builder/funnel-builder.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'funnel-builder/funnel-builder.php' ) ) : ?>
                        <a href="#" data-plugin-slug="funnel-builder" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="form-builders,data-management">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/wpforms-lite/assets/icon-128x128.png' ); ?>" alt="<?php esc_attr_e( 'WPForms Lite', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'WPForms Lite', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Easily create forms for your website with this user-friendly drag-and-drop form builder.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'wpforms-lite/wpforms.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'wpforms-lite/wpforms.php' ) ) : ?>
                        <a href="#" data-plugin-slug="wpforms-lite" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="form-builders,data-management">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/formidable/assets/icon-128x128.png' ); ?>" alt="<?php esc_attr_e( 'Formidable Forms', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'Formidable Forms', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Create advanced forms and manage form entries with this powerful form builder.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'formidable/formidable.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'formidable/formidable.php' ) ) : ?>
                        <a href="#" data-plugin-slug="formidable" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="analytics">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/google-analytics-for-wordpress/assets/icon-128x128.png' ); ?>" alt="<?php esc_attr_e( 'Google Analytics for WordPress by MonsterInsights', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'Google Analytics for WordPress by MonsterInsights', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Integrate Google Analytics with your WordPress site and get insights into your visitors and content.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'google-analytics-for-wordpress/googleanalytics.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'google-analytics-for-wordpress/googleanalytics.php' ) ) : ?>
                        <a href="#" data-plugin-slug="google-analytics-for-wordpress" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="email-marketing">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/optinmonster/assets/icon-128x128.png' ); ?>" alt="<?php esc_attr_e( 'OptinMonster', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'OptinMonster', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Create high-converting popups and forms to boost your email list and grow your leads.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'optinmonster/optin-monster-wp-api.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'optinmonster/optin-monster-wp-api.php' ) ) : ?>
                        <a href="#" data-plugin-slug="optinmonster" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="automation">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/uncanny-automator/assets/icon-128x128.png' ); ?>" alt="<?php esc_attr_e( 'Uncanny Automator', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'Uncanny Automator', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Automate your WordPress workflows by connecting plugins, apps, and sites with ease.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'uncanny-automator/uncanny-automator.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'uncanny-automator/uncanny-automator.php' ) ) : ?>
                        <a href="#" data-plugin-slug="uncanny-automator" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="analytics">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/metorik-helper/assets/icon-128x128.png' ); ?>" alt="<?php esc_attr_e( 'Metorik Helper', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'Metorik', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Enhance your WooCommerce store analytics and reporting with Metorik.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'metorik-helper/metorik-helper.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'metorik-helper/metorik-helper.php' ) ) : ?>
                        <a href="#" data-plugin-slug="metorik-helper" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="reviews">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/reviews-feed/assets/icon-128x128.png' ); ?>" alt="<?php esc_attr_e( 'Reviews Feed', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'Reviews Feed', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Display reviews from multiple platforms on your WordPress site to boost credibility.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'reviews-feed/sb-reviews.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'reviews-feed/sb-reviews.php' ) ) : ?>
                        <a href="#" data-plugin-slug="reviews-feed" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="giveaways">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/rafflepress/assets/icon-128x128.png' ); ?>" alt="<?php esc_attr_e( 'RafflePress', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'RafflePress', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Create viral giveaways and contests to grow your email list and social media following.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'rafflepress/rafflepress.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'rafflepress/rafflepress.php' ) ) : ?>
                        <a href="#" data-plugin-slug="rafflepress" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="growth-tools-card" data-tags="seo">
            <div class="card-title">
                <img src="<?php echo esc_url( 'https://ps.w.org/all-in-one-seo-pack/assets/icon-128x128.png' ); ?>" alt="<?php esc_attr_e( 'All in One SEO Pack', 'woocommerce-store-toolkit' ); ?>" />
                <h3><?php esc_html_e( 'All in One SEO Pack', 'woocommerce-store-toolkit' ); ?></h3>
            </div>
            <div class="card-body xs-text-center">
                <p class="mt-0"><?php esc_html_e( 'Optimize your WordPress site for SEO with this comprehensive and user-friendly plugin.', 'woocommerce-store-toolkit' ); ?></p>
            </div>
            <div class="card-footer">
                <div class="install-status">
                    <p class="m-0">
                        <strong><?php esc_html_e( 'Status:', 'woocommerce-store-toolkit' ); ?></strong>
                        <span class="install-status-value"><?php echo woo_ce_is_plugin_installed( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ? esc_html_e( 'Installed', 'woocommerce-store-toolkit' ) : esc_html_e( 'Not installed', 'woocommerce-store-toolkit' ); ?></span>
                    </p>
                    <?php if ( ! woo_ce_is_plugin_installed( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ) : ?>
                        <a href="#" data-plugin-slug="all-in-one-seo-pack" class="button button-primary"><?php esc_html_e( 'Install Plugin', 'woocommerce-store-toolkit' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .growth-tools-container {
        max-width: 1200px;
        margin: 20px 0 30px 0;
        display: grid;
        grid-template-columns: max-content 1fr;
        gap: 10px;
    }

    .growth-tools-left-menu {
        background-color: white;
        box-shadow: 1px 2px 5px rgba(0, 0, 0, 0.15);
        padding: 15px 8px;
        border-radius: 6px;
        width: max-content;
    }

    .growth-tools-left-menu ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .growth-tools-left-menu ul li {
        margin-bottom: 10px;
    }

    .growth-tools-left-menu ul li a {
        text-decoration: none;
        color: #333;
        display: block;
        padding: 10px 20px;
        border-radius: 6px;
        transition: background-color 0.3s;
    }

    .growth-tools-left-menu ul li a:active,
    .growth-tools-left-menu ul li a:focus {
        outline: none;
        box-shadow: none;
    }

    .growth-tools-left-menu ul li a.active {
        background-color: #f1f1f1;
    }

    .growth-tools-left-menu ul li a:hover {
        background-color: #f1f1f1;
    }

    .growth-tools-right-content {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        grid-template-rows: max-content;
        gap: 10px;
    }

    .growth-tools-card {
        background-color: white;
        box-shadow: 1px 2px 5px rgba(0, 0, 0, 0.15);
        padding: 20px;
        border-radius: 6px;
        display: grid;
        grid-template-rows: auto auto 1fr auto;
        gap: 0px;
        height: max-content;
    }

    .growth-tools-card.no-shadow {
        box-shadow: none;
        padding: 0;
    }

    .growth-tools-card img {
        max-width: 36px;
        width: 100%;
        margin-right: 8px;
        display: block;
    }

    .growth-tools-card img.team {
        max-width: 100%;
        width: 100%;
        display: block;
    }

    .growth-tools-card .card-title {
        display: flex;
        align-items: center;
    }

    .growth-tools-card .card-body {
        display: block;
    }

    .growth-tools-card .card-footer {
        margin: 10px -20px -20px -20px;
        background: #f1f1f1;
        padding: 5px 20px 5px 20px;
        max-height: 45px;
    }

    .growth-tools-card .card-footer .install-status {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .growth-tools-card .card-footer .install-status a.disabled {
        pointer-events: none;
        cursor: not-allowed;
        background-color: #ccc;
    }

    /* Mobile Responsive CSS */
    @media (max-width: 768px) {
        .growth-tools-container {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .growth-tools-left-menu {
            width: 100%;
            margin-bottom: 20px;
        }

        .growth-tools-left-menu ul li a {
            padding: 10px;
        }

        .growth-tools-right-content {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .growth-tools-card {
            width: 100%;
        }
    }
</style>
