<?php
/**
 * Batch Audit Clean Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\Batch\Audit;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Narvar\Connect\Helper\Config\Clean as CleanHelper;
use Narvar\Connect\Model\Audit\Log as AuditLog;
use Narvar\Connect\Helper\Cron\Log as CronLogHelper;
use Narvar\Connect\Helper\Config\Activation;
use Narvar\Connect\Model\ResourceModel\Audit\Log\CollectionFactory as AuditLogCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Narvar\Connect\Model\Batch\Audit\Clean\DeleteFiles as DeleteFilesHelper;

class Clean
{

    /**
     * Const that define page_size
     */
    const PAGE_SIZE = 100;
    /**
     *
     * @var \Narvar\Connect\Helper\Config\Clean
     */
    private $cleanConfigHelper;

    /**
     *
     * @var \Narvar\Connect\Helper\Cron\Log
     */
    private $cronLogHelper;
    
    /**
     *
     * @var \Narvar\Connect\Helper\Config\Activation
     */
    private $activationHelper;

    /**
     *
     * @var \Narvar\Connect\Model\ResourceModel\Audit\Log\CollectionFactory
     */
    private $auditLogCollectionFactory;

    /**
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DeleteFilesHelper
     */
    private $deleteFilesHelper;
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * Constructor
     *
     * @param CleanHelper $cleanConfigHelper
     * @param CronLogHelper $cronLogHelper
     * @param Activation $activationHelper
     * @param AuditLogCollectionFactory $auditLogCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param DeleteFilesHelper $deleteFilesHelper
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        CleanHelper $cleanConfigHelper,
        CronLogHelper $cronLogHelper,
        Activation $activationHelper,
        AuditLogCollectionFactory $auditLogCollectionFactory,
        StoreManagerInterface $storeManager,
        DeleteFilesHelper $deleteFilesHelper,
        TimezoneInterface $timezone
    ) {
        $this->cleanConfigHelper = $cleanConfigHelper;
        $this->cronLogHelper = $cronLogHelper;
        $this->activationHelper = $activationHelper;
        $this->auditLogCollectionFactory = $auditLogCollectionFactory;
        $this->storeManager = $storeManager;
        $this->deleteFilesHelper = $deleteFilesHelper;
        $this->timezone = $timezone;
    }

    /**
     * Method to clean the Audit Log Entries based on per store configured days interval
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function process()
    {
        $storeIdArray = $this->storeManager->getStores();

        foreach ($storeIdArray as $store){
            $storeId = $store->getId();
            if (! $this->activationHelper->getIsActivatedById($storeId)) {
                continue;
            }

            if ($this->canProcess($storeId)) {
                $auditCleanInterval = $this->cleanConfigHelper->getAuditCleanIntervalByStore($storeId);
                $auditLogs = $this->auditLogCollectionFactory->create()
                    ->addStoreFilter($storeId)
                    ->addAuditCleanFilter($auditCleanInterval);

                if(!$auditLogs->getSize()){
                    continue;
                }

                $pageNumber = 1;
                do {
                    $paginatedCollection = clone $auditLogs;
                    $paginatedCollection->clear();

                    $paginatedCollection->setPageSize(self::PAGE_SIZE)->setCurPage($pageNumber);

                    if(!empty($paginatedCollection->getAllIds())) {
                        $where = sprintf('%s IN (%s)', AuditLog::LOG_ID, implode(',', $paginatedCollection->getAllIds()));
                        $auditLogs->deleteRecords($where);
                    }
                    $pageNumber++;
                } while ($pageNumber <= $paginatedCollection->getLastPageNumber());

                $this->deleteFilesHelper->process($auditCleanInterval, $storeId);
                $this->cronLogHelper->updateAuditClean($storeId);
            }
        }
    }

    /**
     * Method to check can process the clean up
     *
     * @param int|null $storeId
     * @return boolean
     */
    private function canProcess($storeId = null)
    {
        $todayDateTime = $this->timezone->date();

        if($this->cleanConfigHelper->getAuditCleanIntervalByStore($storeId) == '0' ){
            return false;
        }

        if ($this->cleanConfigHelper->getCleanUpTimeByStore($storeId) != $todayDateTime->format('H')) {
            return false;
        }

        return true;
    }
}
