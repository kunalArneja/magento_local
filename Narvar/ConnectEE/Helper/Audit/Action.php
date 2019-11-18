<?php
/**
 * Audit Log Action Helper
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Helper\Audit;

use Narvar\ConnectEE\Helper\Base;

class Action extends Base
{

    /**
     * Constant value narvar api call action create
     */
    const ACTION_CREATE = 'create';

    /**
     * Constant value narvar api call action update
     */
    const ACTION_UPDATE = 'update';

    /**
     * Constant value narvar api call action delte
     */
    const ACTION_DELETE = 'delete';
}
