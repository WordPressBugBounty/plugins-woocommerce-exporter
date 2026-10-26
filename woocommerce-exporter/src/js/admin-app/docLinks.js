/**
 * Visser knowledge base URLs used by the Help button.
 */
const KB_ARTICLE_BASE = 'https://visser.com.au/kb/'

export const KB_HUB_URL = 'https://visser.com.au/kb-categories/woocommerce-store-exporter-deluxe/'

export const KB_ARTICLES = {
  gettingStarted: `${KB_ARTICLE_BASE}getting-started-with-store-exporter-deluxe/`,
  exportFields: `${KB_ARTICLE_BASE}supported-export-fields/`,
  quickExport: `${KB_ARTICLE_BASE}how-to-use-quick-export-in-store-exporter-deluxe/`,
  supportedPlugins: `${KB_ARTICLE_BASE}export-data-from-supported-plugins/`,
  customMetaFields: `${KB_ARTICLE_BASE}exporting-custom-product-order-order-item-meta/`,
  // The AI / MCP article is not published yet, so this points to the KB hub.
  aiMcp: KB_HUB_URL
}
