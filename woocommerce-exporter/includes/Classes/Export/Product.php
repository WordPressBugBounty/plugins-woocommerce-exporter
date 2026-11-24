<?php
/**
 * Author: Rymera Web Co
 *
 * @package VisserLabs\WSE\Classes\Export
 */

namespace VisserLabs\WSE\Classes\Export;

use VisserLabs\WSE\Abstracts\Abstract_Class;
use VisserLabs\WSE\Traits\Singleton_Trait;
use VisserLabs\WSE\Helpers\Export as Export_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Product export type class.
 *
 * @since 2.7.3
 */
class Product extends Abstract_Class {

    use Singleton_Trait;

    /**
     * The export type.
     *
     * @var string
     */
    private $export_type = 'product';

    /**
     * Prefix of a value source that names a post meta key rather than a post field.
     *
     * @since 2.8.3
     * @var string
     */
    const META_SOURCE_PREFIX = 'meta:';

    /**
     * The default number of product rows the "Then by" sort may build in one
     * prepare request. See the `woo_ce_product_sort_build_limit` filter.
     *
     * @since 2.8.3
     */
    const DEFAULT_SORT_BUILD_LIMIT = 1000;

    /**
     * The number of product rows the "Then by" sort can still build in this request.
     *
     * @since 2.8.3
     * @var int
     */
    private $sort_build_budget = 0;

    /**
     * The first IDs of the "Then by" groups that start with a Variation (its
     * Parent is not exported), keyed by ID. Set by group_variations().
     *
     * @since 2.8.3
     * @var array<int, true>
     */
    private $variation_heads = array();

    /**
     * Primary sorts that leave no ties for the "Then by" levels to break.
     *
     * @since 2.8.3
     * @var string[]
     */
    const NO_TIE_PRIMARY_SORTS = array( 'ID', 'rand' );

    /**
     * Primary sorts whose ties the "Then by" levels break, mapped to the value
     * source the primary value is read from.
     *
     * @since 2.8.3
     * @var array<string, string>
     */
    const PRIMARY_TIE_SOURCES = array(
        'title'      => 'post_title',
        'date'       => 'post_date',
        'modified'   => 'post_modified',
        'menu_order' => 'menu_order',
        'sku'        => 'meta:_sku',
    );

    /**
     * Level columns sorted by their stored value rather than the exported one:
     * dates sort chronologically, and the full product row is not built.
     *
     * @since 2.8.3
     * @var array<string, string>
     */
    const RAW_LEVEL_SOURCES = array(
        'product_id'            => 'ID',
        'sku'                   => 'meta:_sku',
        'post_date'             => 'post_date',
        'post_modified'         => 'post_modified',
        'sale_price_dates_from' => 'meta:_sale_price_dates_from',
        'sale_price_dates_to'   => 'meta:_sale_price_dates_to',
    );

    /**
     * Level columns exported as formatted prices (woo_ce_format_price()), core
     * and integration columns alike. Filterable with wsed_product_sort_price_fields.
     *
     * @since 2.8.3
     * @var string[]
     */
    const PRICE_LEVEL_FIELDS = array(
        'price',
        'sale_price',
        'net_price',
        'msrp',
        'unit_price_regular',
        'unit_price_sale',
        'cost_of_goods',
        'cost_of_goods_sold',
        'cost_of_goods_sold_total',
        'wholesale_price',
        'pre_orders_fee',
        'posr',
        'purchase_price',
        'deposit_amount',
    );

    /**
     * Patterns of the formatted price columns named per role or currency
     * ("{role}_wholesale_price", "wcae_regular_price_{currency}", …).
     *
     * @since 2.8.3
     * @var string[]
     */
    const PRICE_LEVEL_FIELD_PATTERNS = array(
        '/_wholesale_price$/',
        '/^wcae_(?:regular|sale)_price_/',
    );

    /**
     * The core price columns, mapped to the stored meta a product that is not
     * variable exports them from, so they are read without building the row.
     * Net price is the sale price when there is one, else the regular price.
     *
     * @since 2.8.3
     * @var array<string, string[]>
     */
    const STORED_PRICE_LEVEL_SOURCES = array(
        'price'      => array( '_regular_price' ),
        'sale_price' => array( '_sale_price' ),
        'net_price'  => array( '_sale_price', '_regular_price' ),
    );

