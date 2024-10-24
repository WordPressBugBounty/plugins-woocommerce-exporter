<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Classes
 */

namespace VisserLabs\WSE\Classes;

use Exception;
use VisserLabs\WSE\Abstracts\Abstract_Exporter;
use VisserLabs\WSE\Traits\Singleton_Trait;

defined( 'ABSPATH' ) || exit;

/**
 * Quick Export class.
 *
 * @since 2.7.3
 */
class Exporter extends Abstract_Exporter {

    use Singleton_Trait;

    /**
     * The exporter.
     *
     * @var object
     */
    protected $_exporter = null;

    /**
     * The export type.
     *
     * @var string
     */
    protected $export_type = '';

    /**
     * Export settings.
     *
     * @var object
     */
    protected $export_settings;

    /**
     * The filename.
     *
     * @var string
     */
    protected $filename = '';

    /**
     * The default export fields.
     *
     * @var array
     */
    protected $default_fields = array();

    /**
     * The export fields.
     *
     * @var array
     */
    protected $fields = array();

    /**
     * Constructor.
     *
     * @since 2.7.3
     * @access public
     */
    public function __construct() {}

    /***********************************************************************
     * AJAX Actions
     **********************************************************************/

    /**
     * Prepare exporter.
     *
     * @since 2.7.3
     * @access public
     *
     * @throws Exception If the exporter is not found.
     */
    public function ajax_prepare() {
        try {
            $form_data = $this->get_form_data();

            if ( empty( $form_data ) ) {
                throw new Exception( __( 'No data was received.', 'woocommerce-exporter' ) );
            }

            if ( get_transient( WOO_CE_PREFIX . '_export_running' ) ) {
                throw new Exception( __( 'An export is already running.', 'woocommerce-exporter' ) );
            }

            // Set transient.
            set_transient( WOO_CE_PREFIX . '_export_running', true );

            $this->prepare_server();

            // Get default fields.
            $this->default_fields = $this->_exporter->get_default_fields();

            // Setup & set the export settings.
            $export = $this->setup_export_settings( $form_data );

            // Exit if no export fields were selected.
            if ( empty( $export->columns ) ) {
                throw new Exception( __( 'No export fields were selected and we could not default to all fields, please try again with at least a single export field.', 'woocommerce-exporter' ) );
            }

            $this->set_export_settings( $export );

            // Set the filename.
            $this->set_filename( $export->filename );

            $this->set_export_type( $export->type );
            $this->set_export_format( $export->export_format );
            $this->set_export_encoding( $export->encoding );

            // Save export form fields to the database.
            $this->save_export_fields( $export, $form_data );

            // Prepare the spreadsheet.
            $this->prepare_file();

            $object_ids = $this->_exporter->get_object_ids( $export );
            if ( empty( $object_ids ) ) {
                throw new Exception( __( 'No objects found to export.', 'woocommerce-exporter' ) );
            }

            // Generate batches in array.
            $batch_size = apply_filters( 'wsed_quick_export_batch_size', $export->batch_size, $export->type );
            $batch      = array_chunk( $object_ids, $batch_size );
            $batch      = array_combine( range( 1, count( $batch ) ), array_values( $batch ) );

            set_transient( WOO_CE_PREFIX . '_export_settings', $this->export_settings );
            set_transient( WOO_CE_PREFIX . '_export_filename', $this->filename );
            set_transient( WOO_CE_PREFIX . '_export_batch', $batch );
            set_transient( WOO_CE_PREFIX . '_total_exported', 0 );

            wp_send_json_success(
                array(
                    'total'   => count( $object_ids ),
                    'batches' => array_keys( $batch ),
                )
            );
        } catch ( Exception $e ) {
            $this->clear_export_transient();
            wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }

    /**
     * Get data in batches for quick export.
     *
     * @since 2.7.3
     * @access public
     *
     * @throws Exception If the export arguments are not found.
     */
    public function ajax_batch() {
        try {
            /**
             * Unfortunatelly, we can't use the global $export variable here, because it's not available in this scope.
             * Because the global variables are not persistent across different requests.
             * So we have to reassign the $export variable from the transient to the global scope.
             * This is the only way to do it currently without changing the whole logic of the plugin.
             */
            global $export;
            $export = get_transient( WOO_CE_PREFIX . '_export_settings' );

            if ( empty( $export ) ) {
                throw new Exception( __( 'Export arguments not found.', 'woocommerce-exporter' ) );
            }

            $filename = get_transient( WOO_CE_PREFIX . '_export_filename' );
            $batch    = get_transient( WOO_CE_PREFIX . '_export_batch' );
            $exported = get_transient( WOO_CE_PREFIX . '_total_exported' );

            $this->set_export_type( $_REQUEST['type'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $this->set_export_settings( $export );
            $this->set_export_batch( $batch );
            $this->set_export_columns( $export->columns );
            $this->set_export_format( $export->export_format );
            $this->set_export_encoding( $export->encoding );
            $this->set_filename( $filename );

            $step = isset( $_REQUEST['step'] ) ? absint( $_REQUEST['step'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

            // Get dataset to export.
            $dataset = $this->_exporter->get_dataset( $batch[ $step ], $export );

            // Write data to file.
            $this->write_data_in_chunks( $dataset, $step );

            // Set total exported.
            $total_exported = $exported + count( $batch[ $step ] );
            set_transient( WOO_CE_PREFIX . '_total_exported', $total_exported );

            // If last batch, generate download link. Otherwise, return next step.
            if ( $step >= count( $batch ) ) {
                $query_args = apply_filters(
                    'woo_ce_download_file_query_args',
                    array(
                        'type'     => $this->export_type,
                        'nonce'    => wp_create_nonce( 'woo_ce_download_file' ),
                        'action'   => 'download_file',
                        'format'   => $export->export_format,
                        'encoding' => $export->encoding,
                        'filename' => $filename,
                    )
                );

                wp_send_json_success(
                    array(
                        'done'     => true,
                        'file_url' => add_query_arg( $query_args, admin_url( 'admin.php?page=woo_ce&tab=export' ) ),
                    )
                );
            } else {
                wp_send_json_success(
                    array(
                        'step'     => ++$step,
                        'exported' => $total_exported,
                    )
                );
            }
        } catch ( Exception $e ) {
            $this->clear_export_transient();
            wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }

    /**
     * Clear export.
     *
     * @since 2.7.3
     * @access public
     */
    public function ajax_clear_export() {
        $this->clear_export_transient();
        wp_send_json_success( array( 'message' => __( 'Export cleared.', 'woocommerce-exporter' ) ) );
    }

    /**
     * Save export fields.
     * Update options changes made on the Export screen.
     *
     * @since 2.7.3
     * @param object $export    The export settings.
     * @param array  $form_data The form data from front end export form.
     */
	public function save_export_fields( $export, $form_data ) {
        if ( ! empty( $form_data['dataset'] ) ) {
            update_option( WOO_CE_PREFIX . '_last_export', $form_data['dataset'] );
        }

		// Default fields.
        $fields = $export->fields;
		if ( false === $fields && ! is_array( $fields ) ) {
			$fields = array();
        }
        if ( ! empty( $fields ) ) {
            update_option( WOO_CE_PREFIX . "_{$this->export_type}_fields", array_map( 'sanitize_text_field', (array) $fields ) );
        }

        // Default sorting.
        $sorting = $export->fields_order;
		if ( false === $sorting && ! is_array( $sorting ) ) {
			$sorting = array();
        }
        if ( ! empty( $sorting ) ) {
            update_option( WOO_CE_PREFIX . "_{$this->export_type}_sorting", array_map( 'absint', (array) $sorting ) );
        }

        if ( in_array( $this->export_type, array( 'product', 'category', 'tag', 'brand', 'order' ), true ) ) {
            $export->description_excerpt_formatting = ( isset( $form_data['description_excerpt_formatting'] ) ? absint( $form_data['description_excerpt_formatting'] ) : false );
            update_option( WOO_CE_PREFIX . 'description_excerpt_formatting', $export->description_excerpt_formatting );
        }

        if ( ! empty( $form_data['export_format'] ) ) {
            update_option( WOO_CE_PREFIX . '_export_format', sanitize_text_field( $form_data['export_format'] ) );
        } else {
            delete_option( WOO_CE_PREFIX . '_export_format' );
        }

        if ( ! empty( $form_data['export_template'] ) ) {
            update_option( WOO_CE_PREFIX . '_export_template', absint( $form_data['export_template'] ) );
        } else {
            delete_option( WOO_CE_PREFIX . '_export_template' );
        }

        if ( ! empty( $form_data['limit_volume'] ) ) {
            update_option( WOO_CE_PREFIX . '_limit_volume', sanitize_text_field( $form_data['limit_volume'] ) );
        } else {
            delete_option( WOO_CE_PREFIX . '_limit_volume' );
        }

        if ( ! empty( $form_data['offset'] ) ) {
            update_option( WOO_CE_PREFIX . '_offset', sanitize_text_field( $form_data['offset'] ) );
        } else {
            delete_option( WOO_CE_PREFIX . '_offset' );
        }

        if ( ! empty( $form_data['batch_size'] ) ) {
            update_option( WOO_CE_PREFIX . '_batch_size', sanitize_text_field( $form_data['batch_size'] ) );
        } else {
            delete_option( WOO_CE_PREFIX . '_batch_size' );
        }

        /**
         * Save export fields.
         *
         * @since 2.7.3
         *
         * @param object $export    The export settings.
         * @param array  $form_data The form data from front end export form.
         */
        do_action( 'wsed_save_export_fields', $export, $form_data );
	}

    /**
     * Clear export transient.
     *
     * @since 2.7.3
     * @access public
     */
    public function clear_export_transient() {
        delete_transient( WOO_CE_PREFIX . '_export_running' );
        delete_transient( WOO_CE_PREFIX . '_export_args' );
        delete_transient( WOO_CE_PREFIX . '_export_batch' );
        delete_transient( WOO_CE_PREFIX . '_export_filename' );
        delete_transient( WOO_CE_PREFIX . '_total_exported' );
    }

    /**
     * Execute model.
     *
     * @since 2.7.3
     * @access public
     */
    public function run() {}
}
