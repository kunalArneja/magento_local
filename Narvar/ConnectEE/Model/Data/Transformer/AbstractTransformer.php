<?php
/**
 * Abtract Transformer
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Data\Transformer;

use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\Order\Address as OrderAddressModel;
use Magento\Directory\Model\Country as CountryModel;
use Narvar\ConnectEE\Helper\Formatter;
use Narvar\ConnectEE\Helper\Config\Status as OrderStatusHelper;
use Narvar\ConnectEE\Helper\Config\Attribute as AttributeHelper;
use Narvar\ConnectEE\Model\Data\DTO;
use Narvar\ConnectEE\Model\Delta\Validator;
use Narvar\ConnectEE\Model\Eav\Attributes as EavAttributesModel;
use Symfony\CS\Fixer\Contrib\ProtectedToPrivateFixer;

class AbstractTransformer
{
    /**
     *
     * @var \Narvar\ConnectEE\Helper\Formatter
     */
    protected $formatter;
    
    /**
     *
     * @var \Narvar\ConnectEE\Model\Delta\Validator
     */
    protected $deltaValidator;
    
    /**
     *
     * @var \Narvar\ConnectEE\Helper\Config\Status
     */
    protected $orderStatusHelper;
    
    /**
     *
     * @var \Narvar\ConnectEE\Helper\Config\Attribute
     */
    protected $configAttributes;
    
    /**
     *
     * @var \Magento\Directory\Model\Country
     */
    protected $countryModel;
    
    /**
     * Constructor
     *
     * @param Formatter $formatter
     * @param Validator $deltaValidator
     * @param OrderStatusHelper $orderStatusHelper
     * @param AttributeHelper $configAttributes
     * @param CountryModel $countryModel
     */
    public function __construct(
        Formatter $formatter,
        Validator $deltaValidator,
        OrderStatusHelper $orderStatusHelper,
        AttributeHelper $configAttributes,
        CountryModel $countryModel
    ) {
        $this->formatter = $formatter;
        $this->deltaValidator = $deltaValidator;
        $this->orderStatusHelper = $orderStatusHelper;
        $this->configAttributes = $configAttributes;
        $this->countryModel = $countryModel;
    }
    
    /**
     * Method to form the address data
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @return multitype:string NULL
     */
    protected function prepareAddressInfo(\Magento\Sales\Model\Order\Address $address)
    {
        $contactInfo = [
            'email' => $this->formatter->format(
                Formatter::FIELDSET_ADDRESS,
                'email',
                $address->getEmail()
            ),
            'first_name' => $this->formatter->format(
                Formatter::FIELDSET_ADDRESS,
                'first_name',
                $address->getFirstname()
            ),
            'last_name' => $this->formatter->format(
                Formatter::FIELDSET_ADDRESS,
                'last_name',
                $address->getLastname()
            ),
            'phone' => $this->formatter->format(
                Formatter::FIELDSET_ADDRESS,
                'phone',
                $address->getTelephone()
            ),
        ];
    
        $addressLoc = $this->prepareAddressLocationInfo($address);
    
        return array_merge($contactInfo, $addressLoc);
    }
    
    /**
     * Method to form the address data
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @return multitype:string NULL
     */
    protected function prepareAddressLocationInfo(\Magento\Sales\Model\Order\Address $address)
    {
        $street = $address->getStreet();
        
        return [
            'address' => [
                'city' => $this->formatter->format(
                    Formatter::FIELDSET_ADDRESS,
                    'city',
                    $address->getCity()
                ),
                'country' => $this->formatter->format(
                    Formatter::FIELDSET_ADDRESS,
                    'country',
                    $this->countryModel->loadByCode($address->getCountryId())->getName()
                ),
                'state' => $this->formatter->format(
                    Formatter::FIELDSET_ADDRESS,
                    'state',
                    $address->getRegion()
                ),
                'street_1' => $this->formatter->format(
                    Formatter::FIELDSET_ADDRESS,
                    'street_1',
                    isset($street[0]) ? $street[0] : ''
                ),
                'street_2' => $this->formatter->format(
                    Formatter::FIELDSET_ADDRESS,
                    'street_2',
                    isset($street[1]) ? $street[1] : ''
                ),
                'zip' => $this->formatter->format(
                    Formatter::FIELDSET_ADDRESS,
                    'zip',
                    $address->getPostcode()
                )
            ]
        ];
    }
    
    /**
     * Method to get the Attribute value for each attributes and return it by using key value
     *
     * @param string $fieldGroup
     * @param array $configValues
     * @param DTO $dto
     * @param Item $orderItem
     * @return multitype
     */
    protected function getAttributeValueByKey(
        $fieldGroup,
        $configValues,
        DTO $dto,
        Item $orderItem = null
    ) {
        $attributeData = [];
        foreach ($configValues as $field => $configValue) {
            $attrValue = '';
            $attrValue = $this->getAttributeValue($fieldGroup, $configValue, $dto, $orderItem, $field);
            $attributeData[$field] = $attrValue;
        }
        
        return $attributeData;
    }

    /**
     * Method to get the Attribute value for each attributes and return it by using attribute code value
     *
     * @param string $fieldGroup
     * @param array $configValues
     * @param DTO $dto
     * @param Item $orderItem
     * @return multitype
     */
    protected function getAttributeValueByCode(
        $fieldGroup,
        $configValues,
        DTO $dto,
        Item $orderItem = null
    ) {
        $attributeData = [];
        
        foreach ($configValues as $configValue) {
            $attrCode = $this->configAttributes->extractAttributeCode($configValue);
            $attributeData[$attrCode] = $this->getAttributeValue($fieldGroup, $configValue, $dto, $orderItem, null);
        }
        
        return $attributeData;
    }
    
    /**
     * Method to get the Attribute value by Entity Type
     *
     * @param string $fieldGroup
     * @param string $configValue
     * @param DTO $dto
     * @param Item $orderItem
     * @param string $field
     */
    protected function getAttributeValue(
        $fieldGroup,
        $configValue,
        DTO $dto,
        Item $orderItem = null,
        $field = null
    ) {
        $entityTypeId = $this->configAttributes->extractEntityTypeId($configValue);
        $entityTypeCode = '';
        if ($entityTypeId && $entityTypeId != '-1') {
            $entityTypeCode = $this->configAttributes->getEntityTypeCode($entityTypeId);
            $attributeCode = $this->configAttributes->extractAttributeCode($configValue);
        }

        $value = '';
        
        switch ($entityTypeCode) {
            case EavAttributesModel::PRODUCT_ENTITY:
                if ($orderItem !== null) {
                    if($orderItem->getProduct()) {
                        $value = $this->getProductAttrVal($orderItem->getProduct(), $attributeCode);
                        if (is_array($value)) {
                            $value = $value[0];
                        }
                    }
                }
                break;
            case EavAttributesModel::ORDER_ENTITY:
                $value = $dto->getOrder()[$attributeCode];
                break;
            case EavAttributesModel::CUSTOMER_ENTITY:
                $value = $this->getCustomerAttrVal($dto->getCustomer(), $attributeCode);
                break;
            case EavAttributesModel::CUSTOMER_ADDRESS_ENTITY:
                $value = $this->getCustomerAddressAttrVal($dto->getOrder()->getBillingAddress(), $attributeCode);
                break;
        }
        
        return $this->formatter->format($fieldGroup, $field, $value);
    }

    /**
     * Method to return the attribute value of product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attributeCode
     * @return multitype
     */
    protected function getProductAttrVal(\Magento\Catalog\Model\Product $product, $attributeCode)
    {
        $productData = $product[$attributeCode];
        
        if ($attributeCode == AttributeHelper::ATTR_COLOR_ID) {
            $productData = $product[AttributeHelper::ATTR_COLOR];
        }
        
        if ($attributeCode == AttributeHelper::ATTR_SIZE_ID) {
            $productData = $product[AttributeHelper::ATTR_SIZE];
        }
        
        if ($product->getResource()->getAttribute($attributeCode)) {
            $attribute = $product->getResource()->getAttribute($attributeCode);
            if ($attribute->getFrontendInput() === 'select' || $attribute->getFrontendInput() === 'multiselect') {
                $productData = $attribute->getFrontend()->getValue($product);
                $productData = ($productData != 'No') ? $productData : '';
            }
        }
        
        return $productData;
    }

    /**
     * Method to return the attribute value of customer
     *
     * @param CustomerModel $customer
     * @param string $attributeCode
     * @return multitype
     */
    protected function getCustomerAttrVal(CustomerModel $customer, $attributeCode)
    {
        $customerData = '';
        
        if (!empty($customer->getData())) {
            $customerData = $customer->getData($attributeCode);
        }
        
        return $customerData;
    }

    /**
     * Method to return the attribute value of customer address
     *
     * @param OrderAddressModel $address
     * @param string $attributeCode
     * @return multitype
     */
    protected function getCustomerAddressAttrVal(OrderAddressModel $address, $attributeCode)
    {
        return $address[$attributeCode];
    }

    /**
     * Method to get the Order Item Status
     *
     * @param Magento\Sales\Model\Order\Item;
     * @return string
     */
    public function getItemStatus(Item $orderItem)
    {
        $orderedQty = $orderItem->getQtyOrdered();
        $shippedQty = $orderItem->getQtyShipped();
        $canceledQty = $orderItem->getQtyCanceled();
        $refundedQty = $orderItem->getQtyRefunded();
        $invoicedQty = $orderItem->getQtyInvoiced();
        $returnedQty = $orderItem->getQtyReturned();
        
        if ($this->isOrdered($canceledQty, $shippedQty, $refundedQty, $returnedQty)) {
            return $this->orderStatusHelper->getOrderedStatus();
        }

        if ($this->isCancelled($orderedQty, $canceledQty, $refundedQty)) {
            return $this->orderStatusHelper->getCancelledStatus();
        }
        
        if ($this->isReturned($orderedQty, $returnedQty)) {
            return $this->orderStatusHelper->getReturnedStatus();
        }
        
        if ($this->isShipped($orderedQty, $canceledQty, $shippedQty, $refundedQty, $returnedQty)) {
            return $this->orderStatusHelper->getShippedStatus();
        }
        
        if ($this->isPartial($orderedQty, $shippedQty)) {
            return $this->orderStatusHelper->getPartialStatus();
        }
    
        return $this->orderStatusHelper->getNotShippedStatus();
    }
    
    /**
     * Method to verify order item status is ordered
     *
     * @param float $canceledQty
     * @param float $shippedQty
     * @param float $refundedQty
     * @param float $returnedQty
     * @return boolean
     */
    protected function isOrdered($canceledQty, $shippedQty, $refundedQty, $returnedQty)
    {
        return ($canceledQty == 0 && $shippedQty == 0 && $refundedQty == 0 && $returnedQty == 0);
    }

    /**
     * Method to verify is shipment partial for Order item
     *
     * @param float $orderedQty
     * @param float $shippedQty
     * @return boolean
     */
    protected function isPartial($orderedQty, $shippedQty)
    {
        if ($shippedQty > 0 && $orderedQty != $shippedQty) {
            return true;
        }
        
        return false;
    }

    /**
     * Method to verify is order item is fully cancelled
     *
     * @param float $orderedQty
     * @param float $canceledQty
     * @return boolean
     */
    protected function isCancelled($orderedQty, $canceledQty, $refundedQty)
    {
        return ($canceledQty == $orderedQty || $refundedQty == $orderedQty);
    }
    
    /**
     * Method to verify Order Item is fully returned
     *
     * @param float $orderedQty
     * @param float $returnedQty
     * @return boolean
     */
    protected function isReturned($orderedQty, $returnedQty)
    {
        return ($orderedQty == $returnedQty);
    }

    /**
     * Method to verify is order item is fully shipped
     *
     * @param float $orderedQty
     * @param float $canceledQty
     * @param float $shippedQty
     * @param float $refundedQty
     * @param float $returnedQty
     * @return boolean
     */
    protected function isShipped($orderedQty, $canceledQty, $shippedQty, $refundedQty, $returnedQty)
    {
        if ($canceledQty == 0 && $refundedQty == 0 && $returnedQty == 0 && $orderedQty == $shippedQty) {
            return true;
        }
        
        if ($canceledQty > 0 && $shippedQty > 0 && ($orderedQty - $canceledQty) == ($shippedQty - $canceledQty)) {
            return true;
        }
        
        if ($refundedQty > 0 && $shippedQty > 0 && ($orderedQty - $refundedQty) == ($shippedQty - $refundedQty)) {
            return true;
        }
        
        if ($returnedQty > 0 && $shippedQty > 0 && ($orderedQty - $returnedQty) == ($shippedQty - $returnedQty)) {
            return true;
        }
        
        return false;
    }
}
