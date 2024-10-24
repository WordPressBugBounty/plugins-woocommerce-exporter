<?php
/**
 * Author: Rymera Web Co.
 *
 * @package VisserLabs\WSE\Helpers
 */

namespace VisserLabs\WSE\Helpers;

/**
 * Stripe class.
 *
 * @since 2.7.3
 */
class Helper {

    /**
     * Get plugin data.
     *
     * @param bool $markup    If the returned data should have HTML markup applied. Default false.
     * @param bool $translate If the returned data should be translated. Default false.
     *
     * @since 2.7.3
     * @return string[]|string
     */
    public static function get_plugin_data( $markup = false, $translate = false ) {
        return get_plugin_data( WOO_CE_PLUGIN_FILE, $markup, $translate );
    }

    /**
     * Get plugin version.
     *
     * @param bool $markup    Optional. If the returned data should have HTML markup applied.
     *                        Default true.
     * @param bool $translate Optional. If the returned data should be translated. Default true.
     *
     * @since 2.7.3
     * @return string
     */
    public static function get_plugin_version( $markup = true, $translate = true ) {
        return self::get_plugin_data( $markup, $translate )['Version'];
    }

    /**
     * Log an error message.
     *
     * @param string $message The message to log.
     *
     * @since 2.7.3
     * @return void
     */
    public static function log_error( $message ) {
        if ( function_exists( 'wc_get_logger' ) ) {
            $logger = wc_get_logger();
            $logger->error( $message, array( 'source' => 'wsed' ) );
        }
    }

    /**
     * Get the current memory usage
     *
     * @since 2.7.3
     * @return string
     */
    public static function get_current_memory_usage() {
        return round( memory_get_usage( true ) / 1024 / 1024, 2 );
    }

    /**
     * Get last error message.
     *
     * @since 2.7.3
     * @return string
     */
    public static function get_last_error() {
        $error = error_get_last();
        return null !== $error ? $error['message'] : '';
    }

    /**
     * Load templates in an overridable manner.
     *
     * @since 2.7.3
     *
     * @param string $template Template path.
     * @param array  $args     Options to pass to the template.
     * @param string $path     Default template path.
     */
    public static function load_template( $template, $args = array(), $path = '' ) {
        $path = $path ? $path : WOO_CE_PATH . 'templates/';
        wc_get_template( $template, $args, '', $path );
    }
}
