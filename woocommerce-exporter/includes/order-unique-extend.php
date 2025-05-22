<?php
// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment, Squiz.PHP.CommentedOutCode.Found
/**
 * Order items formatting: Unique.
 *
 * @param array $order_data Order data.
 * @param int   $i          Index.
 * @param array $order_item Order item data.
 **/
function woo_ce_extend_order_items_unique( $order_data, $i, $order_item ) {

	// Product Add-ons - http://www.woothemes.com/.
	if ( woo_ce_detect_export_plugin( 'product_addons' ) ) {
		$product_addons = woo_ce_get_product_addons();
		if ( ! empty( $product_addons ) ) {
			foreach ( $product_addons as $product_addon ) {
				if ( isset( $order_item['product_addons'][ $product_addon->post_name ] ) ) {
					$order_data[ "order_item_{$i}_product_addon_{$product_addon->post_name}" ] = $order_item['product_addons'][ $product_addon->post_name ] ?? false;
				}
			}
			unset( $product_addons, $product_addon );
		}
	}

	// TODO HERE!.
	// Gravity Forms - http://woothemes.com/woocommerce.
	if (
		(
			woo_ce_detect_export_plugin( 'gravity_forms' ) &&
			woo_ce_detect_export_plugin( 'woocommerce_gravity_forms' )
		)
	) {
		// Check if there are any Products linked to Gravity Forms.
		$gf_fields = woo_ce_get_gravity_forms_fields();
		if ( ! empty( $gf_fields ) ) {
			$meta_type                                     = 'order_item';
			$order_data[ "order_item_{$i}_gf_form_id" ]    = $order_item['gf_form_id'] ?? false;
			$order_data[ "order_item_{$i}_gf_form_label" ] = $order_item['gf_form_label'] ?? false;
			foreach ( $gf_fields as $gf_field ) {
				// Check that we only fill export fields for forms that are actually filled.
				if ( isset( $order_item['gf_form_id'] ) && $gf_field['formId'] === $order_item['gf_form_id'] ) {
					$order_data[ "order_item_{$i}_gf_{$gf_field['formId']}_{$gf_field['id']}" ] = get_metadata( $meta_type, $order_item['id'], $gf_field['label'], true );
				}
			}
		}
		unset( $gf_fields, $gf_field );
	}

	// WooCommerce Checkout Add-Ons - http://www.skyverge.com/product/woocommerce-checkout-add-ons/.
	if ( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
		$order_data[ "order_item_{$i}_checkout_addon_id" ]    = $order_item['checkout_addon_id'] ?? false;
		$order_data[ "order_item_{$i}_checkout_addon_label" ] = $order_item['checkout_addon_label'] ?? false;
		$order_data[ "order_item_{$i}_checkout_addon_value" ] = $order_item['checkout_addon_value'] ?? false;
	}

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/.
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/.
	if ( woo_ce_detect_product_brands() ) {
		$order_data[ "order_item_{$i}_brand" ] = $order_item['brand'] ?? false;
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/.
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/.
	if (
		woo_ce_detect_export_plugin( 'vendors' ) ||
		woo_ce_detect_export_plugin( 'yith_vendor' )
	) {
		$order_data[ "order_item_{$i}_vendor" ] = $order_item['vendor'] ?? false;
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/.
	if ( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		$order_data[ "order_item_{$i}_cost_of_goods" ]       = $order_item['cost_of_goods'] ?? false;
		$order_data[ "order_item_{$i}_total_cost_of_goods" ] = $order_item['total_cost_of_goods'] ?? false;
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590.
	if ( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		$order_data[ "order_item_{$i}_posr" ] = $order_item['posr'] ?? false;
	}

	// WC Fields Factory - https://wordpress.org/plugins/wc-fields-factory/.
	if ( woo_ce_detect_export_plugin( 'wc_fields_factory' ) ) {
		// Product Fields.
		$product_fields = woo_ce_get_wcff_product_fields();
		if ( ! empty( $product_fields ) ) {
			foreach ( $product_fields as $product_field ) {
				$order_data[ "order_item_wccpf_{$product_field['name']}" ] = $order_item[ "wccpf_{$product_field['name']}" ];
			}
		}
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/.
	if ( woo_ce_detect_export_plugin( 'wc_msrp' ) ) {
		$order_data[ "order_item_{$i}_msrp" ] = $order_item['msrp'] ?? false;
	}

	// Local Pickup Plus - http://www.woothemes.com/products/local-pickup-plus/.
	if ( woo_ce_detect_export_plugin( 'local_pickup_plus' ) ) {
		$order_data[ "order_item_{$i}_pickup_location" ] = $order_item['pickup_location'] ?? false;
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/.
	if ( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		$order_data[ "order_item_{$i}_booking_id" ]             = $order_item['booking_id'] ?? false;
		$order_data[ "order_item_{$i}_booking_date" ]           = $order_item['booking_date'] ?? false;
		$order_data[ "order_item_{$i}_booking_type" ]           = $order_item['booking_type'] ?? false;
		$order_data[ "order_item_{$i}_booking_start_date" ]     = $order_item['booking_start_date'] ?? false;
		$order_data[ "order_item_{$i}_booking_start_time" ]     = $order_item['booking_start_time'] ?? false;
		$order_data[ "order_item_{$i}_booking_end_date" ]       = $order_item['booking_end_date'] ?? false;
		$order_data[ "order_item_{$i}_booking_end_time" ]       = $order_item['booking_end_time'] ?? false;
		$order_data[ "order_item_{$i}_booking_all_day" ]        = $order_item['booking_all_day'] ?? false;
		$order_data[ "order_item_{$i}_booking_resource_id" ]    = $order_item['booking_resource_id'] ?? false;
		$order_data[ "order_item_{$i}_booking_resource_title" ] = $order_item['booking_resource_title'] ?? false;
		$order_data[ "order_item_{$i}_booking_persons" ]        = $order_item['booking_persons'] ?? false;
		$order_data[ "order_item_{$i}_booking_persons_total" ]  = $order_item['booking_persons_total'] ?? false;
	}

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619.
	if ( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
		$tm_fields = woo_ce_get_extra_product_option_fields( $order_item['id'] );
		if ( ! empty( $tm_fields ) ) {
			foreach ( $tm_fields as $tm_field ) {

				if ( empty( $tm_field ) ) {
					continue;
                }

				if ( isset( $order_item[ "tm_{$tm_field['name']}" ] ) ) {
					$order_data[ "order_item_{$i}_tm_{$tm_field['name']}" ] = woo_ce_get_extra_product_option_value( $order_item['id'], $tm_field );
				}
				if ( apply_filters( 'woo_ce_enable_advanced_extra_product_options', false ) ) {
					$multiple_value_separator = apply_filters( 'woo_ce_get_extra_product_option_multiple_value_separator', "\n" );
					if ( ! empty( $tm_field['price'] ) ) {
						if ( isset( $order_item[ "tm_{$tm_field['name']}_cost" ] ) ) {
							$order_data[ "order_item_{$i}_tm_{$tm_field['name']}_cost" ] = apply_filters( 'woo_ce_get_extra_product_option_multiple_value_formatting', implode( $multiple_value_separator, $tm_field['price'] ), $tm_field, $order_item );
						}
					}
					if ( ! empty( $tm_field['quantity'] ) ) {
						if ( isset( $order_item[ "tm_{$tm_field['name']}_quantity" ] ) ) {
							$order_data[ "order_item_{$i}_tm_{$tm_field['name']}_quantity" ] = apply_filters( 'woo_ce_get_extra_product_option_multiple_value_formatting', implode( $multiple_value_separator, $tm_field['quantity'] ), $tm_field, $order_item );
						}
					}
				}
			}
		}
		unset( $tm_fields, $tm_field, $multiple_value_separator );
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields.
	if ( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		if ( ! get_option( 'wccf_migrated_to_20' ) ) {
			$options = get_option( 'rp_wccf_options' );
			if ( ! empty( $options ) ) {
				$options = $options[1] ?? false;
				if ( ! empty( $options ) ) {
					// Product Fields.
					$custom_fields = $options['product_fb_config'] ?? false;
					if ( ! empty( $custom_fields ) ) {
						foreach ( $custom_fields as $custom_field ) {
							$order_data[ "order_item_{$i}_wccf_{$custom_field['key']}" ] = $order_item[ "wccf_{$custom_field['key']}" ] ?? false;
                        }
						unset( $custom_fields, $custom_field );
					}
				}
				unset( $options );
			}
		} else {
			// Product Fields.
			$custom_fields = woo_ce_get_wccf_product_fields();
			if ( ! empty( $custom_fields ) ) {
				foreach ( $custom_fields as $custom_field ) {
					$key = get_post_meta( $custom_field->ID, 'key', true );
					$order_data[ "order_item_{$i}_wccf_{$key}" ] = $order_item[ "wccf_{$key}" ] ?? false;
				}
			}
			unset( $custom_fields, $custom_field, $key );
		}
	}

	// WooCommerce Product Custom Options Lite - https://wordpress.org/plugins/woocommerce-custom-options-lite/.
	if ( woo_ce_detect_export_plugin( 'wc_product_custom_options' ) ) {
		$custom_options = woo_ce_get_product_custom_options();
		if ( ! empty( $custom_options ) ) {
			foreach ( $custom_options as $custom_option ) {
				if ( isset( $order_item[ "pco_{$custom_option}" ] ) ) {
					$order_data[ "order_item_{$i}_pco_{$custom_option}" ] = $order_item[ "pco_{$custom_option}" ] ?? false;
                }
			}
		}
	}

	// Barcodes for WooCommerce - http://www.wolkenkraft.com/produkte/barcodes-fuer-woocommerce/.
	if ( woo_ce_detect_export_plugin( 'wc_barcodes' ) ) {
		$order_data[ "order_item_{$i}_barcode_type" ] = $order_item['barcode_type'] ?? false;
		$order_data[ "order_item_{$i}_barcode" ]      = $order_item['barcode'] ?? false;
	}

	// WooCommerce UPC, EAN, and ISBN - https://wordpress.org/plugins/woo-add-gtin/.
	if ( woo_ce_detect_export_plugin( 'woo_add_gtin' ) ) {
		$order_data[ "order_item_{$i}_gtin" ] = $order_item['gtin'] ?? false;
	}

	// WooCommerce Easy Booking - https://wordpress.org/plugins/woocommerce-easy-booking-system/.
	if ( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		$order_data[ "order_item_{$i}_booking_start_date" ] = $order_item['booking_start_date'] ?? false;
		$order_data[ "order_item_{$i}_booking_end_date" ]   = $order_item['booking_end_date'] ?? false;
	}

	// N-Media WooCommerce Personalized Product Meta Manager - http://najeebmedia.com/wordpress-plugin/woocommerce-personalized-product-option/.
	// PPOM for WooCommerce - https://wordpress.org/plugins/woocommerce-product-addon/.
	if (
		woo_ce_detect_export_plugin( 'wc_nm_personalizedproduct' ) ||
		woo_ce_detect_export_plugin( 'wc_ppom' )
	) {
		$custom_fields = woo_ce_get_nm_personalized_product_fields();
		if ( ! empty( $custom_fields ) ) {
			foreach ( $custom_fields as $custom_field ) {
				if ( isset( $order_item[ "nm_{$custom_field['name']}" ] ) ) {
					$order_data[ "order_item_{$i}_nm_{$custom_field['name']}" ] = $order_item[ "nm_{$custom_field['name']}" ] ?? false;
                }
			}
		}
	}

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/.
	if ( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		$order_data[ "order_item_{$i}_appointment_id" ]     = $order_item['appointment_id'] ?? false;
		$order_data[ "order_item_{$i}_booking_start_date" ] = $order_item['booking_start_date'] ?? false;
		$order_data[ "order_item_{$i}_booking_start_time" ] = $order_item['booking_start_time'] ?? false;
		$order_data[ "order_item_{$i}_booking_end_date" ]   = $order_item['booking_end_date'] ?? false;
		$order_data[ "order_item_{$i}_booking_end_time" ]   = $order_item['booking_end_time'] ?? false;
		$order_data[ "order_item_{$i}_booking_all_day" ]    = $order_item['booking_all_day'] ?? false;
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/.
	if ( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if ( ! empty( $wholesale_roles ) ) {
			foreach ( $wholesale_roles as $key => $wholesale_role ) {
				$order_data[ "order_item_{$i}_{$key}_wholesale_price" ] = $order_item[ "{$key}_wholesale_price" ] ?? '';
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	// FooEvents for WooCommerce - https://www.fooevents.com/.
	if ( woo_ce_detect_export_plugin( 'fooevents' ) ) {
		$order_data[ "order_item_{$i}_tickets_purchased" ] = $order_item['tickets_purchased'] ?? false;
	}

	// AliDropship for WooCommerce - https://alidropship.com/.
	if ( woo_ce_detect_export_plugin( 'alidropship' ) ) {
		$order_data[ "order_item_{$i}_ali_product_id" ]  = $order_item['ali_product_id'] ?? false;
		$order_data[ "order_item_{$i}_ali_product_url" ] = $order_item['ali_product_url'] ?? false;
		$order_data[ "order_item_{$i}_ali_store_url" ]   = $order_item['ali_store_url'] ?? false;
		$order_data[ "order_item_{$i}_ali_store_name" ]  = $order_item['ali_store_name'] ?? false;
	}

	// Bookings and Appointments For WooCommerce Premium - https://www.pluginhive.com/product/woocommerce-booking-and-appointments/.
	if ( woo_ce_detect_export_plugin( 'wc_bookings_appointments_pro' ) ) {
		$order_data[ "order_item_{$i}_session_date" ]   = $order_item['session_date'] ?? false;
		$order_data[ "order_item_{$i}_session_time" ]   = $order_item['session_time'] ?? false;
		$order_data[ "order_item_{$i}_booked_from" ]    = $order_item['booked_from'] ?? false;
		$order_data[ "order_item_{$i}_booking_cost" ]   = $order_item['booking_cost'] ?? false;
		$order_data[ "order_item_{$i}_booking_status" ] = $order_item['booking_status'] ?? false;
	}

	// Yith WooCommerce Subscription - https://yithemes.com/themes/plugins/yith-woocommerce-subscription/.
	if ( woo_ce_detect_export_plugin( 'yith_woocommerce_subscription' ) ) {
		$order_data[ "order_item_{$i}_yith_subscription_id" ]                 = $order_item['yith_subscription_id'] ?? false;
		$order_data[ "order_item_{$i}_yith_subscription_start_date" ]         = $order_item['yith_subscription_start_date'] ?? false;
		$order_data[ "order_item_{$i}_yith_subscription_payment_due_date" ]   = $order_item['yith_subscription_payment_due_date'] ?? false;
		$order_data[ "order_item_{$i}_yith_subscription_end_date" ]           = $order_item['yith_subscription_end_date'] ?? false;
		$order_data[ "order_item_{$i}_yith_subscription_expired_date" ]       = $order_item['yith_subscription_expired_date'] ?? false;
		$order_data[ "order_item_{$i}_yith_subscription_cancelled_date" ]     = $order_item['yith_subscription_cancelled_date'] ?? false;
		$order_data[ "order_item_{$i}_yith_subscription_cancelled_by" ]       = $order_item['yith_subscription_cancelled_by'] ?? false;
		$order_data[ "order_item_{$i}_yith_subscription_expired_pause_date" ] = $order_item['yith_subscription_expired_pause_date'] ?? false;
		$order_data[ "order_item_{$i}_yith_subscription_status" ]             = $order_item['yith_subscription_status'] ?? false;
		$order_data[ "order_item_{$i}_yith_subscription_recurring_price" ]    = $order_item['yith_subscription_recurring_price'] ?? false;
		$order_data[ "order_item_{$i}_yith_subscription_price_per" ]          = $order_item['yith_subscription_price_per'] ?? false;
		$order_data[ "order_item_{$i}_yith_subscription_trial_per" ]          = $order_item['yith_subscription_trial_per'] ?? false;
		$order_data[ "order_item_{$i}_yith_subscription_max_length" ]         = $order_item['yith_subscription_max_length'] ?? false;
		// $order_data[ "order_item_{$i}_yith_subscription_next_payment_due_date" ] = $order_item['yith_subscription_next_payment_due_date'];.
	}

	// WooCommerce Warranty Requests - https://woocommerce.com/products/warranty-requests/.
	if ( woo_ce_detect_export_plugin( 'wc_warranty' ) ) {
		$order_data[ "order_item_{$i}_wc_warranty_id" ]                    = $order_item['wc_warranty_id'] ?? false;
		$order_data[ "order_item_{$i}_wc_warranty_code" ]                  = $order_item['wc_warranty_code'] ?? false;
		$order_data[ "order_item_{$i}_wc_warranty_request_type" ]          = $order_item['wc_warranty_request_type'] ?? false;
		$order_data[ "order_item_{$i}_wc_warranty_request_tracking_code" ] = $order_item['wc_warranty_request_tracking_code'] ?? false;
		$order_data[ "order_item_{$i}_wc_warranty_shipping_label" ]        = $order_item['wc_warranty_shipping_label'] ?? false;
		$order_data[ "order_item_{$i}_wc_warranty_type" ]                  = $order_item['wc_warranty_type'] ?? false;
		$order_data[ "order_item_{$i}_wc_warranty_length" ]                = $order_item['wc_warranty_length'] ?? false;
		$order_data[ "order_item_{$i}_wc_warranty_value" ]                 = $order_item['wc_warranty_value'] ?? false;
		$order_data[ "order_item_{$i}_wc_warranty_duration" ]              = $order_item['wc_warranty_duration'] ?? false;
	}

	// Tax Rates.
	$tax_rates = woo_ce_get_order_tax_rates();
	if ( ! empty( $tax_rates ) ) {
		foreach ( $tax_rates as $tax_rate ) {
			if ( isset( $order_item[ "tax_rate_{$tax_rate['rate_id']}" ] ) ) {
				$order_data[ "order_item_{$i}_tax_rate_{$tax_rate['rate_id']}" ] = $order_item[ "tax_rate_{$tax_rate['rate_id']}" ] ?? false;
            }
		}
		unset( $tax_rates, $tax_rate );
	}

	// Variation Attributes.
	// Product Attributes.
	if ( apply_filters( 'woo_ce_enable_product_attributes', true ) ) {
		$attributes = woo_ce_get_product_attributes( 'attribute_name' );
		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $attribute ) {
				$key = rawurlencode( $attribute );
				if ( isset( $order_item[ "attribute_{$key}" ] ) ) {
					$order_data[ "order_item_{$i}_attribute_{$key}" ] = $order_item[ "attribute_{$key}" ] ?? false;
                }
				if ( isset( $order_item[ "product_attribute_{$key}" ] ) ) {
					$order_data[ "order_item_{$i}_product_attribute_{$key}" ] = $order_item[ "product_attribute_{$key}" ] ?? false;
                }
			}
			unset( $key );
		}
		unset( $attributes, $attribute );
	}

	// Custom Order Items fields.
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	if ( ! empty( $custom_order_items ) ) {
		foreach ( $custom_order_items as $custom_order_item ) {
			if ( ! empty( $custom_order_item ) ) {
				if ( isset( $order_item[ $custom_order_item ] ) ) {
					$order_data[ "order_item_{$i}_{$custom_order_item}" ] = woo_ce_format_custom_meta( $order_item[ $custom_order_item ] );
				}
			}
		}
	}

	// Custom Order Item Product fields.
	$custom_order_products = woo_ce_get_option( 'custom_order_products', '' );
	if ( ! empty( $custom_order_products ) ) {
		foreach ( $custom_order_products as $custom_order_product ) {
			if ( ! empty( $custom_order_product ) ) {
				if ( isset( $order_item[ $custom_order_product ] ) ) {
					$order_data[ "order_item_{$i}_{$custom_order_product}" ] = woo_ce_format_custom_meta( $order_item[ $custom_order_product ] );
				}
			}
		}
	}

	// Custom Product fields.
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if ( ! empty( $custom_products ) ) {
		foreach ( $custom_products as $custom_product ) {
			if ( ! empty( $custom_product ) ) {
				if ( isset( $order_item[ $custom_product ] ) ) {
					$order_data[ "order_item_{$i}_{$custom_product}" ] = woo_ce_format_custom_meta( $order_item[ $custom_product ] );
				}
			}
		}
	}

	return $order_data;
}
add_filter( 'woo_ce_order_items_unique', 'woo_ce_extend_order_items_unique', 10, 3 );

/**
 * Order items formatting: Unique fields exclusion.
 *
 * @param array $excluded_fields Excluded fields.
 * @param array $fields          Fields.
 */
function woo_ce_extend_order_items_unique_fields_exclusion( $excluded_fields = array(), $fields = '' ) {

	// Product Add-ons - http://www.woothemes.com/.
	if ( woo_ce_detect_export_plugin( 'product_addons' ) ) {
		$product_addons = woo_ce_get_product_addons();
		if ( ! empty( $product_addons ) ) {
			foreach ( $product_addons as $product_addon ) {
				if ( isset( $fields[ "order_items_product_addon_{$product_addon->post_name}" ] ) ) {
					$excluded_fields[] = "order_items_product_addon_{$product_addon->post_name}";
				}
			}
			unset( $product_addons, $product_addon );
		}
	}

	// Gravity Forms - http://woothemes.com/woocommerce.
	if ( woo_ce_detect_export_plugin( 'gravity_forms' ) && woo_ce_detect_export_plugin( 'woocommerce_gravity_forms' ) ) {
		// Check if there are any Products linked to Gravity Forms.
		$gf_fields = woo_ce_get_gravity_forms_fields();
		if ( ! empty( $gf_fields ) ) {
			if ( isset( $fields['order_items_gf_form_id'] ) ) {
				$excluded_fields[] = 'order_items_gf_form_id';
            }
			if ( isset( $fields['order_items_gf_form_label'] ) ) {
				$excluded_fields[] = 'order_items_gf_form_label';
            }
			foreach ( $gf_fields as $gf_field ) {
				if ( isset( $fields[ "order_items_gf_{$gf_field['formId']}_{$gf_field['id']}" ] ) ) {
					$excluded_fields[] = "order_items_gf_{$gf_field['formId']}_{$gf_field['id']}";
                }
			}
		}
		unset( $gf_fields, $gf_field );
	}

	// WooCommerce Checkout Add-Ons - http://www.skyverge.com/product/woocommerce-checkout-add-ons/.
	if ( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
		if ( isset( $fields['order_items_checkout_addon_id'] ) ) {
			$excluded_fields[] = 'order_items_checkout_addon_id';
        }
		if ( isset( $fields['order_items_checkout_addon_label'] ) ) {
			$excluded_fields[] = 'order_items_checkout_addon_label';
        }
		if ( isset( $fields['order_items_checkout_addon_value'] ) ) {
			$excluded_fields[] = 'order_items_checkout_addon_value';
        }
	}

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/.
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/.
	if ( woo_ce_detect_product_brands() ) {
		if ( isset( $fields['order_items_brand'] ) ) {
			$excluded_fields[] = 'order_items_brand';
        }
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/.
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/.
	if ( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		if ( isset( $fields['order_items_vendor'] ) ) {
			$excluded_fields[] = 'order_items_vendor';
        }
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/.
	if ( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		if ( isset( $fields['order_items_cost_of_goods'] ) ) {
			$excluded_fields[] = 'order_items_cost_of_goods';
        }
		if ( isset( $fields['order_items_total_cost_of_goods'] ) ) {
			$excluded_fields[] = 'order_items_total_cost_of_goods';
        }
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590.
	if ( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		if ( isset( $fields['order_items_posr'] ) ) {
			$excluded_fields[] = 'order_items_posr';
        }
	}

	// WC Fields Factory - https://wordpress.org/plugins/wc-fields-factory/.
	if ( woo_ce_detect_export_plugin( 'wc_fields_factory' ) ) {
		// Product Fields.
		$product_fields = woo_ce_get_wcff_product_fields();
		if ( ! empty( $product_fields ) ) {
			foreach ( $product_fields as $product_field ) {
				$exluded_fields[] = "order_items_{$product_field['name']}";
			}
		}
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/.
	if ( woo_ce_detect_export_plugin( 'wc_msrp' ) ) {
		if ( isset( $fields['order_items_msrp'] ) ) {
			$excluded_fields[] = 'order_items_msrp';
        }
	}

	// Local Pickup Plus - http://www.woothemes.com/products/local-pickup-plus/.
	if ( woo_ce_detect_export_plugin( 'local_pickup_plus' ) ) {
		if ( isset( $fields['order_items_pickup_location'] ) ) {
			$excluded_fields[] = 'order_items_pickup_location';
        }
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/.
	if ( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		if ( isset( $fields['order_items_booking_id'] ) ) {
			$excluded_fields[] = 'order_items_booking_id';
        }
		if ( isset( $fields['order_items_booking_date'] ) ) {
			$excluded_fields[] = 'order_items_booking_date';
        }
		if ( isset( $fields['order_items_booking_type'] ) ) {
			$excluded_fields[] = 'order_items_booking_type';
        }
		if ( isset( $fields['order_items_booking_start_date'] ) ) {
			$excluded_fields[] = 'order_items_booking_start_date';
        }
		if ( isset( $fields['order_items_booking_start_time'] ) ) {
			$excluded_fields[] = 'order_items_booking_start_time';
        }
		if ( isset( $fields['order_items_booking_end_date'] ) ) {
			$excluded_fields[] = 'order_items_booking_end_date';
        }
		if ( isset( $fields['order_items_booking_end_time'] ) ) {
			$excluded_fields[] = 'order_items_booking_end_time';
        }
		if ( isset( $fields['order_items_booking_all_day'] ) ) {
			$excluded_fields[] = 'order_items_booking_all_day';
        }
		if ( isset( $fields['order_items_booking_resource_id'] ) ) {
			$excluded_fields[] = 'order_items_booking_resource_id';
        }
		if ( isset( $fields['order_items_booking_resource_title'] ) ) {
			$excluded_fields[] = 'order_items_booking_resource_title';
        }
		if ( isset( $fields['order_items_booking_persons'] ) ) {
			$excluded_fields[] = 'order_items_booking_persons';
        }
		if ( isset( $fields['order_items_booking_persons_total'] ) ) {
			$excluded_fields[] = 'order_items_booking_persons_total';
        }
	}

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619.
	if ( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
		$tm_fields = woo_ce_get_extra_product_option_fields();
		if ( ! empty( $tm_fields ) ) {
			foreach ( $tm_fields as $tm_field ) {

				if ( empty( $tm_field ) ) {
					continue;
                }

				if ( isset( $fields[ 'order_items_tm_%s' . $tm_field['name'] ] ) ) {
					$excluded_fields[] = 'order_items_tm_%s' . $tm_field['name'];
                }
				if ( apply_filters( 'woo_ce_enable_advanced_extra_product_options', false ) ) {
					if ( isset( $fields[ 'order_items_tm_' . $tm_field['name'] . '_cost' ] ) ) {
						$excluded_fields[] = 'order_items_tm_' . $tm_field['name'] . '_cost';
                    }
					if ( isset( $fields[ 'order_items_tm_' . $tm_field['name'] . '_quantity' ] ) ) {
						$excluded_fields[] = 'order_items_tm_' . $tm_field['name'] . '_quantity';
                    }
				}
			}
		}
		unset( $tm_fields, $tm_field );
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields.
	if ( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		if ( ! get_option( 'wccf_migrated_to_20' ) ) {
			$options = get_option( 'rp_wccf_options' );
			if ( ! empty( $options ) ) {
				$options = $options[1] ?? false;
				if ( ! empty( $options ) ) {
					// Product Fields.
					$custom_fields = $options['product_fb_config'] ?? false;
					if ( ! empty( $custom_fields ) ) {
						foreach ( $custom_fields as $custom_field ) {
							if ( isset( $fields[ "order_items_wccf_{$custom_field['key']}" ] ) ) {
								$excluded_fields[] = "order_items_wccf_{$custom_field['key']}";
                            }
						}
						unset( $custom_fields, $custom_field );
					}
				}
				unset( $options );
			}
		} else {
			// Product Fields.
			$custom_fields = woo_ce_get_wccf_product_fields();
			if ( ! empty( $custom_fields ) ) {
				foreach ( $custom_fields as $custom_field ) {
					$key = get_post_meta( $custom_field->ID, 'key', true );
					if ( isset( $fields[ "order_items_wccf_{$key}" ] ) ) {
						$excluded_fields[] = "order_items_wccf_{$key}";
                    }
				}
			}
			unset( $custom_fields, $custom_field, $key );
		}
	}

	// WooCommerce Product Custom Options Lite - https://wordpress.org/plugins/woocommerce-custom-options-lite/.
	if ( woo_ce_detect_export_plugin( 'wc_product_custom_options' ) ) {
		$custom_options = woo_ce_get_product_custom_options();
		if ( ! empty( $custom_options ) ) {
			foreach ( $custom_options as $custom_option ) {
				if ( isset( $fields[ "order_items_pco_{$custom_option}" ] ) ) {
					$excluded_fields[] = "order_items_pco_{$custom_option}";
                }
			}
		}
	}

	// Barcodes for WooCommerce - http://www.wolkenkraft.com/produkte/barcodes-fuer-woocommerce/.
	if ( woo_ce_detect_export_plugin( 'wc_barcodes' ) ) {
		if ( isset( $fields['order_items_barcode_type'] ) ) {
			$excluded_fields[] = 'order_items_barcode_type';
        }
		if ( isset( $fields['order_items_barcode'] ) ) {
			$excluded_fields[] = 'order_items_barcode';
        }
	}

	// WooCommerce UPC, EAN, and ISBN - https://wordpress.org/plugins/woo-add-gtin/.
	if ( woo_ce_detect_export_plugin( 'woo_add_gtin' ) ) {
		if ( isset( $fields['order_items_gtin'] ) ) {
			$excluded_fields[] = 'order_items_gtin';
        }
	}

	// WooCommerce Easy Booking - https://wordpress.org/plugins/woocommerce-easy-booking-system/.
	if ( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		if ( isset( $fields['order_items_booking_start_date'] ) ) {
			$excluded_fields[] = 'order_items_booking_start_date';
        }
		if ( isset( $fields['order_items_booking_end_date'] ) ) {
			$excluded_fields[] = 'order_items_booking_end_date';
        }
	}

	// N-Media WooCommerce Personalized Product Meta Manager - http://najeebmedia.com/wordpress-plugin/woocommerce-personalized-product-option/.
	// PPOM for WooCommerce - https://wordpress.org/plugins/woocommerce-product-addon/.
	if (
		woo_ce_detect_export_plugin( 'wc_nm_personalizedproduct' ) ||
		woo_ce_detect_export_plugin( 'wc_ppom' )
	) {
		$custom_fields = woo_ce_get_nm_personalized_product_fields();
		if ( ! empty( $custom_fields ) ) {
			foreach ( $custom_fields as $custom_field ) {
				if ( isset( $fields[ "order_items_nm_{$custom_field['name']}" ] ) ) {
					$excluded_fields[] = "order_items_nm_{$custom_field['name']}";
                }
			}
		}
	}

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/.
	if ( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		if ( isset( $fields['order_items_appointment_id'] ) ) {
			$excluded_fields[] = 'order_items_appointment_id';
        }
		if ( isset( $fields['order_items_booking_start_date'] ) ) {
			$excluded_fields[] = 'order_items_booking_start_date';
        }
		if ( isset( $fields['order_items_booking_start_time'] ) ) {
			$excluded_fields[] = 'order_items_booking_start_time';
        }
		if ( isset( $fields['order_items_booking_end_date'] ) ) {
			$excluded_fields[] = 'order_items_booking_end_date';
        }
		if ( isset( $fields['order_items_booking_end_time'] ) ) {
			$excluded_fields[] = 'order_items_booking_end_time';
        }
		if ( isset( $fields['order_items_booking_all_day'] ) ) {
			$excluded_fields[] = 'order_items_booking_all_day';
        }
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/.
	if ( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if ( ! empty( $wholesale_roles ) ) {
			foreach ( $wholesale_roles as $key => $wholesale_role ) {
				if ( isset( $fields[ "order_items_{$key}_wholesale_price" ] ) ) {
					$excluded_fields[] = "order_items_{$key}_wholesale_price";
                }
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	// FooEvents for WooCommerce - https://www.fooevents.com/.
	if ( woo_ce_detect_export_plugin( 'fooevents' ) ) {
		if ( isset( $fields['order_items_tickets_purchased'] ) ) {
			$excluded_fields[] = 'order_items_tickets_purchased';
        }
		if ( isset( $fields['order_items_is_event'] ) ) {
			$excluded_fields[] = 'order_items_is_event';
        }
		if ( isset( $fields['order_items_event_date'] ) ) {
			$excluded_fields[] = 'order_items_event_date';
        }
		if ( isset( $fields['order_items_event_start_time'] ) ) {
			$excluded_fields[] = 'order_items_event_start_time';
        }
		if ( isset( $fields['order_items_event_end_time'] ) ) {
			$excluded_fields[] = 'order_items_event_end_time';
        }
		if ( isset( $fields['order_items_event_venue'] ) ) {
			$excluded_fields[] = 'order_items_event_venue';
        }
		if ( isset( $fields['order_items_event_gps'] ) ) {
			$excluded_fields[] = 'order_items_event_gps';
        }
		if ( isset( $fields['order_items_event_googlemaps'] ) ) {
			$excluded_fields[] = 'order_items_event_googlemaps';
        }
		if ( isset( $fields['order_items_event_directions'] ) ) {
			$excluded_fields[] = 'order_items_event_directions';
        }
		if ( isset( $fields['order_items_event_phone'] ) ) {
			$excluded_fields[] = 'order_items_event_phone';
        }
		if ( isset( $fields['order_items_event_email'] ) ) {
			$excluded_fields[] = 'order_items_event_email';
        }
		if ( isset( $fields['order_items_event_ticket_logo'] ) ) {
			$excluded_fields[] = 'order_items_event_ticket_logo';
        }
		if ( isset( $fields['order_items_event_ticket_subject'] ) ) {
			$excluded_fields[] = 'order_items_event_ticket_subject';
        }
		if ( isset( $fields['order_items_event_ticket_text'] ) ) {
			$excluded_fields[] = 'order_items_event_ticket_text';
        }
		if ( isset( $fields['order_items_event_ticket_thankyou_text'] ) ) {
			$excluded_fields[] = 'order_items_event_ticket_thankyou_text';
        }
		if ( isset( $fields['order_items_event_ticket_background_color'] ) ) {
			$excluded_fields[] = 'order_items_event_ticket_background_color';
        }
		if ( isset( $fields['order_items_event_ticket_button_color'] ) ) {
			$excluded_fields[] = 'order_items_event_ticket_button_color';
        }
		if ( isset( $fields['order_items_event_ticket_text_color'] ) ) {
			$excluded_fields[] = 'order_items_event_ticket_text_color';
        }
	}

	// AliDropship for WooCommerce - https://alidropship.com/.
	if ( woo_ce_detect_export_plugin( 'alidropship' ) ) {
		if ( isset( $fields['order_items_ali_product_id'] ) ) {
			$excluded_fields[] = 'order_items_ali_product_id';
        }
		if ( isset( $fields['order_items_ali_product_url'] ) ) {
			$excluded_fields[] = 'order_items_ali_product_url';
        }
		if ( isset( $fields['order_items_ali_store_url'] ) ) {
			$excluded_fields[] = 'order_items_ali_store_url';
        }
		if ( isset( $fields['order_items_ali_store_name'] ) ) {
			$excluded_fields[] = 'order_items_ali_store_name';
        }
	}

	// Bookings and Appointments For WooCommerce Premium - https://www.pluginhive.com/product/woocommerce-booking-and-appointments/.
	if ( woo_ce_detect_export_plugin( 'wc_bookings_appointments_pro' ) ) {
		if ( isset( $fields['order_items_session_date'] ) ) {
			$excluded_fields[] = 'order_items_session_date';
        }
		if ( isset( $fields['order_items_session_time'] ) ) {
			$excluded_fields[] = 'order_items_session_time';
        }
		if ( isset( $fields['order_items_booked_from'] ) ) {
			$excluded_fields[] = 'order_items_booked_from';
        }
		if ( isset( $fields['order_items_booking_cost'] ) ) {
			$excluded_fields[] = 'order_items_booking_cost';
        }
		if ( isset( $fields['order_items_booking_status'] ) ) {
			$excluded_fields[] = 'order_items_booking_status';
        }
	}

	// Yith WooCommerce Subscription - https://yithemes.com/themes/plugins/yith-woocommerce-subscription/.
	if ( woo_ce_detect_export_plugin( 'yith_woocommerce_subscription' ) ) {
		if ( isset( $fields['yith_subscription_id'] ) ) {
			$excluded_fields[] = 'yith_subscription_id';
        }
		if ( isset( $fields['yith_subscription_start_date'] ) ) {
			$excluded_fields[] = 'yith_subscription_start_date';
        }
		if ( isset( $fields['yith_subscription_payment_due_date'] ) ) {
			$excluded_fields[] = 'yith_subscription_payment_due_date';
        }
		if ( isset( $fields['yith_subscription_end_date'] ) ) {
			$excluded_fields[] = 'yith_subscription_end_date';
        }
		if ( isset( $fields['yith_subscription_expired_date'] ) ) {
			$excluded_fields[] = 'yith_subscription_expired_date';
        }
		if ( isset( $fields['yith_subscription_cancelled_date'] ) ) {
			$excluded_fields[] = 'yith_subscription_cancelled_date';
        }
		if ( isset( $fields['yith_subscription_cancelled_by'] ) ) {
			$excluded_fields[] = 'yith_subscription_cancelled_by';
        }
		if ( isset( $fields['yith_subscription_expired_pause_date'] ) ) {
			$excluded_fields[] = 'yith_subscription_expired_pause_date';
        }
		if ( isset( $fields['yith_subscription_status'] ) ) {
			$excluded_fields[] = 'yith_subscription_status';
        }
		if ( isset( $fields['yith_subscription_recurring_price'] ) ) {
			$excluded_fields[] = 'yith_subscription_recurring_price';
        }
		if ( isset( $fields['yith_subscription_price_per'] ) ) {
			$excluded_fields[] = 'yith_subscription_price_per';
        }
		if ( isset( $fields['yith_subscription_trial_per'] ) ) {
			$excluded_fields[] = 'yith_subscription_trial_per';
        }
		if ( isset( $fields['yith_subscription_max_length'] ) ) {
			$excluded_fields[] = 'yith_subscription_max_length';
        }
		// if( isset( $fields['yith_subscription_next_payment_due_date'] ) ).
			// $excluded_fields[] = 'yith_subscription_next_payment_due_date';.
	}

	// WooCommerce Warranty Requests - https://woocommerce.com/products/warranty-requests/.
	if ( woo_ce_detect_export_plugin( 'wc_warranty' ) ) {
		if ( isset( $fields['wc_warranty_id'] ) ) {
			$excluded_fields[] = 'wc_warranty_id';
        }
		if ( isset( $fields['wc_warranty_code'] ) ) {
			$excluded_fields[] = 'wc_warranty_code';
        }
		if ( isset( $fields['wc_warranty_request_type'] ) ) {
			$excluded_fields[] = 'wc_warranty_request_type';
        }
		if ( isset( $fields['wc_warranty_return_tracking_code'] ) ) {
			$excluded_fields[] = 'wc_warranty_return_tracking_code';
        }
		if ( isset( $fields['wc_warranty_request_tracking_code'] ) ) {
			$excluded_fields[] = 'wc_warranty_request_tracking_code';
        }
		if ( isset( $fields['wc_warranty_shipping_label'] ) ) {
			$excluded_fields[] = 'wc_warranty_shipping_label';
        }
		if ( isset( $fields['wc_warranty_type'] ) ) {
			$excluded_fields[] = 'wc_warranty_type';
        }
		if ( isset( $fields['wc_warranty_length'] ) ) {
			$excluded_fields[] = 'wc_warranty_length';
        }
		if ( isset( $fields['wc_warranty_value'] ) ) {
			$excluded_fields[] = 'wc_warranty_value';
        }
		if ( isset( $fields['wc_warranty_duration'] ) ) {
			$excluded_fields[] = 'wc_warranty_duration';
        }
	}

	// Tax Rates.
	$tax_rates = woo_ce_get_order_tax_rates();
	if ( ! empty( $tax_rates ) ) {
		foreach ( $tax_rates as $tax_rate ) {
			if ( isset( $fields[ "order_items_tax_rate_{$tax_rate['rate_id']}" ] ) ) {
				$excluded_fields[] = "order_items_tax_rate_{$tax_rate['rate_id']}";
            }
		}
	}
	unset( $tax_rates, $tax_rate );

	// Variation Attributes.
	// Product Attributes.
	if ( apply_filters( 'woo_ce_enable_product_attributes', true ) ) {
		$attributes = woo_ce_get_product_attributes( 'attribute_name' );
		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $attribute ) {
				$key = rawurlencode( $attribute );
				if ( isset( $fields[ "order_items_attribute_{$key}" ] ) ) {
					$excluded_fields[] = "order_items_attribute_{$key}";
                }
				if ( isset( $fields[ "order_items_product_attribute_{$key}" ] ) ) {
					$excluded_fields[] = "order_items_product_attribute_{$key}";
                }
			}
			unset( $key );
		}
		unset( $attributes, $attribute );
	}

	// Custom Order Items fields.
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	if ( ! empty( $custom_order_items ) ) {
		foreach ( $custom_order_items as $custom_order_item ) {
			if ( ! empty( $custom_order_item ) ) {
				if ( isset( $fields[ "order_items_{$custom_order_item}" ] ) ) {
					$excluded_fields[] = "order_items_{$custom_order_item}";
                }
			}
		}
	}
	unset( $custom_order_items, $custom_order_item );

	// Custom Order Item Product fields.
	$custom_order_products = woo_ce_get_option( 'custom_order_products', '' );
	if ( ! empty( $custom_order_products ) ) {
		foreach ( $custom_order_products as $custom_order_product ) {
			if ( isset( $fields[ "order_items_{$custom_order_product}" ] ) ) {
				$excluded_fields[] = "order_items_{$custom_order_product}";
            }
		}
	}
	unset( $custom_order_products, $custom_order_product );

	// Custom Product fields.
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if ( ! empty( $custom_products ) ) {
		foreach ( $custom_products as $custom_product ) {
			if ( isset( $fields[ "order_items_{$custom_product}" ] ) ) {
				$excluded_fields[] = "order_items_{$custom_product}";
            }
		}
	}
	unset( $custom_products, $custom_product );

	return $excluded_fields;
}
add_filter( 'woo_ce_add_unique_order_item_fields_exclusion', 'woo_ce_extend_order_items_unique_fields_exclusion', 10, 2 );

/**
 * This prepares the Order columns for the 'unique' Order Item formatting selection.
 *
 * @param  array $fields The Order fields.
 * @param  int   $i      The Order Item index.
 */
function woo_ce_unique_order_item_fields_on( $fields = array(), $i = 0 ) {

	// Product Add-ons - http://www.woothemes.com/.
	if ( woo_ce_detect_export_plugin( 'product_addons' ) ) {
		$product_addons = woo_ce_get_product_addons();
		if ( ! empty( $product_addons ) ) {
			foreach ( $product_addons as $product_addon ) {
				if ( isset( $fields[ "order_items_product_addon_{$product_addon->post_name}" ] ) ) {
					$fields[ "order_item_{$i}_product_addon_{$product_addon->post_name}" ] = 'on';
                }
			}
		}
	}

	// Gravity Forms - http://woothemes.com/woocommerce.
	if ( woo_ce_detect_export_plugin( 'gravity_forms' ) && woo_ce_detect_export_plugin( 'woocommerce_gravity_forms' ) ) {
		// Check if there are any Products linked to Gravity Forms.
		if ( isset( $fields['order_items_gf_form_id'] ) ) {
			$fields[ "order_item_{$i}_gf_form_id" ] = 'on';
        }
		if ( isset( $fields['order_items_gf_form_label'] ) ) {
			$fields[ "order_item_{$i}_gf_form_label" ] = 'on';
        }
		// Check if there are any Products linked to Gravity Forms.
		$gf_fields = woo_ce_get_gravity_forms_fields();
		if ( ! empty( $gf_fields ) ) {
			foreach ( $gf_fields as $key => $gf_field ) {
				if ( isset( $fields[ "order_items_gf_{$gf_field['formId']}_{$gf_field['id']}" ] ) ) {
					$fields[ "order_item_{$i}_gf_{$gf_field['formId']}_{$gf_field['id']}" ] = 'on';
                }
			}
			unset( $gf_fields, $gf_field );
		}
	}

	// WooCommerce Checkout Add-Ons - http://www.skyverge.com/product/woocommerce-checkout-add-ons/.
	if ( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
		if ( isset( $fields['order_items_checkout_addon_id'] ) ) {
			$fields[ "order_item_{$i}_checkout_addon_id" ] = 'on';
        }
		if ( isset( $fields['order_items_checkout_addon_label'] ) ) {
			$fields[ "order_item_{$i}_checkout_addon_label" ] = 'on';
        }
		if ( isset( $fields['order_items_checkout_addon_value'] ) ) {
			$fields[ "order_item_{$i}_checkout_addon_value" ] = 'on';
        }
	}

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/.
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/.
	if ( woo_ce_detect_product_brands() ) {
		if ( isset( $fields['order_items_brand'] ) ) {
			$fields[ "order_item_{$i}_brand" ] = 'on';
        }
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/.
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/.
	if ( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		if ( isset( $fields['order_items_vendor'] ) ) {
			$fields[ "order_item_{$i}_vendor" ] = 'on';
        }
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/.
	if ( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		if ( isset( $fields['order_items_cost_of_goods'] ) ) {
			$fields[ "order_item_{$i}_cost_of_goods" ] = 'on';
        }
		if ( isset( $fields['order_items_total_cost_of_goods'] ) ) {
			$fields[ "order_item_{$i}_total_cost_of_goods" ] = 'on';
        }
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590.
	if ( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		if ( isset( $fields['order_items_posr'] ) ) {
			$fields[ "order_item_{$i}_posr" ] = 'on';
        }
	}

	// WC Fields Factory - https://wordpress.org/plugins/wc-fields-factory/.
	if ( woo_ce_detect_export_plugin( 'wc_fields_factory' ) ) {
		// Product Fields.
		$product_fields = woo_ce_get_wcff_product_fields();
		if ( ! empty( $product_fields ) ) {
			foreach ( $product_fields as $product_field ) {
				if ( isset( $fields[ "order_items_{$product_field['name']}" ] ) ) {
					$fields[ "order_item_{$i}_{$product_field['name']}" ] = 'on';
                }
			}
		}
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/.
	if ( woo_ce_detect_export_plugin( 'wc_msrp' ) ) {
		if ( isset( $fields['order_items_msrp'] ) ) {
			$fields[ "order_item_{$i}_msrp" ] = 'on';
        }
	}

	// Local Pickup Plus - http://www.woothemes.com/products/local-pickup-plus/.
	if ( woo_ce_detect_export_plugin( 'local_pickup_plus' ) ) {
		if ( isset( $fields['order_items_pickup_location'] ) ) {
			$fields[ "order_item_{$i}_pickup_location" ] = 'on';
        }
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/.
	if ( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		if ( isset( $fields['order_items_booking_id'] ) ) {
			$fields[ "order_item_{$i}_booking_id" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_date'] ) ) {
			$fields[ "order_item_{$i}_booking_date" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_type'] ) ) {
			$fields[ "order_item_{$i}_booking_type" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_start_date'] ) ) {
			$fields[ "order_item_{$i}_booking_start_date" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_start_time'] ) ) {
			$fields[ "order_item_{$i}_booking_start_time" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_start_date'] ) ) {
			$fields[ "order_item_{$i}_booking_start_date" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_start_time'] ) ) {
			$fields[ "order_item_{$i}_booking_start_time" ] = 'on';
        }
	}

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619.
	if ( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
		$tm_fields = woo_ce_get_extra_product_option_fields();
		if ( ! empty( $tm_fields ) ) {
			foreach ( $tm_fields as $tm_field ) {

				if ( empty( $tm_field ) ) {
					continue;
                }

				if ( isset( $fields[ "order_items_tm_{$tm_field['name']}" ] ) ) {
					$fields[ "order_item_{$i}_tm_{$tm_field['name']}" ] = 'on';
                }
				if ( apply_filters( 'woo_ce_enable_advanced_extra_product_options', false ) ) {
					if ( isset( $fields[ "order_items_tm_{$tm_field['name']}_cost" ] ) ) {
						$fields[ "order_item_{$i}_tm{$tm_field['name']}_cost" ] = 'on';
                    }
					if ( isset( $fields[ "order_items_tm_{$tm_field['name']}_quantity" ] ) ) {
						$fields[ "order_item_{$i}_tm{$tm_field['name']}_quantity" ] = 'on';
                    }
				}
			}
		}
		unset( $tm_fields, $tm_field );
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields.
	if ( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		$meta_type = 'order_item';
		if ( ! get_option( 'wccf_migrated_to_20' ) ) {
			$options = get_option( 'rp_wccf_options' );
			if ( ! empty( $options ) ) {
				$options = ( isset( $options[1] ) ? $options[1] : false );
				if ( ! empty( $options ) ) {
					// Product Fields.
					$custom_fields = ( isset( $options['product_fb_config'] ) ? $options['product_fb_config'] : false );
					if ( ! empty( $custom_fields ) ) {
						foreach ( $custom_fields as $custom_field ) {
							if ( isset( $fields[ "order_items_wccf_{$custom_field['key']}" ] ) ) {
								$fields[ "order_item_{$i}_wccf_{$custom_field['key']}" ] = 'on';
                            }
						}
						unset( $custom_fields, $custom_field );
					}
				}
				unset( $options );
			}
		} else {
			// Product Fields.
			$custom_fields = woo_ce_get_wccf_product_fields();
			if ( ! empty( $custom_fields ) ) {
				foreach ( $custom_fields as $custom_field ) {
					$key = get_post_meta( $custom_field->ID, 'key', true );
					if ( isset( $fields[ "order_items_wccf_{$key}" ] ) ) {
						$fields[ "order_item_{$i}_wccf_{$key}" ] = 'on';
                    }
				}
			}
			unset( $custom_fields, $custom_field, $key );
		}
	}

	// WooCommerce Easy Booking - https://wordpress.org/plugins/woocommerce-easy-booking-system/.
	if ( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		if ( isset( $fields['order_items_booking_start_date'] ) ) {
			$fields[ "order_item_{$i}_booking_start_date" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_end_date'] ) ) {
			$fields[ "order_item_{$i}_booking_end_date" ] = 'on';
        }
	}

	// N-Media WooCommerce Personalized Product Meta Manager - http://najeebmedia.com/wordpress-plugin/woocommerce-personalized-product-option/.
	// PPOM for WooCommerce - https://wordpress.org/plugins/woocommerce-product-addon/.

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/.
	if ( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		if ( isset( $fields['order_items_appointment_id'] ) ) {
			$fields[ "order_item_{$i}_appointment_id" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_start_date'] ) ) {
			$fields[ "order_item_{$i}_booking_start_date" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_start_time'] ) ) {
			$fields[ "order_item_{$i}_booking_start_time" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_end_date'] ) ) {
			$fields[ "order_item_{$i}_booking_end_date" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_end_time'] ) ) {
			$fields[ "order_item_{$i}_booking_end_time" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_all_day'] ) ) {
			$fields[ "order_item_{$i}_booking_all_day" ] = 'on';
        }
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/.
	if ( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if ( ! empty( $wholesale_roles ) ) {
			foreach ( $wholesale_roles as $key => $wholesale_role ) {
				if ( isset( $fields[ "order_items_{$key}_wholesale_price" ] ) ) {
					$fields[ "order_item_{$i}_{$key}_wholesale_price" ] = 'on';
                }
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	// FooEvents for WooCommerce - https://www.fooevents.com/.
	if ( woo_ce_detect_export_plugin( 'fooevents' ) ) {
		if ( isset( $fields['order_items_tickets_purchased'] ) ) {
			$fields[ "order_item_{$i}_tickets_purchased" ] = 'on';
        }
		if ( isset( $fields['order_items_is_event'] ) ) {
			$fields[ "order_item_{$i}_is_event" ] = 'on';
        }
		if ( isset( $fields['order_items_event_date'] ) ) {
			$fields[ "order_item_{$i}_event_date" ] = 'on';
        }
		if ( isset( $fields['order_items_event_start_time'] ) ) {
			$fields[ "order_item_{$i}_event_start_time" ] = 'on';
        }
		if ( isset( $fields['order_items_event_end_time'] ) ) {
			$fields[ "order_item_{$i}_event_end_time" ] = 'on';
        }
		if ( isset( $fields['order_items_event_venue'] ) ) {
			$fields[ "order_item_{$i}_event_venue" ] = 'on';
        }
		if ( isset( $fields['order_items_event_gps'] ) ) {
			$fields[ "order_item_{$i}_event_gps" ] = 'on';
        }
		if ( isset( $fields['order_items_event_googlemaps'] ) ) {
			$fields[ "order_item_{$i}_event_googlemaps" ] = 'on';
        }
		if ( isset( $fields['order_items_event_directions'] ) ) {
			$fields[ "order_item_{$i}_event_directions" ] = 'on';
        }
		if ( isset( $fields['order_items_event_phone'] ) ) {
			$fields[ "order_item_{$i}_event_phone" ] = 'on';
        }
		if ( isset( $fields['order_items_event_email'] ) ) {
			$fields[ "order_item_{$i}_event_email" ] = 'on';
        }
		if ( isset( $fields['order_items_event_ticket_logo'] ) ) {
			$fields[ "order_item_{$i}_event_ticket_logo" ] = 'on';
        }
		if ( isset( $fields['order_items_event_ticket_subject'] ) ) {
			$fields[ "order_item_{$i}_event_ticket_subject" ] = 'on';
        }
		if ( isset( $fields['order_items_event_ticket_text'] ) ) {
			$fields[ "order_item_{$i}_event_ticket_text" ] = 'on';
        }
		if ( isset( $fields['order_items_event_ticket_thankyou_text'] ) ) {
			$fields[ "order_item_{$i}_event_ticket_thankyou_text" ] = 'on';
        }
		if ( isset( $fields['order_items_event_ticket_background_color'] ) ) {
			$fields[ "order_item_{$i}_event_ticket_background_color" ] = 'on';
        }
		if ( isset( $fields['order_items_event_ticket_button_color'] ) ) {
			$fields[ "order_item_{$i}_event_ticket_button_color" ] = 'on';
        }
		if ( isset( $fields['order_items_event_ticket_text_color'] ) ) {
			$fields[ "order_item_{$i}_event_ticket_text_color" ] = 'on';
        }
	}

	// AliDropship for WooCommerce - https://alidropship.com/.
	if ( woo_ce_detect_export_plugin( 'alidropship' ) ) {
		if ( isset( $fields['order_items_ali_product_id'] ) ) {
			$fields[ "order_item_{$i}_ali_product_id" ] = 'on';
        }
		if ( isset( $fields['order_items_ali_product_url'] ) ) {
			$fields[ "order_item_{$i}_ali_product_url" ] = 'on';
        }
		if ( isset( $fields['order_items_ali_store_url'] ) ) {
			$fields[ "order_item_{$i}_ali_store_url" ] = 'on';
        }
		if ( isset( $fields['order_items_ali_store_name'] ) ) {
			$fields[ "order_item_{$i}_ali_store_name" ] = 'on';
        }
	}

	// Bookings and Appointments For WooCommerce Premium - https://www.pluginhive.com/product/woocommerce-booking-and-appointments/.
	if ( woo_ce_detect_export_plugin( 'wc_bookings_appointments_pro' ) ) {
		if ( isset( $fields['order_items_session_date'] ) ) {
			$fields[ "order_item_{$i}_session_date" ] = 'on';
        }
		if ( isset( $fields['order_items_session_time'] ) ) {
			$fields[ "order_item_{$i}_session_time" ] = 'on';
        }
		if ( isset( $fields['order_items_booked_from'] ) ) {
			$fields[ "order_item_{$i}_booked_from" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_cost'] ) ) {
			$fields[ "order_item_{$i}_booking_cost" ] = 'on';
        }
		if ( isset( $fields['order_items_booking_status'] ) ) {
			$fields[ "order_item_{$i}_booking_status" ] = 'on';
        }
	}

	// Yith WooCommerce Subscription - https://yithemes.com/themes/plugins/yith-woocommerce-subscription/.
	if ( woo_ce_detect_export_plugin( 'yith_woocommerce_subscription' ) ) {
		if ( isset( $fields['yith_subscription_id'] ) ) {
			$fields[ "order_item_{$i}_yith_subscription_id" ] = 'on';
        }
		if ( isset( $fields['yith_subscription_start_date'] ) ) {
			$fields[ "order_item_{$i}_yith_subscription_start_date" ] = 'on';
        }
		if ( isset( $fields['yith_subscription_payment_due_date'] ) ) {
			$fields[ "order_item_{$i}_yith_subscription_payment_due_date" ] = 'on';
        }
		if ( isset( $fields['yith_subscription_end_date'] ) ) {
			$fields[ "order_item_{$i}_yith_subscription_end_date" ] = 'on';
        }
		if ( isset( $fields['yith_subscription_expired_date'] ) ) {
			$fields[ "order_item_{$i}_yith_subscription_expired_date" ] = 'on';
        }
		if ( isset( $fields['yith_subscription_cancelled_date'] ) ) {
			$fields[ "order_item_{$i}_yith_subscription_cancelled_date" ] = 'on';
        }
		if ( isset( $fields['yith_subscription_cancelled_by'] ) ) {
			$fields[ "order_item_{$i}_yith_subscription_cancelled_by" ] = 'on';
        }
		if ( isset( $fields['yith_subscription_expired_pause_date'] ) ) {
			$fields[ "order_item_{$i}_yith_subscription_expired_pause_date" ] = 'on';
        }
		if ( isset( $fields['yith_subscription_status'] ) ) {
			$fields[ "order_item_{$i}_yith_subscription_status" ] = 'on';
        }
		if ( isset( $fields['yith_subscription_recurring_price'] ) ) {
			$fields[ "order_item_{$i}_yith_subscription_recurring_price" ] = 'on';
        }
		if ( isset( $fields['yith_subscription_price_per'] ) ) {
			$fields[ "order_item_{$i}_yith_subscription_price_per" ] = 'on';
        }
		if ( isset( $fields['yith_subscription_trial_per'] ) ) {
			$fields[ "order_item_{$i}_yith_subscription_trial_per" ] = 'on';
        }
		if ( isset( $fields['yith_subscription_max_length'] ) ) {
			$fields[ "order_item_{$i}_yith_subscription_max_length" ] = 'on';
        }
		// if( isset( $fields['yith_subscription_next_payment_due_date'] ) ).
			// $fields["order_item_%d_yith_subscription_next_payment_due_dat{$i}] = 'on';.
	}

	// WooCommerce Warranty Requests - https://woocommerce.com/products/warranty-requests/.
	if ( woo_ce_detect_export_plugin( 'wc_warranty' ) ) {
		if ( isset( $fields['wc_warranty_id'] ) ) {
			$fields[ "order_item_{$i}_wc_warranty_id" ] = 'on';
        }
		if ( isset( $fields['wc_warranty_code'] ) ) {
			$fields[ "order_item_{$i}_wc_warranty_code" ] = 'on';
        }
		if ( isset( $fields['wc_warranty_request_type'] ) ) {
			$fields[ "order_item_{$i}_wc_warranty_request_type" ] = 'on';
        }
		if ( isset( $fields['wc_warranty_return_tracking_code'] ) ) {
			$fields[ "order_item_{$i}_wc_warranty_return_tracking_code" ] = 'on';
        }
		if ( isset( $fields['wc_warranty_request_tracking_code'] ) ) {
			$fields[ "order_item_{$i}_wc_warranty_request_tracking_code" ] = 'on';
        }
		if ( isset( $fields['wc_warranty_shipping_label'] ) ) {
			$fields[ "order_item_{$i}_wc_warranty_shipping_label" ] = 'on';
        }
		if ( isset( $fields['wc_warranty_type'] ) ) {
			$fields[ "order_item_{$i}_wc_warranty_type" ] = 'on';
        }
		if ( isset( $fields['wc_warranty_length'] ) ) {
			$fields[ "order_item_{$i}_wc_warranty_length" ] = 'on';
        }
		if ( isset( $fields['wc_warranty_value'] ) ) {
			$fields[ "order_item_{$i}_wc_warranty_value" ] = 'on';
        }
		if ( isset( $fields['wc_warranty_duration'] ) ) {
			$fields[ "order_item_{$i}_wc_warranty_duration" ] = 'on';
        }
	}

	// Tax Rates.
	$tax_rates = woo_ce_get_order_tax_rates();
	if ( ! empty( $tax_rates ) ) {
		foreach ( $tax_rates as $tax_rate ) {
			if ( isset( $fields[ "order_items_tax_rate_{$tax_rate['rate_id']}" ] ) ) {
				$fields[ "order_item_{$i}_tax_rate_{$tax_rate['rate_id']}" ] = 'on';
            }
		}
	}
	unset( $tax_rates, $tax_rate );

	// Variation Attributes.
	// Product Attributes.
	if ( apply_filters( 'woo_ce_enable_product_attributes', true ) ) {
		$attributes = woo_ce_get_product_attributes( 'attribute_name' );
		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $attribute ) {
				$key = rawurlencode( $attribute );
				if ( isset( $fields[ "order_items_attribute_{$key}" ] ) ) {
					$fields[ "order_item_{$i}_attribute_{$key}" ] = 'on';
                }
				if ( isset( $fields[ "order_items_product_attribute_{$key}" ] ) ) {
					$fields[ "order_item_{$i}_product_attribute_{$key}" ] = 'on';
                }
			}
			unset( $key );
		}
		unset( $attributes, $attribute );
	}

	// Custom Order Items fields.
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	if ( ! empty( $custom_order_items ) ) {
		foreach ( $custom_order_items as $custom_order_item ) {
			if ( ! empty( $custom_order_item ) ) {
				if ( isset( $fields[ "order_items_{$custom_order_item}" ] ) ) {
					$fields[ "order_item_{$i}_{$custom_order_item}" ] = 'on';
                }
			}
		}
	}

	// Custom Order Item Product fields.
	$custom_order_products = woo_ce_get_option( 'custom_order_products', '' );
	if ( ! empty( $custom_order_products ) ) {
		foreach ( $custom_order_products as $custom_order_product ) {
			if ( ! empty( $custom_order_product ) ) {
				if ( isset( $fields[ "order_items_{$custom_order_product}" ] ) ) {
					$fields[ "order_item_{$i}_{$custom_order_product}" ] = 'on';
                }
			}
		}
	}

	// Custom Product fields.
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if ( ! empty( $custom_products ) ) {
		foreach ( $custom_products as $custom_product ) {
			if ( ! empty( $custom_product ) ) {
				if ( isset( $fields[ "order_items_{$custom_product}" ] ) ) {
					$fields[ "order_item_{$i}_{$custom_product}" ] = 'on';
                }
			}
		}
	}

	return $fields;
}
add_filter( 'woo_ce_add_unique_order_item_fields_on', 'woo_ce_unique_order_item_fields_on', 10, 2 );

/**
 * This prepares the Order columns for the 'unique' Order Item formatting selection.
 *
 * @param  array $fields The Order fields.
 * @param  int   $i      The Order Item index.
 * @param  array $original_columns The original columns.
 */
function woo_ce_extend_order_items_unique_columns( $fields = array(), $i = 0, $original_columns = array() ) {

	$order_fields = woo_ce_get_order_fields();

	// Product Add-ons - http://www.woothemes.com/.
	if ( woo_ce_detect_export_plugin( 'product_addons' ) ) {
		$product_addons = woo_ce_get_product_addons();
		if ( ! empty( $product_addons ) ) {
			foreach ( $product_addons as $product_addon ) {
				if ( isset( $original_columns[ "order_item_{$i}_product_addon_{$product_addon->post_name}" ] ) ) {
					$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, $product_addon->post_title );
                }
			}
		}
	}

	// WooCommerce Checkout Add-Ons - http://www.skyverge.com/product/woocommerce-checkout-add-ons/.
	if ( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_checkout_addon_id" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_checkout_addon_id', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_checkout_addon_label" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_checkout_addon_label', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_checkout_addon_value" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_checkout_addon_value', 'name', 'unique', $order_fields ) );
        }
	}

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/.
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/.
	if ( woo_ce_detect_product_brands() ) {
		if ( isset( $original_columns[ "order_item_{$i}_brand" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_brand', 'name', 'unique', $order_fields ) );
        }
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/.
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/.
	if ( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_vendor" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_vendor', 'name', 'unique', $order_fields ) );
        }
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/.
	if ( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_cost_of_goods" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_cost_of_goods', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_total_cost_of_goods" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_total_cost_of_goods', 'name', 'unique', $order_fields ) );
        }
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590.
	if ( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_posr" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_posr', 'name', 'unique', $order_fields ) );
        }
	}

	// WC Fields Factory - https://wordpress.org/plugins/wc-fields-factory/.
	if ( woo_ce_detect_export_plugin( 'wc_fields_factory' ) ) {
		// Product Fields.
		$product_fields = woo_ce_get_wcff_product_fields();
		if ( ! empty( $product_fields ) ) {
			foreach ( $product_fields as $product_field ) {
				if ( isset( $original_columns[ "order_item_{$i}_{$product_field['name']}" ] ) ) {
					$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_' . $product_field['name'], 'name', 'unique', $order_fields ) );
                }
			}
		}
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/.
	if ( woo_ce_detect_export_plugin( 'wc_msrp' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_msrp" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_msrp', 'name', 'unique', $order_fields ) );
        }
	}

	// Gravity Forms - http://woothemes.com/woocommerce.
	if ( woo_ce_detect_export_plugin( 'gravity_forms' ) && woo_ce_detect_export_plugin( 'woocommerce_gravity_forms' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_gf_form_id" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_gf_form_id', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_gf_form_label" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_gf_form_label', 'name', 'unique', $order_fields ) );
        }
		// Check if there are any Products linked to Gravity Forms.
		$gf_fields = woo_ce_get_gravity_forms_fields();
		if ( ! empty( $gf_fields ) ) {
			foreach ( $gf_fields as $key => $gf_field ) {
				if ( isset( $original_columns[ "order_item_{$i}_gf_{$gf_field['formId']}_{$gf_field['id']}" ] ) ) {
					$fields[] = sprintf( apply_filters( 'woo_ce_extend_order_items_unique_columns_gf_fields', __( 'Order Item #%1$d: %2$s - %3$s', 'woocommerce-exporter' ) ), $i, $gf_field['formTitle'], $gf_field['label'] );
                }
			}
			unset( $gf_fields, $gf_field );
		}
	}

	// Local Pickup Plus - http://www.woothemes.com/products/local-pickup-plus/.
	if ( woo_ce_detect_export_plugin( 'local_pickup_plus' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_pickup_location" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_pickup_location', 'name', 'unique', $order_fields ) );
        }
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/.
	if ( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_booking_id" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_id', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_date', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_type" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_type', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_start_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_start_date', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_start_time" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_start_time', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_end_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_end_date', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_end_time" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_end_time', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_all_day" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_all_day', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_resource_id" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_resource_id', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_resource_title" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_resource_title', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_persons" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_persons', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_persons_total" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_persons_total', 'name', 'unique', $order_fields ) );
        }
	}

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619.
	if ( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
		$tm_fields = woo_ce_get_extra_product_option_fields();
		if ( ! empty( $tm_fields ) ) {
			foreach ( $tm_fields as $tm_field ) {

				if ( empty( $tm_field ) ) {
					continue;
                }

				if ( isset( $original_columns[ "order_item_{$i}_tm_{$tm_field['name']}" ] ) ) {
					$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, ( ! empty( $tm_field['section_label'] ) ? $tm_field['section_label'] : $tm_field['name'] ) );
                }
				if ( apply_filters( 'woo_ce_enable_advanced_extra_product_options', false ) ) {
					if ( isset( $original_columns[ "order_item_{$i}_tm_{$tm_field['name']}_cost" ] ) ) {
						$fields[] = sprintf( __( 'Order Item #%1$d: %2$s Cost', 'woocommerce-exporter' ), $i, ( ! empty( $tm_field['section_label'] ) ? $tm_field['section_label'] : $tm_field['name'] ) );
                    }
					if ( isset( $original_columns[ "order_item_{$i}_tm_{$tm_field['name']}_quantity" ] ) ) {
						$fields[] = sprintf( __( 'Order Item #%1$d: %2$s Quantity', 'woocommerce-exporter' ), $i, ( ! empty( $tm_field['section_label'] ) ? $tm_field['section_label'] : $tm_field['name'] ) );
                    }
				}
			}
		}
		unset( $tm_fields, $tm_field );
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields.
	if ( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		$meta_type = 'order_item';
		if ( ! get_option( 'wccf_migrated_to_20' ) ) {
			$options = get_option( 'rp_wccf_options' );
			if ( ! empty( $options ) ) {
				$options = ( isset( $options[1] ) ? $options[1] : false );
				if ( ! empty( $options ) ) {
					// Product Fields.
					$custom_fields = ( isset( $options['product_fb_config'] ) ? $options['product_fb_config'] : false );
					if ( ! empty( $custom_fields ) ) {
						foreach ( $custom_fields as $custom_field ) {
							if ( isset( $original_columns[ "order_item_{$i}_wccf_{$custom_field['key']}" ] ) ) {
								$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, ucfirst( $custom_field['label'] ) );
                            }
						}
						unset( $custom_fields, $custom_field );
					}
				}
				unset( $options );
			}
		} else {
			// Product Fields.
			$custom_fields = woo_ce_get_wccf_product_fields();
			if ( ! empty( $custom_fields ) ) {
				foreach ( $custom_fields as $custom_field ) {
					$label = get_post_meta( $custom_field->ID, 'label', true );
					$key   = get_post_meta( $custom_field->ID, 'key', true );
					if ( isset( $original_columns[ "order_item_{$i}_wccf_{$key}" ] ) ) {
						$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, ucfirst( $label ) );
                    }
				}
			}
			unset( $custom_fields, $custom_field, $label, $key );
		}
	}

	// WooCommerce Easy Booking - https://wordpress.org/plugins/woocommerce-easy-booking-system/.
	if ( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_booking_start_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%d: Start Date', 'woocommerce-exporter' ), $i );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_end_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%d: End Date', 'woocommerce-exporter' ), $i );
        }
	}

	// N-Media WooCommerce Personalized Product Meta Manager - http://najeebmedia.com/wordpress-plugin/woocommerce-personalized-product-option/.
	// PPOM for WooCommerce - https://wordpress.org/plugins/woocommerce-product-addon/.
	if (
		woo_ce_detect_export_plugin( 'wc_nm_personalizedproduct' ) ||
		woo_ce_detect_export_plugin( 'wc_ppom' )
	) {
		$custom_fields = woo_ce_get_nm_personalized_product_fields();
		if ( ! empty( $custom_fieds ) ) {
			foreach ( $custom_fields as $custom_field ) {
				if ( isset( $original_columns[ "order_item_{$i}_tm_{$custom_field['name']}" ] ) ) {
					$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, $custom_field['name'] );
                }
			}
		}
	}

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/.
	if ( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_appointment_id" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_appointment_id', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_start_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_start_date', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_start_time" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_start_time', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_end_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_end_date', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_end_time" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_end_time', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_all_day" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_all_day', 'name', 'unique', $order_fields ) );
        }
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/.
	if ( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if ( ! empty( $wholesale_roles ) ) {
			foreach ( $wholesale_roles as $key => $wholesale_role ) {
				if ( isset( $original_columns[ "order_item_{$i}_{$key}_wholesale_price" ] ) ) {
					$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( "order_items_{$key}_wholesale_price", 'name', 'unique', $order_fields ) );
                }
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	// FooEvents for WooCommerce - https://www.fooevents.com/.
	if ( woo_ce_detect_export_plugin( 'fooevents' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_tickets_purchased" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_tickets_purchased', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_is_event" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_is_event', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_date', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_start_time" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_start_time', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_end_time" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_end_time', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_venue" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_venue', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_gps" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_gps', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_googlemaps" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_googlemaps', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_directions" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_directions', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_phone" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_phone', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_email" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_email', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_ticket_logo" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_ticket_logo', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_ticket_subject" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_ticket_subject', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_ticket_text" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_ticket_text', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_ticket_thankyou_text" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_ticket_thankyou_text', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_ticket_background_color" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_ticket_background_color', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_ticket_button_color" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_ticket_button_color', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_event_ticket_text_color" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_event_ticket_text_color', 'name', 'unique', $order_fields ) );
        }
	}

	// AliDropship for WooCommerce - https://alidropship.com/.
	if ( woo_ce_detect_export_plugin( 'alidropship' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_ali_product_id" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_ali_product_id', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_ali_product_url" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_ali_product_url', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_ali_store_url" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_ali_store_url', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_ali_store_name" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_ali_store_name', 'name', 'unique', $order_fields ) );
        }
	}

	// Bookings and Appointments For WooCommerce Premium - https://www.pluginhive.com/product/woocommerce-booking-and-appointments/.
	if ( woo_ce_detect_export_plugin( 'wc_bookings_appointments_pro' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_session_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_session_date', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_session_time" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_session_time', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booked_from" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booked_from', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_cost" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_cost', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_booking_status" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_status', 'name', 'unique', $order_fields ) );
        }
	}

	// Yith WooCommerce Subscription - https://yithemes.com/themes/plugins/yith-woocommerce-subscription/.
	if ( woo_ce_detect_export_plugin( 'yith_woocommerce_subscription' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_yith_subscription_id" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'yith_subscription_id', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_yith_subscription_start_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'yith_subscription_start_date', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_yith_subscription_payment_due_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'yith_subscription_payment_due_date', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_yith_subscription_end_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'yith_subscription_end_date', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_yith_subscription_expired_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'yith_subscription_expired_date', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_yith_subscription_cancelled_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'yith_subscription_cancelled_date', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_yith_subscription_cancelled_by" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'yith_subscription_cancelled_by', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_yith_subscription_expired_pause_date" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'yith_subscription_expired_pause_date', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_yith_subscription_status" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'yith_subscription_status', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_yith_subscription_price_per" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'yith_subscription_price_per', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_yith_subscription_trial_per" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'yith_subscription_trial_per', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_yith_subscription_max_length" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'yith_subscription_max_length', 'name', 'unique', $order_fields ) );
        }
		// if( isset( $original_columns[ "order_item_{$i}_yith_subscription_next_payment_due_date" ] ) ).
		// $fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'yith_subscription_next_payment_due_date', 'name', 'unique', $order_fields ) );.
	}

	// WooCommerce Warranty Requests - https://woocommerce.com/products/warranty-requests/.
	if ( woo_ce_detect_export_plugin( 'wc_warranty' ) ) {
		if ( isset( $original_columns[ "order_item_{$i}_wc_warranty_id" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'wc_warranty_id', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_wc_warranty_code" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'wc_warranty_code', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_wc_warranty_request_type" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'wc_warranty_request_type', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_wc_warranty_return_tracking_code" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'wc_warranty_return_tracking_code', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_wc_warranty_request_tracking_code" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'wc_warranty_request_tracking_code', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_wc_warranty_shipping_label" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'wc_warranty_shipping_label', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_wc_warranty_type" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'wc_warranty_type', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_wc_warranty_length" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'wc_warranty_length', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_wc_warranty_value" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'wc_warranty_value', 'name', 'unique', $order_fields ) );
        }
		if ( isset( $original_columns[ "order_item_{$i}_wc_warranty_duration" ] ) ) {
			$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'wc_warranty_duration', 'name', 'unique', $order_fields ) );
        }
	}

	// Tax Rates.
	$tax_rates = woo_ce_get_order_tax_rates();
	if ( ! empty( $tax_rates ) ) {
		foreach ( $tax_rates as $tax_rate ) {
			if ( isset( $original_columns[ "order_item_{$i}_tax_rate_{$tax_rate['rate_id']}" ] ) ) {
				$fields[] = sprintf( __( 'Order Item #%1$d: Tax Rate - %2$s', 'woocommerce-exporter' ), $i, $tax_rate['label'] );
            }
		}
	}
	unset( $tax_rates, $tax_rate );

	// Variation Attributes.
	// Product Attributes.
	if ( apply_filters( 'woo_ce_enable_product_attributes', true ) ) {
		$attributes = woo_ce_get_product_attributes();
		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $attribute ) {
				$key = rawurlencode( $attribute->attribute_name );
				if ( isset( $original_columns[ "order_item_{$i}_attribute_{$key}" ] ) ) {
					if ( empty( $attribute->attribute_label ) ) {
						$attribute->attribute_label = $attribute->attribute_name;
                    }
					$fields[] = sprintf( __( 'Order Item #%1$d: %2$s Variation', 'woocommerce-exporter' ), $i, $attribute->attribute_label );
				}
				if ( isset( $original_columns[ "order_item_{$i}_product_attribute_{$key}" ] ) ) {
					if ( empty( $attribute->attribute_label ) ) {
						$attribute->attribute_label = $attribute->attribute_name;
                    }
					$fields[] = sprintf( __( 'Order Item #%1$d: %2$s Attribute', 'woocommerce-exporter' ), $i, $attribute->attribute_label );
				}
			}
		}
		unset( $attributes, $attribute );
	}

	// Custom Order Items fields.
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	if ( ! empty( $custom_order_items ) ) {
		foreach ( $custom_order_items as $custom_order_item ) {
			if ( ! empty( $custom_order_item ) ) {
				if ( isset( $original_columns[ "order_item_{$i}_{$custom_order_item}" ] ) ) {
					$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, $custom_order_item );
                }
			}
		}
	}

	// Custom Order Item Product fields.
	$custom_order_products = woo_ce_get_option( 'custom_order_products', '' );
	if ( ! empty( $custom_order_products ) ) {
		foreach ( $custom_order_products as $custom_order_product ) {
			if ( ! empty( $custom_order_product ) ) {
				if ( isset( $original_columns[ "order_item_{$i}_{$custom_order_product}" ] ) ) {
					$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, $custom_order_product );
                }
			}
		}
	}

	// Custom Product fields.
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if ( ! empty( $custom_products ) ) {
		foreach ( $custom_products as $custom_product ) {
			if ( ! empty( $custom_product ) ) {
				if ( isset( $original_columns[ "order_item_{$i}_{$custom_product}" ] ) ) {
					$fields[] = sprintf( __( 'Order Item #%1$d: %2$s', 'woocommerce-exporter' ), $i, $custom_product );
                }
			}
		}
	}

	return $fields;
}
add_filter( 'woo_ce_unique_order_item_columns', 'woo_ce_extend_order_items_unique_columns', 10, 3 );
// phpcs:enable WordPress.WP.I18n.MissingTranslatorsComment, Squiz.PHP.CommentedOutCode.Found