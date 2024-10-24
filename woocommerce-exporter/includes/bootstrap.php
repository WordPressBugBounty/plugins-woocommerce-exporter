<?php
// Disable basic Store Exporter if it is activated.
require_once WOO_CE_PATH . 'common/common.php';
do_action( 'woo_ce_loaded' );

require_once WOO_CE_PATH . 'includes/functions.php';

if ( is_admin() ) {

    /* Start of: WordPress Administration */

    // Register our install script for first time install.
    include_once WOO_CE_PATH . 'includes/install.php';

    /**
     * Initial scripts and export process.
     */
    function woo_cd_admin_init() {

        global $export, $wp_roles;

        woo_ce_load_export_types();

        $action = ( function_exists( 'woo_get_action' ) ? woo_get_action() : false );

        $troubleshooting_url = 'https://visser.com.au/support/';

        // An effort to reduce the memory load at export time.
        if ( 'export' !== $action ) {

            // Check the User has the activate_plugins capability.
            $user_capability = 'activate_plugins';
            if ( current_user_can( $user_capability ) ) {

                // Detect if another e-Commerce platform is activated.
                if (
                    ! woo_is_woo_activated() &&
                    (
                        woo_is_jigo_activated() ||
                        woo_is_wpsc_activated()
                    )
                ) {
                    $message  = __( 'We have detected another e-Commerce Plugin than WooCommerce activated, please check that you are using Store Exporter Deluxe for the correct platform.', 'woocommerce-exporter' );
                    $message .= sprintf( ' <a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
                    woo_cd_admin_notice( $message, 'error', 'plugins.php' );
                    return;
                } elseif ( ! woo_is_woo_activated() ) {
                    $message  = __( 'We have been unable to detect the WooCommerce Plugin activated on this WordPress site, please check that you are using Store Exporter Deluxe for the correct platform.', 'woocommerce-exporter' );
                    $message .= sprintf( ' <a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
                    woo_cd_admin_notice( $message, 'error', 'plugins.php' );
                    return;
                }

                // Detect if any known conflict Plugins are activated.

                // WooCommerce Subscriptions Exporter - http://codecanyon.net/item/woocommerce-subscription-exporter/6569668.
                if ( function_exists( 'wc_subs_exporter_admin_init' ) ) {
                    $message  = __( 'We have detected an activated Plugin for WooCommerce that is known to conflict with Store Exporter Deluxe, please de-activate WooCommerce Subscriptions Exporter to resolve export issues within Store Exporter Deluxe.', 'woocommerce-exporter' );
                    $message .= sprintf( '<a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
                    woo_cd_admin_notice( $message, 'error', array( 'plugins.php', 'admin.php' ) );
                }

                // WP Easy Events Professional - https://emdplugins.com/plugins/wp-easy-events-professional/.
                if ( class_exists( 'WP_Easy_Events_Professional' ) ) {
                    $message  = __( 'We have detected an activated Plugin that is known to conflict with Store Exporter Deluxe, please de-activate WP Easy Events Professional to resolve export issues within Store Exporter Deluxe.', 'woocommerce-exporter' );
                    $message .= sprintf( '<a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
                    woo_cd_admin_notice( $message, 'error', array( 'plugins.php', 'admin.php' ) );
                }

                // Plugin row notices for the Plugins screen.
                add_action( 'after_plugin_row_' . WOO_CE_RELPATH, 'woo_ce_admin_plugin_row' );

            }

            // Load Dashboard widget for Scheduled Exports.
            add_action( 'wp_dashboard_setup', 'woo_ce_admin_dashboard_setup' );

            // Check the User has the view_woocommerce_reports capability.
            $user_capability = apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' );
            if ( current_user_can( $user_capability ) === false ) {
                return;
            }

            // Migrate scheduled export to CPT.
            if ( get_option( 'woo_ce_auto_format', false ) !== false ) {
                if ( woo_ce_legacy_scheduled_export() ) {
                    $message = __( 'We have detected Scheduled Exports from an earlier release of Store Exporter Deluxe, they have been updated it to work with the new multiple scheduled export engine in Store Exporter Deluxe. Please open WooCommerce &raquo; Store Export &raquo; Settings &raquo; Scheduled Exports to see what\'s available.', 'woocommerce-exporter' );
                    woo_cd_admin_notice( $message );
                }
            }

            // Check that we are on the Store Exporter screen.
            $page = ( isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : false ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( strtolower( WOO_CE_PREFIX ) !== $page ) {
                return;
            }

            // Add memory usage to the screen footer of the WooCommerce > Store Export screen.
            add_filter( 'admin_footer_text', 'woo_ce_admin_footer_text' );

            woo_ce_export_init();

        }

        // Process any pre-export notice confirmations.
        switch ( $action ) {
            // Save changes on Settings screen.
            case 'save-settings':
                woo_ce_export_settings_save();
                break;

            // Save changes on Field Editor screen.
            case 'save-fields':
                // We need to verify the nonce.
                if ( ! empty( $_POST ) && check_admin_referer( 'save_fields', 'woo_ce_save_fields' ) ) {
                    $fields       = ( isset( $_POST['fields'] ) ? array_filter( $_POST['fields'] ) : array() );
                    $fields       = array_map( 'stripslashes', (array) $fields );
                    $hidden       = ( isset( $_POST['hidden'] ) ? array_filter( $_POST['hidden'] ) : array() );
                    $export_type  = ( isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '' );
                    $export_types = array_keys( woo_ce_get_export_types() );
                    // Check we are saving against a valid export type.
                    if ( in_array( $export_type, $export_types, true ) ) {
                        woo_ce_update_option( $export_type . '_labels', $fields );
                        woo_ce_update_option( $export_type . '_hidden', $hidden );
                        $message = __( 'Field labels have been saved.', 'woocommerce-exporter' );
                        woo_cd_admin_notice( $message );
                    } else {
                        $message = __( 'Changes could not be saved as we could not detect a valid export type. Raise this as a Support issue and include what export type you were editing.', 'woocommerce-exporter' );
                        woo_cd_admin_notice( $message, 'error' );
                    }
                }
                break;

        }
    }
    add_action( 'admin_init', 'woo_cd_admin_init', 11 );

    /**
     * HTML templates and form processor for Store Exporter Deluxe screen.
     */
    function woo_cd_html_page() {

        // Check the User has the view_woocommerce_reports capability.
        $user_capability = apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' );
        if ( current_user_can( $user_capability ) === false ) {
            return;
        }

        global $wpdb, $export;

        $title = apply_filters( 'woo_ce_template_header', __( 'Store Exporter', 'woocommerce-exporter' ) );
        woo_cd_template_header( $title );
        $action = ( function_exists( 'woo_get_action' ) ? woo_get_action() : false );
        switch ( $action ) {

            case 'export':
                if ( WOO_CE_DEBUG ) {
                    $export_log = get_transient( WOO_CE_PREFIX . '_debug_log' );
                    if ( false === $export_log ) {
                        $export_log = __( 'No export entries were found within the debug Transient, please try again with different export filters.', 'woocommerce-exporter' );
                    } else {
                        // We take the contents of our WordPress Transient and de-base64 it back to CSV format.
                        $export_log = base64_decode( $export_log ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
                    }
                    delete_transient( WOO_CE_PREFIX . '_debug_log' );
                    // phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_print_r
                    // translators: %s: export file name.
                    $output = '<h3>' . sprintf( __( 'Export Details: %s', 'woocommerce-exporter' ), esc_attr( $export->filename ) ) . '</h3>
                        <p>' . __( 'This prints the $export global that contains the different export options and filters to help reproduce this on another instance of WordPress. Very useful for debugging blank or unexpected exports.', 'woocommerce-exporter' ) . '</p>
                        <textarea id="export_log">' . esc_textarea( print_r( $export, true ) ) . '</textarea>
                        <hr />
                    ';
                    // phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_print_r
                    if ( in_array( $export->export_format, array( 'csv', 'tsv', 'xls' ), true ) ) {
                        $output .= '<script type="text/javascript">
                                $j(function() {
                                    $j(\'#export_sheet\').CSVToTable(\'\', {
                                        startLine: 0';
                                            if ( in_array( $export->export_format, array( 'tsv', 'xls', 'xlsx' ), true ) ) {
                                                $output .= ',
                                        separator: "\t"';
                                            }
                                            $output .= '
                                    });
                                });
                            </script>
                            <h3>' . __( 'Export', 'woocommerce-exporter' ) . '</h3>
                            <p>' . __( 'We use the <a href="http://code.google.com/p/jquerycsvtotable/" target="_blank"><em>CSV to Table plugin</em></a> to see first hand formatting errors or unexpected values within the export file.', 'woocommerce-exporter' ) . '</p>
                            <div id="export_sheet">' . esc_textarea( $export_log ) . '</div>
                            <p class="description">' . __( 'This jQuery plugin can fail with <code>\'Item count (#) does not match header count\'</code> notices which simply mean the number of headers detected does not match the number of cell contents.', 'woocommerce-exporter' ) . '</p>
                            <hr />
                        ';
                    }
                    $output .= '<h3>' . __( 'Export Log', 'woocommerce-exporter' ) . '</h3>
                        <p>' . __( 'This prints the raw export contents and is helpful when the jQuery plugin above fails due to major formatting errors.', 'woocommerce-exporter' ) . '</p>
                        <textarea id="export_log" wrap="off">' . esc_textarea( $export_log ) . '</textarea>
                        <hr />
                    ';
                    echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }

                woo_cd_manage_form();
                break;

            case 'update':
                woo_ce_admin_custom_fields_save();

                $message = __( 'Custom field changes saved. You can now select those additional fields from the Export Fields list. Click the Configure link within the Export Fields section to change the label of your newly added export fields.', 'woocommerce-exporter' );
                woo_cd_admin_notice_html( $message );
                woo_cd_manage_form();
                break;

            default:
                woo_cd_manage_form();
                break;

        }
        woo_cd_template_footer();
    }

    /**
     * HTML template for Export screen.
     */
    function woo_cd_manage_form() {

        $tab = ( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : false ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        // If Skip Overview is set then jump to Export screen.
        if ( false === $tab && woo_ce_get_option( 'skip_overview', false ) ) {
            $tab = 'export';
        }

        // Check that WC() is available.
        if ( ! function_exists( 'WC' ) ) {
            $message = __( 'We couldn\'t load the WooCommerce resource WC(), check that WooCommerce is installed and active. If this persists get in touch with us.', 'woocommerce-exporter' );
            woo_cd_admin_notice_html( $message, 'error' );
            return;
        }

        woo_ce_load_export_types();
        woo_ce_admin_fail_notices();

        include_once WOO_CE_PATH . 'templates/admin/tabs.php';
    }

    /* End of: WordPress Administration */

}

/**
 * Run this function within the WordPress Administration and storefront to ensure Scheduled Exports happen.
 */
function woo_ce_init() {

    include_once WOO_CE_PATH . 'includes/functions.php';
    if ( function_exists( 'woo_ce_register_export_template_cpt' ) ) {
        woo_ce_register_export_template_cpt();
    }

    // Set the Plugin debug and logging levels if not already set.
    if ( ! defined( 'WOO_CE_DEBUG' ) ) {
        $debug_mode = woo_ce_get_option( 'debug_mode', 0 );
        define( 'WOO_CE_DEBUG', $debug_mode );
    }

    if ( ! WOO_CE_DEBUG && ! defined( 'WOO_CE_LOGGING' ) ) {
        // This should be off by default in production environments.
        $logging_mode = woo_ce_get_option( 'logging_mode', 0 );
        define( 'WOO_CE_LOGGING', $logging_mode );
    } elseif ( WOO_CE_DEBUG && ! defined( 'WOO_CE_LOGGING' ) ) {
        // Default to turn on logging mode when debug mode is enabled.
        $logging_mode = woo_ce_get_option( 'logging_mode', 1 );
        define( 'WOO_CE_LOGGING', $logging_mode );
    }
}
add_action( 'init', 'woo_ce_init', 11 );
