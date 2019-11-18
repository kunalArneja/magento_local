<?php
/**
 * Config Unit Of Measurement Weight Source Model
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\System\Config\Source\Uom;

use Narvar\ConnectEE\Helper\Uom as UomHelper;

class Weight implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Method to return Options for Unit Of Measurement Weights
     *
     * @return multitype
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' =>__('-- Please Select --')
            ],
            [
                'value' => UomHelper::GRAM,
                'label' =>__('Gram')
            ],
            [
                'value' => UomHelper::KILOGRAM,
                'label' =>__('Kilogram')
            ],
            [
                'value' => UomHelper::POUND,
                'label' =>__('Pound')
            ]
        ];
    }
}
