<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Classes\Export
 */

namespace VisserLabs\WSE\Classes\Export;

use DateTime;
use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Traits\Singleton_Trait;
use VisserLabs\WSE\Classes\Export\Product;
use VisserLabs\WSE\Helpers\Formatting;
use VisserLabs\WSE\Helpers\Export as Export_Helper;

// Woocommerce.
use WC_Order_Query;
use WC_Tax;
use WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Order export type class.
 *
 * @since 2.7.3
 */
class Order extends Abstract_Class {

    use Singleton_Trait;

    /**
     * The export type.
     *
     * @var string
     */
    private $export_type = 'order';

    /**
     * Order item fields.
     *
     * @var array
     */
    private $order_item_fields = array();

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
        $fields = array(
            array(
                'name'  => 'purchase_id',
                'label' => __( 'Order ID', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'post_id',
                'label' => __( 'Post ID', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'purchase_total',
                'label' => __( 'Order Total', 'woocommerce-exporter' ),
            ),
            array(
                'name'     => 'purchase_subtotal',
                'label'    => __( 'Order Subtotal', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'  => 'order_currency',
                'label' => __( 'Order Currency', 'woocommerce-exporter' ),
            ),
            array(
                'name'     => 'order_discount',
                'label'    => __( 'Order Discount', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'coupon_code',
                'label'    => __( 'Coupon Code', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'coupon_expiry_date',
                'label'    => __( 'Coupon Expiry Date', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'coupon_description',
                'label'    => __( 'Coupon Description', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'purchase_total_tax',
                'label'    => __( 'Order Total Tax', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_subtotal_excl_tax',
                'label'    => __( 'Order Subtotal Excl. Tax', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_sales_tax',
                'label'    => __( 'Sales Tax Total', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_shipping_tax',
                'label'    => __( 'Shipping Tax Total', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_incl_tax',
                'label'    => __( 'Shipping Incl. Tax', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_excl_tax',
                'label'    => __( 'Shipping Excl. Tax', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'refund_total',
                'label'    => __( 'Refund Total', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'refund_date',
                'label'    => __( 'Refund Date', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_tax_percentage',
                'label'    => __( 'Order Tax Percentage', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'  => 'payment_gateway_id',
                'label' => __( 'Payment Gateway ID', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'payment_gateway',
                'label' => __( 'Payment Gateway', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'shipping_method_id',
                'label' => __( 'Shipping Method ID', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'shipping_method',
                'label' => __( 'Shipping Method', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'shipping_instance_id',
                'label' => __( 'Shipping Instance ID', 'woocommerce-exporter' ),
            ),
            array(
                'name'     => 'shipping_cost',
                'label'    => __( 'Shipping Cost', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_weight_total',
                'label'    => __( 'Shipping Weight', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'  => 'payment_status',
                'label' => __( 'Order Status', 'woocommerce-exporter' ),
            ),
            array(
                'name'     => 'post_status',
                'label'    => __( 'Post Status', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_key',
                'label'    => __( 'Order Key', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'transaction_id',
                'label'    => __( 'Transaction ID', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'created_via',
                'label'    => __( 'Created Via', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'cart_hash',
                'label'    => __( 'Cart Hash', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'  => 'purchase_date',
                'label' => __( 'Order Date', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'modified_date',
                'label' => __( 'Order Modified Date', 'woocommerce-exporter' ),
            ),
            array(
                'name'     => 'purchase_time',
                'label'    => __( 'Order Time', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'customer_message',
                'label'    => __( 'Customer Message', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'customer_notes',
                'label'    => __( 'Customer Notes', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_notes',
                'label'    => __( 'Order Notes', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'paypal_payer_paypal_address',
                'label'    => __( 'PayPal: Payer PayPal Address', 'woocommerce-exporter' ),
                'hover'    => __( 'PayPal', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'paypal_payer_first_name',
                'label'    => __( 'PayPal: Payer first name', 'woocommerce-exporter' ),
                'hover'    => __( 'PayPal', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'paypal_payer_last_name',
                'label'    => __( 'PayPal: Payer last name', 'woocommerce-exporter' ),
                'hover'    => __( 'PayPal', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'paypal_payment_type',
                'label'    => __( 'PayPal: Payment type', 'woocommerce-exporter' ),
                'hover'    => __( 'PayPal', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'paypal_payment_status',
                'label'    => __( 'PayPal: Payment status', 'woocommerce-exporter' ),
                'hover'    => __( 'PayPal', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'total_quantity',
                'label'    => __( 'Total Quantity', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'total_order_items',
                'label'    => __( 'Total Order Items', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'user_id',
                'label'    => __( 'User ID', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'user_name',
                'label'    => __( 'Username', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'user_role',
                'label'    => __( 'User Role', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'ip_address',
                'label'    => __( 'Checkout IP Address', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'browser_agent',
                'label'    => __( 'Checkout Browser Agent', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'has_downloads',
                'label'    => __( 'Has Downloads', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'has_downloaded',
                'label'    => __( 'Has Downloaded', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'  => 'billing_full_name',
                'label' => __( 'Billing: Full Name', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_first_name',
                'label' => __( 'Billing: First Name', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_last_name',
                'label' => __( 'Billing: Last Name', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_company',
                'label' => __( 'Billing: Company', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_address',
                'label' => __( 'Billing: Street Address (Full)', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_address_1',
                'label' => __( 'Billing: Street Address 1', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_address_2',
                'label' => __( 'Billing: Street Address 2', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_city',
                'label' => __( 'Billing: City', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_postcode',
                'label' => __( 'Billing: ZIP Code', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_state',
                'label' => __( 'Billing: State (prefix)', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_state_full',
                'label' => __( 'Billing: State', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_country',
                'label' => __( 'Billing: Country (prefix)', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_country_full',
                'label' => __( 'Billing: Country', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_phone',
                'label' => __( 'Billing: Phone Number', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'billing_email',
                'label' => __( 'Billing: E-mail Address', 'woocommerce-exporter' ),
            ),
            array(
                'name'     => 'shipping_full_name',
                'label'    => __( 'Shipping: Full Name', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_first_name',
                'label'    => __( 'Shipping: First Name', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_last_name',
                'label'    => __( 'Shipping: Last Name', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_company',
                'label'    => __( 'Shipping: Company', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_address',
                'label'    => __( 'Shipping: Street Address (Full)', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_address_1',
                'label'    => __( 'Shipping: Street Address 1', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_address_2',
                'label'    => __( 'Shipping: Street Address 2', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_city',
                'label'    => __( 'Shipping: City', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_postcode',
                'label'    => __( 'Shipping: ZIP Code', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_state',
                'label'    => __( 'Shipping: State (prefix)', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_state_full',
                'label'    => __( 'Shipping: State', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_country',
                'label'    => __( 'Shipping: Country (prefix)', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'shipping_country_full',
                'label'    => __( 'Shipping: Country', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
        );

        $order_item_fields = array(
            array(
                'name'  => 'order_items_id',
                'label' => __( 'Order Items: ID', 'woocommerce-exporter' ),
            ),
            array(
                'name'     => 'order_items_product_id',
                'label'    => __( 'Order Items: Product ID', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_variation_id',
                'label'    => __( 'Order Items: Variation ID', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_sku',
                'label'    => __( 'Order Items: SKU', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'  => 'order_items_name',
                'label' => __( 'Order Items: Product Name', 'woocommerce-exporter' ),
            ),
            array(
                'name'     => 'order_items_variation',
                'label'    => __( 'Order Items: Product Variation', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_image_embed',
                'label'    => __( 'Order Items: Featured Image (Embed)', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_description',
                'label'    => __( 'Order Items: Product Description', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_excerpt',
                'label'    => __( 'Order Items: Product Excerpt', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_publish_date',
                'label'    => __( 'Order Items: Publish Date', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_modified_date',
                'label'    => __( 'Order Items: Modified Date', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'  => 'order_items_tax_class',
                'label' => __( 'Order Items: Tax Class', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'order_items_quantity',
                'label' => __( 'Order Items: Quantity', 'woocommerce-exporter' ),
            ),
            array(
                'name'  => 'order_items_total',
                'label' => __( 'Order Items: Total', 'woocommerce-exporter' ),
            ),
            array(
                'name'     => 'order_items_subtotal',
                'label'    => __( 'Order Items: Subtotal', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_rrp',
                'label'    => __( 'Order Items: RRP', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_discount',
                'label'    => __( 'Order Items: Discount', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_stock',
                'label'    => __( 'Order Items: Stock', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_shipping_class',
                'label'    => __( 'Order Items: Shipping Class', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_tax',
                'label'    => __( 'Order Items: Tax', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_tax_percentage',
                'label'    => __( 'Order Items: Tax Percentage', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_tax_subtotal',
                'label'    => __( 'Order Items: Tax Subtotal', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_refund_subtotal',
                'label'    => __( 'Order Items: Refund Subtotal', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_refund_subtotal_incl_tax',
                'label'    => __( 'Order Items: Refund Subtotal Incl. Tax', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_refund_quantity',
                'label'    => __( 'Order Items: Refund Quantity', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_type',
                'label'    => __( 'Order Items: Type', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_type_id',
                'label'    => __( 'Order Items: Type ID', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_category',
                'label'    => __( 'Order Items: Category', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_tag',
                'label'    => __( 'Order Items: Tag', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_total_sales',
                'label'    => __( 'Order Items: Total Sales', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_weight',
                'label'    => __( 'Order Items: Weight', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_height',
                'label'    => __( 'Order Items: Height', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_width',
                'label'    => __( 'Order Items: Width', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_length',
                'label'    => __( 'Order Items: Length', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_total_weight',
                'label'    => __( 'Order Items: Total Weight', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_refund_items_prices_include_tax',
                'label'    => __( 'Refund Items: Prices Include Tax', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_refund_items_refund_amount',
                'label'    => __( 'Refund Items: Refund Amount', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_refund_items_refunded_by',
                'label'    => __( 'Refund Items: Refunded By', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_refund_items_refunded_payment',
                'label'    => __( 'Refund Items: Refunded Payment', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
            array(
                'name'     => 'order_items_refund_items_refund_reason',
                'label'    => __( 'Refund Items: Refund Reason', 'woocommerce-exporter' ),
                'disabled' => 1,
            ),
        );

        // Variation Attributes.
        if ( apply_filters( 'wsed_enable_product_attributes', true ) ) {
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            if ( ! empty( $attribute_taxonomies ) ) {
                foreach ( $attribute_taxonomies as $attribute ) {
                    // First row is to fetch the Variation Attribute linked to the Order Item.
                    $order_item_fields[] = array(
                        'name'     => sprintf( 'order_items_attribute_%s', $attribute->attribute_name ),
                        // translators: %s: Attribute Label.
                        'label'    => sprintf( __( 'Order Items: %s Variation', 'woocommerce-exporter' ), $attribute->attribute_label ),
                        'hover'    => sprintf( '%s: %s (#%d)', __( 'Product Variation', 'woocommerce-exporter' ), $attribute->attribute_name, $attribute->attribute_id ),
                        'disabled' => 1,
                    );
                    // The second row is to fetch the Product Attribute from the Order Item Product.
                    $order_item_fields[] = array(
                        'name'     => sprintf( 'order_items_product_attribute_%s', $attribute->attribute_name ),
                        // translators: %s: Attribute Label.
                        'label'    => sprintf( __( 'Order Items: %s Attribute', 'woocommerce-exporter' ), $attribute->attribute_label ),
                        'hover'    => sprintf( '%s: %s (#%d)', __( 'Product Attribute', 'woocommerce-exporter' ), $attribute->attribute_name, $attribute->attribute_id ),
                        'disabled' => 1,
                    );
                }
            }
        }

        // Add tax rates fields if enabled.
        if ( apply_filters( 'wsed_allow_individual_tax_fields', true ) ) {
            $tax_classes = array();

            // Standard Tax Rates.
            $tax_class_standard                    = new \stdClass();
            $tax_class_standard->tax_rate_class_id = 0;
            $tax_class_standard->slug              = 'standard';
            $tax_class_standard->name              = __( 'Standard', 'woocommerce-exporter' );

            array_push( $tax_classes, $tax_class_standard );

            // Get the Tax Rate Classes then merge them with the standard tax rate.
            $tax_classes = array_merge( $tax_classes, WC_Tax::get_tax_rate_classes() );

            if ( ! empty( $tax_classes ) ) {
                foreach ( $tax_classes as $tax_class ) {
                    $fields[] = array(
                        'name'     => 'purchase_total_tax_rate_' . $tax_class->tax_rate_class_id,
                        'label'    => sprintf(
                            // translators: %s - tax class name.
                            __( 'Order Tax: %s', 'woocommerce-exporter' ),
                            $tax_class->name
                        ),
                        'disabled' => 1,
                    );

                    $order_item_fields[] = array(
                        'name'     => 'order_items_tax_rate_' . $tax_class->tax_rate_class_id,
                        'label'    => sprintf(
                            // translators: %s - tax class name.
                            __( 'Order Items: %s', 'woocommerce-exporter' ),
                            $tax_class->name
                        ),
                        'disabled' => 1,
                    );
                }
            }
        }

        // Custom Order fields.
        $custom_orders = get_option( WOO_CE_PREFIX . '_custom_orders', '' );
        if ( ! empty( $custom_orders ) ) {
            foreach ( $custom_orders as $custom_order ) {
                $fields[] = array(
                    'name'     => $custom_order,
                    'label'    => Export_Helper::clean_export_label( $custom_order ),
                    'hover'    => sprintf( apply_filters( 'wsed_extend_order_fields_custom_order_hover', '%s: %s' ), __( 'Custom Order', 'woocommerce-exporter' ), $custom_order ),
                    'disabled' => 1,
                );
            }
        }

        // Custom Order Items fields.
        $custom_order_items = get_option( WOO_CE_PREFIX . '_custom_order_items', '' );
        if ( ! empty( $custom_order_items ) ) {
            foreach ( $custom_order_items as $custom_order_item ) {
                $order_item_fields[] = array(
                    'name'     => sprintf( 'order_items_%s', sanitize_key( $custom_order_item ) ),
                    // translators: %s: Custom Order Item.
                    'label'    => sprintf( __( 'Order Items: %s', 'woocommerce-exporter' ), Export_Helper::clean_export_label( $custom_order_item ) ),
                    'hover'    => sprintf( apply_filters( 'wsed_extend_order_fields_custom_order_item_hover', '%s: %s' ), __( 'Custom Order Item', 'woocommerce-exporter' ), $custom_order_item ),
                    'disabled' => 1,
                );
            }
        }

        // Custom Order Item Product fields.
        $custom_order_products = get_option( WOO_CE_PREFIX . '_custom_order_products', '' );
        if ( ! empty( $custom_order_products ) ) {
            foreach ( $custom_order_products as $custom_order_product ) {
                $order_item_fields[] = array(
                    'name'     => sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) ),
                    // translators: %s: Custom Order Item Product.
                    'label'    => sprintf( __( 'Order Items: %s', 'woocommerce-exporter' ), Export_Helper::clean_export_label( $custom_order_product ) ),
                    'hover'    => sprintf( apply_filters( 'wsed_extend_order_fields_custom_order_product_hover', '%s: %s' ), __( 'Custom Order Item Product', 'woocommerce-exporter' ), $custom_order_product ),
                    'disabled' => 1,
                );
            }
        }

        // Custom Product fields.
        $custom_products = get_option( WOO_CE_PREFIX . '_custom_products', '' );
        if ( ! empty( $custom_products ) ) {
            foreach ( $custom_products as $custom_product ) {
                $order_item_fieldss[] = array(
                    'name'     => sprintf( 'order_items_%s', sanitize_key( $custom_product ) ),
                    // translators: %s: Custom Product.
                    'label'    => sprintf( __( 'Order Items: %s', 'woocommerce-exporter' ), Export_Helper::clean_export_label( $custom_product ) ),
                    'hover'    => sprintf( apply_filters( 'wsed_extend_order_fields_custom_product_hover', '%s: %s' ), __( 'Custom Product', 'woocommerce-exporter' ), $custom_product ),
                    'disabled' => 1,
                );
            }
        }

        /**
         * Filter the Order fields.
         * Allows for adding/removing fields from the Order export.
         *
         * @since 2.7.3
         * @param array $fields The default fields.
         */
        $fields = apply_filters( 'wsed_order_fields', $fields );

        /**
         * Filter the Order Items fields.
         * Allows for adding/removing fields from the Order Items export.
         *
         * @since 2.7.3
         * @param array $order_items_fields The default fields.
         */
        $order_item_fields       = apply_filters( 'wsed_order_items_fields', $order_item_fields );
        $this->order_item_fields = $order_item_fields;

        // Merge the fields.
        $fields = array_merge( $fields, $order_item_fields );

        // Check if we're dealing with an Export Template.
        $sorting = false;
        if ( ! empty( $post_id ) ) {
            $remember = get_post_meta( $post_id, sprintf( '_%s_fields', $this->export_type ), true );
            $hidden   = get_post_meta( $post_id, sprintf( '_%s_hidden', $this->export_type ), false );
            $sorting  = get_post_meta( $post_id, sprintf( '_%s_sorting', $this->export_type ), true );
        } else {
            $remember = get_option( WOO_CE_PREFIX . '_' . $this->export_type . '_fields', array() );
            $hidden   = get_option( WOO_CE_PREFIX . '_' . $this->export_type . '_hidden', array() );
        }
        if ( ! empty( $remember ) ) {
            $remember = maybe_unserialize( $remember );
            $hidden   = maybe_unserialize( $hidden );
            $size     = count( $fields );
            for ( $i = 0; $i < $size; $i++ ) {
                $fields[ $i ]['disabled'] = ( isset( $fields[ $i ]['disabled'] ) ? $fields[ $i ]['disabled'] : 0 );
                $fields[ $i ]['hidden']   = ( isset( $fields[ $i ]['hidden'] ) ? $fields[ $i ]['hidden'] : 0 );
                $fields[ $i ]['default']  = 1;
                if ( isset( $fields[ $i ]['name'] ) ) {
                    // If not found turn off default.
                    if ( ! array_key_exists( $fields[ $i ]['name'], $remember ) ) {
                        $fields[ $i ]['default'] = 0;
                    }
                    // Remove the field from exports if found.
                    if ( array_key_exists( $fields[ $i ]['name'], $hidden ) ) {
                        $fields[ $i ]['hidden'] = 1;
                    }
                }
            }
        }

        $size = count( $fields );

        // Load the default sorting.
        if ( empty( $sorting ) ) {
            $sorting = get_option( WOO_CE_PREFIX . '_order_sorting', array() );
        }

        for ( $i = 0; $i < $size; $i++ ) {
            if ( ! isset( $fields[ $i ]['name'] ) ) {
                unset( $fields[ $i ] );
                continue;
            }
            $fields[ $i ]['reset'] = $i;
            $fields[ $i ]['order'] = ( isset( $sorting[ $fields[ $i ]['name'] ] ) ? $sorting[ $fields[ $i ]['name'] ] : $i );
        }
        $fields = Export_Helper::sort_export_fields( $fields, 'order' );
        return $fields;
    }

    /**
     * Save export fields settings.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array $export    The export settings.
     * @param array $form_data The form data.
     */
    public function save_export_fields( $export, $form_data ) {
        $orders_filters = $form_data['orders_filters'] ?? array();

        // Date filters.
        if ( ! empty( $orders_filters['date'] ) && 'on' === $orders_filters['date'] ) {
            if ( ! empty( $form_data['order_dates_filter'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_dates_filter', $form_data['order_dates_filter'] );
            }
            if ( ! empty( $form_data['order_dates_from'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_dates_from', $form_data['order_dates_from'] );
            }
            if ( ! empty( $form_data['order_dates_to'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_dates_to', $form_data['order_dates_to'] );
            }
            if ( ! empty( $form_data['order_dates_filter_variable'] ) && ! empty( $form_data['order_dates_filter_variable_length'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_dates_filter_variable', $form_data['order_dates_filter_variable'] );
                update_option( WOO_CE_PREFIX . '_order_dates_filter_variable_length', $form_data['order_dates_filter_variable_length'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_dates_filter' );
            delete_option( WOO_CE_PREFIX . '_order_dates_from' );
            delete_option( WOO_CE_PREFIX . '_order_dates_to' );
            delete_option( WOO_CE_PREFIX . '_order_dates_filter_variable' );
            delete_option( WOO_CE_PREFIX . '_order_dates_filter_variable_length' );
        }

        // Modified date filters.
        if ( ! empty( $orders_filters['modified_date'] ) && 'on' === $orders_filters['modified_date'] ) {
            if ( ! empty( $form_data['order_modified_dates_filter'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_modified_dates_filter', $form_data['order_modified_dates_filter'] );
            }
            if ( ! empty( $form_data['order_modified_dates_from'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_modified_dates_from', $form_data['order_modified_dates_from'] );
            }
            if ( ! empty( $form_data['order_modified_dates_to'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_modified_dates_to', $form_data['order_modified_dates_to'] );
            }
            if ( ! empty( $form_data['order_modified_dates_filter_variable'] ) && ! empty( $form_data['order_modified_dates_filter_variable_length'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_modified_dates_filter_variable', $form_data['order_modified_dates_filter_variable'] );
                update_option( WOO_CE_PREFIX . '_order_modified_dates_filter_variable_length', $form_data['order_modified_dates_filter_variable_length'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_modified_dates_filter' );
            delete_option( WOO_CE_PREFIX . '_order_modified_dates_from' );
            delete_option( WOO_CE_PREFIX . '_order_modified_dates_to' );
            delete_option( WOO_CE_PREFIX . '_order_modified_dates_filter_variable' );
            delete_option( WOO_CE_PREFIX . '_order_modified_dates_filter_variable_length' );
        }

        // Order status filters.
        if ( ! empty( $orders_filters['status'] ) && 'on' === $orders_filters['status'] ) {
            if ( ! empty( $form_data['order_filter_status'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_status', $form_data['order_filter_status'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_status' );
        }

        // Order customer filters.
        if ( ! empty( $orders_filters['customer'] ) && 'on' === $orders_filters['customer'] ) {
            if ( ! empty( $form_data['order_filter_customer'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_customer', $form_data['order_filter_customer'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_customer' );
        }

        // Order billing country filters.
        if ( ! empty( $orders_filters['billing_country'] ) && 'on' === $orders_filters['billing_country'] ) {
            if ( ! empty( $form_data['order_filter_billing_country'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_billing_country', $form_data['order_filter_billing_country'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_billing_country' );
        }

        // Order shipping country filters.
        if ( ! empty( $orders_filters['shipping_country'] ) && 'on' === $orders_filters['shipping_country'] ) {
            if ( ! empty( $form_data['order_filter_shipping_country'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_shipping_country', $form_data['order_filter_shipping_country'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_shipping_country' );
        }

        // Order user role filters.
        if ( ! empty( $orders_filters['user_role'] ) && 'on' === $orders_filters['user_role'] ) {
            if ( ! empty( $form_data['order_filter_user_role'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_user_roles', $form_data['order_filter_user_role'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_user_roles' );
        }

        // Order coupon filters.
        if ( ! empty( $orders_filters['coupon'] ) && 'on' === $orders_filters['coupon'] ) {
            if ( ! empty( $form_data['order_filter_coupon'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_coupon', $form_data['order_filter_coupon'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_coupon' );
        }

        // Order product filters.
        if ( ! empty( $orders_filters['product'] ) && 'on' === $orders_filters['product'] ) {
            if ( ! empty( $form_data['order_filter_product'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_product', $form_data['order_filter_product'] );
                if ( ! empty( $form_data['order_filter_product_exclude'] ) ) {
                    update_option( WOO_CE_PREFIX . '_order_product_exclude', $form_data['order_filter_product_exclude'] );
                }
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_product' );
            delete_option( WOO_CE_PREFIX . '_order_product_exclude' );
        }

        // Order category filters.
        if ( ! empty( $orders_filters['category'] ) && 'on' === $orders_filters['category'] ) {
            if ( ! empty( $form_data['order_filter_category'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_category', $form_data['order_filter_category'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_category' );
        }

        // Order tag filters.
        if ( ! empty( $orders_filters['tag'] ) && 'on' === $orders_filters['tag'] ) {
            if ( ! empty( $form_data['order_filter_tag'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_tag', $form_data['order_filter_tag'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_tag' );
        }

        // Order IDs filters.
        if ( ! empty( $orders_filters['id'] ) && 'on' === $orders_filters['id'] ) {
            if ( ! empty( $form_data['order_filter_id'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_order_ids', $form_data['order_filter_id'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_order_ids' );
        }

        // Order payment gateway filters.
        if ( ! empty( $orders_filters['payment_gateway'] ) && 'on' === $orders_filters['payment_gateway'] ) {
            if ( ! empty( $form_data['order_filter_payment_gateway'] ) ) {
                update_option( WOO_CE_PREFIX . '_payment_method', $form_data['order_filter_payment_gateway'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_payment_method' );
        }

        // Order shipping method filters.
        if ( ! empty( $orders_filters['shipping_method'] ) && 'on' === $orders_filters['shipping_method'] ) {
            if ( ! empty( $form_data['order_filter_shipping_method'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_shipping_method', $form_data['order_filter_shipping_method'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_shipping_method' );
        }

        // Order digital products filters.
        if ( ! empty( $orders_filters['digital_products'] ) && 'on' === $orders_filters['digital_products'] ) {
            if ( ! empty( $form_data['order_filter_digital_products'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_digital_products', $form_data['order_filter_digital_products'] );
            }
        } else {
            delete_option( WOO_CE_PREFIX . '_order_digital_products' );
        }

        // Order order by filters.
        if ( ! empty( $form_data['order_orderby'] ) ) {
            update_option( WOO_CE_PREFIX . '_order_orderby', $form_data['order_orderby'] );
        }

        // Order order/sorting filters.
        if ( ! empty( $form_data['order_order'] ) ) {
            update_option( WOO_CE_PREFIX . '_order_order', $form_data['order_order'] );
        }

        // Export Options.
        // Order items formatting.
        if ( ! empty( $form_data['order_items'] ) ) {
            update_option( WOO_CE_PREFIX . '_order_items_formatting', $form_data['order_items'] );
        }

        // Max unique order items.
        if ( ! empty( $form_data['max_order_items'] ) ) {
            update_option( WOO_CE_PREFIX . '_max_order_items', $form_data['max_order_items'] );
        }

        // Order item types.
        if ( ! empty( $form_data['order_items_types'] ) ) {
            update_option( WOO_CE_PREFIX . '_order_items_types', $form_data['order_items_types'] );
        }

        // Order flag notes.
        if ( ! empty( $form_data['order_flag_notes'] ) ) {
            update_option( WOO_CE_PREFIX . '_order_flag_notes', $form_data['order_flag_notes'] );
        }
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
        $orders_filters = $settings['orders_filters'] ?? array();
        if ( ! empty( $orders_filters ) ) {
            if ( isset( $orders_filters['date'] ) && 'on' === $orders_filters['date'] ) {
                $order_dates_filter         = sanitize_text_field( $settings['order_dates_filter'] ) ?? false;
                $args['order_dates_filter'] = $order_dates_filter;

                if ( 'manual' === $order_dates_filter ) {
                    $args['order_dates_from'] = isset( $settings['order_dates_from'] ) ? Formatting::sanitize_date( $settings['order_dates_from'] ) : '';
                    $args['order_dates_to']   = isset( $settings['order_dates_to'] ) ? Formatting::sanitize_date( $settings['order_dates_to'] ) : '';
                } elseif ( 'variable' === $order_dates_filter ) {
                    $args['order_dates_filter_variable']        = isset( $settings['order_dates_filter_variable'] ) ? absint( $settings['order_dates_filter_variable'] ) : false;
                    $args['order_dates_filter_variable_length'] = isset( $settings['order_dates_filter_variable_length'] ) ? sanitize_text_field( $settings['order_dates_filter_variable_length'] ) : false;
                }
            }
            if ( isset( $orders_filters['modified_date'] ) && 'on' === $orders_filters['modified_date'] ) {
                $order_modified_dates_filter         = sanitize_text_field( $settings['order_modified_dates_filter'] ) ?? false;
                $args['order_modified_dates_filter'] = $order_modified_dates_filter;

                if ( 'manual' === $order_modified_dates_filter ) {
                    $args['order_modified_dates_from'] = isset( $settings['order_modified_dates_from'] ) ? Formatting::sanitize_date( $settings['order_modified_dates_from'] ) : '';
                    $args['order_modified_dates_to']   = isset( $settings['order_modified_dates_to'] ) ? Formatting::sanitize_date( $settings['order_modified_dates_to'] ) : '';
                } elseif ( 'variable' === $order_modified_dates_filter ) {
                    $args['order_modified_dates_filter_variable']        = isset( $settings['order_modified_dates_filter_variable'] ) ? absint( $settings['order_modified_dates_filter_variable'] ) : false;
                    $args['order_modified_dates_filter_variable_length'] = isset( $settings['order_modified_dates_filter_variable_length'] ) ? sanitize_text_field( $settings['order_modified_dates_filter_variable_length'] ) : false;
                }
            }
            if ( isset( $orders_filters['status'] ) && 'on' === $orders_filters['status'] ) {
                $args['order_status'] = isset( $settings['order_filter_status'] ) ? array_map( 'sanitize_text_field', (array) $settings['order_filter_status'] ) : false;
            }
            if ( isset( $orders_filters['customer'] ) && 'on' === $orders_filters['customer'] ) {
                if ( isset( $settings['order_filter_customer'] ) && is_array( $settings['order_filter_customer'] ) ) {
                    // If the customer filter is empty, we want to include all customers.
                    if ( in_array( '', $settings['order_filter_customer'], true ) ) {
                        $args['order_customer'] = 'all';
                    } else {
                        $args['order_customer'] = array_map( 'absint', (array) $settings['order_filter_customer'] );
                    }
                }
            }
            if ( isset( $orders_filters['billing_country'] ) && 'on' === $orders_filters['billing_country'] ) {
                if ( isset( $settings['order_filter_billing_country'] ) && is_array( $settings['order_filter_billing_country'] ) ) {
                    // If the billing country filter has empty string, we want to include all customers.
                    if ( in_array( '', $settings['order_filter_billing_country'], true ) ) {
                        $args['order_billing_country'] = 'all';
                    } else {
                        $args['order_billing_country'] = array_map( 'sanitize_text_field', (array) $settings['order_filter_billing_country'] );
                    }
                }
            }
            if ( isset( $orders_filters['shipping_country'] ) && 'on' === $orders_filters['shipping_country'] ) {
                if ( isset( $settings['order_filter_shipping_country'] ) && is_array( $settings['order_filter_shipping_country'] ) ) {
                    // If the shipping country filter has empty string, we want to include all customers.
                    if ( in_array( '', $settings['order_filter_shipping_country'], true ) ) {
                        $args['order_shipping_country'] = 'all';
                    } else {
                        $args['order_shipping_country'] = array_map( 'sanitize_text_field', (array) $settings['order_filter_shipping_country'] );
                    }
                }
            }
            if ( isset( $orders_filters['user_role'] ) && 'on' === $orders_filters['user_role'] ) {
                if ( isset( $settings['order_filter_user_role'] ) && ! empty( $settings['order_filter_user_role'] ) ) {
                    $args['order_user_roles'] = array_map( 'sanitize_text_field', (array) $settings['order_filter_user_role'] );
                }
            }
            if ( isset( $orders_filters['coupon'] ) && 'on' === $orders_filters['coupon'] ) {
                if ( isset( $settings['order_filter_coupon'] ) && ! empty( $settings['order_filter_coupon'] ) ) {
                    $args['order_coupon'] = array_map( 'sanitize_text_field', (array) $settings['order_filter_coupon'] );
                }
            }
            if ( isset( $orders_filters['product'] ) && 'on' === $orders_filters['product'] ) {
                if ( isset( $settings['order_filter_product'] ) && ! empty( $settings['order_filter_product'] ) ) {
                    $args['order_product']         = array_map( 'absint', (array) $settings['order_filter_product'] );
                    $args['order_product_exclude'] = isset( $settings['order_filter_product_exclude'] ) ? absint( $settings['order_filter_product_exclude'] ) : false;
                }
            }
            if ( isset( $orders_filters['category'] ) && 'on' === $orders_filters['category'] ) {
                $args['order_category'] = ! empty( $settings['order_filter_category'] ) ? array_map( 'absint', (array) $settings['order_filter_category'] ) : false;
            }
            if ( isset( $orders_filters['tag'] ) && 'on' === $orders_filters['tag'] ) {
                $args['order_tag'] = ! empty( $settings['order_filter_tag'] ) ? array_map( 'absint', (array) $settings['order_filter_tag'] ) : false;
            }
            if ( isset( $orders_filters['id'] ) && 'on' === $orders_filters['id'] ) {
                $args['order_ids'] = ! empty( $settings['order_filter_id'] ) ? Export_Helper::sanitize_multiple_id_input( $settings['order_filter_id'] ) : false;
            }
            if ( isset( $orders_filters['payment_gateway'] ) && 'on' === $orders_filters['payment_gateway'] ) {
                $args['order_payment'] = ! empty( $settings['order_filter_payment_gateway'] ) ? array_map( 'sanitize_text_field', (array) $settings['order_filter_payment_gateway'] ) : false;
            }
            if ( isset( $orders_filters['shipping_method'] ) && 'on' === $orders_filters['shipping_method'] ) {
                $args['order_shipping'] = ! empty( $settings['order_filter_shipping_method'] ) ? array_map( 'sanitize_text_field', (array) $settings['order_filter_shipping_method'] ) : false;
            }
            if ( isset( $orders_filters['digital_products'] ) && 'on' === $orders_filters['digital_products'] ) {
                $args['order_items_digital'] = ! empty( $settings['order_filter_digital_products'] ) ? sanitize_text_field( $settings['order_filter_digital_products'] ) : false;
            }
        }

        // Order Export Options.
        $args['order_items']              = isset( $settings['order_items'] ) ? sanitize_text_field( $settings['order_items'] ) : false;
        $args['order_items_types']        = isset( $settings['order_items_types'] ) ? array_map( 'sanitize_text_field', (array) $settings['order_items_types'] ) : false;
        $args['order_flag_notes']         = isset( $settings['order_flag_notes'] ) ? absint( $settings['order_flag_notes'] ) : false;
        $args['max_order_items']          = isset( $settings['max_order_items'] ) ? absint( $settings['max_order_items'] ) : 10;
        $args['order_orderby']            = isset( $settings['order_orderby'] ) ? sanitize_text_field( $settings['order_orderby'] ) : 'DATE';
        $args['order_order']              = isset( $settings['order_order'] ) ? sanitize_text_field( $settings['order_order'] ) : 'DESC';
        $args['product_image_formatting'] = get_option( WOO_CE_PREFIX . '_product_image_formatting', 1 );
        $args['gallery_formatting']       = get_option( WOO_CE_PREFIX . '_gallery_formatting', 1 );

        if ( $export->scheduled_export ) {
            // Backward compatibility.
            // For the scheduled exports export settings are stored in the post meta.
            // We will refactor the scheduled exports in the future.
            $args = apply_filters( 'woo_ce_extend_cron_dataset_args', $args, $export->post_id, $export->type, true );
        }

        /**
         * Filter the Order Export Dataset args.
         * This filter allows developers to modify the arguments used to generate the dataset for an order export.
         * It is particularly useful for customizing the data retrieval process during the export based on specific conditions or business logic.
         *
         * @since 2.7.3
         *
         * @param array  $args     The current set of arguments that will be used to fetch the order data.
         *                         This includes parameters like date filters, order statuses, customer data, etc.
         * @param object $export   The export settings object, which contains settings and parameters related to the current export operation.
         * @param array  $settings Raw export settings obtained from the form or the post data.
         *                         This might include user inputs or predefined settings that dictate how the export should be performed.
         */
        return apply_filters( 'wsed_order_export_dataset_args', $args, $export, $settings );
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
        $export_args = $export->args;

        $args = array(
            'limit'  => -1,
            'return' => 'ids',
            'type'   => 'shop_order',
        );

        if ( ! empty( $export_args['order_order'] ) ) {
            $args['order'] = $export_args['order_order'];
        }

        if ( ! empty( $export_args['order_orderby'] ) ) {
            $args['order'] = $export_args['order_orderby'];
        }

        if ( ! empty( $export_args['limit_volume'] ) ) {
            $args['limit'] = $export_args['limit_volume'];
        }

        if ( ! empty( $export_args['offset'] ) ) {
            $args['offset'] = $export_args['offset'];
        }

        // Order Created Dates.
        if ( ! empty( $export_args['order_dates_filter'] ) ) {
            list($date_from, $date_to) = Export_Helper::get_date_filter(
                $export_args['order_dates_filter'],
                array(
                    'from'            => $export_args['order_dates_from'] ?? null,
                    'to'              => $export_args['order_dates_to'] ?? null,
                    'variable'        => $export_args['order_dates_filter_variable'] ?? null,
                    'variable_length' => $export_args['order_dates_filter_variable_length'] ?? null,
                ),
            );
            $args['date_created']      = $date_from && $date_to ? "{$date_from}...{$date_to}" : '';
        }

        // Order Modified Dates.
        if ( ! empty( $export_args['order_modified_dates_filter'] ) ) {
            list($modified_date_from, $modified_date_to) = Export_Helper::get_date_filter(
                $export_args['order_modified_dates_filter'],
                array(
                    'from'            => $export_args['order_modified_dates_from'] ?? null,
                    'to'              => $export_args['order_modified_dates_to'] ?? null,
                    'variable'        => $export_args['order_modified_dates_filter_variable'] ?? null,
                    'variable_length' => $export_args['order_modified_dates_filter_variable_length'] ?? null,
                ),
            );

            $args['date_modified'] = $modified_date_from && $modified_date_to ? "{$modified_date_from}...{$modified_date_to}" : '';
        }

        // Order Status.
        if ( ! empty( $export_args['order_status'] ) ) {
            $args['status'] = $export_args['order_status'];
        }

        // Order Customers.
        if ( ! empty( $export_args['order_customer'] ) && 'all' !== $export_args['order_customer'] ) {
            $args['field_query'][] = array(
                array(
                    'field'   => 'customer_id',
                    'value'   => implode( ',', $export_args['order_customer'] ),
                    'compare' => 'IN',
                ),
            );
        }

        // Order Billing Country.
        if ( ! empty( $export_args['order_billing_country'] ) && 'all' !== $export_args['order_billing_country'] ) {
            $args['field_query'][] = array(
                array(
                    'field'   => 'billing_country',
                    'value'   => implode( ',', $export_args['order_billing_country'] ),
                    'compare' => 'IN',
                ),
            );
        }

        // Order Shipping Country.
        if ( ! empty( $export_args['order_shipping_country'] ) && 'all' !== $export_args['order_shipping_country'] ) {
            $args['field_query'][] = array(
                array(
                    'field'   => 'shipping_country',
                    'value'   => implode( ',', $export_args['order_shipping_country'] ),
                    'compare' => 'IN',
                ),
            );
        }

        // Order User Roles.
        if ( ! empty( $export_args['order_user_roles'] ) ) {
            $user_ids = get_users(
                array(
                    'role__in' => $export_args['order_user_roles'],
                    'fields'   => 'ID',
                )
            );

            // Include guest orders.
            if ( in_array( 'guest', $export_args['order_user_roles'], true ) ) {
                $user_ids[] = 0;
            }

            if ( ! empty( $user_ids ) ) {
                $args['field_query'][] = array(
                    array(
                        'field'   => 'customer_id',
                        'value'   => implode( ',', $user_ids ),
                        'compare' => 'IN',
                    ),
                );
            }
        }

        // Order IDs.
        if ( ! empty( $export_args['order_ids'] ) ) {
            $args['field_query'][] = array(
                array(
                    'field'   => 'id',
                    'value'   => implode( ',', $export_args['order_ids'] ),
                    'compare' => 'IN',
                ),
            );
        }

        // Order Payment Gateway.
        if ( ! empty( $export_args['order_payment'] ) ) {
            $args['field_query'][] = array(
                array(
                    'field'   => 'payment_method',
                    'value'   => implode( ',', $export_args['order_payment'] ),
                    'compare' => 'IN',
                ),
            );
        }

        /**
         * Filter the Order Query args.
         *
         * @since 2.7.3
         * @param array $args         The Order Query args.
         * @param array $export_args  The export args.
         */
        $object_ids = wc_get_orders( apply_filters( 'wsed_get_object_ids_args', $args, $export_args ) );

        /**
         * Below are the fields that are not supported by the Order Query.
         * We will need to loop through the orders and filter them manually.
         * This is not ideal, but it is the only way to get the data.
         */
        if (
            ! empty( $export_args['order_coupon'] ) ||
            ! empty( $export_args['order_product'] ) ||
            ! empty( $export_args['order_category'] ) ||
            ! empty( $export_args['order_tag'] ) ||
            ! empty( $export_args['order_shipping'] ) ||
            ! empty( $export_args['order_items_digital'] )
        ) {
            global $wpdb;

            // Order Coupons.
            if ( ! empty( $export_args['order_coupon'] ) ) {
                $object_ids = array_intersect(
                    $object_ids,
                    $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT DISTINCT order_id FROM {$wpdb->prefix}wc_order_coupon_lookup WHERE coupon_id IN (%s)",
                            implode( "','", $export_args['order_coupon'] )
                        )
                    )
                );
            }

            // Order Products.
            if ( ! empty( $export_args['order_product'] ) ) {
                $excl_or_incl = $export_args['order_product_exclude'] ? 'NOT IN' : 'IN';
                $object_ids   = array_intersect(
                    $object_ids,
                    $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT DISTINCT order_id FROM {$wpdb->prefix}wc_order_product_lookup WHERE product_id {$excl_or_incl} (%s)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                            implode( "','", $export_args['order_product'] )
                        )
                    )
                );
            }

            // Order Categories.
            if ( ! empty( $export_args['order_category'] ) ) {
                $object_ids = array_intersect(
                    $object_ids,
                    $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT DISTINCT order_id
                                FROM {$wpdb->prefix}wc_order_product_lookup as wopl
                                INNER JOIN {$wpdb->prefix}term_relationships as tr ON tr.object_id = wopl.product_id
                                WHERE tr.term_taxonomy_id IN (%s)",
                            implode( "','", $export_args['order_category'] )
                        )
                    )
                );
            }

            // Order Tags.
            if ( ! empty( $export_args['order_tag'] ) ) {
                $object_ids = array_intersect(
                    $object_ids,
                    $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT DISTINCT order_id
                                FROM {$wpdb->prefix}wc_order_product_lookup as wopl
                                INNER JOIN {$wpdb->prefix}term_relationships as tr ON tr.object_id = wopl.product_id
                                WHERE tr.term_taxonomy_id IN (%s)",
                            implode( "','", $export_args['order_tag'] ),
                        )
                    )
                );
            }

            // Order Shipping Methods.
            if ( ! empty( $export_args['order_shipping'] ) ) {
                $shipping_clause_placeholder = '(' . implode( ',', array_fill( 0, count( $export_args['order_shipping'] ), '%s' ) ) . ')';

                // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                // phpcs:disable WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
                $object_ids = array_intersect(
                    $object_ids,
                    $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT DISTINCT oi.order_id 
                                FROM {$wpdb->prefix}woocommerce_order_items as oi
                                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta as oim ON oi.order_item_id = oim.order_item_id
                                WHERE oi.order_item_type = 'shipping'
                                    AND oim.meta_key = 'method_id'
                                    AND oim.meta_value IN {$shipping_clause_placeholder}",
                            array_merge( $export_args['order_shipping'] )
                        )
                    )
                );
                // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                // phpcs:enable ordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
            }

            // Order Digital Products.
            if ( ! empty( $export_args['order_items_digital'] ) && ! empty( $export_args['order_items_digital'] ) ) {
                $orders_clause_placeholder = '(' . implode( ',', array_fill( 0, count( $object_ids ), '%s' ) ) . ')';

                // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $orders_downloadable = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT wopl.order_id,
                            GROUP_CONCAT(DISTINCT pm.meta_value ORDER BY pm.meta_key DESC SEPARATOR '|') AS downloadable
                            FROM wp_wc_order_product_lookup AS wopl
                            INNER JOIN wp_postmeta AS pm ON (wopl.product_id = pm.post_id)
                            WHERE pm.meta_key = '_downloadable'
                            AND wopl.order_id IN {$orders_clause_placeholder}
                            GROUP BY wopl.order_id",
                        $object_ids
                    )
                );
                // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

                if ( 'include_digital' === $export_args['order_items_digital'] ) {
                    $orders_with_digital = array();
                    foreach ( $orders_downloadable as $order_downloadable ) {
                        if ( false !== strpos( $order_downloadable->downloadable, 'yes' ) ) {
                            $orders_with_digital[] = $order_downloadable->order_id;
                        }
                    }
                    $object_ids = array_intersect( $object_ids, $orders_with_digital );
                } elseif ( 'exclude_digital' === $export_args['order_items_digital'] ) {
                    $orders_without_digital = array();
                    foreach ( $orders_downloadable as $order_downloadable ) {
                        if ( false === strpos( $order_downloadable->downloadable, 'yes' ) ) {
                            $orders_without_digital[] = $order_downloadable->order_id;
                        }
                    }
                    $object_ids = array_intersect( $object_ids, $orders_without_digital );
                } elseif ( 'exclude_digital_only' === $export_args['order_items_digital'] ) {
                    $orders_digital_only = array();
                    foreach ( $orders_downloadable as $order_downloadable ) {
                        $values = explode( '|', $order_downloadable->downloadable );
                        foreach ( $values as $value ) {
                            if ( 'yes' === $value ) {
                                $orders_digital_only[] = $order_downloadable->order_id;
                                break;
                            }
                        }
                    }
                    $object_ids = array_intersect( $object_ids, $orders_digital_only );
                }
            }
        }

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
     * @param array  $object_ids The object IDs.
     * @param object $export     The export settings.
     * @return array
     */
    public function get_dataset( $object_ids, $export = null ) {
        $dataset = array();
        $columns = $export->columns;
        $fields  = $export->fields;

        $orders_query  = new WC_Order_Query(
            array(
                'post__in' => $object_ids,
                'limit'    => -1,
            )
        );
        $order_objects = $orders_query->get_orders();

        if ( ! empty( $order_objects ) ) {
            foreach ( $order_objects as $i => $order ) {
                $data       = array();
                $order_data = $order->get_data();

                // Consists of the order data that is provided by the WooCommerce Order object.
                $this->_get_order_data( $data, $fields, $order, $order_data );

                // Consists of the order data that is not provided by the WooCommerce Order object.
                // Therefore, we need to do some additional processing to get the data.
                $this->_get_order_notes_data( $data, $fields, $order, $export->category_separator );
                $this->_get_order_shipping_method_data( $data, $fields, $order, $export->category_separator );
                $this->_get_order_shipping_weight_total_data( $data, $fields, $order );
                $this->_get_order_taxes_data( $data, $fields, $order, $export->category_separator );
                $this->_get_order_user_data( $data, $fields, $order, $export->category_separator );
                $this->_get_order_refund_data( $data, $fields, $order, $export->category_separator );
                $this->_get_order_coupon_data( $data, $fields, $order, $export->category_separator );
                $this->_get_order_custom_data( $data, $fields, $order );

                // Fetch order items dataset.
                $data_order_items = $this->fetch_order_items_dataset( $order, $fields, $export );
                if ( ! empty( $data_order_items ) ) {
                    $data['order_items'] = $data_order_items;
                }

                /**
                 * Filter the Order data.
                 *
                 * @since 2.7.3
                 * @param array    $data  The default data array.
                 * @param array    $fields The selected fields.
                 * @param int      $i     The current order index.
                 * @param WC_Order $order The order object.
                 * @param object   $export The export settings.
                 * @return array
                 */
                $dataset[ $i ] = apply_filters( 'wsed_order_export_data', $data, $fields, $order, $export );
            }
        }

        /**
         * Filter the Order dataset.
         *
         * @since 2.7.3
         * @param array    $dataset The default dataset array.
         * @param array    $fields  The selected fields.
         * @param WC_Order $order   The order object.
         * @param object   $export  The export settings.
         * @return array
         */
        $dataset = apply_filters( 'wsed_order_export_dataset', $dataset, $fields, $order, $export );

        return $this->parse_dataset( $dataset, $export );
    }

    /**
     * Fetch the order items dataset.
     *
     * @since 2.7.3
     * @access public
     *
     * @param WC_Order $order  The order object.
     * @param array    $fields The selected fields.
     * @param object   $export The export settings.
     * @return array
     */
    public function fetch_order_items_dataset( $order, $fields, $export ) {
        $i               = 1;
        $order_item_data = array();
        $order_items     = $order->get_items( $export->args['order_items_types'] );
        $max_order_items = $export->args['max_order_items'];

        if ( ! empty( $order_items ) ) {
            foreach ( $order_items as $item_id => $item ) {
                if ( $max_order_items > 0 && $i > $max_order_items ) {
                    break;
                }

                // Process the order item.
                $order_item_data[ $i ] = $this->_process_order_item( $item, $fields, $order, $export );
                ++$i;
            }
        }

        /**
         * Get order item refunds data.
         * The order item refunds data is not available in the order item object, so we need to fetch it manually.
         * The refunds data will be fetched only if the order items types include 'refund'.
         * And if the max order items is still has not reached.
         */
        if (
            isset( $export->args['order_items_types'] ) &&
            is_array( $export->args['order_items_types'] ) &&
            in_array( 'refund', $export->args['order_items_types'], true ) &&
            $max_order_items > 0 && $i <= $max_order_items
        ) {
            $refunds = $order->get_refunds();
            if ( ! empty( $refunds ) ) {
                foreach ( $refunds as $refund ) {
                    $data        = array();
                    $refund_data = $refund->get_data();
                    $refund_id   = $refund->get_id();

                    // Get the refund items data.
                    $this->_get_order_items_refund_data( $data, $fields, $refund, $refund_data );
                    $order_item_data[ $i ] = $data;
                    ++$i;
                }
            }
        }

        return apply_filters( 'wsed_order_items_export_dataset', $order_item_data, $order, $order_items, $export );
    }

    /**
     * Process the order item.
     *
     * @since 2.7.3
     * @access private
     *
     * @param WC_Order_Item $item   The order item object.
     * @param array         $fields The selected fields.
     * @param WC_Order      $order  The order object.
     * @param object        $export The export settings.
     */
    private function _process_order_item( $item, $fields, $order, $export ) {
        $data      = array();
        $item_data = $item->get_data();
        $item_id   = $item->get_id();
        $item_type = $item->get_type();

        $this->_get_order_item_data( $data, $fields, $item, $item_data, $item_type );

        switch ( $item_type ) {
            case 'line_item':
                $this->_get_order_item_refund_data( $data, $fields, $item, $item_type, $order );
                $this->_get_order_item_line_item_data( $data, $fields, $item, $item_data, $order, $export->category_separator, $export->export_format );
                break;
            case 'shipping':
                $this->_get_order_item_refund_data( $data, $fields, $item, $item_type, $order );
                $this->_get_order_item_shipping_data( $data, $fields, $item, $item_data );
                break;
            case 'tax':
                $this->_get_order_item_tax_data( $data, $fields, $item, $item_data );
                break;
            case 'coupon':
                $this->_get_order_item_coupon_data( $data, $fields, $item, $item_data );
                break;
        }

        // Custom Order Items.
        $this->_get_order_item_custom_data( $data, $fields, $item );

        /**
         * Filter the Order Items dataset.
         * Allows to modify the Order Items dataset before it is returned.
         *
         * @since 2.7.3
         *
         * @param array         $data            The default data array.
         * @param array         $fields          The selected fields.
         * @param WC_Order_Item $item            The order item object.
         * @param WC_Order      $order           The order object.
         * @param object        $export_settings The export settings object.
         */
        return apply_filters( 'wsed_order_items_export_data', $data, $fields, $item, $order, $export );
    }

    /**
     * Parse the dataset.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array  $dataset The dataset to parse.
     * @param object $export  The export settings object.
     * @return array
     */
    public function parse_dataset( $dataset, $export ) {
        // Set the default data.
        $default_data    = array();
        $max_order_items = $export->args['max_order_items'];
        $columns         = $export->columns;
        foreach ( $columns as $name => $label ) {
            if ( is_array( $label ) ) {
                $default_data[ $name ] = array();
                foreach ( $label as $child_name => $child_label ) {
                    $default_data[ $name ][ $child_name ] = '';
                }
            } else {
                $default_data[ $name ] = '';
            }
        }

        // Dynamically call a parser function based on the order items export format.
        $parse_order_item_data_func = '_parse_order_item_data_' . $export->args['order_items'];
        $this->$parse_order_item_data_func( $dataset, $default_data, $export->export_format, $export->category_separator );

        return $dataset;
    }

    /**
     * Parse the order item data for combined export.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array  $dataset           The dataset to parse.
     * @param array  $default_data      The default data array.
     * @param string $export_format     The export format.
     * @param string $category_separator The category separator.
     * @return array
     */
    private function _parse_order_item_data_combined( &$dataset, $default_data, $export_format, $category_separator ) {
        foreach ( $dataset as $i => $data ) {
            if ( ! empty( $data['order_items'] ) ) {
                $order_items = array();
                foreach ( $data['order_items'] as $key => $order_item ) {
                    foreach ( $order_item as $field => $value ) {
                        $order_items[ $field ][] = $value;
                    }
                }

                // Remove the order_items key from the dataset.
                unset( $dataset[ $i ]['order_items'], $data['order_items'] );

                $order_items_data = array();
                foreach ( $order_items as $field => $values ) {
                    // Add 'order_items_' prefix to the order item fields.
                    $order_items_data[ "order_items_{$field}" ] = implode( $category_separator, $values );
                }

                // Re-assign the order items data to main data array based on the export format.
                if ( in_array( $export_format, array( 'csv', 'tsv', 'xls', 'xlsx' ), true ) ) {
                    $data = array_merge( $data, $order_items_data );
                } else {
                    $data['order_items'] = $order_items_data;
                }
            }

            // Strip out data if not exist in $default_data.
            $data = array_intersect_key( $data, $default_data );

            // Add the default data to the dataset.
            $dataset[ $i ] = wp_parse_args( $data, $default_data );
        }
        return $dataset;
    }

    /**
     * Parse the order item data for unique export.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array  $dataset           The dataset to parse.
     * @param array  $default_data      The default data array.
     * @param string $export_format     The export format.
     * @param string $category_separator The category separator.
     * @return array
     */
    private function _parse_order_item_data_unique( &$dataset, $default_data, $export_format, $category_separator ) {
        // For XML, RSS and JSON export formats, the order items is in its own array.
        // We have to get the default order items data and unset them from the default data array.
        $default_order_item_data = array();
        if ( ! empty( $default_data['order_items'] ) ) {
            $default_order_item_data = $default_data['order_items'];
            unset( $default_data['order_items'] );
        }

        foreach ( $dataset as $i => $data ) {
            // Add 'order_items_' prefix to the order item fields,
            // then assign the data to the order item data array.
            if ( ! empty( $data['order_items'] ) ) {
                $order_items      = $data['order_items'];
                $order_items_data = array();

                // Remove the order_items key from the dataset.
                unset( $dataset[ $i ]['order_items'], $data['order_items'] );

                if ( in_array( $export_format, array( 'csv', 'tsv', 'xls', 'xlsx' ), true ) ) {
                    foreach ( $order_items as $key => $order_item ) {
                        foreach ( $order_item as $field => $value ) {
                            $order_items_data[ "order_items_{$key}_{$field}" ] = $value;
                        }
                    }
                    $data = array_merge( $data, $order_items_data );
                } else {
                    foreach ( $order_items as $key => $order_item ) {
                        foreach ( $order_item as $field => $value ) {
                            $order_items_data[ $key ][ "order_items_{$field}" ] = $value;
                        }

                        // Strip out data if not exist in $default_order_item_data.
                        $order_items_data[ $key ] = array_intersect_key( $order_items_data[ $key ], $default_order_item_data );

                        /**
                         * Individually parse the order item data with the default order item data.
                         * Because the order items data for XML, RSS and JSON export formats are stored in their own array.
                         * This is to ensure that the order items data is consistent across all the order items.
                         */
                        $order_items_data[ $key ] = wp_parse_args( $order_items_data[ $key ], $default_order_item_data );
                    }
                }
            }

            // Strip out data if not exist in $default_data.
            $data = array_intersect_key( $data, $default_data );

            // Add the default data to the dataset.
            $dataset[ $i ] = wp_parse_args( $data, $default_data );

            // For XML, RSS and JSON export formats, assign the modified order items data to the dataset.
            if ( ! empty( $order_items_data ) && in_array( $export_format, array( 'xml', 'rss', 'json' ), true ) ) {
                $dataset[ $i ]['order_items'] = $order_items_data;
            }
        }
        return $dataset;
    }

    /**
     * Parse the order item data for individual export.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array  $dataset           The dataset to parse.
     * @param array  $default_data      The default data array.
     * @param string $export_format     The export format.
     * @param string $category_separator The category separator.
     * @return array
     */
    private function _parse_order_item_data_individual( &$dataset, $default_data, $export_format, $category_separator ) {
        $dataset_individual = $dataset;
        $dataset            = array();
        $i                  = 1;
        foreach ( $dataset_individual as $data ) {
            if ( ! empty( $data['order_items'] ) ) {
                $order_items = $data['order_items'];
                unset( $data['order_items'] );

                foreach ( $order_items as $key => $order_item ) {
                    // Add 'order_items_' prefix to the order item fields.
                    $order_items_data = array();
                    foreach ( $order_item as $field => $value ) {
                        $order_items_data[ "order_items_{$field}" ] = $value;
                    }

                    // Re-assign the order items data to main data array based on the export format.
                    if ( in_array( $export_format, array( 'csv', 'tsv', 'xls', 'xlsx' ), true ) ) {
                        $data = array_merge( $data, $order_items_data );
                    } else {
                        $data['order_items'] = $order_items_data;
                    }

                    // Strip out data if not exist in $default_data.
                    $data = array_intersect_key( $data, $default_data );

                    $dataset[ $i ] = wp_parse_args( $data, $default_data );
                    ++$i;
                }
            } else {
                // Strip out data if not exist in $default_data.
                $data = array_intersect_key( $data, $default_data );

                $dataset[ $i ] = wp_parse_args( $data, $default_data );
                ++$i;
            }
        }
        return $dataset;
    }

    /**
     * Override export columns.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array  $columns The default columns.
     * @param array  $fields  The selected fields.
     * @param object $export  The export settings object.
     */
    public function export_columns( $columns, $fields, $export ) {
        // Return if fields are empty.
        if ( empty( $fields ) ) {
            return $columns;
        }

        /**
         * Filter to modify the Export Columns for unique Order Items or XML, RSS and JSON.
         *
         * Order Items fields are removed from the main fields array then,
         * For unique Order Items, generate the order items fields with the unique order items fields.
         * For XML, RSS and JSON export formats, strip the 'Order Items: ' prefix from the Order Items fields.
         */
        if ( 'unique' === $export->args['order_items'] ||
            in_array( $export->export_format, array( 'xml', 'rss', 'json' ), true )
        ) {
            $columns = array();

            // Extract the Order Items fields from the main order fields array.
            [ $order_fields, $order_item_fields ] = $this->_extract_order_item_fields( $fields, $this->order_item_fields );

            // Assign the main fields to the columns array.
            if ( ! empty( $order_fields ) ) {
                foreach ( $order_fields as $field ) {
                    $columns[ $field['name'] ] = $field['label'];
                }
            }

            if ( ! empty( $order_item_fields ) ) {
                // Set the Order Items fields to the class property.
                $this->order_item_fields = $order_item_fields;

                // Strip the 'Order Items: ' prefix from the Order Items fields.
                if ( in_array( $export->export_format, array( 'xml', 'rss', 'json' ), true ) ) {
                    foreach ( $order_item_fields as $field ) {
                        $columns['order_items'][ $field['name'] ] = str_replace( 'Order Items: ', '', $field['label'] );
                    }
                } else { // Add 'order_items_' prefix to the Order Items fields.
                    // Check for the max_order_items override.
                    $max_order_items = get_option( WOO_CE_PREFIX . '_max_order_items', 10 );
                    if ( isset( $export->args['max_order_items'] ) && ! empty( $export->args['max_order_items'] ) ) {
                        $max_order_items = $export->args['max_order_items'];
                    }

                    // Generate the unique Order Items fields.
                    for ( $i = 1; $i <= $max_order_items; $i++ ) {
                        foreach ( $order_item_fields as $field ) {
                            $name  = str_replace( 'order_items_', '', $field['name'] );
                            $label = isset( $field['label'] ) ? str_replace( 'Order Items: ', '', $field['label'] ) : '';

                            // translators: %1$d - order item number, %2$s - field label.
                            $columns[ "order_items_{$i}_{$name}" ] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, $label );
                        }
                    }
                }
            }
        }
        return $columns;
    }

    /**
     * Extract the Order Items fields from the main order fields array.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array $fields            The fields array.
     * @param array $order_item_fields The Order Items fields array.
     * @return array The Order and Order Items fields.
     */
    private function _extract_order_item_fields( $fields, $order_item_fields ) {
        // Remove the Order Items fields from the main fields array.
        $filtered_order_fields = array_filter(
            $fields,
            function ( $field ) use ( $order_item_fields ) {
                return ! in_array( $field['name'], array_column( $order_item_fields, 'name' ), true );
            }
        );

        // Filter the Order Items fields from the main fields array.
        $filtered_order_item_fields = array_filter(
            $fields,
            function ( $field ) use ( $order_item_fields ) {
                return in_array( $field['name'], array_column( $order_item_fields, 'name' ), true );
            }
        );

        return array( $filtered_order_fields, $filtered_order_item_fields );
    }

    /**
     * Get order dataset values.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array    $data       The data array.
     * @param array    $fields     The fields array.
     * @param WC_Order $order      The order object.
     * @param array    $order_data The order data.
     * @return array
     */
    private function _get_order_data( &$data, $fields, $order, $order_data ) {
        foreach ( $fields as $key => $field ) {
            switch ( $key ) {
                case 'purchase_id':
                case 'post_id':
                    $data[ $key ] = $order->get_id();
                    break;
                case 'post_status':
                case 'payment_status':
                    $data[ $key ] = wc_get_order_status_name( $order_data['status'] );
                    break;
                case 'order_currency':
                    $data[ $key ] = $order_data['currency'];
                    break;
                case 'payment_gateway_id':
                    $data[ $key ] = $order_data['payment_method'];
                    break;
                case 'payment_gateway':
                    $data[ $key ] = $order_data['payment_method_title'];
                    break;
                case 'order_key':
                    $data[ $key ] = $order_data['order_key'];
                    break;
                case 'transaction_id':
                    $data[ $key ] = $order_data['transaction_id'];
                    break;
                case 'created_via':
                    $data[ $key ] = $order_data['created_via'];
                    break;
                case 'cart_hash':
                    $data[ $key ] = $order_data['cart_hash'];
                    break;
                case 'purchase_date':
                    $data[ $key ] = Formatting::format_date( $order_data['date_created'] );
                    break;
                case 'modified_date':
                    $data[ $key ] = Formatting::format_date( $order_data['date_modified'] );
                    break;
                case 'purchase_time':
                    $data[ $key ] = Formatting::format_date( $order_data['date_created'], get_option( 'time_format' ) );
                    break;
                case 'ip_address':
                    $data[ $key ] = $order_data['customer_ip_address'];
                    break;
                case 'browser_agent':
                    $data[ $key ] = $order_data['customer_user_agent'];
                    break;
                case 'total_quantity':
                    $data[ $key ] = $order->get_item_count();
                    break;
                case 'total_order_items':
                    $data[ $key ] = $order->get_item_count( 'line_items' );
                    break;
                case 'has_downloads':
                    $data[ $key ] = $order->has_downloadable_item() ? 'Yes' : 'No';
                    break;
                case 'has_downloaded':
                    $data[ $key ] = $order->is_download_permitted() ? 'Yes' : 'No';
                    break;
                case 'customer_message':
                    $data[ $key ] = $order_data['customer_note'];
                    break;

                // Shipping fields.
                case 'billing_first_name':
                    $data[ $key ] = $order_data['billing']['first_name'];
                    break;
                case 'billing_last_name':
                    $data[ $key ] = $order_data['billing']['last_name'];
                    break;
                case 'billing_full_name':
                    $data[ $key ] = $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'];
                    break;
                case 'billing_company':
                    $data[ $key ] = $order_data['billing']['company'];
                    break;
                case 'billing_address':
                    $data[ $key ] = $order_data['billing']['address_1'] . ' ' . $order_data['billing']['address_2'];
                    break;
                case 'billing_address_1':
                    $data[ $key ] = $order_data['billing']['address_1'];
                    break;
                case 'billing_address_2':
                    $data[ $key ] = $order_data['billing']['address_2'];
                    break;
                case 'billing_city':
                    $data[ $key ] = $order_data['billing']['city'];
                    break;
                case 'billing_postcode':
                    $data[ $key ] = $order_data['billing']['postcode'];
                    break;
                case 'billing_state':
                    $data[ $key ] = $order_data['billing']['state'];
                    break;
                case 'billing_country':
                    $data[ $key ] = $order_data['billing']['country'];
                    break;
                case 'billing_email':
                    $data[ $key ] = $order_data['billing']['email'];
                    break;
                case 'billing_phone':
                    $data[ $key ] = $order_data['billing']['phone'];
                    break;
                case 'billing_state_full':
                    $data[ $key ] = Formatting::state_name( $order_data['billing']['country'], $order_data['billing']['state'] );
                    break;
                case 'billing_country_full':
                    $data[ $key ] = Formatting::country_name( $order_data['billing']['country'] );
                    break;

                // Shipping fields.
                case 'shipping_first_name':
                    $data[ $key ] = $order_data['shipping']['first_name'];
                    break;
                case 'shipping_last_name':
                    $data[ $key ] = $order_data['shipping']['last_name'];
                    break;
                case 'shipping_full_name':
                    $data[ $key ] = $order_data['shipping']['first_name'] . ' ' . $order_data['shipping']['last_name'];
                    break;
                case 'shipping_company':
                    $data[ $key ] = $order_data['shipping']['company'];
                    break;
                case 'shipping_address':
                    $data[ $key ] = $order_data['shipping']['address_1'] . ' ' . $order_data['shipping']['address_2'];
                    break;
                case 'shipping_address_1':
                    $data[ $key ] = $order_data['shipping']['address_1'];
                    break;
                case 'shipping_address_2':
                    $data[ $key ] = $order_data['shipping']['address_2'];
                    break;
                case 'shipping_city':
                    $data[ $key ] = $order_data['shipping']['city'];
                    break;
                case 'shipping_postcode':
                    $data[ $key ] = $order_data['shipping']['postcode'];
                    break;
                case 'shipping_state':
                    $data[ $key ] = $order_data['shipping']['state'];
                    break;
                case 'shipping_country':
                    $data[ $key ] = $order_data['shipping']['country'];
                    break;
                case 'shipping_phone':
                    $data[ $key ] = $order_data['shipping']['phone'];
                    break;
                case 'shipping_state_full':
                    $data[ $key ] = Formatting::state_name( $order_data['shipping']['country'], $order_data['shipping']['state'] );
                    break;
                case 'shipping_country_full':
                    $data[ $key ] = Formatting::country_name( $order_data['shipping']['country'] );
                    break;
                case 'shipping_cost':
                    $data[ $key ] = Formatting::format_price( $order_data['shipping_total'] );
                    break;
                case 'order_shipping_tax':
                    $data[ $key ] = Formatting::format_price( $order_data['shipping_tax'] );
                    break;
                case 'shipping_excl_tax':
                    $data[ $key ] = Formatting::format_price( $order_data['shipping_total'] );
                    break;
                case 'shipping_incl_tax':
                    $data[ $key ] = Formatting::format_price( (float) $order_data['shipping_total'] + (float) $order_data['shipping_tax'] );
                    break;

                // User fields.
                case 'user_id':
                    $data[ $key ] = $order_data['customer_id'];
                    break;

                // Prices.
                case 'purchase_total':
                    /**
                     * Order total deducted with refund amount.
                     * Why not `get_total()`? Because `get_total()` includes the refunded amount.
                     * This function is used to get the total amount of the order, minus the refunded amount.
                     */
                    $data[ $key ] = Formatting::format_price( $order->get_remaining_refund_amount() );
                    break;
                case 'purchase_subtotal':
                    /**
                     * The order subtotal is the total of the items in the order, before tax and shipping.
                     * It is the sum of the line items, minus the sum of the line item discounts.
                     * $total_refunded_subtotal stands for the refund subtotal amount, without shipping and refund tax.
                     */
                    $total_refunded_subtotal   = (float) $order->get_total_refunded() - ( (float) $order->get_total_shipping_refunded() + (float) $order->get_total_tax_refunded() );
                    $data['purchase_subtotal'] = Formatting::format_price( (float) $order->get_subtotal() - (float) $order_data['discount_total'] - (float) $total_refunded_subtotal );
                    break;
                case 'purchase_total_tax':
                    $data['purchase_total_tax'] = Formatting::format_price( (float) $order_data['total_tax'] - (float) $order->get_total_tax_refunded() );
                    break;
                case 'order_sales_tax':
                    $data['order_sales_tax'] = Formatting::format_price( $order_data['cart_tax'] );
                    break;
                case 'order_subtotal_excl_tax':
                    $data['order_subtotal_excl_tax'] = Formatting::format_price( (float) $order->get_remaining_refund_amount() - (float) $order_data['total_tax'] - (float) $order->get_total_tax_refunded() );
                    break;
                case 'order_discount':
                    $data['order_discount'] = Formatting::format_price( $order_data['discount_total'] );
                    break;
            }
        }
        return $data;
    }

    /**
     * Get the order notes.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array    $data               The order data array.
     * @param array    $fields             The fields array.
     * @param WC_Order $order              The order object.
     * @param string   $category_separator The category separator.
     * @return array
     */
    private function _get_order_notes_data( &$data, $fields, $order, $category_separator ) {
        if ( isset( $fields['order_notes'] ) || isset( $fields['customer_notes'] ) ) {
            $order_notes_data = array();
            $order_notes      = wc_get_order_notes( array( 'order_id' => $order->get_id() ) );
            if ( ! empty( $order_notes ) ) {
                foreach ( $order_notes as $note ) {
                    if ( $note->customer_note ) {
                        $order_notes_data['customer_notes'][] = Formatting::format_date( $note->date_created ) . ': ' . $note->content;
                    } else {
                        $order_notes_data['order_notes'][] = Formatting::format_date( $note->date_created ) . ': ' . $note->content;
                    }
                }
                $data['order_notes']    = ! empty( $order_notes_data['order_notes'] ) ? implode( $category_separator, $order_notes_data['order_notes'] ) : '';
                $data['customer_notes'] = ! empty( $order_notes_data['customer_notes'] ) ? implode( $category_separator, $order_notes_data['customer_notes'] ) : '';
            }
        }
        return $data;
    }

    /**
     * Get the customer order notes.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array    $data               The order data array.
     * @param array    $fields             The fields array.
     * @param WC_Order $order              The order object.
     * @param string   $category_separator The category separator.
     * @return array
     */
    private function _get_order_shipping_method_data( &$data, $fields, $order, $category_separator ) {
        // Shipping method.
        if (
            isset( $fields['shipping_method_id'] ) ||
            isset( $fields['shipping_instance_id'] ) ||
            isset( $fields['shipping_method'] ) ||
            isset( $fields['shipping_cost'] ) ||
            isset( $fields['shipping_excl_tax'] )
        ) {
            $shipping_custom_data = array();
            $order_shipping       = $order->get_items( 'shipping' );
            if ( ! empty( $order_shipping ) ) {
                foreach ( $order_shipping as $id => $shipping ) {
                    $shipping_data = $shipping->get_data();

                    $shipping_custom_data['shipping_method_id'][ $id ]   = $shipping_data['method_id'];
                    $shipping_custom_data['shipping_instance_id'][ $id ] = $shipping_data['instance_id'];
                    $shipping_custom_data['shipping_method'][ $id ]      = $shipping_data['method_title'];
                    $shipping_custom_data['shipping_cost'][ $id ]        = Formatting::format_price( (float) $shipping_data['total'] + (float) $shipping_data['total_tax'] );
                    $shipping_custom_data['shipping_excl_tax'][ $id ]    = Formatting::format_price( $shipping_data['total'] );
                }
            }

            if ( isset( $fields['shipping_method_id'] ) && ! empty( $shipping_custom_data['shipping_method_id'] ) ) {
                $data['shipping_method_id'] = implode( $category_separator, $shipping_custom_data['shipping_method_id'] );
            }
            if ( isset( $fields['shipping_instance_id'] ) && ! empty( $shipping_custom_data['shipping_instance_id'] ) ) {
                $data['shipping_instance_id'] = implode( $category_separator, $shipping_custom_data['shipping_instance_id'] );
            }
            if ( isset( $fields['shipping_method'] ) && ! empty( $shipping_custom_data['shipping_method'] ) ) {
                $data['shipping_method'] = implode( $category_separator, $shipping_custom_data['shipping_method'] );
            }
            if ( isset( $fields['shipping_cost'] ) && ! empty( $shipping_custom_data['shipping_cost'] ) ) {
                $data['shipping_cost'] = implode( $category_separator, $shipping_custom_data['shipping_cost'] );
            }
            if ( isset( $fields['shipping_excl_tax'] ) && ! empty( $shipping_custom_data['shipping_excl_tax'] ) ) {
                $data['shipping_excl_tax'] = implode( $category_separator, $shipping_custom_data['shipping_excl_tax'] );
            }
        }
        return $data;
    }

    /**
     * Calculate the total weight of the order.
     *
     * WooCommerce doesn't have a method to get the total weight of the order.
     * This is a custom method to get the total weight of the order.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array    $data   The order data array.
     * @param array    $fields The fields array.
     * @param WC_Order $order  The order object.
     * @return array
     */
    private function _get_order_shipping_weight_total_data( &$data, $fields, $order ) {
        if ( isset( $fields['shipping_weight_total'] ) ) {
            $shipping_weight_total = 0;
            foreach ( $order->get_items() as $item_id => $item ) {
                $product = $item->get_product();
                if ( $product ) {
                    $weight = $product->get_weight();
                    if ( ! empty( $weight ) ) {
                        $shipping_weight_total += (float) $weight * absint( $item->get_quantity() );
                    }
                }
            }
            $data['shipping_weight_total'] = $shipping_weight_total;
        }
        return $data;
    }

    /**
     * Get the order taxes data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array    $data   The order data array.
     * @param array    $fields The fields array.
     * @param WC_Order $order  The order object.
     * @param string   $category_separator The category separator.
     * @return array
     */
    private function _get_order_taxes_data( &$data, $fields, $order, $category_separator ) {
        $selected_tax_rate_fields = $this->_get_selected_tax_rate_fields( $fields );

        // Taxes.
        if ( isset( $fields['order_tax_percentage'] ) || ! empty( $selected_tax_rate_fields ) ) {
            $tax_rates_percent = array();
            $taxes             = $order->get_taxes();
            if ( ! empty( $taxes ) ) {
                foreach ( $taxes as $tax ) {
                    $rate_id           = $tax->get_rate_id();
                    $tax_rate_class_id = $this->_get_tax_rate_class_id_by_rate_id( $rate_id );

                    if ( in_array( 'purchase_total_tax_rate_' . $tax_rate_class_id, $selected_tax_rate_fields, true ) ) {
                        $data[ 'purchase_total_tax_rate_' . $tax_rate_class_id ] = (float) $tax->get_tax_total() + (float) $tax->get_shipping_tax_total();
                    }

                    if ( isset( $fields['order_tax_percentage'] ) ) {
                        $tax_rates_percent[] = $tax->get_rate_percent() . apply_filters( 'wsed_order_tax_percentage_format', '%' );
                    }
                }

                if ( isset( $fields['order_tax_percentage'] ) && ! empty( $tax_rates_percent ) ) {
                    $data['order_tax_percentage'] = implode( $category_separator, $tax_rates_percent );
                }
            }
        }
        return $data;
    }

    /**
     * Getting Tax class ID for the tax rate.
     *
     * WooCommerce doesn't have identifier or unique ID to get the standart tax class ID.
     * So, we are using '0' as a default value for the standard tax class ID.
     *
     * NOTE: the rate id is the unique identifier for each tax rate under tax class.
     *       Here, we want to get the tax class ID.
     *
     * @since 2.7.3
     * @access private
     *
     * @param int $rate_id The tax rate ID.
     * @return string The tax class ID.
     */
    private function _get_tax_rate_class_id_by_rate_id( $rate_id ) {
        $tax_rate = WC_Tax::_get_tax_rate( $rate_id );
        return empty( $tax_rate['tax_rate_class'] ) ? '0' : $tax_rate['tax_rate_class'];
    }

    /**
     * Get the order taxes data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array    $data   The order data array.
     * @param array    $fields The fields array.
     * @param WC_Order $order  The order object.
     * @param string   $category_separator The category separator.
     * @return array
     */
    private function _get_order_user_data( &$data, $fields, $order, $category_separator ) {
        // Order user data.
        if ( ( isset( $fields['user_name'] ) || isset( $fields['user_role'] ) ) && 0 !== $order->get_customer_id() ) {
            $user = get_userdata( $order->get_customer_id() );

            if ( $user ) {
                $data['user_name'] = isset( $fields['user_name'] ) ? $user->user_login : '';
                $data['user_role'] = isset( $fields['user_role'] ) && ! empty( $user->roles ) ? implode( $category_separator, $user->roles ) : '';
            }
        }
        return $data;
    }

    /**
     * Get the order refund data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array    $data   The order data array.
     * @param array    $fields The fields array.
     * @param WC_Order $order  The order object.
     * @param string   $category_separator The category separator.
     * @return array
     */
    private function _get_order_refund_data( &$data, $fields, $order, $category_separator ) {
        // Refunds.
        if ( isset( $fields['refund_total'] ) || isset( $fields['refund_tax'] ) || isset( $fields['refund_date'] ) ) {
            $refunds = $order->get_refunds();
            if ( ! empty( $refunds ) ) {
                if ( isset( $fields['refund_total'] ) ) {
                    $data['refund_total'] = $order->get_total_refunded();
                }
                if ( isset( $fields['refund_tax'] ) ) {
                    $data['refund_tax'] = $order->get_total_tax_refunded();
                }

                if ( isset( $fields['refund_date'] ) ) {
                    $refunds_date = array();
                    foreach ( $refunds as $refund ) {
                        $refunds_date[] = Formatting::format_date( $refund->get_date_created() );
                    }
                    $data['refund_date'] = ( ! empty( $refunds_date ) ? implode( $category_separator, $refunds_date ) : '' );
                }
            }
        }
        return $data;
    }

    /**
     * Get the order coupon data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array    $data   The order data array.
     * @param array    $fields The fields array.
     * @param WC_Order $order  The order object.
     * @param string   $category_separator The category separator.
     * @return array
     */
    private function _get_order_coupon_data( &$data, $fields, $order, $category_separator ) {
        if ( isset( $fields['coupon_code'] ) || isset( $fields['coupon_description'] ) || isset( $fields['coupon_expiry_date'] ) ) {
            $coupons_data = array();
            $coupons      = $order->get_items( 'coupon' );
            if ( ! empty( $coupons ) ) {
                foreach ( $coupons as $coupon ) {
                    $coupon_data = $coupon->get_data();
                    $coupon_meta = $coupon->get_meta( 'coupon_data' );

                    $coupons_data['code'][]         = ! empty( $coupon_data['code'] ) ? $coupon_data['code'] : '';
                    $coupons_data['description'][]  = ! empty( $coupon_meta['description'] ) ? $coupon_meta['description'] : '';
                    $coupons_data['date_expires'][] = ! empty( $coupon_meta['date_expires'] ) ? Formatting::format_date( $coupon_meta['date_expires'] ) : '';
                }

                if ( isset( $fields['coupon_code'] ) && ! empty( $coupons_data['code'] ) ) {
                    $data['coupon_code'] = implode( $category_separator, $coupons_data['code'] );
                }
                if ( isset( $fields['coupon_description'] ) && ! empty( $coupons_data['description'] ) ) {
                    $data['coupon_description'] = implode( $category_separator, $coupons_data['description'] );
                }
                if ( isset( $fields['coupon_expiry_date'] ) && ! empty( $coupons_data['date_expires'] ) ) {
                    $data['coupon_expiry_date'] = implode( $category_separator, $coupons_data['date_expires'] );
                }
            }
        }
        return $data;
    }

    /**
     * Get custom order data.
     * This is the custom order data that is set by the user.
     * The custom order data is stored in the order meta.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array    $data   The order data array.
     * @param array    $fields The fields array.
     * @param WC_Order $order  The order object.
     * @return array
     */
    private function _get_order_custom_data( &$data, $fields, $order ) {
        // Custom Order fields.
        $custom_orders = get_option( WOO_CE_PREFIX . '_custom_orders', '' );
        if ( ! empty( $custom_orders ) ) {
            foreach ( $custom_orders as $custom_order ) {
                if ( isset( $fields[ $custom_order ] ) && 'on' === $fields[ $custom_order ] ) {
                    $data[ $custom_order ] = maybe_serialize( $order->get_meta( $custom_order, true ) );
                }
            }
        }
        return $data;
    }

    /**
     * Get the order item data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array         $data      The order data array.
     * @param array         $fields    The fields array.
     * @param WC_Order_Item $item      The order item object.
     * @param array         $item_data The order item data.
     * @param string        $item_type The order item type.
     * @return array
     */
    private function _get_order_item_data( &$data, $fields, $item, $item_data, $item_type ) {
        foreach ( $fields as $key => $field ) {
            switch ( $key ) {
                case 'order_items_id':
                    $data['id'] = $item_data['id'];
                    break;
                case 'order_items_name':
                    $data['name'] = $item_data['name'];
                    break;
                case 'order_items_type':
                    $data['type'] = Formatting::format_order_item_type( $item_type );
                    break;
                case 'order_items_type_id':
                    $data['type_id'] = $item_type;
                    break;
            }
        }
        return $data;
    }

    /**
     * Get the order item refund data.
     * Refund data in the order item level only available for 'line_item' and 'shipping'.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array         $data      The order data array.
     * @param array         $fields    The fields array.
     * @param WC_Order_Item $item      The order item object.
     * @param string        $item_type The order item type.
     * @param WC_Order      $order     The order object.
     * @return array
     */
    private function _get_order_item_refund_data( &$data, $fields, $item, $item_type, $order ) {
        if ( in_array( $item_type, array( 'line_item', 'shipping' ), true ) ) {
            foreach ( $fields as $key => $field ) {
                switch ( $key ) {
                    case 'order_items_refund_subtotal':
                        $data['refund_subtotal'] = $order->get_total_refunded_for_item( $item->get_id(), $item_type );
                        break;
                    case 'order_items_refund_quantity':
                        $data['refund_quantity'] = $order->get_qty_refunded_for_item( $item->get_id(), $item_type );
                        break;
                    case 'order_items_refund_subtotal_incl_tax':
                        $refunded_tax = 0;
                        $tax_lines    = $order->get_items( 'tax' );
                        if ( ! empty( $tax_lines ) ) {
                            foreach ( $tax_lines as $tax_line ) {
                                $refunded_tax += $order->get_tax_refunded_for_item( $item->get_id(), $tax_line->get_rate_id(), $item_type );
                            }
                        }
                        $data['refund_subtotal_incl_tax'] = $order->get_total_refunded_for_item( $item->get_id(), $item_type ) + $refunded_tax;
                        break;
                }
            }
        }
        return $data;
    }

    /**
     * Get the order item line item (product) data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array         $data               The order data array.
     * @param array         $fields             The fields array.
     * @param WC_Order_Item $item               The order item object.
     * @param array         $item_data          The order item data.
     * @param WC_Order      $order              The order object.
     * @param string        $category_separator The category separator.
     * @param string        $export_format      The export format.
     * @return array
     */
    private function _get_order_item_line_item_data( &$data, $fields, $item, $item_data, $order, $category_separator, $export_format ) {
        $product = $item->get_product();
        if ( $product && $product instanceof \WC_Product ) {
            $product_data = $product->get_data();
            foreach ( $fields as $key => $field ) {
                switch ( $key ) {
                    case 'order_items_product_id':
                        $data['product_id'] = $product_data['id'];
                        break;
                    case 'order_items_sku':
                        $data['sku'] = $product_data['sku'];
                        break;
                    case 'order_items_rrp':
                        $data['rrp'] = $product_data['regular_price'];
                        break;
                    case 'order_items_subtotal':
                        $data['subtotal'] = Formatting::format_price( $item_data['subtotal'] );
                        break;
                    case 'order_items_discount':
                        $data['discount'] = ( (float) $product_data['regular_price'] - (float) $item_data['quantity'] ) - (float) $item_data['subtotal'];
                        break;
                    case 'order_items_quantity':
                        $data['quantity'] = $item_data['quantity'];
                        break;
                    case 'order_items_stock':
                        $data['stock'] = $product_data['stock_quantity'];
                        break;
                    case 'order_items_total_sales':
                        $data['total_sales'] = $product_data['total_sales'];
                        break;
                    case 'order_items_description':
                        $data['description'] = $product_data['description'];
                        break;
                    case 'order_items_excerpt':
                        $data['excerpt'] = $product_data['short_description'];
                        break;
                    case 'order_items_tax_class':
                        $data['tax_class'] = $product_data['tax_class'];
                        break;
                    case 'order_items_publish_date':
                        $data['publish_date'] = Formatting::format_date( $product_data['date_created'] );
                        break;
                    case 'order_items_modified_date':
                        $data['modified_date'] = Formatting::format_date( $product_data['date_modified'] );
                        break;
                    case 'order_items_shipping_class':
                        $data['shipping_class'] = $product_data['shipping_class_id'];
                        break;
                    case 'order_items_length':
                        $data['length'] = $product_data['length'];
                        break;
                    case 'order_items_width':
                        $data['width'] = $product_data['width'];
                        break;
                    case 'order_items_height':
                        $data['height'] = $product_data['height'];
                        break;
                    case 'order_items_weight':
                        $data['weight'] = $product_data['weight'];
                        break;
                    case 'order_items_total_weight':
                        $data['total_weight'] = (float) $product_data['weight'] * (float) $item_data['quantity'];
                        break;
                    case 'order_items_total':
                        $data['total'] = Formatting::format_price( $item_data['total'] );
                        break;
                    case 'order_items_tax':
                        $data['tax'] = $item_data['total_tax'];
                        break;
                    case 'order_items_category':
                        $categories_name = array();
                        $categories      = $product_data['category_ids'];
                        if ( ! empty( $categories ) ) {
                            foreach ( $categories as $category_id ) {
                                $category = get_term_by( 'id', $category_id, 'product_cat' );
                                if ( $category ) {
                                    $categories_name[] = $category->name;
                                }
                            }
                        }
                        $data['category'] = ! empty( $categories_name ) ? implode( $category_separator, $categories_name ) : '';
                        break;
                    case 'order_items_tag':
                        $tags_name = array();
                        $tags      = $product_data['tag_ids'];
                        if ( ! empty( $tags ) ) {
                            foreach ( $tags as $tag_id ) {
                                $tag = get_term_by( 'id', $tag_id, 'product_tag' );
                                if ( $tag ) {
                                    $tags_name[] = $tag->name;
                                }
                            }
                        }
                        $data['tag'] = ! empty( $tags_name ) ? implode( $category_separator, $tags_name ) : '';
                        break;
                    case 'order_items_tax':
                        $data['tax'] = $item_data['total_tax'];
                        break;
                    case 'order_items_tax':
                        $data['tax'] = $item_data['total_tax'];
                        break;
                }
            }

            $this->_get_order_item_line_item_tax_data( $data, $fields, $item, $item_data, $order );
            $this->_get_order_item_line_item_image_embed_data( $data, $fields, $product, $export_format );
            $this->_get_order_item_line_item_variation_data( $data, $fields, $item, $item_data, $product, $product_data );

            // Custom Order Products data.
            $this->_get_order_item_line_custom_data( $data, $fields, $product );
        }
        return $data;
    }

    /**
     * Get the order item line item tax data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array         $data      The order data array.
     * @param array         $fields    The fields array.
     * @param WC_Order_Item $item      The order item object.
     * @param array         $item_data The order item data.
     * @param WC_Order      $order     The order object.
     * @return array
     */
    private function _get_order_item_line_item_tax_data( &$data, $fields, $item, $item_data, $order ) {
        // Check if Purchase Total Tax Rate is selected.
        $selected_tax_rate = $this->_get_selected_tax_rate_fields( $fields, true );

        // Populate the Tax fields.
        if (
            isset( $fields['order_items_tax_subtotal'] ) ||
            isset( $fields['order_items_tax_percentage'] ) ||
            ! empty( $selected_tax_rate )
        ) {
            if ( isset( $fields['order_items_tax_subtotal'] ) ) {
                $data['tax_subtotal'] = $item_data['subtotal_tax'];
            }
            if ( isset( $fields['order_items_tax_percentage'] ) ) {
                $data['tax_percentage'] = round( (float) $item_data['subtotal_tax'] / (float) $item_data['subtotal'] * 100, 2 ) . '%';
            }

            if ( ! empty( $selected_tax_rate ) ) {
                $tax_lines = $order->get_items( 'tax' );
                if ( ! empty( $tax_lines ) ) {
                    foreach ( $tax_lines as $tax_line ) {
                        $tax_line_data     = $tax_line->get_data();
                        $tax_rate_class_id = $this->_get_tax_rate_class_id_by_rate_id( $tax_line_data['rate_id'] );

                        $data[ 'tax_rate_' . $tax_rate_class_id ] = (float) $item_data['subtotal'] * ( (float) $tax_line_data['rate_percent'] / 100 );
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Get the order item line item image embed data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array      $data          The order data array.
     * @param array      $fields        The fields array.
     * @param WC_Product $product       The product object.
     * @param string     $export_format The export format.
     * @return array
     */
    private function _get_order_item_line_item_image_embed_data( &$data, $fields, $product, $export_format ) {
        // Check if Image Embed is selected.
        if ( isset( $fields['order_items_image_embed'] ) ) {
            $image_id = $product->get_image_id();
            if ( ! empty( $image_id ) ) {
                $image_url = wp_get_attachment_image_src( $image_id, 'thumbnail' );
                if ( ! empty( $image_url ) ) {
                    // For XLSX format, we need to get the image path, to embed the image in the Excel file.
                    // For other formats, we can use the image URL.
                    if ( 'xlsx' === $export_format ) {
                        $upload_dir = wp_upload_dir();
                        $image_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $image_url[0] );

                        $data['image_embed'] = $image_path;
                    } else {
                        $data['image_embed'] = $image_url[0];
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Get the order item line item variation data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array         $data         The order data array.
     * @param array         $fields       The fields array.
     * @param WC_Order_Item $item         The order item object.
     * @param array         $item_data    The order item data.
     * @param WC_Product    $product      The product object.
     * @param array         $product_data The product data.
     * @return array
     */
    private function _get_order_item_line_item_variation_data( &$data, $fields, $item, $item_data, $product, $product_data ) {
        if ( $product->get_type() !== 'variation' ) {
            return $data;
        }

        if ( isset( $fields['order_items_product_id'] ) ) {
            $data['product_id'] = $product_data['parent_id'];
        }

        if ( isset( $fields['order_items_variation'] ) ) {
            $data['variation'] = $product_data['attribute_summary'];
        }

        if ( isset( $fields['order_items_variation_id'] ) ) {
            $data['variation_id'] = $item_data['variation_id'];
        }

        // Check if Variation fields are selected.
        $selected_attributes_fields = $this->_get_selected_order_item_attribute_fields( $fields );
        if ( ! empty( $selected_attributes_fields ) && ! empty( $product_data['attributes'] ) ) {
            foreach ( $product_data['attributes'] as $attribute_key => $attribute ) {
                if ( 'pa_' === substr( $attribute_key, 0, 3 ) ) {
                    // Remove 'pa_' prefix from the attribute key.
                    $attribute_key_no_prefix = substr( $attribute_key, 3 );

                    $data[ 'attribute_' . $attribute_key_no_prefix ]         = $item->get_meta( $attribute_key );
                    $data[ 'product_attribute_' . $attribute_key_no_prefix ] = $product->get_attribute( $attribute_key );
                }
            }
        }
        return $data;
    }

    /**
     * Get the order item line custom data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array      $data              The order data array.
     * @param array      $fields            The fields array.
     * @param WC_Product $product      The product object.
     * @return array
     */
    private function _get_order_item_line_custom_data( &$data, $fields, $product ) {
        $product_meta_data = Product::instance()->get_product_meta_data( $product->get_id() );

        // Custom Order Products.
        $custom_order_products = get_option( WOO_CE_PREFIX . '_custom_order_products', '' );
        if ( ! empty( $custom_order_products ) ) {
            foreach ( $custom_order_products as $custom_order_product ) {
                if ( isset( $fields[ 'order_items_' . $custom_order_product ] ) && 'on' === $fields[ 'order_items_' . $custom_order_product ] ) {
                    $data[ $custom_order_product ] = $product_meta_data[ $custom_order_product ] ?? '';
                }
            }
        }

        // Custom Products.
        $custom_products = get_option( WOO_CE_PREFIX . '_custom_products', '' );
        if ( ! empty( $custom_products ) ) {
            foreach ( $custom_products as $custom_product ) {
                if ( isset( $fields[ 'order_items_' . $custom_product ] ) && 'on' === $fields[ 'order_items_' . $custom_product ] ) {
                    $data[ $custom_product ] = $product_meta_data[ $custom_product ] ?? '';
                }
            }
        }
        return $data;
    }

    /**
     * Get the order item shipping data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array         $data      The order data array.
     * @param array         $fields    The fields array.
     * @param WC_Order_Item $item      The order item object.
     * @param array         $item_data The order item data.
     * @return array
     */
    private function _get_order_item_shipping_data( &$data, $fields, $item, $item_data ) {
        foreach ( $fields as $key => $field ) {
            switch ( $key ) {
                case 'order_items_total':
                    $data['total'] = Formatting::format_price( $item_data['total'] );
                    break;
                case 'order_items_tax':
                    $data['tax'] = $item_data['total_tax'];
                    break;
                case 'order_items_tax_percentage':
                    $data['tax_percentage'] = round( (float) $item_data['total_tax'] / (float) $item_data['total'] * 100, 2 ) . '%';
                    break;
                case 'order_items_description':
                    $data['description'] = html_entity_decode( $item->get_meta( 'Items' ) );
                    break;
            }
        }
        return $data;
    }

    /**
     * Get the order item tax data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array         $data      The order data array.
     * @param array         $fields    The fields array.
     * @param WC_Order_Item $item      The order item object.
     * @param array         $item_data The order item data.
     * @return array
     */
    private function _get_order_item_tax_data( &$data, $fields, $item, $item_data ) {
        foreach ( $fields as $key => $field ) {
            switch ( $key ) {
                case 'order_items_name':
                    $data['name'] = $item_data['rate_code'];
                    break;
                case 'order_items_tax':
                    $data['tax'] = Formatting::format_price( (float) $item_data['tax_total'] + (float) $item_data['shipping_tax_total'] );
                    break;
                case 'order_items_tax_subtotal':
                    $data['tax_subtotal'] = Formatting::format_price( $item_data['tax_total'] );
                    break;
                case 'order_items_tax_percentage':
                    $data['tax_percentage'] = $item_data['rate_percent'] . '%';
                    break;
            }
        }
        return $data;
    }

    /**
     * Get the order item coupon data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array         $data      The order data array.
     * @param array         $fields    The fields array.
     * @param WC_Order_Item $item      The order item object.
     * @param array         $item_data The order item data.
     * @return array
     */
    private function _get_order_item_coupon_data( &$data, $fields, $item, $item_data ) {
        // Get coupon data from meta.
        $coupon_data = $item->get_meta( 'coupon_data' );

        foreach ( $fields as $key => $field ) {
            switch ( $key ) {
                case 'order_items_publish_date':
                    $data['publish_date'] = Formatting::format_date( $coupon_data['date_created'] );
                    break;
                case 'order_items_modified_date':
                    $data['modified_date'] = Formatting::format_date( $coupon_data['date_modified'] );
                    break;
                case 'order_items_name':
                    $data['name'] = $coupon_data['code'];
                    break;
                case 'order_items_description':
                    $data['description'] = $coupon_data['description'];
                    break;
                case 'order_items_excerpt':
                    $data['excerpt'] = $coupon_data['description'];
                    break;
                case 'order_items_discount':
                    $data['discount'] = $item_data['discount'];
                    break;
            }
        }
        return $data;
    }

    /**
     * Get the order item refund data.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array           $data        The order data array.
     * @param array           $fields      The fields array.
     * @param WC_Order_Refund $refund      The refund object.
     * @param array           $refund_data The refund data.
     * @return array
     */
    private function _get_order_items_refund_data( &$data, $fields, $refund, $refund_data ) {
        foreach ( $fields as $key => $field ) {
            switch ( $key ) {
                case 'order_items_id':
                    $data['id'] = $refund->get_id();
                    break;
                case 'order_items_name':
                    $data['name'] = html_entity_decode( $refund->get_post_title() );
                    break;
                case 'order_items_type':
                    $data['type'] = Formatting::format_order_item_type( $refund->get_type() );
                    break;
                case 'order_items_type_id':
                    $data['type'] = $refund->get_type();
                    break;
                case 'order_items_publish_date':
                    $data['publish_date'] = Formatting::format_date( $refund_data['date_created'] );
                    break;
                case 'order_items_modified_date':
                    Formatting::format_date( $refund_data['date_modified'] );
                    break;
                case 'order_items_refund_quantity':
                    $qty = 0;
                    foreach ( $refund->get_items() as $refunded_item ) {
                        $qty += $refunded_item->get_quantity();
                    }
                    $data['refund_quantity'] = $qty;
                    break;
                case 'order_items_refund_subtotal_incl_tax':
                    $data['refund_subtotal_incl_tax'] = (float) $refund_data['total'] - ( (float) $refund_data['shipping_total'] + (float) $refund_data['shipping_tax'] );
                    break;
                case 'order_items_refund_subtotal':
                    $data['refund_subtotal'] = (float) $refund_data['total'] - (float) $refund_data['cart_tax'] - ( (float) $refund_data['shipping_total'] + (float) $refund_data['shipping_tax'] );
                    break;
                case 'order_items_refund_items_prices_include_tax':
                    $data['refund_items_prices_include_tax'] = $refund_data['prices_include_tax'];
                    break;
                case 'order_items_refund_items_refund_amount':
                    $data['refund_items_refund_amount'] = $refund_data['amount'];
                    break;
                case 'order_items_refund_items_refunded_by':
                    $data['refund_items_refunded_by'] = $refund_data['refunded_by'];
                    break;
                case 'order_items_refund_items_refunded_payment':
                    $data['refund_items_refunded_payment'] = $refund_data['refunded_payment'];
                    break;
                case 'order_items_refund_items_refund_reason':
                    $data['refund_items_refund_reason'] = $refund_data['reason'];
                    break;
            }
        }
        return $data;
    }

    /**
     * Get custom order item data.
     * This is the custom order item data that is set by the user.
     * The custom order item data is stored in the order item meta.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array         $data   The order data array.
     * @param array         $fields The fields array.
     * @param WC_Order_Item $item The order item object.
     * @return array
     */
    private function _get_order_item_custom_data( &$data, $fields, $item ) {
        $custom_order_items = get_option( WOO_CE_PREFIX . '_custom_order_items', '' );
        if ( ! empty( $custom_order_items ) ) {
            $item_meta_data = $this->_get_order_item_meta_data( $item->get_id() );
            foreach ( $custom_order_items as $custom_order_item ) {
                if ( isset( $fields[ 'order_items_' . $custom_order_item ] ) && 'on' === $fields[ 'order_items_' . $custom_order_item ] ) {
                    $data[ $custom_order_item ] = $item_meta_data[ $custom_order_item ] ?? '';
                }
            }
        }
        return $data;
    }

    /**
     * Get selected tax rate fields.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array $fields     The fields array.
     * @param bool  $order_item Whether the fields are for order items.
     * @return array
     */
    private function _get_selected_tax_rate_fields( $fields, $order_item = false ) {
        $prefix   = $order_item ? 'order_items_tax_rate_' : 'purchase_total_tax_rate_';
        $selected = array_filter(
            array_keys( $fields ),
            function ( $key ) use ( $prefix ) {
                return strpos( $key, $prefix ) === 0;
            }
        );
        return $selected;
    }

    /**
     * Get selected order item attribute fields.
     *
     * @since 2.7.3
     * @access private
     *
     * @param array $fields The fields array.
     * @return array
     */
    private function _get_selected_order_item_attribute_fields( $fields ) {
        return array_filter(
            array_keys( $fields ),
            function ( $key ) {
                return strpos( $key, 'order_items_attribute_' ) === 0 || strpos( $key, 'order_items_product_attribute_' ) === 0;
            }
        );
    }

    /**
     * Get the Order or Order Item meta data.
     * Because woocommerce doesn't allow to get the hidden meta data ('_' prefix),
     * we need to use custom SQL queries.
     *
     * @since 2.7.3
     * @access private
     *
     * @param int $order_id The Order ID.
     * @return array
     */
    public function _get_order_meta_data( $order_id ) {
        global $wpdb;

        $meta_data = array();
        $meta_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_key,meta_value FROM {$wpdb->prefix}wc_orders_meta WHERE order_id = %d",
                $order_id
            ),
            ARRAY_A
        );
        return ! empty( $meta_data ) ? array_column( $meta_data, 'meta_value', 'meta_key' ) : array();
    }

    /**
     * Get the Order Item meta data.
     * Because woocommerce doesn't allow to get the hidden meta data ('_' prefix),
     * we need to use custom SQL queries.
     *
     * @since 2.7.3
     * @access private
     *
     * @param int $order_item_id The Order Item ID.
     * @return array
     */
    public function _get_order_item_meta_data( $order_item_id ) {
        global $wpdb;
        $meta_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_key,meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = %d",
                $order_item_id
            ),
            ARRAY_A
        );

        return ! empty( $meta_data ) ? array_column( $meta_data, 'meta_value', 'meta_key' ) : array();
    }

    /**
     * Execute model.
     *
     * @since 2.7.3
     * @access public
     */
    public function run() {
        add_filter( 'wsed_extend_export_dataset_args', array( $this, 'extend_export_dataset_args' ), 10, 3 );
        add_filter( 'wsed_override_export_columns', array( $this, 'export_columns' ), 10, 3 );
        add_action( 'wsed_save_export_fields', array( $this, 'save_export_fields' ), 10, 2 );
    }
}
