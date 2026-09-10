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
use Automattic\WooCommerce\Utilities\OrderUtil;
use WC_Order_Query;
use WC_Tax;
use WC_Product;
use WC_Order_Refund;

defined( 'ABSPATH' ) || exit;

/**
 * Order export type class.
 *
 * @since 2.7.3
 */
class Order extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Allowed "Order items formatting" values.
     *
     * @since 2.8.1
     * @var string[]
     */
    const ORDER_ITEMS_FORMATTING_FORMATS = array( 'combined', 'unique', 'individual' );

    /**
     * Default "Order items formatting" value, matching the admin screen.
     *
     * @since 2.8.1
     * @var string
     */
    const ORDER_ITEMS_FORMATTING_DEFAULT = 'unique';

    /**
     * Billing e-mail matched when the Customer filter is enabled but the submitted
     * value is not a usable address. The domain is reserved by RFC 2606 so no order
     * can carry it, which keeps an enabled filter from widening the export.
     *
     * @since 2.8.1
     * @var string
     */
    const UNMATCHABLE_CUSTOMER_EMAIL = 'no-matching-customer@invalid';

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
                'name'  => 'completed_date',
                'label' => __( 'Order Completed Date', 'woocommerce-exporter' ),
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
                'name'     => 'order_items_global_unique_id',
                'label'    => __( 'Order Items: GTIN, UPC, EAN, or ISBN', 'woocommerce-exporter' ),
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

        // Custom User meta fields.
        $custom_users = get_option( WOO_CE_PREFIX . '_custom_users', '' );
        if ( ! empty( $custom_users ) ) {
            foreach ( $custom_users as $custom_user ) {
                if ( ! empty( $custom_user ) ) {
                    $fields[] = array(
                        'name'  => $custom_user,
                        'label' => Export_Helper::clean_export_label( $custom_user ),
                        'hover' => sprintf( apply_filters( 'wsed_extend_order_fields_custom_user_hover', '%s: %s' ), __( 'Custom User', 'woocommerce-exporter' ), $custom_user ),
                    );
                }
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

        // Order digital products filters. See resolve_digital_filter() for the SPA/legacy key handling.
        $digital_filter = $this->resolve_digital_filter( $form_data );
        if ( $digital_filter['enabled'] ) {
            if ( ! empty( $digital_filter['value'] ) ) {
                update_option( WOO_CE_PREFIX . '_order_digital_products', $digital_filter['value'] );
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
     * Normalise an "Order items formatting" value to one of the allowed formats.
     *
     * Returns $value unchanged when it is one of self::ORDER_ITEMS_FORMATTING_FORMATS;
     * otherwise falls back to the configured `order_items_formatting` option, and
     * finally to self::ORDER_ITEMS_FORMATTING_DEFAULT. This guarantees the return
     * value is always a valid format, so downstream parser dispatch can never build
     * an undefined `_parse_order_item_data_*` method name and fatal the export.
     *
     * @since 2.8.1
     * @access private
     *
     * @param mixed $value The candidate "Order items formatting" value.
     * @return string A value guaranteed to be one of self::ORDER_ITEMS_FORMATTING_FORMATS.
     */
    private static function normalize_order_items_format( $value ) {
        if ( in_array( $value, self::ORDER_ITEMS_FORMATTING_FORMATS, true ) ) {
            return $value;
        }

        $value = woo_ce_get_option( 'order_items_formatting', self::ORDER_ITEMS_FORMATTING_DEFAULT );

        return in_array( $value, self::ORDER_ITEMS_FORMATTING_FORMATS, true )
            ? $value
            : self::ORDER_ITEMS_FORMATTING_DEFAULT;
    }

    /**
     * Determine whether a Quick Export order filter card is enabled.
     *
     * The Vue Quick Export SPA submits each filter's enable toggle as the string '1'
     * (not the classic UI's 'on'), and — for most order filters — under a flat
     * '{filter}_enabled' key rather than inside the nested 'orders_filters[]' array the
     * classic UI used. Accept the SPA's flat key, the nested key, and the classic 'on'
     * value so an enabled filter's value survives regardless of payload shape.
     *
     * @since 2.8.1
     *
     * @param array  $settings       Parsed Quick Export form data.
     * @param array  $orders_filters The nested 'orders_filters' sub-array from $settings.
     * @param string $nested_key     Key within $orders_filters (e.g. 'status', 'date').
     * @param string $spa_enable_key Flat enable key submitted by the SPA (e.g. 'order_filter_status_enabled'), or '' if none.
     * @return bool
     */
    private function is_quick_filter_enabled( $settings, $orders_filters, $nested_key, $spa_enable_key = '' ) {
        if ( '' !== $spa_enable_key && ! empty( $settings[ $spa_enable_key ] ) ) {
            return true;
        }

        return ! empty( $orders_filters[ $nested_key ] );
    }

    /**
     * Apply a Quick Export date range filter to the export dataset args.
     *
     * The Order Created Dates and Order Modified Dates filters are structurally identical —
     * they differ only in the dataset arg key prefix and the SPA field names — so both route
     * through here. The SPA date_range control submits ISO 'Y-m-d' values; the classic UI
     * submitted a named filter ('manual', 'variable' or a preset) with its own companion fields.
     *
     * Both SPA bounds are required: get_date_filter()'s 'manual' mode only builds a range when
     * from AND to are present, so a one-sided range falls through to the classic payload rather
     * than setting a 'manual' filter it cannot satisfy.
     *
     * @since 2.8.1
     *
     * @param array  $args         The export dataset args.
     * @param array  $settings     Parsed Quick Export form data.
     * @param string $arg_prefix   Dataset arg key prefix (e.g. 'order_dates', 'order_modified_dates').
     * @param string $spa_from_key SPA field name holding the range start (e.g. 'orders_filter_date_from').
     * @param string $spa_to_key   SPA field name holding the range end (e.g. 'orders_filter_date_to').
     * @return array The export dataset args.
     */
    private function apply_quick_date_filter( $args, $settings, $arg_prefix, $spa_from_key, $spa_to_key ) {
        $spa_from = $settings[ $spa_from_key ] ?? '';
        $spa_to   = $settings[ $spa_to_key ] ?? '';

        if ( '' !== $spa_from && '' !== $spa_to ) {
            // Native <input type="date"> always emits ISO dates, which WC_DateTime parses natively.
            $args[ $arg_prefix . '_filter' ] = 'manual';
            $args[ $arg_prefix . '_from' ]   = sanitize_text_field( $spa_from );
            $args[ $arg_prefix . '_to' ]     = sanitize_text_field( $spa_to );

            return $args;
        }

        if ( empty( $settings[ $arg_prefix . '_filter' ] ) ) {
            return $args;
        }

        // Classic UI payload.
        $date_filter                     = sanitize_text_field( $settings[ $arg_prefix . '_filter' ] );
        $args[ $arg_prefix . '_filter' ] = $date_filter;

        if ( 'manual' === $date_filter ) {
            $args[ $arg_prefix . '_from' ] = isset( $settings[ $arg_prefix . '_from' ] ) ? Formatting::sanitize_date( $settings[ $arg_prefix . '_from' ] ) : '';
            $args[ $arg_prefix . '_to' ]   = isset( $settings[ $arg_prefix . '_to' ] ) ? Formatting::sanitize_date( $settings[ $arg_prefix . '_to' ] ) : '';
        } elseif ( 'specific' === $date_filter ) {
            // The SPA date picker submits the preset key as 'specific' with ISO (Y-m-d) bounds
            // under the reader-aligned from/to keys; Export_Helper::get_date_filter() parses them.
            $args[ $arg_prefix . '_from' ] = isset( $settings[ $arg_prefix . '_from' ] ) ? sanitize_text_field( $settings[ $arg_prefix . '_from' ] ) : '';
            $args[ $arg_prefix . '_to' ]   = isset( $settings[ $arg_prefix . '_to' ] ) ? sanitize_text_field( $settings[ $arg_prefix . '_to' ] ) : '';
        } elseif ( 'variable' === $date_filter ) {
            $args[ $arg_prefix . '_filter_variable' ]        = isset( $settings[ $arg_prefix . '_filter_variable' ] ) ? absint( $settings[ $arg_prefix . '_filter_variable' ] ) : false;
            $args[ $arg_prefix . '_filter_variable_length' ] = isset( $settings[ $arg_prefix . '_filter_variable_length' ] ) ? sanitize_text_field( $settings[ $arg_prefix . '_filter_variable_length' ] ) : false;
        }

        return $args;
    }

    /**
     * Resolve the Digital Products order filter from a submitted-settings array.
     *
     * Accepts both the Vue admin SPA keys (`order_filter_digital_enabled` toggle +
     * `order_filter_digital` value) and the legacy form keys (`orders_filters[digital_products]`
     * + `order_filter_digital_products`), so Quick Export applies the filter regardless of which
     * UI submitted it. Without the SPA keys the value never reached the export and every order
     * was returned regardless of the selection.
     *
     * @since 2.8.1
     * @access private
     *
     * @param array $src Submitted settings (the saved `$form_data` or the export `$settings`).
     * @return array {
     *     @type bool   $enabled Whether the Digital Products filter is active.
     *     @type string $value   The selected filter value (e.g. 'include_digital', 'exclude_digital'), or '' if none. Sanitized via sanitize_text_field().
     * }
     */
    private function resolve_digital_filter( array $src ): array {
        $orders_filters = $src['orders_filters'] ?? array();
        $enabled        = ! empty( $src['order_filter_digital_enabled'] )
            || ( ! empty( $orders_filters['digital_products'] ) && 'on' === $orders_filters['digital_products'] );
        $value          = sanitize_text_field( $src['order_filter_digital'] ?? $src['order_filter_digital_products'] ?? '' );

        return array(
            'enabled' => $enabled,
            'value'   => $value,
        );
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
        // Fire the legacy `woo_ce_extend_dataset_args` filter so third-party Order
        // filter callbacks registered in `includes/order-extend.php` (Brand, Product
        // Vendor, Delivery Date, Booking Date, Voucher Redeemed, Subscription Order
        // Type, custom order meta) actually run during Quick Export. Every other
        // export class fires this filter; Order was the lone exception, leaving
        // those third-party filters dead in Quick Export. The legacy callback fires
        // first with the bare dataset args; this class's gated modern logic below
        // then overwrites keys it has authoritative handling for (`order_customer`,
        // `order_coupon`, `order_order_type`) when the matching filter checkbox is
        // enabled.
        if ( ! $export->scheduled_export ) {
            $args = apply_filters( 'woo_ce_extend_dataset_args', $args, $export->type, $settings );
        }

        // Order filters (Quick Export).
        //
        // The Vue Quick Export SPA and the classic UI submit filter toggles/values under
        // different key shapes. Each filter below is self-gated via is_quick_filter_enabled()
        // (which accepts the SPA's flat '{filter}_enabled' key, the nested 'orders_filters[X]'
        // key, and the classic 'on' value), then reads its value from the SPA field name with
        // a fallback to the legacy name. This reconciles the frontend/backend contract mismatch
        // that silently discarded every order filter during Quick Export.
        $orders_filters = $settings['orders_filters'] ?? array();

        // Order Created Dates.
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'date' ) ) {
            $args = $this->apply_quick_date_filter( $args, $settings, 'order_dates', 'orders_filter_date_from', 'orders_filter_date_to' );
        }

        // Order Modified Dates.
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'modified_date' ) ) {
            $args = $this->apply_quick_date_filter( $args, $settings, 'order_modified_dates', 'orders_filter_modified_date_from', 'orders_filter_modified_date_to' );
        }

        // Order Status.
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'status' ) ) {
            $status               = $settings['orders_filter_status'] ?? $settings['order_filter_status'] ?? false;
            $args['order_status'] = false !== $status ? array_map( 'sanitize_text_field', (array) $status ) : false;
        }

        // Order Customer. The SPA "Customer" control submits a single email address; the classic
        // UI submitted an array of customer IDs. Match orders by billing email in the email case so
        // both registered-customer and guest orders are found (resolved in get_object_ids()).
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'customer', 'order_filter_customer_enabled' ) ) {
            $customer = $settings['order_filter_customer'] ?? '';
            if ( is_array( $customer ) ) {
                if ( in_array( '', $customer, true ) ) {
                    $args['order_customer'] = 'all';
                } elseif ( ! empty( $customer ) ) {
                    $args['order_customer'] = array_map( 'absint', $customer );
                }
            } elseif ( '' !== trim( (string) $customer ) ) {
                // Always clear order_customer. woo_ce_order_dataset_args() runs first (via the
                // legacy filter above) and sets it unconditionally through absint(), turning an
                // email address into array( 0 ). On PHP 8 the zero-key guard in
                // woo_ce_format_product_filters() no longer fires ( 0 == '' is false there ), so
                // that residue survived into get_object_ids() as customer_id IN (0) and
                // restricted the export to guest orders — a registered customer's email matched
                // nothing at all. An email is never a customer ID, so the residue is always
                // wrong here regardless of whether the address itself is valid.
                $args['order_customer'] = false;

                // An enabled filter must never widen the export. sanitize_email() returns '' for
                // an unusable address, and leaving order_customer_email unset drops the customer
                // filter entirely — with order_customer cleared above, get_object_ids() would then
                // export every order. Fall back to an address no order can carry so the export
                // comes back empty instead.
                $customer_email               = sanitize_email( (string) $customer );
                $args['order_customer_email'] = '' !== $customer_email ? $customer_email : self::UNMATCHABLE_CUSTOMER_EMAIL;
            }
        }

        // Order Billing Country.
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'billing_country', 'order_filter_billing_country_enabled' ) ) {
            if ( isset( $settings['order_filter_billing_country'] ) && is_array( $settings['order_filter_billing_country'] ) ) {
                // An empty selection means "all countries".
                $args['order_billing_country'] = in_array( '', $settings['order_filter_billing_country'], true )
                    ? 'all'
                    : array_map( 'sanitize_text_field', $settings['order_filter_billing_country'] );
            }
        }

        // Order Shipping Country.
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'shipping_country', 'order_filter_shipping_country_enabled' ) ) {
            if ( isset( $settings['order_filter_shipping_country'] ) && is_array( $settings['order_filter_shipping_country'] ) ) {
                $args['order_shipping_country'] = in_array( '', $settings['order_filter_shipping_country'], true )
                    ? 'all'
                    : array_map( 'sanitize_text_field', $settings['order_filter_shipping_country'] );
            }
        }

        // Order User Roles.
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'user_role', 'order_filter_user_role_enabled' ) ) {
            if ( ! empty( $settings['order_filter_user_role'] ) ) {
                $args['order_user_roles'] = array_map( 'sanitize_text_field', (array) $settings['order_filter_user_role'] );
            }
        }

        // Order Coupon. The SPA "Coupon code" control submits a coupon code string; the classic
        // UI's select submitted numeric coupon IDs. Both are resolved to coupon codes because
        // get_object_ids() matches against woocommerce_order_items.order_item_name.
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'coupon', 'order_filter_coupon_enabled' ) ) {
            if ( ! empty( $settings['order_filter_coupon'] ) ) {
                // Always overwrite. woo_ce_extend_order_dataset_args() runs first (via the legacy
                // filter above) and sets this key unconditionally through absint(), which turns a
                // non-numeric code into array( 0 ). Leaving that residue in place produced a
                // lookup on "0" and an empty export. The SPA text control submits a coupon *code*,
                // so resolve it code-first ('code') — a coupon coded with a bare number ("25")
                // must not be shadowed by coupon post 25.
                $args['order_coupon'] = Export_Helper::resolve_coupon_codes( $settings['order_filter_coupon'], true, 'code' );
            }
        }

        // Order Product. The SPA submits a comma-separated list of product IDs as a string.
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'product', 'order_filter_product_enabled' ) ) {
            $product_raw = $settings['order_filter_product'] ?? '';
            $product_ids = array();
            foreach ( is_array( $product_raw ) ? $product_raw : explode( ',', (string) $product_raw ) as $pid ) {
                $pid = absint( trim( (string) $pid ) );
                if ( $pid > 0 ) {
                    $product_ids[] = $pid;
                }
            }
            if ( ! empty( $product_ids ) ) {
                $args['order_product']         = $product_ids;
                $args['order_product_exclude'] = isset( $settings['order_filter_product_exclude'] ) ? absint( $settings['order_filter_product_exclude'] ) : false;
            }
        }

        // Order Product Category.
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'category', 'order_filter_category_enabled' ) ) {
            $args['order_category'] = ! empty( $settings['order_filter_category'] ) ? array_map( 'absint', (array) $settings['order_filter_category'] ) : false;
        }

        // Order Product Tag.
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'tag', 'order_filter_tag_enabled' ) ) {
            $args['order_tag'] = ! empty( $settings['order_filter_tag'] ) ? array_map( 'absint', (array) $settings['order_filter_tag'] ) : false;
        }

        // Order Product Brand. Assign only inside the gate so a payload without the Brand keys
        // leaves whatever the legacy woo_ce_extend_order_dataset_args() callback supplied.
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'brand', 'order_filter_brand_enabled' ) ) {
            $args['order_brand'] = ! empty( $settings['order_filter_brand'] ) ? array_map( 'absint', (array) $settings['order_filter_brand'] ) : false;
        }

        // Order IDs (classic UI only; no SPA control).
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'id' ) ) {
            $args['order_ids'] = ! empty( $settings['order_filter_id'] ) ? Export_Helper::sanitize_multiple_id_input( $settings['order_filter_id'] ) : false;
        }

        // Order Payment Gateway. SPA nests the toggle under orders_filters[payment_method];
        // the classic UI used orders_filters[payment_gateway].
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'payment_method' ) || ! empty( $orders_filters['payment_gateway'] ) ) {
            $payment               = $settings['orders_filter_payment_method'] ?? $settings['order_filter_payment_gateway'] ?? false;
            $args['order_payment'] = ! empty( $payment ) ? array_map( 'sanitize_text_field', (array) $payment ) : false;
        }

        // Order Shipping Method.
        if ( $this->is_quick_filter_enabled( $settings, $orders_filters, 'shipping_method', 'order_filter_shipping_enabled' ) ) {
            $shipping               = $settings['order_filter_shipping'] ?? $settings['order_filter_shipping_method'] ?? false;
            $args['order_shipping'] = ! empty( $shipping ) ? array_map( 'sanitize_text_field', (array) $shipping ) : false;
        }

        // Digital Products. See resolve_digital_filter() for the SPA/legacy key handling.
        $digital_filter = $this->resolve_digital_filter( $settings );
        if ( $digital_filter['enabled'] ) {
            $args['order_items_digital'] = ! empty( $digital_filter['value'] ) ? $digital_filter['value'] : false;
        }

        // Order Export Options.
        // Normalise the "Order items formatting" value to a guaranteed-valid format rather than boolean
        // false when the setting is absent or empty — an empty or invalid value produces the undefined
        // method name "_parse_order_item_data_<value>" downstream and fatals the export.
        // Normalising here means every downstream consumer sees a valid format too.
        $args['order_items']              = self::normalize_order_items_format( ! empty( $settings['order_items'] ) ? sanitize_text_field( $settings['order_items'] ) : '' );
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

        // HPOS stores resolve `field_query`; the legacy posts-table store does not.
        // Detect the active storage mode so the field filters below can fall back to
        // the equivalent WC_Order_Query vars (which map to postmeta) when HPOS is off.
        $hpos_enabled = class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' )
            && OrderUtil::custom_orders_table_usage_is_enabled();

        $args = array(
            'limit'  => -1,
            'return' => 'ids',
            'type'   => 'shop_order',
        );

        if ( ! empty( $export_args['order_order'] ) ) {
            $args['order'] = $export_args['order_order'];
        }

        if ( ! empty( $export_args['order_orderby'] ) ) {
            $args['orderby'] = $export_args['order_orderby'];
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
            $this->add_order_field_filter( $args, $hpos_enabled, 'customer_id', $export_args['order_customer'] );
        }

        // Order Customer Email (the Quick Export "Customer" filter submits an email address).
        // 'billing_email' is a supported wc_get_orders query var on both HPOS and legacy stores,
        // so it narrows to orders placed with that email — registered-customer and guest alike.
        if ( ! empty( $export_args['order_customer_email'] ) ) {
            $args['billing_email'] = $export_args['order_customer_email'];
        }

        // Order Billing Country.
        if ( ! empty( $export_args['order_billing_country'] ) && 'all' !== $export_args['order_billing_country'] ) {
            $this->add_order_field_filter( $args, $hpos_enabled, 'billing_country', $export_args['order_billing_country'] );
        }

        // Order Shipping Country.
        if ( ! empty( $export_args['order_shipping_country'] ) && 'all' !== $export_args['order_shipping_country'] ) {
            $this->add_order_field_filter( $args, $hpos_enabled, 'shipping_country', $export_args['order_shipping_country'] );
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
                $this->add_order_field_filter( $args, $hpos_enabled, 'customer_id', $user_ids );
            } else {
                // Selected role(s) resolved to no users; force zero results.
                // -1 is safe as a sentinel because WP user IDs are always positive,
                // so no order's customer_id can ever equal it.
                $this->add_order_field_filter( $args, $hpos_enabled, 'customer_id', array( -1 ) );
            }
        }

        // Order IDs.
        if ( ! empty( $export_args['order_ids'] ) ) {
            $this->add_order_field_filter( $args, $hpos_enabled, 'id', $export_args['order_ids'] );
        }

        // Order Payment Gateway.
        if ( ! empty( $export_args['order_payment'] ) ) {
            $this->add_order_field_filter( $args, $hpos_enabled, 'payment_method', $export_args['order_payment'] );
        }

        // Order Custom meta.
        // The Quick Export order path builds its query with wc_get_orders() and never
        // applies the legacy `woo_ce_get_orders_args` filter, so the
        // `woo_ce_extend_get_orders_args` callback that normally turns `order_custom_meta`
        // into a meta_query never fires. Translate it directly here so the custom-meta
        // filter actually scopes the export.
        $order_meta = ( isset( $export_args['order_custom_meta'] ) ? $export_args['order_custom_meta'] : false );
        if ( ! empty( $order_meta ) ) {
            $custom_orders = woo_ce_get_option( 'custom_orders', '' );
            if ( ! empty( $custom_orders ) ) {
                if ( ! isset( $args['meta_query'] ) ) {
                    $args['meta_query'] = array();
                }
                foreach ( $custom_orders as $custom_order ) {
                    if ( isset( $order_meta[ esc_attr( $custom_order ) ] ) && ! empty( $order_meta[ esc_attr( $custom_order ) ] ) ) {
                        $args['meta_query'][] = array(
                            'key'   => $custom_order,
                            'value' => is_array( $order_meta[ esc_attr( $custom_order ) ] ) ? $order_meta[ esc_attr( $custom_order ) ] : explode( ',', $order_meta[ esc_attr( $custom_order ) ] ),
                        );
                    }
                }
            }
            unset( $custom_orders, $custom_order );
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
            ! empty( $export_args['order_brand'] ) ||
            ! empty( $export_args['order_shipping'] ) ||
            ! empty( $export_args['order_items_digital'] )
        ) {
            global $wpdb;

            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            // phpcs:disable WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

            // Order Coupons. Matched against the coupon code recorded on the order item rather
            // than WooCommerce Analytics' wc_order_coupon_lookup, which is derived data: when it
            // is stale or was never backfilled the intersect below silently drops orders that do
            // match. woocommerce_order_items is written on every order regardless.
            //
            // The order_id bound is not optional — woocommerce_order_items carries no index on
            // order_item_type or order_item_name, so without it MySQL scans the whole table.
            if ( ! empty( $export_args['order_coupon'] ) && ! empty( $object_ids ) ) {
                list( $placeholders, $order_bounds, $query_args ) = $this->get_bounded_in_clause_args( $export_args['order_coupon'], $object_ids );

                $object_ids = array_intersect(
                    $object_ids,
                    $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT DISTINCT oi.order_id
                                FROM {$wpdb->prefix}woocommerce_order_items AS oi
                                WHERE oi.order_item_type = 'coupon'
                                AND oi.order_item_name IN ({$placeholders})
                                AND oi.order_id IN ({$order_bounds})",
                            $query_args
                        )
                    )
                );
            }

            // Order Products. Resolved from the order items tables for the same reason as the
            // coupon filter above. Exclusion is applied with array_diff() rather than a
            // NOT IN clause: on a table holding one row per product, `product_id NOT IN (...)`
            // selects orders carrying at least one *other* product, so an order containing both
            // an excluded product and any other product survived the filter.
            if ( ! empty( $export_args['order_product'] ) && ! empty( $object_ids ) ) {
                list( $placeholders, $order_bounds, $query_args ) = $this->get_bounded_in_clause_args( $export_args['order_product'], $object_ids );

                $matching_ids = $wpdb->get_col(
                    $wpdb->prepare(
                        "SELECT DISTINCT oi.order_id
                            FROM {$wpdb->prefix}woocommerce_order_items AS oi
                            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim
                                ON oi.order_item_id = oim.order_item_id AND oim.meta_key = '_product_id'
                            WHERE oi.order_item_type = 'line_item'
                            AND oim.meta_value IN ({$placeholders})
                            AND oi.order_id IN ({$order_bounds})",
                        $query_args
                    )
                );

                $object_ids = ! empty( $export_args['order_product_exclude'] )
                    ? array_diff( $object_ids, $matching_ids )
                    : array_intersect( $object_ids, $matching_ids );
            }

            // Order Categories. The order item's _product_id is the parent product for variations,
            // matching how Analytics keyed wc_order_product_lookup.product_id, so the terms read
            // here are the same ones the lookup join resolved.
            //
            // Both the Quick Export dropdown (Quick_Export_Controller::get_term_choices()) and
            // the classic template submit term IDs, so match term_id rather than
            // term_taxonomy_id — the two columns coincide on most stores but are not
            // guaranteed to.
            if ( ! empty( $export_args['order_category'] ) && ! empty( $object_ids ) ) {
                list( $placeholders, $order_bounds, $query_args ) = $this->get_bounded_in_clause_args( $export_args['order_category'], $object_ids );

                $object_ids = array_intersect(
                    $object_ids,
                    $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT DISTINCT oi.order_id
                                FROM {$wpdb->prefix}woocommerce_order_items AS oi
                                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim
                                    ON oi.order_item_id = oim.order_item_id AND oim.meta_key = '_product_id'
                                INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = oim.meta_value
                                INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                                WHERE oi.order_item_type = 'line_item'
                                AND tt.term_id IN ({$placeholders})
                                AND oi.order_id IN ({$order_bounds})",
                            $query_args
                        )
                    )
                );
            }

            // Order Tags. Same resolution as the category filter above, term_id included.
            if ( ! empty( $export_args['order_tag'] ) && ! empty( $object_ids ) ) {
                list( $placeholders, $order_bounds, $query_args ) = $this->get_bounded_in_clause_args( $export_args['order_tag'], $object_ids );

                $object_ids = array_intersect(
                    $object_ids,
                    $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT DISTINCT oi.order_id
                                FROM {$wpdb->prefix}woocommerce_order_items AS oi
                                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim
                                    ON oi.order_item_id = oim.order_item_id AND oim.meta_key = '_product_id'
                                INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = oim.meta_value
                                INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                                WHERE oi.order_item_type = 'line_item'
                                AND tt.term_id IN ({$placeholders})
                                AND oi.order_id IN ({$order_bounds})",
                            $query_args
                        )
                    )
                );
            }

            // Order Product Brands. The `order_brand` arg was previously built by
            // woo_ce_extend_order_dataset_args() but never consumed here, so the Brand selection
            // was silently discarded and every order was exported. Brand term IDs come from
            // whichever brand taxonomy the filter dropdown offered (product_brand, pwb-brand,
            // pa_brand, yith_product_brand), so match on term_id and let the taxonomy follow the
            // submitted IDs rather than pinning it to one.
            //
            // Products are resolved from the order items tables for the same reason as the
            // category filter above.
            if ( ! empty( $export_args['order_brand'] ) && ! empty( $object_ids ) ) {
                list( $placeholders, $order_bounds, $query_args ) = $this->get_bounded_in_clause_args( $export_args['order_brand'], $object_ids );

                $object_ids = array_intersect(
                    $object_ids,
                    $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT DISTINCT oi.order_id
                                FROM {$wpdb->prefix}woocommerce_order_items AS oi
                                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim
                                    ON oi.order_item_id = oim.order_item_id AND oim.meta_key = '_product_id'
                                INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = oim.meta_value
                                INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                                WHERE oi.order_item_type = 'line_item'
                                AND tt.term_id IN ({$placeholders})
                                AND oi.order_id IN ({$order_bounds})",
                            $query_args
                        )
                    )
                );
            }

            // Order Shipping Methods.
            if ( ! empty( $export_args['order_shipping'] ) ) {
                $placeholders = $this->get_in_clause_placeholders( $export_args['order_shipping'] );
                $object_ids   = array_intersect(
                    $object_ids,
                    $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT DISTINCT oi.order_id
                                FROM {$wpdb->prefix}woocommerce_order_items as oi
                                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta as oim ON oi.order_item_id = oim.order_item_id
                                WHERE oi.order_item_type = 'shipping'
                                    AND oim.meta_key = 'method_id'
                                    AND oim.meta_value IN ({$placeholders})",
                            $export_args['order_shipping']
                        )
                    )
                );
            }

            // phpcs:enable WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
            // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

            // Order Digital Products.
            if ( ! empty( $export_args['order_items_digital'] ) && ! empty( $object_ids ) ) {
                $orders_clause_placeholder = '(' . implode( ',', array_fill( 0, count( $object_ids ), '%s' ) ) . ')';

                // Derive the downloadable flag from the order items tables
                // ({$wpdb->prefix}woocommerce_order_items / _order_itemmeta), which always
                // exist for WooCommerce regardless of table prefix, HPOS, or whether the
                // WooCommerce Analytics lookup table (wc_order_product_lookup) is provisioned.
                // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- %s placeholders are inside $orders_clause_placeholder, invisible to static analysis.
                $orders_downloadable = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT oi.order_id,
                            GROUP_CONCAT(DISTINCT pm.meta_value ORDER BY pm.meta_key DESC SEPARATOR '|') AS downloadable
                            FROM {$wpdb->prefix}woocommerce_order_items AS oi
                            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim
                                ON oi.order_item_id = oim.order_item_id AND oim.meta_key = '_product_id'
                            INNER JOIN {$wpdb->postmeta} AS pm
                                ON pm.post_id = oim.meta_value AND pm.meta_key = '_downloadable'
                            WHERE oi.order_item_type = 'line_item'
                            AND oi.order_id IN {$orders_clause_placeholder}
                            GROUP BY oi.order_id",
                        $object_ids
                    )
                );
                // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

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
     * Add an order field filter that works under both HPOS and legacy order storage.
     *
     * HPOS-enabled stores resolve `field_query`, but the legacy posts-table store
     * (`WC_Order_Data_Store_CPT`) ignores it. For legacy storage we instead pass the
     * equivalent `WC_Order_Query` var as an array, which WooCommerce maps to a
     * `_`-prefixed postmeta `IN` query via the order data store's internal meta keys.
     * The `id` field has no meta equivalent, so it falls back to WP_Query `post__in`.
     *
     * @since 2.7.10
     *
     * @param array        $args         The Order Query args (passed by reference).
     * @param bool         $hpos_enabled Whether HPOS custom order tables are in use.
     * @param string       $field        The HPOS field name (e.g. 'payment_method', 'customer_id').
     * @param array|string $values       The values to match against. Scalars are normalised to an
     *                                   array, so this is left untyped by design.
     * @return void
     */
    private function add_order_field_filter( array &$args, bool $hpos_enabled, string $field, $values ): void {
        $values = array_values( (array) $values );

        if ( $hpos_enabled ) {
            $args['field_query'][] = array(
                array(
                    'field'   => $field,
                    'value'   => implode( ',', $values ),
                    'compare' => 'IN',
                ),
            );
            return;
        }

        // Legacy posts-table store: 'id' has no postmeta equivalent, so scope by post__in.
        if ( 'id' === $field ) {
            $this->intersect_post__in( $args, array_map( 'absint', $values ) );
            return;
        }

        // Other fields map to a postmeta IN query via the order data store's internal meta keys.
        // When a second filter targets the same field (e.g. the Order Customers and Order User
        // Roles filters both constrain customer_id), the HPOS path AND-combines them via separate
        // field_query entries. Mirror that here by intersecting the value sets rather than letting
        // the second call overwrite the first. -1 can never equal a real customer ID, country code
        // or payment method, so an empty intersection resolves to no matching orders.
        if ( isset( $args[ $field ] ) ) {
            $intersection   = array_values( array_intersect( (array) $args[ $field ], $values ) );
            $args[ $field ] = ! empty( $intersection ) ? $intersection : array( -1 );
            return;
        }

        $args[ $field ] = $values;
    }

    /**
     * Constrain a query's `post__in` to the given IDs, intersecting with any existing constraint.
     *
     * When `post__in` is already set, the two constraints are AND-ed via intersection so one
     * filter can't overwrite another. An empty intersection falls back to `array( 0 )` because
     * WP_Query silently ignores `post__in => array()` and would otherwise return all posts.
     *
     * @since 2.7.10
     *
     * @param array $args The Order Query args (passed by reference).
     * @param array $ids  The order IDs to constrain to.
     * @return void
     */
    private function intersect_post__in( array &$args, array $ids ): void {
        if ( empty( $args['post__in'] ) ) {
            $args['post__in'] = $ids;
        } else {
            $intersection     = array_intersect( $args['post__in'], $ids );
            $args['post__in'] = ! empty( $intersection ) ? $intersection : array( 0 );
        }
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
            // Skip refund processing for OrderRefund objects (they don't have sub-refunds).
            if ( $order instanceof WC_Order_Refund || is_a( $order, 'Automattic\WooCommerce\Admin\Overrides\OrderRefund' ) ) {
                $refunds = array();
            } else {
                $refunds = $order->get_refunds();
            }
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
     * Get IN clause placeholders for prepared SQL queries.
     *
     * @since 2.7.8
     * @access private
     *
     * @param array  $items  The items to create placeholders for.
     * @param string $format The placeholder format. Default '%s'.
     * @return string
     */
    private function get_in_clause_placeholders( $items, $format = '%s' ) {
        return implode( ',', array_fill( 0, count( $items ), $format ) );
    }

    /**
     * Build the placeholders and bound arguments for an order-item lookup that is
     * scoped to an already-resolved set of order IDs.
     *
     * The order_id bound keeps these queries off a full scan of
     * {$wpdb->prefix}woocommerce_order_items, which carries no index on
     * order_item_type or order_item_name. The returned argument list is ordered to
     * match: filter values first, order IDs second.
     *
     * @since 2.8.1
     *
     * @param array|string $filter_values The filter values to match against.
     * @param array        $object_ids    The order IDs to bound the query to.
     * @return array Numeric array of [ filter placeholders, order ID placeholders, bound arguments ].
     */
    private function get_bounded_in_clause_args( $filter_values, $object_ids ) {
        return array(
            $this->get_in_clause_placeholders( (array) $filter_values ),
            $this->get_in_clause_placeholders( $object_ids ),
            array_merge( array_values( (array) $filter_values ), array_values( $object_ids ) ),
        );
    }

    /**
     * Process a single order item into a dataset row.
     *
     * @since 2.7.3
     * @access private
     *
     * @param WC_Order_Item $item   The order item object.
     * @param array         $fields The fields to include in the export.
     * @param WC_Order      $order  The parent order object.
     * @param object        $export The export object.
     * @return array The processed order item data.
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

        // Dynamically call a parser function based on the order items export format. Normalise to a
        // guaranteed-valid format (defence-in-depth for legacy paths that populate $export->args
        // without going through extend_export_dataset_args()) so the export never dispatches to an
        // undefined parser method.
        $order_items_format = self::normalize_order_items_format( $export->args['order_items'] ?? '' );

        $parse_order_item_data_func = '_parse_order_item_data_' . $order_items_format;
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
                    $data[ $key ] = isset( $order_data['currency'] ) ? $order_data['currency'] : '';
                    break;
                case 'payment_gateway_id':
                    $data[ $key ] = isset( $order_data['payment_method'] ) ? $order_data['payment_method'] : '';
                    break;
                case 'payment_gateway':
                    $data[ $key ] = isset( $order_data['payment_method_title'] ) ? $order_data['payment_method_title'] : '';
                    break;
                case 'order_key':
                    $data[ $key ] = isset( $order_data['order_key'] ) ? $order_data['order_key'] : '';
                    break;
                case 'transaction_id':
                    $data[ $key ] = isset( $order_data['transaction_id'] ) ? $order_data['transaction_id'] : '';
                    break;
                case 'created_via':
                    $data[ $key ] = isset( $order_data['created_via'] ) ? $order_data['created_via'] : '';
                    break;
                case 'cart_hash':
                    $data[ $key ] = isset( $order_data['cart_hash'] ) ? $order_data['cart_hash'] : '';
                    break;
                case 'purchase_date':
                    $data[ $key ] = Formatting::format_date( $order_data['date_created'] );
                    break;
                case 'modified_date':
                    $data[ $key ] = Formatting::format_date( $order_data['date_modified'] );
                    break;
                case 'completed_date':
                    $data[ $key ] = Formatting::format_date( $order->get_date_completed() );
                    break;
                case 'purchase_time':
                    $data[ $key ] = Formatting::format_date( $order_data['date_created'], get_option( 'time_format' ) );
                    break;
                case 'ip_address':
                    $data[ $key ] = isset( $order_data['customer_ip_address'] ) ? $order_data['customer_ip_address'] : '';
                    break;
                case 'browser_agent':
                    $data[ $key ] = isset( $order_data['customer_user_agent'] ) ? $order_data['customer_user_agent'] : '';
                    break;
                case 'total_quantity':
                    $data[ $key ] = $order->get_item_count();
                    break;
                case 'total_order_items':
                    $total_items  = 0;
                    $total_items += count( $order->get_items( array( 'line_item', 'fee', 'tax', 'shipping', 'coupon' ) ) );
                    $data[ $key ] = $total_items;
                    break;
                case 'has_downloads':
                    $data[ $key ] = $order->has_downloadable_item() ? 'Yes' : 'No';
                    break;
                case 'has_downloaded':
                    $data[ $key ] = $order->is_download_permitted() ? 'Yes' : 'No';
                    break;
                case 'customer_message':
                    $data[ $key ] = isset( $order_data['customer_note'] ) ? $order_data['customer_note'] : '';
                    break;

                // Shipping fields.
                case 'billing_first_name':
                    $data[ $key ] = isset( $order_data['billing']['first_name'] ) ? $order_data['billing']['first_name'] : '';
                    break;
                case 'billing_last_name':
                    $data[ $key ] = isset( $order_data['billing']['last_name'] ) ? $order_data['billing']['last_name'] : '';
                    break;
                case 'billing_full_name':
                    $first_name   = isset( $order_data['billing']['first_name'] ) ? $order_data['billing']['first_name'] : '';
                    $last_name    = isset( $order_data['billing']['last_name'] ) ? $order_data['billing']['last_name'] : '';
                    $data[ $key ] = trim( $first_name . ' ' . $last_name );
                    break;
                case 'billing_company':
                    $data[ $key ] = isset( $order_data['billing']['company'] ) ? $order_data['billing']['company'] : '';
                    break;
                case 'billing_address':
                    $address_1    = isset( $order_data['billing']['address_1'] ) ? $order_data['billing']['address_1'] : '';
                    $address_2    = isset( $order_data['billing']['address_2'] ) ? $order_data['billing']['address_2'] : '';
                    $data[ $key ] = trim( $address_1 . ' ' . $address_2 );
                    break;
                case 'billing_address_1':
                    $data[ $key ] = isset( $order_data['billing']['address_1'] ) ? $order_data['billing']['address_1'] : '';
                    break;
                case 'billing_address_2':
                    $data[ $key ] = isset( $order_data['billing']['address_2'] ) ? $order_data['billing']['address_2'] : '';
                    break;
                case 'billing_city':
                    $data[ $key ] = isset( $order_data['billing']['city'] ) ? $order_data['billing']['city'] : '';
                    break;
                case 'billing_postcode':
                    $data[ $key ] = isset( $order_data['billing']['postcode'] ) ? $order_data['billing']['postcode'] : '';
                    break;
                case 'billing_state':
                    $data[ $key ] = isset( $order_data['billing']['state'] ) ? $order_data['billing']['state'] : '';
                    break;
                case 'billing_country':
                    $data[ $key ] = isset( $order_data['billing']['country'] ) ? $order_data['billing']['country'] : '';
                    break;
                case 'billing_email':
                    $data[ $key ] = isset( $order_data['billing']['email'] ) ? $order_data['billing']['email'] : '';
                    break;
                case 'billing_phone':
                    $data[ $key ] = isset( $order_data['billing']['phone'] ) ? $order_data['billing']['phone'] : '';
                    break;
                case 'billing_state_full':
                    $country      = isset( $order_data['billing']['country'] ) ? $order_data['billing']['country'] : '';
                    $state        = isset( $order_data['billing']['state'] ) ? $order_data['billing']['state'] : '';
                    $data[ $key ] = $country && $state ? Formatting::state_name( $country, $state ) : '';
                    break;
                case 'billing_country_full':
                    $country      = isset( $order_data['billing']['country'] ) ? $order_data['billing']['country'] : '';
                    $data[ $key ] = $country ? Formatting::country_name( $country ) : '';
                    break;

                // Shipping fields.
                case 'shipping_first_name':
                    $data[ $key ] = isset( $order_data['shipping']['first_name'] ) ? $order_data['shipping']['first_name'] : '';
                    break;
                case 'shipping_last_name':
                    $data[ $key ] = isset( $order_data['shipping']['last_name'] ) ? $order_data['shipping']['last_name'] : '';
                    break;
                case 'shipping_full_name':
                    $first_name   = isset( $order_data['shipping']['first_name'] ) ? $order_data['shipping']['first_name'] : '';
                    $last_name    = isset( $order_data['shipping']['last_name'] ) ? $order_data['shipping']['last_name'] : '';
                    $data[ $key ] = trim( $first_name . ' ' . $last_name );
                    break;
                case 'shipping_company':
                    $data[ $key ] = isset( $order_data['shipping']['company'] ) ? $order_data['shipping']['company'] : '';
                    break;
                case 'shipping_address':
                    $address_1    = isset( $order_data['shipping']['address_1'] ) ? $order_data['shipping']['address_1'] : '';
                    $address_2    = isset( $order_data['shipping']['address_2'] ) ? $order_data['shipping']['address_2'] : '';
                    $data[ $key ] = trim( $address_1 . ' ' . $address_2 );
                    break;
                case 'shipping_address_1':
                    $data[ $key ] = isset( $order_data['shipping']['address_1'] ) ? $order_data['shipping']['address_1'] : '';
                    break;
                case 'shipping_address_2':
                    $data[ $key ] = isset( $order_data['shipping']['address_2'] ) ? $order_data['shipping']['address_2'] : '';
                    break;
                case 'shipping_city':
                    $data[ $key ] = isset( $order_data['shipping']['city'] ) ? $order_data['shipping']['city'] : '';
                    break;
                case 'shipping_postcode':
                    $data[ $key ] = isset( $order_data['shipping']['postcode'] ) ? $order_data['shipping']['postcode'] : '';
                    break;
                case 'shipping_state':
                    $data[ $key ] = isset( $order_data['shipping']['state'] ) ? $order_data['shipping']['state'] : '';
                    break;
                case 'shipping_country':
                    $data[ $key ] = isset( $order_data['shipping']['country'] ) ? $order_data['shipping']['country'] : '';
                    break;
                case 'shipping_phone':
                    $data[ $key ] = isset( $order_data['shipping']['phone'] ) ? $order_data['shipping']['phone'] : '';
                    break;
                case 'shipping_state_full':
                    $country      = isset( $order_data['shipping']['country'] ) ? $order_data['shipping']['country'] : '';
                    $state        = isset( $order_data['shipping']['state'] ) ? $order_data['shipping']['state'] : '';
                    $data[ $key ] = $country && $state ? Formatting::state_name( $country, $state ) : '';
                    break;
                case 'shipping_country_full':
                    $country      = isset( $order_data['shipping']['country'] ) ? $order_data['shipping']['country'] : '';
                    $data[ $key ] = $country ? Formatting::country_name( $country ) : '';
                    break;
                case 'shipping_cost':
                    $data[ $key ] = isset( $order_data['shipping_total'] ) ? Formatting::format_price( $order_data['shipping_total'] ) : '';
                    break;
                case 'order_shipping_tax':
                    $data[ $key ] = isset( $order_data['shipping_tax'] ) ? Formatting::format_price( $order_data['shipping_tax'] ) : '';
                    break;
                case 'shipping_excl_tax':
                    $data[ $key ] = isset( $order_data['shipping_total'] ) ? Formatting::format_price( $order_data['shipping_total'] ) : '';
                    break;
                case 'shipping_incl_tax':
                    $shipping_total = isset( $order_data['shipping_total'] ) ? (float) $order_data['shipping_total'] : 0;
                    $shipping_tax   = isset( $order_data['shipping_tax'] ) ? (float) $order_data['shipping_tax'] : 0;
                    $data[ $key ]   = Formatting::format_price( $shipping_total + $shipping_tax );
                    break;

                // User fields.
                case 'user_id':
                    $data[ $key ] = isset( $order_data['customer_id'] ) ? $order_data['customer_id'] : '';
                    break;

                // Prices.
                case 'purchase_total':
                    /**
                     * Order total deducted with refund amount.
                     * Why not `get_total()`? Because `get_total()` includes the refunded amount.
                     * This function is used to get the total amount of the order, minus the refunded amount.
                     * For refund objects, we use get_amount() instead since get_remaining_refund_amount() doesn't exist.
                     */
                    if ( $order instanceof WC_Order_Refund || is_a( $order, 'Automattic\WooCommerce\Admin\Overrides\OrderRefund' ) ) {
                        // For refunds, use the refund amount (negative value).
                        $data[ $key ] = Formatting::format_price( $order->get_amount() );
                    } else {
                        // For regular orders, use remaining amount after refunds.
                        $data[ $key ] = Formatting::format_price( $order->get_remaining_refund_amount() );
                    }
                    break;
                case 'purchase_subtotal':
                    /**
                     * The order subtotal is the total of the items in the order, before tax and shipping.
                     * It is the sum of the line items, minus the sum of the line item discounts.
                     * $total_refunded_subtotal stands for the refund subtotal amount, without shipping and refund tax.
                     */
                    if ( $order instanceof WC_Order_Refund || is_a( $order, 'Automattic\WooCommerce\Admin\Overrides\OrderRefund' ) ) {
                        // For refunds, use the refund amount (negative value).
                        $data['purchase_subtotal'] = Formatting::format_price( $order->get_amount() );
                    } else {
                        $total_refunded_subtotal   = (float) $order->get_total_refunded() - ( (float) $order->get_total_shipping_refunded() + (float) $order->get_total_tax_refunded() );
                        $subtotal                  = (float) $order->get_subtotal();
                        $discount_total            = isset( $order_data['discount_total'] ) ? (float) $order_data['discount_total'] : 0;
                        $data['purchase_subtotal'] = Formatting::format_price( $subtotal - $discount_total - (float) $total_refunded_subtotal );
                    }
                    break;
                case 'purchase_total_tax':
                    if ( $order instanceof WC_Order_Refund || is_a( $order, 'Automattic\WooCommerce\Admin\Overrides\OrderRefund' ) ) {
                        // For refunds, use the refund tax amount.
                        $data['purchase_total_tax'] = Formatting::format_price( $order->get_total_tax() );
                    } else {
                        $total_tax                  = isset( $order_data['total_tax'] ) ? (float) $order_data['total_tax'] : 0;
                        $tax_refunded               = (float) $order->get_total_tax_refunded();
                        $data['purchase_total_tax'] = Formatting::format_price( $total_tax - $tax_refunded );
                    }
                    break;
                case 'order_sales_tax':
                    $data['order_sales_tax'] = isset( $order_data['cart_tax'] ) ? Formatting::format_price( $order_data['cart_tax'] ) : '';
                    break;
                case 'order_subtotal_excl_tax':
                    if ( $order instanceof WC_Order_Refund || is_a( $order, 'Automattic\WooCommerce\Admin\Overrides\OrderRefund' ) ) {
                        // For refunds, get the refund amount excluding tax.
                        $refund_amount                   = (float) $order->get_amount();
                        $refund_tax                      = (float) $order->get_total_tax();
                        $data['order_subtotal_excl_tax'] = Formatting::format_price( $refund_amount - $refund_tax );
                    } else {
                        // For regular orders, use remaining amount after refunds minus taxes.
                        $remaining_refund_amount         = (float) $order->get_remaining_refund_amount();
                        $total_tax                       = isset( $order_data['total_tax'] ) ? (float) $order_data['total_tax'] : 0;
                        $tax_refunded                    = (float) $order->get_total_tax_refunded();
                        $data['order_subtotal_excl_tax'] = Formatting::format_price( $remaining_refund_amount - $total_tax - $tax_refunded );
                    }
                    break;
                case 'order_discount':
                    $data['order_discount'] = isset( $order_data['discount_total'] ) ? Formatting::format_price( $order_data['discount_total'] ) : '';
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

        // Custom User meta fields.
        // Guarded against overwriting standard order columns (e.g. billing_email) when an admin
        // registers a user meta key that collides with a built-in field name.
        $custom_users = get_option( WOO_CE_PREFIX . '_custom_users', '' );
        if ( ! empty( $custom_users ) && 0 !== $order->get_customer_id() ) {
            foreach ( $custom_users as $custom_user ) {
                if ( ! empty( $custom_user ) && isset( $fields[ $custom_user ] ) && 'on' === $fields[ $custom_user ] && ! isset( $data[ $custom_user ] ) ) {
                    $data[ $custom_user ] = maybe_serialize( get_user_meta( $order->get_user_id(), $custom_user, true ) );
                }
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
            // Skip refund processing for OrderRefund objects (they don't have sub-refunds).
            if ( $order instanceof WC_Order_Refund || is_a( $order, 'Automattic\WooCommerce\Admin\Overrides\OrderRefund' ) ) {
                // For refund objects, use their own methods.
                if ( isset( $fields['refund_total'] ) ) {
                    $data['refund_total'] = $order->get_amount();
                }
                if ( isset( $fields['refund_tax'] ) ) {
                    $data['refund_tax'] = $order->get_total_tax();
                }
            } else {
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
                    // Check if this is a WC_Order internal meta field.
                    $meta_value            = $order->get_meta( $custom_order, true );
                    $data[ $custom_order ] = maybe_serialize( $meta_value );
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
                    case 'order_items_global_unique_id':
                        $data['global_unique_id'] = $product_data['global_unique_id'];
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
                $tax_percentage = 0;
                // Check if the subtotal is greater than 0.
                if ( $item_data['subtotal'] > 0 ) {
                    $tax_percentage = round( (float) $item_data['subtotal_tax'] / (float) $item_data['subtotal'] * 100, 2 );
                }

                $data['tax_percentage'] = $tax_percentage . '%';
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
            $data['variation'] = isset( $product_data['attribute_summary'] ) ? $product_data['attribute_summary'] : '';
        }

        if ( isset( $fields['order_items_variation_id'] ) ) {
            $data['variation_id'] = isset( $item_data['variation_id'] ) ? $item_data['variation_id'] : '';
        }

        $missing_attributes_with_key_value = array(); // find and store the deleted attributes of variable product placed in an order.
        $order_items_meta_data             = $item->get_meta_data() ?? array(); // focus on variation attributes only(do not consider other product attributes apart from variation attributes).

        foreach ( $order_items_meta_data as $meta_data ) { // if somehow variation attributes are deleted from the product, but saved in order items(products). re-store the product attributes from the order items to the exported data.
            if ( ! isset( $product_data['attributes'][ $meta_data->key ] ) ) {
                $missing_attributes_with_key_value[ $meta_data->key ] = $meta_data->value;
            }
        }

        foreach ( $missing_attributes_with_key_value as $attribute_key => $attribute_value ) { // now after getting the missing attributes from the line order items(products), append it to the 'attribute_summary' key.
            $attribute_key                      = false === strpos( $attribute_key, 'pa_' ) ? 'pa_' . $attribute_key : $attribute_key;
            $attribute_label                    = wc_attribute_label( $attribute_key ) ?? '';
            $term                               = get_term_by( 'slug', $attribute_value, $attribute_key );
            $is_the_term_value_empty            = ( $term && ! is_wp_error( $term ) ) ? false : true;
            $attribute_value                    = ! $is_the_term_value_empty ? $term->name : $attribute_value;
            $attribute_label                    = $is_the_term_value_empty ? str_replace( 'pa_', '', $attribute_label ) : $attribute_label;
            $product_data['attribute_summary'] .= empty( $product_data['attribute_summary'] ) ? "$attribute_label: $attribute_value" : ", $attribute_label: $attribute_value";
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
