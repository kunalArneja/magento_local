<?php
/**
 * Customer Data Transformer
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Data\Transformer;

use Magento\Customer\Model\Data\Customer as CustomerModel;
use Magento\Directory\Model\Country as CountryModel;
use Narvar\ConnectEE\Helper\Formatter;
use Narvar\ConnectEE\Helper\Config\Status as OrderStatusHelper;
use Narvar\ConnectEE\Helper\Config\Attribute as AttributeHelper;
use Narvar\ConnectEE\Model\Data\DTO;
use Narvar\ConnectEE\Model\Delta\Validator;
use Narvar\ConnectEE\Model\Data\Transformer\AbstractTransformer;
use Narvar\ConnectEE\Model\Data\Transformer\TransformerInterface;
use Magento\Customer\Model\Group as CustomerGroup;

class Customer extends AbstractTransformer implements TransformerInterface
{

    /**
     * @var \Magento\Customer\Model\Group
     */
    private $customerGroup;
    
    /**
     * Constructor
     *
     * @param CustomerGroup $customerGroup
     * @param Formatter $formatter
     * @param Validator $deltaValidator
     * @param OrderStatusHelper $orderStatusHelper
     * @param AttributeHelper $configAttributes
     * @param CountryModel $countryModel
     */
    public function __construct(
        CustomerGroup $customerGroup,
        Formatter $formatter,
        Validator $deltaValidator,
        OrderStatusHelper $orderStatusHelper,
        AttributeHelper $configAttributes,
        CountryModel $countryModel
    ) {
        $this->customerGroup = $customerGroup;
        
        parent::__construct(
            $formatter,
            $deltaValidator,
            $orderStatusHelper,
            $configAttributes,
            $countryModel
        );
    }

    /**
     * Method to prepare the customer data in Required Narvar API Format
     *
     * @see \Narvar\ConnectEE\Model\Data\Transformer\TransformerInterface::transform()
     */
    public function transform(DTO $dto)
    {
        return [
            'customer_id' => $this->formatter->format(
                Formatter::FIELDSET_CUSTOMER,
                'customer_id',
                $dto->getOrder()->getCustomerId() != '' ? $dto->getOrder()->getCustomerId() : 0
            ),
            'email' => $this->formatter->format(
                Formatter::FIELDSET_ADDRESS,
                'email',
                $dto->getOrder()->getCustomerEmail()
            ),
            'first_name' => $this->formatter->format(
                Formatter::FIELDSET_ADDRESS,
                'first_name',
                $dto->getOrder()->getCustomerFirstname()
            ),
            'last_name' => $this->formatter->format(
                Formatter::FIELDSET_ADDRESS,
                'last_name',
                $dto->getOrder()->getCustomerLastname()
            ),
            'phone' => $this->formatter->format(
                Formatter::FIELDSET_ADDRESS,
                'phone',
                $dto->getOrder()->getBillingAddress()->getTelephone()
            ),
            'customer_type' => $this->formatter->format(
                Formatter::FIELDSET_CUSTOMER,
                'customer_type',
                $this->getCustomerGroup($dto->getOrder())
            ),
            'notification_pref' => $this->getNotificationPreference($dto)
        ];
    }

    /**
     * Method to get the notification pref value
     *
     * @param DTO $dto
     * @return multitype:|multitype:unknown
     */
    private function getNotificationPreference(DTO $dto)
    {
        $notificationPreference = [];
        
        if ($this->configAttributes->getAttrNotificationPref() == '-1') {
            return $notificationPreference;
        }
        
        $notPrefVal = $this->getAttributeValue(
            Formatter::FIELDSET_CUSTOMER,
            $this->configAttributes->getAttrNotificationPref(),
            $dto,
            null,
            AttributeHelper::ATTR_NOTIFICATION_PREF
        );
        
        if (! empty($notPrefVal)) {
            $notificationPreference[] = $notPrefVal;
        }
        
        return $notificationPreference;
    }
        
    /**
     * Method to get the customer group name
     *
     * @param \Magento\Sales\Model\Order $order
     */
    private function getCustomerGroup(\Magento\Sales\Model\Order $order)
    {
        return $this->customerGroup->load($order->getCustomerGroupId())->getCustomerGroupCode();
    }
}
