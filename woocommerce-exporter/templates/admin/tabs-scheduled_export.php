<ul class="subsubsub">
    <li><a href="#scheduled-exports"><?php esc_html_e( 'Scheduled Exports', 'woocommerce-exporter' ); ?></a> |</li>
    <li><a href="#recent-scheduled-exports"><?php esc_html_e( 'Recent Scheduled Exports', 'woocommerce-exporter' ); ?></a></li>
    <?php do_action( 'woo_ce_scheduled_export_settings_top' ); ?>
</ul>
<!-- .subsubsub -->
<br class="clear" />

<?php do_action( 'woo_ce_before_scheduled_exports' ); ?>

<h3 id="scheduled-exports">
    <?php esc_html_e( 'Scheduled Exports', 'woocommerce-exporter' ); ?>
    <a href="#" class="add-new-h2 wse-upsell-button-disabled">
        <?php esc_html_e( 'Add New', 'woocommerce-exporter' ); ?>
    </a>
</h3>

<div class="wse-upsell-content-overlayed">
    <table class="widefat page fixed striped scheduled-exports">
        <thead>

            <tr>
                <th class="manage-column"><?php esc_html_e( 'Name', 'woocommerce-exporter' ); ?></th>
                <th class="manage-column"><?php esc_html_e( 'Export Type', 'woocommerce-exporter' ); ?></th>
                <th class="manage-column"><?php esc_html_e( 'Export Format', 'woocommerce-exporter' ); ?></th>
                <th class="manage-column"><?php esc_html_e( 'Export Method', 'woocommerce-exporter' ); ?></th>
                <th class="manage-column"><?php esc_html_e( 'Status', 'woocommerce-exporter' ); ?></th>
                <th class="manage-column"><?php esc_html_e( 'Frequency', 'woocommerce-exporter' ); ?></th>
                <th class="manage-column"><?php esc_html_e( 'Next run', 'woocommerce-exporter' ); ?></th>
                <th class="manage-column"><?php esc_html_e( 'Action', 'woocommerce-exporter' ); ?></th>
            </tr>

        </thead>
        <tbody id="the-list">
            <ul class="subsubsub">
                <li class="all">
                    <a href="#" class="current">
                        <?php esc_html_e( 'All', 'woocommerce-exporter' ); ?>
                        <span class="count">(<?php echo esc_html( '3' ); ?>)</span>
                    </a> |
                </li>
                <li class="published">
                    <a href="#">
                        <?php esc_html_e( 'Published', 'woocommerce-exporter' ); ?>
                        <span class="count">(<?php echo esc_html( '3' ); ?>)</span>
                    </a> |
                </li>
                <li class="draft">
                    <a href="#">
                        <?php esc_html_e( 'Draft', 'woocommerce-exporter' ); ?>
                    <span class="count">(<?php echo esc_html( '0' ); ?>)</span></a>
                </li>
            </ul>
            <tr>
                <td class=" post-title column-title">
                    <strong>
                        <a href="#" title="<?php esc_attr_e( 'Edit scheduled export', 'woocommerce-exporter' ); ?>">
                            <?php echo 'Scheduled Export - Products'; ?>
                        </a>
                    </strong>
                    <div class="row-actions">
                        <a href="#" title="<?php esc_attr_e( 'Edit this scheduled export', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Edit', 'woocommerce-exporter' ); ?></a> |
                        <a href="#" title="<?php esc_attr_e( 'Duplicate this Scheduled Export', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Clone', 'woocommerce-exporter' ); ?></a> |
                        <a href="#" title="<?php esc_attr_e( 'Draft this Scheduled Export', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Draft', 'woocommerce-exporter' ); ?></a> |
                        <span class="trash"><a href="#" class="submitdelete" title="<?php esc_attr_e( 'Delete this scheduled export', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Delete', 'woocommerce-exporter' ); ?></a></span>
                    </div>
                <!-- .row-actions -->
                </td>
                <td><?php echo esc_html__( 'Products', 'woocommerce-exporter' ); ?></td>
                <td><?php echo esc_html__( 'CSV', 'woocommerce-exporter' ); ?></td>
                <td>
                    <?php echo esc_html__( 'Archive to WordPress Media', 'woocommerce-exporter' ); ?>
                </td>
                <td><?php echo esc_html__( 'Published', 'woocommerce-exporter' ); ?></td>
                <td>
                    <?php echo esc_html( sprintf( __( 'Every %d minutes', 'woocommerce-exporter' ), '30' ) ); ?>
                </td>
                <td class="next_run">
                    <?php echo esc_html( '1 Jan 2024 00:00' ); ?>
                </td>
                <td>
                    <a href="#" class="button">
                        <?php esc_html_e( 'Execute', 'woocommerce-exporter' ); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td class=" post-title column-title">
                    <strong>
                        <a href="#" title="<?php esc_attr_e( 'Edit scheduled export', 'woocommerce-exporter' ); ?>">
                            <?php echo 'Scheduled Export - Orders'; ?>
                        </a>
                    </strong>
                    <div class="row-actions">
                        <a href="#" title="<?php esc_attr_e( 'Edit this scheduled export', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Edit', 'woocommerce-exporter' ); ?></a> |
                        <a href="#" title="<?php esc_attr_e( 'Duplicate this Scheduled Export', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Clone', 'woocommerce-exporter' ); ?></a> |
                        <a href="#" title="<?php esc_attr_e( 'Draft this Scheduled Export', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Draft', 'woocommerce-exporter' ); ?></a> |
                        <span class="trash"><a href="#" class="submitdelete" title="<?php esc_attr_e( 'Delete this scheduled export', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Delete', 'woocommerce-exporter' ); ?></a></span>
                    </div>
                <!-- .row-actions -->
                </td>
                <td><?php echo esc_html__( 'Orders', 'woocommerce-exporter' ); ?></td>
                <td><?php echo esc_html__( 'Excel (XLSX)', 'woocommerce-exporter' ); ?></td>
                <td>
                    <?php echo esc_html__( 'Save to this server', 'woocommerce-exporter' ); ?>
                </td>
                <td><?php echo esc_html__( 'Published', 'woocommerce-exporter' ); ?></td>
                <td>
                    <?php echo esc_html( sprintf( __( 'Every %d minutes', 'woocommerce-exporter' ), '30' ) ); ?>
                </td>
                <td class="next_run">
                    <?php echo esc_html( '1 Jan 2024 00:00' ); ?>
                </td>
                <td>
                    <a href="#" class="button">
                        <?php esc_html_e( 'Execute', 'woocommerce-exporter' ); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td class=" post-title column-title">
                    <strong>
                        <a href="#" title="<?php esc_attr_e( 'Edit scheduled export', 'woocommerce-exporter' ); ?>">
                            <?php echo 'Scheduled Export - Subscriptions	'; ?>
                        </a>
                    </strong>
                    <div class="row-actions">
                        <a href="#" title="<?php esc_attr_e( 'Edit this scheduled export', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Edit', 'woocommerce-exporter' ); ?></a> |
                        <a href="#" title="<?php esc_attr_e( 'Duplicate this Scheduled Export', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Clone', 'woocommerce-exporter' ); ?></a> |
                        <a href="#" title="<?php esc_attr_e( 'Draft this Scheduled Export', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Draft', 'woocommerce-exporter' ); ?></a> |
                        <span class="trash"><a href="#" class="submitdelete" title="<?php esc_attr_e( 'Delete this scheduled export', 'woocommerce-exporter' ); ?>"><?php esc_html_e( 'Delete', 'woocommerce-exporter' ); ?></a></span>
                    </div>
                <!-- .row-actions -->
                </td>
                <td><?php echo esc_html__( 'Subscriptions	', 'woocommerce-exporter' ); ?></td>
                <td><?php echo esc_html__( 'Excel (XLSX)', 'woocommerce-exporter' ); ?></td>
                <td>
                    <?php echo esc_html__( 'Send as e-mail', 'woocommerce-exporter' ); ?>
                </td>
                <td><?php echo esc_html__( 'Published', 'woocommerce-exporter' ); ?></td>
                <td>
                    <?php echo esc_html__( 'Daily' ); ?>
                </td>
                <td class="next_run">
                    <?php echo esc_html( '1 Jan 2024 00:00' ); ?>
                </td>
                <td>
                    <a href="#" class="button">
                        <?php esc_html_e( 'Execute', 'woocommerce-exporter' ); ?>
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
    <?php
    do_action(
        'wse_show_upsell_overlay',
        'scheduledexport',
        __( 'Scheduled Exports available in Store Exporter Deluxe', 'woocommerce-exporter' ),
        __( 'Upgrade to Store Exporter Deluxe for advanced export scheduling and premium support.', 'woocommerce-exporter' )
    );
    ?>
</div>
<!-- .scheduled-exports -->
<p style="text-align:right;"><?php printf( esc_html( __( '%d items', 'woocommerce-exporter' ) ), '3' ); ?></p>
<hr />
<?php do_action( 'woo_ce_after_scheduled_exports' ); ?>
