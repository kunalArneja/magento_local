<?php
/**
 * Configuration Carrier Helper
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

class Carriers extends Base
{

    /**
     * Return Config Group
     */
    const CONFIG_GRP = 'carriers';

    /**
     * Method to get the allowed carriers of Narvar API
     *
     * @return mixed
     */
    public function getAllowedCarriers()
    {
        $configPath = sprintf('%s/%s', self::CONFIG_SECTION, self::CONFIG_GRP);
        
        return array_values($this->getConfigValue($configPath));
    }
}
