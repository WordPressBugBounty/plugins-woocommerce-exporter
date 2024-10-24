<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Traits
 */

namespace VisserLabs\WSE\Traits;

/**
 * Trait Trait_Instance
 *
 * @since 2.7.3
 */
trait Singleton_Trait {

    /**
     * Holds the class instance object
     *
     * @var Singleton_Trait $instance object
     * @since 2.7.3
     */
    protected static $instance;

    /**
     * Return an instance of this class
     *
     * @param array ...$args The arguments to pass to the constructor.
     *
     * @codeCoverageIgnore
     *
     * @return Singleton_Trait The class instance object
     * @since 2.7.3
     */
    public static function instance( ...$args ) {

        if ( null === static::$instance ) {
            static::$instance = new static( ...$args );
        }

        return static::$instance;
    }

    /**
     * Magic get method
     *
     * @param string $key Class property to get.
     *
     * @return null|mixed
     * @since 2.7.3
     */
    public function __get( $key ) {

        return $this->$key ?? null;
    }
}
