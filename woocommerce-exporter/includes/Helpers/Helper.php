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
     * Whether the "Reset language to English" setting is on.
     *
     * @since 2.8.3
     * @return bool
     */
    public static function is_language_reset() {
        return (bool) get_option( 'woo_ce_reset_language_english', false );
    }

    /**
     * Run a callback in the current user's locale.
     *
     * Field-list REST requests run in the site locale, because their field
     * labels become the saved export headers. The labels the SPA only displays
     * (categories, groups, data types) must still follow the user's language,
     * like the rest of the screen.
     *
     * With "Reset language to English" on the callback runs as is: the plugin's
     * strings stay English whatever the locale. switch_to_locale() also does
     * nothing when the user locale has no core language pack installed; the
     * labels then stay in the site language.
     *
     * @param callable $callback Callback whose return value is passed through.
     *
     * @since 2.8.3
     * @return mixed The callback's return value.
     */
    public static function in_user_locale( callable $callback ) {
        if ( self::is_language_reset() ) {
            return $callback();
        }

        $switched = switch_to_locale( get_user_locale() );
        try {
            return $callback();
        } finally {
            if ( $switched ) {
                restore_previous_locale();
            }
        }
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
     * Validate a file path to prevent directory traversal attacks.
     *
     * @since 2.7.8
     *
     * @param string $file_path The file path to validate.
     * @return bool True if the file path is valid, false otherwise.
     */
    public static function validate_file_path( $file_path ) {
        // Check for directory traversal attempts.
        if ( false !== strpos( $file_path, '..' ) ) {
            return false;
        }

        // Check for null bytes.
        if ( false !== strpos( $file_path, "\0" ) ) {
            return false;
        }

        // Validate the file using WordPress function.
        if ( 0 !== validate_file( $file_path ) ) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize a Filename pattern.
     *
     * Keeps `{tag}` and `%tag%` placeholders intact, because the result is a
     * Filename pattern that is resolved at export time rather than a finished
     * filename. Strips a trailing file extension, turns whitespace into hyphens,
     * and keeps dots and non-ASCII letters.
     *
     * @since 2.8.3
     *
     * @param string $filename The filename to sanitize.
     * @return string The sanitized filename.
     */
    public static function sanitize_filename( $filename = '' ) {
        if ( empty( $filename ) || ! is_string( $filename ) ) {
            return '';
        }

        $filename = wp_unslash( $filename );

        // Strip the file extension, as the extension always comes from the export format.
        $filename = Export::strip_filename_extension( $filename );

        // Whitespace becomes a hyphen, as sanitize_file_name() does, so "monthly orders" keeps its word break.
        $filename = preg_replace( '/\s+/u', '-', trim( wp_check_invalid_utf8( $filename, true ) ) );

        // Allow letters in any script, digits, dots, underscores, hyphens, percent signs and braces.
        return (string) preg_replace( '/[^\p{L}\p{N}._%{}\-]/u', '', $filename );
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

    /**
     * Build the capability map for the plugin's private export post types.
     *
     * The Export Template post type registers with `map_meta_cap => false`, so every
     * primitive capability WordPress resolves must be listed explicitly. Supplying a
     * capabilities array is itself what turns meta-capability mapping off, and any
     * key left to fall back would name a `*_export_template` capability that nothing
     * in this plugin grants.
     *
     * @since 2.8.1
     *
     * @param string $export_capability Capability every key maps to.
     * @return array Capability map for register_post_type()'s `capabilities` arg.
     */
    public static function export_post_type_capabilities( $export_capability ) {
        return array_fill_keys(
            array(
                'edit_post',
                'read_post',
                'delete_post',
                'edit_posts',
                'edit_others_posts',
                'delete_posts',
                'publish_posts',
                'read_private_posts',
                'create_posts',
            ),
            $export_capability
        );
    }

    /**
     * Whether a Quick Export count gets the post-export upgrade prompt.
     *
     * The prompt shows on the 1st Quick Export and then on every 10th.
     *
     * @since 2.8.3
     * @param int $count Number of successful Quick Exports, this one included.
     * @return bool
     */
    public static function is_post_export_prompt_count( $count ) {
        $count = (int) $count;
        return 1 === $count || ( $count > 0 && 0 === $count % 10 );
    }
}
