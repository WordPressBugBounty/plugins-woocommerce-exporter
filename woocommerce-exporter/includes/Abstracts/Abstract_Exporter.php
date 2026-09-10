<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE
 */

namespace VisserLabs\WSE\Abstracts;

use Exception;
use VisserLabs\WSE\Helpers\Helper;
use VisserLabs\WSE\Classes\Export\Product as Export_Product;
use VisserLabs\WSE\Classes\Export\Category as Export_Category;
use VisserLabs\WSE\Classes\Export\Tag as Export_Tag;
use VisserLabs\WSE\Classes\Export\Order as Export_Order;
use VisserLabs\WSE\Classes\Export\Customer as Export_Customer;
use VisserLabs\WSE\Classes\Export\User as Export_User;
use VisserLabs\WSE\Classes\Export\Review as Export_Review;
use VisserLabs\WSE\Classes\Export\Coupon as Export_Coupon;
use VisserLabs\WSE\Classes\Export\Attribute as Export_Attribute;
use VisserLabs\WSE\Classes\Export\Shipping_Class as Export_Shipping_Class;
use VisserLabs\WSE\Classes\Export\Subscription as Export_Subscription;
use VisserLabs\WSE\Classes\Export\Booking as Export_Booking;
use VisserLabs\WSE\Classes\Export\Brand as Export_Brand;
use VisserLabs\WSE\Classes\Export\Product_Vendor as Export_Product_Vendor;
use VisserLabs\WSE\Classes\Export\Commission as Export_Commission;
use VisserLabs\WSE\Classes\Export\Ticket as Export_Ticket;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Class
 */
abstract class Abstract_Exporter {

    /**
     * The name of the export directory.
     *
     * @var string
     */
    const EXPORT_DIR_NAME = '/store-exporter';

    /**
     * The path to the export directory.
     *
     * @var string
     */
    const EXPORT_DIR = WP_CONTENT_DIR . self::EXPORT_DIR_NAME . '/';

    /**
     * Option storing the random slug appended to the archive sub-directory name.
     *
     * @since 2.8.1
     * @var string
     */
    const ARCHIVE_DIR_SLUG_OPTION = 'wse_archive_dir_slug';

    /**
     * Get the path to the archived export sub-directory (with a trailing slash).
     *
     * Archives live in their own sub-directory of the export directory so they
     * never collide with, or get unlinked alongside, the short-lived temp files
     * that {@see get_file_path()} writes directly into EXPORT_DIR.
     *
     * The directory name carries a random, per-site slug. EXPORT_DIR sits inside
     * the document root, and nginx, Caddy and OpenLiteSpeed ignore the deny-all
     * guard files {@see ensure_export_dir()} writes. On those servers a
     * predictable path is a credential-free download link; the slug makes
     * containment independent of server configuration.
     *
     * @since 2.8.1
     * @access public
     *
     * @return string
     */
    public static function get_archive_dir(): string {
        return self::EXPORT_DIR . 'archives-' . self::get_archive_dir_slug() . '/';
    }

    /**
     * Read the per-site archive directory slug, seeding it on first use.
     *
     * Uses add_option() instead of update_option() so two concurrent first
     * calls cannot each persist a different slug: the INSERT against the unique
     * option_name index lets only one row survive, and both callers re-read it.
     *
     * @since 2.8.1
     * @access private
     *
     * @return string A 32-character hexadecimal slug.
     */
    private static function get_archive_dir_slug(): string {
        $slug = get_option( self::ARCHIVE_DIR_SLUG_OPTION, '' );
        if ( self::is_archive_dir_slug( $slug ) ) {
            return $slug;
        }

        $candidate = md5( wp_generate_password( 64, false, false ) );
        add_option( self::ARCHIVE_DIR_SLUG_OPTION, $candidate, '', true );

        wp_cache_delete( self::ARCHIVE_DIR_SLUG_OPTION, 'options' );
        wp_cache_delete( 'alloptions', 'options' );

        $stored = get_option( self::ARCHIVE_DIR_SLUG_OPTION, '' );

        return self::is_archive_dir_slug( $stored ) ? $stored : $candidate;
    }

    /**
     * Whether a stored value is a well-formed archive directory slug.
     *
     * @since 2.8.1
     * @access private
     *
     * @param mixed $slug The stored option value.
     * @return bool
     */
    private static function is_archive_dir_slug( $slug ): bool {
        return is_string( $slug ) && (bool) preg_match( '/^[a-f0-9]{32}$/', $slug );
    }

    /**
     * The only stored Header formatting values that suppress the column-header row.
     *
     * Anything else, including an unrecognised value, writes the header, which is
     * the safe default. Both directions run through this list:
     * normalize_header_formatting() reads it, and the Settings REST write path
     * canonicalises through that same normaliser, so the stored value and the
     * engine's reading of it cannot drift apart.
     *
     * @since 2.8.1
     * @var array
     */
    const HEADER_FORMATTING_EXCLUDED = array( '0', 'exclude' );

    /**
     * The exporter.
     *
     * @var object
     */
    protected $_exporter = null;

    /**
     * The export type.
     *
     * @var string
     */
    protected $export_type = '';

    /**
     * Export settings.
     *
     * @var object
     */
    protected $export_settings;

    /**
     * Batch to export.
     *
     * @var array
     */
    protected $export_batch = array();

