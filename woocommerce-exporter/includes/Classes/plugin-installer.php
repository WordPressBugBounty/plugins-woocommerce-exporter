<?php
namespace WOO_CE;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic of the Plugin_Installer module.
 *
 * @since 2.4.0
 */
class Plugin_Installer {
    /**
     * 3rd party plugins stored in a private to prevent change.
     *
     * @since 2.4.0
     * @var array
     */
    private $allowed_plugins = array(
        'advanced-coupons-for-woocommerce-free' => 'advanced-coupons-for-woocommerce-free/advanced-coupons-for-woocommerce-free.php',
        'wc-vendors'                            => 'wc-vendors/class-wc-vendors.php',
        'woocommerce-wholesale-prices'          => 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php',
        'invoice-gateway-for-woocommerce'       => 'invoice-gateway-for-woocommerce/invoice-gateway-for-woocommerce.php',
        'woocommerce-store-exporter'            => 'woocommerce-store-exporter/exporter.php',
        'woo-product-feed-pro'                  => 'woo-product-feed-pro/woocommerce-sea.php',
        'funnelkit-stripe-woo-payment-gateway'  => 'funnelkit-stripe-woo-payment-gateway/funnelkit-stripe-woo-payment-gateway.php',
        'funnel-builder'                        => 'funnel-builder/funnel-builder.php',
        'wpforms-lite'                          => 'wpforms-lite/wpforms.php',
        'formidable'                            => 'formidable/formidable.php',
        'google-analytics-for-wordpress'        => 'google-analytics-for-wordpress/googleanalytics.php',
        'optinmonster'                          => 'optinmonster/optin-monster-wp-api.php',
        'uncanny-automator'                     => 'uncanny-automator/uncanny-automator.php',
        'metorik-helper'                        => 'metorik-helper/metorik-helper.php',
        'reviews-feed'                          => 'reviews-feed/sb-reviews.php',
        'rafflepress'                           => 'rafflepress/rafflepress.php',
        'all-in-one-seo-pack'                   => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
    );

    /**
     * Constructor.
     *
     * @since 2.4.0
     */
    public function __construct() {
        add_action( 'wp_ajax_woo_ce_install_plugin', array( $this, 'ajax_install_activate_plugin' ) );
    }

    /**
     * Download and activate a given plugin.
     *
     * @since 2.4.0
     * @access public
     *
     * @param string $plugin_slug Plugin slug.
     * @param bool   $silently download plugin silently.
     * @return bool|\WP_Error True if successful, WP_Error otherwise.
     */
    public function download_and_activate_plugin( $plugin_slug, $silently = false ) {

        // Check if the current user has the required permissions.
        if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
            return new \WP_Error( 'permission_denied', __( 'You do not have sufficient permissions to install and activate plugins.', 'woocommerce-store-toolkit' ) );
        }

        // Check if the plugin is valid.
        if ( ! $this->_is_plugin_allowed_for_install( $plugin_slug ) ) {
            return new \WP_Error( 'woo_ce_plugin_not_allowed', __( 'The plugin is not valid.', 'woocommerce-store-toolkit' ) );
        }

        // Get required files since we're calling this outside of context.
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        // Get the plugin info from WordPress.org's plugin repository.
        $api = plugins_api( 'plugin_information', array( 'slug' => $plugin_slug ) );
        if ( is_wp_error( $api ) ) {
            return $api;
        }

        $plugin_basename = $this->get_plugin_basename_by_slug( $plugin_slug );

        // Check if the plugin is already active.
        if ( is_plugin_active( $plugin_basename ) ) {
            return new \WP_Error( 'woo_ce_plugin_already_active', __( 'The plugin is already installed.', 'woocommerce-store-toolkit' ) );
        }

        // Check if the plugin is already installed but inactive, just activate it and return true.
        if ( woo_ce_is_plugin_installed( $plugin_basename ) ) {
            return $this->_activate_plugin( $plugin_basename, $plugin_slug );
        }

