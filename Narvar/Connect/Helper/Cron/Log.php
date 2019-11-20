<?php
/**
 * Cron Log Helper
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Helper\Cron;

use Magento\Framework\App\Helper\Context;
use Narvar\Connect\Model\Cron\Log as CronLog;
use Narvar\Connect\Model\Cron\LogFactory as CronLogFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\ResourceConnection;

class Log extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    /**
     * Constant value for Job Code Bulk Push
     */
    const BULK_PUSH = 'bulk_push';

    /**
     * Constant value for Job Code Audit Clean
     */
    const AUDIT_CLEAN = 'audit_clean';
    
    /**
     * @var \Narvar\Connect\Model\Cron\LogFactory
     */
    private $cronLogFactory;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CronLogFactory $cronLogFactory
     * @param DateTime $dateTime
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        CronLogFactory $cronLogFactory,
        DateTime $dateTime,
        ResourceConnection $resource
    ) {
        $this->cronLogFactory = $cronLogFactory;
        $this->dateTime = $dateTime;
        $this->resource = $resource;
        parent::__construct($context);
    }

    /**
     * Method to update the last execution time for job code audit clean
     *
     * @param $storeId
     * @return \Narvar\Connect\Helper\Cron\Log
     */
    public function updateAuditClean($storeId)
    {
        $this->update(self::AUDIT_CLEAN, $storeId);
        
        return $this;
    }

    /**
     * Method to update the last execution time for job code Bulkpush
     *
     * @param $storeId
     * @return \Narvar\Connect\Helper\Cron\Log
     */
    public function updateBulkPush($storeId)
    {
        $this->update(self::BULK_PUSH, $storeId);
        
        return $this;
    }

    /**
     * Method to get the last execution time of job code Bulkpush
     *
     * @param $storeId
     * @return DateTime
     */
    public function getBulkPush($storeId)
    {
        return $this->lastExecutedTime(self::BULK_PUSH, $storeId);
    }

    /**
     * Method to get the last execution time of job code audit clean
     *
     * @param $storeId
     * @return DateTime
     */
    public function getAuditClean($storeId)
    {
        return $this->lastExecutedTime(self::AUDIT_CLEAN, $storeId);
    }

    /**
     * Method to update the last executed time
     *
     * @param string $jobCode
     * @param $storeId
     */
    public function update($jobCode, $storeId)
    {
        $collection = $this->cronLogFactory->create()->getCollection();
        $collection->addFieldToFilter(CronLog::JOB_CODE, array('eq'=>$jobCode))
            ->addFieldToFilter(CronLog::STORE_ID, array('eq'=>$storeId));

        $collection->getFirstItem()
            ->setLastExecutedAt($this->dateTime->date())
            ->save();
    }

    /**
     * Method to get the last executed time
     *
     * @param string $jobCode
     * @param $storeId
     * @return DateTime
     */
    public function lastExecutedTime($jobCode, $storeId)
    {
        $collection = $this->cronLogFactory->create()->getCollection();
        $collection->addFieldToFilter(CronLog::JOB_CODE, array('eq'=>$jobCode))
            ->addFieldToFilter(CronLog::STORE_ID, array('eq'=>$storeId));

        return $collection->getFirstItem()->getLastExecutedAt();
    }

    /**
     * Method to set the last executed time for specific store
     *
     * @param string $date
     * @param int $storeId
     */
    public function initiate($storeId, $date)
    {
        /** @var \Narvar\Connect\Model\ResourceModel\Cron\Log\Collection $collection */
        $collection = $this->cronLogFactory->create()
            ->getCollection()
            ->addFieldToFilter(CronLog::STORE_ID, array('eq'=>$storeId))
            ->getFirstItem();

        if ($collection->isEmpty()) {
            $bulkPushData = [
                CronLog::JOB_CODE => self::BULK_PUSH,
                CronLog::STORE_ID => $storeId,
                CronLog::LAST_EXECUTED_AT => $date
            ];
            $initialData[] = $bulkPushData;

            $auditCleanData = [
                CronLog::JOB_CODE => self::AUDIT_CLEAN,
                CronLog::STORE_ID => $storeId,
                CronLog::LAST_EXECUTED_AT => $date
            ];
            $initialData[] = $auditCleanData;

            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName(CronLog::TABLE_NAME);
            $connection->insertMultiple($tableName, $initialData);
        }

    }
}
