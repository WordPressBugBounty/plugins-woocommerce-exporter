<?php
/**
 * Store Exporter Deluxe - send export result to email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/scheduled-export.php.
 *
 * @version 2.7.3
 */
defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php echo wp_kses_post( $email_contents ); ?>

<?php
do_action( 'woocommerce_email_footer', $email );