    /**
     * Constructor.
     *
     * @var array
     */
    public function __construct() {}

    /**
     * Get default fields.
     *
     * @since 2.7.3
     * @access public
     *
     * @param int $post_id The post ID.
     * @return array
     */
    public function get_default_fields( $post_id = 0 ) {
        return woo_ce_get_product_fields( 'full', $post_id );
    }

    /**
     * Extend export dataset args.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array  $args     The export dataset args.
     * @param object $export   The export settings.
     * @param array  $settings Raw export settings obtained from the form or the post data.
     * @return array
     */
    public function extend_export_dataset_args( $args, $export, $settings ) {
        /**
         * Filter the dataset args.
         * This filter is old way of extending the dataset args.
         * Should be refactored in the future for better performance & readability.
         */
        if ( $export->scheduled_export ) {
            $args = apply_filters( 'woo_ce_extend_cron_dataset_args', $args, $export->post_id, $export->type, true );
        } else {
            $args = apply_filters( 'woo_ce_extend_dataset_args', $args, $export->type, $settings );
        }
        return $args;
    }

    /**
     * Get object ids.
     *
     * @since 2.7.3
     * @since 2.8.3 Re-orders the IDs by the "Then by" levels before the filter runs.
     * @access public
     *
     * @param object $export The export settings.
     * @return array
     */
    public function get_object_ids( $export ) {
        $object_ids = woo_ce_get_products( $export->args, $export );

        $object_ids = $this->sort_by_field_levels( (array) $object_ids, $export );

        /**
         * Filter the object IDs.
         *
         * @since 2.7.3
         * @since 2.8.3 Receives the IDs already re-ordered by the "Then by" levels.
         * @param array $object_ids  The object IDs.
         * @param array $export_args The export args.
         */
        return apply_filters( 'wsed_' . $this->export_type . '_object_ids', $object_ids, $export->args );
    }

