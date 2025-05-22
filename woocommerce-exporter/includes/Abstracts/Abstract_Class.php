<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE
 */

namespace VisserLabs\WSE\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Class
 */
abstract class Abstract_Class {

    /**
     * Magic get method.
     *
     * @param string $key The key to get.
     *
     * @since 2.7.3
     * @return null|mixed
     */
    public function __get( $key ) {

        return $this->$key ?? null;
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 2.7.3
     */
    abstract public function run();
}
