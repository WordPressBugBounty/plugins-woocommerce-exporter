<?php
/**
 * Tools REST Controller.
 *
 * @since   2.7.10
 * @package VisserLabs\WSE\Classes\REST
 */

namespace VisserLabs\WSE\Classes\REST;

use VisserLabs\WSE\Abstracts\Abstract_Class;

defined( 'ABSPATH' ) || exit;

/**
 * Tools_Controller.
 *
 * Provides data for the Vue tools page: WooCommerce tool links and
 * the full export-modules list with live activation status.
 *
 * @since 2.7.10
 */
class Tools_Controller extends Abstract_Class {

    const REST_NAMESPACE = 'wse/v1';
    const REST_BASE      = 'tools';

    /**
     * Register hooks.
     *
     * @since 2.7.10
     * @return void
     */
    public function run() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST routes.
     *
     * @since 2.7.10
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_BASE,
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_tools_data' ),
                'permission_callback' => array( $this, 'check_permission' ),
            )
        );

        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_BASE . '/refresh',
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'refresh_module_status' ),
                'permission_callback' => array( $this, 'check_permission' ),
            )
        );

        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_BASE . '/install',
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'install_plugin' ),
                'permission_callback' => array( $this, 'check_install_permission' ),
                'args'                => array(
                    'slug' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => function ( $value ) {
                            return ! empty( $value ) && preg_match( '/^[a-z0-9\-]+$/', $value );
                        },
                    ),
                ),
            )
        );
    }

    /**
     * Permission callback.
     *
     * @since 2.7.10
     * @return bool|\WP_Error
     */
    public function check_permission() {
        if ( ! current_user_can( apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' ) ) ) {
            return new \WP_Error(
                'rest_forbidden',
                __( 'Sorry, you are not allowed to access this endpoint.', 'woocommerce-exporter' ),
                array( 'status' => rest_authorization_required_code() )
            );
        }
        return true;
    }

    /**
     * Return full tools page data.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 2.7.10
     * @return \WP_REST_Response
     */
    public function get_tools_data( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
        $modules = $this->with_reliable_status( array_values( woo_ce_modules_list() ) );

        return rest_ensure_response(
            array(
                'woo_tools' => $this->get_woo_tools(),
                'modules'   => $modules,
                'counts'    => $this->derive_counts( $modules ),
                'urls'      => $this->get_tab_urls(),
            )
        );
    }

    /**
     * Bust cached module counts and return fresh data.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 2.7.10
     * @return \WP_REST_Response
     */
    public function refresh_module_status( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
        delete_transient( WOO_CE_PREFIX . '_modules_all_count' );
        delete_transient( WOO_CE_PREFIX . '_modules_active_count' );
        delete_transient( WOO_CE_PREFIX . '_modules_inactive_count' );

        $modules = $this->with_reliable_status( array_values( woo_ce_modules_list() ) );

        return rest_ensure_response(
            array(
                'modules' => $modules,
                'counts'  => $this->derive_counts( $modules ),
            )
        );
    }

    /**
     * Permission callback for the install endpoint.
     *
     * Requires both the standard WSE capability and WordPress's install_plugins cap,
     * which may be absent on managed hosts that restrict filesystem access.
     *
     * @since 2.7.10
     * @return bool|\WP_Error
     */
    public function check_install_permission() {
        if ( ! current_user_can( apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' ) ) ) {
            return new \WP_Error(
                'rest_forbidden',
                __( 'Sorry, you are not allowed to access this endpoint.', 'woocommerce-exporter' ),
                array( 'status' => rest_authorization_required_code() )
            );
        }

        if ( ! current_user_can( 'install_plugins' ) ) {
            return new \WP_Error(
                'rest_cannot_install',
                __( 'You do not have permission to install plugins.', 'woocommerce-exporter' ),
                array( 'status' => rest_authorization_required_code() )
            );
        }

        return true;
    }

    /**
     * Install and activate a plugin from WordPress.org.
     *
     * Downloads the plugin matching the given slug, installs it via
     * Plugin_Upgrader, then activates it. If the plugin is already installed
     * the download step is skipped and activation is attempted directly.
     *
     * @param \WP_REST_Request $request REST request — expects { slug: string }.
     *
     * @since 2.7.10
     * @return \WP_REST_Response|\WP_Error
     */
    public function install_plugin( \WP_REST_Request $request ) {
        $slug = $request->get_param( 'slug' );

        // Load the WP upgrader stack (not available on the REST bootstrap path).
        // file.php must come first — it defines request_filesystem_credentials(),
        // which WP_Upgrader_Skin calls unconditionally during initialisation.
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        // Check whether the plugin is already present on disk.
        $plugin_file = $this->find_plugin_file( $slug );

        if ( null === $plugin_file ) {
            // Fetch package URL from the wordpress.org API.
            $api = plugins_api(
                'plugin_information',
                array(
                    'slug'   => $slug,
                    'fields' => array(
                        'sections' => false,
                        'tags'     => false,
                    ),
                )
            );

            if ( is_wp_error( $api ) ) {
                return new \WP_Error(
                    'wse_plugin_api_failed',
                    /* translators: %s: slug */
                    sprintf( __( 'Could not retrieve plugin data for "%s" from WordPress.org.', 'woocommerce-exporter' ), $slug ),
                    array( 'status' => 502 )
                );
            }

            $skin     = new \WP_Ajax_Upgrader_Skin();
            $upgrader = new \Plugin_Upgrader( $skin );
            $result   = $upgrader->install( $api->download_link );

            if ( is_wp_error( $result ) ) {
                return new \WP_Error(
                    'wse_install_failed',
                    $result->get_error_message(),
                    array( 'status' => 500 )
                );
            }

            if ( false === $result ) {
                return new \WP_Error(
                    'wse_install_failed',
                    __( 'Plugin installation failed. Check that WordPress has write access to the plugins directory.', 'woocommerce-exporter' ),
                    array( 'status' => 500 )
                );
            }

            // Re-resolve the plugin file after install.
            $plugin_file = $this->find_plugin_file( $slug );

            if ( null === $plugin_file ) {
                return new \WP_Error(
                    'wse_install_failed',
                    __( 'Plugin installed but could not be located. Please activate it manually from the Plugins page.', 'woocommerce-exporter' ),
                    array( 'status' => 500 )
                );
            }
        }

        // Activate the plugin (no-op if already active).
        if ( ! is_plugin_active( $plugin_file ) ) {
            try {
                $activated = activate_plugin( $plugin_file );
            } catch ( \Throwable $e ) {
                // The plugin was installed and added to active_plugins, but its
                // own code threw during activation (e.g. a PHP 8.x compatibility
                // error). Return a descriptive message rather than a fatal crash.
                return new \WP_Error(
                    'wse_activation_failed',
                    sprintf(
                        /* translators: 1: plugin slug, 2: error message */
                        __( '"%1$s" was installed but could not be activated: %2$s', 'woocommerce-exporter' ),
                        $slug,
                        $e->getMessage()
                    ),
                    array( 'status' => 500 )
                );
            }

            if ( is_wp_error( $activated ) ) {
                return new \WP_Error(
                    'wse_activation_failed',
                    $activated->get_error_message(),
                    array( 'status' => 500 )
                );
            }
        }

        // Bust module-count transients so woo_ce_modules_list() re-counts.
        delete_transient( WOO_CE_PREFIX . '_modules_all_count' );
        delete_transient( WOO_CE_PREFIX . '_modules_active_count' );
        delete_transient( WOO_CE_PREFIX . '_modules_inactive_count' );

        // Return fresh module data so the Vue store can update in one round trip
        // without needing a separate /refresh call.
        $modules = $this->with_reliable_status( array_values( woo_ce_modules_list() ) );

        return rest_ensure_response(
            array(
                'success'     => true,
                'plugin_file' => $plugin_file,
                'message'     => __( 'Plugin installed and activated successfully.', 'woocommerce-exporter' ),
                'modules'     => $modules,
                'counts'      => $this->derive_counts( $modules ),
            )
        );
    }

    /**
     * Find the plugin main file for a given slug among installed plugins.
     *
     * Returns null when the plugin is not on disk.
     *
     * @since 2.7.10
     * @param string $slug Plugin directory slug (e.g. "woocommerce-store-toolkit").
     * @return string|null Plugin file relative to the plugins directory, or null.
     */
    private function find_plugin_file( string $slug ): ?string {
        // get_plugins() returns all installed plugins keyed by "dir/file.php".
        $all_plugins = get_plugins();

        foreach ( array_keys( $all_plugins ) as $plugin_file ) {
            if ( str_starts_with( $plugin_file, $slug . '/' ) ) {
                return $plugin_file;
            }
        }

        return null;
    }

    /**
     * Supplement module status using is_plugin_active() for slug-based modules.
     *
     * The woo_ce_modules_list() function determines status via function_exists/class_exists.
     * Those checks silently fail when a plugin has been refactored and no longer
     * defines the legacy function/class name stored in the module registry.
     *
     * For every module that has a 'slug' and is still showing 'inactive', this
     * method performs a definitive check: if the matching plugin file is listed
     * in WordPress's active_plugins option, the status is overridden to 'active'.
     *
     * @since 2.7.10
     * @param array $modules Modules array with 'status' already set.
     * @return array
     */
    private function with_reliable_status( array $modules ): array {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Build a set of active plugin file paths for O(1) lookup.
        $active         = array_flip( (array) get_option( 'active_plugins', array() ) );
        $installed_keys = array_keys( get_plugins() );

        foreach ( $modules as $key => $module ) {
            if ( ! isset( $module['slug'] ) ) {
                continue;
            }

            // Expose a canonical WordPress.org URL so the Vue title link can
            // always point to the plugin's public page, regardless of how
            // woo_ce_modules_list() has overwritten the 'url' field.
            $modules[ $key ]['wp_org_url'] = 'https://wordpress.org/plugins/' . rawurlencode( $module['slug'] ) . '/';

            // Skip the active-status re-check for modules already marked active.
            if ( 'active' === ( $module['status'] ?? '' ) ) {
                continue;
            }

            foreach ( $installed_keys as $plugin_file ) {
                if ( str_starts_with( $plugin_file, $module['slug'] . '/' ) ) {
                    if ( isset( $active[ $plugin_file ] ) ) {
                        $modules[ $key ]['status'] = 'active';
                    }
                    break;
                }
            }
        }

        return $modules;
    }

    /**
     * Derive all/active/inactive counts from a module list that already has 'status' set.
     *
     * @since 2.7.10
     * @param array $modules Modules array (with 'status' key on each entry).
     * @return array{all: int, active: int, inactive: int}
     */
    private function derive_counts( array $modules ): array {
        $active = count(
            array_filter( $modules, fn( $m ) => 'active' === ( $m['status'] ?? '' ) )
        );
        $total  = count( $modules );

        return array(
            'all'      => $total,
            'active'   => $active,
            'inactive' => $total - $active,
        );
    }

    /**
     * Build the WooCommerce companion-tool link list.
     *
     * Returns internal admin URLs when a companion plugin is installed,
     * or external product-page URLs otherwise.
     *
     * @since 2.7.10
     * @return array[]
     */
    private function get_woo_tools(): array {
        $tools = array();

        // Product Importer Deluxe.
        $pd_url      = 'http://www.visser.com.au/woocommerce/plugins/product-importer-deluxe/';
        $pd_internal = false;
        if ( function_exists( 'woo_pd_init' ) ) {
            $pd_url      = esc_url_raw( add_query_arg( array( 'page' => 'woo_pd', 'tab' => null ), admin_url( 'admin.php' ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
            $pd_internal = true;
        }
        $tools[] = array(
            'title'       => __( 'Import Products from CSV', 'woocommerce-exporter' ),
            'description' => __( 'Use Product Importer Deluxe to import Product changes back into your WooCommerce store.', 'woocommerce-exporter' ),
            'url'         => $pd_url,
            'is_internal' => $pd_internal,
        );

        // Store Toolkit.
        $st_url      = 'http://www.visser.com.au/woocommerce/plugins/store-toolkit/';
        $st_internal = false;
        if ( function_exists( 'woo_st_admin_init' ) ) {
            $st_url      = esc_url_raw( add_query_arg( array( 'page' => 'woo_st', 'tab' => null ), admin_url( 'admin.php' ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
            $st_internal = true;
        }
        $tools[] = array(
            'title'       => __( 'Store Toolkit', 'woocommerce-exporter' ),
            'description' => __( 'Store Toolkit includes a growing set of commonly-used WooCommerce administration tools aimed at web developers and store maintainers.', 'woocommerce-exporter' ),
            'url'         => $st_url,
            'is_internal' => $st_internal,
        );

        return $tools;
    }

    /**
     * Build the standard admin tab URL map used by the Vue app for navigation.
     *
     * @since 2.7.10
     * @return array
     */
    private function get_tab_urls(): array {
        $base = esc_url_raw( add_query_arg( 'page', 'woo_ce', admin_url( 'admin.php' ) ) );

        return array(
            'overview'         => esc_url_raw( $base . '&tab=overview' ),
            'tools'            => esc_url_raw( $base . '&tab=tools' ),
            'scheduled_export' => esc_url_raw( $base . '&tab=scheduled_export' ),
            'archive'          => esc_url_raw( $base . '&tab=archive' ),
            'settings'         => esc_url_raw( $base . '&tab=settings' ),
            'plugins'          => esc_url_raw( admin_url( 'plugins.php' ) ),
        );
    }
}