    /**
     * Re-order the object IDs by up to two exported columns (the "Then by" levels).
     *
     * The query has already ordered the IDs by the primary "Sort by", so that order
     * is kept as-is: only runs of IDs that tie on the primary value (title, date
     * created, date modified, SKU, menu order) are re-sorted by the levels. An ID or
     * Random primary sort leaves no ties, so the levels change nothing; with the
     * default order the whole list is one run and the levels lead. An unknown
     * primary sort keeps the query order. The final tiebreak is the query
     * position, so the sort is stable. A Variation always stays with its Parent:
     * the levels order the Parent rows, and the Variations only inside their
     * Parent's group (see group_variations()).
     *
     * A level only applies when its field is an enabled export column. Most levels
     * read the stored value (see stored_level_value()): the RAW_LEVEL_SOURCES
     * columns, custom meta columns, the Product Name column, and the core price
     * columns of a product that is not variable. Only the other columns, and a variable product's price,
     * build the row with woo_ce_get_product_data(), which computes the full
     * product row inside the prepare request. The number of rows built is capped
     * by the `woo_ce_product_sort_build_limit` filter; above it the levels are not
     * applied and the query order is kept.
     *
     * @since 2.8.3
     * @access private
     *
     * @param int[]  $object_ids The product IDs in query order.
     * @param object $export     The export settings.
     * @return int[] The same IDs, re-ordered.
     */
    private function sort_by_field_levels( array $object_ids, $export ) {
        $args    = (array) $export->args;
        $columns = (array) $export->columns;
        $levels  = array();

        foreach ( array( 1, 2 ) as $level_number ) {
            $field = $args[ 'product_orderby_field' . $level_number ] ?? '';
            if ( ! is_string( $field ) || '' === $field || ! array_key_exists( $field, $columns ) ) {
                continue;
            }
            $direction = $args[ 'product_order_field' . $level_number ] ?? '';
            $levels[]  = array(
                'field' => $field,
                'desc'  => 'DESC' === strtoupper( (string) $direction ),
            );
        }

        $orderby = (string) ( $args['product_orderby'] ?? '' );
        if ( empty( $levels ) || count( $object_ids ) < 2 || in_array( $orderby, self::NO_TIE_PRIMARY_SORTS, true ) ) {
            return $object_ids;
        }

        // The ties of an unknown primary sort cannot be read, so its order stands.
        if ( '' !== $orderby && ! isset( self::PRIMARY_TIE_SOURCES[ $orderby ] ) ) {
            return $object_ids;
        }
        $primary_source = self::PRIMARY_TIE_SOURCES[ $orderby ] ?? '';

        // Group each Variation with the product row it follows (its Parent, or the
        // first Variation of that Parent when the Parent is not exported), so the
        // levels never move a Variation away from its Parent.
        $groups = $this->group_variations( $object_ids );

        // Split the query order into runs of groups that tie on the primary value of
        // the group's first row.
        $runs     = array();
        $last_key = null;
        foreach ( array_chunk( $groups, WSED_DEFAULT_BATCH_SIZE ) as $chunk ) {
            $heads = array_column( $chunk, 0 );
            if ( '' !== $primary_source ) {
                _prime_post_caches( $heads, false, $this->is_meta_source( $primary_source ) );
            }
            foreach ( $chunk as $group ) {
                $key = '' === $primary_source ? '' : $this->primary_tie_key( $this->read_source_value( $primary_source, $group[0] ) );
                if ( $key !== $last_key ) {
                    $runs[]   = array();
                    $last_key = $key;
                }
                $runs[ count( $runs ) - 1 ][] = $group;
            }
            $this->flush_runtime_cache( $heads );
        }

        /**
         * Filter the number of product rows the "Then by" sort may build in one request.
         *
         * A level column without a stored value (for example Stock, or the price
         * of a variable product) builds the full product row for each tied
         * product inside the prepare request. Above this limit the levels are
         * not applied and the primary sort order is kept, so a large catalog
         * cannot time out or run out of memory before the export starts.
         *
         * @since 2.8.3
         * @param int   $limit      The maximum number of product rows to build.
         * @param int[] $object_ids The product IDs in query order.
         */
        $this->sort_build_budget = max( 0, (int) apply_filters( 'woo_ce_product_sort_build_limit', self::DEFAULT_SORT_BUILD_LIMIT, $object_ids ) );

        $parts = array();
        foreach ( $runs as $run ) {
            $sorted = $this->sort_groups_by_levels( $run, $levels, $args, $export );
            if ( null === $sorted ) {
                $this->log_sort_build_limit( count( $object_ids ) );
                return $object_ids;
            }
            $parts[] = $sorted;
        }

        return array_merge( array(), ...$parts );
    }

    /**
     * Split the query order into groups: a product row followed by the Variations
     * that woo_ce_get_products() placed after it.
     *
     * A Variation joins the current group when the group belongs to the same
     * Parent: the group starts with that Parent, or with a sibling Variation (the
     * Parent itself is not exported). Every other row starts a new group.
     *
     * @since 2.8.3
     * @access private
     *
     * @param int[] $object_ids The product IDs in query order.
     * @return array<int, int[]> The groups, in query order.
     */
    private function group_variations( array $object_ids ): array {
        $groups                = array();
        $group_parent          = null;
        $this->variation_heads = array();
        foreach ( array_chunk( array_values( $object_ids ), WSED_DEFAULT_BATCH_SIZE ) as $chunk ) {
            _prime_post_caches( $chunk, false, false );
            foreach ( $chunk as $object_id ) {
                $post = get_post( $object_id );
                if ( $post && 'product_variation' === $post->post_type ) {
                    $parent_id = (int) $post->post_parent;
                    if ( null !== $group_parent && $parent_id === $group_parent ) {
                        $groups[ count( $groups ) - 1 ][] = $object_id;
                        continue;
                    }
                    $group_parent                        = $parent_id;
                    $this->variation_heads[ $object_id ] = true;
                } else {
                    $group_parent = (int) $object_id;
                }
                $groups[] = array( $object_id );
            }
            $this->flush_runtime_cache( $chunk );
        }
        return $groups;
    }

