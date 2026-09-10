<?php
/**
 * Author: Rymera Web Co.
 *
 * @package VisserLabs\WSE\Helpers
 */

namespace VisserLabs\WSE\Helpers;

use ActionScheduler;
use WC_DateTime;
use DateTimeZone;
use VisserLabs\WSE\Helpers\Helper;
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Stripe class.
 *
 * @since 2.7.3
 */
class Export {

    /**
     * Sentinel returned by resolve_coupon_codes() when an *enabled* Coupon filter resolves to
     * nothing.
     *
     * A negative value is used deliberately: the residue a *disabled* Coupon filter leaves behind
     * is built with absint() (see woo_ce_extend_order_dataset_args() in order-extend.php), which can
     * never produce a negative number, so "enabled but unresolvable" is always distinguishable from
     * "disabled". Matched set-wise against woocommerce_order_items.order_item_name, `IN ('-1')`
     * matches no order, so the export correctly returns nothing rather than silently widening to
     * every order.
     *
     * @since 2.8.1
     *
     * @var int
     */
    const NO_MATCH = -1;

    /**
     * Get export type count.
     *
     * @since 2.7.3
     *
     * @return array
     */
    public static function get_export_type_count() {
        global $wpdb;
        $cache = get_transient( WOO_CE_PREFIX . '_export_type_count' );
        if ( false === $cache ) {
            $query            = '';
            $sql_result_order = array(
                'product'        => 0,
                'category'       => 0,
                'tag'            => 0,
                'order'          => 0,
                'customer'       => 0,
                'user'           => 0,
                'review'         => 0,
                'coupon'         => 0,
                'attribute'      => 0,
                'shipping_class' => 0,
            );

            $values = array();

            // Product.
            $query   .= "SELECT COUNT(*) AS count FROM {$wpdb->posts} WHERE post_type = %s OR post_type = %s UNION ALL ";
            $values[] = 'product';
            $values[] = 'product_variation';

            // Product Categories.
            $query   .= "SELECT COUNT(*) AS count FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s UNION ALL ";
            $values[] = 'product_cat';

            // Product Tags.
            $query   .= "SELECT COUNT(*) AS count FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s UNION ALL ";
            $values[] = 'product_tag';

            // Orders.
            if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
                $query   .= "SELECT COUNT(*) AS count FROM {$wpdb->prefix}wc_orders WHERE type = %s UNION ALL ";
                $values[] = 'shop_order';
            } else {
                $query   .= "SELECT COUNT(*) AS count FROM {$wpdb->posts} WHERE post_type = %s UNION ALL ";
                $values[] = 'shop_order';
            }

            // Customers.
            if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
                $query   .= "SELECT COUNT(DISTINCT cl.customer_id) AS count
                    FROM {$wpdb->prefix}wc_customer_lookup AS cl
                        INNER JOIN {$wpdb->prefix}wc_order_stats AS os ON os.customer_id = cl.customer_id
                        INNER JOIN {$wpdb->prefix}wc_orders AS o ON os.order_id = o.id
                    WHERE o.type = %s UNION ALL ";
                $values[] = 'shop_order';
            } else {
                $query   .= "SELECT COUNT(DISTINCT cl.customer_id) AS count
                    FROM {$wpdb->prefix}wc_customer_lookup AS cl
                        INNER JOIN {$wpdb->prefix}wc_order_stats AS os ON os.customer_id = cl.customer_id
                        INNER JOIN {$wpdb->posts} AS p ON os.order_id = p.ID
                    WHERE p.post_type = %s UNION ALL ";
                $values[] = 'shop_order';
            }

            // Users.
            $query .= "SELECT COUNT(*) AS count FROM {$wpdb->users} UNION ALL ";

            // Reviews.
            $query   .= "SELECT COUNT(*) AS count FROM {$wpdb->comments} WHERE comment_type = %s UNION ALL ";
            $values[] = 'review';

            // Coupons.
            $query   .= "SELECT COUNT(*) AS count FROM {$wpdb->posts} WHERE post_type = %s UNION ALL ";
            $values[] = 'shop_coupon';

