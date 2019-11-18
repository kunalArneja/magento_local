<?php
/**
 * Billing Data Transformer Model
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Data\Transformer;

use Magento\Directory\Model\Country as CountryModel;
use Narvar\ConnectEE\Helper\Formatter;
use Narvar\ConnectEE\Helper\Config\Status as OrderStatusHelper;
use Narvar\ConnectEE\Helper\Config\Attribute as AttributeHelper;
use Narvar\ConnectEE\Model\Data\DTO;
use Narvar\ConnectEE\Model\Delta\Validator;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Framework\Encryption\EncryptorInterface;
use Narvar\ConnectEE\Model\Data\Transformer\AbstractTransformer;
use Narvar\ConnectEE\Model\Data\Transformer\TransformerInterface;

class Billing extends AbstractTransformer implements TransformerInterface
{

    /**
     * @var \Magento\Payment\Model\Config
     */
    private $paymentConfig;
    
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;
    
    /**
     * Constant value for payment method Saved CC
     */
    const SAVED_CC = 'ccsave';

    /**
     * Constuctor
     *
     * @param PaymentConfig $paymentConfig
     * @param EncryptorInterface $encryptor
     * @param Formatter $formatter
     * @param Validator $deltaValidator
     * @param OrderStatusHelper $orderStatusHelper
     * @param AttributeHelper $configAttributes
     * @param CountryModel $countryModel
     */
    public function __construct(
        PaymentConfig $paymentConfig,
        EncryptorInterface $encryptor,
        Formatter $formatter,
        Validator $deltaValidator,
        OrderStatusHelper $orderStatusHelper,
        AttributeHelper $configAttributes,
        CountryModel $countryModel
    ) {
        $this->paymentConfig = $paymentConfig;
        $this->encryptor = $encryptor;
                
        parent::__construct(
            $formatter,
            $deltaValidator,
            $orderStatusHelper,
            $configAttributes,
            $countryModel
        );
    }
    
    /**
     * Method to prepare the Order billing Infomration into API Format
     *
     * @see \Narvar\ConnectEE\Model\Data\Transformer\TransformerInterface::transform()
     */
    public function transform(DTO $dto)
    {
        return [
            'amount' => $this->formatter->format(
                Formatter::FIELDSET_BILLING,
                'amount',
                $dto->getOrder()->getBaseSubtotal()
            ),
            'payments' => $this->getPaymentInformation($dto->getOrder()->getPayment()),
            'tax_amount' => $this->formatter->format(
                Formatter::FIELDSET_BILLING,
                'tax_amount',
                $dto->getOrder()->getBaseTaxAmount()
            ),
            'tax_rate' => $this->formatter->format(
                Formatter::FIELDSET_BILLING,
                'tax_rate',
                $dto->getOrder()->getBaseTaxAmount()
            ),
            'shipping_handling' => $this->formatter->format(
                Formatter::FIELDSET_BILLING,
                'shipping_handling',
                $dto->getOrder()->getShippingInclTax()
            )
        ];
    }

    /**
     * Method to return the payment Information in API format
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return multitype:boolean string
     */
    private function getPaymentInformation(\Magento\Sales\Model\Order\Payment $payment)
    {
        $payMethod = $payment->getMethod();
        $paymentData = [];
        if ($payMethod == self::SAVED_CC) {
            $ccTypes = $this->paymentConfig->getCcTypes();
            $paymentData[] = [
                'card' => $this->formatter->format(
                    Formatter::FIELDSET_BILLING,
                    'card',
                    $this->encryptor->decrypt($payment->getCcNumberEnc())
                ),
                'is_gift_card' => $this->formatter->format(
                    Formatter::FIELDSET_BILLING,
                    'is_gift_card',
                    false
                ),
                'merchant' => $this->formatter->format(
                    Formatter::FIELDSET_BILLING,
                    'merchant',
                    $ccTypes[$payment->getCcType()]
                ),
                'method' => $this->formatter->format(
                    Formatter::FIELDSET_BILLING,
                    'method',
                    $payment->getMethod()
                ),
                'expiration_date' => $this->formatter->format(
                    Formatter::FIELDSET_BILLING,
                    'expiration_date',
                    date("m/y", strtotime($payment->getCcExpYear() . '-' . $payment->getCcExpMonth()))
                )
            ];
        }
        
        return $paymentData;
    }
}
