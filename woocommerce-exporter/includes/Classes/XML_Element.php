<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Classes
 */

namespace VisserLabs\WSE\Classes;

use SimpleXMLElement;

defined( 'ABSPATH' ) || exit;

/**
 * XML Element
 * Extends SimpleXMLElement to allow for property access
 *
 * @since 2.7.3
 */
class XML_Element extends SimpleXMLElement {

    /**
     * Add CDATA to a SimpleXMLElement node
     *
     * @since 2.7.3
     *
     * @param string $str The string to add as CDATA.
     * @throws \Exception If the SimpleXMLElement cannot be imported into DOM.
     */
    public function addCData( $str ) {
        $node = dom_import_simplexml( $this );
        if ( ! $node ) {
            throw new \Exception( 'Failed to import SimpleXMLElement into DOM.' );
        }
        $no = $node->ownerDocument;
        $node->appendChild( $no->createCDATASection( $str ) );
    }
}
