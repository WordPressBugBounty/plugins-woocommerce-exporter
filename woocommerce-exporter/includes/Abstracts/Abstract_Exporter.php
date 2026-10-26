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
     * The Escape formatting modes the delimited writer understands.
     *
     * The single source of truth for the setting's vocabulary. Every surface —
     * the export engine, both settings screens and the Settings REST route —
     * resolves through normalize_escape_formatting(), so a fourth mode belongs
     * here plus its own radio, and nowhere else.
     *
     * The modes control *quoting only*, and only for CSV and TSV:
     *  - `all`   wrap every cell, doubling any embedded quote.
     *  - `excel` quote only where the delimiter, a quote or a newline forces it.
     *  - `none`  write the raw value, quoting nothing.
     *
     * @since 2.8.2
     * @var array
     */
    const ESCAPE_FORMATTING_MODES = array( 'all', 'excel', 'none' );

    /**
     * The Escape formatting mode an unset, empty or unrecognised value resolves to.
     *
     * `all` matches the default the settings screens have always advertised, and
     * is the fail-safe of the three: quoting every cell keeps a file parseable
     * whatever it contains, where `none` can produce a corrupt one.
     *
     * @since 2.8.2
     * @var string
     */
    const DEFAULT_ESCAPE_FORMATTING = 'all';

    /**
     * Option key the Settings SPA has been writing this control to.
     *
     * Not the key anything reads. The SPA bound its radios to `field_escape`
     * while every read path — the engine and the legacy settings screen — uses
     * `escape_formatting`, so a store that has only ever used the SPA has its
     * choice stranded under this key. stored_escape_formatting() adopts it when
     * the canonical option was never written.
     *
     * @since 2.8.2
     * @var string
     */
    const ESCAPE_FORMATTING_LEGACY_SPA_OPTION = 'field_escape';

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
     * Whether $export_settings belongs to the file this instance serves.
     *
     * A download can run while a newer export is in progress. The shared settings
     * transient then holds that newer export's run stats, which must not be
     * written into the archive meta of the downloaded file.
     *
     * @since 2.8.2
     * @var bool
     */
    protected $settings_match_file = true;

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

                $write = $this->write_delimited_row( $headers, $this->delimited_escape_mode(), $this->escapes_delimited_formulas() );
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
            // Both are read once per chunk, not once per row or cell.
            $escape_formulas = $this->escapes_delimited_formulas();
            $escape_mode     = $this->delimited_escape_mode();

            // Write the data to the file.
            foreach ( $dataset as $data ) {
                if ( $escape_formulas ) {
                    $data = array_map( array( $this, 'escape_formula_value' ), $data );
                }
                $this->write_delimited_row( $data, $escape_mode, $escape_formulas );
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
        add_post_meta( $post_id, '_woo_end_time', microtime( true ) );
        add_post_meta( $post_id, '_woo_rows', $this->total_rows );

        // The run stats come from the settings. When they belong to another
        // export, they are left out rather than written against this file.
        if ( $this->settings_match_file ) {
            add_post_meta( $post_id, '_woo_start_time', $this->export_settings->start_time );
            add_post_meta( $post_id, '_woo_idle_memory_start', $this->export_settings->idle_memory_start );
            add_post_meta( $post_id, '_woo_data_memory_start', $this->export_settings->data_memory_start );
            add_post_meta( $post_id, '_woo_idle_memory_end', Helper::get_current_memory_usage() - $this->export_settings->idle_memory_start );
            add_post_meta( $post_id, '_woo_columns', $this->export_settings->total_columns );
        }

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
     * Resolve any stored or posted Escape formatting value to one of the known modes.
     *
     * Mirrors normalize_header_formatting(): called once in
     * setup_export_settings() rather than at each write site, so every surface
     * agrees about what a stored value means — including what an unset option
     * defaults to.
     *
     * Idempotent, so a caller may safely re-feed it its own result.
     *
     * Unknown values fail safe to DEFAULT_ESCAPE_FORMATTING rather than falling
     * through to the permissive `none`: a hand-edited option or a bad REST client
     * must never be the thing that turns quoting off and starts writing corrupt
     * delimited files.
     *
     * `self::` deliberately, not `static::`. The external caller
     * (Settings_Controller) names Abstract_Exporter explicitly, so a subclass
     * override would reach Quick Export but not the settings screens — the exact
     * cross-surface disagreement this normaliser exists to eliminate.
     *
     * @since 2.8.2
     * @access public
     *
     * @param mixed $value The per-export value, or an empty string to fall back to the stored setting.
     * @return string One of ESCAPE_FORMATTING_MODES.
     */
    public static function normalize_escape_formatting( $value ) {
        // A non-scalar (a stray `escape_formatting[]` in the posted form data) or
        // an empty value means "not specified" — fall back to the stored setting.
        if ( ! is_scalar( $value ) || '' === $value ) {
            return self::stored_escape_formatting();
        }

        return self::match_escape_formatting_mode( $value );
    }

    /**
     * Match one non-empty scalar against the known Escape formatting modes.
     *
     * Extracted so normalize_escape_formatting() and stored_escape_formatting()
     * share one definition of what counts as a mode rather than each carrying a
     * copy that could drift — and so neither has to call the other, which would
     * put a mutual recursion one careless guard-edit away.
     *
     * Case and surrounding whitespace are forgiven because a hand-edited option
     * can reach here; anything still unrecognised resolves to the fail-safe
     * default.
     *
     * @since 2.8.2
     * @access private
     *
     * @param scalar $value A non-empty scalar to match.
     * @return string One of ESCAPE_FORMATTING_MODES.
     */
    private static function match_escape_formatting_mode( $value ) {
        $value = strtolower( trim( (string) $value ) );

        return in_array( $value, self::ESCAPE_FORMATTING_MODES, true ) ? $value : self::DEFAULT_ESCAPE_FORMATTING;
    }

    /**
     * The stored global Escape formatting mode.
     *
     * A named wrapper over the "no per-export value" case, whose bare empty
     * string reads as a placeholder at the call site rather than as "fall back to
     * the stored setting". Keeps the option keys and the default in one place.
     *
     * Reconciles the two option keys this control has been split across. The
     * canonical `escape_formatting` wins whenever it holds a value, because it is
     * the key the legacy settings screen writes. Only when it was never written
     * does the SPA's orphan `field_escape` apply — such a store has never used
     * the legacy settings screen, so there is no competing intent to override,
     * and adopting it is what makes the SPA control work at all. The orphan is
     * adopted only when it holds `all` or `excel`; an orphan `none` falls back
     * to the default.
     *
     * @since 2.8.2
     * @access public
     *
     * @return string One of ESCAPE_FORMATTING_MODES.
     */
    public static function stored_escape_formatting() {
        // woo_ce_get_option() collapses a missing option and one stored as an
        // empty string to the supplied default, so '' means "never written" for
        // both keys without a second get_option() round trip.
        $stored = woo_ce_get_option( 'escape_formatting', '' );

        if ( '' === $stored ) {
            $stored = woo_ce_get_option( self::ESCAPE_FORMATTING_LEGACY_SPA_OPTION, '' );

            // The orphan value never had an effect, so it is weak evidence of
            // intent. Adopt it only when it is a safe mode. `none` can corrupt a
            // row that holds the delimiter, so a store must choose it again on
            // purpose.
            if ( ! in_array( $stored, array( 'all', 'excel' ), true ) ) {
                $stored = '';
            }
        }

        if ( ! is_scalar( $stored ) || '' === $stored ) {
            return self::DEFAULT_ESCAPE_FORMATTING;
        }

        return self::match_escape_formatting_mode( $stored );
    }

    /**
     * Sanitize a map of per-field column-label overrides.
     *
     * Keys are export-field slugs and values are the store owner's replacement
     * column headers, so both sides are cleaned as free text. The key is not put
     * through sanitize_key(): registered field names are case-sensitive — the
     * Review type exports `comment_ID` and `comment_author_IP` — and
     * lower-casing them would stop the override matching its column at all. An
     * entry whose label reduces to an empty string is dropped rather than kept,
     * because a blank header is never what a rename meant and the field's
     * default label is the correct fallback.
     *
     * @since 2.8.2
     * @access private
     *
     * @param mixed $labels Raw overrides, keyed by field slug. Anything but an array yields an empty map.
     * @return array Sanitized overrides, keyed by field slug.
     */
    private static function sanitize_field_labels( $labels ) {
        if ( ! is_array( $labels ) ) {
            return array();
        }

        $sanitized = array();

        foreach ( $labels as $name => $label ) {
            if ( ! is_scalar( $label ) ) {
                continue;
            }

            $label = sanitize_text_field( (string) $label );

            if ( '' === $label ) {
                continue;
            }

            $sanitized[ sanitize_text_field( (string) $name ) ] = $label;
        }

        return $sanitized;
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
     * Write one CSV/TSV row, honouring the Escape formatting setting.
     *
     * Every mode goes through this one writer. fputcsv() is not used: with a
     * backslash escape character it does not double a quote that follows a
     * backslash, so `a\",=HYPERLINK(...)` ended the cell early for an RFC 4180
     * reader and put the formula at the start of a new cell. Here every
     * embedded quote is doubled and the backslash has no special meaning.
     *
     * The line terminator is "\n" in every mode, matching what fputcsv() wrote,
     * so switching modes changes quoting and nothing else.
     *
     * @since 2.8.2
     * @access protected
     *
     * @param array  $fields          The row's cell values, in column order.
     * @param string $mode            One of ESCAPE_FORMATTING_MODES, from delimited_escape_mode().
     * @param bool   $escape_formulas Whether to neutralise formula pieces of an unenclosed `none` cell.
     * @return bool True on success, false if the write failed.
     */
    protected function write_delimited_row( $fields, $mode, $escape_formulas ) {
        $delimiter = $this->_get_delimiter();
        $cells     = array();

        // `excel` keeps the cells fputcsv() quoted: the delimiter, a quote, a
        // backslash, a line break, a tab or a space. `none` quotes only a quote
        // or a line break (see below). `all` quotes every cell.
        $excel_triggers = $delimiter . "\"\\\r\n\t ";
        $none_triggers  = "\"\r\n";

        foreach ( $fields as $field ) {
            $field = (string) $field;

            if ( 'all' === $mode ) {
                $must_enclose = true;
            } elseif ( 'excel' === $mode ) {
                $must_enclose = '' !== $field && false !== strpbrk( $field, $excel_triggers );
            } else {
                // SECURITY: in `none`, a value that carries a line break or a
                // quote is still enclosed. escape_formula_value() only tests the
                // FIRST character of a cell. A raw line break ends the record, and
                // a raw quote opens a quoted field that the next enclosed cell's
                // quote closes. Either way, text the offset-0 test never saw lands
                // at the start of a new record or cell and evaluates on open.
                // Enclosing keeps the payload inside one cell.
                //
                // Enclosing rather than neutralising is deliberate: it leaves the
                // bytes exactly as stored, which is what "do not escape any cells"
                // promises, where inserting an apostrophe would not.
                //
                // A cell containing only the delimiter is still written raw and will
                // still split the row. That is the documented, data-preserving hazard
                // of the mode and the reason both settings screens caution against it.
                $must_enclose = false !== strpbrk( $field, $none_triggers );

                // An unenclosed cell is read by a spreadsheet as several fields
                // when it carries the delimiter. Each piece is a new cell start the
                // offset-0 test never saw, so every piece is neutralised on its own.
                if ( ! $must_enclose && $escape_formulas ) {
                    $field = $this->escape_formula_pieces( $field, $delimiter );
                }
            }

            // Doubling the embedded quote before wrapping (RFC 4180) is what stops
            // a quoted cell terminating early, so the file stays parseable.
            $cells[] = $must_enclose ? '"' . str_replace( '"', '""', $field ) . '"' : $field;
        }

        // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
        $written = fwrite( $this->file, implode( $delimiter, $cells ) . "\n" );
        // phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fwrite

        return false !== $written;
    }

    /**
     * The Escape formatting mode for this export's delimited writer.
     *
     * Resolved here rather than read straight off the settings object, which is
     * not guaranteed to have been through setup_export_settings(): a batch
     * resumed from the settings transient (which may have been written by an
     * earlier plugin version) or a third-party `wsed_export_settings` filter can
     * both put a raw or empty value on the property. Left untested, an empty
     * value would miss the `excel` and `all` branches and silently write the
     * whole file unquoted — the one mode that can corrupt it.
     *
     * Callers read it once per header or chunk, not once per row.
     *
     * @since 2.8.2
     * @access protected
     *
     * @return string One of ESCAPE_FORMATTING_MODES.
     */
    protected function delimited_escape_mode() {
        return self::normalize_escape_formatting( $this->export_settings->escape_formatting ?? '' );
    }

    /**
     * Neutralise every piece of an unenclosed cell that a spreadsheet would read
     * as the start of a field.
     *
     * Splits on the delimiter, then runs each piece through
     * escape_formula_value(). A cell without the delimiter is one piece, so the
     * result matches escape_formula_value() for it. An unenclosed cell never
     * holds a quote, because write_delimited_row() encloses any cell that does.
     *
     * @since 2.8.2
     * @access private
     *
     * @param string $value     Cell value.
     * @param string $delimiter Column delimiter.
     * @return string Cell value with each piece neutralised.
     */
    private function escape_formula_pieces( $value, $delimiter ) {
        if ( '' === $delimiter || false === strpos( $value, $delimiter ) ) {
            return $this->escape_formula_value( $value );
        }

        return implode( $delimiter, array_map( array( $this, 'escape_formula_value' ), explode( $delimiter, $value ) ) );
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
     * @since 2.8.2 `$export->escape_formatting` is now guaranteed to be one of
     *              `all`, `excel` or `none`. It previously carried the raw
     *              stored value, which could be an empty string or any
     *              hand-edited text. Resolve a value of your own through
     *              Abstract_Exporter::normalize_escape_formatting().
     * @since 2.8.2 `$export->field_labels` is new: the per-export column-label
     *              overrides the caller sent, keyed by field slug and already
     *              applied to `$export->columns`. Empty when the caller sent
     *              none.
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
        $export->escape_formatting  = self::normalize_escape_formatting( $settings['escape_formatting'] ?? '' );
        $export->batch_size         = $settings['batch_size'] ?? get_option( WOO_CE_PREFIX . '_batch_size', WSED_DEFAULT_BATCH_SIZE );
        $export->export_template    = ( isset( $settings['export_template'] ) ? absint( $settings['export_template'] ) : false );
        $export->type               = ( isset( $settings['dataset'] ) ? sanitize_text_field( $settings['dataset'] ) : false );
        $export->limit_volume       = ( isset( $settings['limit_volume'] ) ? sanitize_text_field( $settings['limit_volume'] ) : '' );
        $export->offset             = ( isset( $settings['offset'] ) ? sanitize_text_field( $settings['offset'] ) : '' );
        $export->fields             = ( isset( $settings[ $export->type . '_fields' ] ) && is_array( $settings[ $export->type . '_fields' ] ) ? array_map( 'sanitize_text_field', $settings[ $export->type . '_fields' ] ) : array() );
        $export->fields_order       = ( isset( $settings[ $export->type . '_fields_order' ] ) && is_array( $settings[ $export->type . '_fields_order' ] ) ? array_map( 'absint', $settings[ $export->type . '_fields_order' ] ) : false );
        $export->field_labels       = self::sanitize_field_labels( $settings[ $export->type . '_labels' ] ?? array() );

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
            'date_format'  => \VisserLabs\WSE\Helpers\Export::get_date_format(),
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
     * @since 2.8.2 Applies `$export->field_labels`, the per-export column-label
     *              overrides, on top of any the Export Template has stored — and
     *              does so AFTER `wsed_override_export_columns`, so a consumer of
     *              that filter which rebuilds the column map cannot discard the
     *              rename. An integration that needs the last word on a column
     *              label must therefore hook `wsed_export_settings` and edit
     *              `$export->field_labels` instead.
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

        $columns = apply_filters( 'wsed_override_export_columns', $columns, $fields, $export );

        // Override labels the caller renamed for this export only.
        //
        // Applied last, after the filter above, for two reasons.
        //
        // A consumer of that filter can rebuild $columns from the field defaults
        // rather than edit what it was handed — Order does so for its item
        // columns ({@see \VisserLabs\WSE\Classes\Export\Order::export_columns()}).
        // An override applied before the filter is discarded on that path.
        //
        // Running last also keeps the rename ahead of the Export Template block
        // above, which is the right way round: the Quick Export SPA populates
        // this map only from the store owner's own inline chip rename or Manage
        // Fields edit, made after the template was loaded, so it is the later
        // and more specific instruction.
        //
        // Without any of this the rename reached the server and was silently
        // dropped, and the file kept the field's default header.
        if ( ! empty( $export->field_labels ) ) {
            $export_format = isset( $export->export_format ) ? $export->export_format : '';

            foreach ( $export->field_labels as $key => $label ) {
                // A nested value is a column GROUP a filter consumer built, never
                // a single header one rename can stand in for.
                //
                // Item columns are the known gap here: the Order exporter re-keys
                // them (`order_items_sku` becomes `order_items_1_sku`), so a rename
                // on one has no matching key left to find.
                if ( ! isset( $columns[ $key ] ) || is_array( $columns[ $key ] ) ) {
                    continue;
                }

                // Keep the field's default label when this format's writer would
                // derive a broken identifier from the rename. Silently, because
                // that is exactly the behaviour before this override existed —
                // those writers only ever saw the default label — so no export
                // that works today starts failing.
                if ( ! self::label_is_usable_by_writer( $label, $export_format ) ) {
                    continue;
                }

                $columns[ $key ] = $label;
            }
        }

        return $columns;
    }

    /**
     * Whether a column label can serve as an identifier for the given format's writer.
     *
     * The XML, RSS and JSON writers do not print the column label — they turn it
     * into an element name or an object key with sanitize_key() — so a label that
     * reduces to something unusable there breaks the file rather than merely
     * reading oddly:
     *
     * - A label carrying no ASCII alphanumerics, `商品名` for one, reduces to an
     *   empty string. SimpleXMLElement::addChild() throws a ValueError on that,
     *   and a JSON export collapses every such column onto the `""` key.
     * - A label opening with a digit or a hyphen, `2024 Total` for one, reduces
     *   to `2024total`, which is not a valid XML name.
     *
     * Every other format prints the label verbatim and constrains it in no way.
     *
     * @since 2.8.2
     * @access private
     *
     * @param string $label         The replacement column label.
     * @param string $export_format The export format slug.
     * @return bool False only when the format derives an identifier from the label and this label cannot form a valid one.
     */
    private static function label_is_usable_by_writer( $label, $export_format ) {
        $key = sanitize_key( $label );

        switch ( $export_format ) {
            case 'xml':
            case 'rss':
                // An XML element name has to open with a letter or an underscore,
                // and sanitize_key() has already reduced the label to [a-z0-9_-].
                return 1 === preg_match( '/^[a-z_]/', $key );
            case 'json':
                return '' !== $key;
            default:
                return true;
        }
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
     * Set whether the export settings belong to the file this instance serves.
     *
     * @since 2.8.2
     * @access public
     *
     * @param bool $matches False when the settings belong to another export.
     */
    public function set_settings_match_file( $matches ) {
        $this->settings_match_file = (bool) $matches;
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
