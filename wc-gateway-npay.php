<?php
/**
 * Plugin Name: WC Gateway nPay
 * Plugin URI: https://github.com/axisthemes/wc-gateway-npay
 * Description: WooCommerce nPay is a Nepali payment gateway with SCT Cards support for WooCommerce.
 * Version: 1.0.0
 * Author: AxisThemes
 * Author URI: http://axisthemes.com
 * License: GPLv3 or later
 * Text Domain: wc-gateway-npay
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_nPay' ) ) :

/**
 * WooCommerce nPay main class.
 */
class WC_nPay {

	/**
	 * Plugin version.
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Instance of this class.
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Checks with WooCommerce and SOAP module is installed.
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.3', '>=' ) && class_exists( 'SoapClient' ) ) {
			$this->includes();

			// Hooks.
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'missing_dependencies_notice' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/wc-gateway-npay/wc-gateway-npay-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/wc-gateway-npay-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wc-gateway-npay' );

		load_textdomain( 'wc-gateway-npay', WP_LANG_DIR . '/wc-gateway-npay/wc-gateway-npay-' . $locale . '.mo' );
		load_plugin_textdomain( 'wc-gateway-npay', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Includes.
	 */
	private function includes() {
		include_once( 'includes/class-wc-npay-gateway.php' );
	}

	/**
	 * Add the gateway to WooCommerce.
	 *
	 * @param  array $methods WooCommerce payment methods.
	 * @return array          Payment methods with nPay.
	 */
	public function add_gateway( $methods ) {
		$methods[] = 'WC_Gateway_nPay';
		return $methods;
	}

	/**
	 * Display action links in the Plugins list table.
	 * @param  array $actions
	 * @return array
	 */
	public function plugin_action_links( $actions ) {
		$new_actions = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=npay' ) . '" title="' . esc_attr( __( 'View Settings', 'wc-gateway-npay' ) ) . '">' . __( 'Settings', 'wc-gateway-npay' ) . '</a>',
		);

		return array_merge( $new_actions, $actions );
	}

	/**
	 * Missing dependencies notice.
	 * @return string
	 */
	public function missing_dependencies_notice() {
		if ( ! class_exists( 'SoapClient' ) ) {
			echo '<div class="error notice is-dismissible"><p>' . __( 'WooCommerce nPay needs to have the SOAP module installed on your server to work!', 'wc-gateway-npay' ) . '</p></div>';
		}

		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			echo '<div class="error notice is-dismissible"><p>' . sprintf( __( 'WooCommerce nPay depends on the last version of %s or later to work!', 'wc-gateway-npay' ), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">' . __( 'WooCommerce 2.3', 'wc-gateway-npay' ) . '</a>' ) . '</p></div>';
		}
	}
}

add_action( 'plugins_loaded', array( 'WC_nPay', 'get_instance' ) );

endif;