    /**
     * Sort one run of tied groups by the "Then by" levels.
     *
     * The groups are ordered by the level values of their first row. Inside a
     * group, a Parent stays first and only its Variations are re-sorted.
     *
     * @since 2.8.3
     * @access private
     *
     * @param array<int, int[]> $run    Groups that tie on the primary sort, in query order.
     * @param array             $levels The levels, each { field, desc }.
     * @param array             $args   The export dataset args.
     * @param object            $export The export settings.
     * @return int[]|null The IDs of the run, re-ordered, or null when the sort needs
     *                    more product rows than the build limit allows.
     */
    private function sort_groups_by_levels( array $run, array $levels, array $args, $export ): ?array {
        if ( count( $run ) > 1 ) {
            $heads  = array_column( $run, 0 );
            $sorted = $this->sort_run_by_levels( $heads, $levels, $args, $export );
            if ( null === $sorted ) {
                return null;
            }
            $by_head = array();
            foreach ( $run as $group ) {
                $by_head[ $group[0] ] = $group;
            }
            $run = array();
            foreach ( $sorted as $head ) {
                $run[] = $by_head[ $head ];
            }
        }

        $ids = array();
        foreach ( $run as $group ) {
            $head       = array_shift( $group );
            $is_parent  = ! isset( $this->variation_heads[ $head ] );
            $variations = $is_parent ? $group : array_merge( array( $head ), $group );
            if ( count( $variations ) > 1 ) {
                $variations = $this->sort_run_by_levels( $variations, $levels, $args, $export );
                if ( null === $variations ) {
                    return null;
                }
            }
            if ( $is_parent ) {
                $ids[] = $head;
            }
            array_push( $ids, ...$variations );
        }
        return $ids;
    }

    /**
     * Record that the "Then by" levels were not applied because of the build limit.
     *
     * @since 2.8.3
     * @access private
     *
     * @param int $product_count The number of products in the export.
     * @return void
     */
    private function log_sort_build_limit( int $product_count ) {
        if ( ! function_exists( 'wc_get_logger' ) ) {
            return;
        }
        wc_get_logger()->warning(
            sprintf(
                'Store Exporter did not apply the Product "Then by" sort to %d products: it needs more product rows than the woo_ce_product_sort_build_limit filter allows. The primary sort order is used.',
                $product_count
            ),
            array( 'source' => 'woocommerce-exporter' )
        );
    }

    /**
     * Normalise a primary sort value so values the database orders as equal
     * (case- and accent-insensitive titles) fall into the same tie run.
     *
     * @since 2.8.3
     * @access private
     *
     * @param string $value The raw post field value.
     * @return string
     */
    private function primary_tie_key( string $value ): string {
        return strtolower( remove_accents( trim( $value ) ) );
    }

    /**
     * Whether a value source names a post meta key ("meta:<key>") rather than a post field.
     *
     * @since 2.8.3
     * @access private
     *
     * @param string $source The value source.
     * @return bool
     */
    private function is_meta_source( string $source ): bool {
        return 0 === strpos( $source, self::META_SOURCE_PREFIX );
    }

    /**
     * Read the stored value of a product from a value source.
     *
     * @since 2.8.3
     * @access private
     *
     * @param string $source    A post field name, or "meta:<key>" for a post meta key.
     * @param int    $object_id The product ID.
     * @return string
     */
    private function read_source_value( string $source, $object_id ): string {
        if ( $this->is_meta_source( $source ) ) {
            $value = get_post_meta( $object_id, substr( $source, strlen( self::META_SOURCE_PREFIX ) ), true );
        } else {
            $value = get_post_field( $source, $object_id );
        }
        return is_scalar( $value ) ? (string) $value : '';
    }

