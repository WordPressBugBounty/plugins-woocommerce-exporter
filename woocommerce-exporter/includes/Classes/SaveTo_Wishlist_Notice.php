<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Classes
 */

namespace VisserLabs\WSE\Classes;

use VisserLabs\WSE\Abstracts\Abstract_Class;

defined( 'ABSPATH' ) || exit;

/**
 * SaveTo Wishlist admin notice class.
 *
 * @since 2.7.8
 */
class SaveTo_Wishlist_Notice extends Abstract_Class {

    /**
     * Option name for the show after timestamp.
     *
     * @var string
     */
    const SHOW_AFTER_OPTION = 'woo_ce_saveto_notice_show_after';

    /**
     * Option name for the dismissed flag.
     *
     * @var string
     */
    const DISMISSED_OPTION = 'woo_ce_saveto_notice_dismissed';

    /**
     * SaveTo Wishlist Lite plugin basename.
     *
     * @var string
     */
    const SAVETO_PLUGIN = 'saveto-wishlist-lite-for-woocommerce/saveto-wishlist-lite-for-woocommerce.php';

    /**
     * Advanced Coupons Free plugin basename.
     *
     * @var string
     */
    const ACFWF_PLUGIN = 'advanced-coupons-for-woocommerce-free/advanced-coupons-for-woocommerce-free.php';

    /**
     * Run the class.
     *
     * @since 2.7.8
     */
    public function run() {
        add_action( 'all_admin_notices', array( $this, 'maybe_display_notice' ) );
        add_action( 'wp_ajax_woo_ce_dismiss_saveto_notice', array( $this, 'ajax_dismiss_notice' ) );
    }

    /**
     * Maybe display the SaveTo Wishlist admin notice.
     *
     * @since 2.7.8
     */
    public function maybe_display_notice() {
        // 1. Must have install_plugins capability.
        if ( ! current_user_can( 'install_plugins' ) ) {
            return;
        }

        // 2. Only show on relevant screens.
        if ( ! $this->is_allowed_screen() ) {
            return;
        }

        // 3. AC Free is active — defer to its notice.
        if ( is_plugin_active( self::ACFWF_PLUGIN ) ) {
            return;
        }

        // 4. SaveTo already active.
        if ( defined( 'STWLITE_VERSION' ) ) {
            return;
        }

        // 5. Already dismissed.
        if ( get_option( self::DISMISSED_OPTION ) === 'yes' ) {
            return;
        }

        // 6. Timing check.
        $show_after = get_option( self::SHOW_AFTER_OPTION );
        if ( false === $show_after ) {
            $install_date = get_option( 'woo_ce_initial_install_date' );
            if ( ! $install_date ) {
                // Existing install (predates this code) — show immediately.
                update_option( self::SHOW_AFTER_OPTION, time() );
                $show_after = time();
            } else {
                // Fresh install — delay 20 days.
                $show_after_time = (int) $install_date + ( 20 * DAY_IN_SECONDS );
                update_option( self::SHOW_AFTER_OPTION, $show_after_time );
                return;
            }
        }

        if ( time() < (int) $show_after ) {
            return;
        }

        // 7. Render.
        $this->render_notice();
    }

    /**
     * Check if the current screen is one where the notice should display.
     *
     * @since 2.7.8
     *
     * @return bool
     */
    private function is_allowed_screen() {
        $screen = get_current_screen();
        if ( ! $screen ) {
            return false;
        }

        // Never show on edit/add new screens (including HPOS orders).
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( in_array( $action, array( 'edit', 'new' ), true ) || 'add' === $screen->action || isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return false;
        }

        // Plugins list page.
        if ( 'plugins' === $screen->id ) {
            return true;
        }

        // WooCommerce subpages (orders, settings, status, etc.).
        if ( false !== strpos( $screen->id, 'woocommerce' ) ) {
            return true;
        }

        // WooCommerce post type list views (coupons, orders, products).
        if ( in_array( $screen->post_type, array( 'shop_coupon', 'shop_order', 'product' ), true ) ) {
            return true;
        }

        // Store Exporter own pages.
        if ( false !== strpos( $screen->id, 'woo_ce' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Render the SaveTo Wishlist admin notice.
     *
     * @since 2.7.8
     */
    private function render_notice() {
        $logo_url         = WOO_CE_IMAGES_URL . 'savetowishlist-logo.svg';
        $dismiss_nonce    = wp_create_nonce( 'woo_ce_saveto_notice_nonce' );
        $install_nonce    = wp_create_nonce( 'woo_ce_install_plugin' );
        $plugin_installed = file_exists( WP_PLUGIN_DIR . '/' . self::SAVETO_PLUGIN );
        $button_text      = $plugin_installed ? __( 'Activate', 'woocommerce-exporter' ) : __( 'Install & Activate', 'woocommerce-exporter' );

        include WOO_CE_VIEWS_PATH . 'notices/view-saveto-wishlist-notice.php';
    }

    /**
     * AJAX handler to dismiss the SaveTo Wishlist notice.
     *
     * @since 2.7.8
     */
    public function ajax_dismiss_notice() {
        check_ajax_referer( 'woo_ce_saveto_notice_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Insufficient permissions.', 'woocommerce-exporter' ) );
        }

        update_option( self::DISMISSED_OPTION, 'yes' );
        wp_send_json_success();
    }
}
