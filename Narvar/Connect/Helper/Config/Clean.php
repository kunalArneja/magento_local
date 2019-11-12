<?php

namespace Narvar\Connect\Helper\Config;

use Narvar\Connect\Helper\Base;

/**
 * Below methods will used to get configuration value
 *
 * @method string getBatchBulkPushFreq()
 * @method string getBatchFirstPushTime()
 * @method string getBatchAuditCleanInterval()
 */
class Clean extends Base
{

    /**
     * Batch Process Bulk Upload Start Time config path
     */
    const CLEAN_UP_TIME = 'clean_up/clean_up_time';

    /**
     * Batch Process Audit Clean Interval config path
     */
    const BATCH_AUDIT_CLEAN_INTERVAL = 'clean_up/audit_cleanup_interval';

    /**
     * @param $storeId
     * @return mixed
     */
    public function getCleanUpTimeByStore($storeId)
    {
        $path = sprintf('%s/%s', self::CONFIG_SECTION, self::CLEAN_UP_TIME);
        return $this->getConfigValue($path, $storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getAuditCleanIntervalByStore($storeId){
        $path = sprintf('%s/%s', self::CONFIG_SECTION,  self::BATCH_AUDIT_CLEAN_INTERVAL);
        return $this->getConfigValue($path, $storeId);
    }
}
