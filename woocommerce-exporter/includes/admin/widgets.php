<?php

use VisserLabs\WSE\Helpers\Export as Export_Helper;

/**
 * Displays the scheduled export widget in the admin area.
 *
 * This function retrieves the scheduled export options and displays the scheduled exports
 * that are set to be published. It also checks the user capability to view WooCommerce reports.
 * If the widget template file is not found, an error message is displayed.
 */
function woo_ce_admin_scheduled_export_widget() {

    $enable_auto = woo_ce_get_option( 'enable_auto', 0 );
    if ( $enable_auto ) {

        $next_export = '';
        $next_time   = '';

        // Get widget options.
        $widget_options = woo_ce_get_option( 'scheduled_export_widget_options', array() );
        if ( ! $widget_options ) {
            $widget_options = array(
                'number' => 5,
            );
        }

        // Loop through each scheduled export, only show Published.
        $args = array(
            'post_status' => 'publish',
        );
        if ( $scheduled_exports = woo_ce_get_scheduled_exports( $args ) ) {
            $export_times = array();
            foreach ( $scheduled_exports as $key => $scheduled_export ) {

                // Only display enabled scheduled exports.
                if ( get_post_status( $scheduled_export ) <> 'publish' ) {
                    unset( $scheduled_exports[ $key ] );
                    continue;
                }

                // Figure out which scheduled export will run next.
                $next_export                       = $scheduled_export;
                $next_time                         = Export_Helper::get_scheduled_date( $scheduled_export, 'string' );
                $export_times[ $scheduled_export ] = $next_time;
            }

            // Sort the scheduled exports by the order of next run.
            if ( ! empty( $export_times ) ) {
                arsort( $export_times );
                $scheduled_exports = array_keys( $export_times );
            }

            // Check if we need to limit the number of scheduled exports.
            $size = count( $scheduled_exports );
            if ( $size > $widget_options['number'] ) {
                $i = $size;
                // Loop through the recent exports till we get it down to our limit.
                for ( $i; $i > $widget_options['number']; $i-- ) {
                    array_pop( $scheduled_exports );
                }
            }
            unset( $next_time );
        }
    }

    // Check the User has the view_woocommerce_reports capability.
    $user_capability = apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' );

    $template = 'dashboard_widget-scheduled_export.php';
    if ( file_exists( WOO_CE_PATH . 'templates/admin/' . $template ) ) {
        include_once WOO_CE_PATH . 'templates/admin/' . $template;
    } else {
        $message = sprintf( __( 'We couldn\'t load the widget template file <code>%1$s</code> within <code>%2$s</code>, this file should be present.', 'woocommerce-exporter' ), 'dashboard_widget-scheduled_export.php', WOO_CE_PATH . 'templates/admin/...' );

        ob_start(); ?>
        <p><strong><?php echo wp_kses_post( $message ); ?></strong></p>
        <p><?php esc_html_e( 'You can see this error for one of a few common reasons', 'woocommerce-exporter' ); ?>:</p>
        <ul class="ul-disc">
            <li><?php esc_html_e( 'WordPress was unable to create this file when the Plugin was installed or updated', 'woocommerce-exporter' ); ?></li>
            <li><?php esc_html_e( 'The Plugin files have been recently changed and there has been a file conflict', 'woocommerce-exporter' ); ?></li>
            <li><?php esc_html_e( 'The Plugin file has been locked and cannot be opened by WordPress', 'woocommerce-exporter' ); ?></li>
        </ul>
        <p><?php esc_html_e( 'Jump onto our website and download a fresh copy of this Plugin as it might be enough to fix this issue. If this persists get in touch with us.', 'woocommerce-exporter' ); ?></p>
    <?php
        ob_end_flush();
    }
}

/**
 * Renders the recent scheduled export widget in the admin area.
 *
 * This function retrieves the necessary options and recent exports from the database,
 * and then renders the widget template file. If the template file is not found, it
 * displays an error message with possible reasons for the issue.
 */
