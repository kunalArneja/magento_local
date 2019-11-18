<?php
/**
 * Configuration Account Helper
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
 * @method string getNarvarApiEndpoint($storeId = null)
 * @method string getNarvarAccountId($storeId = null)
 * @method string getNarvarAuthToken($storeId = null)
 * @method string getNarvarRetailer($storeId = null)
 */
class Account extends Base
{
    /**
     * Narvar Account Connect Config Group
     */
    const CONFIG_GRP = 'account';

    /**
     * Narvar Api Url Config Path
     */
    const NARVAR_API_ENDPOINT = 'narvar_api_endpoint';

    /**
     * Narvar Account Id Config Path
     */
    const NARVAR_ACCOUNT_ID = 'narvar_acc_id';

    /**
     * Narvar Auth Token Config Path
     */
    const NARVAR_AUTH_TOKEN = 'narvar_auth_token';
}
