<h3>
    <?php esc_html_e( 'Export Templates', 'woocommerce-exporter' ); ?>
    <a href="#" class="add-new-h2 wse-upsell-button-disabled">
        <?php esc_html_e( 'Add New', 'woocommerce-exporter' ); ?>
    </a>
</h3>
<div class="wse-upsell-content-overlayed">
    <table class="widefat page fixed striped export-templates">
        <thead>

            <tr>
                <th class="manage-column"><?php esc_html_e( 'Name', 'woocommerce-exporter' ); ?></th>
                <th class="manage-column"><?php esc_html_e( 'Status', 'woocommerce-exporter' ); ?></th>
                <th class="manage-column"><?php esc_html_e( 'Excerpt', 'woocommerce-exporter' ); ?></th>
            </tr>

        </thead>
        <tbody id="the-list">
            <tr>
                <td class="post-title column-title">
                    <strong>
                        <a href="#" title="<?php esc_attr_e( 'Edit export template', 'woocommerce-exporter' ); ?>">
                            <?php echo esc_html( 'Products Export Template' ); ?>
                        </a>
                    </strong>
                    <div class="row-actions">
                        <a href="#" title="<?php esc_attr_e( 'Edit this export template', 'woocommerce-exporter' ); ?>">
                            <?php esc_html_e( 'Edit', 'woocommerce-exporter' ); ?>
                        </a> |
                        <span class="trash">
                            <a href="#" class="submitdelete" title="<?php esc_attr_e( 'Delete this export template', 'woocommerce-exporter' ); ?>">
                                <?php esc_html_e( 'Delete', 'woocommerce-exporter' ); ?>
                            </a>
                        </span>
                    </div>
                    <!-- .row-actions -->
                </td>
                <td><?php echo esc_html( 'Publish' ); ?></td>
                <td><?php echo esc_html( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' ); ?></td>
            </tr>
            <tr>
                <td class="post-title column-title">
                    <strong>
                        <a href="#" title="<?php esc_attr_e( 'Edit export template', 'woocommerce-exporter' ); ?>">
                            <?php echo esc_html( 'Orders Export Template' ); ?>
                        </a>
                    </strong>
                    <div class="row-actions">
                        <a href="#" title="<?php esc_attr_e( 'Edit this export template', 'woocommerce-exporter' ); ?>">
                            <?php esc_html_e( 'Edit', 'woocommerce-exporter' ); ?>
                        </a> |
                        <span class="trash">
                            <a href="#" class="submitdelete" title="<?php esc_attr_e( 'Delete this export template', 'woocommerce-exporter' ); ?>">
                                <?php esc_html_e( 'Delete', 'woocommerce-exporter' ); ?>
                            </a>
                        </span>
                    </div>
                    <!-- .row-actions -->
                </td>
                <td><?php echo esc_html( 'Publish' ); ?></td>
                <td><?php echo esc_html( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' ); ?></td>
            </tr>
            <tr>
                <td class="post-title column-title">
                    <strong>
                        <a href="#" title="<?php esc_attr_e( 'Edit export template', 'woocommerce-exporter' ); ?>">
                            <?php echo esc_html( 'Subscriptions Export Template' ); ?>
                        </a>
                    </strong>
                    <div class="row-actions">
                        <a href="#" title="<?php esc_attr_e( 'Edit this export template', 'woocommerce-exporter' ); ?>">
                            <?php esc_html_e( 'Edit', 'woocommerce-exporter' ); ?>
                        </a> |
                        <span class="trash">
                            <a href="#" class="submitdelete" title="<?php esc_attr_e( 'Delete this export template', 'woocommerce-exporter' ); ?>">
                                <?php esc_html_e( 'Delete', 'woocommerce-exporter' ); ?>
                            </a>
                        </span>
                    </div>
                    <!-- .row-actions -->
                </td>
                <td><?php echo esc_html( 'Draft' ); ?></td>
                <td><?php echo esc_html( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' ); ?></td>
            </tr>
        </tbody>

    </table>
    <?php
    do_action(
        'wse_show_upsell_overlay',
        'exporttemplates',
        __( 'Export Templates available in Store Exporter Deluxe', 'woocommerce-exporter' ),
        __( 'Upgrade to Store Exporter Deluxe to create custom export templates and premium support.', 'woocommerce-exporter' )
    );
    ?>
</div>
<!-- .export-templates -->

<p style="text-align:right;">
    <?php
    // translators: %d is the number of export templates.
    echo esc_html( sprintf( __( '%d items', 'woocommerce-exporter' ), '3' ) );
    ?>
</p>
