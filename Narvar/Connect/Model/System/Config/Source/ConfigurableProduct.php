<?php

namespace Narvar\Connect\Model\System\Config\Source;

class ConfigurableProduct implements \Magento\Framework\Data\OptionSourceInterface
{
    const SIMPLE = 1;
    const PARENT = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::SIMPLE,
                'label' => __('Use simple product data')
            ],
            [
                'value' => self::PARENT,
                'label' => __('Use parent product data')
            ]
        ];
    }
}