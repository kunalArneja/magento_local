<?php
/**
 * Configuration Status Helper
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Helper\Config;

use Narvar\Connect\Helper\Base;

/**
 * Below methods will used to get configuration value
 *
 * @method string getFulfilledStatus()
 * @method string getNotShippedStatus()
 * @method string getShippedStatus()
 * @method string getCancelledStatus()
 * @method string getReturnedStatus()
 * @method string getPartialStatus()
 * @method string getOrderedStatus()
 */
class Status extends Base
{
    /**
     * Narvar - Magento Order Status Map Group
     */
    const CONFIG_GRP = 'status_map';

    /**
     * Order Fullfilled Status config path
     */
    const FULFILLED_STATUS = 'fulfilled';

    /**
     * Order Fullfilled Status config path
     */
    const NOT_SHIPPED_STATUS = 'not_shipped';

    /**
     * Order Fullfilled Status config path
     */
    const SHIPPED_STATUS = 'shipped';

    /**
     * Order Cancelled Status config path
     */
    const CANCELLED_STATUS = 'cancelled';

    /**
     * Order Returned Status config path
     */
    const RETURNED_STATUS = 'returned';

    /**
     * Order Partial Status config path
     */
    const PARTIAL_STATUS = 'partial';
    
    /**
     * Order Ordered Status config path
     */
    const ORDERED_STATUS = 'ORDER_CREATE';
}