    /**
     * Release the cached post and post meta of a batch of products, so priming a
     * batch at a time does not keep every product of a large catalog in memory.
     *
     * With a persistent object cache, the in-memory copy is flushed: the data
     * stays in the persistent cache. Without one, the whole cache lives in
     * memory (also the options and the data of other plugins), so only the
     * entries of the batch are deleted.
     *
     * @since 2.8.3
     * @access private
     *
     * @param int[] $object_ids The product IDs of the batch.
     * @return void
     */
    private function flush_runtime_cache( array $object_ids ) {
        // wp_cache_flush_runtime() is available from WordPress 6.0.
        if ( wp_using_ext_object_cache() && function_exists( 'wp_cache_flush_runtime' ) ) {
            wp_cache_flush_runtime();
            return;
        }
        foreach ( $object_ids as $object_id ) {
            wp_cache_delete( (int) $object_id, 'posts' );
            wp_cache_delete( (int) $object_id, 'post_meta' );
        }
    }

    /**
     * Sort one run of tied product IDs by the "Then by" levels.
     *
     * @since 2.8.3
     * @access private
     *
     * @param int[]  $run    Product IDs that tie on the primary sort, in query order.
     * @param array  $levels The levels, each { field, desc }.
     * @param array  $args   The export dataset args.
     * @param object $export The export settings.
     * @return int[]|null The same IDs, re-ordered, or null when the sort needs more
     *                    product rows than the build limit allows.
     */
    private function sort_run_by_levels( array $run, array $levels, array $args, $export ): ?array {
        $level_fields      = wp_list_pluck( $levels, 'field' );
        $custom_meta_keys  = $this->custom_meta_level_fields( $level_fields );
        $reads_stored_type = ! empty( array_intersect( $level_fields, array_keys( self::STORED_PRICE_LEVEL_SOURCES ) ) );

        // Collect the level values once per product, priming the caches a batch at a time.
        $values = array();
        foreach ( array_chunk( $run, WSED_DEFAULT_BATCH_SIZE, true ) as $chunk ) {
            _prime_post_caches( array_values( $chunk ), $reads_stored_type );
            foreach ( $chunk as $position => $object_id ) {
                $data  = null;
                $built = false;
                $row   = array();
                foreach ( $level_fields as $field ) {
                    $value = $this->stored_level_value( $field, $object_id, $custom_meta_keys );
                    if ( null === $value ) {
                        // Only a column without a stored source builds the full row, once per product.
                        if ( ! $built ) {
                            if ( $this->sort_build_budget < 1 ) {
                                return null;
                            }
                            --$this->sort_build_budget;
                            $data  = woo_ce_get_product_data( $object_id, $args, $level_fields, $export );
                            $built = true;
                        }
                        $text  = $this->exported_level_value( $data, $field );
                        $value = array(
                            'number' => $this->level_value_number( $text, $field ),
                            'text'   => $text,
                        );
                    }
                    $row[] = $value;
                }
                $values[ $position ] = $row;
            }
            $this->flush_runtime_cache( $chunk );
        }

        $positions = array_keys( $run );
        usort(
            $positions,
            function ( $a, $b ) use ( $values, $levels ) {
                foreach ( $levels as $index => $level ) {
                    $result = $this->compare_level_values( $values[ $a ][ $index ], $values[ $b ][ $index ] );
                    if ( 0 !== $result ) {
                        return $level['desc'] ? -$result : $result;
                    }
                }
                return $a <=> $b;
            }
        );

        return array_map(
            function ( $position ) use ( $run ) {
                return $run[ $position ];
            },
            $positions
        );
    }

    /**
     * The level columns that are custom Product meta keys (the "custom_products"
     * setting), which the export fills last and so take precedence over a core
     * column of the same name.
     *
     * @since 2.8.3
     * @access private
     *
     * @param string[] $level_fields The level columns.
     * @return string[] The level columns that are custom meta keys.
     */
    private function custom_meta_level_fields( array $level_fields ): array {
        $custom_meta_keys = woo_ce_get_option( 'custom_products', '' );
        if ( ! is_array( $custom_meta_keys ) ) {
            return array();
        }
        return array_values( array_intersect( $level_fields, array_filter( $custom_meta_keys ) ) );
    }

