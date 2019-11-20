<?php
/**
 * Config Batch Frequency Source Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\System\Config\Source\Batch;

use Narvar\Connect\Model\Batch\Frequency as FrequencyModel;

class Frequency implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Method to return Options for Batch Frequency Configuration
     *
     * @return array of frequencies
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('-- Please Select --')
            ],
            [
                'value' => FrequencyModel::ONE_TIME_A_DAY,
                'label' => __('1 time a day')
            ],
            [
                'value' => FrequencyModel::EVERY_TWELVE_HOURS_A_DAY,
                'label' => __('Every 12 Hours')
            ],
            [
                'value' => FrequencyModel::EVERY_EIGHT_HOURS_A_DAY,
                'label' => __('Every 8 Hours')
            ],
            [
                'value' => FrequencyModel::EVERY_SIX_HOURS_A_DAY,
                'label' => __('Every 6 Hours')
            ],
            [
                'value' => FrequencyModel::EVERY_FOUR_HOURS_A_DAY,
                'label' => __('Every 4 Hours')
            ],
            [
                'value' => FrequencyModel::EVERY_TW0_HOURS_A_DAY,
                'label' => __('Every 2 Hours')
            ],
            [
                'value' => FrequencyModel::EVERY_HOUR_A_DAY,
                'label' => __('Every 1 Hours')
            ]
        ];
    }
}
