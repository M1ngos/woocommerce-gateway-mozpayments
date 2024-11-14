import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';
import { useEffect, useState } from '@wordpress/element';

const settings = getSetting('dummy_data', {});

/**
 * Payment Method Content Component
 */
const Content = ({ eventRegistration, emitResponse }) => {
    const [phoneNumber, setPhoneNumber] = useState('');
    const [error, setError] = useState('');
    const { onPaymentProcessing } = eventRegistration;

    useEffect(() => {
        const unsubscribe = onPaymentProcessing(() => {
            if (!phoneNumber) {
                return {
                    type: 'error',
                    message: 'Por favor introduza um número de telefone',
                };
            }

            // Validate phone number
            const prefix = phoneNumber.substring(0, 2);
            const validPrefixes = ['84', '85', '86', '87'];
            if (!validPrefixes.includes(prefix) || phoneNumber.length !== 9) {
                return {
                    type: 'error',
                    message: 'Número de telefone inválido. Utilize os números M-Pesa (84/85) ou E-Mola (86/87).',
                };
            }

            console.log(settings.nonce); // Check if this logs the correct nonce value

            return {
                type: 'success',
                meta: {
                    paymentMethodData: {
                        mobile_number: phoneNumber,
                        nonce: settings.nonce, // Include the nonce here
                    },
                },
            };
        });

        return () => unsubscribe();
    }, [onPaymentProcessing, phoneNumber]);

    return (
        <div className="mobile-payment-method-block">
            <div className="mobile-payment-input">
                <label htmlFor="mobile-number">
				Número a cobrar:
                    <input
                        type="tel"
                        id="mobile-number"
                        pattern="[0-9]{9}"
                        maxLength="9"
                        value={phoneNumber}
                        onChange={(e) => {
                            const value = e.target.value.replace(/[^0-9]/g, '');
                            setPhoneNumber(value);
                            setError('');
                        }}
                        className="components-text-control__input"
                        placeholder="8XXXXXXXX"
                    />
                </label>
                {error && <div className="mobile-payment-error">{error}</div>}
                {/* <div className="mobile-payment-info">
                    <small>M-Pesa: Números começados por 84 ou 85</small>
                    <br />
                    <small>E-Mola: Números começados por 86 ou 87</small>
                </div> */}
            </div>
        </div>
    );
};

const Label = (props) => {
	const { PaymentMethodLabel } = props.components;
    return (
        <PaymentMethodLabel
            text={decodeEntities(settings.title) || 'Mobile Payment'}
        />
    );
};

const MobilePaymentMethod = {
    name: 'dummy',
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: () => true,
    ariaLabel: decodeEntities(settings.title) || 'Mobile Payment',
    supports: {
        features: settings.supports || [],
    },
    paymentMethodId: 'dummy',
};

registerPaymentMethod(MobilePaymentMethod);