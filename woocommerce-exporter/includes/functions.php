<?php
// phpcs:disable
/**
 * Load export types.
 *
 * Only load these resources if we are running an export, can be limited to a single export type.
 *
 * @param string|bool $export_type The export type to load. Default is false.
 */
function woo_ce_load_export_types( $export_type = false ) {

	if ( ! empty( $export_type ) ) {
		if ( file_exists( WOO_CE_PATH . 'includes/' . $export_type . '.php' ) ) {
			include_once WOO_CE_PATH . 'includes/' . $export_type . '.php';
        }
		if ( file_exists( WOO_CE_PATH . 'includes/' . $export_type . '-extend.php' ) ) {
			include_once WOO_CE_PATH . 'includes/' . $export_type . '-extend.php';
        }
		if ( file_exists( WOO_CE_PATH . 'includes/admin/' . $export_type . '.php' ) ) {
			include_once WOO_CE_PATH . 'includes/admin/' . $export_type . '.php';
        }
		return;
	}

	include_once WOO_CE_PATH . 'includes/product.php';
	include_once WOO_CE_PATH . 'includes/product-extend.php';
	include_once WOO_CE_PATH . 'includes/category.php';
	include_once WOO_CE_PATH . 'includes/category-extend.php';
	include_once WOO_CE_PATH . 'includes/tag.php';
	include_once WOO_CE_PATH . 'includes/tag-extend.php';
	include_once WOO_CE_PATH . 'includes/brand.php';
	include_once WOO_CE_PATH . 'includes/brand-extend.php';
	include_once WOO_CE_PATH . 'includes/order.php';
	include_once WOO_CE_PATH . 'includes/order-extend.php';
	include_once WOO_CE_PATH . 'includes/order-combined-extend.php';
	include_once WOO_CE_PATH . 'includes/order-individual.php';
	include_once WOO_CE_PATH . 'includes/order-individual-extend.php';
	include_once WOO_CE_PATH . 'includes/order-unique.php';
	include_once WOO_CE_PATH . 'includes/order-unique-extend.php';
	include_once WOO_CE_PATH . 'includes/customer.php';
	include_once WOO_CE_PATH . 'includes/customer-extend.php';
	include_once WOO_CE_PATH . 'includes/user.php';
	include_once WOO_CE_PATH . 'includes/user-extend.php';
	include_once WOO_CE_PATH . 'includes/review.php';
	include_once WOO_CE_PATH . 'includes/review-extend.php';
	include_once WOO_CE_PATH . 'includes/coupon.php';
	include_once WOO_CE_PATH . 'includes/coupon-extend.php';
	include_once WOO_CE_PATH . 'includes/subscription.php';
	include_once WOO_CE_PATH . 'includes/subscription-extend.php';
	include_once WOO_CE_PATH . 'includes/product_vendor.php';
	include_once WOO_CE_PATH . 'includes/product_vendor-extend.php';
	include_once WOO_CE_PATH . 'includes/commission.php';
	include_once WOO_CE_PATH . 'includes/shipping_class.php';
	include_once WOO_CE_PATH . 'includes/shipping_class-extend.php';
	include_once WOO_CE_PATH . 'includes/ticket.php';
	include_once WOO_CE_PATH . 'includes/ticket-extend.php';
	include_once WOO_CE_PATH . 'includes/attribute.php';
	include_once WOO_CE_PATH . 'includes/booking.php';

	// Load the export type resources first.
	if ( is_admin() ) {
		include_once WOO_CE_PATH . 'includes/admin/product.php';
		include_once WOO_CE_PATH . 'includes/admin/product-extend.php';
		include_once WOO_CE_PATH . 'includes/admin/category.php';
		include_once WOO_CE_PATH . 'includes/admin/tag.php';
		include_once WOO_CE_PATH . 'includes/admin/brand.php';
		include_once WOO_CE_PATH . 'includes/admin/order.php';
		include_once WOO_CE_PATH . 'includes/admin/order-extend.php';
		include_once WOO_CE_PATH . 'includes/admin/customer.php';
		include_once WOO_CE_PATH . 'includes/admin/user.php';
		include_once WOO_CE_PATH . 'includes/admin/review.php';
		include_once WOO_CE_PATH . 'includes/admin/coupon.php';
		include_once WOO_CE_PATH . 'includes/admin/subscription.php';
		include_once WOO_CE_PATH . 'includes/admin/product_vendor.php';
		include_once WOO_CE_PATH . 'includes/admin/commission.php';
		include_once WOO_CE_PATH . 'includes/admin/shipping_class.php';
		include_once WOO_CE_PATH . 'includes/admin/ticket.php';
		include_once WOO_CE_PATH . 'includes/admin/booking.php';
	}
}

// Check if we are using PHP 5.3 and above.
if ( version_compare( phpversion(), '5.3' ) >= 0 ) {
	require_once WOO_CE_PATH . 'includes/legacy.php';
}
require_once WOO_CE_PATH . 'includes/formatting.php';

// Check if we are using PHP 5.3 and above.
if ( version_compare( phpversion(), '5.3' ) >= 0 ) {
	include_once WOO_CE_PATH . 'includes/legacy.php';
}
require_once WOO_CE_PATH . 'includes/formatting.php';

