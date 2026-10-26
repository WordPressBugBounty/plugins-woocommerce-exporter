<?php
/**
 * AI / MCP REST Controller.
 *
 * @since   2.8.3
 * @package VisserLabs\WSE\Classes\REST
 */

namespace VisserLabs\WSE\Classes\REST;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Classes\Abilities;

defined( 'ABSPATH' ) || exit;

/**
 * AI_MCP_Controller
 *
 * REST endpoints for the AI / MCP screen. The screen promotes the free
 * StoreAgent plugin, which serves the Store Exporter abilities over MCP,
 * and lets the store owner allow or block all Store Exporter write
 * abilities in the StoreAgent MCP allowlist in one step.
 *
 * Ported from Store Exporter Deluxe. The free plugin has fewer abilities, so
 * the prompt cards and screen text cover only the free abilities.
 *
 * StoreAgent has no public MCP API. Every StoreAgent class, method and
 * constant used here is internal, so each call is guarded. A missing symbol
 * shows the "Update StoreAgent" state, never a fatal error.
 *
 * Routes:
 *   GET  /wse/v1/ai-mcp              - screen state
 *   POST /wse/v1/ai-mcp/write-access - allow or block the write abilities
 *
 * @since 2.8.3
 */
class AI_MCP_Controller extends Abstract_Class {

    const REST_NAMESPACE = 'wse/v1';
    const REST_BASE      = 'ai-mcp';

    const STOREAGENT_SLUG     = 'storeagent-ai-for-woocommerce';
    const STOREAGENT_BASENAME = 'storeagent-ai-for-woocommerce/storeagent-ai-for-woocommerce.php';

    /**
     * Oldest StoreAgent version that serves abilities over MCP.
     */
    const STOREAGENT_MIN_VERSION = '1.1.9';

    /**
     * Allowlist size limit, used when StoreAgent does not define its own.
     */
    const DEFAULT_ALLOWLIST_LIMIT = 200;

    const STATE_NOT_INSTALLED = 'not_installed';
    const STATE_INACTIVE      = 'inactive';
    const STATE_OUTDATED      = 'outdated';
    const STATE_SETUP         = 'setup';
    const STATE_READY         = 'ready';

    /**
     * Memoized result of has_mcp_api().
     *
     * @var bool|null
     */
    private $has_mcp_api = null;

