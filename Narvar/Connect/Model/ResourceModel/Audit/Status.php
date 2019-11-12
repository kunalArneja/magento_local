<?php
/**
 * Audit Status Resource Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\ResourceModel\Audit;

use Narvar\Connect\Model\Audit\Status as AuditStatusModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Status extends AbstractDb
{

    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init(AuditStatusModel::TABLE_NAME, AuditStatusModel::STATUS_ID);
    }
}
