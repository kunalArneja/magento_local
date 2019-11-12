<?php
/**
 * Configuration General Helper
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
 * @method string getIsActivated()
 * @method string getActivationDate()
 * @method string getAuthKey()
 * @method string getAuthToken()
 */
class General extends Base
{
    /**
     * Narvar Account Connect Config Group
     */
    const CONFIG_GRP = 'general';

    /**
     * Narvar Connect Module activation Status Config field
     */
    const IS_ACTIVATED = 'enable';

    /**
     * Narvar Connect Module activation date Config field
     */
    const ACTIVATION_DATE = 'activation_date';

    /**
     * Narvar Connect Module Authentication key for return request
     */
    const AUTH_KEY = 'auth_key';

    /**
     * Narvar Connect Module Authentication token for return request
     */
    const AUTH_TOKEN = 'auth_token';
}