function woo_ce_admin_recent_scheduled_export_widget() {

    $enable_auto    = woo_ce_get_option( 'enable_auto', 0 );
    $recent_exports = woo_ce_get_option( 'recent_scheduled_exports', array() );
    if ( empty( $recent_exports ) ) {
        $recent_exports = array();
    }
    $size           = count( $recent_exports );
    $recent_exports = array_reverse( $recent_exports );

    // Get widget options.
    $widget_options = woo_ce_get_option( 'recent_scheduled_export_widget_options', array() );
    if ( ! $widget_options ) {
        $widget_options = array(
            'number' => 5,
        );
    }

    // Check if we need to limit the number of recent exports.
    if ( $size > $widget_options['number'] ) {
        $i = $size;
        // Loop through the recent exports till we get it down to our limit.
        for ( $i; $i >= $widget_options['number']; $i-- ) {
            unset( $recent_exports[ $i ] );
        }
    }

    // Check the User has the view_woocommerce_reports capability.
    $user_capability = apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' );

    $template = 'dashboard_widget-recent_scheduled_export.php';
    if ( file_exists( WOO_CE_PATH . 'templates/admin/' . $template ) ) {
        include_once WOO_CE_PATH . 'templates/admin/' . $template;
    } else {
        $message = sprintf( __( 'We couldn\'t load the widget template file <code>%1$s</code> within <code>%2$s</code>, this file should be present.', 'woocommerce-exporter' ), 'dashboard_widget-recent_scheduled_export.php', WOO_CE_PATH . 'templates/admin/...' );

        ob_start();
    ?>
        <p><strong><?php echo wp_kses_post( $message ); ?></strong></p>
        <p><?php esc_html_e( 'You can see this error for one of a few common reasons', 'woocommerce-exporter' ); ?>:</p>
        <ul class="ul-disc">
            <li><?php esc_html_e( 'WordPress was unable to create this file when the Plugin was installed or updated', 'woocommerce-exporter' ); ?></li>
            <li><?php esc_html_e( 'The Plugin files have been recently changed and there has been a file conflict', 'woocommerce-exporter' ); ?></li>
            <li><?php esc_html_e( 'The Plugin file has been locked and cannot be opened by WordPress', 'woocommerce-exporter' ); ?></li>
        </ul>
        <p><?php esc_html_e( 'Jump onto our website and download a fresh copy of this Plugin as it might be enough to fix this issue. If this persists get in touch with us.', 'woocommerce-exporter' ); ?></p>
    <?php
        ob_end_flush();
    }
}

/**
 * Displays the configuration options for the scheduled export widget.
 *
 * This function retrieves the widget options and updates them if a POST request is made.
 * It displays a form with a field to control the number of scheduled exports to be shown.
 *
 * @return void
 */
function woo_ce_admin_scheduled_export_widget_configure() {

    // Get widget options.
    if ( ! $widget_options = woo_ce_get_option( 'scheduled_export_widget_options', array() ) ) {
        $widget_options = array(
            'number' => 5,
        );
    }

    // Update widget options.
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['woo_ce_scheduled_export_widget_post'] ) ) {
        $widget_options = array_map( 'sanitize_text_field', (array) $_POST['woo_ce_scheduled_export'] );
        if ( empty( $widget_options['number'] ) ) {
            $widget_options['number'] = 5;
        }
        update_option( 'woo_ce_scheduled_export_widget_options', $widget_options );
    }
    ?>
    <div>
        <label for="woo_ce_scheduled_export-number"><?php esc_html_e( 'Number of scheduled exports', 'woocommerce-exporter' ); ?>:</label><br />
        <input type="text" id="woo_ce_scheduled_export-number" name="woo_ce_scheduled_export[number]" value="<?php echo esc_attr( $widget_options['number'] ); ?>" />
        <p class="description"><?php esc_html_e( 'Control the number of scheduled exports that are shown.', 'woocommerce-exporter' ); ?></p>
    </div>
    <input name="woo_ce_scheduled_export_widget_post" type="hidden" value="1" />
<?php
}

/**
 * Configure the recent scheduled export widget.
 *
 * This function retrieves and updates the widget options for the recent scheduled export widget.
 * It displays a form with a field to control the number of scheduled exports to show.
 */
function woo_ce_admin_recent_scheduled_export_widget_configure() {

    // Get widget options.
    if ( ! $widget_options = woo_ce_get_option( 'recent_scheduled_export_widget_options', array() ) ) {
        $widget_options = array(
            'number' => 5,
        );
    }

    // Update widget options.
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['woo_ce_recent_scheduled_export_widget_post'] ) ) {
        $widget_options = array_map( 'sanitize_text_field', (array) $_POST['woo_ce_recent_scheduled_export'] );
        if ( empty( $widget_options['number'] ) ) {
            $widget_options['number'] = 5;
        }
        update_option( 'woo_ce_recent_scheduled_export_widget_options', $widget_options );
    }
?>
    <div>
        <label for="woo_ce_recent_scheduled_export-number"><?php esc_html_e( 'Number of scheduled exports', 'woocommerce-exporter' ); ?>:</label><br />
        <input type="text" id="woo_ce_recent_scheduled_export-number" name="woo_ce_recent_scheduled_export[number]" value="<?php echo esc_attr( $widget_options['number'] ); ?>" />
        <p class="description"><?php esc_html_e( 'Control the number of recent scheduled exports that are shown.', 'woocommerce-exporter' ); ?></p>
    </div>
    <input name="woo_ce_recent_scheduled_export_widget_post" type="hidden" value="1" />
<?php
}
?>
