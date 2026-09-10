<?php
/**
 * Export Field Registry.
 *
 * Centralised catalogue of field-to-category assignments and predefined
 * field selections for every supported export type.
 *
 * @since   2.8.0
 * @package VisserLabs\WSE\Classes\Export
 */

namespace VisserLabs\WSE\Classes\Export;

defined( 'ABSPATH' ) || exit;

/**
 * Field_Registry
 *
 * Provides authoritative category assignments for export fields so they can
 * be grouped meaningfully in the Add Export Fields picker and elsewhere in
 * the plugin. Categories and predefined selections are filterable via
 * WordPress hooks so third-party extensions can add their own fields.
 *
 * Usage:
 *   $category  = Field_Registry::get_category( 'product', 'sale_price' );
 *   $defaults  = Field_Registry::get_predefined( 'order' );
 *
 * @since 2.8.0
 */
class Field_Registry {

    /**
     * Category to field-name mappings for each export type.
     *
     * Format: [ type => [ category_label => [ field_names ] ] ]
     * The inverted (field_name => category) map is built lazily and cached
     * in {@see $inverted_cache}.
     *
     * @since 2.8.0
     * @var array<string, array<string, string[]>>
     */
    private static $categories = array(

        // Products.
        'product'        => array(
            'Basic Info'            => array(
                'product_id',
                'parent_id',
                'parent_sku',
                'sku',
                'global_unique_id',
                'name',
                'post_title',
                'slug',
                'permalink',
                'product_url',
                'type',
                'status',
                'product_status',
                'visibility',
                'featured',
                'virtual',
                'downloadable',
                'sold_individually',
                'allow_backorders',
                'enable_reviews',
                'button_text',
                'external_url',
                'menu_order',
                'post_author',
                'purchase_note',
                'description',
                'excerpt',
                'user_name',
                'user_role',
            ),
            'Pricing'               => array(
                'price',
                'sale_price',
                'net_price',
                'sale_price_dates_from',
                'sale_price_dates_to',
                'tax_status',
                'tax_class',
            ),
            'Inventory'             => array(
                'manage_stock',
                'quantity',
                'stock_status',
                'total_sales',
                'average_rating',
                'rating_count',
                'review_count',
                'grouped_products',
            ),
            'Shipping & Dimensions' => array(
                'weight',
                'weight_unit',
                'height',
                'height_unit',
                'width',
                'width_unit',
                'length',
                'length_unit',
                'shipping_class',
            ),
            'Images & Media'        => array(
                'image',
                'image_thumbnail',
                'image_embed',
                'image_title',
                'image_caption',
                'image_alt',
                'image_description',
                'product_gallery',
                'product_gallery_thumbnail',
                'product_gallery_embed',
                'product_gallery_title',
                'product_gallery_caption',
                'product_gallery_alt',
                'product_gallery_description',
            ),
            'Categories & Tags'     => array(
                'category',
                'category_level_1',
                'category_level_2',
                'category_level_3',
                'tag',
            ),
            'Downloads'             => array(
                'download_file_name',
                'download_file_path',
                'download_limit',
                'download_expiry',
                'download_type',
            ),
            'Related Products'      => array(
                'crosssell_ids',
                'upsell_ids',
            ),
            'Dates'                 => array(
                'post_date',
                'post_modified',
            ),
        ),

        // Orders.
        'order'          => array(
            'Order Info'        => array(
                'purchase_id',
                'post_id',
                'order_key',
                'transaction_id',
                'created_via',
                'cart_hash',
                'payment_status',
                'post_status',
                'ip_address',
                'browser_agent',
            ),
            'Financial'         => array(
                'purchase_total',
                'purchase_subtotal',
                'order_currency',
                'order_discount',
                'purchase_total_tax',
                'order_subtotal_excl_tax',
                'order_sales_tax',
                'order_shipping_tax',
                'shipping_incl_tax',
                'shipping_excl_tax',
                'refund_total',
                'order_tax_percentage',
                'total_quantity',
                'total_order_items',
            ),
            'Payment'           => array(
                'payment_gateway_id',
                'payment_gateway',
                'paypal_payer_paypal_address',
                'paypal_payer_first_name',
                'paypal_payer_last_name',
                'paypal_payment_type',
                'paypal_payment_status',
            ),
            'Shipping Details'  => array(
                'shipping_method_id',
                'shipping_method',
                'shipping_instance_id',
                'shipping_cost',
                'shipping_weight_total',
            ),
            'Billing Address'   => array(
                'billing_first_name',
                'billing_last_name',
                'billing_full_name',
                'billing_company',
                'billing_address',
                'billing_address_1',
                'billing_address_2',
                'billing_city',
                'billing_state',
                'billing_state_full',
                'billing_postcode',
                'billing_country',
                'billing_country_full',
                'billing_email',
                'billing_phone',
            ),
            'Shipping Address'  => array(
                'shipping_first_name',
                'shipping_last_name',
                'shipping_full_name',
                'shipping_company',
                'shipping_address',
                'shipping_address_1',
                'shipping_address_2',
                'shipping_city',
                'shipping_state',
                'shipping_state_full',
                'shipping_postcode',
                'shipping_country',
                'shipping_country_full',
            ),
            'Customer'          => array(
                'user_id',
                'user_name',
                'user_role',
                'customer_message',
                'customer_notes',
            ),
            'Coupons'           => array(
                'coupon_code',
                'coupon_expiry_date',
                'coupon_description',
            ),
            'Order Items'       => array(
                'order_items_id',
                'order_items_product_id',
                'order_items_name',
                'order_items_sku',
                'order_items_quantity',
                'order_items_subtotal',
                'order_items_total',
                'order_items_tax',
                'order_items_discount',
                'order_items_type',
                'order_items_type_id',
                'order_items_variation',
                'order_items_variation_id',
                'order_items_category',
                'order_items_tag',
                'order_items_description',
                'order_items_excerpt',
                'order_items_global_unique_id',
                'order_items_image_embed',
                'order_items_stock',
                'order_items_rrp',
                'order_items_total_weight',
                'order_items_weight',
                'order_items_width',
                'order_items_height',
                'order_items_length',
                'order_items_shipping_class',
                'order_items_tax_class',
                'order_items_tax_percentage',
                'order_items_tax_subtotal',
                'order_items_total_sales',
                'order_items_refund_quantity',
                'order_items_refund_subtotal',
                'order_items_refund_subtotal_incl_tax',
                'order_items_refund_items_prices_include_tax',
                'order_items_refund_items_refund_amount',
                'order_items_refund_items_refund_reason',
                'order_items_refund_items_refunded_by',
                'order_items_refund_items_refunded_payment',
                'order_items_modified_date',
                'order_items_publish_date',
            ),
            'Notes & Downloads' => array(
                'order_notes',
                'has_downloads',
                'has_downloaded',
            ),
            'Dates'             => array(
                'purchase_date',
                'purchase_time',
                'modified_date',
                'refund_date',
            ),
        ),

        // Customers.
        'customer'       => array(
            'Account'          => array( 'user_id', 'user_name', 'user_role' ),
            'Billing Address'  => array(
                'billing_full_name',
                'billing_first_name',
                'billing_last_name',
                'billing_company',
                'billing_address',
                'billing_address_1',
                'billing_address_2',
                'billing_city',
                'billing_postcode',
                'billing_state',
                'billing_state_full',
                'billing_country',
                'billing_phone',
                'billing_email',
            ),
            'Shipping Address' => array(
                'shipping_full_name',
                'shipping_first_name',
                'shipping_last_name',
                'shipping_company',
                'shipping_address',
                'shipping_address_1',
                'shipping_address_2',
                'shipping_city',
                'shipping_postcode',
                'shipping_state',
                'shipping_state_full',
                'shipping_country',
                'shipping_country_full',
            ),
            'Order History'    => array( 'total_spent', 'completed_orders', 'total_orders' ),
        ),

        // Users.
        'user'           => array(
            'Account'       => array( 'user_id', 'user_name', 'user_role', 'email', 'password' ),
            'Profile'       => array(
                'first_name',
                'last_name',
                'full_name',
                'nick_name',
                'url',
                'description',
                'aim',
                'yim',
                'jabber',
            ),
            'Order History' => array( 'orders', 'money_spent' ),
            'Dates'         => array( 'date_registered', 'last_update' ),
        ),

        // Coupons.
        'coupon'         => array(
            'Basic Info'            => array(
                'coupon_code',
                'discount_type',
                'coupon_amount',
                'coupon_description',
                'individual_use',
                'exclude_sale_items',
                'apply_before_tax',
                'usage_cost',
                'used_in',
            ),
            'Restrictions'          => array(
                'minimum_amount',
                'maximum_amount',
                'customer_email',
            ),
            'Products & Categories' => array(
                'product_ids',
                'exclude_product_ids',
                'product_categories',
                'exclude_product_categories',
            ),
            'Usage'                 => array( 'usage_count', 'usage_limit', 'used_by' ),
            'Dates'                 => array( 'expiry_date', 'post_date', 'post_modified' ),
        ),

        // Subscriptions.
        'subscription'   => array(
            'Subscription Info' => array(
                'subscription_id',
                'order_id',
                'post_status',
                'transaction_id',
                'payment_method',
            ),
            'Status & Schedule' => array(
                'status',
                'order_status',
                'start_date',
                'trial_end_date',
                'trial_length',
                'trial_period',
                'next_payment',
                'end_date',
                'last_payment',
                'coupon',
            ),
            'Financial'         => array(
                'recurring',
                'recurring_total',
                'purchase_total',
                'purchase_subtotal',
                'sign_up_fee',
            ),
            'Billing Address'   => array(
                'billing_first_name',
                'billing_last_name',
                'billing_full_name',
                'billing_company',
                'billing_address',
                'billing_address_1',
                'billing_address_2',
                'billing_city',
                'billing_state',
                'billing_state_full',
                'billing_postcode',
                'billing_country',
                'billing_country_full',
                'billing_email',
                'billing_phone',
            ),
            'Shipping Address'  => array(
                'shipping_first_name',
                'shipping_last_name',
                'shipping_full_name',
                'shipping_company',
                'shipping_address',
                'shipping_address_1',
                'shipping_address_2',
                'shipping_city',
                'shipping_state',
                'shipping_state_full',
                'shipping_postcode',
                'shipping_country',
                'shipping_country_full',
                'shipping_method',
                'shipping_method_id',
                'shipping_cost',
            ),
            'Customer'          => array( 'user', 'user_id' ),
            'Order Items'       => array(
                'order_items_name',
                'order_items_product_id',
                'order_items_sku',
                'order_items_quantity',
                'order_items_variation',
                'order_items_variation_id',
            ),
        ),

        // Reviews.
        'review'         => array(
            'Review'  => array(
                'comment_ID',
                'comment_post_ID',
                'comment_author',
                'comment_author_email',
                'comment_content',
                'rating',
                'verified',
                'comment_author_IP',
            ),
            'Product' => array( 'product_name', 'sku' ),
            'Dates'   => array( 'comment_date', 'comment_time' ),
        ),

        // Categories.
        'category'       => array(
            'Basic Info' => array(
                'term_id',
                'name',
                'slug',
                'description',
                'parent_id',
                'parent_name',
                'display_type',
                'order',
                'count',
                'term_url',
            ),
            'Hierarchy'  => array(
                'heirachy',
                'category_level_1',
                'category_level_2',
                'category_level_3',
                'category_level_4',
                'category_level_5',
                'category_level_6',
                'category_level_7',
            ),
            'Media'      => array( 'image', 'image_embed' ),
        ),

        // Tags.
        'tag'            => array(
            'Basic Info' => array( 'term_id', 'name', 'slug', 'description', 'count', 'term_url' ),
        ),

        // Attributes.
        'attribute'      => array(
            'Attribute Info' => array(
                'attribute_id',
                'attribute_name',
                'attribute_label',
                'attribute_type',
                'attribute_orderby',
                'attribute_public',
                'attribute_count',
            ),
            'Terms'          => array(
                'taxonomy',
                'term_id',
                'term_name',
                'term_slug',
                'term_description',
                'term_count',
            ),
        ),

        // Shipping Classes.
        'shipping_class' => array(
            'Basic Info' => array( 'term_id', 'name', 'slug', 'description' ),
        ),
    );