/* Start of: WordPress Administration */
if ( is_admin() ) {

	require_once WOO_CE_PATH . 'includes/admin.php';

	require_once WOO_CE_PATH . 'includes/admin/widgets.php';
	require_once WOO_CE_PATH . 'includes/admin/scheduled_export.php';
	require_once WOO_CE_PATH . 'includes/export_template.php';
	require_once WOO_CE_PATH . 'includes/archives.php';
	require_once WOO_CE_PATH . 'includes/settings.php';

	/**
	 * Initialize the export process.
	 */
	function woo_ce_export_init() {

		// Process any pre-export notice confirmations.
		$action = ( function_exists( 'woo_get_action' ) ? woo_get_action() : false );
		switch ( $action ) {

			// Disable Plugin localisation.
			case 'reset_language_english':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_reset_language_english' ) ) {
					$state = woo_ce_get_option( 'reset_language_english', false );
					woo_ce_update_option( 'reset_language_english', ( $state ? false : 1 ) );
					if ( $state ) {
						$message = __( 'Plugin localisation has been re-activated and will load the included language file.', 'woocommerce-exporter' );
					} else {
						$message = __( 'Plugin localisation has been de-activated.', 'woocommerce-exporter' );
					}
					woo_cd_admin_notice( $message );
				}
				break;

			// Reset hidden Export Types on the Quick Export screen.
			case 'reset_hidden_export_types':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_reset_hidden_export_types' ) ) {
					woo_ce_update_option( 'hidden_export_types', false );
					$message = __( 'All hidden Export Types on the Quick Export screen have been restored.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			// Switch for turning on/off debug mode.
			case 'enable_debug_mode':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_enable_debug_mode' ) ) {
					woo_ce_update_option( 'debug_mode', 1 );
					$message = __( 'Debugging mode within Store Exporter Deluxe has been enabled. Confirm this by opening the WooCommerce > Status screen.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			case 'disable_debug_mode':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_disable_debug_mode' ) ) {
					woo_ce_update_option( 'debug_mode', 0 );
					$message = __( 'Debugging mode within Store Exporter Deluxe has been de-activated. Confirm this by opening the WooCommerce > Status screen.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			// Switch for turning on/off logging mode.
			case 'enable_logging_mode':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_enable_logging_mode' ) ) {
					woo_ce_update_option( 'logging_mode', 1 );
					$message = __( 'Logging mode within Store Exporter Deluxe has been enabled. Confirm this by opening the WooCommerce > Status screen.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			case 'disable_logging_mode':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_disable_logging_mode' ) ) {
					woo_ce_update_option( 'logging_mode', 0 );
					$message = __( 'Logging mode within Store Exporter Deluxe has been de-activated. Confirm this by opening the WooCommerce > Status screen.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			// Rebuild known TM EPO fields.
			case 'rebuild_tm_epo_fields':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_rebuild_tm_epo_fields' ) ) {
					// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619.
					if ( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
						delete_transient( WOO_CE_PREFIX . '_extra_product_option_fields' );
						delete_transient( WOO_CE_PREFIX . '_extra_product_option_fields_temp' );
						if ( function_exists( 'woo_ce_get_extra_product_option_fields' ) ) {
							woo_ce_get_extra_product_option_fields();
						}
					}
					$message = __( 'The list of known WooCommerce TM Extra Product Options fields has been refreshed.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			// Reset all dismissed notices within Store Exporter Deluxe.
			case 'nuke_notices':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_nuke_notices' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_nuke_dismissed_notices();
					$message = __( 'All dimissed notices within Store Exporter Deluxe have been restored.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			// Delete all WordPress Options associated with Store Exporter Deluxe.
			case 'nuke_options':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_nuke_options' ) ) {
					// Delete WordPress Options used by Store Exporter Deluxe (Uninstall).
					if ( woo_ce_nuke_options() ) {
						$message = __( 'All Store Exporter Deluxe WordPress Options have been deleted from your WordPress site, you can now de-activate and delete Store Exporter Deluxe.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message );
					} else {
						$message = __( 'Not all Store Exporter Deluxe WordPress Options could be deleted from your WordPress site, please see the WordPress Options table for Options prefixed by <code>woo_ce_</code>.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message, 'error' );
					}
				}
				break;

			// Delete all Archives.
			case 'nuke_archives':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_nuke_archives' ) ) {
					// Delete saved exports.
					if ( woo_ce_nuke_archive_files() ) {
						$message = __( 'All existing Archives and their export files have been deleted from your WordPress site.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message );
					} else {
						$message = __( 'There were no existing Archives to be deleted from your WordPress site.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message, 'error' );
					}
				}
				break;

			// Delete Recent Scheduled Exports.
			case 'nuke_recent_scheduled_exports':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_nuke_recent_scheduled_exports' ) ) {
					// Delete recent scheduled exports.
					woo_ce_update_option( 'recent_scheduled_exports', false );
					$message = __( 'The Recent Scheduled Exports list has been cleared.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			// Reset WP-CRON.
			case 'nuke_cron':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_nuke_cron' ) ) {
					// Reset WP-CRON.
					if ( woo_ce_nuke_cron() ) {
						$message = __( 'The WordPress Option \'cron\' has been reset, it will be re-populated on the next screen load.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message );
					} else {
						$message = __( ' WordPress Option \'cron\' could not be reset.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message, 'error' );
					}
				}
				break;

			// Delete all Scheduled Exports.
			case 'nuke_scheduled_exports':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_nuke_archives' ) ) {
					// Delete Scheduled Exports.
					if ( woo_ce_nuke_scheduled_exports() ) {
						$message = __( 'All existing Scheduled Exports have been deleted from your WordPress site.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message );
					} else {
						$message = __( 'There were no existing Scheduled Exports to be deleted from your WordPress site.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message, 'error' );
					}
				}
				break;

			// Delete Export Transient.
			case 'clear_export_transient':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'clear_export_transient' ) ) {
					$exporter = new VisserLabs\WSE\Classes\Exporter();
					$exporter->clear_export_transient();

					$message = __( 'Export transient cache has been cleared.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			case 'dismiss_archives_privacy_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_archives_privacy_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_archives_privacy_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'override_archives_privacy':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_override_archives_privacy' ) ) {
					// Update Post Status of unsecured saved exports.
					if ( woo_ce_update_archives_privacy() ) {
						$message = __( 'All archived exports have been updated.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message );
					} else {
						$message = __( 'There were no existing archived exports to be updated.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message, 'error' );
					}
				}
				break;

			// Prompt on Export screen when insufficient memory (less than 64M is allocated).
			case 'dismiss_memory_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_memory_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_memory_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			// Prompt on Export screen when memory is showing different amounts (WordPress vs PHP).
			case 'dismiss_memory_sync_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_memory_sync_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_memory_sync_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			// Prompt on Scheduled Export screen when DISABLE_WP_CRON is defined.
			case 'dismiss_disable_wp_cron_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_disable_wp_cron_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_disable_wp_cron_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			// Prompt on Export screen when open_basedir is enabled and the PHP temp directory is not in the exception list.
			case 'dismiss_open_basedir_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_open_basedir_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_open_basedir_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			// Prompt on Export screen when PHP configuration option max_execution_time cannot be increased.
			case 'dismiss_execution_time_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_execution_time_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_execution_time_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			// Prompt on Export screen when PHP 5.2 or lower is installed.
			case 'dismiss_php_legacy':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_php_legacy' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_php_legacy', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			// Displays a notice if Checkout Field Editor Pro is installed.
			case 'dismiss_wcfe_notice':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_wcfe_notice' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_wcfe_notice', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_subscription_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_subscription_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_subscription_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_checkout_addons_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_checkout_addons_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_checkout_addons_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_extra_product_options_scan_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_extra_product_options_scan_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_extra_product_options_scan_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_query_monitor_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_query_monitor_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_query_monitor_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_export_running_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_export_running_prompt' ) ) {
					// Remember that we've dismissed this notice.
					delete_transient( WOO_CE_PREFIX . '_running' );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_multisite_network_admin_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_multisite_network_admin_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_multisite_network_admin_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_ziparchive_missing_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_ziparchive_missing_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_ziparchive_missing_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_checkout_field_editor_pro_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_checkout_field_editor_pro_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_checkout_field_editor_pro_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_secure_archives_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_secure_archives_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_secure_archives_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_overview_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_overview_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_overview_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_quick_export_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_quick_export_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_quick_export_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_quick_export_language_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_quick_export_language_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_quick_export_language_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_max_input_vars_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_max_input_vars_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_max_input_vars_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'enable_archives':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_enable_archives' ) ) {
					woo_ce_update_option( 'delete_file', 0 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_archives_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_archives_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_archives_prompt', 1 );
					$url = add_query_arg(
						array(
							'action'   => null,
							'_wpnonce' => null,
						)
					);
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'hide_archives_tab':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_hide_archives_tab' ) ) {
					// Remember to hide the Archives tab.
					woo_ce_update_option( 'hide_archives_tab', 1 );
					$url = add_query_arg(
						array(
							'tab'      => 'export',
							'action'   => null,
							'_wpnonce' => null,
                        )
                    );
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'restore_archives_tab':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_restore_archives_tab' ) ) {
					// Remember to show the Archives tab.
					woo_ce_update_option( 'hide_archives_tab', 0 );
					$url = add_query_arg(
                        array(
							'tab'      => 'archive',
							'action'   => null,
							'_wpnonce' => null,
                        )
                    );
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_scheduled_exports_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_scheduled_exports_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_scheduled_exports_prompt', 1 );
					$url = add_query_arg(
                        array(
							'action'   => null,
							'_wpnonce' => null,
                        )
                    );
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_wp_mail_false_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_wp_mail_false_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_wp_mail_false_prompt', 1 );
					$url = add_query_arg(
                        array(
							'action'   => null,
							'_wpnonce' => null,
                        )
                    );
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'hide_wp_mail_false_prompt':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_hide_wp_mail_false_prompt' ) ) {
					// Remember that we've dismissed this notice.
					woo_ce_update_option( 'dismiss_wp_mail_false_prompt', 1 );
					woo_ce_update_option( 'hide_wp_mail_false_prompt', 1 );
					$url = add_query_arg(
                        array(
							'action'   => null,
							'_wpnonce' => null,
                        )
                    );
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'hide_scheduled_exports_tab':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_hide_scheduled_exports_tab' ) ) {
					// Remember to hide the Scheduled Exports tab.
					woo_ce_update_option( 'hide_scheduled_exports_tab', 1 );
					$url = add_query_arg(
                        array(
							'tab'      => 'export',
							'action'   => null,
							'_wpnonce' => null,
                        )
                    );
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'restore_scheduled_exports_tab':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_restore_scheduled_exports_tab' ) ) {
					// Remember to show the Scheduled Exports tab.
					woo_ce_update_option( 'hide_scheduled_exports_tab', 0 );
					$url = add_query_arg(
                        array(
							'tab'      => 'scheduled_export',
							'action'   => null,
							'_wpnonce' => null,
                        )
                    );
					wp_safe_redirect( $url );
					exit();
				}
				break;

			case 'enable_scheduled_exports':
				// We need to verify the nonce.
				if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_enable_scheduled_exports' ) ) {
					woo_ce_update_option( 'enable_auto', 1 );
					$url = add_query_arg(
                        array(
                            'action'   => null,
                            '_wpnonce' => null,
                        )
                    );
                    wp_safe_redirect( $url );
                    exit();
                }
                break;

            /**
             * Process scheduled export on next screen load.
             */
            case 'cancel_scheduled_export':
                // We need to verify the nonce.
                if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_cancel_scheduled_export' ) ) {
                    // Get the Scheduled Export Post ID.
                    $scheduled_export = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
                    if ( ! empty( $scheduled_export ) ) {
                        // Create a new Recent Scheduled Export entry for failed Scheduled Exports.
                        $message = 'Scheduled Export aborted from Scheduled Exports screen. The CRON export engine is available again.';
                        woo_ce_add_recent_scheduled_export( absint( get_transient( WOO_CE_PREFIX . '_scheduled_export_id' ) ), false, false, $message );
                        // Clear the Scheduled Export transient so other Scheduled Exports can run.
                        delete_transient( WOO_CE_PREFIX . '_scheduled_export_id' );
                        $url = add_query_arg(
                            array(
                                'tab'      => 'scheduled_export',
                                'action'   => null,
                                'post'     => null,
                                '_wpnonce' => null,
                            )
                        );
                        wp_safe_redirect( $url );
                        exit();
                    }
                }
                break;

            /**
             * Mark Scheduled Export as Draft.
             */
            case 'draft_scheduled_export':
                // We need to verify the nonce.
                if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_draft_scheduled_export' ) ) {
                    // Get the Scheduled Export Post ID.
                    $scheduled_export = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
                    if ( ! empty( $scheduled_export ) ) {
                        $args = array(
                            'ID'          => $scheduled_export,
                            'post_status' => 'draft',
                        );
                        wp_update_post( $args );
                        $url = add_query_arg(
                            array(
                                'tab'      => 'scheduled_export',
                                'action'   => null,
                                'post'     => null,
                                'draft'    => 1,
                                'publish'  => null,
                                'clone'    => null,
                                '_wpnonce' => null,
                            )
                        );
                        wp_safe_redirect( $url );
                        exit();
                    }
                }

                break;

            /**
             * Mark Scheduled Export as Publish.
             */
            case 'publish_scheduled_export':
                // We need to verify the nonce.
                if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_publish_scheduled_export' ) ) {
                    // Get the Scheduled Export Post ID.
                    $scheduled_export = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
                    if ( ! empty( $scheduled_export ) ) {
                        $args = array(
                            'ID'          => $scheduled_export,
                            'post_status' => 'publish',
                        );
                        wp_update_post( $args );
                        $url = add_query_arg(
                            array(
                                'tab'      => 'scheduled_export',
                                'action'   => null,
                                'post'     => null,
                                'publish'  => 1,
                                'draft'    => null,
                                'clone'    => null,
                                '_wpnonce' => null,
                            )
                        );
                        wp_safe_redirect( $url );
                        exit();
                    }
                }
                break;

            /**
             * Clone Scheduled Export.
             */
            case 'clone_scheduled_export':
                // We need to verify the nonce.
                if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_clone_scheduled_export' ) ) {
                    // Get the Scheduled Export Post ID.
                    $scheduled_export = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
                    if ( ! empty( $scheduled_export ) ) {
                        // Get the existing Scheduled Export.
                        $post = get_post( $scheduled_export );
                        if ( ! empty( $post ) ) {
                            $meta_keys = get_post_custom_keys( $scheduled_export );
                            // Create a new Scheduled Export with Draft Status.
                            $post_status    = 'draft';
                            $title          = ! empty( $post->post_title ) ? $post->post_title : __( 'Untitled', 'woocommerce-exporter' );
                            $post_author    = wp_get_current_user();
                            $post_author_id = $post_author->ID;
                            $menu_order     = 0;
                            $args           = array(
                                'post_author'           => $post_author_id,
                                'post_content'          => $post->post_content,
                                'post_content_filtered' => $post->post_content_filtered,
                                'post_title'            => $title,
                                'post_excerpt'          => $post->post_excerpt,
                                'post_status'           => $post_status,
                                'post_type'             => $post->post_type,
                                'menu_order'            => $menu_order,
                                'comment_status'        => $post->comment_status,
                                'ping_status'           => $post->ping_status,
                            );
                            $new_post_id    = wp_insert_post( wp_slash( $args ) );
                            // Save the existing Post meta to the new Scheduled Export.
                            if ( ! empty( $meta_keys ) ) {
                                foreach ( $meta_keys as $meta_key ) {
                                    $meta_values = get_post_custom_values( $meta_key, $scheduled_export );
                                    if ( ! empty( $meta_values ) ) {
                                        foreach ( $meta_values as $meta_value ) {

                                            // Skip the following.
                                            if ( in_array( $meta_key, array( '_last_export', '_total_exports' ), true ) ) {
                                                continue;
                                            }

                                            $meta_value = maybe_unserialize( $meta_value );
                                            add_post_meta( $new_post_id, $meta_key, wp_slash( $meta_value ) );
                                        }
                                    }
                                }
                            }
                        }
                        $url = add_query_arg(
                            array(
                                'tab'      => 'scheduled_export',
                                'action'   => null,
                                'post'     => null,
                                'clone'    => 1,
                                'publish'  => null,
                                'draft'    => null,
                                '_wpnonce' => null,
                            )
                        );
                        wp_safe_redirect( $url );
                        exit();
                    }
                }
                break;

            /**
             * Hide an export type from the Quick Export screen.
             */
            case 'hide_export_type':
                // We need to verify the nonce.
                if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_hide_export_type' ) ) {
                    $export_type = isset( $_GET['export_type'] ) ? sanitize_text_field( wp_unslash( $_GET['export_type'] ) ) : false;
                    if ( ! empty( $export_type ) ) {
                        // Do something.
                        $hidden_export_types = woo_ce_get_option( 'hidden_export_types', array() );
												if ( false === $hidden_export_types ) {
                            $hidden_export_types = array();
                        }
                        if ( ! in_array( $export_type, $hidden_export_types, true ) ) {
                            $hidden_export_types[] = $export_type;
                        }
                        woo_ce_update_option( 'hidden_export_types', $hidden_export_types );

                        // Show the response.
												// translators: %s: export type.
                        $message = sprintf( __( 'The selected export type has been hidden. To re-show hidden export types from Quick Export screen <a href="%s">open the Settings tab</a> and select Reset hidden Export Types from the General Settings > View advanced settings section.', 'woocommerce-exporter' ), add_query_arg( array( 'tab' => 'settings' ) ) );
                        woo_cd_admin_notice_html( $message );
                    }
                }
                break;

            /**
             * Reset the Transient counters for all export types.
             */
            case 'refresh_export_type_counts':
                // We need to verify the nonce.
                if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_refresh_export_type_counts' ) ) {
                    woo_ce_load_export_types();
                    delete_transient( WOO_CE_PREFIX . '_export_type_count' );

                    // Add in one for WooCommerce Extra Product Options.
                    delete_transient( WOO_CE_PREFIX . '_extra_product_option_fields' );
                    delete_transient( WOO_CE_PREFIX . '_extra_product_option_fields_temp' );

                    // Misc.
                    delete_transient( WOO_CE_PREFIX . '_user_roles' );
                    delete_transient( WOO_CE_PREFIX . '_order_statuses' );

                    // Gravity Forms - http://woothemes.com/woocommerce.
                    if ( woo_ce_detect_export_plugin( 'gravity_forms' ) ) {
                        delete_transient( WOO_CE_PREFIX . '_gravity_forms_products' );
                        delete_transient( WOO_CE_PREFIX . '_gravity_forms_fields' );
                        if ( function_exists( 'woo_ce_get_gravity_forms_fields' ) ) {
                            woo_ce_get_gravity_forms_fields();
                        }
                    }

                    // WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619.
                    if ( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
                        delete_transient( WOO_CE_PREFIX . '_extra_product_option_fields' );
                        delete_transient( WOO_CE_PREFIX . '_extra_product_option_fields_temp' );
                        if ( function_exists( 'woo_ce_get_extra_product_option_fields' ) ) {
                            woo_ce_get_extra_product_option_fields();
                        }
                    }

                    $url = add_query_arg(
                        array(
                            'action'   => null,
                            '_wpnonce' => null,
                        )
                    );
                    wp_safe_redirect( $url );
                    exit();
                }
                break;

            /**
             * Refresh module counts.
             */
            case 'refresh_module_counts':
                // We need to verify the nonce.
                if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_refresh_module_counts' ) ) {

                    // Delete the existing count Transients.
                    delete_transient( WOO_CE_PREFIX . '_modules_active' );
                    delete_transient( WOO_CE_PREFIX . '_modules_all_count' );
                    delete_transient( WOO_CE_PREFIX . '_modules_active_count' );
                    delete_transient( WOO_CE_PREFIX . '_modules_inactive_count' );

                    // Refresh the count Transients.
                    woo_ce_modules_list();
                    woo_ce_refresh_active_export_plugins();

                    $url = add_query_arg(
                        array(
                            'action'   => null,
                            '_wpnonce' => null,
                        )
                    );
                    wp_safe_redirect( $url );
                    exit();
                }
                break;

            /**
             * Move legacy archives exports to the sed-exports directory within Uploads.
             */
            case 'relocate_archived_exports':
                // We need to verify the nonce.
                if ( ! empty( $_GET ) && check_admin_referer( 'woo_ce_relocate_archived_exports' ) ) {

                    // Create the sed-exports directory if it hasn't been.
                    woo_cd_create_secure_archives_dir();

                    $updated = 0;
                    $files   = woo_ce_get_archive_files();
                    if ( $files ) {
                        foreach ( $files as $key => $file ) {
                            $filepath = get_attached_file( $file->ID );
                            // Check for archived exports that have not been moved to sed-exports.
                            if ( strpos( $filepath, 'sed-exports' ) === false ) {
                                // Move the export.

                                // Update the Post meta key _wp_attached_file.
                                $attached_file = get_post_meta( $file->ID, '_wp_attached_file', true );
                                if ( ! empty( $attached_file ) ) {
                                    $attached_file = trailingslashit( 'sed-exports' ) . basename( $attached_file );
                                }
                                ++$updated;
                            }
                        }
                    }

                    // Show the response.
                    // translators: %d: number of relocated export files.
                    $message = sprintf( __( 'That\'s sorted, we\'ve relocated %d export files to the newly created <code>sed-exports</code> folder within the WordPress Uploads directory. Happy exporting!', 'woocommerce-exporter' ), $updated );
                    woo_cd_admin_notice_html( $message );
                }
                break;

            /**
             * Save skip overview preference.
             */
            case 'skip_overview':
                // We need to verify the nonce.
                if ( ! empty( $_POST ) && check_admin_referer( 'skip_overview', 'woo_ce_skip_overview' ) ) {
                    $skip_overview = false;
                    if ( isset( $_POST['skip_overview'] ) ) {
                        $skip_overview = 1;
                    }
                    // Remember that we've dismissed this notice.
                    woo_ce_update_option( 'skip_overview', $skip_overview );

                    if ( $skip_overview === 1 ) {
                        $url = add_query_arg(
                            array(
                                'tab'      => 'export',
                                '_wpnonce' => null,
                            )
                        );
                        wp_safe_redirect( $url );
                        exit();
                    }
                }
                break;
		}
    }

	/**
	 * Displays a HTML notice when a WordPress or Store Exporter error is encountered
	 */
	function woo_ce_admin_fail_notices() {

		$troubleshooting_url = 'https://www.visser.com.au/documentation/store-exporter-deluxe/troubleshooting/';

		// If the failed flag is set then prepare for an error notice.
		if ( isset( $_GET['failed'] ) ) {
			$message = '';
			if ( isset( $_GET['message'] ) ) {
				$message = sanitize_text_field( wp_unslash( $_GET['message'] ) );
			}
			if ( $message ) {
				// Translators: %s is the error message.
				$message  = sprintf( esc_html__( 'A WordPress or server error caused the export to fail, the exporter was provided with a reason: <em>%s</em>', 'woocommerce-exporter' ), esc_html( $message ) );
				$message .= ' (<a href="' . esc_url( $troubleshooting_url ) . '" target="_blank">' . esc_html__( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
			} else {
				$message  = esc_html__( 'A WordPress or server error caused the exporter to fail, no reason was provided, if this persists please get in touch so we can reproduce and resolve this with you.', 'woocommerce-exporter' );
				$message .= ' (<a href="' . esc_url( $troubleshooting_url ) . '" target="_blank">' . esc_html__( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
			}
			woo_cd_admin_notice_html( $message, 'error' );
		}

		// Check if WOO_CE_DEBUG is running.
		if ( defined( 'WOO_CE_DEBUG' ) && WOO_CE_DEBUG ) {
			$message  = esc_html__( 'Store Exporter Deluxe is currently in <attr title="WOO_CE_DEBUG">debugging mode</attr>. No export file downloads will be generated from the Quick Export screen nor will Scheduled Exports or manual CRON exports generate export files until debug mode is de-activated.', 'woocommerce-exporter' );
			$message .= ' (<a href="' . esc_url( $troubleshooting_url ) . '" target="_blank">' . esc_html__( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
			woo_cd_admin_notice_html( $message, 'error' );
		}

		// Displays a notice where the maximum execution time cannot be set.
		if ( ! woo_ce_get_option( 'dismiss_execution_time_prompt', 0 ) ) {
			$max_execution_time = ini_get( 'max_execution_time' );
			// Check if max_execution is unlimited.
			if ( false === $max_execution_time || 0 < $max_execution_time ) {
				$response = ( function_exists( 'ini_set' ) ? @ini_set( 'max_execution_time', 120 ) : false );
				if ( false === $response || ( $response !== absint( $max_execution_time ) ) ) {
					$dismiss_url = esc_url(
						add_query_arg(
							array(
								'action'   => 'dismiss_execution_time_prompt',
								'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_execution_time_prompt' ),
							)
						)
					);
					if ( false === $max_execution_time ) {
						$message  = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
						$message .= __( 'We could not detect or override the PHP configuration option <code>max_execution_time</code>, this is not an indicatative of an issue but may limit the size of large exports.', 'woocommerce-exporter' );
						$message .= __( ' If exports fail after a period of loading/inactivity then this limit has likely been reached and may need to be increased by contacting your hosting provider.', 'woocommerce-exporter' );
					} else {
						$message = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
						// Translators: %d is the max execution time in seconds.
						$message .= sprintf( __( 'We could not override the PHP configuration option <code>max_execution_time</code> which is set at %d seconds, this is typical of most hosting providers and is not an indicatation of an issue but may limit the size of large exports.', 'woocommerce-exporter' ), $max_execution_time );
						// Translators: %d is the max execution time in seconds.
						$message .= sprintf( __( ' If exports fail after %d seconds of loading/inactivity then this limit has likely been reached and may need to be increased by contacting your hosting provider.', 'woocommerce-exporter' ), $max_execution_time );
					}
					// Translators: %s is the troubleshooting URL.
					$message .= sprintf( __( ' See: <a href="%s" target="_blank">Increasing the PHP max_execution_time configuration option</a>', 'woocommerce-exporter' ), esc_url( $troubleshooting_url ) );
					woo_cd_admin_notice_html( wp_kses_post( $message ), 'notice' );
				}
			}
		}

		// Displays a notice where the memory allocated to WordPress falls below 64MB.
		if ( ! woo_ce_get_option( 'dismiss_memory_prompt', 0 ) ) {
			$memory_limit = ini_get( 'memory_limit' );
			// Check for unlimited memory.
			if ( '-1' !== $memory_limit ) {
				$memory_limit         = absint( $memory_limit );
				$minimum_memory_limit = 64;
				if ( $memory_limit < $minimum_memory_limit ) {
					$dismiss_url = esc_url(
						add_query_arg(
							array(
								'action'   => 'dismiss_memory_prompt',
								'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_memory_prompt' ),
							)
						)
					);
					$message     = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
					// Translators: %1$d is the recommended memory limit, %2$d is the current memory limit.
					$message .= sprintf( esc_html__( 'We recommend setting memory to at least %1$dMB, your site has only %2$dMB allocated to it.', 'woocommerce-exporter' ), $minimum_memory_limit, $memory_limit );
					$message .= esc_html__( 'See', 'woocommerce-exporter' ) . ': ' . sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $troubleshooting_url ), esc_html__( 'Increasing memory allocated to PHP', 'woocommerce-exporter' ) );
					woo_cd_admin_notice_html( $message, 'error' );
				}
			}
		}

		// Displays a notice where the memory allocated to WordPress is above 64MB but different to the php.ini configuration option.
		if ( ! woo_ce_get_option( 'dismiss_memory_sync_prompt', 0 ) ) {
			$memory_limit = ini_get( 'memory_limit' );
			// Check for unlimited memory.
			if ( '-1' !== $memory_limit ) {
				$wp_memory_limit     = WP_MEMORY_LIMIT;
				$wp_max_memory_limit = WP_MAX_MEMORY_LIMIT;
				if ( $wp_memory_limit !== $wp_max_memory_limit ) {
					$wp_memory_limit = WP_MAX_MEMORY_LIMIT;
				}
				if ( $wp_memory_limit > $memory_limit ) {
					$dismiss_url = esc_url(
						add_query_arg(
							array(
								'action'   => 'dismiss_memory_sync_prompt',
								'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_memory_sync_prompt' ),
							)
						)
					);
					$message     = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
					// Translators: %1$d is the recommended memory limit, %2$d is the current memory limit.
					$message .= sprintf( esc_html__( 'We recommend setting memory to at least %1$dMB, your site has the recommended %2$dMB allocated to it but PHP is showing different values when querying the WordPress Constant WP_MEMORY_LIMIT and comparing it to the memory_limit configuration option. This is not an indicatative of an issue but may limit the size of large exports.', 'woocommerce-exporter' ), $minimum_memory_limit, $memory_limit );
					$message .= ' ' . esc_html__( 'See', 'woocommerce-exporter' ) . ': ' . sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $troubleshooting_url ), esc_html__( 'Increasing memory allocated to PHP', 'woocommerce-exporter' ) );
					woo_cd_admin_notice_html( $message, 'error' );
				}
			}
		}

		// Displays a notice where the PHP open_basedir restriction is enabled.
		if ( ! woo_ce_get_option( 'dismiss_open_basedir_prompt', 0 ) ) {
			$open_basedir = ini_get( 'open_basedir' );
			if ( $open_basedir ) {
				$temp_dir          = sys_get_temp_dir();
				$override_temp_dir = apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() );
				// Check if the woo_ce_sys_get_temp_dir WordPress Filter has been used.
				$has_filter = false;
				$has_valid  = false;
				if ( $temp_dir !== $override_temp_dir ) {
					$has_filter = true;
					$temp_dir   = $override_temp_dir;
				}
				// Check if this is a Windows system.
				if ( 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) ) {
					$temp_dir     = str_replace( array( 'C:\\', 'D:\\' ), '../', $temp_dir );
					$temp_dir     = str_replace( '\\', '/', $temp_dir );
					$open_basedir = str_replace( array( 'C:\\', 'D:\\' ), '../', $open_basedir );
					$open_basedir = str_replace( '\\', '/', $open_basedir );
				}
				// Check if the sys_get_temp_dir() is within the open_basedir list.
				$open_basedir = explode( ':', $open_basedir );
				if ( is_array( $open_basedir ) ) {
					if ( ! empty( $open_basedir ) ) {
						foreach ( $open_basedir as $path ) {
							if ( false !== strstr( $path, $temp_dir ) ) {
								$has_valid = true;
								break;
							}
						}
						if ( $has_valid ) {
							// Show a notice to confirm that the temporary path exists and is writable.
							$dismiss_url = esc_url(
								add_query_arg(
									array(
										'action'   => 'dismiss_open_basedir_prompt',
										'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_open_basedir_prompt' ),
									)
								)
							);
							$message     = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
							// Translators: %1$s is the temp directory, %2$s is the troubleshooting URL.
							$message .= sprintf( esc_html__( 'The PHP open_basedir restriction is enabled for this WordPress site, ensure that you can save tempoary files to <code>%1$s</code>.<br /><br />If you experience corrupt exports read how to <a href="%2$s" target="_blank">override the default directory PHP stores temporary files for this Plugin</a>.', 'woocommerce-exporter' ), esc_html( $temp_dir ), esc_url( $troubleshooting_url . '#General_troubleshooting' ) );
							woo_cd_admin_notice_html( $message, 'notice' );
						} else {
							// Show a notice explaining what PHP open_basedir is and how to resolve it.
							$dismiss_url = esc_url(
								add_query_arg(
									array(
										'action'   => 'dismiss_open_basedir_prompt',
										'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_open_basedir_prompt' ),
									)
								)
							);
							$message     = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
							// Translators: %1$s is the temp directory, %2$s is the list of allowed directories, %3$s is the troubleshooting URL.
							$message .= sprintf( esc_html__( 'The PHP open_basedir restriction is enabled for this WordPress site but the directory <code>%1$s</code> is not in the allowed list of directories (<code>%2$s</code>).<br /><br />If you experience corrupt exports read how to <a href="%3$s" target="_blank">override the default directory PHP stores temporary files for this Plugin</a> or contact your hosting provider to resolve this.', 'woocommerce-exporter' ), esc_html( $temp_dir ), esc_html( implode( ', ', $open_basedir ) ), esc_url( $troubleshooting_url . '#General_troubleshooting' ) );
							woo_cd_admin_notice_html( $message, 'error' );
						}
					}
				}
				unset( $open_basedir, $override_temp_dir, $has_filter, $has_valid );
			}
		}

		// Displays a notice if PHP 5.2 or lower is installed.
		if ( ! woo_ce_get_option( 'dismiss_php_legacy', 0 ) ) {
			if ( version_compare( phpversion(), '5.3', '<' ) ) {
				$dismiss_url = esc_url(
					add_query_arg(
						array(
							'action'   => 'dismiss_php_legacy',
							'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_php_legacy' ),
						)
					)
				);
				$message     = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				// Translators: %1$s is the PHP version, %2$s is the troubleshooting URL.
				$message .= sprintf( esc_html__( 'Your PHP version (%1$s) is not supported and is very much out of date, since 2010 all users are strongly encouraged to upgrade to PHP 5.3+ and above. Contact your hosting provider to make this happen. See: <a href="%2$s" target="_blank">Migrating from PHP 5.2 to 5.3</a>', 'woocommerce-exporter' ), phpversion(), esc_url( $troubleshooting_url . '#General_troubleshooting' ) );
				woo_cd_admin_notice_html( $message, 'error' );
			}
		}

		// Displays a notice if Checkout Field Editor Pro is installed.
		if ( ! woo_ce_get_option( 'dismiss_wcfe_notice', 0 ) ) {
			if ( woo_ce_detect_export_plugin( 'checkout_field_editor_pro' ) ) {
				$dismiss_url  = esc_url(
					add_query_arg(
						array(
							'action'   => 'dismiss_wcfe_notice',
							'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_wcfe_notice' ),
						)
					)
				);
				$override_url = esc_url(
					add_query_arg(
						array(
							'page' => 'th_checkout_field_editor_pro',
							'tab'  => 'advanced_settings',
						),
						'admin.php'
					)
				);
				$message      = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message     .= esc_html__( 'If you would like to see custom Checkout fields created by WooCommerce Checkout Field Editor Plugin appear as exportable fields here on the Quick Export screen please open WooCommerce > Checkout Form > Advanced Settings and include the CSV export columns.', 'woocommerce-exporter' ) . '<br /><br /><a href="' . esc_url( $override_url ) . '" class="button-primary">' . esc_html__( 'Open Advanced Settings screen', 'woocommerce-exporter' ) . '</a>';
				woo_cd_admin_notice_html( $message, 'notice' );
			}
		}

		// Displays notice if there are more than 2500 Subscriptions.
		if ( ! woo_ce_get_option( 'dismiss_subscription_prompt', 0 ) ) {
			if ( class_exists( 'WC_Subscriptions' ) ) {
				$wcs_version = ( function_exists( 'woo_ce_get_wc_subscriptions_version' ) ? woo_ce_get_wc_subscriptions_version() : false );
				if ( version_compare( $wcs_version, '2.0.1', '<' ) ) {
					if ( method_exists( 'WC_Subscriptions', 'is_large_site' ) ) {
						// Does this store have roughly more than 3000 Subscriptions.
						if ( WC_Subscriptions::is_large_site() ) {
							$dismiss_url = esc_url(
								add_query_arg(
									array(
										'action'   => 'dismiss_subscription_prompt',
										'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_subscription_prompt' ),
									)
								)
							);
							$message     = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
							$message    .= esc_html__( 'We\'ve detected the <em>is_large_site</em> flag has been set within WooCommerce Subscriptions. Please get in touch if exports are incomplete as we need to spin up an alternative export process to export Subscriptions from large stores.', 'woocommerce-exporter' );
							$message    .= ' (<a href="' . esc_url( $troubleshooting_url ) . '" target="_blank">' . esc_html__( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
							woo_cd_admin_notice_html( $message, 'notice' );
						}
					}
				}
			}
		}

		// Displays notice if WooCommerce Checkout Add-Ons is installed.
		if ( ! woo_ce_get_option( 'dismiss_checkout_addons_prompt', 0 ) ) {
			if ( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
				$dismiss_url = esc_url(
					add_query_arg(
						array(
							'action'   => 'dismiss_checkout_addons_prompt',
							'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_checkout_addons_prompt' ),
						)
					)
				);
				$message     = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message    .= esc_html__( 'It looks like you have WooCommerce Checkout Add-Ons activated. To export the Checkout Add-on ID, Checkout Add-on Label and Checkout Add-on Value associated with Orders ensure the Fee Order Item Type is selected from Export Options.', 'woocommerce-exporter' );
				woo_cd_admin_notice_html( $message, 'notice' );
			}
		}

		// Display notice if Extra Product Options is installed.
		if ( ! woo_ce_get_option( 'dismiss_extra_product_options_scan_prompt', 0 ) ) {
			if ( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
				$dismiss_url = esc_url(
					add_query_arg(
						array(
							'action'   => 'dismiss_extra_product_options_scan_prompt',
							'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_extra_product_options_scan_prompt' ),
						)
					)
				);
				$message     = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message    .= esc_html__( 'It looks like you have WooCommerce Extra Product Options activated. Just a heads up there may be a significant increase in memory usage as we automatically scan existing Orders to populate the available Order export fields. More information about this functionality is available from the Docs &raquo; Store Exporter Deluxe &raquo; Usage page on our website.', 'woocommerce-exporter' );
				woo_cd_admin_notice_html( $message, 'notice' );
			}
		}

		// Display notice if Query Monitor is installed.
		if ( ! woo_ce_get_option( 'dismiss_query_monitor_prompt', 0 ) ) {
			if ( class_exists( 'QM_Plugin' ) ) {
				$dismiss_url = esc_url(
					add_query_arg(
						array(
							'action'   => 'dismiss_query_monitor_prompt',
							'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_query_monitor_prompt' ),
						)
					)
				);
				$message     = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message    .= esc_html__( 'It looks like you have Query Monitor activated. Just a heads up there may be a significant increase in memory usage and time to generate exports as Query Monitor logs all queries.', 'woocommerce-exporter' );
				woo_cd_admin_notice_html( $message, 'notice' );
			}
		}

		// If the export failed the WordPress Transient will still exist.
		if ( get_transient( WOO_CE_PREFIX . '_running' ) ) {
			// Check if a fatal PHP error has been detected.
			if ( get_transient( WOO_CE_PREFIX . '_crashed' ) ) {
				$message  = esc_html__( 'A WordPress or server error caused the exporter to fail with a blank screen, this is usually isolated to a memory or timeout issue, if this persists please get in touch so we can reproduce and resolve this.', 'woocommerce-exporter' );
				$message .= ' (<a href="' . esc_url( $troubleshooting_url ) . '" target="_blank">' . esc_html__( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
				woo_cd_admin_notice_html( $message, 'error' );
				delete_transient( WOO_CE_PREFIX . '_crashed' );
				delete_transient( WOO_CE_PREFIX . '_running' );
			} else {
				$dismiss_url = esc_url(
					add_query_arg(
						array(
							'action'   => 'dismiss_export_running_prompt',
							'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_export_running_prompt' ),
						)
					)
				);
				$message     = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message    .= esc_html__( 'It looks like an export is currently running in the background. Unfortunately we cannot tell if the background export has completed just that it hasn\'t yet finished. If you are confident there are no background exports running click Dismiss to hide this notice.', 'woocommerce-exporter' );
				$message    .= ' (<a href="' . esc_url( $troubleshooting_url ) . '" target="_blank">' . esc_html__( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
				woo_cd_admin_notice_html( $message, 'notice' );
			}
		}

		// If the woo_cd_exported WordPress Option exists then an Order export failed and we should roll back changes.
		$orders = get_option( WOO_CE_PREFIX . '_exported', false );
		if ( ! empty( $orders ) ) {
			if ( ! empty( $orders ) ) {
				foreach ( $orders as $order_id ) {
					// Fetch the Order object.
					$order = woo_ce_get_order_wc_data( $order_id );

					// Remove the export flag.
					$order->delete_meta_data( '_woo_cd_exported' );
					$order->save_meta_data();

					$order_flag_notes = woo_ce_get_option( 'order_flag_notes', 0 );
					if ( $order_flag_notes ) {
						// Add an additional Order Note.
						$note = esc_html__( 'Order export flag was cleared.', 'woocommerce-exporter' );
						if ( method_exists( $order, 'add_order_note' ) ) {
							$order->add_order_note( $note );
						}
						unset( $order );
					}
				}
			}
			unset( $order_id );
			delete_option( WOO_CE_PREFIX . '_exported' );
			$message  = esc_html__( 'It looks like a previous Orders export failed before it could complete, we have removed the exported flag assigned to those Orders so they are not excluded from your next export using <em>Filter Orders by Order Date</em> > <em>Since last export</em>.', 'woocommerce-exporter' );
			$message .= ' (<a href="' . esc_url( $troubleshooting_url ) . '" target="_blank">' . esc_html__( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
			woo_cd_admin_notice_html( $message );
		}
		unset( $orders );

		// Archives.
		if ( isset( $_GET['tab'] ) && 'archive' === sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {

			// Displays a notice if Archives is disabled and the Archives tab is opened.
			if (
				'1' === woo_ce_get_option( 'delete_file', '1' )
				&& ( ! woo_ce_get_option( 'dismiss_archives_prompt', 0 ) )
			) {
				$dismiss_url  = esc_url(
					add_query_arg(
						array(
							'action'   => 'dismiss_archives_prompt',
							'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_archives_prompt' ),
						)
					)
				);
				$override_url = esc_url(
					add_query_arg(
						array(
							'action'   => 'hide_archives_tab',
							'_wpnonce' => wp_create_nonce( 'woo_ce_hide_archives_tab' ),
						)
					)
				);
				$message      = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message     .= esc_html__( 'It looks like the saving of export archives is disabled from the Enabled Archives option on the Settings tab, would you like to hide the Archives tab as well?', 'woocommerce-exporter' ) . '<br /><br /><a href="' . esc_url( $override_url ) . '" class="button-primary">' . esc_html__( 'Hide Archives tab', 'woocommerce-exporter' ) . '</a>';
				woo_cd_admin_notice_html( $message, 'notice' );
			}
		}

		// Scheduled Exports.
		if ( isset( $_GET['tab'] ) && 'scheduled_export' === sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {

			// Displays a HTML notice if a Scheduled Export fails with a wp_mail() false response and the Scheduled Exports tab is opened.
			if (
				woo_ce_get_option( 'wp_mail_false_prompt', '0' )
				&& ( ! woo_ce_get_option( 'dismiss_wp_mail_false_prompt', 0 ) )
			) {
				$dismiss_url  = esc_url(
					add_query_arg(
						array(
							'action'   => 'dismiss_wp_mail_false_prompt',
							'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_wp_mail_false_prompt' ),
						)
					)
				);
				$override_url = esc_url(
					add_query_arg(
						array(
							'action'   => 'hide_wp_mail_false_prompt',
							'_wpnonce' => wp_create_nonce( 'woo_ce_hide_wp_mail_false_prompt' ),
						)
					)
				);
				$message      = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message     .= esc_html__( 'It looks like a recent scheduled export returned false when attempting to send an e-mail, you can override this response for future scheduled exports if the recipient received the e-mail as expected by clicking the Override mail response button.', 'woocommerce-exporter' ) . '<br /><br /><a href="' . esc_url( $override_url ) . '" class="button-primary">' . esc_html__( 'Override mail response', 'woocommerce-exporter' ) . '</a>';
				woo_cd_admin_notice_html( $message, 'notice' );
			}
		}

		// Displays a notice if Archives are detected without a Post Status of private.
		if ( woo_ce_get_unprotected_archives( array( 'count' => true ) ) && ! woo_ce_get_option( 'dismiss_archives_privacy_prompt', 0 ) ) {
			$dismiss_url  = esc_url(
				add_query_arg(
					array(
						'action'   => 'dismiss_archives_privacy_prompt',
						'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_archives_privacy_prompt' ),
					)
				)
			);
			$override_url = esc_url(
				add_query_arg(
					array(
						'action'   => 'override_archives_privacy',
						'_wpnonce' => wp_create_nonce( 'woo_ce_override_archives_privacy' ),
					)
				)
			);
			$message      = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
			$message     .= esc_html__( 'It looks like some archived exports require updating, would you like to update these archived exports now?', 'woocommerce-exporter' ) . '<br /><br /><a href="' . esc_url( $override_url ) . '" class="button-primary">' . esc_html__( 'Update export archives', 'woocommerce-exporter' ) . '</a>';
			woo_cd_admin_notice_html( $message, 'notice' );
		}

		// Displays a notice if this is WordPress MultiSite and the current User is the Network Admin.
		if ( ! woo_ce_get_option( 'dismiss_multisite_network_admin_prompt', 0 ) ) {
			if ( is_multisite() ) {
				if ( is_main_network( get_current_blog_id() ) ) {
					$dismiss_url = esc_url(
						add_query_arg(
							array(
								'action'   => 'dismiss_multisite_network_admin_prompt',
								'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_multisite_network_admin_prompt' ),
							)
						)
					);
					$message     = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
					$message    .= esc_html__( 'It looks like you are logged in as the Network Admin within your WordPress MultiSite. The Quick Export screen may load a little slower as the Export Types Count number checks all of your WordPress Sites.', 'woocommerce-exporter' );
					woo_cd_admin_notice_html( $message, 'notice' );
				}
			}
		}

		// Displays a notice if the ZipArchive Class is not detected.
		if ( ! woo_ce_get_option( 'dismiss_ziparchive_missing_prompt', 0 ) ) {
			if ( ! class_exists( 'ZipArchive' ) ) {
				$dismiss_url = esc_url(
					add_query_arg(
						array(
							'action'   => 'dismiss_ziparchive_missing_prompt',
							'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_ziparchive_missing_prompt' ),
						)
					)
				);
				$message     = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				/* translators: %s: Troubleshooting URL */
				$message .= sprintf( esc_html__( 'It looks like the PHP Class ZipArchive is missing, this means we are unable to generate Excel XLSX (Excel 97-2003) and some Excel XLS (Excel 2007-2013) files.<br /><br />Read how to <a href="%s" target="_blank">install the ZipArchive Class or use the alternate PCLZip PHP Class</a> or contact your hosting provider to resolve this.', 'woocommerce-exporter' ), esc_url( $troubleshooting_url . '#General_troubleshooting' ) );
				woo_cd_admin_notice_html( $message, 'error' );
			}
		}

		// Displays a notice if WooCommerce Checkout Field Editor Pro and export support is not enabled.
		if ( ! woo_ce_get_option( 'dismiss_checkout_field_editor_pro_prompt', 0 ) ) {
			if (
				woo_ce_detect_export_plugin( 'checkout_field_editor_pro' ) &&
				! class_exists( 'WCFE_Checkout_Fields_Export_Handler' )
			) {
				$dismiss_url = esc_url(
					add_query_arg(
						array(
							'action'   => 'dismiss_checkout_field_editor_pro_prompt',
							'_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_checkout_field_editor_pro_prompt' ),
						)
					)
				);
				$message     = '<span style="float:right;"><a href="' . esc_url( $dismiss_url ) . '" class="woocommerce-message-close notice-dismiss">' . esc_html__( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message    .= esc_html__( 'It looks like export support is not enabled within WooCommerce Checkout Field Editor Pro. To enable export integration open the WooCommerce » Checkout Form » Advanced Settings screen and select Enable CSV Export support then fill the CSV export columns field.', 'woocommerce-exporter' );
				woo_cd_admin_notice_html( $message, 'notice' );
			}
		}
	}

	/**
	 * Saves the state of Export fields for next export.
	 *
	 * @param string $export_type The export type.
	 * @param array  $fields      The fields to save.
	 * @param array  $sorting     The sorting to save.
     */
	function woo_ce_save_fields( $export_type = '', $fields = array(), $sorting = array() ) {

		// Default fields.
		if ( false === $fields && ! is_array( $fields ) ) {
			$fields = array();
		}
		if ( false === $sorting && ! is_array( $sorting ) ) {
			$sorting = array();
		}

		$export_types = array_keys( woo_ce_get_export_types() );
		if ( in_array( $export_type, $export_types, true ) && ! empty( $fields ) ) {
			woo_ce_update_option( $export_type . '_fields', array_map( 'sanitize_text_field', (array) $fields ) );
			woo_ce_update_option( $export_type . '_sorting', array_map( 'absint', (array) $sorting ) );
		}
	}

	/**
	 * Returns number of an Export type prior to export, used on Store Exporter screen.
	 *
	 * @param string $export_type The export type.
	 * @param array  $args        Additional arguments.
	 * @return int
	 */
	function woo_ce_get_export_type_count( $export_type = '', $args = array() ) {

		global $wpdb;

		$count_sql           = null;
		$woocommerce_version = woo_get_woo_version();

		switch ( $export_type ) {

			case 'product':
			case 'category':
			case 'tag':
			case 'order':
			case 'customer':
			case 'user':
			case 'review':
			case 'coupon':
			case 'shipping_class':
			case 'ticket':
			case 'attribute':
				$count = ( function_exists( 'woo_ce_get_export_type_' . $export_type . '_count' ) ? call_user_func( 'woo_ce_get_export_type_' . $export_type . '_count' ) : 0 );
				break;

			// Allow Plugin/Theme authors to populate their own custom export type counts.
			default:
				$count = 0;
				$count = apply_filters( 'woo_ce_get_export_type_count', $count, $export_type, $args );
				break;
		}
		if ( isset( $count ) || $count_sql ) {
			if ( isset( $count ) ) {
				if ( is_object( $count ) ) {
					$count = (array) $count;
					$count = absint( array_sum( $count ) );
				}
				return $count;
			} elseif ( $count_sql ) {
				$count = $wpdb->get_var( $wpdb->prepare( '%s', $count_sql ) );
			} else {
				$count = 0;
			}
			return $count;
		} else {
			return 0;
		}
	}
	/**
	 * In-line display of export file and export details when viewed via WordPress Media screen.
	 *
	 * @param WP_Post|false $post The post object or false.
	 */
	function woo_ce_read_export_file( $post = false ) {

		if ( empty( $post ) ) {
			if ( isset( $_GET['post'] ) ) {
				$post = get_post( absint( $_GET['post'] ) );
			}
		}

		if ( 'attachment' !== $post->post_type ) {
			return;
		}

		// Check if the Post matches one of our Post Mime Types.
		if ( ! in_array( $post->post_mime_type, array_values( woo_ce_get_mime_types() ), true ) ) {
			return;
		}

		$filepath = get_attached_file( $post->ID );

		// We can only read CSV, TSV, XML and JSON file types, the others are encoded.
		$allowed_post_mime_types = array(
			'text/csv',
			'text/tab-separated-values',
			'application/xml',
			'application/rss+xml',
			'application/json',
		);
		if ( in_array( $post->post_mime_type, $allowed_post_mime_types, true ) ) {

			$contents = __( 'No export entries were found, please try again with different export filters.', 'woocommerce-exporter' );
			if ( file_exists( $filepath ) ) {
				$response = wp_remote_get( $filepath );
				if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
					$contents = wp_remote_retrieve_body( $response );
				} else {
					$contents = '';
				}
			} else {
				// This resets the _wp_attached_file Post meta key to the correct value.
				update_attached_file( $post->ID, $post->guid );
				// Try grabbing the file contents again.
				$filepath = get_attached_file( $post->ID );
				if ( file_exists( $filepath ) ) {
					global $wp_filesystem;
					require_once ABSPATH . '/wp-admin/includes/file.php';
					WP_Filesystem();
					$contents = $wp_filesystem->get_contents( $filepath );
				}
			}
			if ( ! empty( $contents ) ) {
				include_once WOO_CE_PATH . 'templates/admin/media-csv_file.php';
			}
		}

		// We can still show the Export Details for any supported Post Mime Type.
		$export_type       = get_post_meta( $post->ID, '_woo_export_type', true );
		$columns           = get_post_meta( $post->ID, '_woo_columns', true );
		$rows              = get_post_meta( $post->ID, '_woo_rows', true );
		$scheduled_id      = get_post_meta( $post->ID, '_scheduled_id', true );
		$start_time        = get_post_meta( $post->ID, '_woo_start_time', true );
		$end_time          = get_post_meta( $post->ID, '_woo_end_time', true );
		$idle_memory_start = get_post_meta( $post->ID, '_woo_idle_memory_start', true );
		$data_memory_start = get_post_meta( $post->ID, '_woo_data_memory_start', true );
		$data_memory_end   = get_post_meta( $post->ID, '_woo_data_memory_end', true );
		$idle_memory_end   = get_post_meta( $post->ID, '_woo_idle_memory_end', true );

		include_once WOO_CE_PATH . 'templates/admin/media-export_details.php';
	}
	add_action( 'edit_form_after_editor', 'woo_ce_read_export_file' );

	/**
	 * Returns a list of archived exports.
	 *
	 * @return array
	 */
	function woo_ce_get_archive_files() {

		$post_type = 'attachment';
		$meta_key  = '_woo_export_type';
		$args      = array(
			'post_type'      => $post_type,
			'post_mime_type' => array_values( woo_ce_get_mime_types() ),
			'meta_key'       => $meta_key,
			'meta_value'     => null,
			'post_status'    => 'any',
			'posts_per_page' => -1,
		);
		if ( isset( $_GET['filter'] ) ) {
			$filter = sanitize_text_field( wp_unslash( $_GET['filter'] ) );
			if ( ! empty( $filter ) ) {
				$args['meta_value'] = $filter;
			}
		}
		$files = get_posts( $args );
		return $files;
	}

	/**
	 * Delete all archive files.
	 *
	 * @return bool
	 */
	function woo_ce_nuke_archive_files() {

		$post_type  = 'attachment';
		$meta_key   = '_woo_export_type';
		$args       = array(
			'post_type'      => $post_type,
			'post_mime_type' => array_values( woo_ce_get_mime_types() ),
			'meta_key'       => $meta_key,
			'meta_value'     => null,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);
		$post_query = new WP_Query( $args );
		if ( ! empty( $post_query->found_posts ) ) {
			foreach ( $post_query->posts as $post_ID ) {
				wp_delete_attachment( $post_ID, true );
			}
			return true;
		}
		return false;
	}

	/**
	 * Reset WP-CRON.
	 *
	 * @return bool True if cron was reset successfully, false otherwise.
	 */
	function woo_ce_nuke_cron() {
		if ( update_option( 'cron', '' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Delete all Scheduled Exports.
	 *
	 * @return bool True if exports were deleted, false otherwise.
	 */
	function woo_ce_nuke_scheduled_exports() {
		$scheduled_exports = woo_ce_get_scheduled_exports();
		if ( ! empty( $scheduled_exports ) ) {
			foreach ( $scheduled_exports as $post_ID ) {
				wp_delete_post( $post_ID, true );
			}
			return true;
		}
		return false;
	}

	/**
	 * Delete all WordPress Options generated by Store Exporter.
	 *
	 * @return bool True if all options were deleted, false otherwise.
	 */
	function woo_ce_nuke_options() {
		global $wpdb;

		$prefix = 'woo_ce_%';

		// Get a list of WordPress Options prefixed by woo_ce_.
		$options = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT `option_name` FROM `{$wpdb->prefix}options` WHERE `option_name` LIKE %s",
				$prefix
			)
		);

		if ( ! empty( $options ) ) {
			$count = 0;
			$size  = count( $options );
			foreach ( $options as $option ) {
				if ( delete_option( $option ) ) {
					++$count;
				}
			}
			// Compare the count of WordPress Options vs deleted WordPress Options.
			if ( $count === $size ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Reset all dismissed notices within Store Exporter.
	 */
	function woo_ce_nuke_dismissed_notices() {
		global $wpdb;

		$prefix = 'woo_ce_dismiss_%';

		// Get a list of WordPress Options prefixed by woo_ce_dismiss_.
		$options = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT `option_name` FROM `{$wpdb->prefix}options` WHERE `option_name` LIKE %s",
				$prefix
			)
		);

		if ( ! empty( $options ) ) {
			foreach ( $options as $option ) {
				delete_option( $option );
			}
		}
	}

	/**
	 * Returns a list of Attachments which are exposed to the public.
	 *
	 * @param array $postarr Additional query arguments.
	 * @return array|int Array of post IDs or count.
	 */
	function woo_ce_get_unprotected_archives( $postarr = array() ) {
		$post_type  = 'attachment';
		$meta_key   = '_woo_export_type';
		$args       = array(
			'post_type'      => $post_type,
			'post_mime_type' => array_values( woo_ce_get_mime_types() ),
			'meta_key'       => $meta_key,
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);
		$args       = wp_parse_args( $postarr, $args );
		$post_query = new WP_Query( $args );
		if ( ! empty( $post_query->found_posts ) ) {
			// Check if we are returning a count or list.
			if ( isset( $postarr['count'] ) ) {
				return $post_query->found_posts;
			}
			return $post_query->posts;
		}
		return array();
	}

	/**
	 * Update archives privacy.
	 *
	 * @return bool True if archives were updated, false otherwise.
	 */
	function woo_ce_update_archives_privacy() {
		$attachments = woo_ce_get_unprotected_archives();
		if ( ! empty( $attachments ) ) {
			foreach ( $attachments as $post_ID ) {
				$args = array(
					'ID'          => $post_ID,
					'post_status' => 'private',
				);
				wp_update_post( $args );
			}
			return true;
		}
		return false;
	}

	/**
	 * Returns an archived export with additional details.
	 *
	 * @param WP_Post $file The file post object.
	 * @return WP_Post The file post object with additional details.
	 */
	function woo_ce_get_archive_file( $file = '' ) {
		$upload_dir              = wp_upload_dir();
		$file->export_type       = get_post_meta( $file->ID, '_woo_export_type', true );
		$file->export_type_label = woo_ce_export_type_label( $file->export_type );
		if ( empty( $file->export_type ) ) {
			$file->export_type = __( 'Unassigned', 'woocommerce-exporter' );
		}
		if ( empty( $file->guid ) ) {
			$file->guid = $upload_dir['url'] . '/' . basename( $file->post_title );
		}
		$file->post_mime_type = get_post_mime_type( $file->ID );
		if ( ! $file->post_mime_type ) {
			$file->post_mime_type = __( 'N/A', 'woocommerce-exporter' );
		}
		$file->media_icon = wp_get_attachment_image( $file->ID, array( 80, 60 ), true );
		$author           = get_user_by( 'id', $file->post_author );
		if ( $author ) {
			$file->post_author_name = $author->display_name;
		}
		$file->post_date = woo_ce_format_archive_date( $file->ID );
		return $file;
	}

	/**
	 * HTML template for displaying the current export type filter on the Archives screen.
	 *
	 * @param string $current The current filter.
	 */
	function woo_ce_archives_quicklink_current( $current = '' ) {
		$output = '';
		if ( isset( $_GET['filter'] ) ) {
			$filter = sanitize_text_field( wp_unslash( $_GET['filter'] ) );
			if ( $filter === $current ) {
				$output = ' class="current"';
			}
		} elseif ( 'all' === $current ) {
			$output = ' class="current"';
		}
		echo wp_kses_post( $output );
	}

	/**
	 * HTML template for displaying the number of each export type filter on the Archives screen.
	 *
	 * @param string $type The export type.
	 * @return int The count of archives for the given type.
	 */
	function woo_ce_archives_quicklink_count( $type = '' ) {
		$post_type = 'attachment';
		$meta_key  = '_woo_export_type';
		$args      = array(
			'post_type'   => $post_type,
			'meta_key'    => $meta_key,
			'meta_value'  => null,
			'numberposts' => -1,
			'post_status' => 'any',
			'fields'      => 'ids',
		);
		if ( ! empty( $type ) ) {
			$args['meta_value'] = $type;
		}
		$post_query = new WP_Query( $args );
		return absint( $post_query->found_posts );
	}

	/**
	 * Display export fields summary text.
	 *
	 * @param string $type The export type.
	 */
	function woo_ce_export_fields_summary_text( $type = '' ) {
		$plural = false;
		$type   = woo_ce_export_type_label( $type, false, $plural );
		$output = sprintf(
			/* translators: %s: export type */
			esc_html__( 'Select the %s fields you would like to export, you can drag-and-drop to reorder export fields and change the label of export fields from the Configure link. Your field selection and supported export filters are saved for future exports.', 'woocommerce-exporter' ),
			esc_html( $type )
		);
		echo wp_kses_post( $output );
	}

}
/* End of: WordPress Administration */

/**
 * Raise export memory limit.
 *
 * @return string
 */
function woo_ce_raise_export_memory_limit() {
	// Check if WP_MAX_MEMORY_LIMIT is less than WP_MEMORY_LIMIT.
	if ( wp_convert_hr_to_bytes( WP_MAX_MEMORY_LIMIT ) < wp_convert_hr_to_bytes( WP_MEMORY_LIMIT ) ) {
		return WP_MEMORY_LIMIT;
	}
}

/**
 * Export process for CSV file.
 *
 * @param string $export_type Export type.
 * @param string $output      Output buffer.
 * @return string|bool
 */
function woo_ce_export_dataset( $export_type = null, &$output = null ) {
	global $export;

	if ( WOO_CE_LOGGING ) {
		woo_ce_error_log( sprintf( 'Debug: %s', 'functions.php - before woo_ce_export_dataset(): ' . ( time() - $export->start_time ) ) );
	}

	$separator             = $export->delimiter;
	$line_ending           = woo_ce_get_line_ending();
	$export->columns       = array();
	$export->total_rows    = 0;
	$export->total_columns = 0;

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

	if ( ! $export->cron && ! $export->scheduled_export ) {
		set_transient( WOO_CE_PREFIX . '_running', time(), woo_ce_get_option( 'timeout', HOUR_IN_SECONDS ) );
	}

	// Load up the fatal error notice if we 500 Internal Server Error (memory), hit a server timeout or encounter a fatal PHP error.
	add_action( 'shutdown', 'woo_ce_fatal_error' );

	// Drop in our content filters here.
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );
	add_filter( 'attribute_escape', 'woo_ce_filter_attribute_escape', 10, 2 );

	woo_ce_load_export_types();
	switch ( $export_type ) {
		// Products.
		case 'product':
			$fields = woo_ce_get_product_fields( 'summary' );
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				foreach ( $export->fields as $key => $field ) {
					$export->columns[] = woo_ce_get_product_field( $key );
				}
			}
			if ( $export->args['gallery_unique'] ) {
				$export->fields  = woo_ce_unique_product_gallery_fields( $export->fields );
				$export->columns = woo_ce_unique_product_gallery_columns( $export->columns, $export->fields );
			}
			$export->total_columns     = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_product', 'woo_ce_export_dataset_multisite_override_product', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_product', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_product', 'woo_ce_export_dataset_multisite_override_product' );
			} else {
				add_filter( 'woo_ce_export_dataset_product', 'woo_ce_export_dataset_override_product', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_product', $output, $export_type );
				// Field multi-level sorting.
				if ( 'csv' === $export->export_format || 'tsv' === $export->export_format || 'xls' === $export->export_format || 'xlsx' === $export->export_format ) {
					$output = woo_ce_multi_level_sorting( $output, $export );
				}
				remove_filter( 'woo_ce_export_dataset_product', 'woo_ce_export_dataset_override_product' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Categories.
		case 'category':
			$fields = woo_ce_get_category_fields( 'summary' );
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				foreach ( $export->fields as $key => $field ) {
					$export->columns[] = woo_ce_get_category_field( $key );
				}
			}
			$export->total_columns     = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_category', 'woo_ce_export_dataset_multisite_override_category', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_category', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_category', 'woo_ce_export_dataset_multisite_override_category' );
			} else {
				add_filter( 'woo_ce_export_dataset_category', 'woo_ce_export_dataset_override_category', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_category', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_category', 'woo_ce_export_dataset_override_category' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Tags.
		case 'tag':
			$fields = woo_ce_get_tag_fields( 'summary' );
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				foreach ( $export->fields as $key => $field ) {
					$export->columns[] = woo_ce_get_tag_field( $key );
				}
			}
			$export->total_columns     = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_tag', 'woo_ce_export_dataset_multisite_override_tag', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_tag', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_tag', 'woo_ce_export_dataset_multisite_override_tag' );
			} else {
				add_filter( 'woo_ce_export_dataset_tag', 'woo_ce_export_dataset_override_tag', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_tag', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_tag', 'woo_ce_export_dataset_override_tag' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Brands.
		case 'brand':
			$fields = woo_ce_get_brand_fields( 'summary' );
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				foreach ( $export->fields as $key => $field ) {
					$export->columns[] = woo_ce_get_brand_field( $key );
				}
			}
			$export->total_columns     = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_brand', 'woo_ce_export_dataset_multisite_override_brand', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_brand', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_brand', 'woo_ce_export_dataset_multisite_override_brand' );
			} else {
				add_filter( 'woo_ce_export_dataset_brand', 'woo_ce_export_dataset_override_brand', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_brand', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_brand', 'woo_ce_export_dataset_override_brand' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Orders.
		case 'order':
			$fields = woo_ce_get_order_fields( 'summary' );
			if ( WOO_CE_LOGGING ) {
				woo_ce_error_log( sprintf( 'Debug: %s', 'functions.php - woo_ce_export_dataset() > woo_ce_get_order_fields(): ' . ( time() - $export->start_time ) ) );
			}
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				if ( WOO_CE_LOGGING ) {
					woo_ce_error_log( sprintf( 'Debug: %s', 'array_intersect_assoc(): ' . ( time() - $export->start_time ) ) );
				}
				// Do not apply Field Editor changes to the unique Order Items Formatting rule.
				if ( 'unique' === $export->args['order_items'] ) {
					remove_filter( 'woo_ce_order_fields', 'woo_ce_override_order_field_labels', 11 );
				}
				$export->columns = woo_ce_get_order_field_array( $export->fields );
				if ( WOO_CE_LOGGING ) {
					woo_ce_error_log( sprintf( 'Debug: %s', 'foreach $export->fields: ' . ( time() - $export->start_time ) ) );
				}
				// Do not apply Field Editor changes to the unique Order Items Formatting rule.
				if ( 'unique' === $export->args['order_items'] ) {
					add_filter( 'woo_ce_order_fields', 'woo_ce_override_order_field_labels', 11 );
				}
			}
			// Check this is not an XML, RSS or JSON export format.
			if ( 'unique' === $export->args['order_items'] && ! in_array( $export->export_format, array( 'xml', 'rss', 'json' ), true ) ) {
				$export->fields = woo_ce_unique_order_item_fields( $export->fields, $export->args );
				if ( WOO_CE_LOGGING ) {
					woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_unique_order_item_fields(): ' . ( time() - $export->start_time ) ) );
				}
				$export->columns = woo_ce_unique_order_item_columns( $export->columns, $export->fields, $export->args );
				if ( WOO_CE_LOGGING ) {
					woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_unique_order_item_columns(): ' . ( time() - $export->start_time ) ) );
				}
			}
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( WOO_CE_LOGGING ) {
				woo_ce_error_log( sprintf( 'Debug: %s', 'before woo_ce_export_dataset_order: ' . ( time() - $export->start_time ) ) );
			}
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_order', 'woo_ce_export_dataset_multisite_override_order', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_order', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_order', 'woo_ce_export_dataset_multisite_override_order' );
			} else {
				add_filter( 'woo_ce_export_dataset_order', 'woo_ce_export_dataset_override_order', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_order', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_order', 'woo_ce_export_dataset_override_order' );
			}
			if ( WOO_CE_LOGGING ) {
				woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_export_dataset_order: ' . ( time() - $export->start_time ) ) );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Customers.
		case 'customer':
			$fields = woo_ce_get_customer_fields( 'summary' );
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				foreach ( $export->fields as $key => $field ) {
					$export->columns[] = woo_ce_get_customer_field( $key );
				}
			}
			$export->total_columns     = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_customer', 'woo_ce_export_dataset_multisite_override_customer', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_customer', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_customer', 'woo_ce_export_dataset_multisite_override_customer' );
			} else {
				add_filter( 'woo_ce_export_dataset_customer', 'woo_ce_export_dataset_override_customer', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_customer', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_customer', 'woo_ce_export_dataset_override_customer' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Users.
		case 'user':
			$fields = woo_ce_get_user_fields( 'summary' );
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				foreach ( $export->fields as $key => $field ) {
					$export->columns[] = woo_ce_get_user_field( $key );
				}
			}
			$export->total_columns     = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_user', 'woo_ce_export_dataset_multisite_override_user', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_user', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_user', 'woo_ce_export_dataset_multisite_override_user' );
			} else {
				add_filter( 'woo_ce_export_dataset_user', 'woo_ce_export_dataset_override_user', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_user', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_user', 'woo_ce_export_dataset_override_user' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Reviews.
		case 'review':
			$fields = woo_ce_get_review_fields( 'summary' );
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				foreach ( $export->fields as $key => $field ) {
					$export->columns[] = woo_ce_get_review_field( $key );
				}
			}
			$export->total_columns     = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_review', 'woo_ce_export_dataset_multisite_override_review', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_review', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_review', 'woo_ce_export_dataset_multisite_override_review' );
			} else {
				add_filter( 'woo_ce_export_dataset_review', 'woo_ce_export_dataset_override_review', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_review', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_review', 'woo_ce_export_dataset_override_review' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Coupons.
		case 'coupon':
			$fields = woo_ce_get_coupon_fields( 'summary' );
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				foreach ( $export->fields as $key => $field ) {
					$export->columns[] = woo_ce_get_coupon_field( $key );
				}
			}
			$export->total_columns     = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_coupon', 'woo_ce_export_dataset_multisite_override_coupon', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_coupon', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_coupon', 'woo_ce_export_dataset_multisite_override_coupon' );
			} else {
				add_filter( 'woo_ce_export_dataset_coupon', 'woo_ce_export_dataset_override_coupon', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_coupon', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_coupon', 'woo_ce_export_dataset_override_coupon' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Subscriptions.
		case 'subscription':
			$fields = woo_ce_get_subscription_fields( 'summary' );
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				foreach ( $export->fields as $key => $field ) {
					$export->columns[] = woo_ce_get_subscription_field( $key );
				}
			}
			$export->total_columns     = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_subscription', 'woo_ce_export_dataset_multisite_override_subscription', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_subscription', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_subscription', 'woo_ce_export_dataset_multisite_override_subscription' );
			} else {
				if ( WOO_CE_LOGGING ) {
					woo_ce_error_log( sprintf( 'Debug: %s', 'before woo_ce_export_dataset_subscription: ' . ( time() - $export->start_time ) ) );
				}
				add_filter( 'woo_ce_export_dataset_subscription', 'woo_ce_export_dataset_override_subscription', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_subscription', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_subscription', 'woo_ce_export_dataset_override_subscription', 10, 2 );
				if ( WOO_CE_LOGGING ) {
					woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_export_dataset_subscription: ' . ( time() - $export->start_time ) ) );
				}
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Product Vendors.
		case 'product_vendor':
			$fields = woo_ce_get_product_vendor_fields( 'summary' );
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				foreach ( $export->fields as $key => $field ) {
					$export->columns[] = woo_ce_get_product_vendor_field( $key );
				}
			}
			$export->total_columns     = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_product_vendor', 'woo_ce_export_dataset_multisite_override_product_vendor', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_product_vendor', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_product_vendor', 'woo_ce_export_dataset_multisite_override_product_vendor' );
			} else {
				add_filter( 'woo_ce_export_dataset_product_vendor', 'woo_ce_export_dataset_override_product_vendor', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_product_vendor', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_product_vendor', 'woo_ce_export_dataset_override_product_vendor' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Commissions.
		case 'commission':
			$fields = woo_ce_get_commission_fields( 'summary' );
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				foreach ( $export->fields as $key => $field ) {
					$export->columns[] = woo_ce_get_commission_field( $key );
				}
			}
			$export->total_columns     = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_commission', 'woo_ce_export_dataset_multisite_override_commission', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_commission', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_commission', 'woo_ce_export_dataset_multisite_override_commission' );
			} else {
				add_filter( 'woo_ce_export_dataset_commission', 'woo_ce_export_dataset_override_commission', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_commission', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_commission', 'woo_ce_export_dataset_override_commission' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Shipping Classes.
		case 'shipping_class':
			$fields = woo_ce_get_shipping_class_fields( 'summary' );
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				foreach ( $export->fields as $key => $field ) {
					$export->columns[] = woo_ce_get_shipping_class_field( $key );
				}
			}
			$export->total_columns     = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_shipping_class', 'woo_ce_export_dataset_multisite_override_shipping_class', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_shipping_class', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_shipping_class', 'woo_ce_export_dataset_multisite_override_shipping_class' );
			} else {
				add_filter( 'woo_ce_export_dataset_shipping_class', 'woo_ce_export_dataset_override_shipping_class', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_shipping_class', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_shipping_class', 'woo_ce_export_dataset_override_shipping_class' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Tickets.
		case 'ticket':
			$fields = woo_ce_get_ticket_fields( 'summary' );
			if ( $export->fields = array_intersect_assoc( (array) $export->fields, $fields ) ) {
				foreach ( $export->fields as $key => $field ) {
					$export->columns[] = woo_ce_get_ticket_field( $key );
				}
			}
			$export->total_columns     = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_ticket', 'woo_ce_export_dataset_multisite_override_ticket', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_ticket', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_ticket', 'woo_ce_export_dataset_multisite_override_ticket' );
			} else {
				add_filter( 'woo_ce_export_dataset_ticket', 'woo_ce_export_dataset_override_ticket', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_ticket', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_ticket', 'woo_ce_export_dataset_override_ticket' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

        // Bookings.
        case 'booking':
            $fields         = woo_ce_get_booking_fields( 'summary' );
            $export->fields = array_intersect_assoc( (array) $export->fields, $fields );
            if ( ! empty( $export->fields ) ) {
                foreach ( $export->fields as $key => $field ) {
                    $export->columns[] = woo_ce_get_booking_field( $key );
                }
            }
            $export->total_columns     = count( $export->columns );
            $export->data_memory_start = woo_ce_current_memory_usage();
            if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
                add_filter( 'woo_ce_export_dataset_multisite_booking', 'woo_ce_export_dataset_multisite_override_booking', 10, 2 );
                $output = apply_filters( 'woo_ce_export_dataset_multisite_booking', $output, $export_type );
                remove_filter( 'woo_ce_export_dataset_multisite_booking', 'woo_ce_export_dataset_multisite_override_booking' );
            } else {
                add_filter( 'woo_ce_export_dataset_booking', 'woo_ce_export_dataset_override_booking', 10, 2 );
                $output = apply_filters( 'woo_ce_export_dataset_booking', $output, $export_type );
                remove_filter( 'woo_ce_export_dataset_booking', 'woo_ce_export_dataset_override_booking' );
            }
            $export->data_memory_end = woo_ce_current_memory_usage();
            break;

        // Attributes.
        case 'attribute':
            $fields         = woo_ce_get_attribute_fields( 'summary' );
            $export->fields = array_intersect_assoc( (array) $export->fields, $fields );
            if ( ! empty( $export->fields ) ) {
                foreach ( $export->fields as $key => $field ) {
                    $export->columns[] = woo_ce_get_attribute_field( $key );
                }
            }
            $export->total_columns     = count( $export->columns );
            $export->data_memory_start = woo_ce_current_memory_usage();
            if ( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
                add_filter( 'woo_ce_export_dataset_multisite_attribute', 'woo_ce_export_dataset_multisite_override_attribute', 10, 2 );
                $output = apply_filters( 'woo_ce_export_dataset_multisite_attribute', $output, $export_type );
                remove_filter( 'woo_ce_export_dataset_multisite_attribute', 'woo_ce_export_dataset_multisite_override_attribute' );
            } else {
                add_filter( 'woo_ce_export_dataset_attribute', 'woo_ce_export_dataset_override_attribute', 10, 2 );
                $output = apply_filters( 'woo_ce_export_dataset_attribute', $output, $export_type );
                remove_filter( 'woo_ce_export_dataset_attribute', 'woo_ce_export_dataset_override_attribute' );
            }
            $export->data_memory_end = woo_ce_current_memory_usage();
            break;

        default:
            // Allow Plugin/Theme authors to add support for additional export types.
            do_action( 'woo_ce_export_dataset_custom_export_type', $export_type );
            $output = apply_filters( 'woo_ce_export_dataset_custom_export_type_output', $output, $export_type );
            break;
	}

	// Remove our content filters here to play nice with other Plugins.
	remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );
	remove_filter( 'attribute_escape', 'woo_ce_filter_attribute_escape' );

	// Remove our fatal error notice so not to conflict with the CRON or scheduled export engine.
	remove_action( 'shutdown', 'woo_ce_fatal_error' );

	// Export completed successfully.
	if ( ! $export->cron && ! $export->scheduled_export ) {
		delete_transient( WOO_CE_PREFIX . '_running' );
	}

	if ( WOO_CE_LOGGING ) {
		woo_ce_error_log( sprintf( 'Debug: %s', 'functions.php - after woo_ce_export_dataset(): ' . ( time() - $export->start_time ) ) );
	}

	// Check if we're using PHPExcel or generic export engine.
	if ( WOO_CE_DEBUG || in_array( $export->export_format, array( 'xml', 'rss', 'json' ), true ) ) {

		// Check that the export file is populated, export columns have been assigned and rows counted.
		if ( ! empty( $output ) && $export->total_rows && $export->total_columns ) {
			if ( WOO_CE_DEBUG && ! in_array( $export->export_format, apply_filters( 'woo_ce_phpexcel_supported_export_formats', array( 'csv', 'tsv', 'xls', 'xlsx' ) ), true ) && ( ! $export->cron && ! $export->scheduled_export ) ) {
				if ( in_array( $export->export_format, array( 'xml', 'rss', 'json' ), true ) ) {
					$output = woo_ce_format_xml( $output );
				}
				$response = set_transient( WOO_CE_PREFIX . '_debug_log', base64_encode( $output ), woo_ce_get_option( 'timeout', ( MINUTE_IN_SECONDS * 10 ) ) );
				if ( true !== $response ) {
					$message = __( 'The export contents were too large to store in a single WordPress transient, use the Volume offset / Limit volume options to reduce the size of your export and try again.', 'woocommerce-exporter' ) . ' (<a href="' . esc_url( $troubleshooting_url ) . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
					if ( function_exists( 'woo_cd_admin_notice' ) ) {
						woo_cd_admin_notice( $message, 'error' );
					} elseif ( WOO_CE_LOGGING ) {
						woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'functions.php - woo_ce_export_dataset() - %s', $message ) ) );
					}
					return;
				} else {
					return true;
				}
			} else {
				return $output;
			}
		}
	} else {
		return $output;
	}
}

