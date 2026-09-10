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
 * Attribute export type class.
 *
 * @since 2.7.3
 */
class Attribute extends Abstract_Class {

    use Singleton_Trait;

    /**
     * The export type.
     *
     * @var string
     */
    protected $export_type = 'attribute';

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
        return woo_ce_get_attribute_fields( 'full', $post_id );
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
        $object_ids = woo_ce_get_attributes( $export->args, $export );

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
     * @param array       $object_ids This batch's slice of hydrated attribute objects from get_object_ids().
     * @param null|object $export The export settings.
     */
    public function get_dataset( $object_ids, $export = null ) {
        // $object_ids is this batch's slice of the fully-hydrated objects returned
        // by get_object_ids(). Parse that slice directly instead of re-fetching the
        // entire attribute set, which duplicated every row once per batch on stores
        // with more than one batch of attributes.
        return Export_Helper::parse_dataset( $object_ids, $export->columns );
    }

    /**
     * Execute model.
     *
     * @since 2.7.3
     * @access public
     */
    public function run() {}
}