    /**
     * The filename.
     *
     * @var string
     */
    protected $filename = '';

    /**
     * Form data.
     *
     * @var array
     */
    protected $form_data = array();

    /**
     * Export columns.
     *
     * @var array
     */
    protected $export_columns = array();

    /**
     * Export format.
     *
     * @var string
     */
    protected $export_format = '';

    /**
     * Export encoding.
     *
     * @var string
     */
    protected $export_encoding = '';

    /**
     * Is scheduled.
     *
     * @var array
     */
    protected $is_scheduled = false;

    /**
     * Export file.
     *
     * @var Spreadsheet|resource|SimpleXMLElement
     */
    protected $file = null;

    /**
     * Export file path.
     *
     * @var string
     */
    protected $file_path = '';

    /**
     * Export file contents.
     *
     * @var string
     */
    protected $file_contents = '';

    /**
     * Total rows.
     *
     * @var int
     */
    protected $total_rows = 0;

    /**
     * Default fields.
     *
     * @var array
     */
    protected $default_fields = array();

    /**
     * Preapre file.
     *
     * Note: Scheduled exports doesn't run in batches.
     *       This is because the export is run in the background and the file is prepared in one go.
     *       Because background processes doens't have a timeout issue.
     *
     * @since 2.7.3
     * @since 2.8.1 Header formatting is honoured for CSV and TSV; formula-triggering cells are neutralised.
     * @access public
     *
     * @throws Exception If the file cannot be opened for writing.
     */
    protected function prepare_file() {
        $this->file_path = $this->get_file_path( $this->filename );
        if ( in_array( $this->export_format, array( 'csv', 'tsv' ), true ) ) {
            // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fclose, WordPress.WP.AlternativeFunctions.file_system_operations_fopen
            $this->file = fopen( $this->file_path, 'w' );
            if ( false === $this->file ) {
                // translators: %s: file path.
                throw new Exception( sprintf( esc_html__( 'Failed to open file %s for writing.', 'woocommerce-exporter' ), esc_html( $this->file_path ) ) );
            }

            // Write the headers to the file, honouring the Header formatting setting.
            if ( $this->export_settings->header_formatting ) {
                $headers = array_values( $this->export_settings->columns );

                // Neutralise the header row on the same terms as the data rows: column
                // labels can carry store-controlled text (Export Template overrides and
                // custom-meta labels), so row 1 is as capable of injecting a formula as
                // any other.
                if ( $this->escapes_delimited_formulas() ) {
                    $headers = array_map( array( $this, 'escape_formula_value' ), $headers );
                }

                // PHP 8.4 deprecates calling fputcsv() without $escape. The
                // historical default is a backslash, so it is passed explicitly to
                // keep the written file byte-identical on every supported version.
                // Passing "" instead would silently change the escaping rules.
                $write = fputcsv( $this->file, $headers, $this->_get_delimiter(), '"', '\\' );
                if ( false === $write ) {
                    // translators: %s: file path.
                    throw new Exception( sprintf( esc_html__( 'Failed to write headers to file %s.', 'woocommerce-exporter' ), esc_html( $this->file_path ) ) );
                }
            }

            // Close the file if quick export.
            if ( ! $this->is_scheduled ) {
                fclose( $this->file );
            }
            // phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fclose, WordPress.WP.AlternativeFunctions.file_system_operations_fopen
        }
    }

    /**
     * Write data to a file in chunks.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array $dataset The data to write to the file.
     * @param int   $step    The step of the batch currenly on.
     * @return void
     * @throws Exception If the file cannot be opened for writing.
     */
    public function write_data_in_chunks( $dataset, $step = 0 ) { // phpcs:ignore
        // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
        // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
        // phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        if ( empty( $this->file_path ) ) {
            $this->file_path = $this->get_file_path( $this->filename );
        }

