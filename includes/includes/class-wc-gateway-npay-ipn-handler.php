<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once( 'class-wc-gateway-npay-response.php' );

/**
 * Handles IPN Responses from nPay.
 */
class WC_Gateway_nPay_IPN_Handler extends WC_Gateway_nPay_Response {

	/**
	 * Constructor.
	 * @param WC_Gateway_nPay $gateway
	 * @param bool            $sandbox
	 */
	public function __construct( $gateway, $sandbox = false ) {
		add_action( 'woocommerce_api_wc_gateway_npay', array( $this, 'check_response' ) );
		add_action( 'valid-npay-standard-ipn-request', array( $this, 'valid_response' ) );

		$this->sandbox = $sandbox;
		$this->gateway = $gateway;
	}

	/**
	 * Check for nPay IPN Response.
	 */
	public function check_response() {
		@ob_clean();

		if ( ! empty( $_REQUEST ) && $this->validate_ipn() ) {
			$requested = wp_unslash( $_REQUEST );

			do_action( 'valid-npay-standard-ipn-request', $requested );
			exit;
		}

		wp_die( 'nPay IPN Request Failure', 'nPay IPN', array( 'response' => 200 ) );
	}

	/**
	 * There was a valid response.
	 * @param array $requested Request data after wp_unslash
	 */
	public function valid_response( $requested ) {
		if ( ! empty( $requested['MERCHANTTXNID'] ) && $order = wc_get_order( $requested['MERCHANTTXNID'] ) ) {

			// Lowercase returned variables.
			$requested['payment_status'] = strtolower( get_post_meta( $order->id, '_txn_status', true ) );

			// Validate transaction status.
			if ( isset( $requested['MERCHANTTXNID'] ) && 'success' == $requested['payment_status'] ) {
				$requested['payment_status'] = 'completed';
				$requested['pending_reason'] = __( 'nPay IPN response failed.', 'wc-gateway-npay' );
			} else {
				$requested['payment_status'] = 'failed';
			}

			WC_Gateway_nPay::log( 'Found order #' . $order->id );
			WC_Gateway_nPay::log( 'Payment status: ' . $requested['payment_status'] );

			if ( method_exists( $this, 'payment_status_' . $requested['payment_status'] ) ) {
				call_user_func( array( $this, 'payment_status_' . $requested['payment_status'] ), $order, $requested );
			}
		}

		echo "0"; // Send valid delivery status message.
	}

	/**
	 * Check nPay IPN validity.
	 */
	public function validate_ipn() {
		$reference_num  = wc_clean( stripslashes( $_REQUEST['GTWREFNO'] ) );
		$transaction_id = wc_clean( stripslashes( $_REQUEST['MERCHANTTXNID'] ) );

		// Check if transaction status is completed.
		if ( $order = wc_get_order( $transaction_id ) ) {
			$txn_status = get_post_meta( $order->id, '_txn_status', true );
			if ( ! empty( $txn_status ) && 'success' == strtolower( $txn_status ) ) {
				WC_Gateway_nPay::log( 'IPN payment is already completed for order ' . $order->get_order_number() );
				return false;
			}
		}

		WC_Gateway_nPay::log( 'Checking IPN response is valid' );

		// Merchant credentials.
		$merchant_id        = $this->gateway->get_option( 'merchant_id' );
		$merchant_username  = $this->gateway->get_option( 'merchant_username' );
		$merchant_password  = $this->gateway->get_option( 'merchant_password' );
		$signature_password = $this->gateway->get_option( 'signature_password' );

		// Hash Merchant and Signature Password with SHA256.
		$hash_merchant_password  = hash( 'sha256', $merchant_username . $merchant_password );
		$hash_signature_password = hash( 'sha256', $signature_password . $merchant_username . $transaction_id );

		try {
			$client = new SoapClient(
				$this->sandbox ? 'http://gateway.sandbox.npay.com.np/websrv/Service.asmx?wsdl' : 'http://gateway.npay.com.np/websrv/Service.asmx?wsdl',
				array(
					'encoding'   => 'UTF-8',
					'trace'      => true,
					'exceptions' => true,
					'cache_wsdl' => false,
				)
			);

			$response = $client->CheckTransactionStatus( array(
				'MerchantId'       => $merchant_id,
				'MerchantUserName' => $merchant_username,
				'MerchantPassword' => $hash_merchant_password,
				'Signature'        => $hash_signature_password,
				'MerchantTxnId'    => $transaction_id,
				'GTWREFNO'         => $reference_num
			) );

			WC_Gateway_nPay::log( 'IPN Response: ' . print_r( $response, true ) );

			// Check to see if the request was valid.
			if ( 0 == $response->CheckTransactionStatusResult->STATUS_CODE ) {
				WC_Gateway_nPay::log( 'Received valid response from nPay' );

				// Log npay transaction amt and other meta data.
				if ( is_object( $response->CheckTransactionStatusResult ) ) {
					$this->save_npay_meta_data( $order, $response->CheckTransactionStatusResult );
				}

				return true;
			}

			WC_Gateway_nPay::log( 'Received invalid response from nPay' );

		} catch ( Exception $e ) {
			WC_Gateway_nPay::log( 'Error response: ' . print_r( $e->getMessage(), true ) );
		}

		return false;
	}

