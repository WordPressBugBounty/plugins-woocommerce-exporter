<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Traits
 */

namespace VisserLabs\WSE\Traits;

trait Magic_Get_Trait {

    /**
     * Magic get method.
     *
     * @param string $key The key to get.
     *
     * @return null|mixed
     * @since 2.7.3
     */
    public function __get( $key ) {

        return $this->$key ?? null;
    }
}
