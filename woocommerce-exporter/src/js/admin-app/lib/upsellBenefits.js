import { __ } from '@wordpress/i18n'

/**
 * Generic Store Exporter Deluxe value props, shared by the upsell modal and
 * the upgrade card in the Export Complete dialog.
 *
 * A function, not a module-level __() result, so locale data registered after
 * this module loads still applies.
 *
 * @return {string[]} Translated benefit lines.
 */
export function upsellBenefits () {
  return [
    __('Unlock every export type — customers, coupons, subscriptions, bookings & more', 'woocommerce-exporter'),
    __('Export to Excel (XLS / XLSX) and XML, not just CSV', 'woocommerce-exporter'),
    __('Schedule automatic exports and save reusable export templates', 'woocommerce-exporter'),
    __('Access hundreds more fields with advanced filtering', 'woocommerce-exporter'),
  ]
}