    /**
     * Register hooks.
     *
     * @since 2.8.3
     * @return void
     */
    public function run() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST routes.
     *
     * @since 2.8.3
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_BASE,
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_screen' ),
                'permission_callback' => array( $this, 'check_permission' ),
            )
        );

        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_BASE . '/write-access',
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'update_write_access' ),
                'permission_callback' => array( $this, 'check_write_permission' ),
                'args'                => array(
                    'enabled' => array(
                        'type'     => 'boolean',
                        'required' => true,
                    ),
                ),
            )
        );
    }

    /**
     * Permission check. Same capability as every other wse/v1 route.
     *
     * @since 2.8.3
     * @return bool|\WP_Error
     */
    public function check_permission() {
        if ( ! current_user_can( $this->get_export_capability() ) ) {
            return new \WP_Error(
                'rest_forbidden',
                __( 'Sorry, you are not allowed to access this endpoint.', 'woocommerce-exporter' ),
                array( 'status' => rest_authorization_required_code() )
            );
        }

        return true;
    }

    /**
     * Permission check for the write-access route.
     *
     * The route changes a StoreAgent MCP setting, so it also needs the same
     * capability as StoreAgent's own MCP settings route.
     *
     * @since 2.8.3
     * @return bool|\WP_Error
     */
    public function check_write_permission() {
        $allowed = $this->check_permission();

        if ( true !== $allowed ) {
            return $allowed;
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return new \WP_Error(
                'rest_forbidden',
                __( 'Sorry, you are not allowed to change AI access.', 'woocommerce-exporter' ),
                array( 'status' => rest_authorization_required_code() )
            );
        }

        return true;
    }

    /**
     * Return the screen state.
     *
     * @param \WP_REST_Request $request REST request.
     *
     * @since 2.8.3
     * @return \WP_REST_Response
     */
    public function get_screen( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
        $state = $this->get_storeagent_state();

        return rest_ensure_response(
            array(
                'state'        => $state,
                'install_slug' => self::STOREAGENT_SLUG,
                'cta'          => $this->get_cta( $state ),
                'write_access' => $this->get_write_access( $state ),
                'endpoint'     => $this->get_endpoint( $state ),
                'who_can_use'  => $this->get_who_can_use(),
                'categories'   => $this->get_categories(),
                'recipes'      => $this->get_recipes(),
                'labels'       => $this->get_labels(),
            )
        );
    }

    /**
     * Allow or block all Store Exporter write abilities in the StoreAgent
     * MCP allowlist.
     *
     * Saves with the same sequence as the StoreAgent MCP settings REST route:
     * MCP::update_settings(), then a gateway sync. Only the allowlist changes.
     * The `enabled` and `read_denylist` settings and the allowlist entries of
     * other plugins are kept.
     *
     * @param \WP_REST_Request $request REST request.
     *
     * @since 2.8.3
     * @return \WP_REST_Response|\WP_Error
     */
    public function update_write_access( $request ) {
        $state = $this->get_storeagent_state();

        if ( ! $this->is_connected( $state ) ) {
            return new \WP_Error(
                'wse_storeagent_not_connected',
                /* translators: %s: StoreAgent version number. */
                sprintf( __( 'Connect StoreAgent %s or later first.', 'woocommerce-exporter' ), self::STOREAGENT_MIN_VERSION ),
                array( 'status' => 400 )
            );
        }

        $enable      = (bool) $request->get_param( 'enabled' );
        $write_names = $this->get_write_abilities();

        if ( $enable && empty( $write_names ) ) {
            return new \WP_Error(
                'wse_ai_mcp_no_abilities',
                __( 'No Store Exporter write abilities are registered on this site.', 'woocommerce-exporter' ),
                array( 'status' => 400 )
            );
        }

        $allowlist = $this->get_allowlist();

        if ( $enable ) {
            $allowlist = array_values( array_unique( array_merge( $allowlist, $write_names ) ) );
        } else {
            // Remove every Store Exporter ability name, also names that are not
            // registered now (for example when the abilities are turned off for a
            // time), so they do not silently get write access back later. Read
            // abilities never need the allowlist, so it is safe to remove them too.
            // Names of other Visser Labs plugins share the namespace and are kept.
            $allowlist = array_values( array_diff( $allowlist, ( new Abilities() )->get_ability_names() ) );
        }

        // StoreAgent silently truncates an oversized allowlist on save, so refuse
        // before anything is written.
        if ( $enable && count( $allowlist ) > $this->get_allowlist_limit() ) {
            return new \WP_Error(
                'wse_ai_mcp_allowlist_full',
                __( 'The StoreAgent MCP allowlist is full. Remove some entries in StoreAgent > Settings > MCP, then try again.', 'woocommerce-exporter' ),
                array( 'status' => 400 )
            );
        }

        if ( ! \SAAI\Helpers\MCP::update_settings( array( 'allowlist' => $allowlist ) ) ) {
            return new \WP_Error(
                'wse_ai_mcp_write_access_failed',
                __( 'The AI change access could not be saved. Please try again.', 'woocommerce-exporter' ),
                array( 'status' => 500 )
            );
        }

        $sync = \SAAI\Classes\MCP\Mcp_Gateway_Sync::instance()->sync( \SAAI\Helpers\MCP::get_settings() );

        $status  = 'success';
        $message = $enable
            ? __( 'AI can now change your custom meta fields through MCP.', 'woocommerce-exporter' )
            : __( 'AI can no longer change your custom meta fields through MCP.', 'woocommerce-exporter' );

        if ( $enable && ! \SAAI\Helpers\MCP::is_enabled() ) {
            $message = __( 'Saved. AI can make changes once you turn on MCP in StoreAgent.', 'woocommerce-exporter' );
        }

        if ( is_array( $sync ) && isset( $sync['status'] ) && 'failed' === $sync['status'] ) {
            $status  = 'warning';
            $message = __( 'The setting was saved, but StoreAgent could not sync it to the MCP gateway. Retry from StoreAgent > Settings > MCP.', 'woocommerce-exporter' );
        }

        return rest_ensure_response(
            array(
                'status'       => $status,
                'message'      => $message,
                'write_access' => $this->get_write_access( $state ),
            )
        );
    }

    /***********************************************************************
     * StoreAgent state
     **********************************************************************/

    /**
     * Detect the StoreAgent state.
     *
     * @since 2.8.3
     * @return string One of the STATE_* constants.
     */
    private function get_storeagent_state() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ( ! is_plugin_active( self::STOREAGENT_BASENAME ) ) {
            return '' !== $this->get_installed_version() ? self::STATE_INACTIVE : self::STATE_NOT_INSTALLED;
        }

        if ( ! $this->has_mcp_api() ) {
            return self::STATE_OUTDATED;
        }

        return \SAAI\Helpers\MCP::is_available() ? self::STATE_READY : self::STATE_SETUP;
    }

    /**
     * Whether every StoreAgent MCP symbol this controller calls exists.
     *
     * @since 2.8.3
     * @return bool
     */
    private function has_mcp_api() {
        if ( null === $this->has_mcp_api ) {
            $this->has_mcp_api = class_exists( '\SAAI\Helpers\MCP' )
                && class_exists( '\SAAI\Helpers\Connect' )
                && class_exists( '\SAAI\Helpers\MCP_Gateway_Client' )
                && class_exists( '\SAAI\Classes\MCP\Mcp_Gateway_Sync' )
                && method_exists( '\SAAI\Helpers\MCP', 'get_settings' )
                && method_exists( '\SAAI\Helpers\MCP', 'update_settings' )
                && method_exists( '\SAAI\Helpers\MCP', 'classify_ability' )
                && method_exists( '\SAAI\Helpers\MCP', 'is_available' )
                && method_exists( '\SAAI\Helpers\MCP', 'is_enabled' )
                && method_exists( '\SAAI\Helpers\Connect', 'is_connected' )
                && method_exists( '\SAAI\Helpers\MCP_Gateway_Client', 'get_endpoint_url' )
                && method_exists( '\SAAI\Classes\MCP\Mcp_Gateway_Sync', 'instance' )
                && method_exists( '\SAAI\Classes\MCP\Mcp_Gateway_Sync', 'sync' )
                && defined( '\SAAI\Helpers\MCP::CLASS_WRITE' );
        }

        return $this->has_mcp_api;
    }

    /**
     * Installed StoreAgent version, active or not.
     *
     * @since 2.8.3
     * @return string Version, or an empty string when StoreAgent is not installed.
     */
    private function get_installed_version() {
        if ( defined( 'SAAI_VERSION' ) ) {
            return (string) SAAI_VERSION;
        }

        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();

        return isset( $plugins[ self::STOREAGENT_BASENAME ] ) ? (string) $plugins[ self::STOREAGENT_BASENAME ]['Version'] : '';
    }

    /**
     * Whether StoreAgent is connected to its account.
     *
     * @param string $state StoreAgent state.
     *
     * @since 2.8.3
     * @return bool
     */
    private function is_connected( $state ) {
        return in_array( $state, array( self::STATE_SETUP, self::STATE_READY ), true ) && \SAAI\Helpers\Connect::is_connected();
    }

    /***********************************************************************
     * Abilities and allowlist
     **********************************************************************/

    /**
     * Registered Store Exporter abilities.
     *
     * @since 2.8.3
     * @return array Ability objects, keyed by ability name.
     */
    private function get_registered_abilities() {
        if ( ! function_exists( 'wp_get_abilities' ) ) {
            return array();
        }

        // wp_get_ability() logs a notice for a name that is not registered, so
        // filter the full list instead.
        $names     = array_flip( ( new Abilities() )->get_ability_names() );
        $abilities = array();
        foreach ( wp_get_abilities() as $ability ) {
            if ( isset( $names[ $ability->get_name() ] ) ) {
                $abilities[ $ability->get_name() ] = $ability;
            }
        }

        return $abilities;
    }

    /**
     * Names of the registered Store Exporter abilities that StoreAgent
     * classifies as write abilities.
     *
     * @since 2.8.3
     * @return string[]
     */
    private function get_write_abilities() {
        if ( ! $this->has_mcp_api() ) {
            return array();
        }

        $names = array();
        foreach ( $this->get_registered_abilities() as $name => $ability ) {
            if ( \SAAI\Helpers\MCP::CLASS_WRITE === \SAAI\Helpers\MCP::classify_ability( $ability ) ) {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * The StoreAgent MCP allowlist.
     *
     * @since 2.8.3
     * @return string[]
     */
    private function get_allowlist() {
        $settings = \SAAI\Helpers\MCP::get_settings();

        return isset( $settings['allowlist'] ) && is_array( $settings['allowlist'] ) ? $settings['allowlist'] : array();
    }

    /**
     * The StoreAgent MCP allowlist size limit.
     *
     * @since 2.8.3
     * @return int
     */
    private function get_allowlist_limit() {
        return defined( '\SAAI\Helpers\MCP::MAX_ALLOWLIST_ENTRIES' ) ? (int) \SAAI\Helpers\MCP::MAX_ALLOWLIST_ENTRIES : self::DEFAULT_ALLOWLIST_LIMIT;
    }

    /**
     * Capability that the export abilities and the wse/v1 routes need.
     *
     * @since 2.8.3
     * @return string
     */
    private function get_export_capability() {
        return apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' );
    }

    /***********************************************************************
     * Screen data
     **********************************************************************/

    /**
     * The hero call to action for a StoreAgent state.
     *
     * Action `install` installs or activates StoreAgent in place through the
     * wse/v1/tools/install route. Action `link` opens `url`. Action `none` shows
     * only the note.
     *
     * @param string $state StoreAgent state.
     *
     * @since 2.8.3
     * @return array
     */
    private function get_cta( $state ) {
        $file_mods_allowed = wp_is_file_mod_allowed( 'wse_install_plugin' );

        // The tools/install route needs install_plugins, and it activates the plugin.
        $can_install = current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' ) && $file_mods_allowed;
        $version     = $this->get_installed_version();

        switch ( $state ) {
            case self::STATE_NOT_INSTALLED:
                if ( $can_install ) {
                    return $this->cta(
                        'install',
                        __( 'Install StoreAgent Free', 'woocommerce-exporter' ),
                        '',
                        __( 'Free on WordPress.org. Takes about a minute to set up.', 'woocommerce-exporter' )
                    );
                }

                return $this->cta(
                    'link',
                    __( 'Get StoreAgent Free', 'woocommerce-exporter' ),
                    $this->get_wporg_url(),
                    $file_mods_allowed
                        ? __( 'Ask a site administrator to install StoreAgent.', 'woocommerce-exporter' )
                        : __( 'This site does not allow plugin installs from the dashboard.', 'woocommerce-exporter' ),
                    true
                );

            case self::STATE_INACTIVE:
                if ( $can_install ) {
                    return $this->cta(
                        'install',
                        __( 'Activate StoreAgent', 'woocommerce-exporter' ),
                        '',
                        __( 'StoreAgent is installed but not active.', 'woocommerce-exporter' )
                    );
                }

                // DISALLOW_FILE_MODS takes install_plugins away, which the install
                // route needs. A user who can still activate plugins gets the core
                // activation link.
                if ( current_user_can( 'activate_plugin', self::STOREAGENT_BASENAME ) ) {
                    return $this->cta(
                        'link',
                        __( 'Activate StoreAgent', 'woocommerce-exporter' ),
                        // Not wp_nonce_url(): it HTML-escapes the URL for output.
                        add_query_arg(
                            array(
                                'action'   => 'activate',
                                'plugin'   => rawurlencode( self::STOREAGENT_BASENAME ),
                                '_wpnonce' => wp_create_nonce( 'activate-plugin_' . self::STOREAGENT_BASENAME ),
                            ),
                            admin_url( 'plugins.php' )
                        ),
                        __( 'StoreAgent is installed but not active.', 'woocommerce-exporter' )
                    );
                }

                return $this->cta(
                    'none',
                    '',
                    '',
                    __( 'StoreAgent is installed but not active. Ask a site administrator to activate it.', 'woocommerce-exporter' )
                );

            case self::STATE_OUTDATED:
                // A version from 1.1.9 up that fails the API check renamed an
                // internal symbol, so do not claim a version number for it.
                $note = '' !== $version && version_compare( $version, self::STOREAGENT_MIN_VERSION, '<' )
                    /* translators: %s: StoreAgent version number. */
                    ? sprintf( __( 'MCP needs StoreAgent %s or later.', 'woocommerce-exporter' ), self::STOREAGENT_MIN_VERSION )
                    : __( 'This StoreAgent version does not work with this screen. Update StoreAgent to the latest version.', 'woocommerce-exporter' );

                return $this->cta(
                    'link',
                    __( 'Update StoreAgent', 'woocommerce-exporter' ),
                    admin_url( 'plugins.php' ),
                    $note
                );

            case self::STATE_SETUP:
                $connected = $this->is_connected( $state );

                return $this->cta(
                    'link',
                    $connected
                        ? __( 'Turn On MCP In StoreAgent', 'woocommerce-exporter' )
                        : __( 'Connect StoreAgent', 'woocommerce-exporter' ),
                    $connected
                        ? admin_url( 'admin.php?page=storeagent-settings&tab=mcp&subtab=general' )
                        : admin_url( 'admin.php?page=storeagent-dashboard' ),
                    $connected
                        ? __( 'Turn on MCP in StoreAgent to finish setup.', 'woocommerce-exporter' )
                        : __( 'Connect StoreAgent and turn on MCP to finish setup.', 'woocommerce-exporter' )
                );

            default:
                return $this->cta(
                    'link',
                    __( 'Manage StoreAgent Connection', 'woocommerce-exporter' ),
                    admin_url( 'admin.php?page=storeagent-settings&tab=mcp&subtab=connections' ),
                    sprintf(
                        /* translators: %s: StoreAgent version number. */
                        __( 'StoreAgent %s. Change access applies to every connected AI client.', 'woocommerce-exporter' ),
                        $version
                    )
                );
        }
    }

    /**
     * Build a call to action array.
     *
     * @param string $action   Action: install, link or none.
     * @param string $label    Button label.
     * @param string $url      Link URL for the link action.
     * @param string $note     Footnote under the button.
     * @param bool   $external Whether the link opens a new tab.
     *
     * @since 2.8.3
     * @return array
     */
    private function cta( $action, $label, $url, $note, $external = false ) {
        return array(
            'action'   => $action,
            'label'    => $label,
            'url'      => $url ? esc_url_raw( $url ) : '',
            'note'     => $note,
            'external' => $external,
        );
    }

    /**
     * The "Allow AI to make changes" toggle data.
     *
     * The toggle is on only when every registered write ability is in the
     * allowlist. `allowed` and `total` let the screen show a partial count.
     *
     * @param string $state StoreAgent state.
     *
     * @since 2.8.3
     * @return array
     */
    private function get_write_access( $state ) {
        $connected   = $this->is_connected( $state );
        $write_names = $connected ? $this->get_write_abilities() : array();
        $allowed     = $connected ? array_intersect( $write_names, $this->get_allowlist() ) : array();
        $note        = '';

        if ( empty( $this->get_registered_abilities() ) ) {
            $note = function_exists( 'wp_register_ability' )
                ? __( 'The Store Exporter AI abilities are turned off on this site.', 'woocommerce-exporter' )
                : __( 'The Store Exporter AI abilities need WordPress 6.9 or later.', 'woocommerce-exporter' );
        }

        return array(
            'available' => $connected && ! empty( $write_names ) && current_user_can( 'manage_woocommerce' ),
            'enabled'   => ! empty( $write_names ) && count( $allowed ) === count( $write_names ),
            'allowed'   => count( $allowed ),
            'total'     => count( $write_names ),
            'note'      => $note,
        );
    }

    /**
     * The MCP endpoint card data.
     *
     * @param string $state StoreAgent state.
     *
     * @since 2.8.3
     * @return array
     */
    private function get_endpoint( $state ) {
        $has_endpoint = in_array( $state, array( self::STATE_SETUP, self::STATE_READY ), true );

        if ( self::STATE_READY === $state ) {
            $help = __( 'Paste this into any MCP client to connect it to your store.', 'woocommerce-exporter' );
        } elseif ( $has_endpoint ) {
            $help = __( 'This is your store\'s endpoint. It works in MCP clients once you connect StoreAgent and turn on MCP.', 'woocommerce-exporter' );
        } elseif ( self::STATE_OUTDATED === $state ) {
            $help = __( 'Available once StoreAgent is updated.', 'woocommerce-exporter' );
        } else {
            $help = __( 'Available once StoreAgent is installed and activated.', 'woocommerce-exporter' );
        }

        return array(
            'available' => self::STATE_READY === $state,
            'url'       => $has_endpoint
                ? \SAAI\Helpers\MCP_Gateway_Client::get_endpoint_url()
                : 'https://api.storeagent.ai/mcp/' . strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ),
            'help'      => $help,
        );
    }

    /**
     * The "Who can use it" lines.
     *
     * @since 2.8.3
     * @return string[]
     */
    private function get_who_can_use() {
        $capability = $this->get_export_capability();

        $lines = array(
            'view_woocommerce_reports' === $capability
                ? __( 'Export abilities need the Shop Manager role or higher.', 'woocommerce-exporter' )
                /* translators: %s: WordPress capability name. */
                : sprintf( __( 'Export abilities need the %s capability.', 'woocommerce-exporter' ), $capability ),
        );

        // The free abilities return metadata only. Running an export from AI is
        // a Store Exporter Deluxe ability.
        $lines[] = __( 'AI can look up export types, fields and filters, and change custom meta fields. It never gets your export rows.', 'woocommerce-exporter' );
        $lines[] = __( 'Running, scheduling and finding exports from AI needs Store Exporter Deluxe.', 'woocommerce-exporter' );

        return $lines;
    }

    /**
     * The prompt card filter chips.
     *
     * @since 2.8.3
     * @return array[]
     */
    private function get_categories() {
        return array(
            array(
                'id'    => 'all',
                'label' => __( 'All', 'woocommerce-exporter' ),
            ),
            array(
                'id'    => 'explore',
                'label' => __( 'Explore', 'woocommerce-exporter' ),
            ),
            array(
                'id'    => 'custom-fields',
                'label' => __( 'Custom Fields', 'woocommerce-exporter' ),
            ),
        );
    }

    /**
     * The "Try It" prompt cards.
     *
     * `abilities` are short names in the visser-labs namespace. `needs_write`
     * marks a card that uses a write ability, so the screen locks it while
     * change access is off.
     *
     * @since 2.8.3
     * @return array[]
     */
    private function get_recipes() {
        $recipes = array(
            array( 'what-can-i-export', 'explore', 'list-checks', __( 'What Can I Export?', 'woocommerce-exporter' ), __( 'Which export types can I use in my store, and which fields can I export for orders?', 'woocommerce-exporter' ), array( 'list-export-types', 'get-export-fields' ), false ),
            array( 'store-at-a-glance', 'explore', 'calculator', __( 'Store At A Glance', 'woocommerce-exporter' ), __( 'How many products, orders and users can I export, and when did I last export each one?', 'woocommerce-exporter' ), array( 'list-export-types' ), false ),
            array( 'order-filter-options', 'explore', 'search', __( 'Order Filter Options', 'woocommerce-exporter' ), __( 'Which order statuses and sort options can I use when I export orders?', 'woocommerce-exporter' ), array( 'get-export-filters' ), false ),
            array( 'check-custom-fields', 'custom-fields', 'activity', __( 'Check Custom Fields', 'woocommerce-exporter' ), __( 'Which custom meta keys are added to my order and product exports?', 'woocommerce-exporter' ), array( 'get-custom-fields' ), false ),
            array( 'add-order-meta', 'custom-fields', 'receipt', __( 'Add Order Meta', 'woocommerce-exporter' ), __( 'Add the _delivery_date order meta key to my order exports for Quick Export.', 'woocommerce-exporter' ), array( 'get-custom-fields', 'set-custom-fields' ), true ),
            array( 'add-product-meta', 'custom-fields', 'package', __( 'Add Product Meta', 'woocommerce-exporter' ), __( 'Add the _supplier_code product meta key to my product exports.', 'woocommerce-exporter' ), array( 'get-custom-fields', 'set-custom-fields' ), true ),
        );

        return array_map(
            function ( $recipe ) {
                return array(
                    'id'          => $recipe[0],
                    'category'    => $recipe[1],
                    'icon'        => $recipe[2],
                    'title'       => $recipe[3],
                    'prompt'      => $recipe[4],
                    'abilities'   => array_map(
                        function ( $name ) {
                            return Abilities::CATEGORY . '/' . $name;
                        },
                        $recipe[5]
                    ),
                    'needs_write' => $recipe[6],
                );
            },
            $recipes
        );
    }

    /**
     * Static screen text.
     *
     * The admin SPA has no translation loader, so the screen reads its text from
     * this response.
     *
     * @since 2.8.3
     * @return array
     */
    private function get_labels() {
        return array(
            'title'            => __( 'AI / MCP', 'woocommerce-exporter' ),
            'subtitle'         => __( 'Let AI assistants look up your export options and manage custom meta fields.', 'woocommerce-exporter' ),
            'eyebrow'          => __( 'WordPress Abilities API + Store Exporter', 'woocommerce-exporter' ),
            'heading'          => __( 'Ask Your AI About Your Store Exports', 'woocommerce-exporter' ),
            /* translators: The text between <link> and </link> becomes a link to the StoreAgent plugin on WordPress.org. */
            'intro'            => __( 'Connect Store Exporter to AI assistants like Claude, ChatGPT and Cursor. Ask which export types and fields you have, look up filter options, or add custom meta keys to your exports, in plain English. Connect them with the free <link>StoreAgent</link> plugin.', 'woocommerce-exporter' ),
            'wporg_url'        => $this->get_wporg_url(),
            'any_client'       => __( '+ Any MCP Client', 'woocommerce-exporter' ),
            'write_toggle'     => __( 'Allow AI to make changes', 'woocommerce-exporter' ),
            /* translators: 1: number of allowed write abilities, 2: number of write abilities. */
            'write_partial'    => __( '%1$d of %2$d allowed', 'woocommerce-exporter' ),
            'try_heading'      => __( 'Try It: Export Tasks AI Can Handle For You', 'woocommerce-exporter' ),
            'try_help_on'      => __( 'Copy a prompt into your AI assistant. AI can read your export options and change your custom meta fields for you.', 'woocommerce-exporter' ),
            'try_help_off'     => __( 'Copy a prompt into your AI assistant. Change access is off, so AI can look things up but can\'t change your custom meta fields.', 'woocommerce-exporter' ),
            'filter_label'     => __( 'Filter prompts', 'woocommerce-exporter' ),
            'needs_write'      => __( 'Needs change access', 'woocommerce-exporter' ),
            'docs_link'        => __( 'Abilities API Docs', 'woocommerce-exporter' ),
            'endpoint_heading' => __( 'Your Store\'s MCP Endpoint', 'woocommerce-exporter' ),
            'endpoint_label'   => __( 'MCP endpoint URL', 'woocommerce-exporter' ),
            'copy'             => __( 'Copy', 'woocommerce-exporter' ),
            'copied'           => __( 'Endpoint URL copied.', 'woocommerce-exporter' ),
            'copy_manual'      => __( 'Press Ctrl+C or Cmd+C to copy the selected URL.', 'woocommerce-exporter' ),
            'who_heading'      => __( 'Who Can Use It', 'woocommerce-exporter' ),
            'installing'       => __( 'Installing…', 'woocommerce-exporter' ),
            'activating'       => __( 'Activating…', 'woocommerce-exporter' ),
            'install_done'     => __( 'StoreAgent is active. Reloading the page…', 'woocommerce-exporter' ),
            'install_failed'   => __( 'StoreAgent could not be installed. Please try again.', 'woocommerce-exporter' ),
            'activate_failed'  => __( 'StoreAgent could not be activated. Please try again.', 'woocommerce-exporter' ),
            'write_failed'     => __( 'The AI change access could not be saved. Please try again.', 'woocommerce-exporter' ),
        );
    }

    /**
     * StoreAgent plugin page on WordPress.org.
     *
     * @since 2.8.3
     * @return string
     */
    private function get_wporg_url() {
        return 'https://wordpress.org/plugins/' . self::STOREAGENT_SLUG . '/';
    }
}