	/**
	 * Check payment amount from IPN matches the order.
	 * @param WC_Order $order
	 * @param int      $amount
	 */
	protected function validate_amount( $order, $amount ) {
		if ( number_format( $order->get_total(), 2, '.', '' ) != number_format( $amount, 2, '.', '' ) ) {
			WC_Gateway_nPay::log( 'Payment error: Amounts do not match (gross ' . $amount . ')' );

			// Put this order on-hold for manual checking.
			$order->update_status( 'on-hold', sprintf( __( 'Validation error: nPay amounts do not match (gross %s).', 'wc-gateway-npay' ), $amount ) );
			exit;
		}
	}

	/**
	 * Handle a completed payment.
	 * @param WC_Order $order
	 * @param array    $requested
	 */
	protected function payment_status_completed( $order, $requested ) {
		if ( $order->has_status( 'completed' ) ) {
			WC_Gateway_nPay::log( 'Aborting, Order #' . $order->id . ' is already complete.' );
			exit;
		}

		$this->validate_amount( $order, get_post_meta( $order->id, '_txn_amt', true ) );

		if ( 'completed' === $requested['payment_status'] ) {
			$this->payment_complete( $order, ( ! empty( $requested['GTWREFNO'] ) ? wc_clean( $requested['GTWREFNO'] ) : '' ), __( 'IPN payment completed', 'wc-gateway-npay' ) );
		} else {
			$this->payment_on_hold( $order, sprintf( __( 'Payment pending: %s', 'wc-gateway-npay' ), $requested['pending_reason'] ) );
		}
	}

	/**
	 * Handle a failed payment.
	 * @param WC_Order $order
	 * @param array    $requested
	 */
	protected function payment_status_failed( $order, $requested ) {
		$order->update_status( 'failed', sprintf( __( 'Payment %s via IPN.', 'wc-gateway-npay' ), wc_clean( $requested['payment_status'] ) ) );
	}

	/**
	 * Save important data from the IPN to the order.
	 * @param WC_Order $order
	 * @param object $response
	 */
	protected function save_npay_meta_data( $order, $response ) {
		if ( ! empty( $response->AMOUNT ) ) {
			update_post_meta( $order->id, '_txn_amt', wc_clean( $response->AMOUNT ) );
		}
		if ( ! empty( $response->TRANSACTION_STATUS ) ) {
			update_post_meta( $order->id, '_txn_status', wc_clean( $response->TRANSACTION_STATUS ) );
		}
		if ( ! empty( $response->GTWREFNO ) ) {
			update_post_meta( $order->id, 'Gateway Reference No.', wc_clean( $response->GTWREFNO ) );
		}
		if ( ! empty( $response->PROCESS_ID ) ) {
			update_post_meta( $order->id, 'Transaction Process ID', wc_clean( $response->PROCESS_ID ) );
		}
	}
}
