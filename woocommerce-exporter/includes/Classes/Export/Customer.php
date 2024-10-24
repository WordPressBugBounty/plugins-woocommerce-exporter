<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Classes\Export
 */

namespace VisserLabs\WSE\Classes\Export;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Traits\Singleton_Trait;
use VisserLabs\WSE\Helpers\Formatting;
use VisserLabs\WSE\Helpers\Export as Export_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Customer export type class.
 *
 * @since 2.7.3
 */
class Customer extends Abstract_Class {

    use Singleton_Trait;

    /**
     * The export type.
     *
     * @var string
     */
    protected $export_type = 'customer';

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
        return woo_ce_get_customer_fields( 'full', $post_id );
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
     * @param object $export The export settings object.
     * @return array
     */
    public function get_object_ids( $export ) {
        global $wpdb;

        $export_args = $export->args;

        $query = "SELECT 
            cl.*,
            max(o.id) AS order_id
            FROM {$wpdb->prefix}wc_customer_lookup cl
                INNER JOIN {$wpdb->prefix}wc_order_stats os ON os.customer_id = cl.customer_id
                INNER JOIN {$wpdb->prefix}wc_orders o ON os.order_id = o.id
            WHERE o.type = 'shop_order'";

        if ( ! empty( $export_args['order_status'] ) ) {
            $sql_placeholders = implode( ',', array_fill( 0, count( $export_args['order_status'] ), '%s' ) );

            $query .= " AND o.status IN ({$sql_placeholders})";
            $query  = $wpdb->prepare( $query, $export_args['order_status'] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        $query .= ' GROUP BY cl.customer_id';

        if ( ! empty( $export_args['order_order'] ) ) {
            $sorting = 'ASC' === $export_args['order_order'] ? 'ASC' : 'DESC';
            $query  .= " ORDER BY o.id {$sorting}";
        }

        if ( ! empty( $export_args['limit_volume'] ) ) {
            $query .= ' LIMIT %d';
            $query  = $wpdb->prepare( $query, $export_args['limit_volume'] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        if ( ! empty( $export_args['offset'] ) ) {
            $query .= ' OFFSET %d';
            $query  = $wpdb->prepare( $query, $export_args['offset'] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        $objects = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        // Order user roles filter.
        if ( ! empty( $export_args['order_user_roles'] ) ) {
            foreach ( $objects as $key => $object ) {
                // Guest customer.
                if ( null === $object['user_id'] ) {
                    // Skip if guest customer is not selected.
                    if ( ! in_array( 'guest', $export_args['order_user_roles'], true ) ) {
                        unset( $objects[ $key ] );
                    }
                    continue;
                }

                $wc_customer   = new \WC_Customer( $object['user_id'] );
                $customer_data = $wc_customer->get_data();
                if ( ! in_array( $customer_data['role'], $export_args['order_user_roles'], true ) ) {
                    unset( $objects[ $key ] );
                }
            }
        }

        $object_ids = wp_list_pluck( $objects, 'order_id' );

        /**
         * Filter the object IDs.
         *
         * @since 2.7.3
         * @param array $object_ids  The object IDs.
         * @param array $export_args The export args.
         */
        return apply_filters( 'wsed_' . $this->export_type . '_object_ids', $object_ids, $export_args );
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
        global $wpdb;

        $dataset = array();
        $fields  = $export->fields;

        $orders_query  = new \WC_Order_Query(
            array(
                'post__in' => $object_ids,
                'limit'    => -1,
            )
        );
        $order_objects = $orders_query->get_orders();

        if ( ! empty( $order_objects ) ) {
            foreach ( $order_objects as $i => $order ) {
                $order_data = $order->get_data();

                // Customer Data.
                if ( 0 !== $order_data['customer_id'] ) {
                    $wc_customer   = new \WC_Customer( $order_data['customer_id'] );
                    $customer_data = $wc_customer->get_data();

                    $data['user_id']   = $order_data['customer_id'];
                    $data['user_name'] = $customer_data['username'];
                    $data['user_role'] = $customer_data['role'];

                    $data['total_spent']  = Formatting::format_price( $wc_customer->get_total_spent() );
                    $data['total_orders'] = $wc_customer->get_order_count();

                    if ( isset( $fields['completed_orders'] ) ) {
                        $customer_completed_orders_query = new \WC_Order_Query(
                            array(
                                'customer_id' => $order_data['customer_id'],
                                'status'      => 'completed',
                                'return'      => 'ids',
                            )
                        );

                        $data['completed_orders'] = count( $customer_completed_orders_query->get_orders() );
                    }
                }

                // Billing.
                $data['billing_full_name']    = $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'];
                $data['billing_first_name']   = $order_data['billing']['first_name'];
                $data['billing_last_name']    = $order_data['billing']['last_name'];
                $data['billing_company']      = $order_data['billing']['company'];
                $data['billing_address']      = $order_data['billing']['address_1'] . ' ' . $order_data['billing']['address_2'];
                $data['billing_address_1']    = $order_data['billing']['address_1'];
                $data['billing_address_2']    = $order_data['billing']['address_2'];
                $data['billing_city']         = $order_data['billing']['city'];
                $data['billing_postcode']     = $order_data['billing']['postcode'];
                $data['billing_state']        = $order_data['billing']['state'];
                $data['billing_state_full']   = Formatting::state_name( $order_data['billing']['country'], $order_data['billing']['state'] );
                $data['billing_country']      = $order_data['billing']['country'];
                $data['billing_country_full'] = Formatting::country_name( $order_data['billing']['country'] );
                $data['billing_phone']        = $order_data['billing']['phone'];
                $data['billing_email']        = $order_data['billing']['email'];

                // Shipping.
                $data['shipping_full_name']    = $order_data['shipping']['first_name'] . ' ' . $order_data['shipping']['last_name'];
                $data['shipping_first_name']   = $order_data['shipping']['first_name'];
                $data['shipping_last_name']    = $order_data['shipping']['last_name'];
                $data['shipping_company']      = $order_data['shipping']['company'];
                $data['shipping_address']      = $order_data['shipping']['address_1'] . ' ' . $order_data['shipping']['address_2'];
                $data['shipping_address_1']    = $order_data['shipping']['address_1'];
                $data['shipping_address_2']    = $order_data['shipping']['address_2'];
                $data['shipping_city']         = $order_data['shipping']['city'];
                $data['shipping_postcode']     = $order_data['shipping']['postcode'];
                $data['shipping_state']        = $order_data['shipping']['state'];
                $data['shipping_state_full']   = Formatting::state_name( $order_data['billing']['country'], $order_data['shipping']['state'] );
                $data['shipping_country']      = $order_data['shipping']['country'];
                $data['shipping_country_full'] = Formatting::country_name( $order_data['shipping']['country'] );

                /**
                 * Filter the Order dataset.
                 *
                 * @since 2.7.3
                 * @param array    $data  The default data array.
                 * @param int      $i     The current order index.
                 * @param WC_Order $order The order object.
                 * @return array
                 */
                $dataset[ $i ] = apply_filters( 'wsed_' . $this->export_type . '_dataset', $data, $i, $order );
            }
        }

        return Export_Helper::parse_dataset( $dataset, $export->columns );
    }

    /**
     * Execute model.
     *
     * @since 2.7.3
     * @access public
     */
    public function run() {
        add_filter( 'wsed_extend_export_dataset_args', array( $this, 'extend_export_dataset_args' ), 10, 3 );
    }
}
