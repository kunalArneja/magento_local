<?php
/**
 * Configuration Batch Helper
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Helper\Config;

use Narvar\ConnectEE\Helper\Base;

class Batch extends Base
{
    /**
     * Narvar Account Connect Config Group
     */
    const CONFIG_GRP = 'batch';

    /**
     * Batch Process Bulk Upload Frequency config path
     */
    const BATCH_BULK_PUSH_FREQ = 'bulk_push_frequency';

    /**
     * Batch Process Bulk Upload Start Time config path
     */
    const BATCH_PUSH_TIME = 'bulk_push_time';

    /**
     * @param $storeId
     * @return mixed
     */
    public function getBatchPushTimeByStore($storeId)
    {
        $path = sprintf('%s/%s/%s', self::CONFIG_SECTION, self::CONFIG_GRP, self::BATCH_PUSH_TIME);
        return $this->getConfigValue($path, $storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getBatchBulkPushFreqByStore($storeId){
        $path = sprintf('%s/%s/%s', self::CONFIG_SECTION, self::CONFIG_GRP, self::BATCH_BULK_PUSH_FREQ);
        return $this->getConfigValue($path, $storeId);
    }

}
