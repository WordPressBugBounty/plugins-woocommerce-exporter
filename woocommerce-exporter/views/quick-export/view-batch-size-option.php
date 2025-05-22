<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<tr>
    <th><label for="offset"><?php esc_html_e( 'Batch size', 'woocommerce-exporter' ); ?></label></th>
    <td>
        <input
            type="text"
            size="3"
            id="batch_size"
            name="batch_size"
            size="5"
            class="text"
            title="<?php esc_attr_e( 'Batch size', 'woocommerce-exporter' ); ?>"
            value="<?php echo esc_attr( get_option( WOO_CE_PREFIX . '_batch_size', WSED_DEFAULT_BATCH_SIZE ) ); ?>"
            placeholder="<?php echo esc_attr( WSED_DEFAULT_BATCH_SIZE ); ?>"
        />
        <p class="description">
            <?php esc_html_e( 'Set the number of records to be exported in each batch.', 'woocommerce-exporter' ); ?>
        </p>
    </td>
</tr>
