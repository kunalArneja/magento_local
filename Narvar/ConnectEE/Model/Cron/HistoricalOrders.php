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
namespace Narvar\ConnectEE\Model\Cron;

use Narvar\ConnectEE\Model\Audit\Log as AuditLog;
use Narvar\ConnectEE\Helper\Audit\Status as AuditStatusHelper;
use Narvar\ConnectEE\Model\Data\DTO;
use Narvar\ConnectEE\Model\Data\Transformer\Order as OrderTransformer;
use Narvar\ConnectEE\Model\Data\Transformer\Order\Items as OrderItemsTransformer;
use Narvar\ConnectEE\Model\Data\Transformer\Customer as CustomerTransformer;
use Narvar\ConnectEE\Model\Data\Transformer\Billing as BillingTransformer;
use Narvar\ConnectEE\Model\Data\Transformer\Address\Location as AddressLocation;
use Narvar\ConnectEE\Model\Data\Transformer\Address\Billing as BillingAddress;
use Narvar\ConnectEE\Model\Data\Transformer\Brand as BrandTransformer;
use Narvar\ConnectEE\Model\Data\Transformer\Locale as LocaleTransformer;
use Narvar\ConnectEE\Model\Data\Transformer\Shipments as ShipmentTransformer;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Narvar\ConnectEE\Helper\Cron\HistoricalOrders as HistoricalOrdersHelper;

class HistoricalOrders extends \Magento\Framework\DataObject
{
    /**
     * Slug value for file upload api
     */
    const SLUG = 'orders/upload/';

    /**
     * String initial_batch_request
     */
    const ENTITY_TYPE = 'order';

    /**
     * String create
     */
    const ACTION = 'create';

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime;
     */
    private $dateTime;

    /**
     * @var DTO
     */
    private $dto;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var OrderTransformer
     */
    private $orderTransformer;

    /**
     * @var CustomerTransformer
     */
    private $customerTransformer;

    /**
     * @var AddressLocation
     */
    private $addressLocation;

    /**
     * @var BillingTransformer
     */
    private $billing;

    /**
     * @var BillingAddress
     */
    private $billingAddress;

    /**
     * @var BrandTransformer
     */
    private $brandTransformer;

    /**
     * @var LocaleTransformer
     */
    private $localeTransformer;

    /**
     * @var OrderItemsTransformer
     */
    private $orderItemsTransformer;

    /**
     * @var AuditStatusHelper
     */
    private $auditStatusHelper;

    /**
     * @var ShipmentTransformer
     */
    private $shipmentTransformer;

    /**
     * @var Resource
     */
    private $resource;

    /**
     *  Contain true Table name
     */
    private $logDataTableName;

    /**
     * InitialUploader constructor.
     * @param JsonHelper $jsonHelper
     * @param DateTime $dateTime
     * @param DTO $dto
     * @param OrderTransformer $orderTransformer
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param CustomerTransformer $customerTransformer
     * @param AddressLocation $addressLocation
     * @param BillingTransformer $billing
     * @param BillingAddress $billingAddress
     * @param BrandTransformer $brandTransformer
     * @param LocaleTransformer $localeTransformer
     * @param OrderItemsTransformer $orderItemsTransformer
     * @param AuditStatusHelper $auditStatusHelper
     * @param ShipmentTransformer $shipmentTransformer
     * @param ResourceConnection $resource
     */
    public function __construct(
        JsonHelper $jsonHelper,
        DateTime $dateTime,
        DTO $dto,
        OrderTransformer $orderTransformer,
        OrderCollectionFactory $orderCollectionFactory,
        CustomerTransformer $customerTransformer,
        AddressLocation $addressLocation,
        BillingTransformer $billing,
        BillingAddress $billingAddress,
        BrandTransformer $brandTransformer,
        LocaleTransformer $localeTransformer,
        OrderItemsTransformer $orderItemsTransformer,
        AuditStatusHelper $auditStatusHelper,
        ShipmentTransformer $shipmentTransformer,
        ResourceConnection $resource
    ) {
        $this->dateTime = $dateTime;
        $this->jsonHelper = $jsonHelper;
        $this->dto = $dto;
        $this->orderTransformer = $orderTransformer;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerTransformer = $customerTransformer;
        $this->addressLocation = $addressLocation;
        $this->billing = $billing;
        $this->billingAddress = $billingAddress;
        $this->brandTransformer = $brandTransformer;
        $this->localeTransformer = $localeTransformer;
        $this->orderItemsTransformer = $orderItemsTransformer;
        $this->auditStatusHelper = $auditStatusHelper;
        $this->shipmentTransformer = $shipmentTransformer;
        $this->resource = $resource;
        $this->logDataTableName = $this->resource->getTableName(HistoricalOrdersHelper::NARVAR_AUDIT_LOG);
    }


