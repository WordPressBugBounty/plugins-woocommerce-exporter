<?php
/**
 * Author: Rymera Web Co.
 *
 * @package VisserLabs\WSE\Helpers
 */

namespace VisserLabs\WSE\Helpers;

use DateTime;
use WC_DateTime;
use WC_Countries;

/**
 * Stripe class.
 *
 * @since 2.7.3
 */
class Formatting {

    /**
     * Get the state name from the country code and state code.
     *
     * @param string $country_code Country code.
     * @param string $state_code State code.
     * @return string|null
     */
    public static function state_name( $country_code, $state_code ) {
        $countries = new WC_Countries();
        $states    = $countries->get_states( $country_code );

        if ( isset( $states[ $state_code ] ) ) {
            return $states[ $state_code ];
        } else {
            return null;
        }
    }

    /**
     * Get the country name from the country code.
     *
     * @param string $country_code Country code.
     * @return string|null
     */
    public static function country_name( $country_code ) {
        $countries = new WC_Countries();
        $countries = $countries->get_countries();

        if ( isset( $countries[ $country_code ] ) ) {
            return $countries[ $country_code ];
        } else {
            return null;
        }
    }

    /**
     * Format a date.
     *
     * @param string|int|WC_DateTime $date Date.
     * @param string                 $format Date format.
     * @param bool                   $time Whether to include time.
     * @return string
     */
    public static function format_date( $date, $format = '', $time = false ) {
        if ( ! $date ) {
            return '';
        }

        // If string is passed, convert to WC_DateTime.
        // If timestamp is passed, convert to WC_DateTime.
        $date = is_numeric( $date ) ? new WC_DateTime( '@' . $date ) : new WC_DateTime( $date );

        $format = empty( $format ) ? get_option( WOO_CE_PREFIX . '_date_format', 'Y-m-d H:i:s' ) : $format;
        $format = $time ? $format . ' ' . wc_time_format() : $format;

        if ( $time ) {
            $format = $format . ' ' . wc_time_format();
        }

        if ( $date instanceof WC_DateTime ) {
            return $date->format( $format );
        } else {
            return '';
        }
    }

    /**
     * Format a date.
     *
     * @param string $date_string Date string.
     * @return string
     */
    public static function sanitize_date( $date_string ) {
        if ( '' === $date_string ) {
            return '';
        }

        $date_format = get_option( WOO_CE_PREFIX . '_date_format', 'd/m/Y' );

        // Replace pipe (|) with slash (/).
        if ( strpos( $date_string, '|' ) !== false ) {
            $date_string = str_replace( '|', '/', $date_string );
        }
        if ( strpos( $date_format, '|' ) !== false ) {
            $date_format = str_replace( '|', '/', $date_format );
        }

        $date = DateTime::createFromFormat( $date_format, sanitize_text_field( $date_string ) );

        if ( $date instanceof DateTime ) {
            return $date->format( 'Y-m-d' );
        } else {
            return '';
        }
    }

    /**
     * Format a price without HTML wrapper & currency symbol.
     *
     * @param float $price Price.
     * @return string
     */
    public static function format_price( $price ) {
        if ( ! $price ) {
            return '';
        }
        $formatted_price = wc_format_localized_price( $price );

        return apply_filters( 'wsed_format_price_output', $formatted_price, $price );
    }

    /**
     * Format the order item type.
     *
     * @param string $type The order item type.
     * @return string
     */
    public static function format_order_item_type( $type = '' ) {
        switch ( $type ) {
            case 'line_item':
                $output = __( 'Product', 'woocommerce-exporter' );
                break;
            case 'fee':
                $output = __( 'Fee', 'woocommerce-exporter' );
                break;
            case 'shipping':
                $output = __( 'Shipping', 'woocommerce-exporter' );
                break;
            case 'tax':
                $output = __( 'Tax', 'woocommerce-exporter' );
                break;
            case 'coupon':
                $output = __( 'Coupon', 'woocommerce-exporter' );
                break;
            case 'shop_order_refund':
                $output = __( 'Refund', 'woocommerce-exporter' );
                break;
            default:
                $output = $type;
                break;

        }
        return $output;
    }

    /**
     * Format a switch.
     *
     * @param string $input         The input.
     * @param string $output_format The output format.
     * @return string
     */
    public static function format_switch( $input = '', $output_format = 'answer' ) {
        $input = strtolower( $input );
        switch ( $input ) {
            case '1':
            case 'y':
            case 'yes':
            case 'on':
            case 'open':
            case 'active':
                $input = '1';
                break;
            case '0':
            case 'n':
            case 'no':
            case 'off':
            case 'closed':
            case 'inactive':
            default:
                $input = '0';
                break;
        }

        $output = '';
        switch ( $output_format ) {
            case 'int':
                $output = $input;
                break;
            case 'answer':
                $output = '1' === $input ? __( 'Yes', 'woocommerce-exporter' ) : __( 'No', 'woocommerce-exporter' );
                break;
            case 'boolean':
                $output = '1' === $input ? 'on' : 'off';
                break;
        }
        return $output;
    }

    /**
     * Sanitize emails.
     *
     * @since 2.7.3
     *
     * @param string $emails The string of emails.
     * @return string
     */
    public static function sanitize_emails( $emails ) {
        if ( '' === $emails ) {
            return '';
        }

        // Check for semicolons and replace as necessary.
        $emails = str_replace( ';', ',', $emails );
        $emails = explode( ',', $emails );
        $emails = array_map( 'sanitize_email', $emails );
        $emails = array_filter( $emails );
        return implode( ',', $emails );
    }

    /**
     * Remove file extension.
     *
     * @since 2.7.3
     *
     * @param string $filename The filename.
     * @return string
     */
    public static function remove_file_extension( $filename ) {
        return str_replace( array( '.csv', '.tsv', '.txt', '.xls', '.xlsx', '.xml', '.rss' ), '', $filename );
    }

    /***************************************************************************
     * FTP
     * **************************************************************************
     */

    /**
     * Format FTP host.
     *
     * @since 2.7.3
     * @param string $host The host.
     * @return string
     */
    public static function format_ftp_host( $host ) {
        // Strip out the ftp:// or other protocols that may be entered.
        return str_replace( array( 'ftp://', 'ftps://', 'http://', 'https://' ), '', $host );
    }
}
