<?php
/**
 * Config Batch Time Source Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\System\Config\Source\Batch;

class Time implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Method to return Options for Batch start time Configuration
     *
     * @return array of start timings
     */
    public function toOptionArray()
    {
        $options = [
            '' => __('-- Please Select --')
        ];
        
        for ($startTime = 0; $startTime <= 23; $startTime ++) {
            $options[$startTime] = $startTime;
        }

        return $options;
    }
}
