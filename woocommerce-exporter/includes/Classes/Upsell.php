<?php

/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Classes
 */

namespace VisserLabs\WSE\Classes;

use VisserLabs\WSE\Traits\Singleton_Trait;
use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Helpers\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Quick Export class.
 *
 * @since 2.7.3
 */
class Upsell extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Enqueue upsell styles.
     *
     * @since 2.7.3
     * @access public
     */
    public function enqueue_upsell_styles() {
        $page = filter_input( INPUT_GET, 'page', FILTER_UNSAFE_RAW ) ?? '';
        $page = htmlspecialchars( $page, ENT_QUOTES, 'UTF-8' );

        if ( 'woo_ce' !== $page ) {
            return;
        }

        wp_enqueue_style( 'wse-upsell-css', WOO_CE_PLUGINPATH . '/css/upsell.css', array(), Helper::get_plugin_version() );
    }

    /**
     * Show upsell overlay.
     *
     * @since 2.7.3
     * @access public
     *
     * @param string $id    The ID used in the utm_campaign URL parameter.
     * @param string $title Upsell title.
     * @param string $desc  Upsell description.
     */
    public function show_upsell_overlay( $id = '', $title = '', $desc = '' ) {
        include WOO_CE_VIEWS_PATH . '/notices/view-upsell-overlay-notice.php';
    }

    /**
     * Execute model.
     *
     * @since 2.7.3
     * @access public
     */
    public function run() {
        // Enqueue upsell CSS.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_upsell_styles' ) );

        add_action( 'wse_show_upsell_overlay', array( $this, 'show_upsell_overlay' ), 10, 3 );
    }
}
