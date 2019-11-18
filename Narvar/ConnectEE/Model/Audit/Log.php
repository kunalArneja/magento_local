<?php
/**
 * Audit Log Model
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Audit;

use Magento\Framework\Model\AbstractModel;

class Log extends AbstractModel
{

    /**
     * Constant Value of Table Name
     */
    const TABLE_NAME = 'narvar_audit_log';

    /**
     * Table field name log_id
     */
    const LOG_ID = 'log_id';

    /**
     * Table field name order_id
     */
    const ORDER_ID = 'order_id';

    /**
     * Table field name order_inc_id
     */
    const ORDER_INC_ID = 'order_inc_id';

    /**
     * Table field name action
     */
    const ACTION = 'action';

    /**
     * Table field name entity_type
     */
    const ENT_TYPE = 'entity_type';

    /**
     * Table field name request_data
     */
    const REQ_DATA = 'request_data';

    /**
     * Table field name request_time
     */
    const REQ_TIME = 'request_time';

    /**
     * Table field name finish_time
     */
    const FINISH_TIME = 'finish_time';

    /**
     * Table field name status
     */
    const STATUS = 'status';

    /**
     * Table field name slug
     */
    const SLUG = 'slug';

    /**
     * Table field name response
     */
    const RESPONSE = 'response';

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Narvar\ConnectEE\Model\ResourceModel\Audit\Log');
    }
}
