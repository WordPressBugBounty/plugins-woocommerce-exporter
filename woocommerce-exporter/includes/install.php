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

// The active-module cache is invalidated from includes/bootstrap.php, on every
// request, so a Plugin toggled over WP-CLI or the REST API is noticed too.
?>