    /**
     * Read a level value from what is stored, without building the product row,
     * the same way woo_ce_get_product_data() exports it.
     *
     * Covers the RAW_LEVEL_SOURCES columns, custom meta columns, the Product
     * Name column, and the core price columns of a product that is not variable
     * (a variable product's price is a range the export computes from its
     * variations).
     *
     * @since 2.8.3
     * @access private
     *
     * @param string   $field            The level column.
     * @param int      $object_id        The product ID.
     * @param string[] $custom_meta_keys The level columns that are custom meta keys.
     * @return array|null The value, { number, text }, or null when the row must be built.
     */
    private function stored_level_value( string $field, $object_id, array $custom_meta_keys ): ?array {
        if ( in_array( $field, $custom_meta_keys, true ) ) {
            $text = $this->custom_meta_level_text( $field, $object_id );
            return array(
                'number' => $this->level_value_to_number( $text, false ),
                'text'   => $text,
            );
        }

        if ( isset( self::RAW_LEVEL_SOURCES[ $field ] ) ) {
            $text = $this->read_source_value( self::RAW_LEVEL_SOURCES[ $field ], $object_id );
            return array(
                'number' => $this->level_value_number( $text, $field ),
                'text'   => $text,
            );
        }

        // The Product Name column is the filtered title (woo_ce_get_product_data()
        // fills it the same way), so it is read here without building the row.
        // The default primary sort makes the whole catalog one tie run, and Name
        // is the most common level: building the full row for each product in
        // the prepare request made a large catalog time out at the proxy.
        if ( 'name' === $field ) {
            $text = (string) woo_ce_format_post_title( get_the_title( $object_id ) );
            return array(
                'number' => $this->level_value_number( $text, $field ),
                'text'   => $text,
            );
        }

        if ( isset( self::STORED_PRICE_LEVEL_SOURCES[ $field ] ) && ! has_term( 'variable', 'product_type', $object_id ) ) {
            $text = '';
            foreach ( self::STORED_PRICE_LEVEL_SOURCES[ $field ] as $meta_key ) {
                $text = $this->read_source_value( self::META_SOURCE_PREFIX . $meta_key, $object_id );
                if ( '' !== $text ) {
                    break;
                }
            }
            // Format the stored price like the export, so it rounds to the same decimals and ties the same way.
            if ( '' !== $text ) {
                $text = (string) woo_ce_format_price( $text );
            }
            return array(
                'number' => $this->level_value_number( $text, $field ),
                'text'   => $text,
            );
        }

        return null;
    }

    /**
     * Read a custom meta column the way the export fills it: the formatted meta
     * value, falling back to the parent's for a variation when Variation
     * Formatting is on.
     *
     * @since 2.8.3
     * @access private
     *
     * @param string $meta_key  The custom meta key (also the column).
     * @param int    $object_id The product ID.
     * @return string
     */
    private function custom_meta_level_text( string $meta_key, $object_id ): string {
        $value = woo_ce_format_custom_meta( get_post_meta( $object_id, $meta_key, true ) );
        if ( '' == $value && 'product_variation' === get_post_type( $object_id ) && woo_ce_get_option( 'variation_formatting', 0 ) ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- Mirrors the export's own empty check.
            $value = woo_ce_format_custom_meta( get_post_meta( wp_get_post_parent_id( $object_id ), $meta_key, true ) );
        }
        return is_scalar( $value ) ? (string) $value : '';
    }

    /**
     * Read one column of a built product row as a string.
     *
     * @since 2.8.3
     * @access private
     *
     * @param object|null $data  The product row from woo_ce_get_product_data().
     * @param string      $field The column.
     * @return string
     */
    private function exported_level_value( $data, string $field ): string {
        $value = ( is_object( $data ) && isset( $data->{$field} ) ) ? $data->{$field} : '';
        if ( is_array( $value ) ) {
            $value = implode( ', ', $value );
        }
        return is_scalar( $value ) ? (string) $value : '';
    }

