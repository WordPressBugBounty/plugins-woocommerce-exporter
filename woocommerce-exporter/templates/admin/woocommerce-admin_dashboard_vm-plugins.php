<?php if ( ! empty( $vl_plugins ) ) { ?>
    <div class="table table_content">
        <?php if ( $update_available ) { ?>
            <p class="message"><?php esc_html_e( 'A new version of a Visser Labs Plugin for WooCommerce is available for download.', 'woocommerce-exporter' ); ?></p>
        <?php } ?>
        <table class="woo_vm_version_table">
            <thead>
                <tr>
                    <th class="align-left" style="text-align:left;"><?php esc_html_e( 'Plugin', 'woocommerce-exporter' ); ?></th>
                    <th class="align-left" style="text-align:left;"><?php esc_html_e( 'Version', 'woocommerce-exporter' ); ?></th>
                    <th class="align-left" style="text-align:left;"><?php esc_html_e( 'Status', 'woocommerce-exporter' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $vl_plugins as $plugin ) { ?>
                    <?php if ( $plugin['version'] ) { ?>
                        <?php if ( $plugin['installed'] ) { ?>
                            <tr>
                                <td><a href="<?php echo esc_url( $plugin['url'] ); ?>#toc-news" target="_blank"><?php echo esc_html( $plugin['name'] ); ?></a></td>
                                <?php if ( $plugin['version_existing'] ) { ?>
                                    <?php // translators: %1$s: existing plugin version, %2$s: new plugin version. ?>
                                    <td class="version"><?php printf( esc_html__( '%1$s to %2$s', 'woocommerce-exporter' ), esc_html( $plugin['version_existing'] ), '<span>' . esc_html( $plugin['version'] ) . '</span>' ); ?></td>
                                    <?php if ( $plugin['url'] && current_user_can( $user_capability ) ) { ?>
                                        <?php // translators: %s: plugin name. ?>
                                        <td class="status"><a href="<?php echo esc_url( admin_url( 'update-core.php' ) ); ?>"><span class="red" title="<?php printf( esc_html__( 'Plugin update available for %s', 'woocommerce-exporter' ), esc_html( $plugin['name'] ) ); ?>"><?php esc_html_e( 'Update', 'woocommerce-exporter' ); ?></span></a></td>
                                    <?php } else { ?>
                                        <?php // translators: %s: plugin name. ?>
                                        <td class="status"><span class="red" title="<?php printf( esc_html__( 'Plugin update available for %s', 'woocommerce-exporter' ), esc_html( $plugin['name'] ) ); ?>"><?php esc_html_e( 'Update', 'woocommerce-exporter' ); ?></span></td>
                                    <?php } ?>
                                <?php } elseif ( $plugin['version_beta'] ) { ?>
                                    <td class="version"><?php echo esc_html( $plugin['version_beta'] ); ?></td>
                                    <?php // translators: %s: plugin name. ?>
                                    <td class="status"><span class="yellow" title="<?php printf( esc_html__( '%s is from the future.', 'woocommerce-exporter' ), esc_html( $plugin['name'] ) ); ?>"><?php esc_html_e( 'Beta', 'woocommerce-exporter' ); ?></span></td>
                                <?php } else { ?>
                                    <td class="version"><?php echo esc_html( $plugin['version'] ); ?></td>
                                    <?php // translators: %s: plugin name. ?>
                                    <td class="status"><span class="green" title="<?php printf( esc_html__( '%s is up to date.', 'woocommerce-exporter' ), esc_html( $plugin['name'] ) ); ?>"><?php esc_html_e( 'OK', 'woocommerce-exporter' ); ?></span></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
        <!-- .woo_vm_version_table -->
        <p class="link"><a href="http://www.visser.com.au/woocommerce/" target="_blank"><?php esc_html_e( 'Looking for more WooCommerce Plugins?', 'woocommerce-exporter' ); ?></a></p>
    </div>
    <!-- .table -->
<?php } else { ?>
    <p><?php esc_html_e( 'Connection failed. Please check your network settings.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