    /**
     * Predefined (default-selected) field names per export type.
     *
     * These are pre-selected when creating a new export template, giving users
     * a sensible starting point without requiring manual field selection.
     *
     * @since 2.8.0
     * @var array<string, string[]>
     */
    private static $predefined = array(
        'product'        => array( 'name', 'sku', 'type', 'price', 'sale_price', 'stock_status', 'quantity', 'category', 'product_status' ),
        'order'          => array( 'purchase_id', 'purchase_date', 'post_status', 'billing_first_name', 'billing_last_name', 'billing_email', 'purchase_total', 'payment_gateway' ),
        'customer'       => array( 'user_id', 'user_name', 'billing_first_name', 'billing_last_name', 'billing_email', 'billing_address_1', 'billing_city', 'billing_country' ),
        'user'           => array( 'user_id', 'user_name', 'email', 'first_name', 'last_name', 'date_registered', 'user_role' ),
        'coupon'         => array( 'coupon_code', 'discount_type', 'coupon_amount', 'usage_count', 'expiry_date' ),
        'category'       => array( 'name', 'slug', 'description', 'parent_id', 'count' ),
        'tag'            => array( 'name', 'slug', 'description', 'count' ),
        'subscription'   => array( 'subscription_id', 'order_status', 'start_date', 'next_payment', 'billing_first_name', 'billing_last_name', 'billing_email', 'purchase_total' ),
        'review'         => array( 'comment_ID', 'comment_author', 'comment_date', 'comment_content', 'rating', 'product_name' ),
        'attribute'      => array( 'attribute_name', 'attribute_label', 'attribute_type', 'attribute_orderby' ),
        'shipping_class' => array( 'name', 'slug', 'description' ),
    );

