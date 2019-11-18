<?php
/**
 * Eav Attributes Model
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Eav;

use Magento\Eav\Model\Entity\Type as EavEntityType;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as EavAttributeCollectionFactory;

class Attributes
{

    /**
     * Constant Value Attribute Type Customer
     */
    const CUSTOMER_ENTITY = 'customer';

    /**
     * Constant Value Attribute Type Customer Address
     */
    const CUSTOMER_ADDRESS_ENTITY = 'customer_address';

    /**
     * Constant Value Attribute Type Product
     */
    const PRODUCT_ENTITY = 'catalog_product';

    /**
     * Constant Value Attribute Type Order
     */
    const ORDER_ENTITY = 'order';

    /**
     *
     * @var Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    private $eavAttributeCollectionFactory;

    /**
     *
     * @var Magento\Eav\Model\Entity\Type
     */
    private $eavEntityType;

    /**
     * Constructor
     *
     * @param EavEntityType $eavEntityType
     * @param EavAttributeCollectionFactory $eavAttributeCollectionFactory
     */
    public function __construct(
        EavEntityType $eavEntityType,
        EavAttributeCollectionFactory $eavAttributeCollectionFactory
    ) {
        $this->eavAttributeCollectionFactory = $eavAttributeCollectionFactory;
        $this->eavEntityType = $eavEntityType;
    }

    /**
     * Method to get all customer EAV attributes collection
     *
     * @param array $filter
     * @return customer entity collection object
     */
    public function getCustomerAttributes($filter = [])
    {
        return $this->getEavAttributes($this->eavEntityType->loadByCode(self::CUSTOMER_ENTITY), $filter);
    }

    /**
     * Method to get all customer address EAV attributes collection
     *
     * @param array $filter
     * @return customer address entity collection object
     */
    public function getCustomerAddressAttributes($filter = [])
    {
        return $this->getEavAttributes($this->eavEntityType->loadByCode(self::CUSTOMER_ADDRESS_ENTITY), $filter);
    }

    /**
     * Method to get all product EAV attributes collection
     *
     * @param array $filter
     * @return product entity collection object
     */
    public function getProductAttributes($filter = [])
    {
        return $this->getEavAttributes($this->eavEntityType->loadByCode(self::PRODUCT_ENTITY), $filter);
    }

    /**
     * Method to get all order EAV attributes collection
     *
     * @param array $filter
     * @return order entity collection object
     */
    public function getOrderAttributes($filter = [])
    {
        return $this->getEavAttributes($this->eavEntityType->loadByCode(self::ORDER_ENTITY), $filter);
    }

    /**
     * Get EAV collection object based on the entity given
     *
     * @param object $entityType
     * @param array $filter
     * @return Eav collection
     */
    public function getEavAttributes($entityType, $filter = [])
    {
        $eavAttributes = null;
        $eavAttributes = $this->eavAttributeCollectionFactory->create()
            ->setEntityTypeFilter($entityType)
            ->addFieldToFilter(
                'frontend_label',
                [
                    'notnull' => true
                ]
            )
            ->addFieldToFilter(
                'attribute_code',
                [
                    'neq' => 'quantity_and_stock_status'
                ]
            )
            ->addFieldToFilter(
                'frontend_input',
                [
                    'neq' => 'media_image'
                ]
            );
        
        if (isset($filter['datetime_flag']) && $filter['datetime_flag'] === true) {
            $eavAttributes->addFieldToFilter(
                'frontend_input',
                [
                    'eq' => 'date'
                ]
            );
        } elseif (isset($filter['boolean_flag']) && $filter['boolean_flag'] === true) {
            $eavAttributes->addFieldToFilter(
                'frontend_input',
                [
                    'eq' => 'boolean'
                ]
            );
        } else {
            $eavAttributes->addFieldToFilter(
                'frontend_input',
                [
                    'nin' => [
                        'date',
                        'boolean'
                    ]
                ]
            );
        }
        
        return $eavAttributes;
    }

    /**
     * Form the options array from the fiven EAV attributes collection
     *
     * @param objects $attributes
     * @return array of options
     */
    public function toOptions($attributes)
    {
        $options = [];
        foreach ($attributes as $attribute) {
            $options[] = [
                'value' => sprintf('%s_%s', $attribute->getEntityTypeId(), $attribute->getAttributeCode()),
                'label' => $attribute->getFrontendLabel()
            ];
        }
        
        return $options;
    }
}
