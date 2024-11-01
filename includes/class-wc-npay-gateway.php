<?php
/**
 * nPay Payment Gateway.
 *
 * Provides a nPay Payment Gateway.
 *
 * @class    WC_Gateway_nPay
 * @extends  WC_Payment_Gateway
 * @category Class
 * @author   AxisThemes
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gateway_nPay Class.
 */
class WC_Gateway_nPay extends WC_Payment_Gateway {

	/** @var bool Whether or not logging is enabled */
	public static $log_enabled = false;

	/** @var WC_Logger Logger instance */
	public static $log = false;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'npay';
		$this->icon               = apply_filters( 'woocommerce_npay_icon', plugins_url( 'assets/images/npay.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields         = false;
		$this->order_button_text  = __( 'Proceed to nPay', 'wc-gateway-npay' );
		$this->method_title       = __( 'nPay', 'wc-gateway-npay' );
		$this->method_description = sprintf( __( 'The nPay epay system sends customers to nPay to enter their payment information. The nPay IPN requires fsockopen/cURL and SoapClient support to update order statuses after payment. Check the %ssystem status%s page for more details.', 'wc-gateway-npay' ), '<a href="' . admin_url( 'admin.php?page=wc-status' ) . '">', '</a>' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		$this->testmode           = 'yes' === $this->get_option( 'testmode', 'no' );
		$this->debug              = 'yes' === $this->get_option( 'debug', 'no' );
		$this->merchant_id        = $this->get_option( 'merchant_id' );
		$this->merchant_username  = $this->get_option( 'merchant_username' );
		$this->merchant_password  = $this->get_option( 'merchant_password' );
		$this->signature_password = $this->get_option( 'signature_password' );

		self::$log_enabled        = $this->debug;

		// Actions
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = 'no';
		} else if ( $this->merchant_id && $this->merchant_username ) {
			include_once( 'includes/class-wc-gateway-npay-ipn-handler.php' );
			new WC_Gateway_nPay_IPN_Handler( $this, $this->testmode );
		}
	}

	/**
	 * Logging method.
	 * @param string $message
	 */
	public static function log( $message ) {
		if ( self::$log_enabled ) {
			if ( empty( self::$log ) ) {
				self::$log = new WC_Logger();
			}
			self::$log->add( 'npay', $message );
		}
	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 * @return bool
	 */
	public function is_valid_for_use() {
		return in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_npay_supported_currencies', array( 'NPR' ) ) );
	}

	/**
	 * Admin Panel Options.
	 * - Options for bits like 'title' and availability on a country-by-country basis.
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {
		if ( $this->is_valid_for_use() ) {
			parent::admin_options();
		} else {
			?>
			<div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'wc-gateway-npay' ); ?></strong>: <?php _e( 'nPay does not support your store currency.', 'wc-gateway-npay' ); ?></p></div>
			<?php
		}
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = include( 'includes/settings-npay.php' );
	}

	/**
	 * Get the transaction URL.
	 *
	 * @param  WC_Order $order
	 * @return string
	 */
	public function get_transaction_url( $order ) {
		if ( $this->testmode ) {
			$this->view_transaction_url = 'http://mpanel.sandbox.npay.com.np/portal/report/TransactionReport.asp';
		} else {
			$this->view_transaction_url = 'http://mpanel.npay.com.np/portal/report/TransactionReport.asp';
		}
		return parent::get_transaction_url( $order );
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param  int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		// Return pay redirect
		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true )
		);
	}

	/**
	 * Output for the order received page.
	 *
	 * @param int $order_id
	 */
	public function receipt_page( $order_id ) {
		echo '<p>' . __( 'Thank you - your order is now pending payment. You should be automatically redirected to nPay to make payment.', 'wc-gateway-npay' ) . '</p>';

		echo $this->generate_npay_form( $order_id );
	}

