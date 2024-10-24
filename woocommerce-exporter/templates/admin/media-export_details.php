<div class="postbox-container">
    <div id="woo_ce-media-export_details" class="postbox">
        <h3 class="hndle"><?php esc_html_e( 'Export Details', 'woocommerce-exporter' ); ?></h3>
        <div class="inside">

            <h4><?php esc_html_e( 'General', 'woocommerce-exporter' ); ?></h4>
            <dl>
                <dt><?php esc_html_e( 'Export type', 'woocommerce-exporter' ); ?></dt>
                <dd><?php echo esc_html( woo_ce_export_type_label( $export_type ) ); ?></dd>
                <dt><?php esc_html_e( 'Filepath', 'woocommerce-exporter' ); ?></dt>
                <dd><?php echo esc_url( $filepath ); ?></dd>
                <dt><?php esc_html_e( 'Total columns', 'woocommerce-exporter' ); ?></dt>
                <dd><?php echo esc_html( ( ( $columns != false ) ? $columns : '-' ) ); ?></dd>
                <dt><?php esc_html_e( 'Total rows', 'woocommerce-exporter' ); ?></dt>
                <dd><?php echo esc_html( ( ( $rows != false ) ? $rows : '-' ) ); ?></dd>
                <?php if ( $scheduled_id ) { ?>
                    <dt><?php esc_html_e( 'Scheduled export', 'woocommerce-exporter' ); ?></dt>
                    <dd><a href="<?php echo esc_url( get_edit_post_link( $scheduled_id ) ); ?>" title="<?php esc_attr_e( 'Edit scheduled export', 'woocommerce-exporter' ); ?>"><?php echo esc_html( woo_ce_format_post_title( get_the_title( $scheduled_id ) ) ); ?></a></dd>
                <?php } ?>
            </dl>

            <h4><?php esc_html_e( 'Memory', 'woocommerce-exporter' ); ?></h4>
            <dl>
                <dt><?php esc_html_e( 'Process time', 'woocommerce-exporter' ); ?></dt>
                <dd><?php echo esc_html( ( ( ( $start_time != false ) && ( $end_time != false ) ) ? woo_ce_display_time_elapsed( $start_time, $end_time ) : '-' ) ); ?></dd>
                <dt><?php esc_html_e( 'Idle memory usage (start)', 'woocommerce-exporter' ); ?></dt>
                <dd><?php echo esc_html( ( ( $idle_memory_start != false ) ? woo_ce_display_memory( $idle_memory_start ) : '-' ) ); ?></dd>
                <dt><?php esc_html_e( 'Memory usage prior to loading export type', 'woocommerce-exporter' ); ?></dt>
                <dd><?php echo esc_html( ( ( $data_memory_start != false ) ? woo_ce_display_memory( $data_memory_start ) : '-' ) ); ?></dd>
                <dt><?php esc_html_e( 'Memory usage after loading export type', 'woocommerce-exporter' ); ?></dt>
                <dd><?php echo esc_html( ( ( $data_memory_end != false ) ? woo_ce_display_memory( $data_memory_end ) : '-' ) ); ?></dd>
                <dt><?php esc_html_e( 'Idle memory usage (end)', 'woocommerce-exporter' ); ?></dt>
                <dd><?php echo esc_html( ( ( $idle_memory_end != false ) ? woo_ce_display_memory( $idle_memory_end ) : '-' ) ); ?></dd>
            </dl>

        </div>
        <!-- .inside -->
    </div>
    <!-- .postbox -->
</div>
<!-- .postbox-container -->
