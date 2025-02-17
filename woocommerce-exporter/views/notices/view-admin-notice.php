<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="notice notice-<?php echo esc_attr( "$this->type $this->type" ); ?> is-dismissible" id="<?php echo esc_attr( $message_id ); ?>">
    <?php if ( 'html' === $this->message_type ) : ?>
        <?php echo wp_kses( $this->message, 'post' ); ?>
    <?php else : ?>
        <p><?php echo esc_html( $this->message ); ?></p>
    <?php endif; ?>
</div>
