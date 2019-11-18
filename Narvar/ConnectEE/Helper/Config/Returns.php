<?php
/**
 * Configuration Return Helper
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

/**
 * Below methods will used to get configuration value
 *
 * @method string getReturnOrderStatus()
 * @method string getReturnOrderEmail()
 * @method string getAuthKey()
 * @method string getAuthKeyEncrypt()
 * @method string getAuthToken()
 * @method string getReason()
 * @method string getCondition()
 * @method string getResolution()
 */
class Returns extends Base
{
    /**
     * Return Config Group
     */
    const CONFIG_GRP = 'return';

    /**
     * Order Return - Order Status config path
     */
    const RETURN_ORDER_STATUS = 'order_status';

    /**
     * Order Return Request Notification Email config path
     */
    const RETURN_ORDER_EMAIL = 'return_email';

    /**
     * Narvar Connect Module Authentication key for return request
     */
    const AUTH_KEY = 'auth_key';

    /**
     * Narvar Connect Module Encrypted Authentication key for return request
     */
    const AUTH_KEY_ENCRYPT = 'auth_key_encrypt';

    /**
     * Narvar Connect Module Authentication token for return request
     */
    const AUTH_TOKEN = 'auth_token';
    
    /**
     * Order Return - Enterprise RMA attribute reason default value
     */
    const REASON = 'reason';
    
    /**
     * Order Return - Enterprise RMA attribute reason other key
     */
    const REASON_OTHER_VALUE = 'other';
    
    /**
     * Order Return - Enterprise RMA attribute reason other key
     */
    const REASON_OTHER = 'reason_other';
    
    /**
     * Order Condition - Enterprise RMA attribute condition default value
     */
    const CONDITION = 'condition';
    
    /**
     * Order Resolution - Enterprise RMA attribute resolution default value
     */
    const RESOLUTION = 'resolution';
    
    /**
     * Method to Verfiy return configuration is completed for Enterprise Edition
     *
     * @return boolean
     */
    public function canCreateReturnEE()
    {
        return ($this->getCondition() != '-1' && $this->getResolution() != '-1');
    }
    
    /**
     * Method to Verfiy return configuration is completed for Community Edition
     *
     * @return boolean
     */
    public function canCreateReturnCE()
    {
        return ($this->getReturnOrderEmail());
    }
}