    /**
     * Prepare order and shipment (if available) data in required by API format using order collection from DTO
     *
     * @return string
     */
    private function prepareOrderData()
    {
        $order = $this->orderTransformer->transform($this->dto);

        $customer = [
            'customer' => array_merge(
                $this->customerTransformer->transform($this->dto),
                $this->addressLocation->transform($this->dto)
            )
        ];
        $billing = [
            'billing' => array_merge(
                $this->billing->transform($this->dto),
                $this->billingAddress->transform($this->dto)
            )
        ];
        $brand = $this->brandTransformer->transform($this->dto);
        $locale = $this->localeTransformer->transform($this->dto);
        $orderItems = $this->orderItemsTransformer->transform($this->dto);

        $shipments['shipments']  = [];

        if( $this->dto->getOrder()->getShipmentsCollection()->getSize() ){
            $shipmentsCollection = $this->dto->getOrder()->getShipmentsCollection();

            /** @var \Magento\Sales\Model\Order\Shipment $shipmentsCollection */
            foreach ($shipmentsCollection as $key => $shipment){

                if ( $shipment->getTracks() ){
                    $this->dto->setShipment($shipment);

                    $shipments['shipments'] = $this->shipmentTransformer->formShipmentData($this->dto);
                }
            }

            $shipmentsCollection->clear();
        }

        $orderInfo = [
            'order_info' => array_merge($order, $brand, $locale, $customer, $billing, $orderItems, $shipments)
        ];

        return $this->jsonHelper->jsonEncode($orderInfo);
    }

    /**
     * Method to prepare the log data for new record
     *
     * @param $order_id
     * @param $order_inc_id
     * @param string $requestData
     * @return array :NULL unknown string mixed
     */
    private function prepareLog($order_id, $order_inc_id, $requestData)
    {
        return [
            AuditLog::ORDER_ID => $order_id,
            AuditLog::ORDER_INC_ID => $order_inc_id,
            AuditLog::ENT_TYPE => self::ENTITY_TYPE,
            AuditLog::ACTION => self::ACTION,
            AuditLog::STATUS => $this->auditStatusHelper->getFailure(),
            AuditLog::SLUG => self::SLUG,
            AuditLog::REQ_DATA => $requestData,
            AuditLog::RESPONSE => null,
            AuditLog::REQ_TIME => $this->dateTime->date(),
            AuditLog::FINISH_TIME => $this->dateTime->date()
        ];
    }

    /**
     * Prepare order data and insert it into $this->logDataTableName
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $orders
     */
    public function process(\Magento\Sales\Model\ResourceModel\Order\Collection $orders)
    {
        $logData = [];
        foreach ($orders as $key => $order) {
            $this->dto->setOrder($order);
            $data = $this->prepareOrderData();

            $logData[$key] = $this->prepareLog(
                $this->dto->getOrder()->getEntityId(),
                $this->dto->getOrder()->getIncrementId(),
                $data
            );
        }

        $DbConnection = $this->resource->getConnection();
        $DbConnection->insertMultiple($this->logDataTableName, $logData);
    }

}