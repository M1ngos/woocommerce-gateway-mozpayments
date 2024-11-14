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

		$this->method_title       = _x( 'Mozpayments', 'Dummy payment method', 'Mozpayments' );
		$this->method_description = __( 'Mozpayments permite fazer pagamentos online com plataformas de transações financeiras como E-mola e M-pesa em Moçambique.', 'Mozpayments' );

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
                'title'   => __( 'Ativar/Desativar', 'Mozpayments' ),
                'type'    => 'checkbox',
                'label'   => __( 'Ativar pagamentos móveis', 'Mozpayments' ),
                'default' => 'yes',
            ),
            'title' => array(
                'title'       => __( 'Título', 'Mozpayments' ),
                'type'        => 'text',
                'description' => __( 'Isto controla o título que o utilizador vê durante a finalização da compra.', 'Mozpayments' ),
                'default'     => __( 'Mobile Payment (M-Pesa/E-Mola)', 'Mozpayments' ),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __( 'Descrição', 'Mozpayments' ),
                'type'        => 'textarea',
                'description' => __( 'Descrição da forma de pagamento que o cliente verá no seu checkout.', 'Mozpayments' ),
                'default'     => __( 'Pay with M-Pesa (84/85) or E-Mola (86/87)', 'Mozpayments' ),
                'desc_tip'    => true,
            ),
            'merchant_key' => array(
                'title'       => __( 'Chave do comerciante', 'Mozpayments' ),
                'type'        => 'text',
                'description' => __( 'Introduza a sua chave de comerciante para autenticação API.', 'Mozpayments' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'payment_timeout' => array(
                'title'       => __( 'Tempo limite de pagamento', 'Mozpayments' ),
                'type'        => 'number',
                'description' => __( 'Quantos segundos esperar pela confirmação do pagamento', 'Mozpayments' ),
                'default'     => '120',
                'desc_tip'    => true,
            ),
        );
	}

/**
 * Process the payment and return the result.
 *
 * @param  int  $order_id
 * @return array|void
 */
	public function process_payment($order_id) {
		// Verify nonce
		// if (!isset($_POST['woocommerce-process-checkout-nonce']) || 
		// 	!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['woocommerce-process-checkout-nonce'])), 'woocommerce-process-checkout')) {
		// 	wc_add_notice(__('A verificação de segurança falhou. Atualize a página e tente novamente.', 'Mozpayments'), 'error');
		// 	return;
		// }

		// if (!isset($_POST['nonce']) || 
		// 	!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wc-dummy-payment')) {
		// 	wc_add_notice(__('Nonce verification failed. Please refresh the page and try again.', 'Mozpayments'), 'error');
		// 	return;
		// }


	   // Check if payment_data exists and is an array
	   if (!isset($_POST['payment_data']) || !is_array($_POST['payment_data'])) {
        wc_add_notice(__('Payment data is missing.', 'Mozpayments'), 'error');
        error_log('Payment data is missing or not an array. $_POST: ' . print_r($_POST, true));
        return;
    }

		// Sanitize the payment data
		$payment_data = wp_unslash($_POST['payment_data']); // Unslash first
		if (!is_array($payment_data)) {
			wc_add_notice(__('Payment data is invalid.', 'Mozpayments'), 'error');
			error_log('Payment data is not an array after unslash: ' . print_r($payment_data, true));
			return;
		}

		$nonce = '';
		foreach ($payment_data as $data) {
			if (is_array($data) && isset($data['key'])) {
				if ($data['key'] === 'nonce') {
					$nonce = sanitize_text_field($data['value']); // Sanitize nonce
				}
			}
		}

		// Log the payment data structure
		error_log('Payment data received: ' . print_r($payment_data, true));

		// Verify nonce
		if (empty($nonce) || !wp_verify_nonce($nonce, 'wc-dummy-payment')) {
			wc_add_notice(__('Nonce verification failed. Please refresh the page and try again.', 'Mozpayments'), 'error');
			error_log('Nonce verification failed. Nonce: ' . $nonce);
			return;
		}
			


		$order = wc_get_order($order_id);
		if (!$order) {
			wc_add_notice(__('Pedido inválido.', 'Mozpayments'), 'error');
			return;
		}

		// Validate and sanitize mobile number
		if (!isset($_POST['mobile_number'])) {
			wc_add_notice(__('Número de telefone é obrigatório.', 'Mozpayments'), 'error');
			return;
		}

		$phone_number = sanitize_text_field(wp_unslash($_POST['mobile_number']));
		
		// Validate phone number
		if (!$this->validate_phone_number($phone_number)) {
			wc_add_notice(__('Formato de número de telefone inválido. Utilize os números M-Pesa (84/85) ou E-Mola (86/87).', 'Mozpayments'), 'error');
			return;
		}

		// Determine payment provider
		$prefix = substr($phone_number, 0, 2);
		$api_url = in_array($prefix, ['84', '85']) 
			? 'https://mozpayment.online/api/1.1/wf/pagamentorotativompesa/'
			: 'https://mozpayment.online/api/1.1/wf/pagamentorotativoemola/';

		// Sanitize and validate merchant key
		$merchant_key = sanitize_text_field($this->get_option('merchant_key'));
		// if (empty($merchant_key)) {
		// 	wc_add_notice(__('Configuration error: Missing merchant key.', 'Mozpayments'), 'error');
		// 	return;
		// }

		// Prepare and sanitize request data
		$request_data = array(
			'carteira' => $merchant_key,
			'numero' => $phone_number,
			'quem comprou' => $phone_number,
			'valor' => floatval($order->get_total())
		);

		$response = wp_remote_post($api_url, array(
			'method' => 'POST',
			'timeout' => 120,
			'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
			'body' => $request_data,
			'sslverify' => true
		));

		if (is_wp_error($response)) {
			wc_add_notice(
				sprintf(
					/* translators: %s: error message */
					__('Erro de pagamento: %s', 'Mozpayments'),
					esc_html($response->get_error_message())
				),
				'error'
			);
			return;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (isset($body['status']) && $body['status'] === 'success') {
			// Mark the order as processing
			$order->update_status(
				'processing',
				__('Pagamento recebido, encomenda em processamento.', 'Mozpayments')
			);

			// Empty cart
			WC()->cart->empty_cart();

			return array(
				'result' => 'success',
				'redirect' => $this->get_return_url($order)
			);
		} else {
			$error_message = isset($body['message']) ? $body['message'] : __('Unknown error occurred', 'Mozpayments');
			wc_add_notice(
				sprintf(
					/* translators: %s: error message */
					__('Erro de pagamento: %s', 'Mozpayments'),
					esc_html($error_message)
				),
				'error'
			);
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
			$order->update_status( 'failed', __( 'Subscription payment failed. To make a successful payment using Dummy Payments, please review the gateway settings.', 'Mozpayments' ) );
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
			$message = __( 'Order payment failed. To make a successful payment using Dummy Payments, please review the gateway settings.', 'Mozpayments' );
			$order->update_status( 'failed', $message );
		}
	}
}
