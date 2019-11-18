<?php
/**
 * Narvar File Uploader Model
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model;

use Magento\Store\Model\StoreManagerInterface;
use Narvar\ConnectEE\Model\Audit\Log as AuditLog;
use Narvar\ConnectEE\Exception\ConnectorException;
use Magento\Framework\Exception\LocalizedException;
use Narvar\ConnectEE\Helper\CurlFileUploaderFactory;
use Narvar\ConnectEE\Model\ResourceModel\Audit\Log\CollectionFactory as AuditLogCollectionFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Filesystem\Io\File as FileHandler;
use Magento\Framework\App\Filesystem\DirectoryList;
use Narvar\ConnectEE\Helper\Audit\Status as AuditStatusHelper;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Narvar\ConnectEE\Helper\Cron\HistoricalOrders as HistoricalOrdersHelper;

class Uploader extends \Magento\Framework\DataObject
{

    /**
     * Slug value for file upload api
     */
    const SLUG = 'orders/upload/';

    /**
     * Constant store_id
     */
    const STORE_ID = 'store_id';

    /**
     * Const that define page_size
     */
    const PAGE_SIZE = 1000;

    /**
     *
     * @var \Narvar\ConnectEE\Model\ResourceModel\Audit\Log\CollectionFactory
     */
    private $auditLogsCollectionFactory;

    /**
     *
     * @var \Narvar\ConnectEE\Model\ResourceModel\Audit\Log\Collection
     */
    private $logs;

    /**
     * int File size restriction size - 704857 Bytes (0.7Mb)
     */
    const FILE_SIZE_RESTRICTION = 704857;

    /**
     *
     * @var File Upload Temporary file Path
     */
    private $filePath;

    /**
     *
     * @var \Narvar\ConnectEE\Helper\Audit\Status
     */
    private $auditStatusHelper;

    /**
     *
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $fileHandler;

    /**
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     *
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * Where condition for update process
     */
    private $whereCondition = null;

    /**
     * Where condition for update process per File
     */
    protected $whereConditionPerFile = [];
    
    /**
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime;
     */
    private $dateTime;

    /**
     * @var HistoricalOrdersHelper
     */
    private $historicalOrdersHelper;

    /**
     *
     * @var int|null
     */
    private $storeId;

    /**
     *
     * @var \Narvar\ConnectEE\Helper\CurlFileUploader
     */
    private $curlFileUploader;
    /**
     * @var StoreManagerInterface
     */
    private $storeRepository;

    /**
     * Constructor
     *
     * @param AuditStatusHelper $auditStatusHelper
     * @param AuditLogCollectionFactory $auditLogCollectionFactory
     * @param JsonHelper $jsonHelper
     * @param FileHandler $fileHandler
     * @param DirectoryList $directoryList
     * @param DateTimeFactory $dateTimeFactory
     * @param HistoricalOrdersHelper $historicalOrdersHelper
     * @param CurlFileUploaderFactory $curlFileUploader
     * @param StoreManagerInterface $storeRepository
     * @param array $data
     */
    public function __construct(
        AuditStatusHelper $auditStatusHelper,
        AuditLogCollectionFactory $auditLogCollectionFactory,
        JsonHelper $jsonHelper,
        FileHandler $fileHandler,
        DirectoryList $directoryList,
        DateTimeFactory $dateTimeFactory,
        HistoricalOrdersHelper $historicalOrdersHelper,
        CurlFileUploaderFactory $curlFileUploader,
        StoreManagerInterface $storeRepository,
        array $data = []
    ) {
        $this->auditLogsCollectionFactory = $auditLogCollectionFactory;
        $this->dateTime = $dateTimeFactory->create();
        $this->auditStatusHelper = $auditStatusHelper;
        $this->directoryList = $directoryList;
        $this->fileHandler = $fileHandler;
        $this->setFilePath();
        $this->jsonHelper = $jsonHelper;
        $this->historicalOrdersHelper = $historicalOrdersHelper;
        $this->storeId = (isset($data['store_id'])) ? $data['store_id'] : null;
        $this->setLogs($data);
        $this->curlFileUploader = $curlFileUploader;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Method to set the File Folder Path
     */
    private function setFilePath()
    {
        $this->filePath= $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . 'narvar' . DIRECTORY_SEPARATOR .
             'import' . DIRECTORY_SEPARATOR . $this->dateTime->date('Y') . DIRECTORY_SEPARATOR .
             $this->dateTime->date('m') . DIRECTORY_SEPARATOR . $this->dateTime->date('d') .DIRECTORY_SEPARATOR;
    }

    /**
     * Method to set the logs collection
     * @param array $data
     */
    private function setLogs($data)
    {
        /**
         * @var \Narvar\ConnectEE\Model\ResourceModel\Audit\Log\Collection $logs
         */
        $logs = $this->auditLogsCollectionFactory->create()->addFailureFilter();

        if (isset($data['from_time']) && isset($data['to_time'])) {
            $logs->addBulkDateFilter($data['from_time'], $data['to_time']);
        }

        $logs->addStoreFilter($this->storeId);

        $this->logs = $logs;
        $this->whereCondition = sprintf('%s IN (%s)', AuditLog::LOG_ID, implode(',', $this->logs->getAllIds()));
    }

    /**
     * Method to generate the Order information upload file
     *
     * @return array
     * @throws LocalizedException
     */
    private function generateFile()
    {
        $baseFileName = 'narvar_order_data';
        $baseFileName .= '_' . $this->dateTime->date('His');
        if( $this->storeId ){
            $baseFileName = $baseFileName . '_' . $this->storeId . '_store';
        }
        $fileNames[] = $baseFileName;

        $this->fileHandler->setAllowCreateFolders(true);
        $this->fileHandler->open([
            'path' => $this->filePath
        ]);

        $fileSize = 0;
        $content = '';
        $logIds[end($fileNames)] = [];

        foreach ($this->logs as $log) {
            $data = $log->getRequestData();
            $fileSize += (int) strlen($data);
            $content .= "$data\n";
            $logIds[end($fileNames)][] = $log->getId();

            /**
             * If file size more then self::FILE_SIZE_RESTRICTION, new file will be created.
             */
            if( $fileSize >= self::FILE_SIZE_RESTRICTION ){
                $this->fileHandler->write(end($fileNames), $content, 'w+');
                $content = "";
                $fileSize = 0;
                $fileNumber = count($fileNames);
                $fileNames[] = $fileNames[0] . '_' . $fileNumber;
            }
        }
        if(! empty($content)) {
            $this->fileHandler->write(end($fileNames), $content, 'w+');
        }
        $this->fileHandler->close();

        foreach ($fileNames as $file) {
            $this->whereConditionPerFile[$file] = sprintf(
                '%s IN (%s)',
                AuditLog::LOG_ID,
                implode(',', $logIds[$file])
            );
        }

        $this->logs->updateRecords(
            [
                AuditLog::STATUS => $this->auditStatusHelper->getOnHold(),
                AuditLog::FINISH_TIME => $this->dateTime->date()
            ],
            $this->whereCondition
        );

        if (! count($fileNames) ) {
            throw new LocalizedException(__('Unable to generate file'));
        }

        return $fileNames;
    }

    /**
     * @param $storeId
     * @param $page
     * @return
     */
    public function getLogsByPage($storeId, $page){

        /**
         * @var \Narvar\ConnectEE\Model\ResourceModel\Audit\Log\Collection $collection
         */
        $collection = $this->auditLogsCollectionFactory->create();
        $collection->addFailureFilter();
        $collection->addStoreFilter($storeId);
        if( $page ){
            $collection->setPageSize(self::PAGE_SIZE);
            $lastPageNumber = $collection->getLastPageNumber();

            if( $page > $lastPageNumber ){
                return false;
            }

            $collection->setCurPage($page);
        }
        $orders = $collection->load();

        if(! $orders->getSize() ){
            return false;
        }

        return $orders;
    }

    /**
     * Method to generate the Order information upload file per store
     *
     * @return array
     * @throws LocalizedException
     */
    private function generateFileByStore()
    {
        $storeIdArray  = array();
        $allStores = $this->storeRepository->getStores();
        foreach ($allStores as $_eachStoreId => $val)
        {
            array_push($storeIdArray, $_eachStoreId);
        }

        $baseFileName = 'narvar_order_data';
        $baseFileName .= '_' . $this->dateTime->date('His');

        $this->fileHandler->setAllowCreateFolders(true);
        $this->fileHandler->open([
            'path' => $this->filePath
        ]);

        foreach ($storeIdArray as $storeId) {
            $fileNames[$storeId][] = $baseFileName . '_' . $storeId . '_store';
            $fileSize = 0;
            $content = '';
            $logIds[end($fileNames[$storeId])] = [];
            $page = 1;

            while ($logs = $this->getLogsByPage($storeId, $page++)) {
                foreach ($logs as $log) {
                    if ($storeId == $log->getStoreId()) {
                        $data = $log->getRequestData();
                        $fileSize += (int)strlen($data);
                        $content .= "$data\n";
                        $logIds[end($fileNames[$storeId])][] = $log->getId();

                        /**
                         * If file size more then self::FILE_SIZE_RESTRICTION, new file will be created.
                         */
                        if ($fileSize >= self::FILE_SIZE_RESTRICTION) {
                            $this->fileHandler->write(end($fileNames[$storeId]), $content, 'w+');
                            $content = "";
                            $fileSize = 0;
                            $fileNumber = count($fileNames[$storeId]);
                            $fileNames[$storeId][] = $fileNames[$storeId][0] . '_' . $fileNumber;
                        }

                        if (!empty($content)) {
                            $this->fileHandler->write(end($fileNames[$storeId]), $content, 'w+');
                        }
                    }
                }
            }
            if (! file_exists("$this->filePath" . end($fileNames[$storeId]))) {
                array_pop($fileNames[$storeId]);
            }
        }

        $this->fileHandler->close();

        foreach ($fileNames as $filePerStore) {
            foreach ($filePerStore as $file) {
                $this->whereConditionPerFile[$file] = sprintf(
                    '%s IN (%s)',
                    AuditLog::LOG_ID,
                    implode(',', $logIds[$file])
                );
            }
        }

        $this->logs->updateRecords(
            [
                AuditLog::STATUS => $this->auditStatusHelper->getOnHold(),
                AuditLog::FINISH_TIME => $this->dateTime->date()
            ],
            $this->whereCondition
        );

        if (! count($fileNames) ) {
            throw new LocalizedException(__('Unable to generate file'));
        }

        return $fileNames;
    }

    /**
     * Method to process the file generation and file upload to narvar
     * @param array $data
     * @return bool
     * @throws LocalizedException
     */
    public function process()
    {
        if (! $this->getFailedRecordsCount()) {
            throw new LocalizedException(__('Failure Records Not Found'));
        }
        $filesArray = $this->generateFile();

        foreach ($filesArray as $file){

            try {
                $storeId = ['storeId' => $this->storeId];
                $responseMsg = $this->uploadInitiate($storeId, $file);

                $this->updateRecords(
                    $this->auditStatusHelper->getSuccess(),
                    $responseMsg,
                    $this->whereConditionPerFile[$file]
                );

            } catch (ConnectorException $e) {
                $this->updateRecords(
                    $this->auditStatusHelper->getFailure(),
                    $e->getMessage(),
                    $this->whereConditionPerFile[$file]
                );

                $failure = $e->getMessage();
            }
        }

        if (isset($failure)) {
            throw new LocalizedException(__('%1',$failure));
        }

        return true;
    }

    /**
     * Method to process the file generation and file upload to narvar by store
     * @param array $data
     * @return bool
     * @throws LocalizedException
     */
    public function processByStore()
    {
        if (! $this->getFailedRecordsCount()) {
            throw new LocalizedException(__('Failure Records Not Found'));
        }
        $filesArray = $this->generateFileByStore();

        foreach ($filesArray as $storeId => $files){
            $storeId = ['storeId' => $storeId];

            foreach ($files as $file){
                try {
                    $responseMsg = $this->uploadInitiate($storeId, $file);

                    $this->updateRecords(
                        $this->auditStatusHelper->getSuccess(),
                        $responseMsg,
                        $this->whereConditionPerFile[$file]
                    );

                } catch (ConnectorException $e) {
                    $this->updateRecords(
                        $this->auditStatusHelper->getFailure(),
                        $e->getMessage(),
                        $this->whereConditionPerFile[$file]
                    );

                    $failure = $e->getMessage();
                }
            }
        }

        if (isset($failure)) {
            throw new LocalizedException(__('%1',$failure));
        }

        return true;
    }

    /**
     * Method to initiate upload process
     *
     * @param array $storeId
     * @param string $fileName
     * @return string
     */
    private function uploadInitiate(array $storeId, $fileName)
    {
        $uploadFile = [
            "file" => "$this->filePath" . $fileName,
        ];

        $responseMsg = $this->curlFileUploader->create(['data' => $storeId])->upload(
            self::SLUG,
            $uploadFile
        );

        return $responseMsg;
    }

    /**
     * Method to update log data per file sent
     *
     * @param $status
     * @param $message
     * @param $whereCondition
     */
    private function updateRecords($status, $message, $whereCondition)
    {
        $this->logs->updateRecords(
            [
                AuditLog::STATUS => $status,
                AuditLog::RESPONSE => $message,
                AuditLog::FINISH_TIME => $this->dateTime->date()
            ],
            $whereCondition
        );
    }

    public function getFailedRecordsCount(){
        /**
         * @var \Narvar\ConnectEE\Model\ResourceModel\Audit\Log\Collection $collection
         */
        $collection = $this->auditLogsCollectionFactory->create()->addFailureFilter();
        $collection->getSelect()->columns('COUNT(*) as record_count');
        $result = $collection->getFirstItem()->getData('record_count') > 0 ? $collection->getFirstItem()->getData('record_count') : false ;

        return $result;
    }
}
