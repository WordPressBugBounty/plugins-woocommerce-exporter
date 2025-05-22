<?php
/**
 * Order items formatting: Individual.
 *
 * @param array $order      Order array.
 * @param array $order_item Order item array.
 * @return object $order
 */
function woo_ce_order_items_individual( $order, $order_item ) {

    // Drop in our content filters here.
    add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

    // Cycle through all $order->order_items... and clear them.
    if ( ! empty( $order ) ) {
        foreach ( $order as $key => $column ) {
            if (
                strpos( $key, 'order_items_' ) !== false &&
                is_string( $order[ $key ] )
            ) {
                $order[ $key ] = '';
            }
        }
        unset( $key, $column );
    }

    $order['order_items_id']                       = isset( $order_item['id'] ) ? $order_item['id'] : '';
    $order['order_items_product_id']               = isset( $order_item['product_id'] ) ? $order_item['product_id'] : '';
    $order['order_items_variation_id']             = isset( $order_item['variation_id'] ) ? $order_item['variation_id'] : '';
    $order['order_items_sku']                      = isset( $order_item['sku'] ) ? $order_item['sku'] : '';
    $order['order_items_name']                     = isset( $order_item['name'] ) ? $order_item['name'] : '';
    $order['order_items_variation']                = isset( $order_item['variation'] ) ? $order_item['variation'] : '';
    $order['order_items_image_embed']              = isset( $order_item['image_embed'] ) ? $order_item['image_embed'] : '';
    $order['order_items_description']              = isset( $order_item['description'] ) ? woo_ce_format_description_excerpt( $order_item['description'] ) : '';
    $order['order_items_excerpt']                  = isset( $order_item['excerpt'] ) ? woo_ce_format_description_excerpt( $order_item['excerpt'] ) : '';
    $order['order_items_publish_date']             = isset( $order_item['publish_date'] ) ? $order_item['publish_date'] : '';
    $order['order_items_modified_date']            = isset( $order_item['modified_date'] ) ? $order_item['modified_date'] : '';
    $order['order_items_tax_class']                = isset( $order_item['tax_class'] ) ? $order_item['tax_class'] : '';
    $order['order_items_quantity']                 = isset( $order_item['quantity'] ) ? $order_item['quantity'] : '';
    $order['order_items_total']                    = isset( $order_item['total'] ) ? $order_item['total'] : '';
    $order['order_items_subtotal']                 = isset( $order_item['subtotal'] ) ? $order_item['subtotal'] : '';
    $order['order_items_rrp']                      = isset( $order_item['rrp'] ) ? $order_item['rrp'] : '';
    $order['order_items_discount']                 = isset( $order_item['rrp'] ) && isset( $order_item['quantity'] ) && isset( $order_item['total'] ) ? round( ( (int) $order_item['rrp'] * (int) $order_item['quantity'] ) - (int) $order_item['total'], 2, PHP_ROUND_HALF_DOWN ) : '';
    $order['order_items_stock']                    = isset( $order_item['stock'] ) ? $order_item['stock'] : '';
    $order['order_items_shipping_class']           = isset( $order_item['shipping_class'] ) ? $order_item['shipping_class'] : '';
    $order['order_items_tax']                      = isset( $order_item['tax'] ) ? $order_item['tax'] : '';
    $order['order_items_tax_percentage']           = isset( $order_item['tax_percentage'] ) ? $order_item['tax_percentage'] : '';
    $order['order_items_tax_subtotal']             = isset( $order_item['tax_subtotal'] ) ? $order_item['tax_subtotal'] : '';
    $order['order_items_refund_subtotal']          = isset( $order_item['refund_subtotal'] ) ? $order_item['refund_subtotal'] : '';
    $order['order_items_refund_subtotal_incl_tax'] = isset( $order_item['refund_subtotal_incl_tax'] ) ? $order_item['refund_subtotal_incl_tax'] : '';
    $order['order_items_refund_quantity']          = isset( $order_item['refund_quantity'] ) ? $order_item['refund_quantity'] : '';
    $order['order_items_type']                     = isset( $order_item['type'] ) ? $order_item['type'] : '';
    $order['order_items_type_id']                  = isset( $order_item['type_id'] ) ? $order_item['type_id'] : '';
    $order['order_items_category']                 = isset( $order_item['category'] ) ? $order_item['category'] : '';
    $order['order_items_tag']                      = isset( $order_item['tag'] ) ? $order_item['tag'] : '';
    $order['order_items_weight']                   = isset( $order_item['weight'] ) ? $order_item['weight'] : '';
    $order['order_items_height']                   = isset( $order_item['height'] ) ? $order_item['height'] : '';
    $order['order_items_width']                    = isset( $order_item['width'] ) ? $order_item['width'] : '';
    $order['order_items_length']                   = isset( $order_item['length'] ) ? $order_item['length'] : '';
    $order['order_items_total_sales']              = isset( $order_item['total_sales'] ) ? $order_item['total_sales'] : '';
    $order['order_items_total_weight']             = isset( $order_item['total_weight'] ) ? $order_item['total_weight'] : '';
    $order['refund_items_refunded_by']             = isset( $order_item['refunded_by'] ) ? $order_item['refunded_by'] : '';
    $order['refund_items_refunded_payment']        = isset( $order_item['refunded_payment'] ) ? $order_item['refunded_payment'] : '';
    $order['refund_items_refund_reason']           = isset( $order_item['refund_reason'] ) ? $order_item['refund_reason'] : '';
    $order['refund_items_refund_amount']           = isset( $order_item['refund_amount'] ) ? $order_item['refund_amount'] : '';
    $order['refund_items_prices_include_tax']      = isset( $order_item['prices_include_tax'] ) ? $order_item['prices_include_tax'] : '';
    $order['credit_note_date']                     = isset( $order_item['credit_note_date'] ) ? $order_item['credit_note_date'] : '';
    $order['credit_note_number']                   = isset( $order_item['credit_note_number'] ) ? $order_item['credit_note_number'] : '';

    // Add Order Item weight to Shipping Weight.
    if ( version_compare( woo_get_woo_version(), '2.7', '<' ) ) {
        if ( ! empty( $order_item['total_weight'] ) ) {
            $order['shipping_weight_total'] += $order_item['total_weight'];
        }
    }

    // Remove our content filters here to play nice with other Plugins.
    remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

    return $order;
}
add_filter( 'woo_ce_order_items_individual', 'woo_ce_order_items_individual', 10, 2 );