            // Attributes.
            $query .= "SELECT COUNT(*) AS count FROM {$wpdb->prefix}woocommerce_attribute_taxonomies UNION ALL ";

            // Shipping Class.
            $query   .= "SELECT COUNT(*) AS count FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s";
            $values[] = 'product_shipping_class';

            $result = $wpdb->get_results( $wpdb->prepare( $query, $values ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

            // Set the count based on the order of the SQL query.
            $count = array();
            foreach ( $result as $key => $value ) {
                $count[ key( $sql_result_order ) ] = absint( $value['count'] );
                next( $sql_result_order );
            }

            /**
             * Filter the export type count.
             *
             * @since 2.7.3
             *
             * @param array $count The export type count.
             * @return array
             */
            $count = apply_filters( 'wsed_export_type_count', $count );

            set_transient( WOO_CE_PREFIX . '_export_type_count', $count, HOUR_IN_SECONDS );
        } else {
            $count = $cache;
        }
        return $count;
    }

    /**
     * Sort export fields.
     *
     * @param array  $fields The fields to sort.
     * @param string $key The key to sort by.
     *
     * @since 2.7.3
     * @return array
     */
    public static function sort_export_fields( $fields, $key ) {
        usort(
            $fields,
            function ( $a, $b ) use ( $key ) {
                return strnatcmp( $a[ $key ], $b[ $key ] );
            }
        );
        return $fields;
    }

    /**
     * Get date filter.
     *
     * @param string $filter      The filter to apply.
     * @param array  $args        The arguments.
     *                            Example: array( 'from' => '2021-01-01', 'to' => '2021-01-31', 'variable' => '1', 'variable_length' => 'month' ).
     * @param string $returns     The return type. Default 'string'. Accepts 'string' or 'timestamp'.
     * @param string $format      The date format. Default 'Y-m-d H:i:s'.
     *
     * @since 2.7.3
     * @return array
     */
    public static function get_date_filter( $filter, $args = array(), $returns = 'string', $format = 'Y-m-d H:i:s' ) {
        $from     = null;
        $to       = null;
        $timezone = new DateTimeZone( wc_timezone_string() );
        switch ( $filter ) {
            case 'tomorrow':
                $from = new WC_DateTime( '+1 day', $timezone );
                $to   = new WC_DateTime( '+2 day', $timezone );
                break;
            case 'today':
                $from = new WC_DateTime( 'now', $timezone );
                $to   = new WC_DateTime( '+1 day', $timezone );
                break;
            case 'yesterday':
                $from = new WC_DateTime( '-1 day', $timezone );
                $to   = new WC_DateTime( 'now', $timezone );
                break;
            case 'current_week':
                $from = new WC_DateTime( 'monday this week', $timezone );
                $to   = new WC_DateTime( 'sunday this week', $timezone );
                break;
            case 'last_week':
                $from = new WC_DateTime( 'monday last week', $timezone );
                $to   = new WC_DateTime( 'sunday last week', $timezone );
                break;
            case 'current_month':
                $from = new WC_DateTime( 'first day of this month', $timezone );
                $to   = new WC_DateTime( 'last day of this month', $timezone );
                break;
            case 'last_month':
                $from = new WC_DateTime( 'first day of last month', $timezone );
                $to   = new WC_DateTime( 'last day of last month', $timezone );
                break;
            case 'current_year':
                $from = new WC_DateTime( 'first day of january this year', $timezone );
                $to   = new WC_DateTime( 'last day of december this year', $timezone );
                break;
            case 'last_year':
                $from = new WC_DateTime( 'first day of january last year', $timezone );
                $to   = new WC_DateTime( 'last day of december last year', $timezone );
                break;
            case 'specific':
                // The SPA quick export date picker submits an ISO (Y-m-d) custom range.
                if ( ! empty( $args['from'] ) && ! empty( $args['to'] ) ) {
                    list( $from_str, $to_str ) = self::parse_iso_range( $args['from'], $args['to'], 'Y-m-d 00:00:00', 'Y-m-d 23:59:59' );
                    if ( false !== $from_str && false !== $to_str ) {
                        $from = new WC_DateTime( $from_str, $timezone );
                        $to   = new WC_DateTime( $to_str, $timezone );
                    }
                }
                break;
            case 'manual':
                if ( isset( $args['from'] ) && isset( $args['to'] ) ) {
                    $from = new WC_DateTime( $args['from'], $timezone );
                    $to   = new WC_DateTime( $args['to'], $timezone );
                }
                break;
            case 'variable':
                $variable = $args['variable'] ?? null;
                $length   = $args['variable_length'] ?? null;

                if ( $variable && $length ) {
                    $from = new WC_DateTime( "-{$variable} {$length}", $timezone );
                    $to   = new WC_DateTime( 'now', $timezone );
                    break;
                }
                break;
        }

        if ( null !== $from && null !== $to ) {

            // Always start from the beginning of the day.
            // For manual, variable and specific filters, we don't want to set the time to 00:00:00.
            if ( ! in_array( $filter, array( 'manual', 'variable', 'specific' ), true ) ) {
                $from->setTime( 0, 0, 0 );
                $to->setTime( 0, 0, 0 );
            }

            switch ( $returns ) {
                case 'timestamp':
                    $from = $from->getTimestamp();
                    $to   = $to->getTimestamp();
                    break;
                case 'string':
                    $from = $from->date( $format );
                    $to   = $to->date( $format );
                    break;
                case 'object':
                default:
                    break;
            }
        }

        return array( $from, $to );
    }