/**
 * Handle fatal errors during export process.
 */
function woo_ce_fatal_error() {

	global $export;

	$troubleshooting_url = 'https://visser.com.au/knowledge-base/';

	$error = error_get_last();
	if ( null !== $error ) {
		set_transient( WOO_CE_PREFIX . '_crashed', 1, woo_ce_get_option( 'timeout', HOUR_IN_SECONDS ) );
		$message = '';

		/* translators: 1: Troubleshooting URL, 2: Error message, 3: Error file, 4: Error line */
		$notice = sprintf(
			__( 'Refer to the following error and if you continue to have problems see our <a href="%1$s" target="_blank">Usage and Troubleshooting documents</a> or contact us on <a href="http://www.visser.com.au/premium-support/" target="_blank">Support</a> for further assistance.<br /><br /><code>%2$s in %3$s on line %4$d</code>', 'woocommerce-exporter' ),
			esc_url( $troubleshooting_url ),
			esc_html( $error['message'] ),
			esc_html( $error['file'] ),
			intval( $error['line'] )
		);

		if ( 'Maximum execution time' === substr( $error['message'], 0, 22 ) ) {
			$message = __( 'The server\'s maximum execution time is too low to complete this export, use our batch export function - Limit Volume and Volume Offset under Export Options - to create smaller exports. This is commonly due to a low timeout limit set by your hosting provider or PHP Safe Mode being enabled. Consider increasing the timeout limit or reducing the size of your export.', 'woocommerce-exporter' );
		} elseif ( 'Allowed memory size' === substr( $error['message'], 0, 19 ) ) {
			$message = __( 'The server\'s maximum memory size is too low to complete this export, use our batch export function - Limit Volume and Volume Offset under Export Options - to create smaller exports. Consider increasing available memory to WordPress or reducing the size of your export.', 'woocommerce-exporter' );
		} elseif ( E_ERROR === $error['type'] ) {
			if ( "Class 'ZipArchive' not found" === substr( $error['message'], 0, 29 ) && ( false !== strstr( $error['file'], 'Excel2007.php' ) ) ) {
				/* translators: %s: Troubleshooting URL */
				$message = sprintf( __( 'A fatal PHP error was encountered during the export process, this was due to the server not loading the ZipArchive Class within the PHPExcel library. See our <a href="%s">Troubleshooting document</a> to learn how to switch to the PCLZip Class for XLS and XLSX exports.', 'woocommerce-exporter' ), esc_url( $troubleshooting_url ) );
			// Test if it's a WooCommerce 3.0 compatibility issue.
			} elseif ( 'Cannot access protected property WC_' === substr( $error['message'], 0, 36 ) ) {
				$message = __( 'A fatal PHP error was encountered during the export process, this was due to an undiscovered compatibility issue with WooCommerce 3.0. Please open a <a href="http://www.visser.com.au/premium-support/" target="_blank">Support ticket with us</a>.', 'woocommerce-exporter' );
			} else {
				$message = __( 'A fatal PHP error was encountered during the export process, we couldn\'t detect or diagnose it further.', 'woocommerce-exporter' );
			}
		}
		if ( ! empty( $message ) ) {

			// Save a record to the PHP error log.
			/* translators: 1: Error message, 2: PHP error message, 3: Error file, 4: Error line */
			woo_ce_error_log( sprintf( __( 'Fatal error: %1$s - PHP response: %2$s in %3$s on line %4$s', 'woocommerce-exporter' ), $message, $error['message'], $error['file'], $error['line'] ) );
			error_log( sprintf( __( 'Fatal error: %1$s - PHP response: %2$s in %3$s on line %4$s', 'woocommerce-exporter' ), $message, $error['message'], $error['file'], $error['line'] ) );

			// Only display the message if this is a manual export.
			if ( ( ! $export->cron && ! $export->scheduled_export ) ) {
				/* translators: 1: Error message, 2: Troubleshooting URL */
				$output = '<div id="message" class="error"><p>' . sprintf( __( '<strong>[store-exporter-deluxe]</strong> An unexpected error occurred. %s', 'woocommerce-exporter' ), $message . ' (<a href="' . esc_url( $troubleshooting_url ) . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)' ) . '</p><p>' . $notice . '</p></div>';
				echo wp_kses_post( $output );
			} elseif ( $export->scheduled_export ) {
				// Create a new Recent Scheduled Export entry for failed Scheduled Exports.
				woo_ce_add_recent_scheduled_export(
					absint( get_transient( WOO_CE_PREFIX . '_scheduled_export_id' ) ),
					false,
					false,
					sprintf( '%s - PHP response: %s in %s on line %s', $message, $error['message'], $error['file'], $error['line'] )
				);
				// Clear the Scheduled Export transient so other Scheduled Exports can run.
				delete_transient( WOO_CE_PREFIX . '_scheduled_export_id' );
			}
		}
	}
}

