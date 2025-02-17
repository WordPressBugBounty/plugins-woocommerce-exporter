var $j = jQuery.noConflict();
$j(function () {
  // This controls the Skip Overview link on the Overview screen
  $j("#skip_overview").click(function () {
    $j("#skip_overview_form").submit();
  });

  // Order Dates
  $j('input[name="order_dates_filter_variable"],select[name="order_dates_filter_variable_length"]').click(function () {
    $j('input:radio[name="order_dates_filter"][value="variable"]').prop("checked", true);
  });

  // Chosen dropdown element
  if ($j.isFunction($j.fn.chosen)) {
    $j(".chzn-select").chosen({
      search_contains: true,
      width: "95%",
    });
  }

  // Sortable export columns
  if ($j.isFunction($j.fn.sortable)) {
    $j("table.ui-sortable").sortable({
      items: "tr",
      cursor: "move",
      axis: "y",
      handle: "td",
      scrollSensitivity: 40,
      helper: function (e, ui) {
        ui.children().each(function () {
          $j(this).width($j(this).width());
        });
        ui.css("left", "0");
        return ui;
      },
      start: function (event, ui) {
        ui.item.css("background-color", "#f6f6f6");
      },
      stop: function (event, ui) {
        ui.item.removeAttr("style");
        field_row_indexes(this);
      },
    });

    function field_row_indexes(obj) {
      rows = $j(obj).find("tr");
      $j(rows).each(function (index, el) {
        $j("input.field_order", el).val(parseInt($j(el).index()));
      });
    }
  }

  // Select all field options for this export type
  $j(".checkall").click(function () {
    $j(this).closest(".postbox").find('input[type="checkbox"]:not(:disabled)').prop("checked", true);
  });

  // Unselect all field options for this export type
  $j(".uncheckall").click(function () {
    $j(this).closest(".postbox").find('input[type="checkbox"]:not(:disabled)').prop("checked", false);
  });

  // Reset sorting of fields for this export type
  $j(".resetsorting").click(function () {
    var type = $j(this).prop("id");
    var type = type.replace("-resetsorting", "");
    for (i = 0; i < $j("#" + type + "-fields tr").length; i++) {
      $j("#" + type + "-" + i).appendTo("#" + type + "-fields");
    }
    field_row_indexes($j("#" + type + "-fields"));
  });

  // Clear all options on Field Editor
  $j(".fields-clearall").click(function () {
    $j(this).closest(".form-table ").find(".all-options").prop("value", "");
  });

  $j(".export-types").hide();
  $j(".export-options").hide();

  // Categories
  $j("#export-products-filters-categories").hide();
  if ($j("#products-filters-categories").prop("checked")) {
    $j("#export-products-filters-categories").show();
  }
  // Tags
  $j("#export-products-filters-tags").hide();
  if ($j("#products-filters-tags").prop("checked")) {
    $j("#export-products-filters-tags").show();
  }
  // Brands
  $j("#export-products-filters-brands").hide();
  if ($j("#products-filters-brands").prop("checked")) {
    $j("#export-products-filters-brands").show();
  }
  // Product Vendors
  $j("#export-products-filters-vendors").hide();
  if ($j("#products-filters-vendors").prop("checked")) {
    $j("#export-products-filters-vendors").show();
  }
  // Product Status
  $j("#export-products-filters-status").hide();
  if ($j("#products-filters-status").prop("checked")) {
    $j("#export-products-filters-status").show();
  }
  // Type
  $j("#export-products-filters-type").hide();
  if ($j("#products-filters-type").prop("checked")) {
    $j("#export-products-filters-type").show();
  }
  // SKU
  $j("#export-products-filters-sku").hide();
  if ($j("#products-filters-sku").prop("checked")) {
    $j("#export-products-filters-sku").show();
  }
  // User Role
  $j("#export-products-filters-user_role").hide();
  if ($j("#products-filters-user_role").prop("checked")) {
    $j("#export-products-filters-user_role").show();
  }
  // Stock
  $j("#export-products-filters-stock").hide();
  if ($j("#products-filters-stock").prop("checked")) {
    $j("#export-products-filters-stock").show();
  }
  // Quantity
  $j("#export-products-filters-quantity").hide();
  if ($j("#products-filters-quantity").prop("checked")) {
    $j("#export-products-filters-quantity").show();
  }
  // Featured
  $j("#export-products-filters-featured").hide();
  if ($j("#products-filters-featured").prop("checked")) {
    $j("#export-products-filters-featured").show();
  }
  // Shipping Classes
  $j("#export-products-filters-shipping_class").hide();
  if ($j("#products-filters-shipping_class").prop("checked")) {
    $j("#export-products-filters-shipping_class").show();
  }
  // Featured Image
  $j("#export-products-filters-featured-image").hide();
  if ($j("#products-filters-featured-image").prop("checked")) {
    $j("#export-products-filters-featured-image").show();
  }
  // Product Gallery
  $j("#export-products-filters-gallery").hide();
  if ($j("#products-filters-gallery").prop("checked")) {
    $j("#export-products-filters-gallery").show();
  }
  // Language
  $j("#export-products-filters-language").hide();
  if ($j("#products-filters-language").prop("checked")) {
    $j("#export-products-filters-language").show();
  }
  // Date Published
  $j("#export-products-filters-date_published").hide();
  if ($j("#products-filters-date_published").prop("checked")) {
    $j("#export-products-filters-date_published").show();
  }
  // Date Modified
  $j("#export-products-filters-date_modified").hide();
  if ($j("#products-filters-date_modified").prop("checked")) {
    $j("#export-products-filters-date_modified").show();
  }
  // Product meta
  $j("#export-products-filters-product_meta").hide();
  if ($j("#products-filters-product_meta").prop("checked")) {
    $j("#export-products-filters-product_meta").show();
  }
  // Product meta
  $j("#export-products-filters-custom_field_sorting").hide();
  if ($j("#products-filters-custom_field_sorting").prop("checked")) {
    $j("#export-products-filters-custom_field_sorting").show();
  }

  $j("#export-category").hide();
  // Language
  $j("#export-categories-filters-language").hide();
  if ($j("#categories-filters-language").prop("checked")) {
    $j("#export-categories-filters-language").show();
  }

  $j("#export-tag").hide();
  // Language
  $j("#export-tags-filters-language").hide();
  if ($j("#tags-filters-language").prop("checked")) {
    $j("#export-tags-filters-language").show();
  }

  $j("#export-brand").hide();

  $j("#export-order").hide();
  // Order Status
  $j("#export-orders-filters-status").hide();
  if ($j("#orders-filters-status").prop("checked")) {
    $j("#export-orders-filters-status").show();
  }
  // Order Date
  $j("#export-orders-filters-date").hide();
  if ($j("#orders-filters-date").prop("checked")) {
    $j("#export-orders-filters-date").show();
  }
  // Order Modified Date
  $j("#export-orders-filters-modified-date").hide();
  if ($j("#orders-filters-modified-date").prop("checked")) {
    $j("#export-orders-filters-modified-date").show();
  }
  // Customer
  $j("#export-orders-filters-customer").hide();
  if ($j("#orders-filters-customer").prop("checked")) {
    $j("#export-orders-filters-customer").show();
  }
  // Billing Country
  $j("#export-orders-filters-billing_country").hide();
  if ($j("#orders-filters-billing_country").prop("checked")) {
    $j("#export-orders-filters-billing_country").show();
  }
  // Shipping Country
  $j("#export-orders-filters-shipping_country").hide();
  if ($j("#orders-filters-shipping_country").prop("checked")) {
    $j("#export-orders-filters-shipping_country").show();
  }
  // User Role
  $j("#export-orders-filters-user_role").hide();
  if ($j("#orders-filters-user_role").prop("checked")) {
    $j("#export-orders-filters-user_role").show();
  }
  // Coupon Code
  $j("#export-orders-filters-coupon").hide();
  if ($j("#orders-filters-coupon").prop("checked")) {
    $j("#export-orders-filters-coupon").show();
  }
  // Products
  $j("#export-orders-filters-product").hide();
  if ($j("#orders-filters-product").prop("checked")) {
    $j("#export-orders-filters-product").show();
  }
  // Categories
  $j("#export-orders-filters-category").hide();
  if ($j("#orders-filters-category").prop("checked")) {
    $j("#export-orders-filters-category").show();
  }
  // Tags
  $j("#export-orders-filters-tag").hide();
  if ($j("#orders-filters-tag").prop("checked")) {
    $j("#export-orders-filters-tag").show();
  }
  // Brands
  $j("#export-orders-filters-brand").hide();
  if ($j("#orders-filters-brand").prop("checked")) {
    $j("#export-orders-filters-brand").show();
  }
  // Order ID
  $j("#export-orders-filters-id").hide();
  if ($j("#orders-filters-id").prop("checked")) {
    $j("#export-orders-filters-id").show();
  }
  // Payment Gateway
  $j("#export-orders-filters-payment_gateway").hide();
  if ($j("#orders-filters-payment_gateway").prop("checked")) {
    $j("#export-orders-filters-payment_gateway").show();
  }
  // Payment Gateway
  $j("#export-orders-filters-shipping_method").hide();
  if ($j("#orders-filters-shipping_method").prop("checked")) {
    $j("#export-orders-filters-shipping_method").show();
  }
  // Digital Products
  $j("#export-orders-filters-digital_products").hide();
  if ($j("#orders-filters-digital_products").prop("checked")) {
    $j("#export-orders-filters-digital_products").show();
  }
  // Product Vendor
  $j("#export-orders-filters-product_vendor").hide();
  if ($j("#orders-filters-product_vendor").prop("checked")) {
    $j("#export-orders-filters-product_vendor").show();
  }
  // Delivery Date
  $j("#export-orders-filters-delivery_date").hide();
  if ($j("#orders-filters-delivery_date").prop("checked")) {
    $j("#export-orders-filters-delivery_date").show();
  }
  // Bookings
  $j("#export-orders-filters-booking_date").hide();
  if ($j("#orders-filters-booking_date").prop("checked")) {
    $j("#export-orders-filters-booking_date").show();
  }
  // Booking Start Date
  $j("#export-orders-filters-booking_start_date").hide();
  if ($j("#orders-filters-booking_start_date").prop("checked")) {
    $j("#export-orders-filters-booking_start_date").show();
  }
  // Voucher Redeemed
  $j("#export-orders-filters-voucher_redeemed").hide();
  if ($j("#orders-filters-voucher_redeemed").prop("checked")) {
    $j("#export-orders-filters-voucher_redeemed").show();
  }
  // Order Type
  $j("#export-orders-filters-order_type").hide();
  if ($j("#orders-filters-order_type").prop("checked")) {
    $j("#export-orders-filters-order_type").show();
  }
  // Order meta
  $j("#export-orders-filters-order_meta").hide();
  if ($j("#orders-filters-order_meta").prop("checked")) {
    $j("#export-orders-filters-order_meta").show();
  }

  // Order Status
  $j("#export-customers-filters-status").hide();
  if ($j("#customers-filters-status").prop("checked")) {
    $j("#export-customers-filters-status").show();
  }
  // User Role
  $j("#export-customers-filters-user_role").hide();
  if ($j("#customers-filters-user_role").prop("checked")) {
    $j("#export-customers-filters-user_role").show();
  }

  // Subscription Date
  $j("#export-subscriptions-filters-date").hide();
  if ($j("#subscriptions-filters-date").prop("checked")) {
    $j("#export-subscriptions-filters-date").show();
  }
  // Subscription Status
  $j("#export-subscriptions-filters-status").hide();
  if ($j("#subscriptions-filters-status").prop("checked")) {
    $j("#export-subscriptions-filters-status").show();
  }
  // Subscription Product
  $j("#export-subscriptions-filters-product").hide();
  if ($j("#subscriptions-filters-product").prop("checked")) {
    $j("#export-subscriptions-filters-product").show();
  }
  // Customer
  $j("#export-subscriptions-filters-customer").hide();
  if ($j("#subscriptions-filters-customer").prop("checked")) {
    $j("#export-subscriptions-filters-customer").show();
  }
  // Customer
  $j("#export-subscriptions-filters-source").hide();
  if ($j("#subscriptions-filters-source").prop("checked")) {
    $j("#export-subscriptions-filters-source").show();
  }

  // Order Date
  $j("#export-commissions-filters-date").hide();
  if ($j("#commissions-filters-date").prop("checked")) {
    $j("#export-commissions-filters-date").show();
  }
  // Product Vendor
  $j("#export-commissions-filters-product_vendor").hide();
  if ($j("#commissions-filters-product_vendor").prop("checked")) {
    $j("#export-commissions-filters-product_vendor").show();
  }
  // Commission Status
  $j("#export-commissions-filters-commission_status").hide();
  if ($j("#commissions-filters-commission_status").prop("checked")) {
    $j("#export-commissions-filters-commission_status").show();
  }

  // Discount Type
  $j("#export-coupons-filters-discount_types").hide();
  if ($j("#coupons-filters-discount_types").prop("checked")) {
    $j("#export-coupons-filters-discount_types").show();
  }

  // User Role
  $j("#export-users-filters-user_role").hide();
  if ($j("#users-filters-user_role").prop("checked")) {
    $j("#export-users-filters-user_role").show();
  }
  // Date Registered
  $j("#export-users-filters-date_registered").hide();
  if ($j("#users-filters-date_registered").prop("checked")) {
    $j("#export-users-filters-date_registered").show();
  }
  // Date Last Updated
  $j("#export-users-filters-modified-date").hide();
  if ($j("#users-filters-modified-date").prop("checked")) {
    $j("#export-users-filters-modified-date").show();
  }

  $j("#export-customer").hide();
  $j("#export-user").hide();
  $j("#export-review").hide();
  $j("#export-coupon").hide();
  $j("#export-subscription").hide();
  $j("#export-product_vendor").hide();
  $j("#export-commission").hide();
  $j("#export-shipping_class").hide();
  $j("#export-ticket").hide();
  $j("#export-attribute").hide();

  $j("#products-filters-categories").click(function () {
    $j("#export-products-filters-categories").toggle();
  });
  $j("#products-filters-tags").click(function () {
    $j("#export-products-filters-tags").toggle();
  });
  $j("#products-filters-brands").click(function () {
    $j("#export-products-filters-brands").toggle();
  });
  $j("#products-filters-vendors").click(function () {
    $j("#export-products-filters-vendors").toggle();
  });
  $j("#products-filters-status").click(function () {
    $j("#export-products-filters-status").toggle();
  });
  $j("#products-filters-type").click(function () {
    $j("#export-products-filters-type").toggle();
  });
  $j("#products-filters-sku").click(function () {
    $j("#export-products-filters-sku").toggle();
  });
  $j("#products-filters-user_role").click(function () {
    $j("#export-products-filters-user_role").toggle();
  });
  $j("#products-filters-stock").click(function () {
    $j("#export-products-filters-stock").toggle();
  });
  $j("#products-filters-quantity").click(function () {
    $j("#export-products-filters-quantity").toggle();
  });
  $j("#products-filters-featured").click(function () {
    $j("#export-products-filters-featured").toggle();
  });
  $j("#products-filters-shipping_class").click(function () {
    $j("#export-products-filters-shipping_class").toggle();
  });
  $j("#products-filters-featured-image").click(function () {
    $j("#export-products-filters-featured-image").toggle();
  });
  $j("#products-filters-gallery").click(function () {
    $j("#export-products-filters-gallery").toggle();
  });
  $j("#products-filters-language").click(function () {
    $j("#export-products-filters-language").toggle();
  });
  $j("#products-filters-date_published").click(function () {
    $j("#export-products-filters-date_published").toggle();
  });
  $j("#products-filters-date_modified").click(function () {
    $j("#export-products-filters-date_modified").toggle();
  });
  $j("#products-filters-product_meta").click(function () {
    $j("#export-products-filters-product_meta").toggle();
  });
  $j("#products-filters-custom_field_sorting").click(function () {
    $j("#export-products-filters-custom_field_sorting").toggle();
  });

  $j("#categories-filters-language").click(function () {
    $j("#export-categories-filters-language").toggle();
  });

  $j("#tags-filters-language").click(function () {
    $j("#export-tags-filters-language").toggle();
  });

  $j("#orders-filters-date").click(function () {
    $j("#export-orders-filters-date").toggle();
  });
  $j("#orders-filters-modified-date").click(function () {
    $j("#export-orders-filters-modified-date").toggle();
  });
  $j("#orders-filters-status").click(function () {
    $j("#export-orders-filters-status").toggle();
  });
  $j("#orders-filters-customer").click(function () {
    $j("#export-orders-filters-customer").toggle();
  });
  $j("#orders-filters-billing_country").click(function () {
    $j("#export-orders-filters-billing_country").toggle();
  });
  $j("#orders-filters-shipping_country").click(function () {
    $j("#export-orders-filters-shipping_country").toggle();
  });
  $j("#orders-filters-user_role").click(function () {
    $j("#export-orders-filters-user_role").toggle();
  });
  $j("#orders-filters-coupon").click(function () {
    $j("#export-orders-filters-coupon").toggle();
  });
  $j("#orders-filters-product").click(function () {
    $j("#export-orders-filters-product").toggle();
  });
  $j("#orders-filters-category").click(function () {
    $j("#export-orders-filters-category").toggle();
  });
  $j("#orders-filters-tag").click(function () {
    $j("#export-orders-filters-tag").toggle();
  });
  $j("#orders-filters-brand").click(function () {
    $j("#export-orders-filters-brand").toggle();
  });
  $j("#orders-filters-id").click(function () {
    $j("#export-orders-filters-id").toggle();
  });
  $j("#orders-filters-payment_gateway").click(function () {
    $j("#export-orders-filters-payment_gateway").toggle();
  });
  $j("#orders-filters-shipping_method").click(function () {
    $j("#export-orders-filters-shipping_method").toggle();
  });
  $j("#orders-filters-digital_products").click(function () {
    $j("#export-orders-filters-digital_products").toggle();
  });
  $j("#orders-filters-product_vendor").click(function () {
    $j("#export-orders-filters-product_vendor").toggle();
  });
  $j("#orders-filters-delivery_date").click(function () {
    $j("#export-orders-filters-delivery_date").toggle();
  });
  $j("#orders-filters-booking_date").click(function () {
    $j("#export-orders-filters-booking_date").toggle();
  });
  $j("#orders-filters-booking_start_date").click(function () {
    $j("#export-orders-filters-booking_start_date").toggle();
  });
  $j("#orders-filters-voucher_redeemed").click(function () {
    $j("#export-orders-filters-voucher_redeemed").toggle();
  });
  $j("#orders-filters-order_type").click(function () {
    $j("#export-orders-filters-order_type").toggle();
  });
  $j("#orders-filters-order_meta").click(function () {
    $j("#export-orders-filters-order_meta").toggle();
  });

  $j("#customers-filters-status").click(function () {
    $j("#export-customers-filters-status").toggle();
  });
  $j("#customers-filters-user_role").click(function () {
    $j("#export-customers-filters-user_role").toggle();
  });

  $j("#subscriptions-filters-date").click(function () {
    $j("#export-subscriptions-filters-date").toggle();
  });
  $j("#subscriptions-filters-status").click(function () {
    $j("#export-subscriptions-filters-status").toggle();
  });
  $j("#subscriptions-filters-product").click(function () {
    $j("#export-subscriptions-filters-product").toggle();
  });
  $j("#subscriptions-filters-customer").click(function () {
    $j("#export-subscriptions-filters-customer").toggle();
  });
  $j("#subscriptions-filters-source").click(function () {
    $j("#export-subscriptions-filters-source").toggle();
  });

  $j("#commissions-filters-date").click(function () {
    $j("#export-commissions-filters-date").toggle();
  });
  $j("#commissions-filters-product_vendor").click(function () {
    $j("#export-commissions-filters-product_vendor").toggle();
  });
  $j("#commissions-filters-commission_status").click(function () {
    $j("#export-commissions-filters-commission_status").toggle();
  });

  $j("#coupons-filters-discount_types").click(function () {
    $j("#export-coupons-filters-discount_types").toggle();
  });

  $j("#users-filters-user_role").click(function () {
    $j("#export-users-filters-user_role").toggle();
  });
  $j("#users-filters-date_registered").click(function () {
    $j("#export-users-filters-date_registered").toggle();
  });
  $j("#users-filters-modified-date").click(function () {
    $j("#export-users-filters-modified-date").toggle();
  });

  // Export types
  $j("#product").click(function () {
    $j(".export-types").hide();
    $j("#export-product").show();

    $j(".export-options").hide();
    $j(".product-options").show();
    // Max unique Product Gallery images
    $j("#max_product_gallery_option").hide();
    var product_gallery_unique = $j("input:radio[name=product_gallery_unique]:checked").val();
    if (product_gallery_unique == "1") $j("#max_product_gallery_option").show();
  });
  $j("#category").click(function () {
    $j(".export-types").hide();
    $j("#export-category").show();

    $j(".export-options").hide();
    $j(".category-options").show();
  });
  $j("#tag").click(function () {
    $j(".export-types").hide();
    $j("#export-tag").show();

    $j(".export-options").hide();
    $j(".tag-options").show();
  });
  $j("#brand").click(function () {
    $j(".export-types").hide();
    $j("#export-brand").show();

    $j(".export-options").hide();
    $j(".brand-options").show();
  });
  $j("#order").click(function () {
    $j(".export-types").hide();
    $j("#export-order").show();

    $j(".export-options").hide();
    $j(".order-options").show();
    // Max unique Order Items
    $j("#max_order_items_option").hide();
    var order_items = $j("input:radio[name=order_items]:checked").val();
    if (order_items == "unique") $j("#max_order_items_option").show();
  });
  $j("#customer").click(function () {
    $j(".export-types").hide();
    $j("#export-customer").show();

    $j(".export-options").hide();
    $j(".customer-options").show();
  });
  $j("#user").click(function () {
    $j(".export-types").hide();
    $j("#export-user").show();

    $j(".export-options").hide();
    $j(".user-options").show();
  });
  $j("#review").click(function () {
    $j(".export-types").hide();
    $j("#export-review").show();

    $j(".export-options").hide();
    $j(".review-options").show();
  });
  $j("#coupon").click(function () {
    $j(".export-types").hide();
    $j("#export-coupon").show();

    $j(".export-options").hide();
    $j(".coupon-options").show();
  });
  $j("#subscription").click(function () {
    $j(".export-types").hide();
    $j("#export-subscription").show();

    $j(".export-options").hide();
    $j(".subscription-options").show();
  });
  $j("#product_vendor").click(function () {
    $j(".export-types").hide();
    $j("#export-product_vendor").show();

    $j(".export-options").hide();
    $j(".product_vendor-options").show();
  });
  $j("#commission").click(function () {
    $j(".export-types").hide();
    $j("#export-commission").show();

    $j(".export-options").hide();
    $j(".commission-options").show();
  });
  $j("#shipping_class").click(function () {
    $j(".export-types").hide();
    $j("#export-shipping_class").show();

    $j(".export-options").hide();
    $j(".shipping_class-options").show();
  });
  $j("#ticket").click(function () {
    $j(".export-types").hide();
    $j("#export-ticket").show();

    $j(".export-options").hide();
    $j(".ticket-options").show();
  });
  $j("#booking").click(function () {
    $j(".export-types").hide();
    $j("#export-booking").show();

    $j(".export-options").hide();
    $j(".booking-options").show();
  });
  $j("#attribute").click(function () {
    $j(".export-types").hide();
    $j("#export-attribute").show();

    $j(".export-options").hide();
    $j(".attribute-options").show();
  });

  // Changing the Export Type will show/hide other options
  $j("#export_type").change(function () {
    var type = $j("select[name=export_type]").val();
    $j(".export_type_options .export-options").hide();
    if (type == null) var type = "product";
    $j(".export_type_options ." + type + "-options").show();
  });

  // Changing the Export Method will show/hide other options
  $j("#export_method").change(function () {
    var type = $j("select[name=export_method]").val();
    $j(".export_method_options .export-options").hide();
    $j(".export_method_options ." + type + "-options").show();
  });

  // Max unique Order Items
  $j("input:radio[name=order_items]").change(function () {
    var order_items = $j("input:radio[name=order_items]:checked").val();
    if (order_items == "unique") $j("#max_order_items_option").show();
    else $j("#max_order_items_option").hide();
  });

  // Max unique Product Gallery images
  $j("input:radio[name=product_gallery_unique]").change(function () {
    var product_gallery_unique = $j("input:radio[name=product_gallery_unique]:checked").val();
    if (product_gallery_unique == "1") $j("#max_product_gallery_option").show();
    else $j("#max_product_gallery_option").hide();
  });

  // Monitor CPT for changes
  if (
    $j("body").hasClass("post-type-scheduled_export") ||
    $j("body").hasClass("post-type-export_template") ||
    ($j("body").hasClass("woocommerce_page_woo_ce") && $j("#poststuff").hasClass("field-editor"))
  ) {
    var is_dirty = false;
  }

  // Confirmation prompt on button actions
  $j(
    ".woocommerce_page_woo_ce .advanced-settings a.delete, .woocommerce_page_woo_ce #archives-filter a.delete, .woocommerce_page_woo_ce a.confirm-button, .woocommerce_page_woo_ce .field-editor a.confirm-button, .post-type-scheduled_export a.confirm-button, .post-type-export_template a.confirm-button"
  ).click(function (e) {
    e.preventDefault();
    var validate = $j(this).attr("data-validate");
    var choice = true;
    if (!validate || (validate && is_dirty)) {
      choice = confirm($j(this).attr("data-confirm"));
    }
    if (choice) window.location.href = $j(this).attr("href");
  });

  // Google Sheets
  $j(".post-type-scheduled_export #google-sheets-change-device-id").click(function (e) {
    return false;
  });

  // Export Template
  $j("#woo-ce #export-template .loading").hide();
  $j('#woo-ce select[name="export_template"]').change(function () {
    $j("#woo-ce #export-template .loading").show();
    var export_template = $j('#woo-ce select[name="export_template"]').val();
    var data = {
      action: "woo_ce_export_load_export_template",
      export_template: export_template,
    };
    $j.post(ajaxurl, data, function (response) {
      var data = $j.parseJSON(response);
      if (typeof data !== "undefined") {
        for (var export_type in data) {
          console.log(export_type);
          console.log(data[export_type]);
          // Fields
          if (typeof data[export_type]["fields"] !== "undefined") {
            console.log(export_type + ": loading field selections...");
            $j("#" + export_type + "-fields")
              .find(":checkbox")
              .prop("checked", false);
            for (var field in data[export_type]["fields"]) {
              $j(
                "#" + export_type + '-fields tr[data-field-name="' + export_type + "-" + field + '"] input:checkbox'
              ).prop("checked", true);
            }
            console.log(export_type + ": loaded field selections");
          }
          // Sorting
          if (typeof data[export_type]["sorting"] !== "undefined") {
            console.log(export_type + ": loading field sorting...");
            for (var field in data[export_type]["sorting"]) {
              console.log(export_type + ": field - " + field);
              $j(
                "#" + export_type + '-fields tr[data-field-name="' + export_type + "-" + field + '"] .field_order'
              ).val(data[export_type]["sorting"][field]);
            }
            $j("table.ui-sortable").trigger("sortable");
            console.log(export_type + ": loaded field sorting...");
          }
        }
      }
    });
    $j("#woo-ce #export-template .loading").hide();
  });

  // Edit Export Template: Export fields
  $j('select[name="export_template"]').click(function () {
    $j('input:radio[name="export_fields"][value="template"]').prop("checked", true);
  });

  // Settings > CRON fields
  $j('select[name="cron_export_template"]').click(function () {
    $j('input:radio[name="cron_fields"][value="template"]').prop("checked", true);
  });

  // Settings > Order Actions: Export fields
  $j('select[name="order_actions_export_template"]').click(function () {
    $j('input:radio[name="order_actions_fields"][value="template"]').prop("checked", true);
  });

  $j("#trigger_new_order_method").change(function () {
    var type = $j("select[name=trigger_new_order_method]").val();
    $j(".export_method_options .export-options").hide();
    $j(".export_method_options ." + type + "-options").show();
  });

  $j(document).ready(function () {
    // Auto-selects the export type based on the link from the Overview screen
    var href = $j(location).prop("href");
    // If this is the Export tab
    if (href.toLowerCase().indexOf("tab=export") >= 0) {
      // If the URL includes an in-line link
      if (href.toLowerCase().indexOf("#") >= 0) {
        var type = href.substr(href.indexOf("#") + 1);
        var type = type.replace("export-", "");
        $j("#" + type).trigger("click");
        $j(window).scrollTop(0);
      } else {
        // Auto-selects the last known export type based on stored WordPress option, defaults to Products
        var type = $j("input:radio[name=dataset]:checked").val();
        if (typeof type === "undefined") var type = $j("input:radio[name=dataset]:not(:disabled):first").val();
        if (typeof type !== "undefined") $j("#" + type).trigger("click");
      }
    } else if (href.toLowerCase().indexOf("tab=settings") >= 0) {
      $j("#trigger_new_order_method").trigger("change");
    } else if (href.toLowerCase().indexOf("post.php") >= 0 || href.toLowerCase().indexOf("post-new.php") >= 0) {
      $j("#export_type").trigger("change");
      $j("#export_method").trigger("change");
    } else {
      // Auto-selects the last known export type based on stored WordPress option, defaults to Products
      var type = $j("input:radio[name=dataset]:checked").val();
      $j("#" + type).trigger("click");
    }

    // CPT Monitoring...
    if (typeof is_dirty !== "undefined") {
      $j(
        'form#post .options_group input[type="text"], form#post .options_group select, form#postform #field-editor input[type="text"]'
      ).change(function () {
        is_dirty = true;
      });
    }

    // Display a list of advanced options on the Settings screen
    $j("#woo-ce #advanced-settings").click(function () {
      $j("#woo-ce .advanced-settings").toggle();
      return false;
    });

    // Tiptip.
    $j(".help_tip").tipTip({
      attribute: "data-tip",
      fadeIn: 50,
      fadeOut: 50,
      delay: 200,
      keepAlive: true,
    });
  });
});