    /**
     * Cached inverted (field_name => category) maps, keyed by export type.
     *
     * @since 2.8.0
     * @var array<string, array<string, string>>
     */
    private static $inverted_cache = array();

    /**
     * Get the display category for a single field.
     *
     * @param string $type       Export type slug (e.g. 'product', 'order').
     * @param string $field_name Field name slug (e.g. 'sale_price').
     *
     * @since 2.8.0
     * @return string Category label; defaults to 'Basic Info' for unknown fields.
     */
    public static function get_category( string $type, string $field_name ): string {
        return self::get_inverted_map( $type )[ $field_name ] ?? 'Basic Info';
    }

    /**
     * Get the predefined (default-selected) field names for an export type.
     *
     * These represent a sensible starting selection for new export templates.
     *
     * @param string $type Export type slug.
     *
     * @since 2.8.0
     * @return string[]
     */
    public static function get_predefined( string $type ): array {
        $predefined = self::$predefined[ $type ] ?? array();

        /**
         * Filters the default-selected fields for an export type.
         *
         * @param string[] $predefined Array of field name slugs.
         * @param string   $type       Export type slug.
         */
        return (array) apply_filters( "wsed_field_registry_predefined_{$type}", $predefined, $type );
    }

    /**
     * Build and cache the inverted (field_name => category) lookup for a type.
     *
     * @param string $type Export type slug.
     *
     * @since 2.8.0
     * @return array<string, string>
     */
    private static function get_inverted_map( string $type ): array {
        if ( isset( self::$inverted_cache[ $type ] ) ) {
            return self::$inverted_cache[ $type ];
        }

        $categories = self::$categories[ $type ] ?? array();

        /**
         * Filters the category to field-name map for a specific export type.
         *
         * Use this to add custom categories or reassign fields to different
         * categories for your integration's fields.
         *
         * Example - adding a custom category for WooCommerce Brands:
         *   add_filter( 'wsed_field_registry_categories_product', function( $cats ) {
         *       $cats['Brands'] = [ 'brand_name', 'brand_slug' ];
         *       return $cats;
         *   } );
         *
         * @param array  $categories Array of [ category_label => [ field_names ] ].
         * @param string $type       Export type slug.
         */
        $categories = (array) apply_filters( "wsed_field_registry_categories_{$type}", $categories, $type );

        $map = array();
        foreach ( $categories as $category => $fields ) {
            foreach ( (array) $fields as $field_name ) {
                $map[ (string) $field_name ] = (string) $category;
            }
        }

        self::$inverted_cache[ $type ] = $map;
        return $map;
    }
}
