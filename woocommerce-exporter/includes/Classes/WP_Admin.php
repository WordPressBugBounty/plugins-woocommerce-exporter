<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Classes
 */

namespace VisserLabs\WSE\Classes;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Classes\Exporter;
use VisserLabs\WSE\Helpers\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * WP_Admin.
 *
 * @since 2.7.3
 */
class WP_Admin extends Abstract_Class {

    /**
     * Enqueue admin scripts.
     *
     * @param string $hook_suffix The current admin page.
     *
     * @since 2.7.3
     * @return void
     */
    public function admin_enqueue_scripts( $hook_suffix = '' ) {
        if ( 'woocommerce_page_woo_ce' === $hook_suffix ) {
            wp_enqueue_style( 'wsed_export', plugins_url( '/css/export.css', WOO_CE_RELPATH ), array(), Helper::get_plugin_version() );
            wp_enqueue_script( 'wsed_export', plugins_url( '/js/export.js', WOO_CE_RELPATH ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-progressbar' ), Helper::get_plugin_version(), true );
            wp_localize_script(
                'wsed_export',
                'wsed_export_params',
                array(
                    'i18n' => array(
                        'export_preparing'       => esc_html__( 'Preparing export...', 'woocommerce-exporter' ),
                        'export_generating_file' => esc_html__( 'Generating file...', 'woocommerce-exporter' ),
                        'export_complete'        => esc_html__( 'Export complete!', 'woocommerce-exporter' ),
                        'export_error'           => esc_html__( 'An error occurred during the export process. Please try again.', 'woocommerce-exporter' ),
                    ),
                )
            );

            // Enqueue the Vue SPA for tabs that use it. woo_ce_is_spa_tab() is
            // the single shared test (also used by the body renderer and the
            // debug-mode admin notice) so every caller agrees on which tabs the
            // app owns. The resolved tab is still needed for the SPA route,
            // which may include a sub-path (e.g. custom_meta_fields/product).
            $tab = function_exists( 'woo_ce_resolve_current_tab' ) ? woo_ce_resolve_current_tab() : 'overview';
            if ( woo_ce_is_spa_tab() ) {
                $this->enqueue_admin_app( $tab );
            }
        }
    }

    /**
     * Enqueue the Vite-built Vue SPA.
     *
     * Passes an initialRoute so the router starts on the correct view without a
     * round-trip, while still allowing client-side navigation between tabs.
     *
     * @since 2.7.10
     * @param string $tab Current WP admin tab slug (may include a sub-path).
     * @return void
     */
    private function enqueue_admin_app( $tab ) {
        $manifest_path = WOO_CE_PATH . 'build/admin-app/.vite/manifest.json';

        if ( ! file_exists( $manifest_path ) ) {
            return;
        }

        $manifest = json_decode( file_get_contents( $manifest_path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        if ( ! is_array( $manifest ) ) {
            return;
        }

        $entry_key = 'src/js/admin-app/main.js';
        $entry     = $manifest[ $entry_key ] ?? null;
        if ( ! $entry ) {
            return;
        }

        $build_url     = plugins_url( '/build/admin-app/', WOO_CE_RELPATH );
        $base_to_route = array(
            'export_items'       => '/export-items',
            'quick_export'       => '/quick-export',
            'scheduled_export'   => '/scheduled-exports',
            'export_template'    => '/export-templates',
            'archive'            => '/archives',
            'settings'           => '/settings',
            'tools'              => '/tools',
            'custom_meta_fields' => '/custom-meta-fields',
            'audit_log'          => '/audit-log',
        );

        // Resolve the full initial route from tab, including any sub-path.
        // e.g. 'custom_meta_fields/product' → '/custom-meta-fields/product'.
        $tab_parts     = explode( '/', $tab, 2 );
        $tab_base      = $tab_parts[0];
        $tab_sub       = isset( $tab_parts[1] ) ? '/' . $tab_parts[1] : '';
        $base_route    = $base_to_route[ $tab_base ] ?? '';
        $initial_route = $base_route ? $base_route . $tab_sub : '/';

        wp_enqueue_script(
            'wse_admin_app',
            $build_url . $entry['file'],
            array(),
            null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Vite hashes filenames, so the version arg is intentionally null.
            true
        );

        // The entry is marked type="module" via the script_loader_tag filter
        // registered in run(); WordPress has no first-class module API < WP 6.5.

        $export_type_options = array();
        if ( function_exists( 'woo_ce_get_export_types' ) ) {
            foreach ( woo_ce_get_export_types() as $slug => $label ) {
                $is_premium            = woo_ce_is_premium_export_type( $slug );
                $export_type_options[] = array(
                    'value'      => $slug,
                    'label'      => $label,
                    'premium'    => $is_premium,
                    'upsell_url' => $is_premium ? woo_ce_get_export_type_upsell_url( $slug ) : null,
                );
            }
        }

        wp_localize_script(
            'wse_admin_app',
            'wseAdminData',
            array(
                'restUrl'          => esc_url_raw( rest_url( 'wse/v1/' ) ),
                'nonce'            => wp_create_nonce( 'wp_rest' ),
                'initialRoute'     => $initial_route,
                'ajaxUrl'          => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
                'quickExportNonce' => wp_create_nonce( 'manual_export' ),
                'exportTypes'      => $export_type_options,
                // Seeded from the CONSTANT, never the raw option: a site can
                // define WOO_CE_DEBUG in wp-config.php, and bootstrap.php only
                // defines it from the option when it is not already set, so the
                // two can legitimately disagree. The constant is what gates the
                // export, so the constant is what the banner must report.
                'debugMode'        => defined( 'WOO_CE_DEBUG' ) && WOO_CE_DEBUG,
            )
        );

        if ( ! empty( $entry['css'] ) ) {
            foreach ( $entry['css'] as $index => $css_file ) {
                wp_enqueue_style(
                    'wse_admin_app_css_' . $index,
                    $build_url . $css_file,
                    array(),
                    null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Vite hashes filenames, so the version arg is intentionally null.
                );
            }
        }

        // Neutralize WP admin chrome around the SPA so the Vue app owns the
        // full content rectangle (no right-side gap, no WP footer underneath).
        $chrome_css = '
            html.wp-toolbar { background: #F9FAFB; }
            #wpwrap { background: #F9FAFB; }
            #wpcontent { padding-left: 0 !important; background: #F9FAFB; }
            #wpbody { background: #F9FAFB; }
            #wpbody-content { padding-bottom: 0 !important; background: #F9FAFB; }
            #wpbody-content > .notice,
            #wpbody-content > .updated,
            #wpbody-content > .error,
            #wpbody-content > div.update-nag { margin: 16px 22px 0; }
            .wse-spa-wrap {
                margin: 0 !important;
                padding: 0 !important;
                max-width: none !important;
                width: 100% !important;
                box-sizing: border-box;
            }
            #wpfooter { display: none !important; }
            #contextual-help-link-wrap,
            #screen-meta-links { display: none !important; }
            #screen-meta { display: none !important; }
            @media (min-width: 783px) {
                .wse-spa-wrap { min-height: calc(100vh - 32px); }
            }
        ';

        // Attach chrome overrides to the first CSS handle, or register a
        // placeholder handle when the bundle inlines its CSS.
        if ( ! empty( $entry['css'] ) ) {
            wp_add_inline_style( 'wse_admin_app_css_0', $chrome_css );
        } else {
            wp_register_style( 'wse_admin_app_chrome', false, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
            wp_enqueue_style( 'wse_admin_app_chrome' );
            wp_add_inline_style( 'wse_admin_app_chrome', $chrome_css );
        }
    }

    /**
     * Add type="module" to the Vue SPA script tag.
     *
     * The Vite build outputs ES modules (format: 'es'); browsers require
     * type="module" on the tag for dynamic import() to work. WordPress has no
     * first-class API for this before WP 6.5, so the tag is rewritten via
     * script_loader_tag for our handle only.
     *
     * @since 2.7.10
     * @param string $tag    The generated <script> tag.
     * @param string $handle The registered script handle.
     * @return string
     */
    public function add_module_type_to_script( $tag, $handle ) {
        if ( 'wse_admin_app' !== $handle ) {
            return $tag;
        }

        return preg_replace( '/(<script\b)[^>]*(src=[\'"][^\'"]+[\'"][^>]*)>/i', '$1 type="module" $2>', $tag );
    }

    /**
     * Export progress bar modal.
     *
     * @since 2.7.3
     * @return void
     */
    public function export_progress_bar_modal() {
        include_once WOO_CE_VIEWS_PATH . 'quick-export/view-quick-export-progressbar-modal.php';
    }

    /**
     * Handles the quick export ajax process.
     *
     * @since 2.7.3
     * @access public
     */
    public function ajax_quick_export() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'manual_export' ) ) {
            wp_send_json_error( array( 'message' => __( 'Nonce verification failed.', 'woocommerce-exporter' ) ) );
        }

        if ( ! current_user_can( apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' ) ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'woocommerce-exporter' ) ) );
        }

        if ( ! isset( $_POST['type'] ) || ! isset( $_POST['method'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Type or method not specified.', 'woocommerce-exporter' ) ) );
        }

        $export_type = sanitize_text_field( wp_unslash( $_POST['type'] ) );

        // Premium export types are Deluxe-only; block them server-side so the
        // free plugin cannot generate their datasets even if the request is forged.
        if ( woo_ce_is_premium_export_type( $export_type ) ) {
            woo_ce_send_premium_type_error( $export_type );
        }

        // Debugging mode refuses the whole runner — prepare, batch and finalise
        // alike — so no export file can be generated while it is on. Clearing
        // the in-progress flag here is what stops debug flipped on mid-export
        // from leaving the 30-minute _export_running transient set and locking
        // out every later export.
        if ( defined( 'WOO_CE_DEBUG' ) && WOO_CE_DEBUG ) {
            $debug_instance = new Exporter();
            $debug_instance->clear_export_transient();

            $debug_form_data = array();
            // is_string() is required, not defensive: form_data[]=x makes
            // parse_str() throw a TypeError on PHP 8 and turns the intended
            // JSON refusal into an uncaught fatal.
            if ( isset( $_POST['form_data'] ) && is_string( $_POST['form_data'] ) ) {
                parse_str( wp_unslash( $_POST['form_data'] ), $debug_form_data ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- the query string is parsed here and the individual values read below are sanitized.
            }
            $debug_format = isset( $debug_form_data['export_format'] ) && is_scalar( $debug_form_data['export_format'] )
                ? sanitize_text_field( (string) $debug_form_data['export_format'] )
                : '';
            $debug_fields = isset( $debug_form_data[ $export_type . '_fields' ] ) && is_array( $debug_form_data[ $export_type . '_fields' ] )
                ? count( $debug_form_data[ $export_type . '_fields' ] )
                : null;

            woo_ce_send_debug_mode_error( $export_type, $debug_format, $debug_fields );
        }

        $instance = new Exporter();
        $instance->set_exporter( $export_type );
        // On first call (ajax_prepare), set the form data.
        if ( 'prepare' === sanitize_text_field( wp_unslash( $_POST['method'] ) ) ) {
            $instance->set_form_data( $_POST['form_data'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

            // Premium export formats are Deluxe-only; block them here, before
            // ajax_prepare() sets the 30-minute _export_running transient, so a
            // forged premium-format request can never lock out a legitimate
            // CSV/TSV export that follows it.
            $requested_format = $instance->get_form_data()['export_format'] ?? '';
            $requested_format = is_scalar( $requested_format ) ? (string) $requested_format : '';
            if ( woo_ce_is_premium_export_format( $requested_format ) ) {
                woo_ce_send_premium_format_error( $requested_format );
            }
        }

        $method = 'ajax_' . sanitize_text_field( wp_unslash( $_POST['method'] ) );
        if ( ! method_exists( $instance, $method ) ) {
            // translators: %s: method name.
            wp_send_json_error( array( 'message' => sprintf( __( 'Unknown AJAX method %s.', 'woocommerce-exporter' ), esc_html( $method ) ) ) );
        }

        $instance->$method();
        wp_die();
    }

    /**
     * Delete an archived export's file from the protected export directory.
     *
     * Archived exports live outside the uploads tree, so WordPress's own
     * wp_delete_attachment() cleanup (which only removes files inside the
     * uploads directory) leaves the file behind.
     *
     * @since 2.8.1
     * @access public
     *
     * @param int $post_id The attachment being deleted.
     * @return void
     */
    public function delete_archive_file( $post_id ) {
        if ( ! metadata_exists( 'post', $post_id, '_woo_export_type' ) ) {
            return;
        }

        $file = get_attached_file( $post_id );
        if ( ! $file ) {
            return;
        }

        // Resolve before comparing: a stored "../" path must never reach the delete sink.
        $real_path = realpath( $file );
        $real_dir  = realpath( Exporter::EXPORT_DIR );
        if ( false === $real_path || false === $real_dir || 0 !== strpos( $real_path, $real_dir . DIRECTORY_SEPARATOR ) ) {
            return;
        }

        wp_delete_file( $real_path );
    }

    /**
     * Serve and download the generated file.
     *
     * @since 2.7.3
     * @access public
     */
    public function download_export_file() {
        if (
            isset( $_GET['action'], $_GET['nonce'] ) &&
            wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'woo_ce_download_file' ) &&
            'download_file' === sanitize_text_field( wp_unslash( $_GET['action'] ) )
        ) {
            // A nonce is a per-user CSRF token, not an authorisation check, so gate
            // the download on the same capability that guards the exporter admin UI
            // before any file is read, streamed, or deleted.
            if ( ! current_user_can( apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' ) ) ) {
                wp_die(
                    esc_html__( 'You do not have permission to download this file.', 'woocommerce-exporter' ),
                    esc_html__( 'Forbidden', 'woocommerce-exporter' ),
                    array( 'response' => 403 )
                );
            }

            $export_type = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';

            // Clear the in-progress flag up front: the premium gates below exit early,
            // and leaving it set would block every subsequent export for 30 minutes.
            $instance = new Exporter();
            $instance->clear_export_transient();

            // Debugging mode gates this second entry point too: a file_url
            // issued before debug was enabled, or a replayed download nonce,
            // must not still serve a real export file.
            if ( defined( 'WOO_CE_DEBUG' ) && WOO_CE_DEBUG ) {
                woo_ce_send_debug_mode_error(
                    $export_type,
                    isset( $_GET['format'] ) ? sanitize_text_field( wp_unslash( $_GET['format'] ) ) : ''
                );
            }

            // Premium export types are Deluxe-only; refuse to serve their files.
            if ( woo_ce_is_premium_export_type( $export_type ) ) {
                woo_ce_send_premium_type_error( $export_type );
            }

            $requested_format = isset( $_GET['format'] ) ? sanitize_text_field( wp_unslash( $_GET['format'] ) ) : '';

            // Premium export formats are Deluxe-only; refuse to serve their files.
            if ( woo_ce_is_premium_export_format( $requested_format ) ) {
                woo_ce_send_premium_format_error( $requested_format );
            }

            $instance->set_exporter( $export_type );

            if ( empty( $_GET['filename'] ) ) {
                wp_send_json_error( array( 'message' => __( 'Filename not specified.', 'woocommerce-exporter' ) ) );
            }

            // Reduce the requested filename to its basename so a path-traversal
            // payload (e.g. "../../../wp-config.php") cannot carry directory
            // components out of the export directory.
            $filename = basename( sanitize_text_field( wp_unslash( $_GET['filename'] ) ) );

            $instance->set_export_settings( get_transient( WOO_CE_PREFIX . '_export_settings' ) );
            // Coerce through the same allowlist that guards Abstract_Exporter's
            // writers, so an empty or unknown format can never reach them and
            // silently emit a 0-byte file.
            $instance->set_export_format( woo_ce_resolve_export_format( $requested_format ) );
            $instance->set_export_encoding( isset( $_GET['encoding'] ) ? sanitize_text_field( wp_unslash( $_GET['encoding'] ) ) : '' );
            $instance->set_filename( $filename );

            // Defence in depth: confirm the resolved path genuinely lives inside the
            // export directory before it reaches the read/stream/unlink sinks in
            // export(). realpath() returns false for a path that does not resolve
            // (including a traversal that escaped or a missing file), which is
            // rejected too.
            $real_path = realpath( $instance->get_file_path( $filename ) );
            $real_dir  = realpath( Exporter::EXPORT_DIR );

            if (
                false === $real_path ||
                false === $real_dir ||
                0 !== strpos( $real_path, $real_dir . DIRECTORY_SEPARATOR )
            ) {
                wp_die(
                    esc_html__( 'The requested file could not be found.', 'woocommerce-exporter' ),
                    esc_html__( 'Not Found', 'woocommerce-exporter' ),
                    array( 'response' => 404 )
                );
            }

            $instance->clear_export_transient();
            $instance->export();
        }
    }

    /**
     * Option to set batch size.
     *
     * @since 2.7.3
     * @access public
     */
    public function batch_size_option() {
        include_once WOO_CE_VIEWS_PATH . 'quick-export/view-batch-size-option.php';
    }

    /**
     * Execute model.
     *
     * @since 2.7.3
     * @access public
     */
    public function run() {
        // Enqueue admin scripts.
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

        // Rewrite the SPA script tag to type="module" for ES module support.
        add_filter( 'script_loader_tag', array( $this, 'add_module_type_to_script' ), 10, 2 );

        // Add export progress bar modal.
        add_action( 'woo_ce_export_after_form', array( $this, 'export_progress_bar_modal' ), 99 );

        // Register AJAX action handler for quick export.
        add_action( 'wp_ajax_woo_ce_quick_export', array( $this, 'ajax_quick_export' ) );

        // Remove an archived export's file when its attachment is deleted.
        add_action( 'delete_attachment', array( $this, 'delete_archive_file' ) );

        // Add batch size option.
        add_action( 'woo_ce_export_options', array( $this, 'batch_size_option' ), 99 );

        // Download the export file.
        add_action( 'admin_init', array( $this, 'download_export_file' ) );
    }
}
