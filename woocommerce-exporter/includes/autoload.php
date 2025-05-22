<?php
/**
 * Plugin autoload file.
 *
 * @package VisserLabs\WSE
 */

namespace VisserLabs\WSE;

defined( 'ABSPATH' ) || exit;

/**
 * Builds the path to the class file within the plugin directory.
 *
 * @param string $class The class name.
 *
 * @return string The full class file path.
 */
function get_class_file_path( $class ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.classFound

    $class = ltrim( $class, '\\' );
    $class = str_replace( '\\', DIRECTORY_SEPARATOR, $class );
    $class = mb_substr( $class, 15 );

    return WOO_CE_PATH . "includes/$class.php";
}

/**
 * Namespaced autoload function for the plugin.
 *
 * @param string $class The class name.
 */
function autoload( $class ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.classFound

    $file = '';
    if ( 'VisserLabs\WSE' === mb_substr( $class, 0, 14 ) ) {

        $file = get_class_file_path( $class );

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
}

try {
    spl_autoload_register( '\VisserLabs\WSE\autoload' );
} catch ( \Exception $exception ) {
    if ( is_admin() ) {
        add_action(
            'admin_notices',
            function () use ( $exception ) {

                ?>
				<div class="error settings-error notice">
					<p><strong>ERROR:</strong> <?php echo esc_html( $exception->getMessage() ); ?></p>
				</div>
                <?php
            }
        );
    } elseif ( current_user_can( 'manage_options' ) ) {
        add_action(
            'wp_footer',
            function () use ( $exception ) {

                ?>
				<div class="error">
					<p class="text-danger">
						<strong>ERROR:</strong> <?php echo esc_html( $exception->getMessage() ); ?></p>
				</div>
                <?php
            }
        );
    }
}