	/**
	 * Generate the npay form.
	 *
	 * @param  int $order_id
	 * @return string
	 */
	public function generate_npay_form( $order_id ) {
		$order = new WC_Order( $order_id );

		$npay_adr = $this->testmode ? 'https://gateway.sandbox.npay.com.np/pay.aspx' : 'https://gateway.npay.com.np/pay.aspx';

		$npay_args = $this->get_npay_args( $order );

		$npay_args_array = array();

		foreach ( $npay_args as $key => $value ) {
			$npay_args_array[] = '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}

		wc_enqueue_js( '
			$.blockUI({
				message: "' . esc_js( __( 'Thank you for your order. We are now redirecting you to nPay to make payment.', 'wc-gateway-npay' ) ) . '",
				baseZ: 99999,
				overlayCSS: {
					background: "#fff",
					opacity: 0.6
				},
				css: {
					padding:        "20px",
					zindex:         "9999999",
					textAlign:      "center",
					color:          "#555",
					border:         "3px solid #aaa",
					backgroundColor:"#fff",
					cursor:         "wait",
					lineHeight:		"24px",
				}
			});
			jQuery("#submit-payment-form").click();
		' );

		return '<form action="' . esc_url( $npay_adr ) . '" method="post" id="payment-form" target="_top">
				' . implode( '', $npay_args_array ) . '
				<input type="submit" class="button alt" id="submit-payment-form" value="' . __( 'Pay via nPay', 'wc-gateway-npay' ) . '" /> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'wc-gateway-npay' ) . '</a>
			</form>';
	}

	/**
	 * Get nPay Args for passing to nPay.
	 * @param  WC_Order $order
	 * @return array
	 */
	protected function get_npay_args( $order ) {
		WC_Gateway_nPay::log( 'Generating payment form for order ' . $order->get_order_number() . '. Notify URL: ' . WC()->api_request_url( 'WC_Gateway_nPay' ) );

		$merchant_id        = $this->get_option( 'merchant_id' );
		$merchant_username  = $this->get_option( 'merchant_username' );
		$merchant_password  = $this->get_option( 'merchant_password' );
		$signature_password = $this->get_option( 'signature_password' );

		// Prepare Transaction ID.
		$transaction_id = $order->get_order_number();

		// Hash Merchant and Signature Password with SHA256.
		$hash_merchant_password  = hash( 'sha256', $merchant_username . $merchant_password );
		$hash_signature_password = hash( 'sha256', $signature_password . $merchant_username . $transaction_id );

		$process_id = '';

		try {
			$client = new SoapClient(
				$this->testmode ? 'http://gateway.sandbox.npay.com.np/websrv/Service.asmx?wsdl' : 'http://gateway.npay.com.np/websrv/Service.asmx?wsdl',
				array(
					'encoding'   => 'UTF-8',
					'trace'      => true,
					'exceptions' => true,
					'cache_wsdl' => false,
				)
			);

			$response = $client->ValidateMerchant( array(
				'MerchantId'       => $merchant_id,
				'MerchantTxnId'    => $transaction_id,
				'MerchantUserName' => $merchant_username,
				'MerchantPassword' => $hash_merchant_password,
				'Signature'        => $hash_signature_password,
				'AMOUNT'           => wc_format_decimal( $order->get_total(), 2 )
			) );

			WC_Gateway_nPay::log( 'Validate Merchant Response: ' . print_r( $response, true ) );

			if ( 0 == $response->ValidateMerchantResult->STATUS_CODE ) {
				$process_id = $response->ValidateMerchantResult->PROCESSID;
			}

		} catch ( Exception $e ) {
			WC_Gateway_nPay::log( 'Error response: ' . print_r( $e->getMessage(), true ) );
		}

		return apply_filters( 'woocommerce_npay_args', array(
			'ProcessID'        => $process_id,
			'MerchantID'       => $merchant_id,
			'MerchantTxnID'    => $transaction_id,
			'MerchantUsername' => $merchant_username,
			'PayAmount'        => wc_format_decimal( $order->get_total(), 2 ),
			'Description'      => sprintf( __( 'Payment for the order %s', 'wc-gateway-npay' ), $order->get_order_number() )
		), $order );
	}
}
