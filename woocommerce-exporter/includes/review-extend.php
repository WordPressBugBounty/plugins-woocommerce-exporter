<?php
// Adds custom Review columns to the Review fields list
function woo_ce_extend_review_fields( $fields = array() ) {

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
add_filter( 'woo_ce_review_fields', 'woo_ce_extend_review_fields' );

function woo_ce_extend_review_item( $review ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$review->blog_id = get_current_blog_id();
		$current_blog_details = get_blog_details( array( 'blog_id' => $review->blog_id ) );
		$review->blog_name = $current_blog_details->blogname;
		unset( $current_blog_details );
	}

	return $review;

}
add_filter( 'woo_ce_review_item', 'woo_ce_extend_review_item' );
?>