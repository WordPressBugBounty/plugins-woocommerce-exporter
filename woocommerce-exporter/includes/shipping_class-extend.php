<?php
// Adds custom Shipping Class columns to the Shipping Class fields list
function woo_ce_extend_shipping_class_fields( $fields = array() ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$fields[] = array(
			'name' => 'blog_id',
			'label' => __( 'Blog ID', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress Multisite', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'blog_name',
			'label' => __( 'Blog Name', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress Multisite', 'woocommerce-exporter' )
		);
	}

	return $fields;

}
add_filter( 'woo_ce_shipping_class_fields', 'woo_ce_extend_shipping_class_fields' );

function woo_ce_extend_shipping_class_item( $shipping_classes ) {

	if( !empty( $shipping_classes ) ) {

		// WordPress MultiSite
		if( is_multisite() ) {
			foreach( $shipping_classes as $key => $shipping_class ) {
				$shipping_classes[$key]->blog_id = get_current_blog_id();
				$current_blog_details = get_blog_details( array( 'blog_id' => $shipping_classes[$key]->blog_id) );
				$shipping_classes[$key]->blog_name = $current_blog_details->blogname;
				unset( $current_blog_details );
			}
		}

	}
	return $shipping_classes;

}
add_filter( 'woo_ce_shipping_class_item', 'woo_ce_extend_shipping_class_item' );
?>