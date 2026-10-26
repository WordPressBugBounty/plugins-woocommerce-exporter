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
use VisserLabs\WSE\Classes\SaveTo_Wishlist_Notice;
use VisserLabs\WSE\Classes\ACFW_Marketing_Page;
use VisserLabs\WSE\Classes\REST\Overview_Controller;
use VisserLabs\WSE\Classes\REST\Export_Items_Controller;
use VisserLabs\WSE\Classes\REST\Export_Fields_Controller;
use VisserLabs\WSE\Classes\REST\Quick_Export_Controller;
use VisserLabs\WSE\Classes\REST\Settings_Controller;
use VisserLabs\WSE\Classes\REST\Tools_Controller;
use VisserLabs\WSE\Classes\REST\Custom_Meta_Fields_Controller;
use VisserLabs\WSE\Classes\REST\AI_MCP_Controller;
use VisserLabs\WSE\Classes\Abilities;

defined( 'ABSPATH' ) || exit;

return array(
    new WP_Admin(),
    new Exporter(),
    new Marketing(),
    new Upsell(),
    new SaveTo_Wishlist_Notice(),
    new ACFW_Marketing_Page(),
    // REST controllers backing the Vue admin SPA (wse/v1 namespace).
    new Overview_Controller(),
    new Export_Items_Controller(),
    new Export_Fields_Controller(),
    new Quick_Export_Controller(),
    new Settings_Controller(),
    new Tools_Controller(),
    new Custom_Meta_Fields_Controller(),
    new AI_MCP_Controller(),
    new Abilities(),
);
