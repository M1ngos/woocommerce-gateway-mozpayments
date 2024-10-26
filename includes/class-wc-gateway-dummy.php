<?php
/**
 * WC_Gateway_Dummy class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Dummy Payments Gateway
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dummy Gateway.
 *
 * @class    WC_Gateway_Dummy
 * @version  1.10.0
 */
class WC_Gateway_Dummy extends WC_Payment_Gateway {

	/**
	 * Payment gateway instructions.
	 * @var string
	 *
	 */
	protected $instructions;

	/**
	 * Whether the gateway is visible for non-admin users.
	 * @var boolean
	 *
	 */
	protected $hide_for_non_admin_users;

	/**
	 * Unique id for the gateway.
	 * @var string
	 *
	 */
	public $id = 'dummy';

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		
		// $this->icon               = apply_filters( 'woocommerce_dummy_gateway_icon', '' );
		$this->icon                = WP_PLUGIN_URL . '/' . plugin_basename( dirname( __DIR__ ) ) . '/assets/images/icon.jpg';
		$this->has_fields         = false;
		$this->supports           = array(
			'pre-orders',
			'products',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'multiple_subscriptions'
		);

		$this->method_title       = _x( 'Dummy Payment', 'Dummy payment method', 'woocommerce-gateway-dummy' );
		$this->method_description = __( 'Allows dummy payments.', 'woocommerce-gateway-dummy' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title                    = $this->get_option( 'title' );
		$this->description              = $this->get_option( 'description' );
		$this->instructions             = $this->get_option( 'instructions', $this->description );
		$this->hide_for_non_admin_users = $this->get_option( 'hide_for_non_admin_users' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_scheduled_subscription_payment_dummy', array( $this, 'process_subscription_payment' ), 10, 2 );
		add_action ( 'wc_pre_orders_process_pre_order_completion_payment_' . $this->id, array( $this, 'process_pre_order_release_payment' ), 10 );
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {

		$this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'woocommerce-gateway-dummy' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Mobile Payments', 'woocommerce-gateway-dummy' ),
                'default' => 'yes',
            ),
            'title' => array(
                'title'       => __( 'Title', 'woocommerce-gateway-dummy' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-dummy' ),
                'default'     => __( 'Mobile Payment (M-Pesa/E-Mola)', 'woocommerce-gateway-dummy' ),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __( 'Description', 'woocommerce-gateway-dummy' ),
                'type'        => 'textarea',
                'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce-gateway-dummy' ),
                'default'     => __( 'Pay with M-Pesa (84/85) or E-Mola (86/87)', 'woocommerce-gateway-dummy' ),
                'desc_tip'    => true,
            ),
            'merchant_key' => array(
                'title'       => __( 'Merchant Key', 'woocommerce-gateway-dummy' ),
                'type'        => 'text',
                'description' => __( 'Enter your merchant key for API authentication.', 'woocommerce-gateway-dummy' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'payment_timeout' => array(
                'title'       => __( 'Payment Timeout', 'woocommerce-gateway-dummy' ),
                'type'        => 'number',
                'description' => __( 'How many seconds to wait for payment confirmation', 'woocommerce-gateway-dummy' ),
                'default'     => '60',
                'desc_tip'    => true,
            ),
        );
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param  int  $order_id
	 * @return array
	 */
	public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $phone_number = sanitize_text_field($_POST['mobile_number']);
        
        // Validate phone number
        if (!$this->validate_phone_number($phone_number)) {
            wc_add_notice(__('Invalid phone number format. Please use M-Pesa (84/85) or E-Mola (86/87) numbers.', 'woocommerce-gateway-dummy'), 'error');
            return;
        }

        // Determine payment provider
        $prefix = substr($phone_number, 0, 2);
        $api_url = in_array($prefix, ['84', '85']) 
            ? 'https://mozpayment.online/api/1.1/wf/pagamentorotativompesa/'
            : 'https://mozpayment.online/api/1.1/wf/pagamentorotativoemola/';

        $response = wp_remote_post($api_url, array(
            'method' => 'POST',
            'timeout' => 45,
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
            'body' => array(
                'carteira' => $this->get_option('merchant_key'),
                'numero' => $phone_number,
                'quem comprou' => $phone_number,
                'valor' => $order->get_total()
            )
        ));

        if (is_wp_error($response)) {
            wc_add_notice(__('Payment error:', 'woocommerce-gateway-dummy') . $response->get_error_message(), 'error');
            return;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($body['status'] === 'success') {
            // Mark as pending payment
            $order->update_status('pending', __('Awaiting mobile payment confirmation.', 'woocommerce-gateway-dummy'));
            
            // Empty cart
            WC()->cart->empty_cart();

            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );
        } else {
            wc_add_notice(__('Payment error:', 'woocommerce-gateway-dummy') . $body['message'], 'error');
            return;
        }
    }

	private function validate_phone_number($phone_number) {
        // Check if number starts with valid prefixes and is 9 digits
        $valid_prefixes = ['84', '85', '86', '87'];
        $prefix = substr($phone_number, 0, 2);
        return in_array($prefix, $valid_prefixes) && strlen($phone_number) === 9;
    }

	/**
	 * Process subscription payment.
	 *
	 * @param  float     $amount
	 * @param  WC_Order  $order
	 * @return void
	 */
	public function process_subscription_payment( $amount, $order ) {
		$payment_result = $this->get_option( 'result' );

		if ( 'success' === $payment_result ) {
			$order->payment_complete();
		} else {
			$order->update_status( 'failed', __( 'Subscription payment failed. To make a successful payment using Dummy Payments, please review the gateway settings.', 'woocommerce-gateway-dummy' ) );
		}
	}

	/**
	 * Process pre-order payment upon order release.
	 *
	 * Processes the payment for pre-orders charged upon release.
	 *
	 * @param WC_Order $order The order object.
	 */
	public function process_pre_order_release_payment( $order ) {
		$payment_result = $this->get_option( 'result' );

		if ( 'success' === $payment_result ) {
			$order->payment_complete();
		} else {
			$message = __( 'Order payment failed. To make a successful payment using Dummy Payments, please review the gateway settings.', 'woocommerce-gateway-dummy' );
			$order->update_status( 'failed', $message );
		}
	}
}
