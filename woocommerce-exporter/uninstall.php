<?php
/**
 * Uninstall handler. Runs when the plugin is deleted from the Plugins screen.
 *
 * @package VisserLabs\WSE
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Same value as Exporter::QUICK_EXPORT_COUNT_OPTION. The plugin autoloader
// does not run during uninstall, so the class is not available here.
$woo_ce_quick_export_count_option = 'wse_quick_export_count';

if ( is_multisite() ) {
    $woo_ce_site_ids = get_sites(
        array(
            'fields' => 'ids',
            'number' => 0,
        )
    );
    foreach ( $woo_ce_site_ids as $woo_ce_site_id ) {
        switch_to_blog( $woo_ce_site_id );
        delete_option( $woo_ce_quick_export_count_option );
        restore_current_blog();
    }
} else {
    delete_option( $woo_ce_quick_export_count_option );
}
