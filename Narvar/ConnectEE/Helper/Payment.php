<?php
/**
 * Payment Helper
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Helper;

use Narvar\ConnectEE\Helper\Base;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\App\Helper\Context;

class Payment extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Offline Payment Method Group
     */
    const PAYMENT_OFFLINE_GROUP = 'offline';

    /**
     *
     * @var \Magento\Payment\Helper\Data
     */
    private $payment;

    public function __construct(Context $context, PaymentHelper $payment)
    {
        $this->payment = $payment;
        parent::__construct($context);
    }

    /**
     * Method to get all payment methods
     */
    public function getAllPayments()
    {
        return $this->payment->getPaymentMethods();
    }

    /**
     * Method to get the Offline Payment Methods Code
     *
     * @return multitype
     */
    public function getOfflinePayMethods()
    {
        $methods = [];
        foreach ($this->getAllPayments() as $code => $data) {
            if (isset($data['group']) && $data['group'] == self::PAYMENT_OFFLINE_GROUP) {
                $methods[] = $code;
            }
        }
        
        return $methods;
    }
}
