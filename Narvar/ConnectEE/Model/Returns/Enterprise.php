<?php
/**
 * Return Enterprise Model
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Returns;

use Narvar\ConnectEE\Helper\Config\Returns as ReturnsHelper;
use Magento\Rma\Helper\Data as RmaHelper;
use Magento\Rma\Model\Rma as RmaModel;
use Magento\Rma\Model\RmaFactory as RmaModelFactory;
use Magento\Rma\Model\Item as RmaItem;
use Magento\Rma\Model\Rma\Status\HistoryFactory as StatusHistory;
use Narvar\ConnectEE\Model\Eav\Attributes\OptionsFactory;
use Magento\Framework\Webapi\Exception as WebApiException;
use Magento\Framework\Stdlib\DateTime as StdDateTime;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Enterprise implements ProcessInterface
{
    /**
     *
     * @var \Narvar\ConnectEE\Helper\Config\Returns
     */
    private $returnConfigHelper;
    
    /**
     *
     * @var \Narvar\ConnectEE\Model\Eav\Attributes\OptionsFactory
     */
    private $eavAttributeOptionFactory;
    
    /**
     *
     * @var \Magento\Rma\Model\Rma\Status\HistoryFactory
     */
    private $statusHistory;
    
    /**
     *
     * @var \Magento\Rma\Helper\Data
     */
    private $rmaHelper;
    
    /**
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime;
     */
    private $dateTime;
    
    /**
     *
     * @var \Magento\Rma\Model\RmaFactory
     */
    private $rmaModelFactory;
    
    /**
     * Constructor
     *
     * @param DateTime $dateTime
     * @param ReturnsHelper $returnConfigHelper
     * @param OptionsFactory $eavAttributeOptionFactory
     * @param StatusHistory $statusHistory
     * @param RmaHelper $rmaHelper
     * @param RmaModelFactory $rmaModelFactory
     */
    public function __construct(
        DateTime $dateTime,
        ReturnsHelper $returnConfigHelper,
        OptionsFactory $eavAttributeOptionFactory,
        StatusHistory $statusHistory,
        RmaHelper $rmaHelper,
        RmaModelFactory $rmaModelFactory
    ) {
        $this->dateTime = $dateTime;
        $this->statusHistory = $statusHistory;
        $this->returnConfigHelper = $returnConfigHelper;
        $this->eavAttributeOptionFactory = $eavAttributeOptionFactory;
        $this->rmaHelper = $rmaHelper;
        $this->rmaModelFactory = $rmaModelFactory;
    }
    
    /**
     * @see \Narvar\ConnectEE\Model\Returns\ProcessInterface::process()
     */
    public function process(
        \Magento\Sales\Model\Order $order,
        $orderItems,
        \Narvar\ConnectEE\Model\Service\Response $narvarApiResponse,
        $dateRequested = null
    ) {
        if ($this->returnConfigHelper->canCreateReturnEE() && $this->rmaHelper->canCreateRma($order)) {
            $this->processReturn($order, $orderItems, $narvarApiResponse);
            return;
        }
        
        $narvarApiResponse->addNarvarErrorMessage(
            __('Access denied to process return for order %1', $order->getIncrementId()),
            WebApiException::HTTP_UNAUTHORIZED
        );
    }
    
    /**
     * Method to process the return
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $orderItems
     * @param \Narvar\ConnectEE\Model\Service\Response $narvarApiResponse
     */
    private function processReturn(
        \Magento\Sales\Model\Order $order,
        $orderItems,
        \Narvar\ConnectEE\Model\Service\Response $narvarApiResponse
    ) {
        try {
            $rmaData = $this->prepareRmaData($order);
            $postData = $this->preparePostData($orderItems);            
            $rmaModel = $this->rmaModelFactory->create();
            $result = $rmaModel->setData($rmaData)->saveRma($postData);
    
            if (! $result) {
                $narvarApiResponse->addNarvarErrorMessage(
                    __('Unable to process return for order %1', $order->getIncrementId()),
                    WebApiException::HTTP_BAD_REQUEST
                );
                return;
            }
            
            $this->addComments($orderItems, $rmaModel);
            $message = __('Return request created for order %1', $order->getIncrementId());
            $narvarApiResponse->clearMessages();
            $narvarApiResponse->addNarvarSuccessMessage($message, 201);
        } catch (Exception $e) {
            $narvarApiResponse->addNarvarErrorMessage(
                __(
                    'Unable to process sreturn for order %1 - %2',
                    $order->getIncrementId(),
                    $e->getMessage()
                ),
                WebApiException::HTTP_BAD_REQUEST
            );
        }
    }
    
    /**
     * Prepare the Rma Form Data
     *
     * @param \Magento\Sales\Model\Order $order
     * @return multitype:string mixed number NULL Ambigous <string, string, multitype:>
     */
    private function prepareRmaData(\Magento\Sales\Model\Order $order)
    {
        return [
            'status' => \Magento\Rma\Model\Rma\Source\Status::STATE_PENDING,
            'date_requested' => $this->dateTime->date(),
            'order_id' => $order->getId(),
            'order_increment_id' => $order->getIncrementId(),
            'store_id' => $order->getStoreId(),
            'customer_id' => $order->getCustomerId(),
            'order_date' => $order->getCreatedAt(),
            'customer_name' => $order->getCustomerName(),
            'customer_custom_email' => '',
            'not_push_to_narvar' => 1
        ];
    }
    
    /**
     * Prepare the Rma Post Data in required Magento RMA post data format
     *
     * @param unknown $orderItems
     * @return multitype:string multitype:
     */
    private function preparePostData($orderItems)
    {
        $postData = [];
        $postData['customer_custom_email'] = '';
        $rmaItems = [];
        
        foreach ($orderItems as $key => $orderItem) {
            $reasonOldValue = $orderItem[ReturnsHelper::REASON];
            $reasonNewValue = $this->getReason($reasonOldValue);
            $rmaItem = [];
            $rmaItem['order_item_id'] = $orderItem['order_item_id'];
            $rmaItem['qty_requested'] = $orderItem['qty'];
            $rmaItem[ReturnsHelper::CONDITION] = $this->getCondition($orderItem[ReturnsHelper::CONDITION]);
            $rmaItem[ReturnsHelper::RESOLUTION] = $this->getResolution($orderItem[ReturnsHelper::RESOLUTION]);
            $rmaItem[ReturnsHelper::REASON] = $reasonNewValue;
            if ($reasonNewValue == ReturnsHelper::REASON_OTHER_VALUE) {
                $rmaItem[ReturnsHelper::REASON_OTHER] = $reasonOldValue;
            }
    
            array_push($rmaItems, $rmaItem);
        }
    
        $postData['items'] = $rmaItems;
        $postData['rma_comment'] = '';
    
        return $postData;
    }
    
    /**
     * Method to get the return reason value
     * If given reason value is available in attributes, then return the id
     * Otherwise method will return default reason value value from configuration
     *
     * @param string $value
     * @return string|unknown
     */
    private function getReason($value)
    {
        $reason = $this->getAttributeValue(ReturnsHelper::REASON, $value);
        if ($reason === false) {
            return $this->returnConfigHelper->getReason();
        }
    
        return $reason;
    }
    
    /**
     * Method to get the return resolution value
     * If given resolution value is available in attributes, then return the id
     * Otherwise method will return default resolution value from configuration
     *
     * @param string $value
     * @return string|unknown
     */
    private function getResolution($value)
    {
        $resolution = $this->getAttributeValue(ReturnsHelper::RESOLUTION, $value);
        if ($resolution === false) {
            return $this->returnConfigHelper->getResolution();
        }
    
        return $resolution;
    }
    
    /**
     * Method to get the return condition value
     * If given condition value is available in attributes, then return the id
     * Otherwise method will return default condition value from configuration
     *
     * @param string $value
     * @return string|unknown
     */
    private function getCondition($value)
    {
        $condition = $this->getAttributeValue(ReturnsHelper::CONDITION, $value);
        if ($condition === false) {
            return $this->returnConfigHelper->getCondition();
        }
    
        return $condition;
    }
    
    /**
     * Method to get the attribute value from Magento attribute Options
     *
     * @param string $attributeCode
     * @param int|boolean $value
     */
    private function getAttributeValue($attributeCode, $value)
    {
        if (! empty($value)) {
            return $this->eavAttributeOptionFactory->create()
                ->getAttributeValue(
                    RmaItem::ENTITY,
                    $attributeCode,
                    $value
                );
        }
    
        return false;
    }
    
    /**
     * Method to set the comments for RMA provided by end user
     *
     * @param array $orderItems
     * @param RmaModel $rmaModel
     */
    private function addComments($orderItems, RmaModel $rmaModel)
    {
        foreach ($orderItems as $orderItem) {
            if (isset($orderItem['comment']) && ! empty($orderItem['comment'])) {
                $this->addComment($rmaModel, $orderItem);
            }
        }
    }
    
    /**
     * Method to add comment
     *
     * @param RmaModel $rmaModel
     * @param multitype $orderItem
     */
    private function addComment(RmaModel $rmaModel, $orderItem)
    {
        $this->statusHistory->create()
            ->setRmaEntityId($rmaModel->getId())
            ->setComment($orderItem['comment'])
            ->setIsVisibleOnFront(true)
            ->setStatus($rmaModel->getStatus())
            ->setCreatedAt($this->dateTime->date())
            ->save();
    }
}
