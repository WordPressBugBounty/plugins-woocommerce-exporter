<ul class="subsubsub">
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', null ) ); ?>" <?php woo_ce_archives_quicklink_current( 'all' ); ?>><?php esc_html_e( 'All', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count() ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'product' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'product' ); ?>><?php esc_html_e( 'Products', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'product' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'category' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'category' ); ?>><?php esc_html_e( 'Categories', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'category' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'tag' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'tag' ); ?>><?php esc_html_e( 'Tags', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'tag' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'brand' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'brand' ); ?>><?php esc_html_e( 'Brands', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'brand' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'order' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'order' ); ?>><?php esc_html_e( 'Orders', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'order' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'customer' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'customer' ); ?>><?php esc_html_e( 'Customers', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'customer' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'user' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'user' ); ?>><?php esc_html_e( 'Users', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'user' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'review' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'review' ); ?>><?php esc_html_e( 'Review', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'review' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'coupon' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'coupon' ); ?>><?php esc_html_e( 'Coupons', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'coupon' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'subscription' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'subscription' ); ?>><?php esc_html_e( 'Subscriptions', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'subscription' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'product_vendor' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'product_vendor' ); ?>><?php esc_html_e( 'Product Vendors', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'product_vendor' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'commission' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'commission' ); ?>><?php esc_html_e( 'Commissions', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'commission' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'shipping_class' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'shipping_class' ); ?>><?php esc_html_e( 'Shipping Classes', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'shipping_class' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'ticket' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'ticket' ); ?>><?php esc_html_e( 'Tickets', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'ticket' ) ); ?>)</span></a> |</li>
    <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'booking' ) ); ?>" <?php woo_ce_archives_quicklink_current( 'booking' ); ?>><?php esc_html_e( 'Bookings', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'booking' ) ); ?>)</span></a></li>
    <!-- <li><a href="<?php echo esc_url( add_query_arg( 'filter', 'attribute' ) ); ?>"<?php woo_ce_archives_quicklink_current( 'attribute' ); ?>><?php esc_html_e( 'Attributes', 'woocommerce-exporter' ); ?> <span class="count">(<?php echo esc_html( woo_ce_archives_quicklink_count( 'attribute' ) ); ?>)</span></a></li> -->
</ul>
<!-- .subsubsub -->
<br class="clear" />

<form id="archives-filter" method="POST">
    <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
    <!-- Now we can render the completed list table -->

    <?php $archives_table->display(); ?>

    <?php if ( ! empty( $count ) ) { ?>
        <a href="
        <?php
        echo esc_url(
            add_query_arg(
                array(
					'action'   => 'nuke_archives',
					'_wpnonce' => wp_create_nonce( 'woo_ce_nuke_archives' ),
                )
            )
        );
?>
" class="button action delete" data-confirm="<?php esc_attr_e( 'This will permanently delete all saved exports listed within the Archives screen of Store Exporter Deluxe. Are you sure you want to proceed?', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Delete All', 'woocommerce-exporter' ); ?></a>
    <?php } ?>
</form>
