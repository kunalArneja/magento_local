<?php
/**
 * Returns Management Service Model
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Service;

use Narvar\ConnectEE\Api\ReturnsManagementInterface;
use Narvar\ConnectEE\Model\Service\Response as NarvarResponse;
use Narvar\ConnectEE\Model\Returns\Community;
use Narvar\ConnectEE\Model\Returns\Enterprise;
use Narvar\ConnectEE\Helper\Config\Locale;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\Webapi\Exception as WebApiException;
use Magento\Sales\Model\OrderFactory;
use Magento\Rma\Helper\Data as RmaHelper;
use Magento\Framework\Webapi\Rest\Response;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ReturnsManagement implements ReturnsManagementInterface
{
    /**
     *  Resource Not Found Message
     */
    const RESOURCE_NOT_FOUND = 'Resource Not Found';

    /**
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     *
     * @var \Narvar\ConnectEE\Model\Service\Response
     */
    private $narvarApiResponse;

    /**
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    private $itemCollectionFactory;
    
    /**
     *
     * @var \Narvar\ConnectEE\Model\Returns\Community
     */
    private $returnCommunity;
    
    /**
     *
     * @var \Narvar\ConnectEE\Model\Returns\Enterprise
     */
    private $returnEnterprise;

    /**
     *
     * @var \Magento\Rma\Helper\Data
     */
    private $rmaHelper;
        
    /**
     *
     * @var \Magento\Framework\Webapi\Rest\Response
     */
    private $restResponse;

    /**
     * @var Resolver
     */
    private $localResolver;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Locale
     */
    private $locale;

    /**
     * Constructor
     *
     * @param OrderFactory $orderFactory
     * @param NarvarResponse $narvarApiResponse
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param Community $returnCommunity
     * @param Enterprise $returnEnterprise
     * @param RmaHelper $rmaHelper
     * @param Response $restResponse
     * @param Resolver $localResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param Locale $locale
     */
    public function __construct(
        OrderFactory $orderFactory,
        NarvarResponse $narvarApiResponse,
        ItemCollectionFactory $itemCollectionFactory,
        Community $returnCommunity,
        Enterprise $returnEnterprise,
        RmaHelper $rmaHelper,
        Response $restResponse,
        Resolver $localResolver,
        ScopeConfigInterface $scopeConfig,
        Locale $locale
    ) {
        $this->orderFactory = $orderFactory;
        $this->narvarApiResponse = $narvarApiResponse;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->returnCommunity = $returnCommunity;
        $this->rmaHelper = $rmaHelper;
        $this->returnEnterprise = $returnEnterprise;
        $this->restResponse = $restResponse;
        $this->localResolver = $localResolver;
        $this->scopeConfig = $scopeConfig;
        $this->locale = $locale;
    }

    /**
     *
     * @see \Narvar\ConnectEE\Api\ReturnsManagementInterface::createReturn()
     */
    public function createReturn($orderNumber, $dateRequested, $orderItems)
    {
        $this->processReturn($orderNumber, $dateRequested, $orderItems);
        $this->restResponse->setHttpResponseCode($this->narvarApiResponse->getStatusCode());
        
        return $this->narvarApiResponse->getResponse();
    }

    /**
     * Method to process the return based on magento edition
     *
     * @param string $orderNumber
     * @param string $dateRequested
     * @param \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface[] $orderItems
     * @return mixed
     */
    private function processReturn($orderNumber, $dateRequested, $orderItems)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($orderNumber);

        $this->locale->setStoreId($order->getStoreId());
        $locale = $this->locale->getLocale();
        $this->localResolver->setLocale($locale);

        if ($order->getId()) {
            $orderItemsData = $this->getOrderItemDetails($order->getId(), $orderItems);

            if (count($orderItemsData) != count($orderItems)) {
                return;
            }
            
            if ($this->rmaHelper->isEnabled()) {
                $this->returnEnterprise->process($order, $orderItemsData, $this->narvarApiResponse);
                
                return;
            }
            
            $this->returnCommunity->process($order, $orderItemsData, $this->narvarApiResponse, $dateRequested);
            
            return;
        }
        
        $this->narvarApiResponse->addNarvarErrorMessage(
            __('Order %1 %2', $orderNumber, self::RESOURCE_NOT_FOUND),
            WebApiException::HTTP_NOT_FOUND
        );
    }

    /**
     * Method to verify the order items are exist and it belongs to specific order
     *
     * @param int $orderId
     * @param \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface[] $orderItems
     * @return array
     */
    private function getOrderItemDetails($orderId, $orderItems)
    {
        $returnOrderItems = [];
        foreach ($orderItems as $key => $orderItem) {
            $orderItemData = $this->getOrderItem($orderId, $orderItem->getItemSku());
            
            if ($orderItemData->getId()) {
                $returnOrderItems[$key]['name'] = $orderItemData->getProduct()->getName();
                $returnOrderItems[$key]['order_item_id'] = $orderItemData->getId();
                if ($orderItemData->getParentItemId()) {
                    $returnOrderItems[$key]['order_item_id'] = $orderItemData->getParentItemId();
                }
                $returnOrderItems[$key]['item_sku'] = $orderItem->getItemSku();
                $returnOrderItems[$key]['qty'] = $orderItem->getQty();
                $returnOrderItems[$key]['condition'] = $orderItem->getCondition();
                $returnOrderItems[$key]['resolution'] = $orderItem->getResolution();
                $returnOrderItems[$key]['reason'] = $orderItem->getReason();
                $returnOrderItems[$key]['comment'] = $orderItem->getComment();
            } else {
                $this->narvarApiResponse->addNarvarErrorMessage(
                    __('Order Item %1 %2', $orderItem->getItemSku(), self::RESOURCE_NOT_FOUND),
                    WebApiException::HTTP_NOT_FOUND
                );
            }
        }
        
        return $returnOrderItems;
    }

    /**
     * Method to get the Order Item Information
     *
     * @param int $orderId
     * @param string $sku
     */
    private function getOrderItem($orderId, $sku)
    {
        return $this->itemCollectionFactory->create()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('sku', $sku)
            ->addFieldToFilter('product_type', ProductType::TYPE_SIMPLE)
            ->setPageSize(1)
            ->getFirstItem();
    }
}
