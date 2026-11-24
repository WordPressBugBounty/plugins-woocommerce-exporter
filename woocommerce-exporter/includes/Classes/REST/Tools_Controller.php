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
     * Directory slug Product Importer Deluxe is installed under.
     *
     * @since 2.8.3
     */
    const PRODUCT_IMPORTER_DELUXE_SLUG = 'woocommerce-product-importer-deluxe';

    /**
     * Directory slug Store Toolkit is installed under.
     *
     * @since 2.8.3
     */
    const STORE_TOOLKIT_SLUG = 'woocommerce-store-toolkit';

    /**
     * Page slug Product Importer Deluxe registers its admin screen under.
     *
     * Verified against the plugin itself: includes/admin.php calls
     * add_submenu_page( 'woocommerce', ..., 'manage_woocommerce', 'woo_pd', 'woo_pd_html_page' ),
     * unchanged from 3.1 through 3.8.3 - so unlike Store Toolkit's, this slug has never moved.
     *
     * @since 2.8.3
     */
    const PRODUCT_IMPORTER_DELUXE_PAGE = 'woo_pd';

    /**
     * Page slug Store Toolkit registers its admin screen under, from 2.4.0 onwards.
     *
     * @since 2.8.3
     */
    const STORE_TOOLKIT_PAGE = 'store-toolkit';

    /**
     * Page slug Store Toolkit registered its admin screen under before 2.4.0.
     *
     * 2.3.10 and older called add_submenu_page( 'woocommerce', ..., 'woo_st', ... ). 2.4.0
     * promoted the screen to a top-level add_menu_page( ..., 'store-toolkit', ... ), so the slug
     * a store needs depends on the version it runs - linking either one unconditionally hands
     * half of them a screen WordPress refuses.
     *
     * @since 2.8.3
     */
    const STORE_TOOLKIT_LEGACY_PAGE = 'woo_st';

    /**
     * Store Toolkit version that renamed the admin page slug.
     *
     * @since 2.8.3
     */
    const STORE_TOOLKIT_PAGE_RENAMED_IN = '2.4.0';

    /**
     * Capability a companion plugin's own admin screen requires.
     *
     * @since 2.8.3
     */
    const COMPANION_CAPABILITY = 'manage_woocommerce';

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
        return rest_ensure_response(
            array_merge( $this->module_snapshot(), array( 'urls' => $this->get_tab_urls() ) )
        );
    }

    /**
     * Bust cached module counts and return fresh data.
     *
     * @param \WP_REST_Request $request The REST request.
     *
     * @since 2.7.10
     * @since 2.8.3 Also returns the companion-tool cards, so one switched on without leaving the
     *              screen updates its card without a page reload.
     * @return \WP_REST_Response
     */
    public function refresh_module_status( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
        // Clears the cached module list as well as the counts. Without the
        // list, Refresh Status left Quick Export reading a day-old detection
        // while this screen showed the real one.
        woo_ce_clear_modules_cache();

        return rest_ensure_response( $this->module_snapshot() );
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
     * @since 2.8.3 Records Store Exporter as the StoreAgent install source, and also returns the
     *              companion-tool cards, for the same reason as the refresh route.
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

            // StoreAgent reads its install source from this option. add_option()
            // keeps the value that another plugin already wrote.
            if ( AI_MCP_Controller::STOREAGENT_SLUG === $slug ) {
                add_option( 'storeagentai_installed_by', 'wse' );
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

        // Bust the module transients so woo_ce_modules_list() re-counts. Also
        // covers the case above where the plugin was already active, so
        // activate_plugin(), and the invalidation it triggers, was skipped.
        woo_ce_clear_modules_cache();

        // Return fresh module data so the Vue store can update in one round trip
        // without needing a separate /refresh call.
        return rest_ensure_response(
            array_merge(
                array(
                    'success'     => true,
                    'plugin_file' => $plugin_file,
                    'message'     => __( 'Plugin installed and activated successfully.', 'woocommerce-exporter' ),
                ),
                $this->module_snapshot()
            )
        );
    }

    /**
     * The Tools screen's live state: the companion-tool cards, the module list and its counts.
     *
     * Shared by all three routes that answer this screen. The cards travel with the modules
     * because a companion plugin can be switched on from here - through Refresh Status or the
     * Export Modules Install button - and its card has to follow without a page reload; they are
     * otherwise only built when the app mounts.
     *
     * @since 2.8.3
     *
     * @return array{woo_tools: array[], modules: array, counts: array}
     */
    private function module_snapshot(): array {
        $modules = $this->with_reliable_status( array_values( woo_ce_modules_list() ) );

        return array(
            'woo_tools' => $this->get_woo_tools(),
            'modules'   => $modules,
            'counts'    => $this->derive_counts( $modules ),
        );
    }

    /**
     * Whether a companion plugin is active and its own admin screen is open to the current user.
     *
     * The bare `function_exists()` test this replaces only holds inside wp-admin. Store Toolkit
     * declares `woo_st_admin_init()` inside an `is_admin()` guard, and this screen is fed by a REST
     * request, where `is_admin()` is false - so the symbol was never there to find and the card
     * always fell back to the external product page, however the store had the plugin set up.
     *
     * The capability test comes first and gates everything. A companion plugin registers its
     * screen for `manage_woocommerce`, while this route authorises through the filterable
     * `woo_ce_admin_user_capability` - so on a store that has filtered that capability down, an
     * internal link would hand the user a screen WordPress refuses. Those users keep the product
     * page, which at least loads.
     *
     * The function test is kept as a fast path, but it is worth being honest about what it covers
     * here. It answers for a companion plugin that declares its symbol unconditionally - Product
     * Importer Deluxe declares `woo_pd_init()` outside its own `is_admin()` block - and for any
     * caller reached from inside wp-admin. It does NOT rescue a Store Toolkit installed under a
     * non-canonical directory on this path: that symbol is admin-only, so in a REST request the
     * slug lookup is the only thing that can find it, and a renamed directory keeps the external
     * card. `is_plugin_active()` is used rather than reading the `active_plugins` option directly
     * because it also reports plugins activated across a multisite network.
     *
     * @since 2.8.3
     *
     * @param string $slug            Plugin directory slug (e.g. "woocommerce-store-toolkit").
     * @param string $legacy_function Function the plugin declares once it has loaded.
     * @return bool
     */
    private function can_open_companion_plugin( string $slug, string $legacy_function ): bool {
        if ( ! current_user_can( self::COMPANION_CAPABILITY ) ) {
            return false;
        }

        if ( function_exists( $legacy_function ) ) {
            return true;
        }

        $plugin_file = $this->find_plugin_file( $slug );

        return null !== $plugin_file && is_plugin_active( $plugin_file );
    }

    /**
     * Page slug Store Toolkit registers its admin screen under on this store.
     *
     * Store Toolkit moved its screen in 2.4.0, from a WooCommerce submenu under `woo_st` to a
     * top-level menu under `store-toolkit`, so the right slug depends on the installed version.
     *
     * The version is read from the plugin header rather than from `WOO_ST_VERSION`, because that
     * constant did not exist before 2.4.0 either - testing `defined()` alone would quietly give
     * every 2.3.x store the new slug, which is the screen WordPress refuses. The constant is only
     * a fallback, for a copy installed in a directory the slug lookup cannot match.
     *
     * "Cannot be determined" means "does not compare as 2.4.0 or newer": an empty or malformed
     * version (including `2.4` and `2.4.0-beta`) is treated as pre-2.4.0, since every release that
     * has the new slug also defines the constant.
     *
     * @since 2.8.3
     *
     * @return string The page slug to link to.
     */
    private function store_toolkit_page(): string {
        $version = $this->installed_plugin_version( self::STORE_TOOLKIT_SLUG );

        if ( '' === $version && defined( 'WOO_ST_VERSION' ) ) {
            $version = (string) WOO_ST_VERSION;
        }

        if ( '' === $version || version_compare( $version, self::STORE_TOOLKIT_PAGE_RENAMED_IN, '<' ) ) {
            return self::STORE_TOOLKIT_LEGACY_PAGE;
        }

        return self::STORE_TOOLKIT_PAGE;
    }

    /**
     * Version header of an installed plugin.
     *
     * @since 2.8.3
     *
     * @param string $slug Plugin directory slug (e.g. "woocommerce-store-toolkit").
     * @return string The plugin's version, or an empty string when it cannot be read.
     */
    private function installed_plugin_version( string $slug ): string {
        $installed   = $this->installed_plugins();
        $plugin_file = $this->match_plugin_file( array_keys( $installed ), $slug );

        if ( null === $plugin_file ) {
            return '';
        }

        return (string) ( $installed[ $plugin_file ]['Version'] ?? '' );
    }

    /**
     * Find the plugin main file for a given slug among installed plugins.
     *
     * Returns null when the plugin is not on disk.
     *
     * @since 2.7.10
     * @since 2.8.3 Loads wp-admin/includes/plugin.php itself, so every caller on the REST
     *              bootstrap path no longer has to repeat that guard.
     * @param string $slug Plugin directory slug (e.g. "woocommerce-store-toolkit").
     * @return string|null Plugin file relative to the plugins directory, or null.
     */
    private function find_plugin_file( string $slug ): ?string {
        return $this->match_plugin_file( array_keys( $this->installed_plugins() ), $slug );
    }

    /**
     * Every installed plugin, keyed by "dir/file.php", as get_plugins() returns them.
     *
     * WordPress does not load wp-admin/includes/plugin.php on the REST bootstrap path, so the
     * include that declares get_plugins() is kept here rather than repeated at each call site.
     * is_plugin_active() is declared in that same file, which is why every caller that asks it
     * reads this list first.
     *
     * In practice the require below is already a no-op: exporter.php loads the same file on every
     * request, before anything in this class runs. It is kept as the local guarantee for this
     * class rather than a dependency on that bootstrap order staying as it is.
     *
     * @since 2.8.3
     *
     * @return array Installed plugins, keyed by plugin file.
     */
    private function installed_plugins(): array {
        // Guarded rather than bare so this is not a second "Cannot redeclare" site on a store where
        // another plugin has declared get_plugins() by hand. That store is already lost by the time
        // this runs - exporter.php requires the same file at load - so the guard buys nothing today
        // beyond keeping this class honest on its own.
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return (array) get_plugins();
    }

    /**
     * Pick the plugin file belonging to a slug out of an already-read plugin list.
     *
     * Split from find_plugin_file() so a caller looping over many slugs reads the installed list
     * once instead of once per slug.
     *
     * @since 2.8.3
     *
     * @param string[] $plugin_files Plugin files relative to the plugins directory.
     * @param string   $slug         Plugin directory slug (e.g. "woocommerce-store-toolkit").
     * @return string|null The matching plugin file, or null.
     */
    private function match_plugin_file( array $plugin_files, string $slug ): ?string {
        foreach ( $plugin_files as $plugin_file ) {
            // Compared with the trailing slash so a directory that merely starts with the slug
            // (woocommerce-store-toolkit-pro) is not mistaken for the plugin itself.
            if ( 0 === strpos( $plugin_file, $slug . '/' ) ) {
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
     * method performs a definitive check: if the matching plugin file is active,
     * the status is overridden to 'active'.
     *
     * @since 2.7.10
     * @since 2.8.3 Asks is_plugin_active() instead of reading the active_plugins option, so a
     *              plugin activated across a multisite network is reported as active here too -
     *              and agrees with the companion-tool cards built from the same request.
     * @param array $modules Modules array with 'status' already set.
     * @return array
     */
    private function with_reliable_status( array $modules ): array {
        // Read once for the whole list rather than once per inactive module.
        $plugin_files = array_keys( $this->installed_plugins() );

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

            $plugin_file = $this->match_plugin_file( $plugin_files, $module['slug'] );

            if ( null !== $plugin_file && is_plugin_active( $plugin_file ) ) {
                $modules[ $key ]['status'] = 'active';
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
     * Returns an internal admin URL when a companion plugin is active and the current user can
     * open its screen, or its external product-page URL otherwise.
     *
     * @since 2.7.10
     * @since 2.8.3 Decides each link through can_open_companion_plugin() instead of a bare
     *              function_exists() test, which never answered in a REST request, and picks
     *              Store Toolkit's page slug from the version it has installed.
     * @return array[]
     */
    private function get_woo_tools(): array {
        $tools = array();

        // Product Importer Deluxe.
        $pd_url      = 'https://visser.com.au/how-to-import-woocommerce-products/';
        $pd_internal = $this->can_open_companion_plugin( self::PRODUCT_IMPORTER_DELUXE_SLUG, 'woo_pd_init' );
        if ( $pd_internal ) {
            $pd_url = $this->companion_page_url( self::PRODUCT_IMPORTER_DELUXE_PAGE );
        }
        $tools[] = array(
            'title'       => __( 'Import Products from CSV', 'woocommerce-exporter' ),
            'description' => __( 'Use Product Importer Deluxe to import Product changes back into your WooCommerce store.', 'woocommerce-exporter' ),
            'url'         => $pd_url,
            'is_internal' => $pd_internal,
        );

        // Store Toolkit.
        $st_url      = 'https://wordpress.org/plugins/woocommerce-store-toolkit/';
        $st_internal = $this->can_open_companion_plugin( self::STORE_TOOLKIT_SLUG, 'woo_st_admin_init' );
        if ( $st_internal ) {
            $st_url = $this->companion_page_url( $this->store_toolkit_page() );
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
     * Admin URL of a companion plugin's own top-level screen.
     *
     * The base is always admin_url( 'admin.php' ), which carries no query string, and
     * add_query_arg() reads the URL it is given rather than the current request - so `page` is
     * the only argument there is anything to set.
     *
     * @since 2.8.3
     *
     * @param string $page The page slug the companion plugin registers its screen under.
     * @return string The absolute admin URL.
     */
    private function companion_page_url( string $page ): string {
        return esc_url_raw( add_query_arg( array( 'page' => $page ), admin_url( 'admin.php' ) ) );
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
