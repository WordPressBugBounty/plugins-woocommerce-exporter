<div class="overview-left">

    <h3>
        <div class="dashicons dashicons-migrate"></div>&nbsp;<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>"><?php esc_html_e( 'Quick Export', 'woocommerce-exporter' ); ?></a>
    </h3>
    <p><?php esc_html_e( 'Export store details out of WooCommerce into common export files (e.g. CSV, TSV, XLS, XLSX, XML, etc.).', 'woocommerce-exporter' ); ?></p>
    <ul class="ul-disc">
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-product"><?php esc_html_e( 'Export Products', 'woocommerce-exporter' ); ?></a>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-category"><?php esc_html_e( 'Export Categories', 'woocommerce-exporter' ); ?></a>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-tag"><?php esc_html_e( 'Export Tags', 'woocommerce-exporter' ); ?></a>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-brand"><?php esc_html_e( 'Export Brands', 'woocommerce-exporter' ); ?></a>
			<span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=exportbrandslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-order"><?php esc_html_e( 'Export Orders', 'woocommerce-exporter' ); ?></a>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-customer"><?php esc_html_e( 'Export Customers', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=exportcustomerslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-user"><?php esc_html_e( 'Export Users', 'woocommerce-exporter' ); ?></a>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-user"><?php esc_html_e( 'Export Reviews', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=exportuserslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-coupon"><?php esc_html_e( 'Export Coupons', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=exportcouponslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-subscription"><?php esc_html_e( 'Export Subscriptions', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=exportsubscriptionslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-product_vendor"><?php esc_html_e( 'Export Product Vendors', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=exportproductvendorslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-commission"><?php esc_html_e( 'Export Commissions', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=exportcommissionslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-shipping_class"><?php esc_html_e( 'Export Shipping Classes', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=exportshippingclassslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-ticket"><?php esc_html_e( 'Export Tickets', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=exportticketslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-booking"><?php esc_html_e( 'Export Bookings', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=exportbookingslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-attribute"><?php esc_html_e( 'Export Attributes', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=settingsxmlsettingslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
    </ul>

    <h3>
        <div class="dashicons dashicons-calendar"></div>&nbsp;<a href="<?php echo esc_url( add_query_arg( 'tab', 'scheduled_export' ) ); ?>"><?php esc_html_e( 'Scheduled Exports', 'woocommerce-exporter' ); ?></a>
        <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=scheduledexportslink' )
                    )
                ) . ')';
                ?>
            </span>
    </h3>
    <p><?php esc_html_e( 'Automatically generate exports and apply filters to export just what you need.', 'woocommerce-exporter' ); ?></p>

    <h3>
        <div class="dashicons dashicons-list-view"></div>&nbsp;<a href="<?php echo esc_url( add_query_arg( 'tab', 'export_template' ) ); ?>"><?php esc_html_e( 'Export Templates', 'woocommerce-exporter' ); ?></a>
        <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=exporttemplateslink' )
                    )
                ) . ')';
                ?>
            </span>
    </h3>
    <p><?php esc_html_e( 'Create lists of pre-defined fields which can be applied to exports.', 'woocommerce-exporter' ); ?></p>

    <h3>
        <div class="dashicons dashicons-list-view"></div>&nbsp;<a href="<?php echo esc_url( add_query_arg( 'tab', 'archive' ) ); ?>"><?php esc_html_e( 'Archives', 'woocommerce-exporter' ); ?></a>
    </h3>
    <p><?php esc_html_e( 'Download copies of prior store exports.', 'woocommerce-exporter' ); ?></p>

    <h3>
        <div class="dashicons dashicons-admin-settings"></div>&nbsp;<a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>"><?php esc_html_e( 'Settings', 'woocommerce-exporter' ); ?></a>
    </h3>
    <p><?php esc_html_e( 'Manage export options from a single detailed screen.', 'woocommerce-exporter' ); ?></p>
    <ul class="ul-disc">
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>#general-settings"><?php esc_html_e( 'General Settings', 'woocommerce-exporter' ); ?></a>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>#csv-settings"><?php esc_html_e( 'CSV Settings', 'woocommerce-exporter' ); ?></a>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>#xml-settings"><?php esc_html_e( 'XML Settings', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=settingsscheduledexportslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>#scheduled-exports"><?php esc_html_e( 'Scheduled Exports', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=settingscronexportslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>#cron-exports"><?php esc_html_e( 'CRON Exports', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=settingsordersscreenlink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>#orders-screen"><?php esc_html_e( 'Orders Screen', 'woocommerce-exporter' ); ?></a>
            <span class="description">
                <?php
                echo '(' . wp_kses_post(
                    sprintf(
                        // translators: %s: link to upgrade to the premium version.
                        __( 'available in %s', 'woocommerce-exporter' ),
                        woo_ce_upsell_link( '?utm_source=wse&utm_medium=overview&utm_campaign=settingsexporttriggerslink' )
                    )
                ) . ')';
                ?>
            </span>
        </li>
    </ul>

    <h3>
        <div class="dashicons dashicons-hammer"></div>&nbsp;<a href="<?php echo esc_url( add_query_arg( 'tab', 'tools' ) ); ?>"><?php esc_html_e( 'Tools', 'woocommerce-exporter' ); ?></a>
    </h3>
    <p><?php esc_html_e( 'Export tools for WooCommerce.', 'woocommerce-exporter' ); ?></p>

    <hr />
    <form id="skip_overview_form" method="post">
        <label><input type="checkbox" id="skip_overview" name="skip_overview" <?php checked( $skip_overview ); ?> /> <?php esc_html_e( 'Jump to Export screen in the future', 'woocommerce-exporter' ); ?></label>
        <input type="hidden" name="action" value="skip_overview" />
        <?php wp_nonce_field( 'skip_overview', 'woo_ce_skip_overview' ); ?>
    </form>

</div>
<!-- .overview-left -->
<div class="welcome-panel overview-right">
	<h3><?php esc_html_e( 'Upgrade to Store Exporter Deluxe today!', 'woocommerce-exporter' ); ?></h3>
	<ul class="ul-disc">
		<li><?php esc_html_e( 'Native export support for 125+ Plugins', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Premium Support', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Select export date ranges', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Export Templates', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Filter exports by multiple filter options', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Export Orders, Customers, Coupons, Subscriptions, Product Vendors, Shippping Classes, Attributes, Bookings, Tickets and more', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Export custom Order, Order Item, Customer, Subscription, Booking, User meta and more', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'CRON export engine', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'WP-CLI export engine', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Schedule automatic exports with filtering options', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Export automatically on new Order received', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Export to remote POST', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Export to e-mail addresses', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Export to remote FTP/FTPS/SFTP', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Export to Excel 2007-2013 (XLSX) file', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Export to Excel 97-2003 (XLS) file', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( 'Export to XML, JSON and RSS file formats', 'woocommerce-exporter' ); ?></li>
		<li><?php esc_html_e( '...and more.', 'woocommerce-exporter' ); ?></li>
	</ul>
	<p>
		<a href="<?php echo 'https://visser.com.au/plugins/woocommerce-export/?utm_source=wse&utm_medium=overview&utm_campaign=upsellupgradenowbutton'; ?>" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade Now', 'woocommerce-exporter' ); ?></a>
		<a href="<?php echo 'https://visser.com.au/plugins/woocommerce-export/?utm_source=wse&utm_medium=overview&utm_campaign=upselltellmemorebutton'; ?>" target="_blank" class="button"><?php esc_html_e( 'Tell me more', 'woocommerce-exporter' ); ?></a>&nbsp;
	</p>
</div>
<!-- .overview-right -->
