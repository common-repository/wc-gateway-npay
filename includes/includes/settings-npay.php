<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for nPay Gateway.
 */
return array(
	'enabled' => array(
		'title'   => __( 'Enable/Disable', 'wc-gateway-npay' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable nPay Payment', 'wc-gateway-npay' ),
		'default' => 'yes'
	),
	'title' => array(
		'title'       => __( 'Title', 'wc-gateway-npay' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'This controls the title which the user sees during checkout.', 'wc-gateway-npay' ),
		'default'     => __( 'nPay', 'wc-gateway-npay' )
	),
	'description' => array(
		'title'       => __( 'Description', 'wc-gateway-npay' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'wc-gateway-npay' ),
		'default'     => __( 'Pay via nPay; you can pay with nPay account securely.', 'wc-gateway-npay' )
	),
	'testmode' => array(
		'title'       => __( 'Sandbox Mode', 'wc-gateway-npay' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable Sandbox Mode', 'wc-gateway-npay' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Enable nPay sandbox to test payments. Sign up for a developer account %shere%s.', 'wc-gateway-npay' ), '<a href="https://gateway.sandbox.npay.com.np/" target="_blank">', '</a>' )
	),
	'debug' => array(
		'title'       => __( 'Debug Log', 'wc-gateway-npay' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'wc-gateway-npay' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log nPay events, such as IPN requests, inside <code>%s</code>', 'wc-gateway-npay' ), wc_get_log_file_path( 'nPay' ) )
	),
	'merchant_details' => array(
		'title'       => __( 'Merchant Credentials', 'wc-gateway-npay' ),
		'type'        => 'title',
		'description' => __( 'Enter your nPay Merchant credentials to process payment via nPay.', 'wc-gateway-npay' ),
	),
	'merchant_id' => array(
		'title'       => __( 'Merchant ID', 'wc-gateway-npay' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'Get your Merchant credentials from nPay.', 'wc-gateway-npay' ),
		'default'     => '',
		'placeholder' => ''
	),
	'merchant_username' => array(
		'title'       => __( 'Merchant Username', 'wc-gateway-npay' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'Get your Merchant credentials from nPay.', 'wc-gateway-npay' ),
		'default'     => '',
		'placeholder' => ''
	),
	'merchant_password' => array(
		'title'       => __( 'Merchant Password', 'wc-gateway-npay' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'Get your Merchant credentials from nPay.', 'wc-gateway-npay' ),
		'default'     => '',
		'placeholder' => ''
	),
	'signature_password' => array(
		'title'       => __( 'Signature Password', 'wc-gateway-npay' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'Get your Merchant credentials from nPay.', 'wc-gateway-npay' ),
		'default'     => '',
		'placeholder' => ''
	)
);