    /**
     * Compare two level values: numbers (formatted prices included) in numeric
     * order, ahead of every other value, which compare in natural,
     * case-insensitive order.
     *
     * Numbers always lead so the order stays consistent when a run mixes them
     * with text (an empty value, a non-numeric column): comparing such a pair
     * by text would make usort() inconsistent. A price range already reads as
     * the number of its low end (see level_value_number()).
     *
     * @since 2.8.3
     * @access private
     *
     * @param array $left  The first value, { number, text }.
     * @param array $right The second value, { number, text }.
     * @return int Negative, zero or positive, like strcmp().
     */
    private function compare_level_values( array $left, array $right ): int {
        $left_is_number  = null !== $left['number'];
        $right_is_number = null !== $right['number'];
        if ( $left_is_number && $right_is_number ) {
            return $left['number'] <=> $right['number'];
        }
        if ( $left_is_number !== $right_is_number ) {
            return $left_is_number ? -1 : 1;
        }
        return strnatcasecmp( $left['text'], $right['text'] );
    }

    /**
     * Whether a level column is exported as a formatted price.
     *
     * @since 2.8.3
     * @access private
     *
     * @param string $field The level column.
     * @return bool
     */
    private function is_price_level_field( string $field ): bool {
        static $price_fields = null;

        if ( null === $price_fields ) {
            /**
             * Filter the Product columns a "Then by" level reads as formatted
             * prices, undoing the store's thousands and decimal separators.
             *
             * @since 2.8.3
             * @param string[] $price_fields The price columns.
             */
            $price_fields = (array) apply_filters( 'wsed_product_sort_price_fields', self::PRICE_LEVEL_FIELDS );
        }
        if ( in_array( $field, $price_fields, true ) ) {
            return true;
        }
        foreach ( self::PRICE_LEVEL_FIELD_PATTERNS as $pattern ) {
            if ( preg_match( $pattern, $field ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Read a level value as a number for sorting.
     *
     * A variable product with differing variation prices exports its price as a
     * range ("10.00-20.00"); a price column sorts that range by its low end so it
     * sits among the simple products instead of after every number. The range is
     * read with the export's own range formats (see price_range_formats()).
     *
     * @since 2.8.3
     * @access private
     *
     * @param string $value The level value.
     * @param string $field The level column.
     * @return float|null The number, or null when the value is not numeric.
     */
    private function level_value_number( string $value, string $field ): ?float {
        $is_price = $this->is_price_level_field( $field );
        $number   = $this->level_value_to_number( $value, $is_price );
        if ( null !== $number || ! $is_price || strlen( $value ) < 2 ) {
            return $number;
        }

        foreach ( $this->price_range_formats( $field ) as $format ) {
            $low = $this->price_range_low_number( $value, $format );
            if ( null !== $low ) {
                return $low;
            }
        }
        return null;
    }

    /**
     * The filtered formats the export builds a variable price range with, split
     * around their two "%s" placeholders as { prefix, separator, suffix }.
     * Net price holds the sale range when there is one, else the regular range,
     * so it tries both.
     *
     * @since 2.8.3
     * @access private
     *
     * @param string $field The price column.
     * @return array[] The range formats; a format without two placeholders is left out.
     */
    private function price_range_formats( string $field ): array {
        static $formats = null;

        if ( null === $formats ) {
            $formats = array();
            foreach ( array(
                'sale'    => apply_filters( 'woo_ce_product_variable_sale_price', '%s-%s' ),
                'regular' => apply_filters( 'woo_ce_product_variable_price', '%s-%s' ),
            ) as $kind => $format ) {
                $parts = explode( '%s', (string) $format );
                if ( 3 === count( $parts ) && '' !== $parts[1] ) {
                    $formats[ $kind ] = array(
                        'prefix'    => $parts[0],
                        'separator' => $parts[1],
                        'suffix'    => $parts[2],
                    );
                }
            }
        }

        switch ( $field ) {
            case 'sale_price':
                $kinds = array( 'sale' );
                break;
            case 'net_price':
                $kinds = array( 'sale', 'regular' );
                break;
            default:
                $kinds = array( 'regular' );
        }
        return array_values( array_intersect_key( $formats, array_flip( $kinds ) ) );
    }

    /**
     * Read the low end of a formatted price range.
     *
     * The prefix and suffix must match; the range then splits at the first
     * separator that leaves a price on both sides, so a separator that also
     * appears inside a price ("1,00,2,00" with "," for decimals) splits right.
     *
     * @since 2.8.3
     * @access private
     *
     * @param string $value  The level value.
     * @param array  $format The range format, { prefix, separator, suffix }.
     * @return float|null The low price, or null when the value is not such a range.
     */
    private function price_range_low_number( string $value, array $format ): ?float {
        $prefix_length = strlen( $format['prefix'] );
        $suffix_length = strlen( $format['suffix'] );
        if (
            strlen( $value ) <= $prefix_length + $suffix_length ||
            0 !== strncmp( $value, $format['prefix'], $prefix_length ) ||
            ( $suffix_length && substr( $value, -$suffix_length ) !== $format['suffix'] )
        ) {
            return null;
        }

        $range     = substr( $value, $prefix_length, strlen( $value ) - $prefix_length - $suffix_length );
        $separator = $format['separator'];
        $offset    = 1;
        while ( false !== ( $split_at = strpos( $range, $separator, $offset ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition -- Walks every separator position.
            $low  = $this->level_value_to_number( trim( substr( $range, 0, $split_at ) ), true );
            $high = $this->level_value_to_number( trim( substr( $range, $split_at + strlen( $separator ) ) ), true );
            if ( null !== $low && null !== $high ) {
                return $low;
            }
            $offset = $split_at + 1;
        }
        return null;
    }

    /**
     * Read a level value as a number, undoing the export's price formatting
     * (thousands and decimal separators) when needed.
     *
     * A price formatted with a "." thousands separator ("1.000" for one thousand)
     * also passes is_numeric() as 1.0, so a price column always goes through the
     * separators when the thousands separator is ".".
     *
     * @since 2.8.3
     * @access private
     *
     * @param string $value    The level value.
     * @param bool   $is_price Whether the value comes from a formatted price column.
     * @return float|null The number, or null when the value is not numeric.
     */
    private function level_value_to_number( string $value, bool $is_price ): ?float {
        static $separators = null;

        if ( '' === $value ) {
            return null;
        }
        if ( null === $separators ) {
            // The same filters the export applies when it formats a price.
            $separators = array(
                (string) apply_filters( 'woo_ce_wc_price_thousands_sep', wc_get_price_thousand_separator() ),
                (string) apply_filters( 'woo_ce_wc_price_decimal_sep', wc_get_price_decimal_separator() ),
            );
        }
        list( $thousands_sep, $decimal_sep ) = $separators;

        if ( is_numeric( $value ) && ! ( $is_price && '.' === $thousands_sep ) ) {
            return (float) $value;
        }
        if ( '' !== $thousands_sep ) {
            $value = str_replace( $thousands_sep, '', $value );
        }
        if ( '' !== $decimal_sep && '.' !== $decimal_sep ) {
            $value = str_replace( $decimal_sep, '.', $value );
        }
        return is_numeric( $value ) ? (float) $value : null;
    }

    /**
     * Get dataset to export.
     *
     * @since 2.7.3
     * @access public
     *
     * @param array       $object_ids The object IDs.
     * @param null|object $export The export settings.
     */
    public function get_dataset( $object_ids, $export = null ) {
        $dataset = array();
        $columns = $export->columns;

        $column_keys = array();
        foreach ( $columns as $column_key => $column ) {
            $column_keys[] = $column_key;
        }

        foreach ( $object_ids as $object_id ) {
            $dataset[] = woo_ce_get_product_data( $object_id, $export->args, $column_keys, $export );
        }

        return Export_Helper::parse_dataset( $dataset, $columns );
    }

    /**
     * Get the Product meta data.
     *
     * @since 2.7.3
     * @access public
     *
     * @param int $product_id The Product ID.
     * @return array
     */
    public function get_product_meta_data( $product_id ) {
        global $wpdb;

        $meta_data = array();
        $meta_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_key,meta_value FROM {$wpdb->postmeta} WHERE post_id = %d",
                $product_id
            ),
            ARRAY_A
        );
        return ! empty( $meta_data ) ? array_column( $meta_data, 'meta_value', 'meta_key' ) : array();
    }

    /**
     * Execute model.
     *
     * @since 2.7.3
     * @access public
     */
    public function run() {
        add_filter( 'wsed_extend_export_dataset_args', array( $this, 'extend_export_dataset_args' ), 10, 3 );
    }
}