        if ( in_array( $this->export_format, array( 'csv', 'tsv' ), true ) ) {
            // Open the file for writing.
            if ( ! $this->is_scheduled ) {
                $this->file = fopen( $this->file_path, 'a' );
            }

            // Neutralise spreadsheet formula injection unless Excel-formula
            // export is explicitly enabled.
            $escape_formulas = $this->escapes_delimited_formulas();

            // Write the data to the file.
            foreach ( $dataset as $data ) {
                if ( $escape_formulas ) {
                    $data = array_map( array( $this, 'escape_formula_value' ), $data );
                }
                // $escape is passed explicitly for the same reason as in
                // prepare_file(): PHP 8.4 deprecates omitting it.
                $write = fputcsv( $this->file, $data, $this->_get_delimiter(), '"', '\\' );
            }

            // Close the file.
            if ( ! $this->is_scheduled ) {
                fclose( $this->file );
            }
        }
        // phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        // phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
        // phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
        // phpcs:enable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
    }

    /**
     * Write footer to the spreadsheet.
     *
     * @since 2.7.3
     * @access public
     *
     * @param object $sheet The spreadsheet object.
     * @param int    $row   The row to write to.
     */
    public function write_footer( $sheet, $row ) {
        // Allow Plugin/Theme authors to add in rows at the end of the export.
        $sheet = apply_filters( 'woo_ce_phpexcel_sheet_footer', $sheet, $row, $this->export_settings->type, $this->export_settings->export_format );
    }

    /**
     * Generate filename of export file.
     *
     * @since 2.7.3
     * @access public
     *
     * @param string $export_type The export type.
     * @param string $export_format The export format.
     */
    public function generate_filename( $export_type, $export_format ) {
        // Check if a fixed filename hasn't been provided.
        if ( ! empty( $override ) ) {
            $filename = $override;
        } else {
            // Get the filename from WordPress options.
            $filename = get_option( WOO_CE_PREFIX . '_export_filename', '%store_name%-export_%dataset%-%date%-%time%-%random%' );
            // Check for empty filename.
            if ( empty( $filename ) ) {
                $filename = '%store_name%-export_%dataset%-%date%-%time%-%random%';
            }
            // Strip file extensions if present.
            $filename = str_replace( array( '.csv', '.tsv', '.txt', '.xls', '.xlsx', '.xml', '.rss', '.json' ), '', $filename );
        }

        // Switch out the Tags for filled values.
        $filename = str_replace( '%dataset%', $export_type, $filename );
        if ( strstr( $filename, '%date%' ) !== false ) {
            $date     = apply_filters( 'woo_ce_filename_tag_date', gmdate( 'Y_m_d' ) );
            $filename = str_replace( '%date%', $date, $filename );
        }
        if ( strstr( $filename, '%year%' ) !== false ) {
            $year     = apply_filters( 'woo_ce_filename_tag_year', gmdate( 'Y' ) );
            $filename = str_replace( '%year%', $year, $filename );
        }
        if ( strstr( $filename, '%month%' ) !== false ) {
            $month    = apply_filters( 'woo_ce_filename_tag_month', gmdate( 'm' ) );
            $filename = str_replace( '%month%', $month, $filename );
        }
        if ( strstr( $filename, '%day%' ) !== false ) {
            $day      = apply_filters( 'woo_ce_filename_tag_day', gmdate( 'd' ) );
            $filename = str_replace( '%day%', $day, $filename );
        }
        if ( strstr( $filename, '%time%' ) !== false ) {
            $time     = apply_filters( 'woo_ce_filename_tag_time', gmdate( 'H_i_s' ) );
            $filename = str_replace( '%time%', $time, $filename );
        }
        if ( strstr( $filename, '%hour%' ) !== false ) {
            $hour     = apply_filters( 'woo_ce_filename_tag_hour', gmdate( 'H' ) );
            $filename = str_replace( '%hour%', $hour, $filename );
        }
        if ( strstr( $filename, '%minute%' ) !== false ) {
            $minute   = apply_filters( 'woo_ce_filename_tag_minute', gmdate( 'i' ) );
            $filename = str_replace( '%minute%', $minute, $filename );
        }
        if ( strstr( $filename, '%random%' ) !== false ) {
            $random   = wp_rand( 10000000, 99999999 );
            $filename = str_replace( '%random%', $random, $filename );
        }
        if ( strstr( $filename, '%store_name%' ) !== false ) {
            $store_name = sanitize_title( get_bloginfo( 'name' ) );
            $filename   = str_replace( '%store_name%', $store_name, $filename );
        }
        if ( strstr( $filename, '%order_id%' ) !== false ) {
            // Check if the Transient is set.
            $order_id = ( $this->export_settings->scheduled_export ? absint( get_transient( WOO_CE_PREFIX . '_single_export_post_ids' ) ) : 0 );
            if ( ! empty( $order_id ) ) {
                $filename = str_replace( '%order_id%', $order_id, $filename );
            }
        }

        $file_extension = $export_format;
        if ( 'rss' === $export_format ) {
            $file_extension = 'xml';
        }

        /**
         * Filter the generated filename.
         *
         * @since 2.7.3
         * @param string $filename    The generated filename.
         * @param string $export_type The export type.
         */
        return sanitize_file_name( apply_filters( 'wsed_generate_filename', $filename, $export_type ) ) . '.' . $file_extension;
    }

    /**
     * Set the export headers.
     *
     * @since 2.7.3
     * @access public
     */
    public function send_headers() {
        // phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.IniSet.Risky
        $post_mime_type = $this->get_post_mime_type( $this->export_format );

        if ( function_exists( 'gc_enable' ) ) {
            gc_enable(); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.gc_enableFound
        }
        if ( function_exists( 'apache_setenv' ) ) {
            @apache_setenv( 'no-gzip', 1 ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_apache_setenv
        }
        @ini_set( 'zlib.output_compression', 'Off' ); // @codingStandardsIgnoreLine
        @ini_set( 'output_buffering', 'Off' ); // @codingStandardsIgnoreLine
        @ini_set( 'output_handler', '' ); // @codingStandardsIgnoreLine
        ignore_user_abort( true );
        wc_set_time_limit( 0 );
        wc_nocache_headers();
        header( sprintf( 'Content-Type: %1$s; charset=%2$s', esc_attr( $post_mime_type ), esc_attr( $this->export_encoding ) ) );
        header( sprintf( 'Content-Encoding: %s', esc_attr( $this->export_encoding ) ) );
        header( sprintf( 'Content-Disposition: attachment; filename=%s', $this->filename ) );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        // phpcs:enable WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.IniSet.Risky
    }

    /**
     * Finalize the export.
     *
     * @since 2.7.3
     * @access public
     */
    public function finalize_export() {
        // phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
        // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
        // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        // phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_chmod
        if ( empty( $this->file_path ) ) {
            $this->file_path = $this->get_file_path( $this->filename );
        }

        if ( in_array( $this->export_format, array( 'csv', 'tsv' ), true ) ) {
            if ( ! $this->is_scheduled ) {
                $this->file = fopen( $this->file_path, 'r' );
            }

            $this->total_rows = $this->count_file_total_rows();

            if ( ! $this->is_scheduled ) {
                fclose( $this->file );
            }
        }

        if ( @file_exists( $this->file_path ) ) {
            $this->file_contents = @file_get_contents( $this->file_path );
        }
        // phpcs:enable WordPress.PHP.NoSilencedErrors.Discouraged
        // phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
        // phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        // phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        // phpcs:enable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        // phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_chmod
    }

    /**
     * Delete the temporary file.
     *
     * @since 2.7.3
     * @access public
     */
    public function delete_temp_file() {
        // phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
        if ( @file_exists( $this->file_path ) ) {
            if ( '0' === get_option( WOO_CE_PREFIX . '_delete_file', '1' ) ) {
                $this->save_file_to_archive();
            }
            unlink( $this->file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
        }
        // phpcs:enable WordPress.PHP.NoSilencedErrors.Discouraged
    }

    /**
     * Send the export content to browser.
     *
     * @since 2.7.3
     * @access public
     */
    public function send_content() {
        echo $this->file_contents; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Count file total rows.
     *
     * @since 2.7.3
     * @access public
     */
    public function count_file_total_rows() {
        $rows = 0;
        if ( in_array( $this->export_format, array( 'csv', 'tsv' ), true ) ) {
            // Change file stream to read mode.
            // For scheduled exports the file is on write mode.
            if ( $this->is_scheduled ) {
                $file = fopen( $this->file_path, 'r' ); // phpcs:ignore
            } else {
                $file = $this->file;
            }

            while ( ! feof( $file ) ) {
                $line = fgets( $file );
                if ( ! empty( $line ) ) {
                    ++$rows;
                }
            }
        }
        return $rows;
    }

    /**
     * Save file to archive.
     *
     * @since 2.7.3
     * @access public
     */
    public function save_file_to_archive() {
        // The protected export directory and its deny-all guard files must exist
        // before customer data is written into it.
        self::ensure_export_dir();

        // Resolve the archive name against the archive sub-directory, not
        // EXPORT_DIR: the export's temp file still sits there at this point and
        // would force a "-1" suffix onto every archive.
        $archive_dir     = self::get_archive_dir();
        $unique_filename = wp_unique_filename( $archive_dir, $this->filename );
        $file_path       = $archive_dir . $unique_filename;

        // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        $bytes_written = file_put_contents( $file_path, $this->file_contents );
        // phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        if ( false === $bytes_written ) {
            return 0;
        }

        $args = array(
            'post_status'    => 'private',
            'post_title'     => $this->filename,
            'post_type'      => 'attachment',
            'post_mime_type' => $this->get_post_mime_type( $this->export_format ),
        );

        $post_id = wp_insert_attachment( $args, $file_path );
        if ( is_wp_error( $post_id ) || ! $post_id ) {
            wp_delete_file( $file_path );
            return 0;
        }

        \update_attached_file( $post_id, $file_path );

        // Never expose a public URL for an archived export. wp_insert_post()
        // backfills an empty guid with get_permalink(), and wp_get_attachment_url()
        // falls back to the guid, so clear it after the insert.
        self::clear_attachment_guid( (int) $post_id );

        add_post_meta( $post_id, '_woo_export_type', $this->export_type );
        add_post_meta( $post_id, '_woo_start_time', $this->export_settings->start_time );
        add_post_meta( $post_id, '_woo_end_time', microtime( true ) );
        add_post_meta( $post_id, '_woo_idle_memory_start', $this->export_settings->idle_memory_start );
        add_post_meta( $post_id, '_woo_data_memory_start', $this->export_settings->data_memory_start );
        add_post_meta( $post_id, '_woo_idle_memory_end', Helper::get_current_memory_usage() - $this->export_settings->idle_memory_start );
        add_post_meta( $post_id, '_woo_columns', $this->export_settings->total_columns );
        add_post_meta( $post_id, '_woo_rows', $this->total_rows );

        return (int) $post_id;
    }

    /**
     * Clear an archive attachment's guid so no public URL can resolve to it.
     *
     * @since 2.8.1
     * @access public
     *
     * @param int $attachment_id The archive attachment post ID.
     * @return void
     */
    public static function clear_attachment_guid( int $attachment_id ) {
        global $wpdb;

        $wpdb->update( $wpdb->posts, array( 'guid' => '' ), array( 'ID' => $attachment_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        clean_post_cache( $attachment_id );
    }

    /**
     * Set the exporter.
     *
     * @since 2.7.3
     * @access public
     *
     * @param string $export_type The export type.
     */
    public function set_exporter( $export_type ) {
        switch ( $export_type ) {
            case 'product':
                $this->_exporter = Export_Product::instance();
                break;
            case 'category':
                $this->_exporter = Export_Category::instance();
                break;
            case 'tag':
                $this->_exporter = Export_Tag::instance();
                break;
            case 'order':
                $this->_exporter = Export_Order::instance();
                break;
            case 'customer':
                $this->_exporter = Export_Customer::instance();
                break;
            case 'user':
                $this->_exporter = Export_User::instance();
                break;
            case 'review':
                $this->_exporter = Export_Review::instance();
                break;
            case 'coupon':
                $this->_exporter = Export_Coupon::instance();
                break;
            case 'attribute':
                $this->_exporter = Export_Attribute::instance();
                break;
            case 'shipping_class':
                $this->_exporter = Export_Shipping_Class::instance();
                break;
            case 'subscription':
                $this->_exporter = Export_Subscription::instance();
                break;
            case 'booking':
                $this->_exporter = Export_Booking::instance();
                break;
            case 'brand':
                $this->_exporter = Export_Brand::instance();
                break;
            case 'product_vendor':
                $this->_exporter = Export_Product_Vendor::instance();
                break;
            case 'commission':
                $this->_exporter = Export_Commission::instance();
                break;
            case 'ticket':
                $this->_exporter = Export_Ticket::instance();
                break;
            default:
                return;
        }
        $this->_exporter->run();
    }

    /**
     * Get post mime type.
     *
     * @since 2.7.3
     * @access public
     *
     * @param string $export_format The export format (csv, tsv, xls, xlsx).
     */
    public function get_post_mime_type( $export_format = 'csv' ) {
        $post_mime_type = 'text/csv';
        switch ( $export_format ) {
            case 'csv':
                $post_mime_type = 'text/csv';
                break;
            case 'tsv':
                $post_mime_type = 'text/tab-separated-values';
                break;
        }

        return $post_mime_type;
    }

    /**
     * Get writer type.
     *
     * @since 2.7.3
     * @access public
     *
     * @param string $export_format The export format (csv, tsv, xls, xlsx).
     */
    public function get_writer_type( $export_format = 'csv' ) {
        $writer_type = 'Xls';
        switch ( $export_format ) {
            case 'xls':
                $writer_type = 'Xls';
                break;
            case 'xlsx':
                $writer_type = 'Xlsx';
                break;
        }
        return $writer_type;
    }

    /**
     * Resolve the Header formatting setting to a boolean.
     *
     * The stored value has two historical shapes and both are still in the wild:
     * the Settings SPA saves the slugs `include`/`exclude`, while the legacy
     * pre-SPA radios save `1`/`0`. Resolving it once here means every export
     * format shares one interpretation of the setting.
     *
     * @since 2.8.1
     * @access public
     *
     * @param mixed $value The per-export value, or an empty string to fall back to the global option.
     * @return bool True to write the column-header row, false to omit it.
     */
    public static function normalize_header_formatting( $value ) {
        // Idempotent: an already-normalised boolean passes straight through.
        // Without this, `(string) false` is `''`, which is not an excluded value
        // and would silently turn the header row back on when a caller re-feeds
        // its own result.
        if ( is_bool( $value ) ) {
            return $value;
        }

        // A non-scalar (a stray `header_formatting[]` in the posted form data) or
        // an empty value means "not specified": fall back to the global option.
        if ( ! is_scalar( $value ) || '' === $value ) {
            $value = woo_ce_get_option( 'header_formatting', 1 );
        }

        // `self::` deliberately: the constant is the single source of truth for
        // every surface, and the external callers (Settings_Controller) name
        // Abstract_Exporter explicitly, so a subclass override could never apply
        // consistently. Extend the constant instead.
        return ! in_array( (string) $value, self::HEADER_FORMATTING_EXCLUDED, true );
    }

    /**
     * Whether the stored global Header formatting setting means "write the column-header row".
     *
     * A named wrapper over `normalize_header_formatting( '' )`, whose bare empty
     * string reads as a placeholder at the call site rather than as "no per-export
     * value, so fall back to the global option".
     *
     * @since 2.8.1
     * @access public
     *
     * @return bool True to write the column-header row, false to omit it.
     */
    public static function stored_header_formatting_includes_headers() {
        return self::normalize_header_formatting( '' );
    }

    /**
     * Neutralise a cell value that a spreadsheet application would interpret as a
     * formula.
     *
     * Prefixes any string whose first character can trigger formula evaluation
     * (`=`, `+`, `-`, `@`, TAB or CR) with a single quote, mitigating CSV/TSV
     * formula injection when Excel-formula export is disabled. Non-string values
     * and strings that cannot trigger a formula are returned untouched.
     *
     * @since 2.8.1
     * @access private
     *
     * @param mixed $value Cell value.
     * @return mixed Neutralised value, or the original value when it cannot trigger a formula.
     */
    private function escape_formula_value( $value ) {
        if ( ! is_string( $value ) || '' === $value ) {
            return $value;
        }

        // Only these characters can initiate formula evaluation.
        if ( ! preg_match( '/^[=\-+@\t\r]/', $value ) ) {
            return $value;
        }

        // Inert: a +/- leading value made only of number and phone punctuation
        // cannot carry a DDE payload. That needs letters, `|` and `!`, none of
        // which the permitted class admits, so `HYPERLINK`, `WEBSERVICE`, `DDE`
        // and `cmd|` are all unreachable here. Sparing these matters because the
        // naive trigger set would put an apostrophe on every negative amount
        // ( -25.00, -1 ) and every international phone number ( +61412345678,
        // +1 555 123 4567, +44 (0)20 7946 0018 ) in the file.
        //
        // The residue is arithmetic evaluation, never code execution. Because the
        // class permits a further `-`, a value made only of digits and hyphens
        // passes through raw and Excel does evaluate it on open ( +1-1 renders 0,
        // -1-1 renders -2 ). Accepted deliberately: widening the class would put
        // the apostrophe back on the phone numbers this narrowing exists to spare.
        // Anything carrying `+`, `=`, `@` or a letter past the first character
        // ( -2+3 ) does not match and still escapes.
        if ( preg_match( '/^[+\-][0-9 ().,\-]*$/', $value ) ) {
            return $value;
        }

        return "'" . $value;
    }

    /**
     * Whether this export may emit live spreadsheet formulas.
     *
     * SECURITY: the preference is matched against an allow-list so that only an
     * explicit "yes" enables live formulas; any other stored shape denies them.
     *
     * @since 2.8.1
     * @access protected
     *
     * @return bool True when live formulas are allowed, false to neutralise them.
     */
    protected function allows_excel_formulas() {
        $preference = apply_filters( 'woo_ce_phpexcel_export_formulas', $this->export_settings->excel_formulas ?? '' );
        return in_array( $preference, array( 1, '1', true, 'allow' ), true );
    }

    /**
     * Whether CSV/TSV cells should be neutralised in this export.
     *
     * Two conditions, both required. The export must be denying formulas, AND
     * the store must not have opted out via Settings > General > Escape formulas
     * in CSV/TSV. The opt-out exists because neutralising is lossy for delimited
     * output: the apostrophe becomes part of the exported bytes, so a store whose
     * downstream importer cannot tolerate it needs a way off.
     *
     * @since 2.8.1
     * @access protected
     *
     * @return bool True when delimited cells should be neutralised.
     */
    protected function escapes_delimited_formulas() {
        return ! $this->allows_excel_formulas() && (bool) woo_ce_get_option( 'csv_formula_escaping', 1 );
    }

    /**
     * Setup export settings.
     *
     * @since 2.7.3
     * @access public
     *
     * The resulting object is passed through the `wsed_export_settings` filter,
     * so its property types are public API.
     *
     * @since 2.8.1 `$export->header_formatting` is now a bool, resolved through
     *              normalize_header_formatting(). It previously carried the raw
     *              stored value (the `include`/`exclude` slug or the legacy
     *              `1`/`0`), so integrations comparing it strictly need updating.
     *
     * @param array $settings The settings to be exported.
     *                        Obtained from the form data via quick export or the post data and meta via scheduled export.
     */
    public function setup_export_settings( $settings ) {
        // Set default values for all export options to be later passed onto the export process.
        $export                     = new \stdClass();
        $export->start_time         = microtime( true );
        $export->time_limit         = ( isset( $time_limit ) ? $time_limit : 0 );
        $export->idle_memory_start  = Helper::get_current_memory_usage();
        $export->data_memory_start  = Helper::get_current_memory_usage();
        $export->encoding           = get_option( WOO_CE_PREFIX . '_encoding', get_option( 'blog_charset', 'UTF-8' ) );
        $export->cron               = $settings['cron'] ?? false;
        $export->scheduled_export   = $settings['scheduled_export'] ?? false;
        $export->post_id            = $settings['post_id'] ?? false;
        $export->export_format      = woo_ce_resolve_export_format( $settings['export_format'] ?? '' );
        $export->bom                = $settings['bom'] ?? get_option( WOO_CE_PREFIX . '_bom', 1 );
        $export->excel_formulas     = $settings['excel_formulas'] ?? get_option( WOO_CE_PREFIX . '_excel_formulas' );
        $export->header_formatting  = self::normalize_header_formatting( $settings['header_formatting'] ?? '' );
        $export->delimiter          = $settings['delimiter'] ?? get_option( WOO_CE_PREFIX . '_delimiter', ',' );
        $export->category_separator = $settings['category_separator'] ?? get_option( WOO_CE_PREFIX . '_category_separator', '|' );
        $export->escape_formatting  = $settings['escape_formatting'] ?? get_option( WOO_CE_PREFIX . '_escape_formatting', 'all' );
        $export->batch_size         = $settings['batch_size'] ?? get_option( WOO_CE_PREFIX . '_batch_size', WSED_DEFAULT_BATCH_SIZE );
        $export->export_template    = ( isset( $settings['export_template'] ) ? absint( $settings['export_template'] ) : false );
        $export->type               = ( isset( $settings['dataset'] ) ? sanitize_text_field( $settings['dataset'] ) : false );
        $export->limit_volume       = ( isset( $settings['limit_volume'] ) ? sanitize_text_field( $settings['limit_volume'] ) : '' );
        $export->offset             = ( isset( $settings['offset'] ) ? sanitize_text_field( $settings['offset'] ) : '' );
        $export->fields             = ( isset( $settings[ $export->type . '_fields' ] ) && is_array( $settings[ $export->type . '_fields' ] ) ? array_map( 'sanitize_text_field', $settings[ $export->type . '_fields' ] ) : array() );
        $export->fields_order       = ( isset( $settings[ $export->type . '_fields_order' ] ) && is_array( $settings[ $export->type . '_fields_order' ] ) ? array_map( 'absint', $settings[ $export->type . '_fields_order' ] ) : false );

        // Force the batch size to default if it's empty.
        $export->batch_size = empty( $export->batch_size ) ? WSED_DEFAULT_BATCH_SIZE : $export->batch_size;

        // Set the limit volume to -1 if it's empty.
        $export->limit_volume = empty( $export->limit_volume ) ? -1 : $export->limit_volume;

        // Set the offset to 0 if it's empty.
        $export->offset = empty( $export->offset ) ? 0 : $export->offset;

        // Set the delimiter to comma if it's empty.
        $export->delimiter = empty( $export->delimiter ) ? ',' : $export->delimiter;

        // Set the description excerpt formatting.
        if ( in_array( $export->type, array( 'product', 'category', 'tag', 'brand', 'order' ), true ) ) {
            $export->description_excerpt_formatting = ( isset( $settings['description_excerpt_formatting'] ) ? absint( $settings['description_excerpt_formatting'] ) : false );
        }

        // If delimiter is TAB, override it to the actual tab character.
        if ( 'TAB' === $export->delimiter ) {
            $export->delimiter = "\t";
        }

        // Override for line break (LF) support in Category Separator.
        if ( 'LF' === $export->category_separator ) {
            $export->category_separator = "\n";
        }

        // Export arguments.
        $export_dataset_args = array(
            'limit_volume' => $export->limit_volume,
            'offset'       => $export->offset,
            'encoding'     => $export->encoding,
            'date_format'  => get_option( WOO_CE_PREFIX . '_date_format', 'd/m/Y' ),
        );
        $export->args        = apply_filters( 'wsed_extend_export_dataset_args', $export_dataset_args, $export, $settings );

        // Set the export columns.
        $export->columns       = $this->get_export_columns( $export->fields, $export );
        $export->total_columns = count( $export->columns );

        // Set the filename.
        $export->filename = $this->generate_filename( $export->type, $export->export_format );

        // Set writer type.
        $export->writer_type = $this->get_writer_type( $export->export_format );

        return apply_filters( 'wsed_export_settings', $export, $export->type );
    }

    /**
     * Get export columns.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array  $fields The default fields.
     * @param object $export The export settings.
     */
    public function get_export_columns( $fields, $export ) {
        if ( ! is_array( $fields ) ) {
            return array();
        }

        $columns        = array();
        $default_fields = $this->default_fields;

        // Sort the fields based on fields order.
        $sorted_fields = array();
        foreach ( $fields as $key => $field ) {
            $index = array_search( $key, array_column( $default_fields, 'name' ), true );
            if ( false !== $index ) {
                $sorted_fields[] = $default_fields[ $index ];
            }
        }
        $fields = $sorted_fields;

        if ( ! empty( $fields ) ) {
            foreach ( $fields as $field ) {
                $columns[ $field['name'] ] = $field['label'];
            }
        }

        // Override labels if export template is set.
        if ( ! empty( $export->export_template ) ) {
            $labels = get_post_meta( $export->export_template, sprintf( '_%s_labels', $export->type ), true );

            if ( $labels ) {
                foreach ( $labels as $key => $label ) {
                    if ( isset( $columns[ $key ] ) ) {
                        $columns[ $key ] = $label;
                    }
                }
            }
        }

        return apply_filters( 'wsed_override_export_columns', $columns, $fields, $export );
    }

    /**
     * Prepare server for export.
     *
     * @since 2.7.3
     * @access public
     */
    public function prepare_server() {
        // Hide error logging during the export process.
        if ( function_exists( 'ini_set' ) ) {
            @ini_set( 'display_errors', 0 ); // phpcs:ignore
        }

        // Welcome in the age of GZIP compression and Object caching.
        if ( ! defined( 'DONOTCACHEPAGE' ) ) {
            define( 'DONOTCACHEPAGE', true );
        }
        if ( ! defined( 'DONOTCACHCEOBJECT' ) ) {
            define( 'DONOTCACHCEOBJECT', true );
        }

        // Cache control.
        $cache_flush = get_option( WOO_CE_PREFIX . '_cache_flush', 0 );
        if ( $cache_flush ) {
            add_action( 'woo_ce_export_cache_flush', 'wp_cache_flush' );
        }
        do_action( 'woo_ce_export_cache_flush' );

        $time_limit = false;
        if ( function_exists( 'ini_get' ) ) {
            $time_limit = ini_get( 'max_execution_time' );
        }

        // absint(): the stored option can be '' (saved by the settings screen), which set_time_limit() rejects on PHP 8.
        $timeout   = absint( get_option( WOO_CE_PREFIX . '_timeout', 0 ) );
        $safe_mode = ( function_exists( 'safe_mode' ) ? ini_get( 'safe_mode' ) : false );

        if ( ! $safe_mode ) {
            // Double up, why not.
            if ( function_exists( 'set_time_limit' ) ) {
                @set_time_limit( $timeout ); // phpcs:ignore
            }
            if ( function_exists( 'ini_set' ) ) {
                @ini_set( 'max_execution_time', $timeout ); // phpcs:ignore
            }
        }

        if ( function_exists( 'ini_set' ) ) {
            @ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT ); // phpcs:ignore
        }
    }

    /**
     * Set export format.
     *
     * @since 2.7.3
     * @access public
     *
     * @param string $format The export format.
     */
    public function set_export_format( $format ) {
        $this->export_format = $format;
    }

    /**
     * Set export columns.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array $columns The export columns.
     */
    public function set_export_columns( $columns ) {
        $this->export_columns = $columns;
    }

    /**
     * Set export encoding.
     *
     * @since 2.7.3
     * @access public
     *
     * @param string $encoding The export encoding.
     */
    public function set_export_encoding( $encoding ) {
        $this->export_encoding = $encoding;
    }

    /**
     * Set export type.
     *
     * @since 2.7.3
     * @access public
     *
     * @param  string $type The export type.
     */
    public function set_export_type( $type ) {
        $this->export_type = $type;
    }

    /**
     * Get export type.
     *
     * @since 2.7.3
     */
    public function get_export_type() {
        return $this->export_type;
    }

    /**
     * Set export batch.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array $batch The batch to export.
     */
    public function set_export_batch( $batch ) {
        $this->export_batch = $batch;
    }

    /**
     * Get export batch.
     *
     * @since 2.7.3
     * @access public
     */
    public function get_export_batch() {
        return $this->export_batch;
    }

    /**
     * Set export settings.
     *
     * @since 2.7.3
     * @access public
     *
     * @param  object $export_settings Export arguments.
     */
    public function set_export_settings( $export_settings ) {
        $this->export_settings = $export_settings;
    }

    /**
     * Get export settings.
     *
     * @since 2.7.3
     * @access public
     */
    public function get_export_settings() {
        return $this->export_settings;
    }

    /**
     * Set filename to export to.
     *
     * @since 2.7.3
     * @access public
     *
     * @param  string $filename Filename to export to.
     */
    public function set_filename( $filename ) {
        $this->filename = $filename;
    }

    /**
     * Generate and return a filename.
     *
     * @since 2.7.3
     * @access public
     *
     * @return string
     */
    public function get_filename() {
        return $this->filename;
    }

    /**
     * Set form data.
     *
     * @since 2.7.3
     * @access public
     *
     * @param string $post The form data.
     */
    public function set_form_data( $post ) {
        parse_str( $post, $form_data );
        $this->form_data = $form_data;
    }

    /**
     * Set form data.
     *
     * @since 2.7.3
     * @access public
     */
    public function get_form_data() {
        return $this->form_data;
    }

    /**
     * Get the delimiter.
     *
     * @since 2.7.3
     * @access private
     *
     * @return string
     */
    private function _get_delimiter() {
        return 'tsv' === $this->export_format ? "\t" : $this->export_settings->delimiter;
    }

    /**
     * Serve the file and remove once sent to the client.
     *
     * @since 2.7.3
     */
    public function export() {
        // Check if we are printing file headers.
        if ( apply_filters( 'woo_ce_export_print_to_browser', true ) ) {
            $this->send_headers();
        }
        $this->finalize_export();
        $this->send_content();
        $this->delete_temp_file();
        die();
    }

    /**
     * Get file path.
     *
     * @since 2.7.3
     * @access public
     *
     * @param string $filename The filename.
     * @return string
     * @throws Exception If the export directory could not be created.
     */
    public function get_file_path( $filename = null ) {
        if ( ! $filename ) {
            $filename = $this->filename;
        }

        self::ensure_export_dir();

        return self::EXPORT_DIR . '/' . $filename;
    }

    /**
     * Ensure the export directory exists and is protected from direct web access.
     *
     * The export directory and the archive sub-directory are seeded with a
     * deny-all `.htaccess` (Apache), a `web.config` (IIS) and an `index.php`
     * (no directory listing). nginx ignores all three, so nginx operators must
     * deny the directory themselves; the random archive directory slug keeps
     * archives unguessable regardless.
     *
     * @since 2.8.1
     * @access public
     *
     * @return void
     * @throws Exception If the export directory or its guard files could not be created.
     */
    public static function ensure_export_dir() {
        // phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions
        $files_to_create = array(
            '.htaccess'  => "# Apache 2.4\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
                . "# Apache 2.2\n<IfModule !mod_authz_core.c>\n    Order deny,allow\n    Deny from all\n</IfModule>\n",
            'index.php'  => '<?php // Silence is golden',
            'web.config' => '<?xml version="1.0" encoding="UTF-8"?><configuration><system.webServer>'
                . '<authorization><deny users="*" /></authorization>'
                . '</system.webServer></configuration>',
        );

        foreach ( array( self::EXPORT_DIR, self::get_archive_dir() ) as $dir ) {
            if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
                throw new Exception( esc_html__( 'Could not create export directory.', 'woocommerce-exporter' ) );
            }

            // Applied on every run: wp_mkdir_p() inherits the parent mode (usually
            // 0755) and an export directory created by an older version would
            // otherwise never be tightened. Idempotent.
            @chmod( $dir, 0700 );

            foreach ( $files_to_create as $file => $file_content ) {
                $path = $dir . $file;

                // Compare contents, not only existence, so an outdated guard file
                // (the old Apache-2.2-only "deny from all") gets the hardened version.
                if ( file_exists( $path ) && @file_get_contents( $path ) === $file_content ) {
                    continue;
                }

                $fh = @fopen( $path, 'w' );
                if ( ! is_resource( $fh ) ) {
                    throw new Exception( esc_html__( 'Could not protect the export directory.', 'woocommerce-exporter' ) );
                }
                fwrite( $fh, $file_content );
                fclose( $fh );
            }
        }
        // phpcs:enable WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions
    }

    /**
     * Run the class
     *
     * @since 2.7.3
     */
    abstract protected function run();
}