/**
 * List of Export types used on Store Exporter screen.
 *
 * @param bool $plural  Whether to return plural or singular labels.
 * @param bool $default Whether to return the default list or allow filtering.
 * @return array Array of export types and labels.
 */
function woo_ce_get_export_types( $plural = true, $default = false ) {

	$export_types = array(
		'product'        => ( $plural ? esc_html__( 'Products', 'woocommerce-exporter' ) : esc_html__( 'Product', 'woocommerce-exporter' ) ),
		'category'       => ( $plural ? esc_html__( 'Categories', 'woocommerce-exporter' ) : esc_html__( 'Category', 'woocommerce-exporter' ) ),
		'tag'            => ( $plural ? esc_html__( 'Tags', 'woocommerce-exporter' ) : esc_html__( 'Tag', 'woocommerce-exporter' ) ),
		'brand'          => ( $plural ? esc_html__( 'Brands', 'woocommerce-exporter' ) : esc_html__( 'Brand', 'woocommerce-exporter' ) ),
		'order'          => ( $plural ? esc_html__( 'Orders', 'woocommerce-exporter' ) : esc_html__( 'Order', 'woocommerce-exporter' ) ),
		'customer'       => ( $plural ? esc_html__( 'Customers', 'woocommerce-exporter' ) : esc_html__( 'Customer', 'woocommerce-exporter' ) ),
		'user'           => ( $plural ? esc_html__( 'Users', 'woocommerce-exporter' ) : esc_html__( 'User', 'woocommerce-exporter' ) ),
		'review'         => ( $plural ? esc_html__( 'Reviews', 'woocommerce-exporter' ) : esc_html__( 'Review', 'woocommerce-exporter' ) ),
		'coupon'         => ( $plural ? esc_html__( 'Coupons', 'woocommerce-exporter' ) : esc_html__( 'Coupon', 'woocommerce-exporter' ) ),
		'subscription'   => ( $plural ? esc_html__( 'Subscriptions', 'woocommerce-exporter' ) : esc_html__( 'Subscription', 'woocommerce-exporter' ) ),
		'product_vendor' => ( $plural ? esc_html__( 'Product Vendors', 'woocommerce-exporter' ) : esc_html__( 'Product Vendor', 'woocommerce-exporter' ) ),
		'commission'     => ( $plural ? esc_html__( 'Commissions', 'woocommerce-exporter' ) : esc_html__( 'Commission', 'woocommerce-exporter' ) ),
		'shipping_class' => ( $plural ? esc_html__( 'Shipping Classes', 'woocommerce-exporter' ) : esc_html__( 'Shipping Class', 'woocommerce-exporter' ) ),
		'ticket'         => ( $plural ? esc_html__( 'Tickets', 'woocommerce-exporter' ) : esc_html__( 'Ticket', 'woocommerce-exporter' ) ),
		'booking'        => ( $plural ? esc_html__( 'Bookings', 'woocommerce-exporter' ) : esc_html__( 'Booking', 'woocommerce-exporter' ) ),
		'attribute'      => ( $plural ? esc_html__( 'Attributes', 'woocommerce-exporter' ) : esc_html__( 'Attribute', 'woocommerce-exporter' ) ),
	);

	// Return the default list of export types.
	if ( $default ) {
		return $export_types;
	}

	// Allow Plugin/Theme authors to add custom export types as needed.
	return apply_filters( 'woo_ce_export_types', $export_types, $plural );
}

/**
 * Returns label of Export type slug used on Store Exporter screen.
 *
 * @param string $export_type Export type slug.
 * @param bool   $echo        Whether to echo or return the label.
 * @param bool   $plural      Whether to return plural or singular label.
 * @return string|void Export type label.
 */
function woo_ce_export_type_label( $export_type = '', $echo = false, $plural = true ) {

	$output = '';
	if ( ! empty( $export_type ) ) {
		$export_types = woo_ce_get_export_types( $plural );
		if ( array_key_exists( $export_type, $export_types ) ) {
			$output = $export_types[ $export_type ];
		}
	}
	if ( $echo ) {
		echo esc_html( $output );
	} else {
		return $output;
	}
}

/**
 * Get the label for a given export type.
 *
 * @param string $export_type Export type slug.
 * @param bool   $plural      Whether to return plural or singular label.
 * @return string Export type label.
 */
function woo_ce_get_export_type_label( $export_type = '', $plural = true ) {

	$output = $export_type;
	if ( ! empty( $export_type ) ) {
		$export_types = woo_ce_get_export_types( $plural );
		// Check our export type exists.
		$output = ( isset( $export_types[ $export_type ] ) ? $export_types[ $export_type ] : $output );
	}
	return $output;
}

/**
 * Generate file headers for export.
 *
 * @param string $post_mime_type MIME type of the export file.
 */
function woo_ce_generate_file_headers( $post_mime_type = 'text/csv' ) {

	global $export;

	header( sprintf( 'Content-Type: %s; charset=%s', esc_attr( $post_mime_type ), esc_attr( $export->encoding ) ) );
	header( sprintf( 'Content-Disposition: attachment; filename="%s"', esc_attr( $export->filename ) ) );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );
	header( sprintf( 'Content-Encoding: %s', esc_attr( $export->encoding ) ) );
}

/**
 * Generate filename of export file based on the Export type.
 *
 * @param string $export_type Export type.
 * @param string $override    Override filename.
 * @return string Generated filename.
 */
