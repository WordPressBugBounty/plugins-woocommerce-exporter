<?php

/**
 *
 * Filename: common.php
 * Description: common.php loads commonly accessed functions across the Visser Labs suite.
 *
 * Premium
 * - woo_ce_admin_deactivate_updater_plugin
 * - woo_ce_updater_api_call
 * - woo_ce_check_for_plugin_update
 * - vl_common_make_request
 *
 * Free
 * - woo_get_action
 * - woo_is_wpsc_activated
 * - woo_is_woo_activated
 * - woo_is_jigo_activated
 * - woo_is_exchange_activated
 * - woo_get_woo_version
 */

if ( ! function_exists( 'woo_get_action' ) ) {
    function woo_get_action( $prefer_get = false ) {

        if ( isset( $_GET['action'] ) && $prefer_get ) {
            return sanitize_text_field( $_GET['action'] );
        }

        if ( isset( $_POST['action'] ) ) {
            return sanitize_text_field( $_POST['action'] );
        }

        if ( isset( $_GET['action'] ) ) {
            return sanitize_text_field( $_GET['action'] );
        }

        return;
    }
}

if ( ! function_exists( 'woo_is_wpsc_activated' ) ) {
    function woo_is_wpsc_activated() {

        if ( class_exists( 'WP_eCommerce' ) || defined( 'WPSC_VERSION' ) ) {
            return true;
        }
    }
}

if ( ! function_exists( 'woo_is_woo_activated' ) ) {
    function woo_is_woo_activated() {

        if ( class_exists( 'Woocommerce' ) ) {
            return true;
        }
    }
}

if ( ! function_exists( 'woo_is_jigo_activated' ) ) {
    function woo_is_jigo_activated() {

        if ( function_exists( 'jigoshop_init' ) ) {
            return true;
        }
    }
}

if ( ! function_exists( 'woo_is_exchange_activated' ) ) {
    function woo_is_exchange_activated() {

        if ( function_exists( 'IT_Exchange' ) ) {
            return true;
        }
    }
}

if ( ! function_exists( 'woo_get_woo_version' ) ) {
    function woo_get_woo_version() {

        $version = false;
        if ( defined( 'WC_VERSION' ) ) {
            $version = WC_VERSION;
            // Backwards compatibility
        } elseif ( defined( 'WOOCOMMERCE_VERSION' ) ) {
            $version = WOOCOMMERCE_VERSION;
        }

        return $version;
    }
}
