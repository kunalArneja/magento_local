<?php

namespace Narvar\Connect\Model\System\Config\Source\Batch\Init;

use Narvar\Connect\Model\Batch\Audit\Interval as AuditInterval;

class Interval implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Method to return Options for Batch Initial Period Configuration
     *
     * @return array of terms
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => AuditInterval::LAST_THIRTY_DAYS,
                'label' => __('Last 30 days')
            ],
            [
                'value' => AuditInterval::LAST_FOURTY_FIVE_DAYS,
                'label' => __('Last 45 days')
            ],
            [
                'value' => AuditInterval::LAST_SIXTY_DAYS,
                'label' => __('Last 60 days')
            ],
            [
                'value' => AuditInterval::DISABLED,
                'label' => __('Don\'t send historical data')
            ]
        ];
    }
}
