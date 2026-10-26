<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Classes
 */

namespace VisserLabs\WSE\Classes;

use VisserLabs\WSE\Traits\Singleton_Trait;
use VisserLabs\WSE\Abstracts\Abstract_Class;

defined( 'ABSPATH' ) || exit;

/**
 * Marketing > Advanced Coupons product education page.
 *
 * @since 2.8.2
 */
class ACFW_Marketing_Page extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Admin page slug. Shared with the other Rymera plugins that register the same page.
     *
     * @var string
     */
    const PAGE_SLUG = 'advanced-coupons-marketing';

    /**
     * Parent menu slug.
     *
     * @var string
     */
    const PARENT_SLUG = 'woocommerce-marketing';

    /**
     * Advanced Coupons Free plugin slug.
     *
     * @var string
     */
    const ACFWF_SLUG = 'advanced-coupons-for-woocommerce-free';

    /**
     * Advanced Coupons Free plugin basename.
     *
     * @var string
     */
    const ACFWF_PLUGIN = 'advanced-coupons-for-woocommerce-free/advanced-coupons-for-woocommerce-free.php';

    /**
     * Hook suffix of the registered page. Empty when the page is not registered.
     *
     * @var string
     */
    private $_hook_suffix = '';

    /**
     * Register the Advanced Coupons page under the WooCommerce Marketing menu.
     *
     * @since 2.8.2
     * @access public
     */
    public function register_menu() {
        global $submenu;

        // Skip when WooCommerce has no Marketing menu, ACFWF is active, or another plugin already added the page.
        if ( ! isset( $submenu[ self::PARENT_SLUG ] ) || is_plugin_active( self::ACFWF_PLUGIN ) || $this->_is_submenu_registered() ) {
            return;
        }

        // The one-click install also activates the plugin, so it needs both capabilities.
        if ( ! $this->_is_acfwf_installed() && ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        $hook_suffix = add_submenu_page(
            self::PARENT_SLUG,
            __( 'Advanced Coupons', 'woocommerce-exporter' ),
            __( 'Advanced Coupons', 'woocommerce-exporter' ),
            $this->_is_acfwf_installed() ? 'activate_plugins' : 'install_plugins',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );

        $this->_hook_suffix = $hook_suffix ? $hook_suffix : '';
    }

    /**
     * Render the Advanced Coupons page.
     *
     * @since 2.8.2
     * @access public
     */
    public function render_page() {
        $step         = $this->_is_acfwf_installed() ? 2 : 1;
        $images_url   = WOO_CE_IMAGES_URL . 'acfw/';
        $install_url  = wp_nonce_url(
            self_admin_url( 'update.php?action=install-plugin&plugin=' . self::ACFWF_SLUG ),
            'install-plugin_' . self::ACFWF_SLUG
        );
        $activate_url = wp_nonce_url(
            self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( self::ACFWF_PLUGIN ) ),
            'activate-plugin_' . self::ACFWF_PLUGIN
        );

        include WOO_CE_VIEWS_PATH . 'marketing/view-acfw-marketing-page.php';
    }

    /**
     * Enqueue the page styles and scripts on the Advanced Coupons page only.
     *
     * @since 2.8.2
     * @access public
     *
     * @param string $hook_suffix The current admin page hook suffix.
     */
    public function enqueue_scripts( $hook_suffix ) {
        if ( '' === $this->_hook_suffix || $this->_hook_suffix !== $hook_suffix ) {
            return;
        }

        wp_enqueue_style( 'woo-ce-acfw-marketing', plugins_url( '/css/acfw-marketing.css', WOO_CE_RELPATH ), array(), WOO_CE_VERSION );
        wp_enqueue_script( 'woo-ce-acfw-marketing', plugins_url( '/js/acfw-marketing.js', WOO_CE_RELPATH ), array( 'jquery' ), WOO_CE_VERSION, true );
        wp_localize_script(
            'woo-ce-acfw-marketing',
            'wooCeAcfwMarketing',
            array(
                'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
                'nonce'       => wp_create_nonce( 'woo_ce_install_plugin' ),
                'pluginSlug'  => self::ACFWF_SLUG,
                'redirectUrl' => admin_url( 'admin.php?page=acfw-dashboard' ),
                'i18n'        => array(
                    'installing' => __( 'Installing Advanced Coupons…', 'woocommerce-exporter' ),
                    'error'      => __( 'Advanced Coupons could not be installed. Please try again or install it from the Plugins page.', 'woocommerce-exporter' ),
                ),
            )
        );
    }

    /**
     * Check if the Advanced Coupons Free plugin is installed.
     *
     * @since 2.8.2
     * @access private
     *
     * @return bool
     */
    private function _is_acfwf_installed() {
        return file_exists( WP_PLUGIN_DIR . '/' . self::ACFWF_PLUGIN );
    }

    /**
     * Check if another plugin already registered the Advanced Coupons page.
     *
     * @since 2.8.2
     * @access private
     *
     * @return bool
     */
    private function _is_submenu_registered() {
        global $submenu;

        if ( empty( $submenu[ self::PARENT_SLUG ] ) ) {
            return false;
        }

        return in_array( self::PAGE_SLUG, wp_list_pluck( $submenu[ self::PARENT_SLUG ], 2 ), true );
    }

    /**
     * Execute model.
     *
     * @since 2.8.2
     * @access public
     */
    public function run() {
        // Priority 100 runs after the other Rymera plugins that register the same page.
        add_action( 'admin_menu', array( $this, 'register_menu' ), 100 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }
}
