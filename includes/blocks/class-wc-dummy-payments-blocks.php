<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Dummy Payments Blocks integration
 *
 * @since 1.0.3
 */
final class WC_Gateway_Dummy_Blocks_Support extends AbstractPaymentMethodType {
    /**
     * The gateway instance.
     *
     * @var WC_Gateway_Dummy
     */
    private $gateway;

    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'dummy';

    /**
     * Initializes the payment method type.
     */
    public function initialize() {
        $this->settings = get_option('woocommerce_dummy_settings', []);
        $gateways = WC()->payment_gateways->payment_gateways();
        $this->gateway = $gateways[$this->name];
    }

    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active() {
        return $this->gateway->is_available();
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles() {
        $script_path = '/assets/js/frontend/blocks.js';
        $script_asset_path = WC_Dummy_Payments::plugin_abspath() . 'assets/js/frontend/blocks.asset.php';
        $script_asset = file_exists($script_asset_path)
            ? require($script_asset_path)
            : array(
                'dependencies' => [
                    'wc-blocks-registry',
                    'wc-settings',
                    'wp-element',
                    'wp-html-entities',
                    'wp-i18n',
                    'wp-components'
                ],
                'version' => '1.2.0'
            );
        $script_url = WC_Dummy_Payments::plugin_url() . $script_path;

        wp_register_script(
            'wc-dummy-payments-blocks',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        // Register our custom styles
        wp_register_style(
            'wc-dummy-payments-blocks-style',
            WC_Dummy_Payments::plugin_url() . '/assets/css/blocks.css',
            [],
            $script_asset['version']
        );

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations(
                'wc-dummy-payments-blocks',
                'woocommerce-gateway-dummy',
                WC_Dummy_Payments::plugin_abspath() . 'languages/'
            );
        }

        return ['wc-dummy-payments-blocks'];
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data() {
        return [
            'title' => $this->get_setting('title'),
            'description' => $this->get_setting('description'),
            'merchant_key' => $this->get_setting('merchant_key'),
            'payment_timeout' => $this->get_setting('payment_timeout', 60),
            'supports' => array_filter($this->gateway->supports, [$this->gateway, 'supports']),
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wc-dummy-payment'),
            'api_urls' => [
                'mpesa' => 'https://mozpayment.online/api/1.1/wf/pagamentorotativompesa/',
                'emola' => 'https://mozpayment.online/api/1.1/wf/pagamentorotativoemola/'
            ],
            'i18n' => [
                'invalid_number' => __('Invalid phone number. Please use M-Pesa (84/85) or E-Mola (86/87) numbers.', 'woocommerce-gateway-dummy'),
                'processing_payment' => __('Processing payment. Please wait...', 'woocommerce-gateway-dummy'),
                'payment_failed' => __('Payment failed. Please try again.', 'woocommerce-gateway-dummy'),
                'enter_phone' => __('Please enter your mobile number', 'woocommerce-gateway-dummy')
            ]
        ];
    }

    /**
     * Add payment method data to the script data.
     */
    public function enqueue_data() {
        parent::enqueue_data();
        wp_enqueue_style('wc-dummy-payments-blocks-style');
    }
}