function woo_ce_generate_filename( $export_type = '', $override = '' ) {

	global $export;

	// Check if a fixed filename hasn't been provided.
	if ( ! empty( $override ) ) {
		$filename = $override;
	} else {
		// Get the filename from WordPress options.
		$filename = woo_ce_get_option( 'export_filename', '%store_name%-export_%dataset%-%date%-%time%-%random%' );
		// Check for empty filename.
		if ( empty( $filename ) ) {
			$filename = '%store_name%-export_%dataset%-%date%-%time%-%random%';
		}
		// Strip file extensions if present.
		$filename = str_replace( array( '.csv', '.tsv', '.txt', '.xls', '.xlsx', '.xml', '.rss', '.json' ), '', $filename );
	}

	// Switch out the Tags for filled values.
	$filename = str_replace( '%dataset%', $export_type, $filename );
	if ( strstr( $filename, '%date%' ) !== false ) {
		$date     = apply_filters( 'woo_ce_filename_tag_date', gmdate( 'Y_m_d', current_time( 'timestamp' ) ) );
		$filename = str_replace( '%date%', $date, $filename );
	}
	if ( strstr( $filename, '%year%' ) !== false ) {
		$year     = apply_filters( 'woo_ce_filename_tag_year', gmdate( 'Y', current_time( 'timestamp' ) ) );
		$filename = str_replace( '%year%', $year, $filename );
	}
	if ( strstr( $filename, '%month%' ) !== false ) {
		$month    = apply_filters( 'woo_ce_filename_tag_month', gmdate( 'm', current_time( 'timestamp' ) ) );
		$filename = str_replace( '%month%', $month, $filename );
	}
	if ( strstr( $filename, '%day%' ) !== false ) {
		$day      = apply_filters( 'woo_ce_filename_tag_day', gmdate( 'd', current_time( 'timestamp' ) ) );
		$filename = str_replace( '%day%', $day, $filename );
	}
	if ( strstr( $filename, '%time%' ) !== false ) {
		$time     = apply_filters( 'woo_ce_filename_tag_time', gmdate( 'H_i_s', current_time( 'timestamp' ) ) );
		$filename = str_replace( '%time%', $time, $filename );
	}
	if ( strstr( $filename, '%hour%' ) !== false ) {
		$hour     = apply_filters( 'woo_ce_filename_tag_hour', gmdate( 'H', current_time( 'timestamp' ) ) );
		$filename = str_replace( '%hour%', $hour, $filename );
	}
	if ( strstr( $filename, '%minute%' ) !== false ) {
		$minute   = apply_filters( 'woo_ce_filename_tag_minute', gmdate( 'i', current_time( 'timestamp' ) ) );
		$filename = str_replace( '%minute%', $minute, $filename );
	}
	if ( strstr( $filename, '%random%' ) !== false ) {
		$random   = wp_rand( 10000000, 99999999 );
		$filename = str_replace( '%random%', $random, $filename );
	}
	if ( strstr( $filename, '%store_name%' ) !== false ) {
		$store_name = sanitize_title( get_bloginfo( 'name' ) );
		$filename   = str_replace( '%store_name%', $store_name, $filename );
	}
	if ( strstr( $filename, '%order_id%' ) !== false ) {
		// Check if the Transient is set.
		$order_id = ( $export->scheduled_export ? absint( get_transient( WOO_CE_PREFIX . '_single_export_post_ids' ) ) : 0 );
		if ( ! empty( $order_id ) ) {
			$filename = str_replace( '%order_id%', $order_id, $filename );
		}
	}

	// Allow Plugin/Theme authors to add Tags to the filename as needed.
	$filename = apply_filters( 'woo_ce_generate_filename', $filename );

	return $filename;
}

/**
 * Returns the Post object of the export file saved as an attachment to the WordPress Media library.
 *
 * @param string $filename       Filename of the export.
 * @param string $post_mime_type MIME type of the export file.
 * @return int|WP_Error Post ID on success, WP_Error object on failure.
 */
function woo_ce_save_file_attachment( $filename = '', $post_mime_type = 'text/csv' ) {

	if ( ! empty( $filename ) ) {
		$post_type = 'woo-export';
		$args      = array(
			'post_status'    => 'private',
			'post_title'     => $filename,
			'post_type'      => $post_type,
			'post_mime_type' => $post_mime_type,
		);
		$post_ID   = wp_insert_attachment( $args, $filename );
		if ( is_wp_error( $post_ID ) ) {
			woo_ce_error_log( sprintf( 'Warning: %s', sprintf( 'save_file_attachment() - %s: %s', $filename, $post_ID->get_error_message() ) ) );
		} else {
			return $post_ID;
		}
	}
	return new WP_Error( 'empty_filename', __( 'Empty filename provided.', 'woocommerce-exporter' ) );
}

/**
 * Updates the GUID of the export file attachment to match the correct file URL.
 *
 * @param int    $post_ID     The post ID of the attachment.
 * @param string $export_type The type of export.
 * @param string $upload_url  The URL of the uploaded file.
 */
function woo_ce_save_file_guid( $post_ID, $export_type, $upload_url = '' ) {
	add_post_meta( $post_ID, '_woo_export_type', sanitize_text_field( $export_type ) );
	if ( ! empty( $upload_url ) ) {
		$args = array(
			'ID'   => absint( $post_ID ),
			'guid' => esc_url_raw( $upload_url ),
		);
		wp_update_post( $args );
	}
}

/**
 * Save critical export details against the archived export.
 *
 * @param int $post_ID The post ID of the attachment.
 */
function woo_ce_save_file_details( $post_ID ) {
	global $export;

	add_post_meta( $post_ID, '_woo_start_time', sanitize_text_field( $export->start_time ) );
	add_post_meta( $post_ID, '_woo_idle_memory_start', absint( $export->idle_memory_start ) );
	add_post_meta( $post_ID, '_woo_columns', absint( $export->total_columns ) );
	// Check if column headers are included.
	if ( $export->header_formatting && in_array( $export->export_format, apply_filters( 'woo_ce_phpexcel_supported_export_formats', array( 'csv', 'tsv', 'xls', 'xlsx' ) ), true ) ) {
		++$export->total_rows;
	}
	add_post_meta( $post_ID, '_woo_rows', absint( $export->total_rows ) );
	add_post_meta( $post_ID, '_woo_data_memory_start', absint( $export->data_memory_start ) );
	add_post_meta( $post_ID, '_woo_data_memory_end', absint( $export->data_memory_end ) );
}

/**
 * Update detail of existing archived export.
 *
 * @param int    $post_ID The post ID of the attachment.
 * @param string $detail  The detail key to update.
 * @param mixed  $value   The value to update.
 */
function woo_ce_update_file_detail( $post_ID, $detail, $value ) {
	if ( strpos( $detail, '_woo_' ) === 0 ) {
		update_post_meta( $post_ID, sanitize_key( $detail ), sanitize_text_field( $value ) );
	}
}

/**
 * Returns a list of allowed Export type statuses, can be overridden on a per-Export type basis.
 *
 * @param array $extra_status Additional statuses to include.
 * @param bool  $override     Whether to override the default statuses.
 * @return array List of allowed statuses.
 */
function woo_ce_post_statuses( $extra_status = array(), $override = false ) {
	$output = array(
		'publish',
		'pending',
		'draft',
		'future',
		'private',
		'trash',
	);
	if ( $override && ! empty( $extra_status ) ) {
		$output = $extra_status;
	} elseif ( $extra_status ) {
		$output = array_merge( $output, $extra_status );
	}
	return array_map( 'sanitize_key', $output );
}

/**
 * Get available export formats.
 *
 * @return array Array of export formats.
 */
function woo_ce_get_export_formats() {
	$export_formats = array(
		'csv'  => array(
			'title'       => __( 'CSV', 'woocommerce-exporter' ),
			'description' => __( 'Comma Separated Values', 'woocommerce-exporter' ),
		),
		'tsv'  => array(
			'title'       => __( 'TSV', 'woocommerce-exporter' ),
			'description' => __( 'Tab Separated Values', 'woocommerce-exporter' ),
		),
		'xls'  => array(
			'title'       => __( 'Excel (XLS)', 'woocommerce-exporter' ),
			'description' => __( 'Excel 97-2003', 'woocommerce-exporter' ),
			'disabled'    => true,
		),
		'xlsx' => array(
			'title'       => __( 'Excel (XLSX)', 'woocommerce-exporter' ),
			'description' => __( 'Excel 2007-2013', 'woocommerce-exporter' ),
			'disabled'    => true,
		),
		'xml'  => array(
			'title'       => __( 'XML', 'woocommerce-exporter' ),
			'description' => __( 'EXtensible Markup Language', 'woocommerce-exporter' ),
			'disabled'    => true,
		),
		'rss'  => array(
			'title'       => __( 'RSS 2.0', 'woocommerce-exporter' ),
			'description' => sprintf(
				// translators: %s: EXtensible Markup Language.
				__( '<attr title="%s">XML</attr> feed in RSS 2.0 format', 'woocommerce-exporter' ),
				__( 'EXtensible Markup Language', 'woocommerce-exporter' )
			),
			'disabled'    => true,
		),
		'json' => array(
			'title'       => __( 'JSON', 'woocommerce-exporter' ),
			'description' => sprintf(
				// translators: %s: JavaScript Object Notation.
				__( '<attr title="%s">JSON</attr> data format', 'woocommerce-exporter' ),
				__( 'JavaScript Object Notation', 'woocommerce-exporter' )
			),
			'disabled'    => true,
		),
	);

	// Allow Plugin/Theme authors to add support for additional export formats.
	return apply_filters( 'woo_ce_get_export_formats', $export_formats );
}

/**
 * Get the label for a given export format.
 *
 * @param string $export_format The export format key.
 * @return string|null The export format label or null if not found.
 */
function woo_ce_get_export_format_label( $export_format = '' ) {
	if ( empty( $export_format ) ) {
		return null;
	}

	$export_formats = woo_ce_get_export_formats();
	return isset( $export_formats[ $export_format ] ) ? $export_formats[ $export_format ]['title'] : $export_format;
}

/**
 * Get MIME types for export formats.
 *
 * @return array Array of MIME types keyed by export format.
 */
function woo_ce_get_mime_types() {

	$mime_types = array(
		'csv'  => 'text/csv',
		'tsv'  => 'text/tab-separated-values',
		'xls'  => 'application/vnd.ms-excel',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'xml'  => 'application/xml',
		'rss'  => 'application/rss+xml',
		'json' => 'application/json',
	);

	/**
	 * Filter the MIME types for export formats.
	 *
	 * @param array $mime_types Array of MIME types.
	 */
	return apply_filters( 'woo_ce_get_mime_types', $mime_types );
}

/**
 * Get MIME type or file extension.
 *
 * @param string $value      Value to search for.
 * @param string $search_by  Whether to search by 'extension' or 'mime_type'.
 * @return string|null       MIME type, file extension, or null if not found.
 */
function woo_ce_get_mime_type_extension( $value, $search_by = 'extension' ) {

	$mime_types = woo_ce_get_mime_types();
	if ( 'extension' === $search_by ) {
		return isset( $mime_types[ $value ] ) ? $mime_types[ $value ] : null;
	} elseif ( 'mime_type' === $search_by ) {
		$key = array_search( $value, $mime_types, true );
		return false !== $key ? strtoupper( $key ) : null;
	}
	return null;
}

/**
 * Add missing MIME types to WordPress allowed MIME types.
 *
 * @param array $mime_types Existing MIME types.
 * @return array Updated MIME types.
 */
function woo_ce_add_missing_mime_type( $mime_types = array() ) {

	$additional_types = array(
		'csv'  => 'text/csv',
		'tsv'  => 'text/tab-separated-values',
		'xls'  => 'application/vnd.ms-excel',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'xml'  => 'application/xml',
		'rss'  => 'application/rss+xml',
		'json' => 'application/json',
	);

	foreach ( $additional_types as $ext => $mime ) {
		if ( ! isset( $mime_types[ $ext ] ) ) {
			$mime_types[ $ext ] = $mime;
		}
	}

	/**
	 * Filter the updated MIME types.
	 *
	 * @param array $mime_types Updated MIME types.
	 */
	return apply_filters( 'woo_ce_add_missing_mime_type', $mime_types );
}
add_filter( 'upload_mimes', 'woo_ce_add_missing_mime_type' );

if ( ! function_exists( 'woo_ce_sort_fields' ) ) {
	/**
	 * Sort fields function.
	 *
	 * @param string $key Field key.
	 * @return string Sorted field key.
	 */
	function woo_ce_sort_fields( $key ) {
		return $key;
	}
}

/**
 * Get scheduled exports.
 *
 * @param array $args Query arguments.
 * @return array|bool Array of post IDs or false if none found.
 */
function woo_ce_get_scheduled_exports( $args = array() ) {

	$post_type = 'scheduled_export';
	$defaults  = array(
		'post_type'        => $post_type,
		'posts_per_page'   => -1,
		'fields'           => 'ids',
		'suppress_filters' => 1,
	);
	$args      = wp_parse_args( $args, $defaults );

	$exports_query = new WP_Query( $args );
	return $exports_query->have_posts() ? $exports_query->posts : false;
}

/**
 * Add a recent scheduled export to the list.
 *
 * @param int    $scheduled_export Scheduled export ID.
 * @param string $gui              GUI identifier.
 * @param int    $post_ID          Post ID.
 * @param string $error            Error message.
 */
function woo_ce_add_recent_scheduled_export( $scheduled_export = 0, $gui = '', $post_ID = 0, $error = '' ) {

	global $export;

	$recent_exports = woo_ce_get_option( 'recent_scheduled_exports', array() );

	if ( ! empty( $error ) || ! empty( $export->error ) ) {
		/**
		 * Action hook for failed scheduled exports.
		 *
		 * @param int    $scheduled_export Scheduled export ID.
		 * @param string $error            Error message.
		 */
		do_action( 'woo_ce_failed_scheduled_export', $scheduled_export, ( ! empty( $error ) ? $error : $export->error ) );
	}

	$recent_exports[] = array(
		'post_id'      => ( empty( $export->error ) ? absint( $post_ID ) : 0 ),
		'name'         => ( isset( $export->filename ) ? sanitize_text_field( $export->filename ) : false ),
		'date'         => time(),
		'method'       => sanitize_text_field( $gui ),
		'error'        => ( ! empty( $error ) ? sanitize_text_field( $error ) : sanitize_text_field( $export->error ) ),
		'scheduled_id' => absint( $scheduled_export ),
	);
	woo_ce_update_option( 'recent_scheduled_exports', $recent_exports );
}

/**
 * Convert legacy scheduled export WordPress Options to CPT.
 *
 * @return bool True on success, false on failure.
 */
function woo_ce_legacy_scheduled_export() {

	$post_type = 'scheduled_export';
	$args      = array(
		'post_type'      => $post_type,
		'post_date'      => current_time( 'mysql' ),
		'post_date_gmt'  => current_time( 'mysql', 1 ),
		'post_title'     => __( 'My scheduled export', 'woocommerce-exporter' ),
		'post_status'    => 'publish',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
		'post_content'   => '',
		'post_excerpt'   => '',
	);
	$post_ID   = wp_insert_post( $args );
	if ( ! is_wp_error( $post_ID ) ) {
		// Load WordPress Options for migration.
		$options_to_migrate = array(
			'auto_type'                       => '_export_type',
			'auto_format'                     => '_export_format',
			'auto_method'                     => '_export_method',
			'export_fields'                   => '_export_fields',
			'auto_product_category'           => '_filter_product_category',
			'auto_product_tag'                => '_filter_product_tag',
			'auto_product_status'             => '_filter_product_status',
			'auto_product_type'               => '_filter_product_type',
			'auto_product_stock'              => '_filter_product_stock',
			'auto_product_featured'           => '_filter_product_featured',
			'auto_product_shipping_class'     => '_filter_product_shipping_class',
			'auto_order_date'                 => '_filter_order_date',
			'auto_order_date_variable'        => '_filter_order_date_variable',
			'auto_order_date_variable_length' => '_filter_order_date_variable_length',
			'auto_order_dates_from'           => '_filter_order_dates_from',
			'auto_order_dates_to'             => '_filter_order_dates_to',
			'auto_order_status'               => '_filter_order_status',
			'auto_order_product'              => '_filter_order_product',
			'auto_order_billing_country'      => '_filter_order_billing_country',
			'auto_order_shipping_country'     => '_filter_order_shipping_country',
			'auto_order_payment'              => '_filter_order_payment',
			'auto_order_shipping'             => '_filter_order_shipping',
			'email_to'                        => '_method_email_to',
			'email_subject'                   => '_method_email_subject',
			'post_to'                         => '_method_post_to',
			'auto_ftp_method_host'            => '_method_ftp_host',
			'auto_ftp_method_port'            => '_method_ftp_port',
			'auto_ftp_method_protocol'        => '_method_ftp_protocol',
			'auto_ftp_method_user'            => '_method_ftp_user',
			'auto_ftp_method_pass'            => '_method_ftp_pass',
			'auto_ftp_method_path'            => '_method_ftp_path',
			'auto_ftp_method_filename'        => '_method_ftp_filename',
			'auto_ftp_method_passive'         => '_method_ftp_passive',
			'auto_ftp_method_timeout'         => '_method_ftp_timeout',
			'auto_schedule'                   => '_auto_schedule',
			'auto_interval'                   => '_auto_interval',
			'auto_commence'                   => '_auto_commence',
			'auto_commence_date'              => '_auto_commence_date',
		);

		foreach ( $options_to_migrate as $option_name => $meta_key ) {
			$option_value = woo_ce_get_option( $option_name, false );
			if ( false !== $option_value ) {
				update_post_meta( $post_ID, $meta_key, $option_value );
				delete_option( WOO_CE_PREFIX . '_' . $option_name );
			}
		}

		// Set default schedule if not set.
		$auto_schedule = get_post_meta( $post_ID, '_auto_schedule', true );
		if ( false === $auto_schedule ) {
			update_post_meta( $post_ID, '_auto_schedule', 'monthly' );
		}

		// Delete the legacy WP-CRON.
		wp_clear_scheduled_hook( 'woo_ce_auto_export_schedule' );

		return true;
	}

	return false;
}

/**
 * Register the Export Template custom post type.
 */
function woo_ce_register_export_template_cpt() {

	$labels = array(
		'name'               => __( 'Export Templates', 'woocommerce-exporter' ),
		'singular_name'      => __( 'Export Template', 'woocommerce-exporter' ),
		'add_new'            => __( 'Add Export Template', 'woocommerce-exporter' ),
		'add_new_item'       => __( 'Add New Export Template', 'woocommerce-exporter' ),
		'edit'               => __( 'Edit', 'woocommerce-exporter' ),
		'edit_item'          => __( 'Edit Export Template', 'woocommerce-exporter' ),
		'new_item'           => __( 'New Export Template', 'woocommerce-exporter' ),
		'view'               => __( 'View Export Template', 'woocommerce-exporter' ),
		'view_item'          => __( 'View Export Template', 'woocommerce-exporter' ),
		'search_items'       => __( 'Search Export Templates', 'woocommerce-exporter' ),
		'not_found'          => __( 'No Export Templates found', 'woocommerce-exporter' ),
		'not_found_in_trash' => __( 'No Export Templates found in trash', 'woocommerce-exporter' ),
		'parent'             => __( 'Parent Export Templates', 'woocommerce-exporter' ),
		'menu_name'          => _x( 'Export Templates', 'Export Template', 'woocommerce-exporter' ),
	);

	$args      = array(
		'labels'             => $labels,
		'description'        => __( 'This is where Export Templates for Store Exporter Deluxe are managed.', 'woocommerce-exporter' ),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => false,
		'query_var'          => true,
		'rewrite'            => false,
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'excerpt' ),
	);
	$post_type = 'export_template';
	register_post_type( $post_type, $args );
}

/**
 * Add Store Export to filter types on the WordPress Media screen.
 *
 * @param array $post_mime_types Existing post MIME types.
 * @return array Modified post MIME types.
 */
function woo_ce_add_post_mime_type( $post_mime_types = array() ) {

	// translators: %s: Number of Store Exports.
	$post_mime_types['text/csv'] = array(
		__( 'Store Exports (CSV)', 'woocommerce-exporter' ),
		__( 'Manage Store Exports (CSV)', 'woocommerce-exporter' ),
		_n_noop( 'Store Export - CSV <span class="count">(%s)</span>', 'Store Exports - CSV <span class="count">(%s)</span>' ),
	);

	// translators: %s: Number of Store Exports.
	$post_mime_types['application/vnd.ms-excel'] = array(
		__( 'Store Exports (Excel 2003)', 'woocommerce-exporter' ),
		__( 'Manage Store Exports (Excel 2003)', 'woocommerce-exporter' ),
		_n_noop( 'Store Export - Excel 2003 <span class="count">(%s)</span>', 'Store Exports - Excel 2003 <span class="count">(%s)</span>' ),
	);

	// translators: %s: Number of Store Exports.
	$post_mime_types['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'] = array(
		__( 'Store Exports (Excel 2007)', 'woocommerce-exporter' ),
		__( 'Manage Store Exports (Excel 2007)', 'woocommerce-exporter' ),
		_n_noop( 'Store Export - Excel 2007 <span class="count">(%s)</span>', 'Store Exports - Excel 2007 <span class="count">(%s)</span>' ),
	);

	// translators: %s: Number of Store Exports.
	$post_mime_types['application/xml'] = array(
		__( 'Store Exports (XML)', 'woocommerce-exporter' ),
		__( 'Manage Store Exports (XML)', 'woocommerce-exporter' ),
		_n_noop( 'Store Export - XML <span class="count">(%s)</span>', 'Store Exports - XML <span class="count">(%s)</span>' ),
	);

	// translators: %s: Number of Store Exports.
	$post_mime_types['application/rss+xml'] = array(
		__( 'Store Exports (RSS)', 'woocommerce-exporter' ),
		__( 'Manage Store Exports (RSS)', 'woocommerce-exporter' ),
		_n_noop( 'Store Export - RSS <span class="count">(%s)</span>', 'Store Exports - RSS <span class="count">(%s)</span>' ),
	);

	// translators: %s: Number of Store Exports.
	$post_mime_types['application/json'] = array(
		__( 'Store Exports (JSON)', 'woocommerce-exporter' ),
		__( 'Manage Store Exports (JSON)', 'woocommerce-exporter' ),
		_n_noop( 'Store Export - JSON <span class="count">(%s)</span>', 'Store Exports - JSON <span class="count">(%s)</span>' ),
	);
	return $post_mime_types;
}
add_filter( 'post_mime_types', 'woo_ce_add_post_mime_type' );

/**
 * Get current memory usage.
 *
 * @return string Memory usage in MB.
 */
function woo_ce_current_memory_usage() {

	$output = '';
	if ( function_exists( 'memory_get_usage' ) ) {
		$output = round( memory_get_usage( true ) / 1024 / 1024, 2 );
	}
	return $output;
}

/**
 * Get the start of week day.
 *
 * @return string Day name.
 */
function woo_ce_get_start_of_week_day() {

	global $wp_locale;

	$output        = 'Monday';
	$start_of_week = get_option( 'start_of_week', 0 );
	for ( $day_index = 0; $day_index <= 6; $day_index++ ) {
		if ( $start_of_week === $day_index ) {
			$output = $wp_locale->get_weekday( $day_index );
			break;
		}
	}
	return $output;
}

/**
 * Get visitor IP address.
 *
 * @return string IP address.
 */
function woo_ce_get_visitor_ip_address() {

	$ip = '';

	// Check for shared internet/ISP IP.
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) && woo_ce_validate_ip( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) ) ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );

    // Check for IPs passing through proxies.
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// Check if multiple IPs exist in var.
		$iplist = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
		foreach ( $iplist as $ip ) {
			if ( woo_ce_validate_ip( $ip ) ) {
				break;
			}
		}
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) && woo_ce_validate_ip( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	return apply_filters( 'woo_ce_get_visitor_ip_address', $ip );
}

/**
 * Validate an IP address.
 *
 * @param string $ip IP address.
 * @return bool True if valid, false if not.
 */
function woo_ce_validate_ip( $ip ) {
	return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false;
}
/**
 * Get line ending.
 *
 * @return string Line ending.
 */
