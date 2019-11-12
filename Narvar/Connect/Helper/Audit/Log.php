<?php
/**
 * Audit Log Helper
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Below methods will used to update audit log status
 *
 * @method string updatePending($logModel, $responseData = null)
 * @method string updateProcessing($logModel, $responseData = null)
 * @method string updateOnHold($logModel, $responseData = null)
 * @method string updateFailure($logModel, $responseData = null)
 * @method string updateSuccess($logModel, $responseData = null)
 * @method string updateBulk($logModel, $responseData = null)
 */
namespace Narvar\Connect\Helper\Audit;

use Magento\Framework\App\Helper\Context;
use Narvar\Connect\Model\Audit\Log as AuditLog;
use Narvar\Connect\Model\Audit\LogFactory as AuditLogFactory;
use Narvar\Connect\Model\ResourceModel\Audit\Log\CollectionFactory as AuditLogCollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Narvar\Connect\Helper\Audit\Status;
use Magento\Framework\Exception\LocalizedException;
use Narvar\Connect\Helper\Audit\Type;

class Log extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     *
     * @var \Narvar\Connect\Model\Audit\LogFactory
     */
    private $auditLogFactory;
    
    /**
     *
     * @var \Narvar\Connect\Model\ResourceModel\Audit\Log\CollectionFactory
     */
    private $auditLogCollectionFactory;

    /**
     *
     * @var \Narvar\Connect\Helper\Audit\Status
     */
    private $auditStatus;

    /**
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime;
     */
    private $dateTime;

    /**
     * Constructor
     *
     * @param Context $context
     * @param AuditLogFactory $auditLogFactory
     * @param DateTime $dateTime
     * @param Status $auditStatus
     * @param AuditLogCollectionFactory $auditLogCollectionFactory
     */
    public function __construct(
        Context $context,
        AuditLogFactory $auditLogFactory,
        DateTime $dateTime,
        Status $auditStatus,
        AuditLogCollectionFactory $auditLogCollectionFactory
    ) {
        $this->auditLogFactory = $auditLogFactory;
        $this->dateTime = $dateTime;
        $this->auditStatus = $auditStatus;
        $this->auditLogCollectionFactory = $auditLogCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Method to create a record in log table
     *
     * @param array $logData
     * @return \Narvar\Connect\Model\Audit\LogFactory
     */
    public function create($logData)
    {
        $logModel = $this->auditLogFactory->create();
        $logModel->setId(null)
            ->setOrderId($logData[AuditLog::ORDER_ID])
            ->setOrderIncId($logData[AuditLog::ORDER_INC_ID])
            ->setAction($logData[AuditLog::ACTION])
            ->setEntityType($logData[AuditLog::ENT_TYPE])
            ->setRequestData($logData[AuditLog::REQ_DATA])
            ->setResponse($logData[AuditLog::RESPONSE])
            ->setSlug($logData[AuditLog::SLUG])
            ->setStatus($logData[AuditLog::STATUS])
            ->setRequestTime($this->dateTime->date())
            ->setFinishTime($this->dateTime->date())
            ->save();
        
        return $logModel;
    }

    /**
     * Method to update the Audit Log
     *
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        if (substr($method, 0, 6) == 'update') {
            $statusClass = new \ReflectionClass('Narvar\Connect\Helper\Audit\Status');
            $constants = $statusClass->getConstants();
            $constantVar = strtoupper(preg_replace('/\B([A-Z])/', '_$1', substr($method, 6)));
            if (array_key_exists($constantVar, $constants)) {
                $statusId = $this->auditStatus->getStatusId($constants[$constantVar]);
                $responseData = null;
                if (isset($args[1])) {
                    $responseData = $args[1];
                }
                
                return $this->update($args[0], $statusId, $responseData);
            }
        }
        
        throw new LocalizedException(__('Method Not Found %1', $method));
    }

    /**
     * Method to update the log status and response data from API
     *
     * @param int $logId
     * @param int $statusId
     * @param string $responseData
     * @return \Narvar\Connect\Model\Audit\LogFactory
     */
    public function update($logModel, $statusId, $responseData = null)
    {
        if ($responseData !== null) {
            $logModel->setResponse($responseData);
        }
        $logModel->setStatus($statusId)->setFinishTime($this->dateTime->date())->save();
        
        return $logModel;
    }

    /**
     * Method to get the Existing Non Successful Pushed Records from Log table
     * for specific Order Id with order or rma entity type
     *
     * @param int $orderId
     * @param string $entityType
     * @return boolean
     */
    public function hasFailures($orderId, $entityType)
    {
        $auditLogs = $this->auditLogCollectionFactory->create();
        $auditLogs->addOrderIdFilter($orderId)->addNonProcessedFilter()->addEntityFilter($entityType);
    
        return $auditLogs->getSize() > 0;
    }

    /**
     * Method to get the Last API call request Data of specific order Id
     *
     * @param int $orderId
     * @param string $entityType
     * @return multitype
     */
    public function lastCallRequestData($orderId, $entityType)
    {
        return $this->auditLogCollectionFactory->create()->addOrderIdFilter($orderId)
            ->addEntityFilter($entityType)
            ->getLastItem()
            ->getRequestData();
    }
}
