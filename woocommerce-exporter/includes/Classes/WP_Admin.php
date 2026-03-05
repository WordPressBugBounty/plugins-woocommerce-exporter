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
     * @since 3.0
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
        }
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

        $instance = new Exporter();
        $instance->set_exporter( sanitize_text_field( wp_unslash( $_POST['type'] ) ) );
        // On first call (ajax_prepare), set the form data.
        if ( 'prepare' === sanitize_text_field( wp_unslash( $_POST['method'] ) ) ) {
            $instance->set_form_data( $_POST['form_data'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
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
	 * Serve and download the generated file.
     *
     * @since 2.7.3
     * @access public
     */
	public function download_export_file() {
        if (
            isset( $_GET['action'], $_GET['nonce'] ) &&
            wp_verify_nonce( wp_unslash( $_GET['nonce'] ), 'woo_ce_download_file' ) &&
            'download_file' === wp_unslash( $_GET['action'] )
        ) {
            $instance = new Exporter();
            $instance->set_exporter( isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '' );

            if ( empty( $_GET['filename'] ) ) {
                wp_send_json_error( array( 'message' => __( 'Filename not specified.', 'woocommerce-exporter' ) ) );
            }

            $instance->set_export_settings( get_transient( WOO_CE_PREFIX . '_export_settings' ) );
            $instance->set_export_format( isset( $_GET['format'] ) ? sanitize_text_field( wp_unslash( $_GET['format'] ) ) : '' );
            $instance->set_export_encoding( isset( $_GET['encoding'] ) ? sanitize_text_field( wp_unslash( $_GET['encoding'] ) ) : '' );
            $instance->set_filename( sanitize_text_field( wp_unslash( $_GET['filename'] ) ) );
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

        // Add export progress bar modal.
        add_action( 'woo_ce_export_after_form', array( $this, 'export_progress_bar_modal' ), 99 );

        // Register AJAX action handler for quick export.
        add_action( 'wp_ajax_woo_ce_quick_export', array( $this, 'ajax_quick_export' ) );

        // Add batch size option.
		add_action( 'woo_ce_export_options', array( $this, 'batch_size_option' ), 99 );

        // Download the export file.
        add_action( 'admin_init', array( $this, 'download_export_file' ) );
    }
}
