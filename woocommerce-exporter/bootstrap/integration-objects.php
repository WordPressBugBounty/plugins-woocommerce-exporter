<?php
/**
 * Integration instance objects.
 *
 * @package VisserLabs\WSE
 */

use VisserLabs\WSE\Integrations\Woocommerce_Subscription;
use VisserLabs\WSE\Integrations\Woocommerce_Bookings;
use VisserLabs\WSE\Integrations\Woocommerce_Brands;
use VisserLabs\WSE\Integrations\Woocommerce_Product_Vendors;
use VisserLabs\WSE\Integrations\Foo_Events;

defined( 'ABSPATH' ) || exit;

return array(
    new Woocommerce_Subscription(),
    new Woocommerce_Bookings(),
    new Woocommerce_Brands(),
    new Woocommerce_Product_Vendors(),
    new Foo_Events(),
);
