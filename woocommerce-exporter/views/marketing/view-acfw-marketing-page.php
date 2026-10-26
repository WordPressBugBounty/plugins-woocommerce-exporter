<?php
/**
 * Marketing > Advanced Coupons page.
 *
 * @package VisserLabs\WSE
 *
 * @var int    $step         Current step. 1 = not installed, 2 = installed but inactive.
 * @var string $images_url   Base URL of the page images.
 * @var string $install_url  WordPress core install URL. Fallback when JavaScript is off.
 * @var string $activate_url WordPress core activate URL.
 */

defined( 'ABSPATH' ) || exit;

$features = array(
    array(
        'icon'  => 'feature-brain.png',
        /* translators: %s: line break. */
        'label' => __( 'Advanced Coupon%sCapabilities', 'woocommerce-exporter' ),
    ),
    array(
        'icon'  => 'feature-credit-card.png',
        /* translators: %s: line break. */
        'label' => __( 'Store Credit%sSystem', 'woocommerce-exporter' ),
    ),
    array(
        'icon'  => 'feature-gem-stone.png',
        /* translators: %s: line break. */
        'label' => __( 'Loyalty%sProgram', 'woocommerce-exporter' ),
    ),
    array(
        'icon'  => 'feature-shopping-bags.png',
        /* translators: %s: line break. */
        'label' => __( 'BOGO%sPromotions', 'woocommerce-exporter' ),
    ),
    array(
        'icon'  => 'feature-wrapped-gift.png',
        /* translators: %s: line break. */
        'label' => __( 'Versatile Gift%sCard Solutions', 'woocommerce-exporter' ),
    ),
);
?>
<div id="woo-ce-acfw-marketing-page" class="wrap woo-ce-marketing-page" data-testid="woo-ce-acfw-marketing-page">
    <?php // Keeps admin notices above the page, not inside the hero. ?>
    <hr class="wp-header-end" />
    <div class="container">
        <div class="section-hero text-center" data-testid="woo-ce-acfw-marketing-hero">
            <img class="logo" src="<?php echo esc_url( $images_url . 'logo.png' ); ?>" alt="<?php esc_attr_e( 'Advanced Coupons for WooCommerce', 'woocommerce-exporter' ); ?>" />
            <h1 class="hero-title">
                <?php
                echo wp_kses_post(
                    sprintf(
                        /* translators: %1$s: line break and opening span tag, %2$s: closing span tag. */
                        __( 'Get Better Results With %1$s Better WooCommerce Coupons %2$s', 'woocommerce-exporter' ),
                        '<br/><span class="text-color-primary">',
                        '</span>'
                    )
                );
                ?>
            </h1>
            <div class="hero-image">
                <img src="<?php echo esc_url( $images_url . 'marketing-hero.png' ); ?>" alt="" />
            </div>
        </div>

        <ul class="features" data-testid="woo-ce-acfw-marketing-features">
            <?php foreach ( $features as $feature ) : ?>
                <li data-testid="woo-ce-acfw-marketing-feature">
                    <div class="feature-icon">
                        <img src="<?php echo esc_url( $images_url . $feature['icon'] ); ?>" alt="" />
                    </div>
                    <div class="feature-text">
                        <?php echo wp_kses_post( sprintf( $feature['label'], '<br/>' ) ); ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="section-description text-center">
            <p><?php esc_html_e( 'Advanced Coupons is the best WooCommerce coupon plugin because it adds more coupon discount types, store credit, and all the advanced options you wish WooCommerce coupons could already do.', 'woocommerce-exporter' ); ?></p>
        </div>

        <ul class="card-plugin-action">
            <li class="card <?php echo 1 !== $step ? 'disabled' : ''; ?>" data-testid="woo-ce-acfw-marketing-step-1">
                <div class="card-title">
                    <span class="card-title--step text-color-primary"><?php esc_html_e( 'Step 1', 'woocommerce-exporter' ); ?></span>
                    <h2><?php esc_html_e( 'Enhance your Coupon Capabilities', 'woocommerce-exporter' ); ?></h2>
                </div>
                <div class="card-body">
                    <ul>
                        <li><?php esc_html_e( 'Implement BOGO deals and smart cart conditions to create offers that adjust to customer behaviors and cart contents, ensuring timely and relevant discounts.', 'woocommerce-exporter' ); ?></li>
                        <li><?php esc_html_e( 'Boost customer retention with a loyalty program and store credits to encourage frequent visits and enhance long-term profitability.', 'woocommerce-exporter' ); ?></li>
                    </ul>
                </div>
                <div class="card-footer">
                    <?php if ( 1 === $step ) : ?>
                        <a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary button-install woo-ce-acfw-install" data-testid="woo-ce-acfw-marketing-install">
                            <?php esc_html_e( 'Get Advanced Coupons', 'woocommerce-exporter' ); ?>
                        </a>
                        <p class="woo-ce-acfw-install-error" role="alert" data-testid="woo-ce-acfw-marketing-install-error" hidden></p>
                    <?php else : ?>
                        <span class="button button-primary button-install" aria-disabled="true"><?php esc_html_e( 'Get Advanced Coupons', 'woocommerce-exporter' ); ?></span>
                    <?php endif; ?>
                </div>
            </li>
            <li class="card <?php echo 2 !== $step ? 'disabled' : ''; ?>" data-testid="woo-ce-acfw-marketing-step-2">
                <div class="card-title">
                    <span class="card-title--step text-color-primary"><?php esc_html_e( 'Step 2', 'woocommerce-exporter' ); ?></span>
                    <h2><?php esc_html_e( 'Configure your Coupon Features', 'woocommerce-exporter' ); ?></h2>
                </div>
                <div class="card-body">
                    <ul>
                        <li><?php esc_html_e( 'Create targeted promotions using coupon templates to help you streamline your promotions and grow your profits.', 'woocommerce-exporter' ); ?></li>
                        <li><?php esc_html_e( 'Optimize coupon management by categorizing, restricting roles, and enabling URL activation to boost engagement and loyalty.', 'woocommerce-exporter' ); ?></li>
                    </ul>
                </div>
                <div class="card-footer">
                    <?php if ( 2 === $step ) : ?>
                        <a href="<?php echo esc_url( $activate_url ); ?>" class="button button-primary button-activate" data-testid="woo-ce-acfw-marketing-activate">
                            <?php esc_html_e( 'Activate Plugin', 'woocommerce-exporter' ); ?>
                        </a>
                    <?php else : ?>
                        <span class="button button-primary button-activate" aria-disabled="true"><?php esc_html_e( 'Activate Plugin', 'woocommerce-exporter' ); ?></span>
                    <?php endif; ?>
                </div>
            </li>
        </ul>
    </div>
</div>
