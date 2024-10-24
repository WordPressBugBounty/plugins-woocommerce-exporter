<?php
function woo_cd_create_secure_archives_dir() {
	$upload_dir =  wp_upload_dir();
	$files = array(
		array(
			'base' 		=> $upload_dir['basedir'] . '/sed-exports',
			'file' 		=> '.htaccess',
			'content' 	=> 'deny from all'
		)
	);
	foreach( $files as $file ) {
		if ( wp_mkdir_p( $file['base'] ) && !file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
			if( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
				fwrite( $file_handle, $file['content'] );
				fclose( $file_handle );
			}
		}
	}

}

// Refresh the list of active modules when a Plugin is activated/de-activated
if( function_exists( 'woo_ce_refresh_active_export_plugins' ) ) {
	add_action( 'activated_plugin', 'woo_ce_refresh_active_export_plugins' );
	add_action( 'deactivated_plugin', 'woo_ce_refresh_active_export_plugins' );
}
?>
