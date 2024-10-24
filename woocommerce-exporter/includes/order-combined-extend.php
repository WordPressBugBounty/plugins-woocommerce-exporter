<?php
// Order items formatting: Combined
function woo_ce_extend_order_items_combined( $order_data, $order_items, $order ) {
	global $export;

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	// Gravity Forms - http://woothemes.com/woocommerce
	if (
		(
			woo_ce_detect_export_plugin( 'gravity_forms' ) &&
			woo_ce_detect_export_plugin( 'woocommerce_gravity_forms' )
		) &&
		$order_items
	) {
		// Check if there are any Products linked to Gravity Forms
		$gf_fields = woo_ce_get_gravity_forms_fields();
		if ( ! empty( $gf_fields ) ) {
			$meta_type                 = 'order_item';
			$order_items_gf_form_id    = '';
			$order_items_gf_form_label = '';
			foreach ( $order_items as $order_item ) {
				$gravity_forms_history = get_metadata( $meta_type, $order_item['id'], '_gravity_forms_history', true );
				// Check that Gravity Forms Order item meta isn't empty
				if ( ! empty( $gravity_forms_history ) ) {
					if ( isset( $gravity_forms_history['_gravity_form_data'] ) ) {
						$order_items_gf_form_id    .= $gravity_forms_history['_gravity_form_data']['id'] . $export->category_separator;
						$gravity_form               = ( method_exists( 'RGFormsModel', 'get_form' ) ? RGFormsModel::get_form( $gravity_forms_history['_gravity_form_data']['id'] ) : array() );
						$order_items_gf_form_label .= ( ! empty( $gravity_form ) ? $gravity_form->title : '' ) . $export->category_separator;
						unset( $gravity_form );
						foreach ( $gf_fields as $gf_field ) {
							// Check that we only fill export fields for forms that are actually filled
							if ( $gf_field['formId'] == $gravity_forms_history['_gravity_form_data']['id'] ) {
								$order->{sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] )} .= get_metadata( $meta_type, $order_item['id'], $gf_field['label'], true ) . $export->category_separator;
                            }
						}
					}
				}
				unset( $gravity_forms_history );
			}
			if ( isset( $order_items_gf_form_id ) ) {
				$order_items_gf_form_id = substr( $order_items_gf_form_id, 0, -1 );
            }
			if ( isset( $order_items_gf_form_label ) ) {
				$order_items_gf_form_label = substr( $order_items_gf_form_label, 0, -1 );
            }
			if ( isset( $order->{sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] )} ) ) {
				$order->{sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] )} = substr( $order->{sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] )}, 0, -1 );
            }
		}
		unset( $gf_fields, $gf_field );
	}

	// WooCommerce Checkout Add-Ons - http://www.skyverge.com/product/woocommerce-checkout-add-ons/
	if (
		woo_ce_detect_export_plugin( 'checkout_addons' ) &&
		$order_items
	) {
		$meta_type = 'order_item';
		foreach ( $order_items as $order_item ) {
			$order_items_checkout_addon_id    .= ( isset( $order_item->checkout_addon_id ) ? $order_item->checkout_addon_id : '' ) . $export->category_separator;
			$order_items_checkout_addon_label .= ( isset( $order_item->checkout_addon_label ) ? $order_item->checkout_addon_label : '' ) . $export->category_separator;
			$order_items_checkout_addon_value .= ( isset( $order_item->checkout_addon_value ) ? $order_item->checkout_addon_value : '' ) . $export->category_separator;
		}
		if ( isset( $order_items_checkout_addon_id ) ) {
			$order_items_checkout_addon_id = substr( $order_items_checkout_addon_id, 0, -1 );
        }
		if ( isset( $order_items_checkout_addon_label ) ) {
			$order_items_checkout_addon_label = substr( $order_items_checkout_addon_label, 0, -1 );
        }
		if ( isset( $order_items_checkout_addon_value ) ) {
			$order_items_checkout_addon_value = substr( $order_items_checkout_addon_value, 0, -1 );
        }
	}

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if (
		woo_ce_detect_product_brands() &&
		$order_items
	) {
		$meta_type = 'order_item';
		foreach ( $order_items as $order_item ) {
			$order_items_brand .= woo_ce_get_product_assoc_brands( $order_item->product_id ) . $export->category_separator;
        }
		if ( isset( $order_items_brand ) ) {
			$order_items_brand = substr( $order_items_brand, 0, -1 );
        }
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if (
		(
			woo_ce_detect_export_plugin( 'vendors' ) ||
			woo_ce_detect_export_plugin( 'yith_vendor' )
		) &&
		$order_items
	) {
		$meta_type = 'order_item';
		foreach ( $order_items as $order_item ) {
			$order_items_vendor = woo_ce_get_product_assoc_product_vendors( $order_item->product_id ) . $export->category_separator;
        }
		if ( isset( $order_items_vendor ) ) {
			$order_items_vendor = substr( $order_items_vendor, 0, -1 );
        }
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/
	if (
		woo_ce_detect_export_plugin( 'wc_cog' ) &&
		$order_items
	) {
		$meta_type = 'order_item';
		foreach ( $order_items as $order_item ) {
			$order_items_cost_of_goods       .= woo_ce_format_price( get_metadata( $meta_type, $order_item['id'], '_wc_cog_item_cost', true ), $order->order_currency ) . $export->category_separator;
			$order_items_total_cost_of_goods .= woo_ce_format_price( get_metadata( $meta_type, $order_item['id'], '_wc_cog_item_total_cost', true ), $order->order_currency ) . $export->category_separator;
		}
		if ( isset( $order_items_cost_of_goods ) ) {
			$order_items_cost_of_goods = substr( $order_items_cost_of_goods, 0, -1 );
        }
		if ( isset( $order_items_total_cost_of_goods ) ) {
			$order_items_total_cost_of_goods = substr( $order_items_total_cost_of_goods, 0, -1 );
        }
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590
	if (
		woo_ce_detect_export_plugin( 'wc_posr' ) &&
		$order_items
	) {
		$meta_type = 'order_item';
		foreach ( $order_items as $order_item ) {
			$order_items_posr .= woo_ce_format_price( get_metadata( $meta_type, $order_item['id'], '_posr_line_cog_total', true ), $order->order_currency ) . $export->category_separator;
		}
		if ( isset( $order_items_posr ) ) {
			$order_items_posr = substr( $order_items_posr, 0, -1 );
        }
	}

	// WC Fields Factory - https://wordpress.org/plugins/wc-fields-factory/
	if ( woo_ce_detect_export_plugin( 'wc_fields_factory' ) ) {
		$meta_type = 'order_item';
		// Product Fields
		$product_fields = woo_ce_get_wcff_product_fields();
		if ( ! empty( $product_fields ) ) {
			foreach ( $product_fields as $product_field ) {
				$order->{sprintf( 'order_items_wccpf_%s', sanitize_key( $product_field['name'] ) )} = '';
			}
			foreach ( $order_items as $order_item ) {
				foreach ( $product_fields as $product_field ) {
					$order->{sprintf( 'order_items_wccpf_%s', sanitize_key( $product_field['name'] ) )} .= ( isset( $order_item->{sprintf( 'wccpf_%s', sanitize_key( $product_field['name'] ) )} ) ? $order_item->{sprintf( 'wccpf_%s', sanitize_key( $product_field['name'] ) )} : '' ) . $export->category_separator;
				}
			}
			foreach ( $product_fields as $product_field ) {
				if ( isset( $order->{sprintf( 'order_items_wccpf_%s', sanitize_key( $product_field['name'] ) )} ) ) {
					$order->{sprintf( 'order_items_wccpf_%s', sanitize_key( $product_field['name'] ) )} = substr( $order->{sprintf( 'order_items_wccpf_%s', sanitize_key( $product_field['name'] ) )}, 0, -1 );
                }
			}
		}
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/
	if (
		woo_ce_detect_export_plugin( 'wc_msrp' ) &&
		$order_items
	) {
		foreach ( $order_items as $order_item ) {
			$order_items_msrp .= woo_ce_format_price( get_post_meta( $order_item->product_id, '_msrp_price', true ) ) . $export->category_separator;
		}
		if ( isset( $order_items_msrp ) ) {
			$order_items_msrp = substr( $order_items_msrp, 0, -1 );
        }
	}

	// Local Pickup Plus - http://www.woothemes.com/products/local-pickup-plus/
	if (
		woo_ce_detect_export_plugin( 'local_pickup_plus' ) &&
		$order_items
	) {
		$meta_type                   = 'order_item';
		$order_items_pickup_location = '';
		foreach ( $order_items as $order_item ) {
			$pickup_location = get_metadata( $meta_type, $order_item['id'], 'Pickup Location', true );
			if ( ! empty( $pickup_location ) ) {
				$order_items_pickup_location .= get_metadata( $meta_type, $order_item['id'], 'Pickup Location', true ) . $export->category_separator;
            }
			unset( $pickup_location );
		}
		if ( isset( $order_items_pickup_location ) ) {
			$order_items_pickup_location = substr( $order_items_pickup_location, 0, -1 );
        }
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	if (
		woo_ce_detect_export_plugin( 'woocommerce_bookings' ) &&
		$order_items
	) {
		$meta_type                          = 'order_item';
		$order_items_booking_id             = '';
		$order_items_booking_date           = '';
		$order_items_booking_type           = '';
		$order_items_booking_start_date     = '';
		$order_items_booking_start_time     = '';
		$order_items_booking_end_date       = '';
		$order_items_booking_end_time       = '';
		$order_items_booking_all_day        = '';
		$order_items_booking_resource_id    = '';
		$order_items_booking_resource_title = '';
		$order_items_booking_persons        = '';
		$order_items_booking_persons_total  = '';
		foreach ( $order_items as $order_item ) {
			$booking_id = woo_ce_get_order_assoc_booking_id( $order->get_id(), $order_item['id'] );
			if ( ! empty( $booking_id ) ) {
				// @mod - Are we double querying here? Check in 2.4+
				$order_items_booking_id .= $booking_id . $export->category_separator;
				$booking_start_date      = get_post_meta( $booking_id, '_booking_start', true );
				if ( ! empty( $booking_start_date ) ) {
					$order_items_booking_start_date .= woo_ce_format_date( date( 'Y-m-d', strtotime( $booking_start_date ) ) ) . $export->category_separator;
					if ( function_exists( 'wc_format_datetime' ) ) {
						$booking_start_time = wc_format_datetime( $booking_start_date, get_option( 'time_format' ) );
						if ( empty( $booking_start_time ) ) {
							$booking_start_time = mysql2date( 'H:i:s', $booking_start_date );
                        }
						$order_items_booking_start_time .= $booking_start_time . $export->category_separator;
					}
				}
				unset( $booking_start_date, $booking_start_time );
				$booking_end_date = get_post_meta( $booking_id, '_booking_end', true );
				if ( ! empty( $booking_end_date ) ) {
					$order_items_booking_end_date .= woo_ce_format_date( date( 'Y-m-d', strtotime( $booking_end_date ) ) ) . $export->category_separator;
					if ( function_exists( 'wc_format_datetime' ) ) {
						$booking_end_time = wc_format_datetime( $booking_end_date, get_option( 'time_format' ) );
						if ( empty( $booking_end_time ) ) {
							$booking_end_time = mysql2date( 'H:i:s', $booking_end_date );
                        }
						$order_items_booking_end_time .= $booking_end_time . $export->category_separator;
					}
				}
				unset( $booking_end_date, $booking_end_time );
				// All Day Booking
				$booking_all_day = woo_ce_format_switch( get_post_meta( $booking_id, '_booking_all_day', true ) );
				if ( ! empty( $booking_all_day ) ) {
					$order_items_booking_all_day .= $booking_all_day . $export->category_separator;
                }
				unset( $booking_all_day );
				// Booking Resource ID
				$booking_resource_id = get_post_meta( $booking_id, '_booking_resource_id', true );
				if ( ! empty( $booking_resource_id ) ) {
					$order_items_booking_resource_id .= $booking_resource_id;
                }
				unset( $booking_resource_id );
				// Booking Resource Name
				if ( ! empty( $order_items_booking_resource_id ) ) {
					$booking_resource_title = get_the_title( $order_items_booking_resource_id );
					if ( ! empty( $booking_resource_title ) ) {
						$order_items_booking_resource_title .= $booking_resource_title;
                    }
					unset( $booking_resource_title );
				}
				// Booking # of Persons
				$booking_persons       = get_post_meta( $booking_id, '_booking_persons', true );
				$booking_persons_total = false;
				if ( ! empty( $booking_persons ) && is_array( $booking_persons ) ) {
					$booking_persons_total = array_sum( $booking_persons );
					foreach ( $booking_persons as $person_id => $person_count ) {
						$person = get_post( $person_id );
						if ( ! empty( $person ) ) {
							$order_items_booking_persons .= sprintf( '%s: %d', $person->post_title, $person_count ) . $export->category_separator;
                        }
					}
				}
				$order_items_booking_persons_total .= ( ! empty( $booking_persons_total ) ? $booking_persons_total : '-' );
				unset( $booking_persons );
			}
			unset( $booking_id );
			$booking_date = get_metadata( $meta_type, $order_item['id'], __( 'Booking Date', 'woocommerce-bookings' ), true );
			if ( ! empty( $booking_date ) ) {
				$order_items_booking_date .= get_metadata( $meta_type, $order_item['id'], __( 'Booking Date', 'woocommerce-bookings' ), true ) . $export->category_separator;
            }
			unset( $booking_date );
			$booking_type = get_metadata( $meta_type, $order_item['id'], __( 'Booking Type', 'woocommerce-bookings' ), true );
			if ( ! empty( $booking_type ) ) {
				$order_items_booking_type .= get_metadata( $meta_type, $order_item['id'], __( 'Booking Type', 'woocommerce-bookings' ), true ) . $export->category_separator;
            }
			unset( $booking_type );
		}
		if ( isset( $order_items_booking_id ) ) {
			$order_items_booking_id = substr( $order_items_booking_id, 0, -1 );
        }
		if ( isset( $order_items_booking_date ) ) {
			$order_items_booking_date = substr( $order_items_booking_date, 0, -1 );
        }
		if ( isset( $order_items_booking_type ) ) {
			$order_items_booking_type = substr( $order_items_booking_type, 0, -1 );
        }
		if ( isset( $order_items_booking_start_date ) ) {
			$order_items_booking_start_date = substr( $order_items_booking_start_date, 0, -1 );
        }
		if ( isset( $order_items_booking_start_time ) ) {
			$order_items_booking_start_time = substr( $order_items_booking_start_time, 0, -1 );
        }
		if ( isset( $order_items_booking_end_date ) ) {
			$order_items_booking_end_date = substr( $order_items_booking_end_date, 0, -1 );
        }
		if ( isset( $order_items_booking_end_time ) ) {
			$order_items_booking_end_time = substr( $order_items_booking_end_time, 0, -1 );
        }
		if ( isset( $order_items_booking_all_day ) ) {
			$order_items_booking_all_day = substr( $order_items_booking_all_day, 0, -1 );
        }
		if ( isset( $order_items_booking_persons ) ) {
			$order_items_booking_persons = substr( $order_items_booking_persons, 0, -1 );
        }
	}

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
	if (
		woo_ce_detect_export_plugin( 'extra_product_options' ) &&
		$order_items
	) {
		$tm_fields = woo_ce_get_extra_product_option_fields();
		if ( ! empty( $tm_fields ) ) {
			foreach ( $tm_fields as $tm_field ) {

				if ( empty( $tm_field ) ) {
					continue;
                }

				$order->{sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) )} = '';
				if ( apply_filters( 'woo_ce_enable_advanced_extra_product_options', false ) ) {
					$order->{sprintf( 'order_items_tm_%s_cost', sanitize_key( $tm_field['name'] ) )}     = '';
					$order->{sprintf( 'order_items_tm_%s_quantity', sanitize_key( $tm_field['name'] ) )} = '';
				}
			}
		}
		foreach ( $order_items as $order_item ) {
			$tm_fields = woo_ce_get_extra_product_option_fields( $order_item['id'] );
			if ( ! empty( $tm_fields ) ) {
				foreach ( $tm_fields as $tm_field ) {

					if ( empty( $tm_field ) ) {
						continue;
                    }

					if ( isset( $order_item->{sprintf( 'tm_%s', sanitize_key( $tm_field['name'] ) )} ) ) {
						$order->{sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) )} .= woo_ce_get_extra_product_option_value( $order_item['id'], $tm_field ) . $export->category_separator;
                    }
					if ( apply_filters( 'woo_ce_enable_advanced_extra_product_options', false ) ) {
						$multiple_value_separator = apply_filters( 'woo_ce_get_extra_product_option_multiple_value_separator', "\n" );
						if ( ! empty( $tm_field['price'] ) ) {
							if ( isset( $order_item->{sprintf( 'tm_%s_cost', sanitize_key( $tm_field['name'] ) )} ) ) {
								$order->{sprintf( 'order_items_tm_%s_cost', sanitize_key( $tm_field['name'] ) )} .= apply_filters( 'woo_ce_get_extra_product_option_multiple_value_formatting', implode( $multiple_value_separator, $tm_field['price'] ), $tm_field, $order_item ) . $export->category_separator;
                            }
						}
						if ( ! empty( $tm_field['quantity'] ) ) {
							if ( isset( $order_item->{sprintf( 'tm_%s_quantity', sanitize_key( $tm_field['name'] ) )} ) ) {
								$order->{sprintf( 'order_items_tm_%s_quantity', sanitize_key( $tm_field['name'] ) )} .= apply_filters( 'woo_ce_get_extra_product_option_multiple_value_formatting', implode( $multiple_value_separator, $tm_field['quantity'] ), $tm_field, $order_item ) . $export->category_separator;
                            }
						}
					}
				}
			}
		}
		$tm_fields = woo_ce_get_extra_product_option_fields();
		if ( ! empty( $tm_fields ) ) {
			foreach ( $tm_fields as $tm_field ) {
				if ( isset( $order->{sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) )} ) ) {
					$order->{sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) )} = substr( $order->{sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) )}, 0, -1 );
                }
				if ( apply_filters( 'woo_ce_enable_advanced_extra_product_options', false ) ) {
					if ( isset( $order->{sprintf( 'order_items_tm_%s_cost', sanitize_key( $tm_field['name'] ) )} ) ) {
						$order->{sprintf( 'order_items_tm_%s_cost', sanitize_key( $tm_field['name'] ) )} = substr( $order->{sprintf( 'order_items_tm_%s_cost', sanitize_key( $tm_field['name'] ) )}, 0, -1 );
                    }
					if ( isset( $order->{sprintf( 'order_items_tm_%s_quantity', sanitize_key( $tm_field['name'] ) )} ) ) {
						$order->{sprintf( 'order_items_tm_%s_quantity', sanitize_key( $tm_field['name'] ) )} = substr( $order->{sprintf( 'order_items_tm_%s_quantity', sanitize_key( $tm_field['name'] ) )}, 0, -1 );
                    }
				}
			}
		}
		unset( $tm_fields, $tm_field );
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
	if ( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		$meta_type = 'order_item';
		if ( ! get_option( 'wccf_migrated_to_20' ) ) {
			$options = get_option( 'rp_wccf_options' );
			if ( ! empty( $options ) ) {
				$options = ( isset( $options[1] ) ? $options[1] : false );
				if ( ! empty( $options ) ) {
					// Product Fields
					$custom_fields = ( isset( $options['product_fb_config'] ) ? $options['product_fb_config'] : false );
					if ( ! empty( $custom_fields ) ) {
						foreach ( $custom_fields as $custom_field ) {
							$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) )} = '';
						}
						foreach ( $order_items as $order_item ) {
							foreach ( $custom_fields as $custom_field ) {
								if ( isset( $order_item->{sprintf( 'wccf_%s', sanitize_key( $custom_field['key'] ) )} ) ) {
									$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) )} .= $order_item->{sprintf( 'wccf_%s', sanitize_key( $custom_field['key'] ) )} . $export->category_separator;
                                }
							}
						}
						foreach ( $custom_fields as $custom_field ) {
							if ( isset( $order->{sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) )} ) ) {
								$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) )} = substr( $order->{sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) )}, 0, -1 );
                            }
						}
						unset( $custom_fields, $custom_field );
					}
				}
				unset( $options );
			}
		} else {
			// Product Fields
			$custom_fields = woo_ce_get_wccf_product_fields();
			if ( ! empty( $custom_fields ) ) {
				foreach ( $custom_fields as $custom_field ) {
					$key = get_post_meta( $custom_field->ID, 'key', true );
					$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $key ) )} = '';
				}
				foreach ( $order_items as $order_item ) {
					foreach ( $custom_fields as $custom_field ) {
						$key = get_post_meta( $custom_field->ID, 'key', true );
						if ( isset( $order_item->{sprintf( 'wccf_%s', sanitize_key( $key ) )} ) ) {
							$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $key ) )} .= $order_item->{sprintf( 'wccf_%s', sanitize_key( $key ) )} . $export->category_separator;
                        }
					}
				}
				foreach ( $custom_fields as $custom_field ) {
					$key = get_post_meta( $custom_field->ID, 'key', true );
					if ( isset( $order->{sprintf( 'order_items_wccf_%s', sanitize_key( $key ) )} ) ) {
						$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $key ) )} = substr( $order->{sprintf( 'order_items_wccf_%s', sanitize_key( $key ) )}, 0, -1 );
                    }
				}
			}
			unset( $custom_fields, $custom_field, $key );
		}
	}

	// WooCommerce Product Custom Options Lite - https://wordpress.org/plugins/woocommerce-custom-options-lite/
	if ( woo_ce_detect_export_plugin( 'wc_product_custom_options' ) ) {
		$custom_options = woo_ce_get_product_custom_options();
		if ( ! empty( $custom_options ) ) {
			foreach ( $custom_options as $custom_option ) {
				$order->{sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) )} = '';
			}
			foreach ( $order_items as $order_item ) {
				foreach ( $custom_options as $custom_option ) {
					if ( isset( $order_item->{sprintf( 'pco_%s', sanitize_key( $custom_option ) )} ) ) {
						$order->{sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) )} = $order_item->{sprintf( 'pco_%s', sanitize_key( $custom_option ) )} . $export->category_separator;
                    }
				}
			}
			foreach ( $custom_options as $custom_option ) {
				if ( isset( $order->{sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) )} ) ) {
					$order->{sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) )} = substr( $order->{sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) )}, 0, -1 );
                }
			}
		}
	}

	// Barcodes for WooCommerce - http://www.wolkenkraft.com/produkte/barcodes-fuer-woocommerce/
	if ( woo_ce_detect_export_plugin( 'wc_barcodes' ) ) {
		$order_items_barcode_type = '';
		$order_items_barcode      = '';
		foreach ( $order_items as $order_item ) {
			$order_items_barcode_type .= get_post_meta( $order_item->product_id, '_barcode_type', true ) . $export->category_separator;
			$order_items_barcode      .= get_post_meta( $order_item->product_id, '_barcode', true ) . $export->category_separator;
		}
		if ( isset( $order_items_barcode_type ) ) {
			$order_items_barcode_type = substr( $order_items_barcode_type, 0, -1 );
        }
		if ( isset( $order_items_barcode ) ) {
			$order_items_barcode = substr( $order_items_barcode, 0, -1 );
        }
	}

	// WooCommerce UPC, EAN, and ISBN - https://wordpress.org/plugins/woo-add-gtin/
	if ( woo_ce_detect_export_plugin( 'woo_add_gtin' ) ) {
		$order_items_gtin = '';
		foreach ( $order_items as $order_item ) {
			$order_items_gtin .= get_post_meta( $order_item->product_id, 'hwp_product_gtin', true ) . $export->category_separator;
		}
		if ( isset( $order_items_gtin ) ) {
			$order_items_gtin = substr( $order_items_gtin, 0, -1 );
        }
	}

	// WooCommerce Easy Booking - https://wordpress.org/plugins/woocommerce-easy-booking-system/
	if ( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		$order_items_booking_start_date = '';
		$order_items_booking_end_date   = '';
		foreach ( $order_items as $order_item ) {
			$order_items_booking_start_date .= $order_item->booking_start_date . $export->category_separator;
			$order_items_booking_end_date   .= $order_item->booking_end_date . $export->category_separator;
		}
		if ( isset( $order_items_booking_start_date ) ) {
			$order_items_booking_start_date = substr( $order_items_booking_start_date, 0, -1 );
        }
		if ( isset( $order_items_booking_end_date ) ) {
			$order_items_booking_end_date = substr( $order_items_booking_end_date, 0, -1 );
        }
	}

	// N-Media WooCommerce Personalized Product Meta Manager - http://najeebmedia.com/wordpress-plugin/woocommerce-personalized-product-option/
	// PPOM for WooCommerce - https://wordpress.org/plugins/woocommerce-product-addon/
	if ( woo_ce_detect_export_plugin( 'wc_nm_personalizedproduct' ) ||
		woo_ce_detect_export_plugin( 'wc_ppom' )
	) {
		$custom_fields = woo_ce_get_nm_personalized_product_fields();
		if ( ! empty( $custom_fields ) ) {
			foreach ( $custom_fields as $custom_field ) {
				$order->{sprintf( 'order_items_nm_%s', $custom_field['name'] )} = '';
			}
			foreach ( $order_items as $order_item ) {
				foreach ( $custom_fields as $custom_field ) {
					$order->{sprintf( 'order_items_nm_%s', $custom_field['name'] )} .= $order_item->{sprintf( 'nm_%s', $custom_field['name'] )} . $export->category_separator;
				}
			}
			foreach ( $custom_fields as $custom_field ) {
				if ( isset( $order->{sprintf( 'order_items_nm_%s', $custom_field['name'] )} ) ) {
					$order->{sprintf( 'order_items_nm_%s', $custom_field['name'] )} = substr( $order->{sprintf( 'order_items_nm_%s', $custom_field['name'] )}, 0, -1 );
                }
			}
		}
	}

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/
	if ( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		$order_items_appointment_id     = '';
		$order_items_booking_start_date = '';
		$order_items_booking_start_time = '';
		$order_items_booking_end_date   = '';
		$order_items_booking_end_time   = '';
		$order_items_booking_all_day    = '';
		foreach ( $order_items as $order_item ) {
			$order_items_appointment_id     .= ( isset( $order_item->appointment_id ) ? $order_item->appointment_id : '' ) . $export->category_separator;
			$order_items_booking_start_date .= ( isset( $order_item->booking_start_date ) ? $order_item->booking_start_date : '' ) . $export->category_separator;
			$order_items_booking_start_time .= ( isset( $order_item->booking_start_time ) ? $order_item->booking_start_time : '' ) . $export->category_separator;
			$order_items_booking_end_date   .= ( isset( $order_item->booking_end_date ) ? $order_item->booking_end_date : '' ) . $export->category_separator;
			$order_items_booking_end_time   .= ( isset( $order_item->booking_end_time ) ? $order_item->booking_end_time : '' ) . $export->category_separator;
			$order_items_booking_all_day    .= ( isset( $order_item->booking_all_day ) ? $order_item->booking_all_day : '' ) . $export->category_separator;
		}
		if ( isset( $order_items_appointment_id ) ) {
			$order_items_appointment_id = substr( $order_items_appointment_id, 0, -1 );
        }
		if ( isset( $order_items_booking_start_date ) ) {
			$order_items_booking_start_date = substr( $order_items_booking_start_date, 0, -1 );
        }
		if ( isset( $order_items_booking_start_time ) ) {
			$order_items_booking_start_time = substr( $order_items_booking_start_time, 0, -1 );
        }
		if ( isset( $order_items_booking_end_date ) ) {
			$order_items_booking_end_date = substr( $order_items_booking_end_date, 0, -1 );
        }
		if ( isset( $order_items_booking_end_time ) ) {
			$order_items_booking_end_time = substr( $order_items_booking_end_time, 0, -1 );
        }
		if ( isset( $order_items_booking_all_day ) ) {
			$order_items_booking_all_day = substr( $order_items_booking_all_day, 0, -1 );
        }
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/
	if ( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if ( ! empty( $wholesale_roles ) ) {
			foreach ( $wholesale_roles as $key => $wholesale_role ) {
				$order_data[ sprintf( 'order_items_%s_wholesale_price', $key ) ] = '';
			}
			foreach ( $order_items as $order_item ) {
				foreach ( $wholesale_roles as $key => $wholesale_role ) {
					$order_data[ sprintf( 'order_items_%s_wholesale_price', $key ) ] .= ( isset( $order_item[ sprintf( '%s_wholesale_price', $key ) ] ) ? $order_item[ sprintf( '%s_wholesale_price', $key ) ] : '' ) . $export->category_separator;
				}
			}
			foreach ( $wholesale_roles as $key => $wholesale_role ) {
				$order_data[ sprintf( 'order_items_%s_wholesale_price', $key ) ] = substr( $order_data[ sprintf( 'order_items_%s_wholesale_price', $key ) ], 0, -1 );
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	// FooEvents for WooCommerce - https://www.fooevents.com/
	if ( woo_ce_detect_export_plugin( 'fooevents' ) ) {
		$order_items_tickets_purchased             = '';
		$order_items_is_event                      = '';
		$order_items_event_date                    = '';
		$order_items_event_start_time              = '';
		$order_items_event_end_time                = '';
		$order_items_event_venue                   = '';
		$order_items_event_gps                     = '';
		$order_items_event_googlemaps              = '';
		$order_items_event_directions              = '';
		$order_items_event_phone                   = '';
		$order_items_event_email                   = '';
		$order_items_event_ticket_logo             = '';
		$order_items_event_ticket_subject          = '';
		$order_items_event_ticket_text             = '';
		$order_items_event_ticket_thankyou_text    = '';
		$order_items_event_ticket_background_color = '';
		$order_items_event_ticket_button_color     = '';
		$order_items_event_ticket_text_color       = '';
		foreach ( $order_items as $order_item ) {
			$order_items_tickets_purchased             .= ( isset( $order_item->tickets_purchased ) ? $order_item->tickets_purchased : '' ) . $export->category_separator;
			$order_items_is_event                      .= ( isset( $order_item->is_event ) ? $order_item->is_event : '' ) . $export->category_separator;
			$order_items_event_date                    .= ( isset( $order_item->event_date ) ? $order_item->event_date : '' ) . $export->category_separator;
			$order_items_event_start_time              .= ( isset( $order_item->event_start_time ) ? $order_item->event_start_time : '' ) . $export->category_separator;
			$order_items_event_end_time                .= ( isset( $order_item->event_end_time ) ? $order_item->event_end_time : '' ) . $export->category_separator;
			$order_items_event_venue                   .= ( isset( $order_item->event_venue ) ? $order_item->event_venue : '' ) . $export->category_separator;
			$order_items_event_gps                     .= ( isset( $order_item->event_gps ) ? $order_item->event_gps : '' ) . $export->category_separator;
			$order_items_event_googlemaps              .= ( isset( $order_item->event_googlemaps ) ? $order_item->event_googlemaps : '' ) . $export->category_separator;
			$order_items_event_directions              .= ( isset( $order_item->event_directions ) ? $order_item->event_directions : '' ) . $export->category_separator;
			$order_items_event_phone                   .= ( isset( $order_item->event_phone ) ? $order_item->event_phone : '' ) . $export->category_separator;
			$order_items_event_email                   .= ( isset( $order_item->event_email ) ? $order_item->event_email : '' ) . $export->category_separator;
			$order_items_event_ticket_logo             .= ( isset( $order_item->event_ticket_logo ) ? $order_item->event_ticket_logo : '' ) . $export->category_separator;
			$order_items_event_ticket_subject          .= ( isset( $order_item->event_ticket_subject ) ? $order_item->event_ticket_subject : '' ) . $export->category_separator;
			$order_items_event_ticket_text             .= ( isset( $order_item->event_ticket_text ) ? $order_item->event_ticket_text : '' ) . $export->category_separator;
			$order_items_event_ticket_thankyou_text    .= ( isset( $order_item->event_ticket_thankyou_text ) ? $order_item->event_thankyou_ticket_text : '' ) . $export->category_separator;
			$order_items_event_ticket_background_color .= ( isset( $order_item->event_ticket_background_color ) ? $order_item->event_ticket_background_color : '' ) . $export->category_separator;
			$order_items_event_ticket_button_color     .= ( isset( $order_item->event_ticket_button_color ) ? $order_item->event_ticket_button_color : '' ) . $export->category_separator;
			$order_items_event_ticket_text_color       .= ( isset( $order_item->event_ticket_text_color ) ? $order_item->event_ticket_text_color : '' ) . $export->category_separator;
		}
		if ( isset( $order_items_tickets_purchased ) ) {
			$order_items_tickets_purchased = substr( $order_items_tickets_purchased, 0, -1 );
        }
		if ( isset( $order_items_is_event ) ) {
			$order_items_is_event = substr( $order_items_is_event, 0, -1 );
        }
		if ( isset( $order_items_event_date ) ) {
			$order_items_event_date = substr( $order_items_event_date, 0, -1 );
        }
		if ( isset( $order_items_event_start_time ) ) {
			$order_items_event_start_time = substr( $order_items_event_start_time, 0, -1 );
        }
		if ( isset( $order_items_event_end_time ) ) {
			$order_items_event_end_time = substr( $order_items_event_end_time, 0, -1 );
        }
		if ( isset( $order_items_event_venue ) ) {
			$order_items_event_venue = substr( $order_items_event_venue, 0, -1 );
        }
		if ( isset( $order_items_event_gps ) ) {
			$order_items_event_gps = substr( $order_items_event_gps, 0, -1 );
        }
		if ( isset( $order_items_event_googlemaps ) ) {
			$order_items_event_googlemaps = substr( $order_items_event_googlemaps, 0, -1 );
        }
		if ( isset( $order_items_event_directions ) ) {
			$order_items_event_directions = substr( $order_items_event_directions, 0, -1 );
        }
		if ( isset( $order_items_event_phone ) ) {
			$order_items_event_phone = substr( $order_items_event_phone, 0, -1 );
        }
		if ( isset( $order_items_event_email ) ) {
			$order_items_event_email = substr( $order_items_event_email, 0, -1 );
        }
		if ( isset( $order_items_event_ticket_logo ) ) {
			$order_items_event_ticket_logo = substr( $order_items_event_ticket_logo, 0, -1 );
        }
		if ( isset( $order_items_event_ticket_subject ) ) {
			$order_items_event_ticket_subject = substr( $order_items_event_ticket_subject, 0, -1 );
        }
		if ( isset( $order_items_event_ticket_text ) ) {
			$order_items_event_ticket_text = substr( $order_items_event_ticket_text, 0, -1 );
        }
		if ( isset( $order_items_event_ticket_thankyou_text ) ) {
			$order_items_event_ticket_thankyou_text = substr( $order_items_event_ticket_thankyou_text, 0, -1 );
        }
		if ( isset( $order_items_event_ticket_background_color ) ) {
			$order_items_event_ticket_background_color = substr( $order_items_event_ticket_background_color, 0, -1 );
        }
		if ( isset( $order_items_event_ticket_button_color ) ) {
			$order_items_event_ticket_button_color = substr( $order_items_event_ticket_button_color, 0, -1 );
        }
		if ( isset( $order_items_event_ticket_text_color ) ) {
			$order_items_event_ticket_text_color = substr( $order_items_event_ticket_text_color, 0, -1 );
        }
	}

	// AliDropship for WooCommerce - https://alidropship.com/
	if ( woo_ce_detect_export_plugin( 'alidropship' ) ) {
		$order_items_ali_product_id  = '';
		$order_items_ali_product_url = '';
		$order_items_ali_store_url   = '';
		$order_items_ali_store_name  = '';
		foreach ( $order_items as $order_item ) {
			$order_items_ali_product_id  .= ( isset( $order_item->ali_product_id ) ? $order_item->ali_product_id : '' ) . $export->category_separator;
			$order_items_ali_product_url .= ( isset( $order_item->ali_product_url ) ? $order_item->ali_product_url : '' ) . $export->category_separator;
			$order_items_ali_store_url   .= ( isset( $order_item->ali_store_url ) ? $order_item->ali_store_url : '' ) . $export->category_separator;
			$order_items_ali_store_name  .= ( isset( $order_item->ali_store_name ) ? $order_item->ali_store_name : '' ) . $export->category_separator;
		}
		if ( isset( $order_items_ali_product_id ) ) {
			$order_items_ali_product_id = substr( $order_items_ali_product_id, 0, -1 );
        }
		if ( isset( $order_items_ali_product_url ) ) {
			$order_items_ali_product_url = substr( $order_items_ali_product_url, 0, -1 );
        }
		if ( isset( $order_items_ali_store_url ) ) {
			$order_items_ali_store_url = substr( $order_items_ali_store_url, 0, -1 );
        }
		if ( isset( $order_items_ali_store_name ) ) {
			$order_items_ali_store_name = substr( $order_items_ali_store_name, 0, -1 );
        }
	}

	// Bookings and Appointments For WooCommerce Premium - https://www.pluginhive.com/product/woocommerce-booking-and-appointments/
	if ( woo_ce_detect_export_plugin( 'wc_bookings_appointments_pro' ) ) {
		$order_items_session_date   = '';
		$order_items_session_time   = '';
		$order_items_booked_from    = '';
		$order_items_booking_cost   = '';
		$order_items_booking_status = '';
		foreach ( $order_items as $order_item ) {
			$order_items_session_date   .= ( isset( $order_item->session_date ) ? $order_item->session_date : '' ) . $export->category_separator;
			$order_items_session_time   .= ( isset( $order_item->session_time ) ? $order_item->session_time : '' ) . $export->category_separator;
			$order_items_booked_from    .= ( isset( $order_item->booked_from ) ? $order_item->booked_from : '' ) . $export->category_separator;
			$order_items_booking_cost   .= ( isset( $order_item->booking_cost ) ? $order_item->booking_cost : '' ) . $export->category_separator;
			$order_items_booking_status .= ( isset( $order_item->booking_status ) ? $order_item->booking_status : '' ) . $export->category_separator;
		}
		if ( isset( $order_items_session_date ) ) {
			$order_items_session_date = substr( $order_items_session_date, 0, -1 );
        }
		if ( isset( $order_items_session_time ) ) {
			$order_items_session_time = substr( $order_items_session_time, 0, -1 );
        }
		if ( isset( $order_items_booked_from ) ) {
			$order_items_booked_from = substr( $order_items_booked_from, 0, -1 );
        }
		if ( isset( $order_items_booking_cost ) ) {
			$order_items_booking_cost = substr( $order_items_booking_cost, 0, -1 );
        }
		if ( isset( $order_items_booking_status ) ) {
			$order_items_booking_status = substr( $order_items_booking_status, 0, -1 );
        }
	}

	// Yith WooCommerce Subscription - https://yithemes.com/themes/plugins/yith-woocommerce-subscription/
	if ( woo_ce_detect_export_plugin( 'yith_woocommerce_subscription' ) ) {
		$order->yith_subscription_id                 = '';
		$order->yith_subscription_start_date         = '';
		$order->yith_subscription_payment_due_date   = '';
		$order->yith_subscription_end_date           = '';
		$order->yith_subscription_expired_date       = '';
		$order->yith_subscription_cancelled_date     = '';
		$order->yith_subscription_cancelled_by       = '';
		$order->yith_subscription_expired_pause_date = '';
		$order->yith_subscription_status             = '';
		$order->yith_subscription_recurring_price    = '';
		$order->yith_subscription_price_per          = '';
		$order->yith_subscription_trial_per          = '';
		$order->yith_subscription_max_length         = '';
		// $order->yith_subscription_next_payment_due_date = '';
		foreach ( $order_items as $order_item ) {
			$order->yith_subscription_id                 .= ( isset( $order_item->yith_subscription_id ) ? $order_item->yith_subscription_id : '' ) . $export->category_separator;
			$order->yith_subscription_start_date         .= ( isset( $order_item->yith_subscription_start_date ) ? $order_item->yith_subscription_start_date : '' ) . $export->category_separator;
			$order->yith_subscription_payment_due_date   .= ( isset( $order_item->yith_subscription_payment_due_date ) ? $order_item->yith_subscription_payment_due_date : '' ) . $export->category_separator;
			$order->yith_subscription_payment_due_date   .= ( isset( $order_item->yith_subscription_payment_due_date ) ? $order_item->yith_subscription_payment_due_date : '' ) . $export->category_separator;
			$order->yith_subscription_end_date           .= ( isset( $order_item->yith_subscription_end_date ) ? $order_item->yith_subscription_end_date : '' ) . $export->category_separator;
			$order->yith_subscription_expired_date       .= ( isset( $order_item->yith_subscription_expired_date ) ? $order_item->yith_subscription_expired_date : '' ) . $export->category_separator;
			$order->yith_subscription_cancelled_date     .= ( isset( $order_item->yith_subscription_cancelled_date ) ? $order_item->yith_subscription_cancelled_date : '' ) . $export->category_separator;
			$order->yith_subscription_cancelled_by       .= ( isset( $order_item->yith_subscription_cancelled_by ) ? $order_item->yith_subscription_cancelled_by : '' ) . $export->category_separator;
			$order->yith_subscription_expired_pause_date .= ( isset( $order_item->yith_subscription_expired_pause_date ) ? $order_item->yith_subscription_expired_pause_date : '' ) . $export->category_separator;
			$order->yith_subscription_status             .= ( isset( $order_item->yith_subscription_status ) ? $order_item->yith_subscription_status : '' ) . $export->category_separator;
			$order->yith_subscription_recurring_price    .= ( isset( $order_item->yith_subscription_recurring_price ) ? $order_item->yith_subscription_recurring_price : '' ) . $export->category_separator;
			$order->yith_subscription_price_per          .= ( isset( $order_item->yith_subscription_price_per ) ? $order_item->yith_subscription_price_per : '' ) . $export->category_separator;
			$order->yith_subscription_trial_per          .= ( isset( $order_item->yith_subscription_trial_per ) ? $order_item->yith_subscription_trial_per : '' ) . $export->category_separator;
			$order->yith_subscription_max_length         .= ( isset( $order_item->yith_subscription_max_length ) ? $order_item->yith_subscription_max_length : '' ) . $export->category_separator;
			// $order->yith_subscription_next_payment_due_date .= ( isset( $order_item->yith_subscription_next_payment_due_date ) ? $order_item->yith_subscription_next_payment_due_date : '' ) . $export->category_separator;
		}
		if ( isset( $order->yith_subscription_id ) ) {
			$order->yith_subscription_id = substr( $order->yith_subscription_id, 0, -1 );
        }
		if ( isset( $order->yith_subscription_start_date ) ) {
			$order->yith_subscription_start_date = substr( $order->yith_subscription_start_date, 0, -1 );
        }
		if ( isset( $order->yith_subscription_payment_due_date ) ) {
			$order->yith_subscription_payment_due_date = substr( $order->yith_subscription_payment_due_date, 0, -1 );
        }
		if ( isset( $order->yith_subscription_end_date ) ) {
			$order->yith_subscription_end_date = substr( $order->yith_subscription_end_date, 0, -1 );
        }
		if ( isset( $order->yith_subscription_expired_date ) ) {
			$order->yith_subscription_expired_date = substr( $order->yith_subscription_expired_date, 0, -1 );
        }
		if ( isset( $order->yith_subscription_cancelled_date ) ) {
			$order->yith_subscription_cancelled_date = substr( $order->yith_subscription_cancelled_date, 0, -1 );
        }
		if ( isset( $order->yith_subscription_cancelled_by ) ) {
			$order->yith_subscription_cancelled_by = substr( $order->yith_subscription_cancelled_by, 0, -1 );
        }
		if ( isset( $order->yith_subscription_expired_pause_date ) ) {
			$order->yith_subscription_expired_pause_date = substr( $order->yith_subscription_expired_pause_date, 0, -1 );
        }
		if ( isset( $order->yith_subscription_status ) ) {
			$order->yith_subscription_status = substr( $order->yith_subscription_status, 0, -1 );
        }
		if ( isset( $order->yith_subscription_recurring_price ) ) {
			$order->yith_subscription_recurring_price = substr( $order->yith_subscription_recurring_price, 0, -1 );
        }
		if ( isset( $order->yith_subscription_price_per ) ) {
			$order->yith_subscription_price_per = substr( $order->yith_subscription_price_per, 0, -1 );
        }
		if ( isset( $order->yith_subscription_trial_per ) ) {
			$order->yith_subscription_trial_per = substr( $order->yith_subscription_trial_per, 0, -1 );
        }
		if ( isset( $order->yith_subscription_max_length ) ) {
			$order->yith_subscription_max_length = substr( $order->yith_subscription_max_length, 0, -1 );
        }
		// if( isset( $order->yith_subscription_next_payment_due_date ) )
		// $order->yith_subscription_next_payment_due_date = substr( $order->yith_subscription_next_payment_due_date, 0, -1 );
	}

	// WooCommerce Warranty Requests - https://woocommerce.com/products/warranty-requests/
	if ( woo_ce_detect_export_plugin( 'wc_warranty' ) ) {

		$order->wc_warranty_id                    = '';
		$order->wc_warranty_code                  = '';
		$order->wc_warranty_request_type          = '';
		$order->wc_warranty_return_tracking_code  = '';
		$order->wc_warranty_request_tracking_code = '';
		$order->wc_warranty_shipping_label        = '';
		$order->wc_warranty_type                  = '';
		$order->wc_warranty_length                = '';
		$order->wc_warranty_value                 = '';
		$order->wc_warranty_duration              = '';

		foreach ( $order_items as $order_item ) {
			$order->wc_warranty_id                    .= ( isset( $order_item->wc_warranty_id ) ? $order_item->wc_warranty_id : '' ) . $export->category_separator;
			$order->wc_warranty_code                  .= ( isset( $order_item->wc_warranty_code ) ? $order_item->wc_warranty_code : '' ) . $export->category_separator;
			$order->wc_warranty_request_type          .= ( isset( $order_item->wc_warranty_request_type ) ? $order_item->wc_warranty_request_type : '' ) . $export->category_separator;
			$order->wc_warranty_return_tracking_code  .= ( isset( $order_item->wc_warranty_return_tracking_code ) ? $order_item->wc_warranty_return_tracking_code : '' ) . $export->category_separator;
			$order->wc_warranty_request_tracking_code .= ( isset( $order_item->wc_warranty_request_tracking_code ) ? $order_item->wc_warranty_request_tracking_code : '' ) . $export->category_separator;
			$order->wc_warranty_shipping_label        .= ( isset( $order_item->wc_warranty_shipping_label ) ? $order_item->wc_warranty_shipping_label : '' ) . $export->category_separator;
			$order->wc_warranty_type                  .= ( isset( $order_item->wc_warranty_type ) ? $order_item->wc_warranty_type : '' ) . $export->category_separator;
			$order->wc_warranty_length                .= ( isset( $order_item->wc_warranty_length ) ? $order_item->wc_warranty_length : '' ) . $export->category_separator;
			$order->wc_warranty_value                 .= ( isset( $order_item->wc_warranty_value ) ? $order_item->wc_warranty_value : '' ) . $export->category_separator;
			$order->wc_warranty_duration              .= ( isset( $order_item->wc_warranty_duration ) ? $order_item->wc_warranty_duration : '' ) . $export->category_separator;
		}

		if ( isset( $order->wc_warranty_id ) ) {
			$order->wc_warranty_id = substr( $order->wc_warranty_id, 0, -1 );
        }
		if ( isset( $order->wc_warranty_code ) ) {
			$order->wc_warranty_code = substr( $order->wc_warranty_code, 0, -1 );
        }
		if ( isset( $order->wc_warranty_request_type ) ) {
			$order->wc_warranty_request_type = substr( $order->wc_warranty_request_type, 0, -1 );
        }
		if ( isset( $order->wc_warranty_return_tracking_code ) ) {
			$order->wc_warranty_return_tracking_code = substr( $order->wc_warranty_return_tracking_code, 0, -1 );
        }
		if ( isset( $order->wc_warranty_request_tracking_code ) ) {
			$order->wc_warranty_request_tracking_code = substr( $order->wc_warranty_request_tracking_code, 0, -1 );
        }
		if ( isset( $order->wc_warranty_shipping_label ) ) {
			$order->wc_warranty_shipping_label = substr( $order->wc_warranty_shipping_label, 0, -1 );
        }
		if ( isset( $order->wc_warranty_type ) ) {
			$order->wc_warranty_type = substr( $order->wc_warranty_type, 0, -1 );
        }
		if ( isset( $order->wc_warranty_length ) ) {
			$order->wc_warranty_length = substr( $order->wc_warranty_length, 0, -1 );
        }
		if ( isset( $order->wc_warranty_value ) ) {
			$order->wc_warranty_value = substr( $order->wc_warranty_value, 0, -1 );
        }
		if ( isset( $order->wc_warranty_duration ) ) {
			$order->wc_warranty_duration = substr( $order->wc_warranty_duration, 0, -1 );
        }
	}

	// Tax Rates
	$tax_rates = woo_ce_get_order_tax_rates();

	if ( ! empty( $tax_rates ) ) {
		foreach ( $tax_rates as $tax_rate ) {
			$order_data[ sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] ) ] = '';
		}
		foreach ( $order_items as $order_item ) {
			foreach ( $tax_rates as $tax_rate ) {
				if ( isset( $order_item[ sprintf( 'tax_rate_%d', $tax_rate['rate_id'] ) ] ) ) {
					$order_data[ sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] ) ] = $order_item[ sprintf( 'tax_rate_%d', $tax_rate['rate_id'] ) ];
				}
			}
		}
		foreach ( $tax_rates as $tax_rate ) {
			if ( isset( $order_data[ sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] ) ] ) ) {
				$order_data[ sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] ) ] = substr( $order_data[ sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] ) ], 0, -1 );
            }
		}
	}
	unset( $tax_rates, $tax_rate );

	// Variation Attributes
	// Product Attributes
	if ( apply_filters( 'woo_ce_enable_product_attributes', true ) ) {
		$attributes = woo_ce_get_product_attributes( 'attribute_name' );
		if (
			! empty( $attributes ) &&
			$order_items
		) {
			foreach ( $attributes as $attribute ) {
				$key = sanitize_key( urlencode( $attribute ) );
				$order->{sprintf( 'order_items_attribute_%s', $key )}         = '';
				$order->{sprintf( 'order_items_product_attribute_%s', $key )} = '';
			}
			foreach ( $order_items as $order_item ) {
				foreach ( $attributes as $attribute ) {
					$key = sanitize_key( urlencode( $attribute ) );
					if ( isset( $order_item->{sprintf( 'attribute_%s', $key )} ) ) {
						$order->{sprintf( 'order_items_attribute_%s', $key )} .= woo_ce_format_custom_meta( $order_item->{sprintf( 'attribute_%s', $key )} ) . $export->category_separator;
                    }
					if ( isset( $order_item->{sprintf( 'product_attribute_%s', $key )} ) ) {
						$order->{sprintf( 'order_items_product_attribute_%s', $key )} .= woo_ce_format_custom_meta( $order_item->{sprintf( 'product_attribute_%s', $key )} ) . $export->category_separator;
                    }
				}
			}
			foreach ( $attributes as $attribute ) {
				$key = sanitize_key( urlencode( $attribute ) );
				if ( isset( $order->{sprintf( 'order_items_attribute_%s', $key )} ) ) {
					$order->{sprintf( 'order_items_attribute_%s', $key )} = substr( $order->{sprintf( 'order_items_attribute_%s', $key )}, 0, -1 );
                }
				if ( isset( $order->{sprintf( 'order_items_product_attribute_%s', $key )} ) ) {
					$order->{sprintf( 'order_items_product_attribute_%s', $key )} = substr( $order->{sprintf( 'order_items_product_attribute_%s', $key )}, 0, -1 );
                }
			}
			unset( $key );
		}
		unset( $attributes, $attribute );
	}

	// Custom Order Items fields
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	if (
		! empty( $custom_order_items ) &&
		$order_items
	) {
		foreach ( $custom_order_items as $custom_order_item ) {
			$order->{sprintf( 'order_items_%s', $custom_order_item )} = '';
        }
		foreach ( $order_items as $order_item ) {
			foreach ( $custom_order_items as $custom_order_item ) {
				if ( ! empty( $custom_order_item ) && isset( $order_item->{$custom_order_item} ) ) {
					$order->{sprintf( 'order_items_%s', $custom_order_item )} .= woo_ce_format_custom_meta( $order_item->{$custom_order_item} ) . $export->category_separator;
                }
			}
		}
		foreach ( $custom_order_items as $custom_order_item ) {
			if ( isset( $order->{sprintf( 'order_items_%s', $custom_order_item )} ) ) {
				$order->{sprintf( 'order_items_%s', $custom_order_item )} = substr( $order->{sprintf( 'order_items_%s', $custom_order_item )}, 0, -1 );
            }
		}
	}
	unset( $custom_order_items, $custom_order_item );

	// Custom Order Item Product fields
	$custom_order_products = woo_ce_get_option( 'custom_order_products', '' );
	if (
		! empty( $custom_order_products ) &&
		$order_items
	) {
		foreach ( $custom_order_products as $custom_order_product ) {
			$order->{sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) )} = '';
        }
		foreach ( $order_items as $order_item ) {
			foreach ( $custom_order_products as $custom_order_product ) {
				$sanitized_key_custom_order_product = sanitize_key( $custom_order_product );
				if ( ! empty( $custom_order_product ) && isset( $order_item->{$sanitized_key_custom_order_product} ) ) {
					$order->{sprintf( 'order_items_%s', $sanitized_key_custom_order_product )} .= woo_ce_format_custom_meta( $order_item->{$sanitized_key_custom_order_product} ) . $export->category_separator;
                }
			}
		}
		foreach ( $custom_order_products as $custom_order_product ) {
			if ( isset( $order->{sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) )} ) ) {
				$order->{sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) )} = substr( $order->{sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) )}, 0, -1 );
            }
		}
	}
	unset( $custom_order_products, $custom_order_product );

	// Custom Product fields
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if (
		! empty( $custom_products ) &&
		$order_items
	) {
		foreach ( $custom_products as $custom_product ) {
			$order->{sprintf( 'order_items_%s', sanitize_key( $custom_product ) )} = '';
        }
		foreach ( $order_items as $order_item ) {
			foreach ( $custom_products as $custom_product ) {
				$sanitized_key_custom_product = sanitize_key( $custom_product );
				if ( ! empty( $custom_product ) && isset( $order_item->{$sanitized_key_custom_product} ) ) {
					$order->{sprintf( 'order_items_%s', $sanitized_key_custom_product )} .= $order_item->{$sanitized_key_custom_product} . $export->category_separator;
                }
			}
		}
		foreach ( $custom_products as $custom_product ) {
			if ( isset( $order->{sprintf( 'order_items_%s', sanitize_key( $custom_product ) )} ) ) {
				$order->{sprintf( 'order_items_%s', sanitize_key( $custom_product ) )} = substr( $order->{sprintf( 'order_items_%s', sanitize_key( $custom_product ) )}, 0, -1 );
            }
		}
	}
	unset( $custom_products, $custom_product );

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	return $order_data;
}
add_filter( 'woo_ce_order_items_combined', 'woo_ce_extend_order_items_combined', 10, 3 );