        // Download the plugin.
        $skin     = $silently ? new \WP_Ajax_Upgrader_Skin() : new \Plugin_Installer_Skin(
            array(
                'type'  => 'web',
                'title' => sprintf( 'Installing Plugin: %s', $api->name ),
            )
        );
        $upgrader = new \Plugin_Upgrader( $skin );

        $result = $upgrader->install( $api->download_link );

        // Check if the plugin was installed successfully.
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // Activate the plugin.
        return $this->_activate_plugin( $plugin_basename, $plugin_slug );
    }

    /**
     * Activate a plugin.
     *
     * @since 2.4.0
     * @access private
     *
     * @param string $plugin_basename Plugin basename.
     * @param string $plugin_slug     Plugin slug.
     * @return bool|\WP_Error True if successful, WP_Error otherwise.
     */
    private function _activate_plugin( $plugin_basename, $plugin_slug ) {
        $result = activate_plugin( $plugin_basename );

        // Update uncanny automator source option.
        if ( 'uncanny-automator' === $plugin_slug ) {
            update_option( 'uncannyautomator_source', 'acoupons' );
        }

        // Update FunnelKit Stripe source option when FunnelKit Stripe or Funnel Builder are installed.
        if ( 'funnelkit-stripe-woo-payment-gateway' === $plugin_slug || 'funnel-builder' === $plugin_slug ) {
            update_option( 'fkwcs_wp_stripe', '51c012eccfe7b12df7e51be418fab892', false );
        }

        return is_wp_error( $result ) ? $result : true;
    }

    /**
     * Get the list of allowed plugins for install.
     *
     * @since 2.4.0
     * @access public
     *
     * @return array List of allowed plugins.
     */
    public function get_allowed_plugins() {
        // Allow other plugins to be installed but not let them overwrite the ones listed above.
        $extra_allowed_plugins = apply_filters( 'woo_ce_allowed_install_plugins', array() );

        return array_merge( $this->allowed_plugins, $extra_allowed_plugins );
    }

    /**
     * Validate if the given plugin is allowed for install.
     *
     * @since 2.4.0
     * @access private
     *
     * @param string $plugin_slug Plugin slug.
     * @return bool True if valid, false otherwise.
     */
    private function _is_plugin_allowed_for_install( $plugin_slug ) {
        return in_array( $plugin_slug, array_keys( $this->get_allowed_plugins() ), true );
    }

    /**
     * Get the plugin basename by slug.
     *
     * @since 2.4.0
     * @access public
     *
     * @param string $plugin_slug Plugin slug.
     * @return string Plugin basename.
     */
    public function get_plugin_basename_by_slug( $plugin_slug ) {
        $allowed_plugins = $this->get_allowed_plugins();

        return $allowed_plugins[ $plugin_slug ] ?? '';
    }


    /*
    |--------------------------------------------------------------------------
    | AJAX Functions
    |--------------------------------------------------------------------------
     */

    /**
     * AJAX install and activate a plugin.
     *
     * @since 2.4.0
     * @access public
     */
    public function ajax_install_activate_plugin() {

        // Check nonce.
        check_ajax_referer( 'woo_ce_install_plugin', 'nonce' );

        // Retrieve the plugin slug from the front-end.
        $plugin_slug = isset( $_REQUEST['plugin_slug'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin_slug'] ) ) : '';

        $silent = isset( $_REQUEST['silent'] ) ?? false;
        $result = $this->download_and_activate_plugin( $plugin_slug, $silent );

        do_action( 'woo_ce_after_install_activate_plugin', $plugin_slug, $result );

        if ( isset( $_REQUEST['redirect'] ) ) {
            wp_safe_redirect( admin_url( 'plugins.php' ) );
        }

        // Check if the result is a WP_Error.
        if ( is_wp_error( $result ) ) {
            // If it is, return a JSON response indicating failure.
            wp_send_json_error( $result->get_error_message() );
        } else {
            // If not, return a JSON response indicating success.
            wp_send_json_success();
        }
    }
}

new Plugin_Installer();
