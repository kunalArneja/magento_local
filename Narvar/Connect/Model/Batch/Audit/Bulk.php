<?php
/**
 * Batch Bulk Process Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\Batch\Audit;

use Narvar\Connect\Helper\Config\Batch as BatchHelper;
use Narvar\Connect\Helper\Cron\Log as CronLogHelper;
use Narvar\Connect\Helper\Config\Activation;
use Narvar\Connect\Model\UploaderFactory;
use Magento\Framework\Stdlib\DateTime as StdDateTime;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Narvar\Connect\Helper\Cron\HistoricalOrders as HistoricalOrdersHelper;
use Narvar\Connect\Model\Cron\HistoricalOrders;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Narvar\Connect\Helper\Config\Debug;
use Magento\Store\Model\ScopeInterface;

class Bulk
{

    /**
     * @var \Narvar\Connect\Helper\Config\Batch
     */
    private $configBatchHelper;

    /**
     * @var \Narvar\Connect\Helper\Cron\Log
     */
    private $cronLogHelper;

    /**
     * @var \Narvar\Connect\Helper\Config\Activation
     */
    private $activationHelper;

    /**
     * @var \Narvar\Connect\Model\UploaderFactory
     */
    private $uploader;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var HistoricalOrdersHelper
     */
    private $historicalOrdersHelper;

    /**
     * @var HistoricalOrders
     */
    private $historicalOrders;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var Pool
     */
    private $cacheFrontendPool;
    /**
     * @var TimezoneInterface
     */
    private $timezone;
    /**
     * @var Debug
     */
    private $debug;

    /**
     * Constructor
     *
     * @param BatchHelper $batchConfigHelper
     * @param CronLogHelper $cronLogHelper
     * @param Activation $activationHelper
     * @param UploaderFactory $uploader
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param HistoricalOrdersHelper $historicalOrdersHelper
     * @param HistoricalOrders $historicalOrders
     * @param LoggerInterface $logger
     * @param ConfigInterface $configInterface
     * @param Config $config
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     * @param TimezoneInterface $timezone
     * @param Debug $debug
     */
    public function __construct(
        BatchHelper $batchConfigHelper,
        CronLogHelper $cronLogHelper,
        Activation $activationHelper,
        UploaderFactory $uploader,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        HistoricalOrdersHelper $historicalOrdersHelper,
        HistoricalOrders $historicalOrders,
        LoggerInterface $logger,
        ConfigInterface $configInterface,
        Config $config,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        TimezoneInterface $timezone,
        Debug $debug
    ) {
        $this->configBatchHelper = $batchConfigHelper;
        $this->cronLogHelper = $cronLogHelper;
        $this->activationHelper = $activationHelper;
        $this->uploader = $uploader;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->historicalOrdersHelper = $historicalOrdersHelper;
        $this->historicalOrders = $historicalOrders;
        $this->configInterface = $configInterface;
        $this->config = $config;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->timezone = $timezone;
        $this->debug = $debug;
    }

    /**
     * Method to process the failure records based on configuration
     */
    public function process()
    {
        $storeIdArray = $this->storeManager->getStores();

        foreach ($storeIdArray as $store) {
            $storeId = $store->getId();
            if (! $this->activationHelper->getIsActivatedById($storeId) ) {
                continue;
            }

            $period = $this->canProcessHistoricalData($storeId);

            if ($period || $this->canProcess($storeId) ) {

                if ($period) {
                    $page = 1;
                    while ($orders = $this->historicalOrdersHelper->getOrders($period, $storeId, $page++)) {
                        $this->historicalOrders->process($orders);
                        $orders->clear();
                    }

                    $path = sprintf(
                        '%s/%s/%s',
                        HistoricalOrdersHelper::CONFIG_SECTION,
                        HistoricalOrdersHelper::CONFIG_GRP,
                        HistoricalOrdersHelper::BATCH_INITIAL_DISABLE
                    );
                    $this->configInterface->saveConfig(
                        $path,
                        1,
                        ScopeInterface::SCOPE_STORES, $store->getId()
                    );

                    $this->cacheTypeList->cleanType('config');
                    foreach ($this->cacheFrontendPool as $cacheFrontend) {
                        $cacheFrontend->getBackend()->clean();
                    }
                }

                $data = [];
                $data['from_time'] = $this->cronLogHelper->getBulkPush($storeId);
                $data['to_time'] = $this->dateTime->date();
                $data['store_id'] = $storeId;

                if (!$period) {
                    $this->cronLogHelper->updateBulkPush($storeId);
                }

                try {
                    $this->uploader->create(['data' => $data])->process();
                } catch (LocalizedException $e) {
                    if($this->debug->getDebugMode()) {
                        $this->logger->error(__('Narvar Bulk Process - Unable to process : %1', $e->getMessage()));
                    }
                }
            }
        }
    }

    /**
     * Method to check can process the bulk upload
     *
     * @param int $storeId
     * @return boolean
     */
    private function canProcess($storeId = null)
    {
        $lastExeBulkPush = $this->cronLogHelper->getBulkPush($storeId);
        $todayDate = $this->timezone->date($this->dateTime->date(StdDateTime::DATE_PHP_FORMAT));
        $lastExecutedDate = $this->timezone->date(
            $this->dateTime->date(
                StdDateTime::DATE_PHP_FORMAT,
                $lastExeBulkPush
            )
        );
        $diffDate = $todayDate->diff($lastExecutedDate);

        $todayDateTime = $this->timezone->date();
        $lastExecutedDateTime = $this->timezone->date($lastExeBulkPush);
        $diffDateTime = $todayDateTime->diff($lastExecutedDateTime);

        if ($this->configBatchHelper->getBatchBulkPushFreqByStore($storeId) == 24
            && $todayDateTime->format('H') == $this->configBatchHelper->getBatchPushTimeByStore($storeId)) {
            return true;
        }

        if ($diffDate->d == 0 && $this->configBatchHelper->getBatchBulkPushFreqByStore($storeId) == $diffDateTime->h) {
            return true;
        }

        return false;
    }

    /**
     * @param $storeId
     * @return bool|int
     */
    private function canProcessHistoricalData($storeId)
    {
        $period = $this->historicalOrdersHelper->getHistoricalOrdersPeriod($storeId);
        if(!$this->historicalOrdersHelper->getIsDisableHistoricalOrders($storeId)){
            return $period;
        }
        return false;
    }
}