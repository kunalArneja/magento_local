<?php
/**
 * Config Attributes Source Model
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\System\Config\Source;

use Narvar\ConnectEE\Model\Eav\AttributesFactory;

class Attributes
{
    /**
     * @var \Narvar\ConnectEE\Model\Eav\AttributesFactory
     */
    private $connectEavAttributesFactory;

    /**
     * Constructor
     *
     * @param AttributesFactory $connectEavAttributesFactory
     */
    public function __construct(
        AttributesFactory $connectEavAttributesFactory
    ) {
        $this->connectEavAttributesFactory = $connectEavAttributesFactory;
    }

    /**
     * Method to return Customer, Order, Address and Product Attributes sets as drop down
     *
     * @param boolean $isMultiSelect
     * @param array $filter
     * @return multitype
     */
    public function toOptionArray($isMultiSelect, $filter = [])
    {
        $connectEavAttributesFactory = $this->connectEavAttributesFactory->create();
        $productAttributes = $connectEavAttributesFactory->getProductAttributes($filter);
        $orderAttributes = $connectEavAttributesFactory->getOrderAttributes($filter);
        $customerAttributes = $connectEavAttributesFactory->getCustomerAttributes($filter);
        $customerAddrAttributes = $connectEavAttributesFactory->getCustomerAddressAttributes($filter);

        return [
            [
                'value' => '-1',
                'label' =>__('-- Please Select --')
            ],
            $connectEavAttributesFactory::PRODUCT_ENTITY => [
                'value' => $connectEavAttributesFactory->toOptions($productAttributes),
                'label' =>__('Product')
            ],
            $connectEavAttributesFactory::ORDER_ENTITY => [
                'value' => $connectEavAttributesFactory->toOptions($orderAttributes),
                'label' =>__('Order')
            ],
            $connectEavAttributesFactory::CUSTOMER_ENTITY => [
                'value' => $connectEavAttributesFactory->toOptions($customerAttributes),
                'label' =>__('Customer')
            ],
            $connectEavAttributesFactory::CUSTOMER_ADDRESS_ENTITY => [
                'value' => $connectEavAttributesFactory->toOptions($customerAddrAttributes),
                'label' =>__('Customer Address')
            ]
        ];
    }
}
