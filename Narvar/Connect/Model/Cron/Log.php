<?php
/**
 * Cron Log Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\Cron;

use Magento\Framework\Model\AbstractModel;

class Log extends AbstractModel
{

    /**
     * Constant Value of Table Name
     */
    const TABLE_NAME = 'narvar_cron_log';

    /**
     * Table field name log_id
     */
    const LOG_ID = 'log_id';

    /**
     * Table field name job_code
     */
    const JOB_CODE = 'job_code';

    /**
     * Table field name store_id
     */
    const STORE_ID = 'store_id';

    /**
     * Table field name last_executed_at
     */
    const LAST_EXECUTED_AT = 'last_executed_at';
    
    /**
     * Constant value for Job Code Bulk Push
     */
    const BULK_PUSH = 'bulk_push';
    
    /**
     * Constant value for Job Code Audit Clean
     */
    const AUDIT_CLEAN = 'audit_clean';

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Narvar\Connect\Model\ResourceModel\Cron\Log');
    }
}
