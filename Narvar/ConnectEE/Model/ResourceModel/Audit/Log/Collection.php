<?php
/**
 * Audit Log Resource Model
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\ResourceModel\Audit\Log;

use Narvar\ConnectEE\Model\Audit\Log as AuditLog;
use Narvar\ConnectEE\Model\Audit\Status as AuditStatusModel;
use Narvar\ConnectEE\Helper\Audit\Status as StatusHelper;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime as StdDateTime;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Collection extends AbstractCollection
{
    /**
     * CONST main_table
     */
    const MAIN_TABLE = 'main_table';

    /**
     *  CONST Sales_order
     */
    const TABLE_FOR_JOIN = 'sales_order';

    /**
     *
     * @var \Narvar\ConnectEE\Model\Audit\Status
     */
    protected $auditStatusModel;

    /**
     *
     * @var \Narvar\ConnectEE\Helper\Audit\Status
     */
    protected $auditStatusHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;
    
    /**
     * Constuctor
     *
     * @param AuditStatusModel $auditStatusModel
     * @param StatusHelper $auditStatusHelper
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        AuditStatusModel $auditStatusModel,
        StatusHelper $auditStatusHelper,
        DateTime $dateTime,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->auditStatusModel = $auditStatusModel;
        $this->auditStatusHelper = $auditStatusHelper;
        $this->dateTime = $dateTime;
        
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init('Narvar\ConnectEE\Model\Audit\Log', 'Narvar\ConnectEE\Model\ResourceModel\Audit\Log');
    }

    /**
     * Method to return the collection by status
     *
     * @param int $statusId
     * @return \Magento\Framework\Data\Collection\$this
     */
    public function addStatusFilter($statusId)
    {
        return $this->addFieldToFilter(
            AuditLog::STATUS,
            [
                'eq' => $statusId
            ]
        );
    }

    /**
     * Method to return the collection by status
     *
     * @param int $orderId
     * @return \Magento\Framework\Data\Collection\$this
     */
    public function addOrderIdFilter($orderId)
    {
        return $this->addFieldToFilter(
            AuditLog::ORDER_ID,
            [
                'eq' => $orderId
            ]
        );
    }

    /**
     * Method to return the collection by action
     *
     * @param string $action
     * @return \Magento\Framework\Data\Collection\$this
     */
    public function addActionFilter($action)
    {
        return $this->addFieldToFilter(
            AuditLog::ACTION,
            [
                'eq' => $action
            ]
        );
    }

    /**
     * Method to return the collection by failure api call records
     *
     * @return \Magento\Framework\Data\Collection\$this
     */
    public function addFailureFilter()
    {
        return $this->addFieldToFilter(
            self::MAIN_TABLE . '.' . AuditLog::STATUS,
            [
                'eq' => $this->auditStatusHelper->getFailure()
            ]
        );
    }

    /**
     * Method to return the collection by entity type
     *
     * @param string $entityType
     * @return \Magento\Framework\Data\Collection\$this
     */
    public function addEntityFilter($entityType)
    {
        return $this->addFieldToFilter(
            AuditLog::ENT_TYPE,
            [
                'eq' => $entityType
            ]
        );
    }

    /**
     * Method to return the collection by failure/pending/processing/onhold
     * api call records
     *
     * @return \Magento\Framework\Data\Collection\$this
     */
    public function addNonProcessedFilter()
    {
        return $this->addFieldToFilter(
            AuditLog::STATUS,
            [
                'in' => $this->auditStatusModel->getNonProcessedStatus()
            ]
        );
    }

    /**
     * Method to return the collection the log records
     * by less than given days
     *
     * @param int $days
     * @return \Magento\Framework\Data\Collection\$this
     */
    public function addAuditCleanFilter($days)
    {
        $logIntDays = sprintf('-%s days', $days);

        return $this->addFieldToFilter(
            AuditLog::FINISH_TIME,
            [
                'lteq' => $this->dateTime->date(StdDateTime::DATETIME_PHP_FORMAT, $logIntDays)
            ]
        );
    }

    /**
     * Method to return the collection of log records
     * by withing given date range of request finish time
     *
     * @param DateTime $start
     * @param DateTime $end
     * @return \Magento\Framework\Data\Collection\$this
     */
    public function addBulkDateFilter($start, $end)
    {
        return $this->addFieldToFilter(
            AuditLog::FINISH_TIME,
            [
                'from' => $start,
                'to' => $end,
                'date' => true
            ]
        );
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $joinTableName = $this->getResource()->getTable(self::TABLE_FOR_JOIN);

        if ($storeId) {
            $this->addFieldToFilter($joinTableName . '.store_id', ['eq' => $storeId]);
        }

        return $this->join([$joinTableName => $joinTableName],
            $joinTableName . '.entity_id' . " = " . self::MAIN_TABLE . '.order_id',
            'store_id');
    }
    
    /**
     * Method to update the Log Records
     *
     * @param array $updateData
     * @param string $where
     */
    public function updateRecords($updateData, $where)
    {
        if ($where) {
            $this->getConnection()->update(
                $this->getResource()->getMainTable(),
                $updateData,
                $where
            );
        }
    }

    /**
     * Method to delete the records
     *
     * @param string $where
     */
    public function deleteRecords($where)
    {
        if ($where) {
            $this->getConnection()->delete($this->getResource()->getMainTable(), $where);
        }
    }
}
