<?php
/**
 * Eav Attribute Options Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Narvar\Connect\Model\Eav\Attributes;

use Magento\Eav\Model\Config;

class Options
{
    /**
     *
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * Constructor
     *
     * @param Config $eavConfig
     */
    public function __construct(
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Method to get the Eav Attribute Options
     *
     * @param string $entityType
     * @param string $attributeCode
     * @return array
     */
    public function getAttributeOptions($entityType, $attributeCode, $withEmpty = false)
    {
        $attribute = $this->eavConfig->getAttribute($entityType, $attributeCode);

        if (!$attribute->getId()) {
            return [
                'value' => '',
                'label' => __('-- Attribute %1 not exist --', $attributeCode)
            ];
        }

        try {
            $options = $attribute->getSource()->getAllOptions();
        } catch (\Exception $e) {

            return [
                'value' => '',
                'label' => __('-- Attribute %1 not exist --', $attributeCode)
            ];
        }
        if ($withEmpty) {
            array_unshift(
                $options,
                [
                    'value' => '',
                    'label' => __('-- Please Select --')
                ]
            );
        }

        return $options;
    }

    /**
     * Method to check the given value is available in attribute options
     *
     * @param string $entityType
     * @param string $attributeCode
     * @param string $attributeValue
     * @return multitype|boolean
     */
    public function getAttributeValue($entityType, $attributeCode, $attributeValue)
    {
        $options = $this->getAttributeOptions($entityType, $attributeCode);

        foreach ($options as $option) {
            if ($option['label'] == $attributeValue) {
                return $option['value'];
            }
        }

        return false;
    }
}