function woo_ce_get_line_ending() {
	$output                 = PHP_EOL;
	$line_ending_formatting = woo_ce_get_option( 'line_ending_formatting', 'windows' );
	if ( false === $line_ending_formatting || '' === $line_ending_formatting ) {
		woo_ce_error_log( sprintf( 'Warning: %s', __( 'Line ending formatting export option was corrupted, defaulted to windows', 'woocommerce-exporter' ) ) );
		$line_ending_formatting = 'windows';
		woo_ce_update_option( 'line_ending_formatting', 'windows' );
	}
	switch ( $line_ending_formatting ) {
		case 'windows':
		default:
			$output = "\r\n";
			break;
		case 'mac':
			$output = "\r";
			break;
		case 'unix':
			$output = "\n";
			break;
	}
	return apply_filters( 'woo_ce_get_line_ending', $output, $line_ending_formatting );
}

/**
 * Get a list of allowed Image Embed fields.
 *
 * @return array List of allowed fields.
 */
function woo_ce_get_image_embed_allowed_fields() {
	global $export;

	$fields = array(
		'image_embed',
		'product_gallery_embed',
		'order_items_image_embed',
	);
	return apply_filters( 'woo_ce_override_embed_allowed_fields', $fields, $export->type );
}

/**
 * Get WPML language name.
 *
 * @param string $language_code Language code.
 * @return string Language name.
 */
function woo_ce_wpml_get_language_name( $language_code = '' ) {
	global $sitepress;

	$output = $language_code;
	if ( method_exists( $sitepress, 'get_language_details' ) ) {
		$details = $sitepress->get_language_details( $language_code );
		if ( ! empty( $details ) ) {
			$output = $details['english_name'];
		}
	}

	return $output;
}

/**
 * Check if WPML is active.
 *
 * @return bool True if WPML is active, false otherwise.
 */
function woo_ce_detect_wpml() {
	return defined( 'ICL_LANGUAGE_CODE' );
}

/**
 * Get the list of WordPress plugins that Store Exporter integrates with.
 *
 * @param bool $module_status Optional. Whether to include module status. Default is false.
 * @return array List of modules with their details.
 */
