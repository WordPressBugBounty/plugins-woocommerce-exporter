<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Classes\Export
 */

namespace VisserLabs\WSE\Classes\Export;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Traits\Singleton_Trait;
use VisserLabs\WSE\Helpers\Export as Export_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Commission export type class.
 *
 * @since 2.7.3
 */
class Commission extends Abstract_Class {

    use Singleton_Trait;

    /**
     * The export type.
     *
     * @var string
     */
    private $export_type = 'commission';

    /**
     * Constructor.
     *
     * @var array
     */
    public function __construct() {}

    /**
     * Get default fields.
     *
     * @since 2.7.3
     * @access public
     *
     * @param int $post_id The post ID.
     * @return array
     */
    public function get_default_fields( $post_id = 0 ) {
        return woo_ce_get_commission_fields( 'full', $post_id );
    }

    /**
     * Extend export dataset args.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array  $args     The export dataset args.
     * @param object $export   The export settings.
     * @param array  $settings Raw export settings obtained from the form or the post data.
     * @return array
     */
    public function extend_export_dataset_args( $args, $export, $settings ) {
        /**
         * Filter the dataset args.
         * This filter is old way of extending the dataset args.
         * Should be refactored in the future for better performance & readability.
         */
        if ( $export->scheduled_export ) {
            $args = apply_filters( 'woo_ce_extend_cron_dataset_args', $args, $export->post_id, $export->type, true );
        } else {
            $args = apply_filters( 'woo_ce_extend_dataset_args', $args, $export->type, $settings );
        }
        return $args;
    }

    /**
     * Get object ids.
     *
     * @since 2.7.3
     * @access public
     *
     * @param object $export The export settings.
     * @return array
     */
    public function get_object_ids( $export ) {
        $object_ids = woo_ce_get_commissions( $export->args, $export );

        /**
         * Filter the object IDs.
         *
         * @since 2.7.3
         * @param array $object_ids  The object IDs.
         * @param array $export_args The export args.
         */
        return apply_filters( 'wsed_' . $this->export_type . '_object_ids', $object_ids, $export->args );
    }

    /**
     * Get dataset to export.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array       $object_ids The object IDs.
     * @param null|object $export The export settings.
     */
    public function get_dataset( $object_ids, $export = null ) {
        $dataset = array();
        $columns = $export->columns;

        foreach ( $object_ids as $object_id ) {
            $dataset[] = woo_ce_get_commission_data( $object_id, $export->args, $export );
        }

        return Export_Helper::parse_dataset( $dataset, $columns );
    }

    /**
     * Execute model.
     *
     * @since 2.7.3
     * @access public
     */
    public function run() {
        if ( ! is_plugin_active( 'wc-vendors/class-wc-vendors.php' ) ) {
            return;
        }

        add_filter( 'wsed_extend_export_dataset_args', array( $this, 'extend_export_dataset_args' ), 10, 3 );
    }
}
