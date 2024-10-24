<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Abstracts
 */

namespace VisserLabs\WSE\Abstracts;

use VisserLabs\WSE\Helpers\Helper;

/**
 * Abstract Update class.<br>
 * Update classes should extend this abstract class and implement the `actions()` method. It should do whatever is
 * needed to update the plugin except updating the version number in database as that is already done by default by the
 * abstract class, unless, `run()` method is overridden.
 *
 * @since 2.7.3
 */
abstract class Abstract_Update extends Abstract_Class {

    /**
     * Run updates.
     *
     * @since 2.7.3
     */
    public function run() {
        if ( version_compare( Helper::get_plugin_version(), get_option( WSE_OPTION_INSTALLED_VERSION ), '!=' ) ) {
            $this->actions();

            /***************************************************************************
             * Update plugin version installed in database
             ***************************************************************************
             *
             * We update the version value in the database to the current version of the
             * plugin.
             */
            update_site_option( WSE_OPTION_INSTALLED_VERSION, Helper::get_plugin_version() );
        }
    }

    /**
     * Perform update actions.
     *
     * @return void
     */
    abstract public function actions();
}
