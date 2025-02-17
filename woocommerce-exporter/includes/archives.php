<?php
function woo_ce_archives_add_options() {

	global $archives_table;

	$args = array(
		'label' => __( 'Archives per page', 'woocommerce-exporter' ),
		'default' => 10,
		'option' => 'archive_per_page'
	);
	add_screen_option( 'per_page', $args );

	$archives_table = new WOO_CE_Archives_List_Table();

}

function woo_ce_set_archives_screen_option( $status, $option, $value ) {

	if( 'archive_per_page' == $option )
		return $value;

}
add_filter( 'set-screen-option', 'woo_ce_set_archives_screen_option', 10, 3 );

if( !class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class WOO_CE_Archives_List_Table extends WP_List_Table {

	function __construct(){

		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'archive',     //singular name of the listed records
			'plural' => 'archives',    //plural name of the listed records
			'ajax' => false        //does this table support ajax?
		) );

	}

	function get_columns() {

		$columns = array(
			'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
			'filename' => __( 'Filename', 'woocommerce-exporter' ),
			'type' => __( 'Type', 'woocommerce-exporter' ),
			'format' => __( 'Format', 'woocommerce-exporter' ),
			'filesize' => __( 'Filesize', 'woocommerce-exporter' ),
			'rows' => __( 'Rows', 'woocommerce-exporter' ),
			'columns' => __( 'Columns', 'woocommerce-exporter' ),
			'process_time' => __( 'Process time', 'woocommerce-exporter' ),
			'idle_memory_end' => __( 'Memory usage', 'woocommerce-exporter' ),
			'author' => __( 'Author', 'woocommerce-exporter' ),
			'date' => __( 'Date', 'woocommerce-exporter' )
		);
		return $columns;

	}

	function column_cb( $item ) {

		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item->ID                //The value of the checkbox should be the record's id
		);

	}

	function column_filename( $item ) {

		// Build row actions
		$actions = array(
			'download' => sprintf( '<a href="%s">Download</a>', wp_get_attachment_url( $item->ID ) ),
			'edit' => sprintf( '<a href="%s">Edit</a>', get_edit_post_link( $item->ID ) ),
			'delete' => sprintf( '<a href="%s">Delete</a>', get_delete_post_link( $item->ID, '', true ) ),
		);

		// Return the title contents
		return sprintf( '%s%s',
			'<a href="' . wp_get_attachment_url( $item->ID ) . '" title="' . sprintf( __( 'Download %s', 'woocommerce-exporter' ), $item->post_title ) . '"><strong>' . $item->post_title . '</strong></a>',
			$this->row_actions( $actions )
		);

	}

	function column_type( $item ) {

		$export_type = get_post_meta( $item->ID, '_woo_export_type', true );
		$export_type_label = woo_ce_export_type_label( $export_type );
		if( empty( $export_type ) )
			$export_type = __( 'Unassigned', 'woocommerce-exporter' );

		return sprintf( '<a href="%s" title="' . __( 'Filter Archives by this Export Type', 'woocommerce-exporter' ) . '">%s</a>', esc_url( add_query_arg( 'filter', $export_type ) ), $export_type_label );

	}

	function column_format( $item ) {

		$export_format = woo_ce_get_mime_type_extension( $item->post_mime_type, 'mime_type' );
		return $export_format;

	}

	function column_filesize( $item ) {

		$output = '-';
		$filepath = get_attached_file( $item->ID );
		if( !empty( $filepath ) ) {
			$filesize = @filesize( $filepath );
			if( $filesize != 0 )
				$output = size_format( $filesize );
		}
		return $output;

	}

	function column_rows( $item ) {

		$output = '-';
		$rows = get_post_meta( $item->ID, '_woo_rows', true );
		if( !empty( $rows ) )
			$output = $rows;
		return $output;

	}

	function column_columns( $item ) {

		$output = '-';
		$rows = get_post_meta( $item->ID, '_woo_columns', true );
		if( !empty( $rows ) )
			$output = $rows;
		return $output;

	}

	function column_author( $item ) {

		// post_author is empty if the export is generated by CRON or scheduled export
		$author_name = __( 'WP-CRON', 'woocommerce-exporter' );
		if( !empty( $item->post_author ) ) {
			if( $author_name = get_user_by( 'id', $item->post_author ) )
				$author_name = $author_name->display_name;
		}

		return $author_name;

	}

	function column_process_time( $item ) {

		$output = '-';
		$start_time = get_post_meta( $item->ID, '_woo_start_time', true );
		$end_time = get_post_meta( $item->ID, '_woo_end_time', true );
		$process_time = ( ( ( $start_time != false ) && ( $end_time != false ) ) ? woo_ce_display_time_elapsed( $start_time, $end_time ) : '-' );
		if( !empty( $process_time ) )
			$output = $process_time;
		return $output;

	}

	function column_idle_memory_end( $item ) {

		$output = '-';
		$idle_memory_end = get_post_meta( $item->ID, '_woo_idle_memory_end', true );
		if ( false !== $idle_memory_end ) {
			$output = woo_ce_display_memory( $idle_memory_end );
		}
		return $output;

	}

	function column_date( $item ) {

		return woo_ce_format_archive_date( $item->ID );

	}

	function get_sortable_columns() {

		$sortable_columns = array(
			'title' => array( 'title',false ),     //true means it's already sorted
			'type' => array( 'type',false ),
			'format' => array( 'format',false ),
			'author' => array( 'author', false ),
			'date' => array( 'date', false )
		);
		return $sortable_columns;

	}

	function get_bulk_actions() {

		$actions = array(
			'delete' => __( 'Delete', 'woocommerce-exporter' )
		);
		return $actions;

	}

	function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if( 'delete' === $this->current_action() ) {
			$items = ( isset( $_POST['archive'] ) ? array_map( 'absint', (array)$_POST['archive'] ) : false );
			if( !empty( $items ) ) {
				foreach( $items as $id ) {
					wp_delete_attachment( $id, true );
				}
			}
		}

	}

	function prepare_items() {

		$per_page = get_user_meta( get_current_user_id(), 'archive_per_page', true );
		if( $per_page == '' )
			$per_page = 10;

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		// $this->_column_headers = array( $columns, $hidden, $sortable );
		$this->_column_headers = $this->get_column_info();
		$this->process_bulk_action();

		$data = woo_ce_get_archive_files();
		$current_page = $this->get_pagenum();
		$total_items = count($data);
		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->items = $data;
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		) );

	}

}