    /**
     * Parse an ISO (Y-m-d) date range and reformat each bound.
     *
     * The SPA quick export date picker submits custom ranges as ISO (Y-m-d)
     * strings. Legacy per-type readers reformat those bounds to the shape their
     * downstream query expects (full-day 'Y-m-d H:i:s' strings, or a bare
     * 'd-m-Y' string that is later exploded into a date_query). This centralises
     * the parse-and-reformat used by every 'specific' date-filter branch.
     *
     * Each bound is parsed strictly, so a bound that is not already canonical ISO
     * yields false rather than a plausible-looking wrong date. See
     * `parse_iso_date()`.
     *
     * @since 2.8.1
     *
     * @param string $from        ISO (Y-m-d) lower bound. Empty or non-canonical ISO yields false.
     * @param string $to          ISO (Y-m-d) upper bound. Empty or non-canonical ISO yields false.
     * @param string $from_format Output format for the lower bound.
     * @param string $to_format   Output format for the upper bound. Defaults to $from_format.
     * @return array Two-element list: array( string|false $from, string|false $to ).
     */
    public static function parse_iso_range( $from, $to, $from_format, $to_format = null ) {
        $to_format = $to_format ?? $from_format;
        $from_obj  = self::parse_iso_date( $from );
        $to_obj    = self::parse_iso_date( $to );
        return array(
            ( null !== $from_obj ) ? $from_obj->format( $from_format ) : false,
            ( null !== $to_obj ) ? $to_obj->format( $to_format ) : false,
        );
    }

    /**
     * Strictly parse a canonical ISO (Y-m-d) date bound.
     *
     * `DateTime` is lenient about out-of-range components — it overflows them, so
     * '2024-13-45' silently becomes 14 Feb 2025 and the export would be filtered
     * by a date the caller never asked for. The round-trip comparison rejects
     * anything that is not already canonical ISO.
     *
     * @since 2.8.1
     *
     * @param string $iso_date Date bound in ISO `Y-m-d`.
     * @return \DateTime|null Parsed date, or null when the input is not canonical ISO.
     */
    private static function parse_iso_date( $iso_date ) {
        $iso_date = (string) $iso_date;
        $parsed   = \DateTime::createFromFormat( '!Y-m-d', $iso_date );

        if ( ! $parsed instanceof \DateTime || $parsed->format( 'Y-m-d' ) !== $iso_date ) {
            return null;
        }

        return $parsed;
    }

