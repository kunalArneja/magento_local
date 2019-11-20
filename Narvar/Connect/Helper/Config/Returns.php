<?php
/**
 * Configuration Return Helper
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
 * @method string getReturnOrderStatus()
 * @method string getReturnOrderEmail()
 * @method string getAuthKey()
 * @method string getAuthKeyEncrypt()
 * @method string getAuthToken()
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
     * Method to Verfiy return configuration is completed for Community Edition
     *
     * @return boolean
     */
    public function canCreateReturnCE()
    {
        return ($this->getReturnOrderEmail());
    }
}
