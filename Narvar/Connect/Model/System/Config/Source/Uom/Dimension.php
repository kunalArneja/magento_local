<?php
/**
 * Config Unit Of Measurement Dimension Source Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\System\Config\Source\Uom;

use Narvar\Connect\Helper\Uom as UomHelper;

class Dimension implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Method to return Options for Unit Of Measurement in Dimension format
     *
     * @return multitype
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('-- Please Select --')
            ],
            [
                'value' => UomHelper::MILLIMETER,
                'label' => __('Millimeter')
            ],
            [
                'value' => UomHelper::CENTIMETER,
                'label' => __('Centimeter')
            ],
            [
                'value' => UomHelper::METER,
                'label' => __('Meter')
            ],
            [
                'value' => UomHelper::INCH,
                'label' => __('Inch')
            ],
            [
                'value' => UomHelper::FOOT,
                'label' => __('Foot')
            ]
        ];
    }
}
