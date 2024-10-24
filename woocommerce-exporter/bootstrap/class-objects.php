<?php
/**
 * Class objects instance list.
 *
 * @since   2.7.3
 * @package VisserLabs\WSE
 */

use VisserLabs\WSE\Classes\WP_Admin;
use VisserLabs\WSE\Classes\Exporter;
use VisserLabs\WSE\Classes\Marketing;
use VisserLabs\WSE\Classes\Upsell;

defined( 'ABSPATH' ) || exit;

return array(
    new WP_Admin(),
    new Exporter(),
    new Marketing(),
    new Upsell(),
);