function woo_ce_modules_list( $module_status = false ) {

	$modules   = array();
	$modules[] = array(
		'name'        => 'aioseop',
		'title'       => __( 'All in One SEO Pack', 'woocommerce-exporter' ),
		'description' => __( 'Optimize your WooCommerce Products for Search Engines. Requires Store Toolkit for All in One SEO Pack integration.', 'woocommerce-exporter' ),
		'url'         => 'http://wordpress.org/extend/plugins/all-in-one-seo-pack/',
		'slug'        => 'all-in-one-seo-pack',
		'function'    => 'aioseop_activate',
	);
	$modules[] = array(
		'name'        => 'store_toolkit',
		'title'       => __( 'Store Toolkit', 'woocommerce-exporter' ),
		'description' => __( 'Store Toolkit includes a growing set of commonly-used WooCommerce administration tools aimed at web developers and store maintainers.', 'woocommerce-exporter' ),
		'url'         => 'http://wordpress.org/extend/plugins/woocommerce-store-toolkit/',
		'slug'        => 'woocommerce-store-toolkit',
		'function'    => 'woo_st_admin_init',
	);
	$modules[] = array(
		'name'        => 'ultimate_seo',
		'title'       => __( 'SEO Ultimate', 'woocommerce-exporter' ),
		'description' => __( 'This all-in-one SEO plugin gives you control over Product details.', 'woocommerce-exporter' ),
		'url'         => 'http://wordpress.org/extend/plugins/seo-ultimate/',
		'slug'        => 'seo-ultimate',
		'function'    => 'su_wp_incompat_notice',
	);
	$modules[] = array(
		'name'        => 'gpf',
		'title'       => __( 'WooCommerce Google Product Feed', 'woocommerce-exporter' ),
		'description' => __( 'Easily configure data to be added to your Google Merchant Centre feed.', 'woocommerce-exporter' ),
		'url'         => 'https://woocommerce.com/products/google-product-feed/',
		'function'    => 'woocommerce_gpf_install',
	);
	$modules[] = array(
		'name'        => 'wpseo',
		'title'       => __( 'Yoast SEO', 'woocommerce-exporter' ),
		'description' => __( 'The first true all-in-one SEO solution for WordPress. Formally WordPress SEO by Yoast.', 'woocommerce-exporter' ),
		'url'         => 'http://yoast.com/wordpress/seo/#utm_source=wpadmin&utm_medium=plugin&utm_campaign=wpseoplugin',
		'slug'        => 'wordpress-seo',
		'function'    => 'wpseo_admin_init',
	);
	$modules[] = array(
		'name'        => 'wpseo_wc',
		'title'       => __( 'Yoast SEO: WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'This extension to WooCommerce and WordPress SEO by Yoast makes sure there\'s perfect communication between the two plugins.', 'woocommerce-exporter' ),
		'url'         => 'https://yoast.com/wordpress/plugins/yoast-woocommerce-seo/',
		'function'    => 'initialize_yoast_woocommerce_seo',
	);
	$modules[] = array(
		'name'        => 'wc_msrp',
		'title'       => __( 'WooCommerce MSRP Pricing', 'woocommerce-exporter' ),
		'description' => __( 'Define and display MSRP prices (Manufacturer\'s suggested retail price) to your customers.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/msrp-pricing/',
		'function'    => 'woocommerce_msrp_activate',
	);
	$modules[] = array(
		'name'        => 'wc_brands',
		'title'       => __( 'WooCommerce Brands Addon', 'woocommerce-exporter' ),
		'description' => __( 'Create, assign and list brands for products, and allow customers to filter by brand.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/brands/',
		'class'       => 'WC_Brands',
	);
	$modules[] = array(
		'name'        => 'wc_cog',
		'title'       => __( 'Cost of Goods', 'woocommerce-exporter' ),
		'description' => __( 'Easily track total profit and cost of goods by adding a Cost of Good field to simple and variable products.', 'woocommerce-exporter' ),
		'url'         => 'http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/',
		'class'       => 'WC_COG',
	);
	$modules[] = array(
		'name'        => 'per_product_shipping',
		'title'       => __( 'Per Product Shipping', 'woocommerce-exporter' ),
		'description' => __( 'Define separate shipping costs per product which are combined at checkout to provide a total shipping cost.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/per-product-shipping/',
		'class'       => 'WC_Shipping_Per_Product_Init',
	);
	$modules[] = array(
		'name'        => 'vendors',
		'title'       => __( 'Product Vendors', 'woocommerce-exporter' ),
		'description' => __( 'Turn your store into a multi-vendor marketplace (such as Etsy or Creative Market).', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/product-vendors/',
		'class'       => 'WC_Product_Vendors',
	);
	$modules[] = array(
		'name'        => 'wc_vendors',
		'title'       => __( 'WC Vendors', 'woocommerce-exporter' ),
		'description' => __( 'Allow vendors to sell their own products and receive a commission for each sale.', 'woocommerce-exporter' ),
		'url'         => 'http://wcvendors.com',
		'class'       => 'WC_Vendors',
	);
	$modules[] = array(
		'name'        => 'wc_marketplace',
		'title'       => __( 'WC Marketplace', 'woocommerce-exporter' ),
		'description' => __( 'The most user recommended multi-vendor marketplace plugin for WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'https://wc-marketplace.com/',
		'function'    => 'wcmp_plugin_init',
	);
	$modules[] = array(
		'name'        => 'acf',
		'title'       => __( 'Advanced Custom Fields', 'woocommerce-exporter' ),
		'description' => __( 'Powerful fields for WordPress developers.', 'woocommerce-exporter' ),
		'url'         => 'http://www.advancedcustomfields.com',
		'class'       => 'acf',
	);
	$modules[] = array(
		'name'        => 'product_addons',
		'title'       => __( 'Product Add-ons', 'woocommerce-exporter' ),
		'description' => __( 'Allow your customers to customise your products by adding input boxes, dropdowns or a field set of checkboxes.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/product-add-ons/',
		'class'       => array( 'Product_Addon_Admin', 'Product_Addon_Display', 'WC_Product_Addons' ),
	);
	$modules[] = array(
		'name'        => 'seq',
		'title'       => __( 'WooCommerce Sequential Order Numbers', 'woocommerce-exporter' ),
		'description' => __( 'This plugin extends the WooCommerce e-commerce plugin by setting sequential order numbers for new orders.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-sequential-order-numbers/',
		'slug'        => 'woocommerce-sequential-order-numbers',
		'class'       => 'WC_Seq_Order_Number',
	);
	$modules[] = array(
		'name'        => 'seq_pro',
		'title'       => __( 'WooCommerce Sequential Order Numbers Pro', 'woocommerce-exporter' ),
		'description' => __( 'Tame your WooCommerce Order Numbers.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/sequential-order-numbers-pro/',
		'class'       => 'WC_Seq_Order_Number_Pro',
	);
	$modules[] = array(
		'name'        => 'print_invoice_delivery_note',
		'title'       => __( 'WooCommerce Print Invoice & Delivery Note', 'woocommerce-exporter' ),
		'description' => __( 'Print invoices and delivery notes for WooCommerce orders.', 'woocommerce-exporter' ),
		'url'         => 'http://wordpress.org/plugins/woocommerce-delivery-notes/',
		'slug'        => 'woocommerce-delivery-notes',
		'class'       => 'WooCommerce_Delivery_Notes',
	);
	$modules[] = array(
		'name'        => 'pdf_invoices_packing_slips',
		'title'       => __( 'WooCommerce PDF Invoices & Packing Slips', 'woocommerce-exporter' ),
		'description' => __( 'Create, print & automatically email PDF invoices & packing slips for WooCommerce orders.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/',
		'slug'        => 'woocommerce-pdf-invoices-packing-slips',
		'class'       => 'WooCommerce_PDF_Invoices',
	);
	$modules[] = array(
		'name'        => 'pdf_invoices_packing_slips_pro',
		'title'       => __( 'WooCommerce PDF Invoices & Packing Slips Professional', 'woocommerce-exporter' ),
		'description' => __( 'Extended functionality for the WooCommerce PDF Invoices & Packing Slips Plugin.', 'woocommerce-exporter' ),
		'url'         => 'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-professional/',
		'class'       => 'WooCommerce_PDF_IPS_Pro',
	);
	$modules[] = array(
		'name'        => 'checkout_manager',
		'title'       => __( 'WooCommerce Checkout Manager & WooCommerce Checkout Manager Pro', 'woocommerce-exporter' ),
		'description' => __( 'Manages the WooCommerce Checkout page and WooCommerce Checkout processes.', 'woocommerce-exporter' ),
		'url'         => 'http://wordpress.org/plugins/woocommerce-checkout-manager/',
		'slug'        => 'woocommerce-checkout-manager',
		'function'    => array( 'wccs_install', 'wooccm_install', 'wccs_install_pro' ),
		'class'       => 'WOOCCM',
	);
	$modules[] = array(
		'name'        => 'wc_pgsk',
		'title'       => __( 'Poor Guys Swiss Knife', 'woocommerce-exporter' ),
		'description' => __( 'A Swiss Knife for WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'http://wordpress.org/plugins/woocommerce-poor-guys-swiss-knife/',
		'slug'        => 'woocommerce-poor-guys-swiss-knife',
		'function'    => 'wcpgsk_init',
	);
	$modules[] = array(
		'name'        => 'checkout_field_editor',
		'title'       => __( 'Checkout Field Editor', 'woocommerce-exporter' ),
		'description' => __( 'Add, edit and remove fields shown on your WooCommerce checkout page.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/woocommerce-checkout-field-editor/',
		'function'    => 'woocommerce_init_checkout_field_editor',
	);
	$modules[] = array(
		'name'        => 'checkout_field_editor_pro',
		'title'       => __( 'Checkout Field Editor Pro', 'woocommerce-exporter' ),
		'description' => __( 'Design woocommerce checkout form in your own way, customize checkout fields (Add, Edit, Delete and re arrange fields).', 'woocommerce-exporter' ),
		'url'         => 'https://www.themehigh.com/product/woocommerce-checkout-field-editor-pro/',
		'function'    => 'run_thwcfe',
		'class'       => 'WCFE_Checkout_Field_Editor',
	);
	$modules[] = array(
		'name'        => 'checkout_field_manager',
		'title'       => __( 'Checkout Field Manager', 'woocommerce-exporter' ),
		'description' => __( 'Quickly and effortlessly add, remove and re-orders fields in the checkout process.', 'woocommerce-exporter' ),
		'url'         => 'http://61extensions.com/shop/woocommerce-checkout-field-manager/',
		'function'    => 'sod_woocommerce_checkout_manager_settings',
	);
	$modules[] = array(
		'name'        => 'checkout_addons',
		'title'       => __( 'WooCommerce Checkout Add-Ons', 'woocommerce-exporter' ),
		'description' => __( 'Add fields at checkout for add-on products and services while optionally setting a cost for each add-on.', 'woocommerce-exporter' ),
		'url'         => 'http://www.skyverge.com/product/woocommerce-checkout-add-ons/',
		'function'    => 'init_woocommerce_checkout_add_ons',
		'class'       => 'WC_Checkout_Add_Ons_Loader',
	);
	$modules[] = array(
		'name'        => 'local_pickup_plus',
		'title'       => __( 'Local Pickup Plus', 'woocommerce-exporter' ),
		'description' => __( 'Let customers pick up products from specific locations.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/local-pickup-plus/',
		'class'       => 'WC_Local_Pickup_Plus',
	);
	$modules[] = array(
		'name'        => 'gravity_forms',
		'title'       => __( 'Gravity Forms', 'woocommerce-exporter' ),
		'description' => __( 'Gravity Forms is hands down the best contact form plugin for WordPress powered websites.', 'woocommerce-exporter' ),
		'url'         => 'http://www.gravityforms.com/',
		'class'       => 'RGForms',
	);
	$modules[] = array(
		'name'        => 'woocommerce_gravity_forms',
		'title'       => __( 'WooCommerce Gravity Forms Product Add-Ons', 'woocommerce-exporter' ),
		'description' => __( 'Allows you to use Gravity Forms on individual WooCommerce products.', 'woocommerce-exporter' ),
		'url'         => 'https://www.woothemes.com/products/gravity-forms-add-ons/',
		'class'       => array( 'woocommerce_gravityforms', 'WC_GFPA_Main' ),
	);
	$modules[] = array(
		'name'        => 'currency_switcher',
		'title'       => __( 'WooCommerce Currency Switcher', 'woocommerce-exporter' ),
		'description' => __( 'Currency Switcher for WooCommerce allows your shop to display prices and accept payments in multiple currencies.', 'woocommerce-exporter' ),
		'url'         => 'http://aelia.co/shop/currency-switcher-woocommerce/',
		'class'       => 'WC_Aelia_CurrencySwitcher',
	);
	$modules[] = array(
		'name'        => 'subscriptions',
		'title'       => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' ),
		'description' => __( 'WC Subscriptions makes it easy to create and manage products with recurring payments.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/woocommerce-subscriptions/',
		'class'       => array( 'WC_Subscriptions', 'WC_Subscriptions_Manager' ),
	);
	$modules[] = array(
		'name'        => 'extra_product_options',
		'title'       => __( 'WooCommerce Extra Product Options', 'woocommerce-exporter' ),
		'description' => __( 'Create extra price fields globally or per-Product', 'woocommerce-exporter' ),
		'url'         => 'http://codecanyon.net/item/woocommerce-extra-product-options/7908619',
		'class'       => array( 'TM_Extra_Product_Options', 'Themecomplete_Extra_Product_Options_Setup' ),
	);
	$modules[] = array(
		'name'        => 'woocommerce_jetpack',
		'title'       => __( 'Booster for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Supercharge your WooCommerce site with these awesome powerful features (formally WooCommerce Jetpack).', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-jetpack/',
		'slug'        => 'woocommerce-jetpack',
		'class'       => 'WC_Jetpack',
	);
	$modules[] = array(
		'name'        => 'woocommerce_jetpack_plus',
		'title'       => __( 'Booster Plus', 'woocommerce-exporter' ),
		'description' => __( 'Unlock all WooCommerce Booster features and supercharge your WordPress WooCommerce site even more (formally WooCommerce Jetpack Plus).', 'woocommerce-exporter' ),
		'url'         => 'http://woojetpack.com/shop/wordpress-woocommerce-jetpack-plus/',
		'class'       => 'WC_Jetpack_Plus',
	);
	$modules[] = array(
		'name'        => 'woocommerce_brands',
		'title'       => __( 'WooCommerce Brands', 'woocommerce-exporter' ),
		'description' => __( 'Woocommerce Brands Plugin. After Install and active this plugin you\'ll have some shortcode and some widget for display your brands in fornt-end website.', 'woocommerce-exporter' ),
		'url'         => 'http://proword.net/Woocommerce_Brands/',
		'class'       => 'woo_brands',
	);
	$modules[] = array(
		'name'        => 'woocommerce_bookings',
		'title'       => __( 'WooCommerce Bookings', 'woocommerce-exporter' ),
		'description' => __( 'Setup bookable products such as for reservations, services and hires.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/woocommerce-bookings/',
		'class'       => 'WC_Bookings',
	);
	$modules[] = array(
		'name'        => 'eu_vat',
		'title'       => __( 'WooCommerce EU VAT Number', 'woocommerce-exporter' ),
		'description' => __( 'The EU VAT Number extension lets you collect and validate EU VAT numbers during checkout to identify B2B transactions verses B2C.', 'woocommerce-exporter' ),
		'url'         => 'https://www.woothemes.com/products/eu-vat-number/',
		'function'    => '__wc_eu_vat_number_init',
		'class'       => 'WC_EU_VAT_Number_Init',
	);
	$modules[] = array(
		'name'        => 'aelia_eu_vat',
		'title'       => __( 'WooCommerce EU VAT Assistant', 'woocommerce-exporter' ),
		'description' => __( 'Assists with EU VAT compliance, for the new VAT regime beginning 1st January 2015.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-eu-vat-assistant/',
		'slug'        => 'woocommerce-eu-vat-assistant',
		'class'       => array( 'WC_Aelia_EU_VAT_Assistant' ),
		'global'      => 'wc-aelia-eu-vat-assistant',
	);
	$modules[] = array(
		'name'        => 'hear_about_us',
		'title'       => __( 'WooCommerce Hear About Us', 'woocommerce-exporter' ),
		'description' => __( 'Ask where your new customers come from at Checkout.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-hear-about-us/',
		'slug'        => 'woocommerce-hear-about-us', // Define this if the Plugin is hosted on the WordPress repo
		'class'       => 'WooCommerce_HearAboutUs',
	);
	$modules[] = array(
		'name'        => 'wholesale_pricing',
		'title'       => __( 'WooCommerce Wholesale Pricing', 'woocommerce-exporter' ),
		'description' => __( 'Allows you to set wholesale prices for products and variations.', 'woocommerce-exporter' ),
		'url'         => 'http://ignitewoo.com/woocommerce-extensions-plugins-themes/woocommerce-wholesale-pricing/',
		'class'       => 'woocommerce_wholesale_pricing',
	);
	$modules[] = array(
		'name'        => 'wc_barcodes',
		'title'       => __( 'Barcodes for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Allows you to add GTIN (former EAN) codes natively to your products.', 'woocommerce-exporter' ),
		'url'         => 'http://www.wolkenkraft.com/produkte/barcodes-fuer-woocommerce/',
		'function'    => 'wpps_requirements_met',
	);
	$modules[] = array(
		'name'        => 'wc_smart_coupons',
		'title'       => __( 'WooCommerce Smart Coupons', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Smart Coupons lets customers buy gift certificates, store credits or coupons easily.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/smart-coupons/',
		'class'       => 'WC_Smart_Coupons',
	);
	$modules[] = array(
		'name'        => 'wc_preorders',
		'title'       => __( 'WooCommerce Pre-Orders', 'woocommerce-exporter' ),
		'description' => __( 'Sell pre-orders for products in your WooCommerce store.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/woocommerce-pre-orders/',
		'class'       => 'WC_Pre_Orders',
	);
	$modules[] = array(
		'name'        => 'order_numbers_basic',
		'title'       => __( 'WooCommerce Basic Ordernumbers', 'woocommerce-exporter' ),
		'description' => __( 'Lets the user freely configure the order numbers in WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'http://open-tools.net/woocommerce/advanced-ordernumbers-for-woocommerce.html',
		'class'       => 'OpenToolsOrdernumbersBasic',
	);
	$modules[] = array(
		'name'        => 'admin_custom_order_fields',
		'title'       => __( 'WooCommerce Admin Custom Order Fields', 'woocommerce-exporter' ),
		'description' => __( 'Easily add custom fields to your WooCommerce orders and display them in the Orders admin, the My Orders section and order emails.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/woocommerce-admin-custom-order-fields/',
		'function'    => 'init_woocommerce_admin_custom_order_fields',
	);
	$modules[] = array(
		'name'        => 'table_rate_shipping_plus',
		'title'       => __( 'WooCommerce Table Rate Shipping Plus', 'woocommerce-exporter' ),
		'description' => __( 'Calculate shipping costs based on destination, weight and price.', 'woocommerce-exporter' ),
		'url'         => 'http://mangohour.com/plugins/woocommerce-table-rate-shipping',
		'function'    => 'mh_wc_table_rate_plus_init',
	);
	$modules[] = array(
		'name'        => 'wc_extra_checkout_fields_brazil',
		'title'       => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' ),
		'description' => __( 'Adds Brazilian checkout fields in WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/',
		'slug'        => 'woocommerce-extra-checkout-fields-for-brazil',
		'class'       => 'Extra_Checkout_Fields_For_Brazil',
	);
	$modules[] = array(
		'name'        => 'wc_quickdonation',
		'title'       => __( 'WooCommerce Quick Donation', 'woocommerce-exporter' ),
		'description' => __( 'Turns WooCommerce into online donation.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-quick-donation/',
		'slug'        => 'woocommerce-quick-donation',
		'class'       => 'WooCommerce_Quick_Donation',
	);
	$modules[] = array(
		'name'        => 'wc_easycheckout',
		'title'       => __( 'Easy Checkout Fields Editor', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Easy Checkout Fields Editor.', 'woocommerce-exporter' ),
		'url'         => 'http://codecanyon.net/item/woocommerce-easy-checkout-field-editor/9799777',
		'function'    => 'pcmfe_admin_form_field',
	);
	$modules[] = array(
		'name'        => 'wc_productfees',
		'title'       => __( 'Product Fees', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Product Fees allows you to add additional fees at checkout based on products that are in the cart.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-product-fees/',
		'slug'        => 'woocommerce-product-fees',
		'class'       => 'WooCommerce_Product_Fees',
	);
	$modules[] = array(
		'name'        => 'fooevents',
		'title'       => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Adds event and ticketing features to WooCommerce, formally FooEvents for WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woocommerceevents.com/',
		'class'       => array( 'WooCommerce_Events', 'FooEvents' ),
	);
	$modules[] = array(
		'name'        => 'wc_tabmanager',
		'title'       => __( 'WooCommerce Tab Manager', 'woocommerce-exporter' ),
		'description' => __( 'A product tab manager for WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/woocommerce-tab-manager/',
		'class'       => 'WC_Tab_Manager',
	);
	$modules[] = array(
		'name'        => 'wc_customfields',
		'title'       => __( 'WooCommerce Custom Fields', 'woocommerce-exporter' ),
		'description' => __( 'Create custom fields for WooCommerce product, checkout, order and customer pages.', 'woocommerce-exporter' ),
		'url'         => 'http://www.rightpress.net/woocommerce-custom-fields',
		'class'       => array( 'RP_WCCF', 'WCCF' ),
	);
	$modules[] = array(
		'name'        => 'barcode_isbn',
		'title'       => __( 'WooCommerce Barcode & ISBN', 'woocommerce-exporter' ),
		'description' => __( 'A plugin to add a barcode & ISBN to WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-barcode-isbn/',
		'slug'        => 'woocommerce-barcode-isbn',
		'function'    => 'woo_add_barcode',
	);
	$modules[] = array(
		'name'        => 'woo_add_gtin',
		'title'       => __( 'WooCommerce UPC, EAN, and ISBN', 'woocommerce-exporter' ),
		'description' => __( 'Add GTIN codes like UPC, EAN, and ISBN to your WooCommerce Products.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woo-add-gtin/',
		'slug'        => 'woo-add-gtin',
		'class'       => 'Woo_GTIN',
	);
	$modules[] = array(
		'name'        => 'video_product_tab',
		'title'       => __( 'WooCommerce Video Product Tab', 'woocommerce-exporter' ),
		'description' => __( 'Extends WooCommerce to allow you to add a Video to the Product page.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-video-product-tab/',
		'slug'        => 'woocommerce-video-product-tab',
		'class'       => 'WooCommerce_Video_Product_Tab',
	);
	$modules[] = array(
		'name'        => 'external_featured_image',
		'title'       => __( 'Nelio External Featured Image', 'woocommerce-exporter' ),
		'description' => __( 'Use external images from anywhere as the featured image of your pages and posts.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/external-featured-image/',
		'slug'        => 'external-featured-image', // Define this if the Plugin is hosted on the WordPress repo
		'function'    => '_nelioefi_url',
	);
	$modules[] = array(
		'name'        => 'variation_swatches_photos',
		'title'       => __( 'WooCommerce Variation Swatches and Photos', 'woocommerce-exporter' ),
		'description' => __( 'Configure colors and photos for shoppers on your site to use when picking variations.', 'woocommerce-exporter' ),
		'url'         => 'https://www.woothemes.com/products/variation-swatches-and-photos/',
		'class'       => 'WC_SwatchesPlugin',
	);
	$modules[] = array(
		'name'        => 'wc_uploads',
		'title'       => __( 'WooCommerce Uploads', 'woocommerce-exporter' ),
		'description' => __( 'Upload files in WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'https://wpfortune.com/shop/plugins/woocommerce-uploads/',
		'class'       => 'WPF_Uploads',
	);
	$modules[] = array(
		'name'        => 'wc_posr',
		'title'       => __( 'WooCommerce Profit of Sales Report', 'woocommerce-exporter' ),
		'description' => __( 'This plugin provides Profit of Sales Report based on Cost of Goods.', 'woocommerce-exporter' ),
		'url'         => 'http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590',
		'function'    => 'POSRFront',
	);
	$modules[] = array(
		'name'        => 'orddd_free',
		'title'       => __( 'Order Delivery Date for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Allow the customers to choose an order delivery date on the checkout page for WooCommerce store owners.', 'woocommerce-exporter' ),
		'slug'        => 'order-delivery-date-for-woocommerce',
		'url'         => 'https://wordpress.org/plugins/order-delivery-date-for-woocommerce/',
		'class'       => 'order_delivery_date_lite',
	);
	$modules[] = array(
		'name'        => 'orddd',
		'title'       => __( 'Order Delivery Date Pro for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Allows customers to choose their preferred Order Delivery Date & Delivery Time during checkout.', 'woocommerce-exporter' ),
		'url'         => 'https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/',
		'class'       => 'order_delivery_date',
	);
	$modules[] = array(
		'name'        => 'wc_eu_vat_compliance',
		'title'       => __( 'WooCommerce EU VAT Compliance', 'woocommerce-exporter' ),
		'description' => __( 'Provides features to assist WooCommerce with EU VAT compliance.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-eu-vat-compliance/',
		'slug'        => 'woocommerce-eu-vat-compliance',
		'class'       => 'WC_EU_VAT_Compliance',
	);
	$modules[] = array(
		'name'        => 'wc_eu_vat_compliance_pro',
		'title'       => __( 'WooCommerce EU VAT Compliance (Premium)', 'woocommerce-exporter' ),
		'description' => __( 'Provides features to assist WooCommerce with EU VAT compliance.', 'woocommerce-exporter' ),
		'url'         => 'https://www.simbahosting.co.uk/s3/product/woocommerce-eu-vat-compliance/',
		'slug'        => 'woocommerce-eu-vat-compliance',
		'class'       => 'WC_EU_VAT_Compliance_Premium',
	);
	$modules[] = array(
		'name'        => 'yith_cm',
		'title'       => __( 'YITH WooCommerce Checkout Manager', 'woocommerce-exporter' ),
		'description' => __( 'YITH WooCommerce Checkout Manager lets you add, edit or remove checkout fields.', 'woocommerce-exporter' ),
		'url'         => 'https://yithemes.com/themes/plugins/yith-woocommerce-checkout-manager/',
		'function'    => 'ywccp_init',
	);
	$modules[] = array(
		'name'        => 'vt_dp',
		'title'       => __( 'Discontinued Product for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Adds the ability to flag a product as discontinued to WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/discontinued-product-for-woocommerce/',
		'slug'        => 'discontinued-product-for-woocommerce',
		'function'    => 'discontinued_product_for_woocommerce_init',
	);
	$modules[] = array(
		'name'        => 'yith_vendor',
		'title'       => __( 'YITH WooCommerce Multi Vendor Premium', 'woocommerce-exporter' ),
		'description' => __( 'Switch your website into a platform hosting more than one shop.', 'woocommerce-exporter' ),
		'url'         => 'http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/',
		'function'    => 'YITH_Vendors',
	);
	$modules[] = array(
		'name'        => 'wc_memberships',
		'title'       => __( 'WooCommerce Memberships', 'woocommerce-exporter' ),
		'description' => __( 'Sell memberships that provide access to restricted content, products, discounts, and more!', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/woocommerce-memberships/',
		'function'    => 'init_woocommerce_memberships',
		'class'       => 'WC_Memberships_Loader',
	);
	$modules[] = array(
		'name'        => 'wc_product_bundles',
		'title'       => __( 'WooCommerce Product Bundles', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce extension for creating simple product bundles, kits and assemblies.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/product-bundles/',
		'class'       => 'WC_Bundles',
	);
	$modules[] = array(
		'name'        => 'wc_min_max',
		'title'       => __( 'WooCommerce Min/Max Quantities', 'woocommerce-exporter' ),
		'description' => __( 'Lets you define minimum/maximum allowed quantities for products, variations and orders.', 'woocommerce-exporter' ),
		'url'         => 'https://woocommerce.com/products/minmax-quantities/',
		'class'       => 'WC_Min_Max_Quantities',
	);
	$modules[] = array(
		'name'        => 'wc_followupemails',
		'title'       => __( 'WooCommerce Follow Ups', 'woocommerce-exporter' ),
		'description' => __( 'Follow-Ups makes it easy to automate communications via email and Twitter to keep your customers engaged and spending money.', 'woocommerce-exporter' ),
		'url'         => 'https://woocommerce.com/products/follow-up-emails/',
		'class'       => 'FollowUpEmails',
	);
	$modules[] = array(
		'name'        => 'wc_ship_multiple',
		'title'       => __( 'Ship to Multiple Addresses', 'woocommerce-exporter' ),
		'description' => __( 'Allow your customers to ship individual items in a single order to multiple addresses.', 'woocommerce-exporter' ),
		'url'         => 'https://woocommerce.com/products/shipping-multiple-addresses/',
		'class'       => 'WC_Ship_Multiple',
	);
	$modules[] = array(
		'name'        => 'ups_ap_shipping',
		'title'       => __( 'WooCommerce UPS Access Point Shipping', 'woocommerce-exporter' ),
		'description' => __( '(UK) This plugin integrates with UPS Access Point&trade; services to deliver parcels at the nearest convenience store.', 'woocommerce-exporter' ),
		'url'         => 'https://shop.renoovodesign.co.uk/product/ups-access-point-plugin-woocommerce/',
		'function'    => 'run_woocommerce_ups_ap_shipping',
	);
	$modules[] = array(
		'name'        => 'awebooking',
		'title'       => __( 'AweBooking', 'woocommerce-exporter' ),
		'description' => __( 'You can easily create a booking/reservation system into your WordPress website without any hassle', 'woocommerce-exporter' ),
		'url'         => 'https://codecanyon.net/item/awebooking-online-hotel-booking-for-wordpress/12323878',
		'function'    => 'awebooking_clean_room',
	);
	$modules[] = array(
		'name'        => 'yith_delivery_pro',
		'title'       => __( 'YITH WooCommerce Delivery Date Premium', 'woocommerce-exporter' ),
		'description' => __( 'Let your customers choose a delivery date for their orders', 'woocommerce-exporter' ),
		'url'         => 'http://yithemes.com/themes/plugins/yith-woocommerce-delivery-date/',
		'function'    => 'yith_delivery_date_init_plugin',
	);
	$modules[] = array(
		'name'        => 'yith_brands_pro',
		'title'       => __( 'YITH WooCommerce Brands Add-On', 'woocommerce-exporter' ),
		'description' => __( 'YITH WooCommerce Brands Add-on allows you to add brands functionality to the default WooCommerce Plugin.', 'woocommerce-exporter' ),
		'url'         => 'http://yithemes.com/themes/plugins/yith-woocommerce-brands-add-on/',
		'function'    => 'yith_brands_constructor',
	);
	$modules[] = array(
		'name'        => 'ign_tiered',
		'title'       => __( 'WooCommerce Tiered Pricing', 'woocommerce-exporter' ),
		'description' => __( 'Allows you to set price tiers for products and variations based on user roles.', 'woocommerce-exporter' ),
		'url'         => 'http://ignitewoo.com/woocommerce-extensions-plugins-themes/woocommerce-tiered-pricing/',
		'function'    => 'ign_tiered_init',
	);
	$modules[] = array(
		'name'        => 'wc_books',
		'title'       => __( 'WooCommerce BookStore', 'woocommerce-exporter' ),
		'description' => __( 'Convert your WooCommerce store to online book store. Sell books using WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'http://www.wpini.com/woocommerce-bookstore-plugin/',
		'function'    => 'woo_bookstore_init',
	);
	$modules[] = array(
		'name'        => 'wc_point_of_sales',
		'title'       => __( 'WooCommerce Point of Sale', 'woocommerce-exporter' ),
		'description' => __( 'Extend your online WooCommerce store by adding a brick and mortar Point of Sale (POS) interface.', 'woocommerce-exporter' ),
		'url'         => 'https://codecanyon.net/item/woocommerce-point-of-sale-pos/7869665',
		'function'    => 'WC_POS',
	);
	$modules[] = array(
		'name'        => 'wc_pdf_product_vouchers',
		'title'       => __( 'WooCommerce PDF Product Vouchers', 'woocommerce-exporter' ),
		'description' => __( 'Customize and sell PDF product vouchers with WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woothemes.com/products/pdf-product-vouchers/',
		'function'    => 'init_woocommerce_pdf_product_vouchers',
	);
	$modules[] = array(
		'name'        => 'wpml',
		'title'       => __( 'WPML Multilingual CMS', 'woocommerce-exporter' ),
		'description' => __( 'WPML Multilingual CMS.', 'woocommerce-exporter' ),
		'url'         => 'https://wpml.org/',
		'function'    => 'icl_sitepress_activate',
	);
	$modules[] = array(
		'name'        => 'wpml_wc',
		'title'       => __( 'WooCommerce Multilingual', 'woocommerce-exporter' ),
		'description' => __( 'Allows running fully multilingual e-Commerce sites with WooCommerce and WPML.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-multilingual/',
		'slug'        => 'woocommerce-multilingual',
		'class'       => 'woocommerce_wpml',
	);
	$modules[] = array(
		'name'        => 'wootabs',
		'title'       => __( 'WooTabs', 'woocommerce-exporter' ),
		'description' => __( 'WooTabs allows you to add extra tabs (as many as you want) to the WooCommerce Product Details page.', 'woocommerce-exporter' ),
		'url'         => 'https://codecanyon.net/item/wootabsadd-extra-tabs-to-woocommerce-product-page/7891253',
		'function'    => 'on_woocommerce_wootabs_loaded',
	);
	$modules[] = array(
		'name'        => 'wc_ean',
		'title'       => __( 'WooCommerce EAN Payment Gateway', 'woocommerce-exporter' ),
		'description' => __( 'This plugin adds an EAN13 Payment Gateway for WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'http://plugins.yanco.dk/woocommerce-ean-payment-gateway',
		'function'    => 'WOOCMMERCE_EAN_PAYMENT_GATEWAY',
	);
	$modules[] = array(
		'name'        => 'wc_germanized',
		'title'       => __( 'WooCommerce Germanized', 'woocommerce-exporter' ),
		'description' => __( 'Extends WooCommerce to become a legally compliant store for the German market.', 'woocommerce-exporter' ),
		'url'         => 'https://www.vendidero.de/woocommerce-germanized',
		'function'    => 'WC_germanized',
	);
	$modules[] = array(
		'name'        => 'wc_germanized_pro',
		'title'       => __( 'WooCommerce Germanized Pro', 'woocommerce-exporter' ),
		'description' => __( 'Extends WooCommerce Germanized with professional features such as PDF invoices, legal text generators and many more.', 'woocommerce-exporter' ),
		'url'         => 'https://www.vendidero.de/woocommerce-germanized',
		'function'    => 'WC_germanized_pro',
	);
	$modules[] = array(
		'name'        => 'wc_umcs',
		'title'       => __( 'WooCommerce Ultimate Multi Currency Suite', 'woocommerce-exporter' ),
		'description' => __( 'Multi currency e-commerce plugin for WordPress-WooCommerce systems.', 'woocommerce-exporter' ),
		'url'         => 'https://codecanyon.net/item/woocommerce-ultimate-multi-currency-suite/11997014',
		'class'       => 'WooCommerce_Ultimate_Multi_Currency_Suite_Main',
	);
	$modules[] = array(
		'name'        => 'wc_entrada',
		'title'       => __( 'Entrada', 'woocommerce-exporter' ),
		'description' => __( 'Declares a plugin that will create custom taxonomy to WooCommerce Products.', 'woocommerce-exporter' ),
		'url'         => 'https://themeforest.net/item/tour-booking-adventure-tour-wordpress-theme-entrada/16867379',
		'function'    => 'entrada_create_product_taxnomy',
	);
	$modules[] = array(
		'name'        => 'wc_deliveryslots',
		'title'       => __( 'WooCommerce Delivery Slots', 'woocommerce-exporter' ),
		'description' => __( 'Allow your customers to select a delivery slot for their order.', 'woocommerce-exporter' ),
		'url'         => 'https://iconicwp.com/products/woocommerce-delivery-slots/',
		'class'       => 'jckWooDeliverySlots',
	);
	$modules[] = array(
		'name'        => 'wc_products_purchase_price',
		'title'       => __( 'Products Purchase Price for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Plug-in for WooCommerce that allows you to insert the cost (or purchase price) of your products!', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/products-purchase-price-for-woocommerce/',
		'slug'        => 'products-purchase-price-for-woocommerce',
		'function'    => 'product_purchase_price_admin_scripts',
	);
	$modules[] = array(
		'name'        => 'wc_product_custom_options',
		'title'       => __( 'WooCommerce Product Custom Options Lite', 'woocommerce-exporter' ),
		'description' => __( 'Give your Ecommerce website the space to add customized options for your products.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-custom-options-lite/',
		'slug'        => 'woocommerce-custom-options-lite',
		'class'       => 'Product_Custom_Options',
	);
	$modules[] = array(
		'name'        => 'wc_wholesale_prices',
		'title'       => __( 'WooCommerce Wholesale Prices', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Extension to provide Wholesale Prices functionality.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-wholesale-prices/',
		'slug'        => 'woocommerce-wholesale-prices',
		'function'    => 'wwp_global_plugin_deactivate',
	);
	$modules[] = array(
		'name'        => 'wc_show_single_variations',
		'title'       => __( 'WooCommerce Show Single Variations', 'woocommerce-exporter' ),
		'description' => __( 'Show product variations in the main product loops.', 'woocommerce-exporter' ),
		'url'         => 'https://codecanyon.net/item/woocommerce-show-single-variations/13523915',
		'class'       => 'JCK_WSSV',
	);
	$modules[] = array(
		'name'        => 'wc_deposits',
		'title'       => __( 'WooCommerce Deposits', 'woocommerce-exporter' ),
		'description' => __( 'Adds deposits support to WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'https://woocommerce.com/products/woocommerce-deposits/',
		'class'       => 'WC_Deposits',
	);
	$modules[] = array(
		'name'        => 'wc_unitofmeasure',
		'title'       => __( 'WooCommerce Unit of Measure', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Unit Of Measure allows the user to add a unit of measure after the price on WooCommerce products.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-unit-of-measure/',
		'slug'        => 'woocommerce-unit-of-measure',
		'class'       => 'Woo_UOM',
	);
	$modules[] = array(
		'name'        => 'wc_easybooking',
		'title'       => __( 'WooCommerce Easy Booking', 'woocommerce-exporter' ),
		'description' => __( 'Easily rent or book your products with WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-easy-booking-system/',
		'slug'        => 'woocommerce-easy-booking-system',
		'class'       => 'Easy_booking',
	);
	$modules[] = array(
		'name'        => 'wc_advanced_quantities',
		'title'       => __( 'WooCommerce Advanced Product Quantities', 'woocommerce-exporter' ),
		'description' => __( 'Easily require your customers to buy a minimum/maximum/incremental amount of products to continue with their Checkout.', 'woocommerce-exporter' ),
		'url'         => 'http://www.wpbackoffice.com/plugins/woocommerce-incremental-product-quantities/',
		'function'    => 'IPQ',
	);
	$modules[] = array(
		'name'        => 'wc_chained_products',
		'title'       => __( 'WooCommerce Chained Products', 'woocommerce-exporter' ),
		'description' => __( 'Create discounted product bundles and combo packs and boost your sales. Automatically add linked / related products to order.', 'woocommerce-exporter' ),
		'url'         => 'https://woocommerce.com/products/chained-products/',
		'class'       => 'WC_Chained_Products',
	);
	$modules[] = array(
		'name'        => 'wc_sample',
		'title'       => __( 'WooCommerce Sample', 'woocommerce-exporter' ),
		'description' => __( 'Include Get Sample Button in products of your online store.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-sample/',
		'slug'        => 'woocommerce-sample',
		'class'       => 'WooCommerce_Sample',
	);
	$modules[] = array(
		'name'        => 'wc_product_importer_deluxe',
		'title'       => __( 'WooCommerce Product Importer Deluxe', 'woocommerce-exporter' ),
		'description' => __( 'Bulk import hundreds, even thousands of linked Products and Product images into your WooCommerce store.', 'woocommerce-exporter' ),
		'url'         => 'https://www.visser.com.au/plugins/product-importer-deluxe/',
		'function'    => 'woo_pd_i18n',
	);
	$modules[] = array(
		'name'        => 'wc_ag_barcode_pro',
		'title'       => __( 'AG WooCommerce Barcode / ISBN & Amazon ASIN - PRO', 'woocommerce-exporter' ),
		'description' => __( 'A plugin to add a barcode, ISBN & Amazon ASIN fields to WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'https://www.weareag.co.uk/product/woocommerce-barcodeisbn-amazon-asin-pro/',
		'function'    => array( 'AGD_load_updater', 'woo_add_barcode' ),
	);
	$modules[] = array(
		'name'        => 'wc_nm_personalizedproduct',
		'title'       => __( 'N-Media WooCommerce Personalized Product Meta Manager', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Personalized Product Option Plugin allows site admin to add unlimited input fields on product page.', 'woocommerce-exporter' ),
		'url'         => 'http://najeebmedia.com/wordpress-plugin/woocommerce-personalized-product-option/',
		'class'       => 'NM_PersonalizedProduct',
	);
	$modules[] = array(
		'name'        => 'wc_appointments',
		'title'       => __( 'WooCommerce Appointments', 'woocommerce-exporter' ),
		'description' => __( 'Setup appointable products for WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'http://www.bizzthemes.com/plugins/woocommerce-appointments/',
		'class'       => 'WC_Appointments',
	);
	$modules[] = array(
		'name'        => 'seo_squirrly',
		'title'       => __( 'SEO Squirrly', 'woocommerce-exporter' ),
		'description' => __( 'SEO Squirrly is for the NON-SEO experts. Get Excellent SEO with Better Content, Ranking and Analytics. For Both Humans and Search Bots.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/squirrly-seo/',
		'slug'        => 'squirrly-seo',
		'function'    => 'sq_phpError',
	);
	$modules[] = array(
		'name'        => 'tickera',
		'title'       => __( 'Tickera', 'woocommerce-exporter' ),
		'description' => __( 'Simple event ticketing system.', 'woocommerce-exporter' ),
		'url'         => 'https://tickera.com/',
		'slug'        => 'tickera',
		'class'       => 'TC',
	);
	$modules[] = array(
		'name'        => 'wc_measurement_price_calc',
		'title'       => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce plugin to provide price and quantity calculations based on product measurements.', 'woocommerce-exporter' ),
		'url'         => 'http://www.woocommerce.com/products/measurement-price-calculator/',
		'function'    => 'init_woocommerce_measurement_price_calculator',
		'class'       => 'WC_Measurement_Price_Calculator_Loader',
	);
	$modules[] = array(
		'name'        => 'wc_stripe',
		'title'       => __( 'WooCommerce Stripe Payment Gateway', 'woocommerce-exporter' ),
		'description' => __( 'Take credit card payments on your store using Stripe.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-gateway-stripe/',
		'slug'        => 'woocommerce-gateway-stripe',
		'class'       => 'WC_Stripe',
	);
	$modules[] = array(
		'name'        => 'yith_ywpi',
		'title'       => __( 'YITH WooCommerce PDF Invoice and Shipping List', 'woocommerce-exporter' ),
		'description' => __( 'Generate PDF invoices for WooCommerce orders. Set manual or automatic invoice generation and shipping list document.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/yith-woocommerce-pdf-invoice/',
		'slug'        => 'yith-woocommerce-pdf-invoice',
		'function'    => 'yith_ywpi_init',
	);
	$modules[] = array(
		'name'        => 'yith_pdf_invoice',
		'title'       => __( 'YITH WooCommerce PDF Invoice and Shipping List Premium', 'woocommerce-exporter' ),
		'description' => __( 'Generate PDF invoices, credit notes, pro-forma invoice and packing slip for WooCommerce orders.', 'woocommerce-exporter' ),
		'url'         => 'http://yithemes.com/themes/plugins/yith-woocommerce-pdf-invoice/',
		'function'    => 'YITH_PDF_Invoice',
	);
	$modules[] = array(
		'name'        => 'alg_con',
		'title'       => __( 'Custom Order Numbers for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Custom order numbers for WooCommerce.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/custom-order-numbers-for-woocommerce/',
		'slug'        => 'custom-order-numbers-for-woocommerce',
		'class'       => 'Alg_WC_Custom_Order_Numbers',
	);
	$modules[] = array(
		'name'        => 'ign_gift_certs',
		'title'       => __( 'WooCommerce Gift Certificates Pro', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Gift Certificates Pro allows you to sell gift certificates / store credits / coupon codes as products in your store.', 'woocommerce-exporter' ),
		'url'         => 'https://ignitewoo.com/woocommerce-extensions-plugins-themes/woocommerce-gift-certificates-pro/',
		'class'       => 'Ignite_Gift_Certs',
	);
	$modules[] = array(
		'name'        => 'wc_fields_factory',
		'title'       => __( 'WC Fields Factory', 'woocommerce-exporter' ),
		'description' => __( 'Sell your products with customized, personalised options. Add custom fields or fields group to your products, your admin screens and customize everything.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/wc-fields-factory/',
		'slug'        => 'wc-fields-factory',
		'class'       => 'wcff',
	);
	$modules[] = array(
		'name'        => 'wc_upload_files',
		'title'       => __( 'WooCommerce Upload Files', 'woocommerce-exporter' ),
		'description' => __( 'WCUF plugin allows your customers to attach files to their orders according to the purchased products.', 'woocommerce-exporter' ),
		'url'         => 'https://codecanyon.net/item/woocommerce-upload-files/11442983',
		'function'    => 'wcuf_init',
	);
	$modules[] = array(
		'name'        => 'wc_ecr_gcr',
		'title'       => __( 'Google Customer Reviews for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Integrates Google Merchant Center\'s Google Customer Reviews survey opt-in and badge into your WooCommerce store.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/ecr-google-customer-reviews/',
		'slug'        => 'ecr-google-customer-reviews',
		'function'    => 'ecr_gcr_missing_wc_notice',
	);
	$modules[] = array(
		'name'        => 'wpla_wplister',
		'title'       => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' ),
		'description' => __( 'List your products on Amazon the easy way.', 'woocommerce-exporter' ),
		'url'         => 'https://www.wplab.com/plugins/wp-lister-for-amazon/',
		'class'       => 'WPLA_WPLister',
	);
	$modules[] = array(
		'name'        => 'wple_wplister',
		'title'       => __( 'WP-Lister Pro for eBay', 'woocommerce-exporter' ),
		'description' => __( 'List your products on eBay the easy way.', 'woocommerce-exporter' ),
		'url'         => 'https://www.wplab.com/plugins/wp-lister/',
		'class'       => 'WPL_WPLister',
	);
	$modules[] = array(
		'name'        => 'alidropship',
		'title'       => __( 'AliDropship for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'AliDropship is a WordPress Plugin to import AliExpress products into your WooCommerce Shop.', 'woocommerce-exporter' ),
		'url'         => 'https://alidropship.com/',
		'function'    => 'adsw_check_server',
	);
	$modules[] = array(
		'name'        => 'wc_alldiscounts_lite',
		'title'       => __( 'Conditional Discounts for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Manage your shop discounts like a pro. Formally WooCommerce All Discounts Lite.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woo-advanced-discounts/',
		'slug'        => 'woo-advanced-discounts',
		'function'    => 'run_wad',
	);
	$modules[] = array(
		'name'        => 'atum_inventory',
		'title'       => __( 'ATUM Inventory Management for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'ATUM is the most advanced free WooCommerce inventory management tool in the WordPress Plugins repository.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/atum-stock-manager-for-woocommerce/',
		'slug'        => 'atum-stock-manager-for-woocommerce',
		'class'       => 'Atum\Inc\Helpers',
	);
	$modules[] = array(
		'name'        => 'wc_bookings_appointments_pro',
		'title'       => __( 'Bookings and Appointments For WooCommerce Premium', 'woocommerce-exporter' ),
		'description' => __( 'Woocommerce Bookings and Appointments Premium converts your time, products or services etc into a bookable resource.', 'woocommerce-exporter' ),
		'url'         => 'https://www.pluginhive.com/product/woocommerce-booking-and-appointments/',
		'class'       => 'phive_booking_initialze_premium',
	);
	$modules[] = array(
		'name'        => 'wc_shipment_tracking',
		'title'       => __( 'WooCommerce Shipment Tracking', 'woocommerce-exporter' ),
		'description' => __( 'Add tracking numbers to orders allowing customers to track their orders via a link.', 'woocommerce-exporter' ),
		'url'         => 'https://woocommerce.com/products/shipment-tracking/',
		'class'       => 'WC_Shipment_Tracking',
	);
	$modules[] = array(
		'name'        => 'wc_ups_shipping',
		'title'       => __( 'UPS WooCommerce Shipping', 'woocommerce-exporter' ),
		'description' => __( 'Obtain Real time shipping rates, Print shipping labels and Track Shipment via the UPS Shipping API.', 'woocommerce-exporter' ),
		'url'         => 'https://www.pluginhive.com/product/woocommerce-ups-shipping-plugin-with-print-label/',
		'class'       => 'UPS_WooCommerce_Shipping',
	);
	$modules[] = array(
		'name'        => 'wc_ppom',
		'title'       => __( 'PPOM for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Adds input fields on product page to personalized your product.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woocommerce-product-addon/',
		'slug'        => 'woocommerce-product-addon',
		'function'    => 'PPOM',
	);
	$modules[] = array(
		'name'        => 'wc_pwb',
		'title'       => __( 'Perfect WooCommerce Brands', 'woocommerce-exporter' ),
		'description' => __( 'Perfect WooCommerce Brands allows you to show product brands in your WooCommerce based store.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/perfect-woocommerce-brands/',
		'slug'        => 'perfect-woocommerce-brands',
		'class'       => '\Perfect_Woocommerce_Brands\Perfect_Woocommerce_Brands',
	);
	$modules[] = array(
		'name'        => 'wc_piva',
		'title'       => __( 'WooCommerce P.IVA e Codice Fiscale per Italia', 'woocommerce-exporter' ),
		'description' => __( 'Il plugin che rende compatibile woocommerce per il mercato italiano.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woo-piva-codice-fiscale-e-fattura-pdf-per-italia/',
		'slug'        => 'woo-piva-codice-fiscale-e-fattura-pdf-per-italia',
		'class'       => 'WC_Piva_Cf_Invoice_Ita',
	);
	$modules[] = array(
		'name'        => 'wc_easy_cf_piva',
		'title'       => __( 'WooCommerce Easy Codice Fiscale Partita Iva', 'woocommerce-exporter' ),
		'description' => __( 'Add the Partita IVA e Codice Fiscale fields in WooCommerce for the italian market.', 'woocommerce-exporter' ),
		'url'         => 'https://wordpress.org/plugins/woo-easy-codice-fiscale-partita-iva/',
		'slug'        => 'woo-easy-codice-fiscale-partita-iva',
		'function'    => 'run_mdt_wc_easy_cf_piva',
	);
	$modules[] = array(
		'name'        => 'wc_serial_numbers',
		'title'       => __( 'WooCommerce Serial Numbers', 'woocommerce-exporter' ),
		'description' => __( 'The best WooCommerce Plugin to sell license keys, redeem cards and other secret numbers!', 'woocommerce-exporter' ),
		'url'         => 'http://wordpress.org/extend/plugins/wc-serial-numbers/',
		'slug'        => 'wc-serial-numbers',
		'class'       => class_exists( 'WC_Serial_Numbers' ) ? 'WC_Serial_Numbers' : 'WCSerialNumbers',
	);
	$modules[] = array(
		'name'        => 'yith_woocommerce_subscription',
		'title'       => __( 'YITH WooCommerce Subscription Premium', 'woocommerce-exporter' ),
		'description' => __( 'Allows enabling automatic recurring payments on your products.', 'woocommerce-exporter' ),
		'url'         => 'https://yithemes.com/themes/plugins/yith-woocommerce-subscription/',
		'function'    => 'YITH_WC_Subscription',
	);
	$modules[] = array(
		'name'        => 'wc_warranty',
		'title'       => __( 'WooCommerce Warranty Requests', 'woocommerce-exporter' ),
		'description' => __( 'Set warranties for your products (free and paid), and allow customers to purchase warranties when buying a product, and to initiate a return request right from their account.', 'woocommerce-exporter' ),
		'url'         => 'https://woocommerce.com/products/warranty-requests/',
		'function'    => 'wc_warranty_init',
	);
	$modules[] = array(
		'name'        => 'all_products_for_woocommerce_subscriptions',
		'title'       => __( 'WooCommerce All Products For Subscriptions', 'woocommerce-exporter' ),
		'description' => __( 'Make existing products available on subscription, and give customers the freedom to add products to their existing subscriptions.', 'woocommerce-exporter' ),
		'url'         => 'https://woocommerce.com/products/all-products-for-woocommerce-subscriptions/',
		'class'       => 'WCS_ATT',
	);

	$modules = apply_filters( 'woo_ce_modules_addons', $modules );

	// Check if the existing Transient exists.
	$modules_all = count( $modules );
	$cached      = get_transient( WOO_CE_PREFIX . '_modules_all_count' );
	if ( false === $cached ) {
		set_transient( WOO_CE_PREFIX . '_modules_all_count', $modules_all, DAY_IN_SECONDS );
	}

	$modules_active   = 0;
	$modules_inactive = 0;

	if ( ! empty( $modules ) ) {
		$user_capability = 'install_plugins';
		foreach ( $modules as $key => $module ) {
			$modules[ $key ]['status'] = 'inactive';
			$module_active             = false;
			// Check if each module is activated.
			if ( ! $module_active && isset( $module['function'] ) ) {
				if ( is_array( $module['function'] ) ) {
					$size = count( $module['function'] );
					for ( $i = 0; $i < $size; $i++ ) {
						if ( function_exists( $module['function'][ $i ] ) ) {
							$modules[ $key ]['status'] = 'active';
							$module_active             = true;
							++$modules_active;
							break;
						}
					}
				} elseif ( function_exists( $module['function'] ) ) {
						$modules[ $key ]['status'] = 'active';
						$module_active             = true;
						++$modules_active;
				}
			}
			if ( ! $module_active && isset( $module['class'] ) ) {
				if ( is_array( $module['class'] ) ) {
					$size = count( $module['class'] );
					for ( $i = 0; $i < $size; $i++ ) {
						if ( class_exists( $module['class'][ $i ] ) ) {
							$modules[ $key ]['status'] = 'active';
							$module_active             = true;
							++$modules_active;
							break;
						}
					}
				} elseif ( class_exists( $module['class'] ) ) {
						$modules[ $key ]['status'] = 'active';
						$module_active             = true;
						++$modules_active;
				}
			}
			if ( ! $module_active && isset( $module['global'] ) ) {
				if ( is_array( $module['global'] ) ) {
					$size = count( $module['global'] );
					for ( $i = 0; $i < $size; $i++ ) {
						if ( array_key_exists( $module['global'], $GLOBALS ) ) {
							$modules[ $key ]['status'] = 'active';
							$module_active             = true;
							++$modules_active;
							break;
						}
					}
				} elseif ( array_key_exists( $module['global'], $GLOBALS ) ) {
						$modules[ $key ]['status'] = 'active';
						$module_active             = true;
						++$modules_active;
				}
			}
			// Filter Modules by Module Status.
			if ( ! empty( $module_status ) ) {
				switch ( $module_status ) {

					case 'active':
						if ( 'inactive' === $modules[ $key ]['status'] ) {
							unset( $modules[ $key ] );
						}
						break;

					case 'inactive':
						if ( 'active' === $modules[ $key ]['status'] ) {
							unset( $modules[ $key ] );
						}
						break;
				}
			}
			// Check that we've got these resources available.
			if ( isset( $modules[ $key ] ) && function_exists( 'current_user_can' ) && did_action( 'init' ) ) {
				// Check if the Plugin has a slug and if User can install Plugins.
				if ( current_user_can( $user_capability ) && isset( $module['slug'] ) ) {
					$modules[ $key ]['url'] = esc_url( admin_url( sprintf( 'plugin-install.php?tab=search&type=term&s=%s', urlencode( $module['slug'] ) ) ) );
				}
			}
		}
	}

	// Check if the existing Transient exists.
	$cached = get_transient( WOO_CE_PREFIX . '_modules_active_count' );
	if ( false === $cached ) {
		set_transient( WOO_CE_PREFIX . '_modules_active_count', $modules_active, DAY_IN_SECONDS );
	}

	// Check if the existing Transient exists.
	$cached = get_transient( WOO_CE_PREFIX . '_modules_inactive_count' );
	if ( false === $cached ) {
		$modules_inactive = $modules_all - $modules_active;
		set_transient( WOO_CE_PREFIX . '_modules_inactive_count', $modules_inactive, DAY_IN_SECONDS );
	}

	return $modules;
}
/**
 * Returns whether a supported export plugin is activated.
 *
 * @param string $plugin_name The name of the plugin to check.
 * @return bool|void True if plugin is active, void if plugin name is empty.
 */
function woo_ce_detect_export_plugin( $plugin_name = '' ) {
	if ( empty( $plugin_name ) ) {
		return;
	}

	// Check if a cached list of active modules is available.
	$active_modules = get_transient( WOO_CE_PREFIX . '_modules_active' );
	if ( false === $active_modules ) {
		$active_modules = woo_ce_refresh_active_export_plugins();
	}

	// Check if the requested plugin is in the list of active export plugins.
	if ( ! empty( $active_modules ) && in_array( $plugin_name, $active_modules, true ) ) {
		return true;
	}
}

/**
 * Refreshes the list of active export plugins.
 *
 * @return array|bool Array of active modules or false if empty.
 */
function woo_ce_refresh_active_export_plugins() {
	// Delete the existing count transients.
	delete_transient( WOO_CE_PREFIX . '_modules_all_count' );
	delete_transient( WOO_CE_PREFIX . '_modules_active_count' );
	delete_transient( WOO_CE_PREFIX . '_modules_inactive_count' );

	// Refresh the count transients.
	$modules = woo_ce_modules_list( 'active' );

	// Create a list of active export plugins.
	$active_modules = false;
	if ( ! empty( $modules ) ) {
		$active_modules = array_column( $modules, 'name' );
	}

	// Save the list of active export plugins.
	set_transient( WOO_CE_PREFIX . '_modules_active', $active_modules, DAY_IN_SECONDS );

	return $active_modules;
}

/**
 * Return a list of Export Template IDs.
 *
 * @param array $args Query arguments.
 * @return array|void Array of template IDs or void if no templates found.
 */
function woo_ce_get_export_templates( $args = array() ) {
	$post_type = 'export_template';
	$defaults  = array(
		'post_type'        => $post_type,
		'posts_per_page'   => -1,
		'fields'           => 'ids',
		'suppress_filters' => 1,
	);
	$args      = wp_parse_args( $args, $defaults );

	$templates_query = new WP_Query( $args );
	if ( $templates_query->posts ) {
		return $templates_query->posts;
	}
}

/**
 * Log errors to WooCommerce logger or fallback to error_log.
 *
 * @param string $message The error message to log.
 * @param string $level   The log level.
 * @return bool True if logged successfully, false otherwise.
 */
function woo_ce_error_log( $message = '', $level = false ) {
	if ( '' === $message ) {
		return false;
	}

	if ( class_exists( 'WC_Logger' ) && apply_filters( 'woo_ce_error_log_use_wc_logger', true ) ) {
		if ( version_compare( woo_get_woo_version(), '3.0', '>=' ) ) {
			$logger       = wc_get_logger();
			$context      = array( 'source' => WOO_CE_PREFIX );
			$notice_level = ! empty( $level ) ? $level : apply_filters( 'woo_ce_error_log_default_level', WC_Log_Levels::NOTICE );
			if ( method_exists( $logger, $notice_level ) ) {
				$logger->{$notice_level}( $message, $context );
				return true;
			}
		} elseif ( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
			$logger       = new WC_Logger();
			$context      = array( 'source' => WOO_CE_PREFIX );
			$notice_level = ! empty( $level ) ? $level : apply_filters( 'woo_ce_error_log_default_level', WC_Log_Levels::NOTICE );
			// Format notice levels to match WC_Log_Levels.
			$notice_level = woo_ce_get_wc_log_level( $notice_level );
			$logger->log( $notice_level, $message, $context );
			return true;
		} else {
			$logger = new WC_Logger();
			$logger->add( WOO_CE_PREFIX, $message );
			return true;
		}
	} else {
		// Fallback where the WooCommerce logging engine is unavailable.
		error_log( sprintf( '[store-exporter-deluxe] %s', $message ) );
		return true;
	}

	return false;
}

/**
 * Get the corresponding WC_Log_Levels constant for a given log level.
 *
 * @param string $level The log level.
 * @return string The corresponding WC_Log_Levels constant.
 */
function woo_ce_get_wc_log_level( $level ) {
	switch ( $level ) {
		case 'emergency':
			return WC_Log_Levels::EMERGENCY;
		case 'alert':
			return WC_Log_Levels::ALERT;
		case 'critical':
			return WC_Log_Levels::CRITICAL;
		case 'error':
			return WC_Log_Levels::ERROR;
		case 'warning':
			return WC_Log_Levels::WARNING;
		case 'notice':
			return WC_Log_Levels::NOTICE;
		case 'info':
			return WC_Log_Levels::INFO;
		case 'debug':
			return WC_Log_Levels::DEBUG;
		default:
			return WC_Log_Levels::NOTICE;
	}
}

/**
 * Get the last error message.
 *
 * @return string The last error message or '-' if no error.
 */
function woo_ce_error_get_last_message() {
	$output = '-';
	if ( function_exists( 'error_get_last' ) ) {
		$last_error = error_get_last();
		if ( isset( $last_error ) && isset( $last_error['message'] ) ) {
			$output = sanitize_text_field( $last_error['message'] );
		}
		unset( $last_error );
	}
	return $output;
}

/**
 * Check if the current export is a scheduled export.
 *
 * @param int|bool $post_ID Optional. The post ID to check.
 * @return bool True if it's a scheduled export, false otherwise.
 */
function woo_ce_is_scheduled_export( $post_ID = false ) {
	global $export;

	$output = false;
	if ( empty( $post_ID ) ) {
		// Check if the Scheduled Export flag is set.
		if ( $export->scheduled_export ) {
			$output = true;
		}
	}

	// If a Post ID is provided then check if that Scheduled Export is running.
	if ( ! empty( $post_ID ) ) {
		$output           = false;
		$scheduled_export = ( $export->scheduled_export ? absint( get_transient( WOO_CE_PREFIX . '_scheduled_export_id' ) ) : 0 );
		if ( ! empty( $scheduled_export ) ) {
			// Check if the given Post ID matches the current Scheduled Export.
			if ( absint( $post_ID ) === $scheduled_export ) {
				$output = true;
			}
		}
	}
	return $output;
}

/**
 * Check if the current export is an export template.
 *
 * @param int|bool $post_ID Optional. The post ID to check.
 * @return bool True if it's an export template, false otherwise.
 */
function woo_ce_is_export_template( $post_ID = false ) {
	global $export;

	$output = false;
	// Check if the Scheduled Export flag is set.
	if ( $export->scheduled_export ) {
		$output = true;
	}

	// If a Post ID is provided then check if a Scheduled Export is running.
	if ( ! empty( $post_ID ) ) {
		$output           = false;
		$scheduled_export = ( $export->scheduled_export ? absint( get_transient( WOO_CE_PREFIX . '_scheduled_export_id' ) ) : 0 );
		if ( ! empty( $scheduled_export ) ) {
			// Check if an Export Template is associated with this Scheduled Export.
			$export_template = get_post_meta( $scheduled_export, '_export_template', true );
			if ( false !== $export_template ) {
				// Check if the given Post ID matches the Export Template associated with this Scheduled Export.
				if ( absint( $post_ID ) === absint( $export_template ) ) {
					$output = true;
				}
			}
		} else {
			// Check if a temporary Transient is set for the Export Template.
			$export_template = get_transient( WOO_CE_PREFIX . '_single_export_template' );
			if ( false !== $export_template ) {
				// Check if the given Post ID matches the Export Template associated with this Scheduled Export.
				if ( absint( $post_ID ) === absint( $export_template ) ) {
					$output = true;
				}
			}
		}
	}
	return $output;
}

/**
 * Get an option value.
 *
 * @param string $option      The option name.
 * @param mixed  $default     Optional. Default value to return if the option does not exist.
 * @param bool   $allow_empty Optional. Whether to allow empty values.
 * @return mixed The option value.
 */
function woo_ce_get_option( $option = null, $default = false, $allow_empty = false ) {
	$output = false;
	if ( null !== $option ) {
		$separator = '_';
		$output    = get_option( WOO_CE_PREFIX . $separator . $option, $default );
		if ( false === $allow_empty && 0 !== $output && ( false === $output || '' === $output ) ) {
			$output = $default;
		}
	}
	return $output;
}

/**
 * Update an option value.
 *
 * @param string $option The option name.
 * @param mixed  $value  The new value.
 * @return bool True if the option was updated, false otherwise.
 */
function woo_ce_update_option( $option = null, $value = null ) {
	$output = false;
	if ( null !== $option && null !== $value ) {
		$separator = '_';
		$output    = update_option( WOO_CE_PREFIX . $separator . $option, $value );
	}
	return $output;
}

/**
 * Apply multi-level sorting to the export output.
 *
 * @param array  $output The export output.
 * @param object $export The export object.
 * @return array The sorted export output.
 */
function woo_ce_multi_level_sorting( $output, $export ) {
	// Field multi-level sorting.
	if ( $export->scheduled_export ) {
		$scheduled_export = absint( get_transient( WOO_CE_PREFIX . '_scheduled_export_id' ) );
		$field1           = get_post_meta( $scheduled_export, '_filter_product_orderby_field1', true );
		$field1           = array_key_exists( $field1, $export->fields ) ? $field1 : false;
		$order_field1     = get_post_meta( $scheduled_export, '_filter_product_order_field1', true );
		$field2           = get_post_meta( $scheduled_export, '_filter_product_orderby_field2', true );
		$field2           = array_key_exists( $field2, $export->fields ) ? $field2 : false;
		$order_field2     = get_post_meta( $scheduled_export, '_filter_product_order_field2', true );

		if ( $field1 && $field2 ) {
			array_multisort( array_column( $output, $field1 ), ( 'ASC' === $order_field1 ) ? SORT_ASC : SORT_DESC, array_column( $output, $field2 ), ( 'ASC' === $order_field2 ) ? SORT_ASC : SORT_DESC, $output );
		} elseif ( $field1 ) {
			array_multisort( array_column( $output, $field1 ), ( 'ASC' === $order_field1 ) ? SORT_ASC : SORT_DESC, $output );
		}
		unset( $field1, $field2, $order_field1, $order_field2 );
	} else {
		$field1       = woo_ce_get_option( 'product_orderby_field1' );
		$field1       = array_key_exists( $field1, $export->fields ) ? $field1 : false;
		$order_field1 = woo_ce_get_option( 'product_order_field1' );
		$field2       = woo_ce_get_option( 'product_orderby_field2' );
		$field2       = array_key_exists( $field2, $export->fields ) ? $field2 : false;
		$order_field2 = woo_ce_get_option( 'product_order_field2' );

		if ( $field1 && $field2 ) {
			array_multisort( array_column( $output, $field1 ), ( 'ASC' === $order_field1 ) ? SORT_ASC : SORT_DESC, array_column( $output, $field2 ), ( 'ASC' === $order_field2 ) ? SORT_ASC : SORT_DESC, $output );
		} elseif ( $field1 ) {
			array_multisort( array_column( $output, $field1 ), ( 'ASC' === $order_field1 ) ? SORT_ASC : SORT_DESC, $output );
		}
		unset( $field1, $field2, $order_field1, $order_field2 );
	}

	return $output;
}

/**
 * Allow usage of meta_query in WooCommerce Order Queries.
 *
 * @param array $wp_query_args The WP_Query args.
 * @param array $query_vars The query vars.
 * @return array The WP_Query args.
 */
function woo_ce_enable_meta_query_in_wc_order_query( $wp_query_args, $query_vars ) {
	if ( isset( $query_vars['meta_query'] ) ) {
		$meta_query                  = isset( $wp_query_args['meta_query'] ) ? $wp_query_args['meta_query'] : array();
		$wp_query_args['meta_query'] = array_merge( $meta_query, $query_vars['meta_query'] );
	}
	return $wp_query_args;
}
add_filter( 'woocommerce_get_wp_query_args', 'woo_ce_enable_meta_query_in_wc_order_query', 10, 2 );

function woo_ce_upsell_link( $utm = '', $text = '', $url = 'https://visser.com.au/plugins/woocommerce-export/', $target = '_blank' ) {
	return sprintf(
		'<a href="%1$s" target="%2$s">%3$s</a>',
		esc_url( $url . $utm ),
		$target,
		$text !== '' ? $text : __( 'Store Exporter Deluxe', 'woocommerce-exporter' ),
	);
}