    /**
     * Check if a string should be exported as CDATA.
     *
     * @param string $str         The string to check.
     * @param string $export_type The export type.
     * @param string $field       The field.
     *
     * @since 2.7.3
     * @return bool
     */
    public static function is_xml_cdata( $str = '', $export_type = '', $field = '' ) {
        if ( null === $str ) {
            return;
        }

        // Force these fields to export as CDATA.
        if ( ! empty( $export_type ) && ! empty( $field ) ) {
            if ( 'product' === $export_type && 'category' === $field ) {
                return true;
            }
        }

        if ( strlen( $str ) === 0 ) {
            return;
        }
        if ( ( ! empty( $str ) && seems_utf8( trim( $str ) ) === false ) || preg_match( '!.!u', trim( $str ) ) === false || strpos( $str, '&nbsp;' ) !== false ) {
            return true;
        }
    }

    /**
     * Sanitize XML string.
     *
     * @param string $str The string to sanitize.
     * @param string $encoding The encoding.
     *
     * @since 2.7.3
     * @return string
     */
    public static function sanitize_xml_string( $str = '', $encoding = '' ) {
        if ( null === $str ) {
            return '';
        }

        $str = str_replace( array( "\r", PHP_EOL ), '', $str );
        $str = preg_replace( '/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $str );
        if ( function_exists( 'mb_convert_encoding' ) ) {
            $to_encoding   = $encoding;
            $from_encoding = 'auto';
            if ( ! empty( $to_encoding ) ) {
                $str = mb_convert_encoding( trim( $str ), $to_encoding, $from_encoding );
            }
            if ( 'UTF-8' !== $to_encoding ) {
                if ( function_exists( 'utf8_encode' ) ) {
                    $str = mb_convert_encoding( $str, 'UTF-8' );
                }
            }
        }
        return htmlspecialchars( $str );
    }

    /**
     * Clean export label.
     *
     * @param string $label The label to clean.
     *
     * @since 2.7.3
     * @return string
     */
    public static function clean_export_label( $label ) {
        // If the first character is an underscore remove it.
        if ( ! empty( $label ) && '_' === $label[0] ) {
            $label = substr( $label, 1 );
        }
        // Replace any underscores and dashes with spaces.
        $label = str_replace( array( '_', '-' ), ' ', $label );
        // Auto-capitalise label.
        $label = ucfirst( $label );

        return $label;
    }

    /**
     * Parse the dataset.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array $dataset The dataset to parse.
     * @param array $columns The columns to parse.
     * @return array
     */
    public static function parse_dataset( $dataset, $columns ) {
        // Set the default data.
        $default_data = array();
        foreach ( $columns as $name => $label ) {
            if ( is_array( $label ) ) {
                $default_data[ $name ] = array();
                foreach ( $label as $child_name => $child_label ) {
                    $default_data[ $name ][ $child_name ] = '';
                }
            } else {
                $default_data[ $name ] = '';
            }
        }

        foreach ( $dataset as $i => $data ) {
            // Check if data is an object.
            if ( is_object( $data ) ) {
                $data_array = array();
                foreach ( $data as $key => $value ) {
                    $data_array[ $key ] = $value;
                }
                $data = $data_array;
            }

            // Remove array keys that are not in the default data.
            $data = array_intersect_key( $data, $default_data );

            // Parse the default data to the dataset.
            $dataset[ $i ] = wp_parse_args( $data, $default_data );
        }

        return $dataset;
    }

    /**
     * Get action scheduler id.
     *
     * @param int $post_id The post ID.
     *
     * @since 2.7.3
     * @return string
     */
    public static function get_action_id( $post_id ) {
        return ActionScheduler::store()->find_action(
            WSED_AS_HOOK,
            array(
                'args'   => array( 'id' => $post_id ),
                'status' => '',
                'order'  => 'DESC',
            ),
            WSED_AS_GROUP
        );
    }

    /**
     * Get action scheduler status.
     *
     * @param int $post_id The post ID.
     *
     * @since 2.7.3
     * @return string
     */
    public static function get_action_status( $post_id ) {
        $action_id = self::get_action_id( $post_id );

        if ( null === $action_id ) {
            return 'not-scheduled';
        }

        return ActionScheduler::store()->get_status( $action_id );
    }

    /**
     * Get action scheduler status.
     *
     * @since 2.7.3
     *
     * @param int           $post_id The post ID.
     * @param string|object $returns The return type. Default 'date'. Accepts 'date' or 'string'.
     *
     *  @return string
     */
    public static function get_scheduled_date( $post_id, $returns = 'date' ) {
        $action_id = self::get_action_id( $post_id );

        if ( null === $action_id ) {
            return '';
        }

        $action = ActionScheduler::store()->fetch_action( $action_id );
        $date   = $action->get_schedule()->get_date();
        if ( $date ) {
            if ( 'string' === $returns ) {
                // Get the timezone.
                $timezone = wc_timezone_string();
                $date->setTimezone( new \DateTimeZone( $timezone ) );
                $output = $date->format( wc_date_format() . ' ' . wc_time_format() );
            } else {
                $output = $date;
            }
        } else {
            $output = '';
        }
        return $output;
    }

    /**
     * Check if there are any pending async scheduled actions due to run.
     *
     * @since 2.7.3
     *
     * @return string
     */
    public static function get_pending_async_actions_due() {
        $as_store        = ActionScheduler::store();
        $pending_actions = $as_store->query_actions(
            array(
                'hook'    => WSED_AS_HOOK,
                'group'   => WSED_AS_ASYNC_GROUP,
                'status'  => $as_store::STATUS_PENDING,
                'orderby' => 'date',
                'order'   => 'DESC',
            )
        );

        return ! empty( $pending_actions ) ? $pending_actions : false;
    }

    /**
     * Get unique filename.
     *
     * @since 2.7.3
     *
     * @param string $path     The path.
     * @param string $filename The filename.
     * @return string
     */
    public static function get_unique_filename( $path, $filename ) {
        return wp_unique_filename( $path, sanitize_file_name( $filename ) );
    }

    /**
     * Resolve a raw Coupon filter value to the list of coupon codes it names.
     *
     * Whether a digits-only value is read as an ID first or as a code first is a property of the
     * *control*, not of the value, so callers state it via $prefer rather than have it guessed:
     *
     * - Code controls (Quick Export SPA text input) submit a coupon *code*, and a code can be a
     *   bare number ("25", "2024"). These are used as codes directly, or a coupon coded "25" would
     *   be shadowed by coupon post 25.
     * - ID controls (classic multiselect, the free-text field above the coupon-list threshold that
     *   submits a comma-separated ID list) submit coupon *IDs*, which are converted to the coupon's
     *   current code. A digits-only value naming a real shop_coupon post is read as that ID first,
     *   so it cannot be shadowed by a coupon whose *code* is the same number.
     *
     * Comma-separated values are split first, so the ID-list free-text fields resolve each entry
     * independently. Every resulting code passes through wc_format_coupon_code(), which applies
     * wc_sanitize_coupon_code() and wc_strtolower() — the same normalisation WooCommerce applies
     * before storing order_item_name, so a code typed in any case still matches.
     *
     * @since 2.8.1
     *
     * @param mixed  $raw               Raw filter value: a coupon code, a coupon ID, a comma-separated
     *                                  list of either, or an array of any of those.
     * @param bool   $no_match_sentinel Whether to return array( self::NO_MATCH ) when nothing resolves.
     *                                  Callers acting on an *enabled* filter want this: NO_MATCH is a
     *                                  value no recorded coupon code will equal, so the export
     *                                  correctly matches nothing, whereas an empty array reads as
     *                                  "no filter" and would silently widen the export to every
     *                                  order. Default false.
     * @param string $prefer            'code' to read digits as a code first, 'id' to read them as an
     *                                  ID first, or 'auto' (default) to infer from the value's shape:
     *                                  an array came from an ID control, a bare string from a code
     *                                  control.
     * @return array List of coupon codes. Empty when nothing resolved and $no_match_sentinel
     *               is false.
     */
    public static function resolve_coupon_codes( $raw, $no_match_sentinel = false, $prefer = 'auto' ) {
        $prefer_id    = ( 'id' === $prefer ) || ( 'auto' === $prefer && is_array( $raw ) );
        $coupon_codes = array();

        foreach ( (array) $raw as $entry ) {
            // The ID-list free-text fields submit a comma-separated string; resolve each entry on
            // its own. A coupon code containing a comma is possible but vanishingly rare, and the
            // ID-list shape is by far the more likely input here.
            foreach ( array_filter( array_map( 'trim', explode( ',', (string) $entry ) ), 'strlen' ) as $coupon ) {
                $coupon = sanitize_text_field( $coupon );

                // ID control: a digits-only value is a coupon ID, so it contributes that coupon's
                // current code rather than a same-numbered code. An ID naming no coupon post — a
                // coupon deleted since the filter was saved — yields no code and is dropped, not
                // reinterpreted as a code, which could otherwise match an unrelated coupon that
                // happens to be coded with the same digits. Such orders can still be exported by
                // filtering on the code itself.
                if ( $prefer_id && ctype_digit( $coupon ) ) {
                    if ( 'shop_coupon' === get_post_type( absint( $coupon ) ) ) {
                        $coupon_codes[] = wc_get_coupon_code_by_id( absint( $coupon ) );
                    }
                    continue;
                }

                $coupon_codes[] = $coupon;
            }
        }

        $coupon_codes = array_map( 'wc_format_coupon_code', $coupon_codes );
        $coupon_codes = array_values( array_unique( array_filter( $coupon_codes, 'strlen' ) ) );

        if ( empty( $coupon_codes ) && $no_match_sentinel ) {
            return array( self::NO_MATCH );
        }

        return $coupon_codes;
    }

    /**
     * Sanitize multiple ID input.
     *
     * This function will sanitize a string of comma separated IDs and ranges into an array of integers.
     * For example, '1,2,3,4,5-10' will be converted to [1, 2, 3, 4, 5, 6, 7, 8, 9, 10].
     * This function will also remove any duplicates and empty values.
     *
     * @since 2.7.3
     *
     * @param string $input The input.
     * @return array
     */
    public static function sanitize_multiple_id_input( $input ) {
        // Explode the input by comma.
        $input = array_map( 'trim', explode( ',', sanitize_text_field( $input ) ) );

        // Check if the input contains a range.
        foreach ( $input as $key => $value ) {
            if ( strpos( $value, '-' ) !== false ) {
                $range = array_map( 'trim', explode( '-', $value ) );
                $input = array_merge( $input, range( $range[0], $range[1] ) );
                unset( $input[ $key ] );
            } else {
                $input[ $key ] = absint( $value );
            }
        }

        // Remove duplicates and empty values.
        return array_unique( array_filter( $input ) );
    }

    /***************************************************************************
     * Email
     * **************************************************************************
     */

    /**
     * Get email headers.
     *
     * @since 2.7.3
     *
     * @param string $cc  The CC email address.
     * @param string $bcc The BCC email address.
     * @return array
     */
    public static function get_email_headers( $cc, $bcc ) {
        $headers = array();

        // From.
        $from_name = apply_filters( 'wsed_email_from_name', get_bloginfo( 'name' ) );

        // CC.
        if ( ! empty( $cc ) ) {
            $headers[] = apply_filters( 'wsed_email_cc', 'Cc: ' . $cc );
        }

        // BCC.
        if ( ! empty( $bcc ) ) {
            $headers[] = apply_filters( 'wsed_email_bcc', 'Bcc: ' . $bcc );
        }

        return apply_filters( 'wsed_email_headers', $headers, $cc, $bcc );
    }

    /**
     * Get email subject.
     *
     * @since 2.7.3
     *
     * @param string $email_subject The email subject.
     * @param string $filename      The filename.
     * @param string $export_type   The export type.
     * @return string
     */
    public static function get_email_subject( $email_subject, $filename, $export_type ) {
        // Default subject.
        if ( empty( $email_subject ) ) {
            // translators: 1: store name, 2: export type, 3: export filename.
            $email_subject = apply_filters( 'wsed_default_email_subject', __( '[%store_name%] Export: %export_type% (%export_filename%)', 'woocommerce-exporter' ), $filename, $export_type ); // phpcs:ignore
        }

        $email_subject = str_replace( '%store_name%', sanitize_title( get_bloginfo( 'name' ) ), $email_subject );
        $email_subject = str_replace( '%export_type%', ucwords( $export_type ), $email_subject );
        $email_subject = str_replace( '%export_filename%', $filename, $email_subject );

        return apply_filters( 'wsed_email_subject', $email_subject, $filename, $export_type );
    }

    /**
     * Get email heading.
     *
     * @since 2.7.3
     *
     * @param string $email_heading The email heading.
     * @param string $filename      The filename.
     * @param string $export_type   The export type.
     * @return string
     */
    public static function get_email_heading( $email_heading, $filename, $export_type ) {
        // Default heading.
        if ( empty( $email_body ) ) {
            // translators: 1: export type, 2: export filename.
            $email_heading = apply_filters( 'wsed_default_email_heading', __( 'Export: %export_type% (%export_filename%)', 'woocommerce-exporter' ), $filename, $export_type ); // phpcs:ignore
        }

        $email_heading = str_replace( '%store_name%', sanitize_title( get_bloginfo( 'name' ) ), $email_heading );
        $email_heading = str_replace( '%export_type%', ucwords( $export_type ), $email_heading );
        $email_heading = str_replace( '%export_filename%', $filename, $email_heading );

        return apply_filters( 'wsed_email_heading', $email_heading, $filename, $export_type );
    }

    /**
     * Get email contents.
     *
     * @since 2.7.3
     *
     * @param string $email_contents The email body.
     * @param string $filename       The filename.
     * @param string $export_type    The export type.
     * @return string
     */
    public static function get_email_contents( $email_contents, $filename, $export_type ) {
        // Default contents.
        if ( empty( $email_contents ) ) {
            $email_contents = apply_filters( 'wsed_default_email_contents', wpautop( __( 'Please find attached your export ready to review.', 'woocommerce-exporter' ) ), $filename, $export_type );
        }

        $email_contents = str_replace( '%store_name%', sanitize_title( get_bloginfo( 'name' ) ), $email_contents );
        $email_contents = str_replace( '%export_type%', ucwords( $export_type ), $email_contents );
        $email_contents = str_replace( '%export_filename%', $filename, $email_contents );

        return apply_filters( 'wsed_email_contents', $email_contents, $filename, $export_type );
    }

    /**
     * Get email body.
     *
     * @since 2.7.3
     *
     * @param string    $email_heading  The email heading.
     * @param string    $email_contents The email body.
     * @param WC_Emails $email          The WC_Emails  object.
     * @param string    $export_type    The export type.
     * @return string
     */
    public static function get_email_body( $email_heading, $email_contents, $email, $export_type ) {
        // Buffer.
        $email_body = ob_start();

        // Load the email template.
        Helper::load_template(
            'emails/scheduled-export.php',
            array(
                'email_heading'  => $email_heading,
                'email_contents' => $email_contents,
                'email'          => $email,
            ),
            WOO_CE_PATH . 'templates/'
        );

        $email_body = ob_get_clean();

        return apply_filters( 'wsed_email_body', $email_body, $email_heading, $email, $email_contents, $export_type );
    }

    /**
     * Switch between FTP_ASCII or FTP_BINARY.
     *
     * @since 2.7.3
     *
     * @param string $ftp_mode The FTP mode.
     * @return string
     */
    public static function get_ftp_mode( $ftp_mode ) {
        switch ( $ftp_mode ) {
            default:
            case 'ASCII':
                $ftp_mode = FTP_ASCII;
                break;
            case 'BINARY':
                $ftp_mode = FTP_BINARY;
                break;
        }
        return apply_filters( 'wsed_export_ftp_mode', $ftp_mode );
    }

    /**
     * Load export files.
     *
     * This is the old way of loading the export files.
     * this helper is used on scheduled exports, because the function is called from the old way, for export types that has not been refactored.
     * Question is why are we load them all instead of loading based on export types (e.g product then load product.php, product-extend.php)?
     * Because I'm not quite sure where is where and what is what.
     * So, I'm loading them all, like the old way.
     * Sometimes (a lot of times actually) when exporting product the function is in subscription.php and which ever it is.
     * So, good luck to trace it.
     *
     * @since 2.7.3
     * @access public
     */
    public static function load_export_files() {
        include_once WOO_CE_PATH . 'includes/product.php';
        include_once WOO_CE_PATH . 'includes/product-extend.php';
        include_once WOO_CE_PATH . 'includes/category.php';
        include_once WOO_CE_PATH . 'includes/category-extend.php';
        include_once WOO_CE_PATH . 'includes/tag.php';
        include_once WOO_CE_PATH . 'includes/tag-extend.php';
        include_once WOO_CE_PATH . 'includes/brand.php';
        include_once WOO_CE_PATH . 'includes/brand-extend.php';
        include_once WOO_CE_PATH . 'includes/order.php';
        include_once WOO_CE_PATH . 'includes/order-extend.php';
        include_once WOO_CE_PATH . 'includes/order-combined-extend.php';
        include_once WOO_CE_PATH . 'includes/order-individual.php';
        include_once WOO_CE_PATH . 'includes/order-individual-extend.php';
        include_once WOO_CE_PATH . 'includes/order-unique.php';
        include_once WOO_CE_PATH . 'includes/order-unique-extend.php';
        include_once WOO_CE_PATH . 'includes/customer.php';
        include_once WOO_CE_PATH . 'includes/customer-extend.php';
        include_once WOO_CE_PATH . 'includes/user.php';
        include_once WOO_CE_PATH . 'includes/user-extend.php';
        include_once WOO_CE_PATH . 'includes/review.php';
        include_once WOO_CE_PATH . 'includes/review-extend.php';
        include_once WOO_CE_PATH . 'includes/coupon.php';
        include_once WOO_CE_PATH . 'includes/coupon-extend.php';
        include_once WOO_CE_PATH . 'includes/subscription.php';
        include_once WOO_CE_PATH . 'includes/subscription-extend.php';
        include_once WOO_CE_PATH . 'includes/product_vendor.php';
        include_once WOO_CE_PATH . 'includes/product_vendor-extend.php';
        include_once WOO_CE_PATH . 'includes/commission.php';
        include_once WOO_CE_PATH . 'includes/shipping_class.php';
        include_once WOO_CE_PATH . 'includes/shipping_class-extend.php';
        include_once WOO_CE_PATH . 'includes/ticket.php';
        include_once WOO_CE_PATH . 'includes/ticket-extend.php';
        include_once WOO_CE_PATH . 'includes/attribute.php';
        include_once WOO_CE_PATH . 'includes/booking.php';

        // Load the export type resources first.
        if ( is_admin() ) {
            include_once WOO_CE_PATH . 'includes/admin/product.php';
            include_once WOO_CE_PATH . 'includes/admin/product-extend.php';
            include_once WOO_CE_PATH . 'includes/admin/category.php';
            include_once WOO_CE_PATH . 'includes/admin/tag.php';
            include_once WOO_CE_PATH . 'includes/admin/brand.php';
            include_once WOO_CE_PATH . 'includes/admin/order.php';
            include_once WOO_CE_PATH . 'includes/admin/order-extend.php';
            include_once WOO_CE_PATH . 'includes/admin/customer.php';
            include_once WOO_CE_PATH . 'includes/admin/user.php';
            include_once WOO_CE_PATH . 'includes/admin/review.php';
            include_once WOO_CE_PATH . 'includes/admin/coupon.php';
            include_once WOO_CE_PATH . 'includes/admin/subscription.php';
            include_once WOO_CE_PATH . 'includes/admin/product_vendor.php';
            include_once WOO_CE_PATH . 'includes/admin/commission.php';
            include_once WOO_CE_PATH . 'includes/admin/shipping_class.php';
            include_once WOO_CE_PATH . 'includes/admin/ticket.php';
            include_once WOO_CE_PATH . 'includes/admin/booking.php';
        }
    }
}
