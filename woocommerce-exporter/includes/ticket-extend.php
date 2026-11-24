<?php
// Adds custom Ticket columns to the Ticket fields list
function woo_ce_extend_ticket_fields( $fields = array() ) {

	// Tickera - https://tickera.com/
	if( woo_ce_detect_export_plugin( 'tickera' ) ) {
		$fields[] = array(
			'name' => 'ticket_code',
			'label' => __( 'Ticket Code', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_type_id',
			'label' => __( 'Ticket Type ID', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_event_id',
			'label' => __( 'Ticket Event ID', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_first_name',
			'label' => __( 'Ticket First Name', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_last_name',
			'label' => __( 'Ticket Last Name', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$tickera_fields = woo_ce_get_tickera_custom_fields();
		if( !empty( $tickera_fields ) ) {
			foreach( $tickera_fields as $tickera_field ) {
				$fields[] = array(
					'name' => sprintf( 'ticket_custom_%s', sanitize_key( $tickera_field['name'] ) ),
					'label' => sprintf( __( 'Ticket: %s', 'woocommerce-exporter' ), $tickera_field['label'] ),
					'hover' => __( 'Tickera', 'woocommerce-exporter' )
				);
			}
		}
		unset( $tickera_fields );
	}

	// FooEvents for WooCommerce - http://www.woocommerceevents.com/
	if( woo_ce_detect_export_plugin( 'fooevents' ) ) {
		$fields[] = array(
			'name' => 'barcode',
			'label' => __( 'Barcode', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'variation_id',
			'label' => __( 'Variation ID', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_event_name',
			'label' => __( 'Event Name', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'purchaser_first_name',
			'label' => __( 'Purchaser First Name', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'purchaser_last_name',
			'label' => __( 'Purchaser Last Name', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'purchaser_email',
			'label' => __( 'Purchaser E-mail', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'attendee_first_name',
			'label' => __( 'Attendee First Name', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'attendee_last_name',
			'label' => __( 'Attendee Last Name', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'attendee_email',
			'label' => __( 'Attendee E-mail', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'attendee_phone',
			'label' => __( 'Attendee Phone', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_sent',
			'label' => __( 'Ticket Sent', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
	}

	return $fields;

}
add_filter( 'woo_ce_ticket_fields', 'woo_ce_extend_ticket_fields' );

/**
 * Add the Tickera and FooEvents columns to an exported ticket.
 *
 * The Barcode and Ticket Sent columns are the two that cost something per row - a file system
 * probe and an Order load - and woo_ce_get_ticket_data() trims the ticket back to the requested
 * fields only AFTER this filter has run, so both were paid for on every row of every ticket
 * export whether or not the column had been asked for. The requested field list is read here to
 * skip them. A column that was not asked for is left unset rather than set to an empty
 * value, so a later callback on this filter can tell the two apart. An empty list means
 * every field, exactly as that trim reads it.
 *
 * @since 2.8.3 Accepts the requested export fields.
 *
 * @param stdClass $ticket    The ticket being exported.
 * @param int      $ticket_id ID of the ticket record. Unused; part of the filter signature.
 * @param array    $fields    Export fields that were requested; empty means all of them.
 * @return stdClass The ticket with the integration columns added.
 */
function woo_ce_extend_ticket_item( $ticket, $ticket_id = 0, $fields = array() ) {

	$requested = ( is_array( $fields ) ? $fields : array() );

	// Tickera - https://tickera.com/
	if( woo_ce_detect_export_plugin( 'tickera' ) ) {
		$ticket->user_id = $ticket->post_author;
		$ticket->status = $ticket->post_status;
		$ticket->order_id = $ticket->post_parent;
		$ticket->ticket_code = get_post_meta( $ticket->ID, 'ticket_code', true );
		$ticket->ticket_type_id = get_post_meta( $ticket->ID, 'ticket_type_id', true );
		$ticket->ticket_event_id = get_post_meta( $ticket->ID, 'event_id', true );
		$ticket->ticket_first_name = get_post_meta( $ticket->ID, 'first_name', true );
		$ticket->ticket_last_name = get_post_meta( $ticket->ID, 'last_name', true );
		$tickera_fields = woo_ce_get_tickera_custom_fields();
		if( !empty( $tickera_fields ) ) {
			foreach( $tickera_fields as $tickera_field )
				$ticket->{sprintf( 'ticket_custom_%s', sanitize_key( $tickera_field['name'] ) )} = get_post_meta( $ticket->ID, $tickera_field['name'], true );
		}
	}

	// FooEvents for WooCommerce - https://www.fooevents.com/
	if( woo_ce_detect_export_plugin( 'fooevents' ) ) {
		$ticket->ticket_id = get_post_meta( $ticket->ID, 'WooCommerceEventsTicketID', true );
		$ticket->status = get_post_meta( $ticket->ID, 'WooCommerceEventsStatus', true );
		$ticket->order_id = get_post_meta( $ticket->ID, 'WooCommerceEventsOrderID', true );
		$ticket->user_id = get_post_meta( $ticket->ID, 'WooCommerceEventsCustomerID', true );
		$ticket->product_id = get_post_meta( $ticket->ID, 'WooCommerceEventsProductID', true );
		$ticket->variation_id = get_post_meta( $ticket->ID, 'WooCommerceEventsVariationID', true );
		$ticket->ticket_event_name = get_post_meta( $ticket->ID, 'WooCommerceEventsProductName', true );
		// Resolved only when the export asked for it - see the docblock above.
		if ( empty( $requested ) || in_array( 'barcode', $requested, true ) ) {
			$ticket->barcode = woo_ce_get_fooevents_ticket_barcode( $ticket->ticket_id, $ticket->ID );
		}
		$ticket->purchaser_first_name = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserFirstName', true );
		$ticket->purchaser_last_name = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserLastName', true );
		$ticket->purchaser_email = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserEmail', true );
		$ticket->attendee_first_name = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeName', true );
		$ticket->attendee_last_name = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeLastName', true );
		$ticket->attendee_email = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeEmail', true );
		$ticket->attendee_phone = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeTelephone', true );
		// Resolved only when the export asked for it - see the docblock above.
		if ( empty( $requested ) || in_array( 'ticket_sent', $requested, true ) ) {
			$ticket->ticket_sent = woo_ce_format_switch( woo_ce_get_fooevents_ticket_sent( $ticket->order_id ) );
		}
	}

	return $ticket;

}
add_filter( 'woo_ce_ticket_item', 'woo_ce_extend_ticket_item', 10, 3 );

if ( ! function_exists( 'woo_ce_get_fooevents_config_property' ) ) {
    /**
     * Read a public property off a FooEvents configuration class.
     *
     * FooEvents has moved the barcode directory between releases: the legacy
     * WooCommerce Events class exposed it as the instance property
     * WooCommerce_Events_Config::$barcodePath, current releases as the instance
     * property FooEvents_Config::$barcode_path. Both shapes are per-instance and
     * public, so the configuration object is built and asked for its public
     * properties - which is also the one read that sees a property a constructor
     * assigns without the class declaring it.
     *
     * get_object_vars() is called from outside the class, so it reports only what is
     * public and initialised. Every other shape - a private property, an uninitialised
     * typed property, a static property, no such property at all - is simply absent
     * from it, with no diagnostic raised, and resolves to an empty string. A
     * constructor that throws, or that needs arguments, is caught for the same result,
     * so an unrecognised FooEvents release costs the Barcode column its image rather
     * than taking the export down.
     *
     * A STATIC property is therefore not read. Checked against FooEvents for WooCommerce
     * 1.19.9, which exposes the directory as a public instance property; a release that
     * moved it to a static would fall through to the legacy class and then to the uploads
     * default, which can name an image from the wrong directory rather than none at all.
     *
     * @since 2.8.3
     *
     * @param string $class_name Configuration class to read.
     * @param string $property   Property holding the barcode directory.
     * @return string Property value, or an empty string when it cannot be read.
     */
    function woo_ce_get_fooevents_config_property( $class_name, $property ) {

        if ( ! class_exists( $class_name ) ) {
            return '';
        }

        try {
            $config = new $class_name();
        } catch ( Throwable $e ) {
            return '';
        }

        $properties = get_object_vars( $config );

        if ( ! array_key_exists( $property, $properties ) ) {
            return '';
        }

        return ( is_string( $properties[ $property ] ) ? $properties[ $property ] : '' );
    }
}

if ( ! function_exists( 'woo_ce_get_fooevents_barcode_directory' ) ) {
    /**
     * Resolve the directory FooEvents keeps its generated barcode images in.
     *
     * Current FooEvents releases expose it on FooEvents_Config, the legacy WooCommerce
     * Events releases on WooCommerce_Events_Config. Stores running a release that ships
     * neither fall back to the directory FooEvents defaults to inside the uploads folder.
     *
     * The Barcode column asks for this once per exported ticket, and resolving it instantiates
     * a third-party class, so the answer is kept for the request.
     * The memo is keyed by blog because the legacy ticket dataset path switches blogs, and
     * the uploads directory the fallback is built from is per-blog.
     *
     * @since 2.8.3
     *
     * @return string Barcode directory with a trailing separator, or an empty string.
     */
    function woo_ce_get_fooevents_barcode_directory() {

        static $memo = array();

        $blog_id = get_current_blog_id();
        if ( isset( $memo[ $blog_id ] ) ) {
            return $memo[ $blog_id ];
        }

        // Current FooEvents releases.
        $barcode_path = woo_ce_get_fooevents_config_property( 'FooEvents_Config', 'barcode_path' );

        // Legacy WooCommerce Events releases.
        if ( '' === $barcode_path ) {
            $barcode_path = woo_ce_get_fooevents_config_property( 'WooCommerce_Events_Config', 'barcodePath' );
        }

        // Neither configuration class resolved, fall back to the FooEvents default.
        if ( '' === $barcode_path ) {
            $uploads = wp_upload_dir();
            if ( empty( $uploads['error'] ) && ! empty( $uploads['basedir'] ) ) {
                $barcode_path = trailingslashit( $uploads['basedir'] ) . 'fooevents/barcodes/';
            }
        }

        $memo[ $blog_id ] = ( '' === $barcode_path ? '' : trailingslashit( sanitize_text_field( $barcode_path ) ) );

        return $memo[ $blog_id ];
    }
}

if ( ! function_exists( 'woo_ce_get_fooevents_ticket_barcode' ) ) {
    /**
     * Resolve the Barcode column value of a FooEvents ticket.
     *
     * FooEvents keeps the generated barcode image outside of the ticket record, so the
     * exported value is the barcode image when one can be resolved and the ticket ID
     * otherwise. Current FooEvents releases name the image {ticket hash}-{ticket ID}.png
     * and the legacy WooCommerce Events releases named it {ticket ID}.png, so both are
     * tried against the resolved directory before the column falls back to the ticket ID.
     *
     * @since 2.8.3
     *
     * @param string $ticket_id FooEvents ticket ID.
     * @param int    $post_id   ID of the ticket record the barcode belongs to.
     * @return string Barcode image path, or the ticket ID when no image resolves.
     */
    function woo_ce_get_fooevents_ticket_barcode( $ticket_id, $post_id = 0 ) {

        $barcode_path = woo_ce_get_fooevents_barcode_directory();

        /**
         * Filter the directory FooEvents barcode images are read from.
         *
         * Lets a store that keeps its barcode images somewhere other than the
         * directory reported by FooEvents point the Barcode column at them. Return
         * an empty string to export the ticket ID instead.
         *
         * @since 2.8.3
         *
         * @param string $barcode_path Resolved barcode directory, including its trailing separator.
         * @param string $ticket_id    FooEvents ticket ID being exported.
         * @param int    $post_id      ID of the ticket record being exported.
         */
        $barcode_path = apply_filters( 'woo_ce_fooevents_barcode_path', $barcode_path, $ticket_id, $post_id );

        // A filter callback can return anything at all, and an array reaches trailingslashit()
        // as a TypeError that would end the export.
        if ( ! is_string( $barcode_path ) ) {
            $barcode_path = '';
        }

        $barcode = '';
        if ( '' !== $barcode_path && ! empty( $ticket_id ) ) {
            $barcode_path = trailingslashit( $barcode_path );

            // Current FooEvents releases prefix the file name with the ticket's hash.
            $ticket_hash = ( ! empty( $post_id ) ? get_post_meta( $post_id, 'WooCommerceEventsTicketHash', true ) : '' );

            // Both segments are store-controlled post meta, and this is the first release that
            // puts them through file_exists(). Left whole, a ticket ID of "../../wp-config.php"
            // would probe outside the barcode directory and, on a hit, write the path it
            // resolved into the exported file. Only the file NAME is reduced to its last
            // segment - the fallback below still exports the ticket ID as it is stored, because
            // that is exported data rather than a path.
            $file_ticket_id = basename( (string) $ticket_id );

            $candidates = array();
            if ( '' !== $file_ticket_id ) {
                if ( ! empty( $ticket_hash ) && is_scalar( $ticket_hash ) ) {
                    $candidates[] = $barcode_path . basename( (string) $ticket_hash ) . '-' . $file_ticket_id . '.png';
                }
                $candidates[] = $barcode_path . $file_ticket_id . '.png';
            }

            foreach ( $candidates as $candidate ) {
                // A local path that no longer resolves to an image would export a dead file reference.
                if ( preg_match( '#^(https?:)?//#', $candidate ) || file_exists( $candidate ) ) {
                    $barcode = $candidate;
                    break;
                }
            }
        }

        /**
         * Filter the resolved FooEvents barcode image.
         *
         * Receives the full path of the image that was found, or an empty string
         * when none of the known file names resolved. Return an empty string to
         * export the ticket ID instead of an image.
         *
         * @since 2.8.3
         *
         * @param string $barcode   Resolved barcode image path, or an empty string.
         * @param string $ticket_id FooEvents ticket ID being exported.
         * @param int    $post_id   ID of the ticket record being exported.
         */
        $barcode = apply_filters( 'woo_ce_fooevents_barcode_image', $barcode, $ticket_id, $post_id );

        return ( ! empty( $barcode ) && is_scalar( $barcode ) ? (string) $barcode : $ticket_id );
    }
}

if ( ! function_exists( 'woo_ce_get_fooevents_ticket_sent' ) ) {
    /**
     * Get the raw FooEvents "ticket sent" flag of the Order a ticket belongs to.
     *
     * FooEvents records the flag against the Order rather than the ticket, as
     * WooCommerceEventsTicketsSent on current releases and WooCommerceEventsSentTicket
     * on the releases that preceded them. An Order is not a Post on stores running
     * High-Performance Order Storage, so the flag is read through the WooCommerce CRUD
     * API instead of get_post_meta(), which returns nothing on those stores. That is
     * worse than a blank column: woo_ce_format_switch() reads an empty value as "not
     * sent", so every ticket of such a store exported a confident "No".
     *
     * Every ticket of an Order carries the same flag, so the Order is loaded once per
     * Order for as long as the request lives. A batched export runs each batch in a
     * request of its own, so the cache starts empty again on every batch.
     *
     * The cache is keyed by blog as well as by Order, because an Order ID is only unique
     * within a blog and the legacy ticket dataset path runs under switch_to_blog().
     *
     * @since 2.8.3
     *
     * @param int $order_id ID of the Order the ticket was purchased on.
     * @return string Raw meta value, or an empty string when the Order cannot be loaded.
     */
    function woo_ce_get_fooevents_ticket_sent( $order_id ) {

        static $ticket_sent_cache = array();

        if ( empty( $order_id ) ) {
            return '';
        }

        $cache_key = get_current_blog_id() . ':' . $order_id;

        if ( isset( $ticket_sent_cache[ $cache_key ] ) ) {
            return $ticket_sent_cache[ $cache_key ];
        }

        $ticket_sent = '';
        $order       = wc_get_order( $order_id );
        if ( ! empty( $order ) ) {
            $ticket_sent = $order->get_meta( 'WooCommerceEventsTicketsSent', true );

            // The releases that preceded FooEvents recorded the flag under a different key.
            // Anything that is not a scalar is no answer either, and would otherwise block
            // the fallback and then be thrown away below.
            if ( ! is_scalar( $ticket_sent ) || '' === $ticket_sent ) {
                $ticket_sent = $order->get_meta( 'WooCommerceEventsSentTicket', true );
            }
        }

        $ticket_sent = ( is_scalar( $ticket_sent ) ? (string) $ticket_sent : '' );

        // woo_ce_format_switch() answers "Yes" to 1|y|yes|on|open|active and "No" to
        // everything else, so a flag stored as the word "true" would export as "No".
        // FooEvents writes that shape of flag elsewhere in its own meta, and widening the
        // shared formatter would change every other export type, so the two words are
        // mapped here and nothing else is touched.
        $vocabulary = strtolower( trim( $ticket_sent ) );
        if ( 'true' === $vocabulary ) {
            $ticket_sent = '1';
        } elseif ( 'false' === $vocabulary ) {
            $ticket_sent = '0';
        }

        $ticket_sent_cache[ $cache_key ] = $ticket_sent;

        return $ticket_sent_cache[ $cache_key ];
    }
}

function woo_ce_extend_ticket_post_type( $post_type = '' ) {

	if( woo_ce_detect_export_plugin( 'tickera' ) )
		$post_type = 'tc_tickets_instances';

	return $post_type;

}
if( woo_ce_detect_tickets() )
	add_filter( 'woo_ce_ticket_post_type', 'woo_ce_extend_ticket_post_type' );
?>