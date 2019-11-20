<?php
/**
 * Data Transformer Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\Data;

use Magento\Store\Model\ScopeInterface;
use Narvar\Connect\Exception\ConnectorException;
use Narvar\Connect\Helper\Audit\Type as AuditType;
use Narvar\Connect\Helper\Audit\Action as AuditAction;
use Narvar\Connect\Helper\Audit\Log as AuditLogHelper;
use Narvar\Connect\Helper\Audit\Status as AuditStatusHelper;
use Narvar\Connect\Helper\Config\Debug;
use Narvar\Connect\Helper\ConnectorFactory;
use Narvar\Connect\Helper\Config\Activation;
use Narvar\Connect\Model\Data\DTO;
use Narvar\Connect\Helper\Payment;
use Narvar\Connect\Model\Data\Transformer\Order as OrderTransformer;
use Narvar\Connect\Model\Data\Transformer\Order\Items as OrderItemsTransformer;
use Narvar\Connect\Model\Data\Transformer\Invoice as InvoiceTransformer;
use Narvar\Connect\Model\Data\Transformer\Invoice\Items as InvoiceItemsTransformer;
use Narvar\Connect\Model\Data\Transformer\Customer as CustomerTransformer;
use Narvar\Connect\Model\Data\Transformer\Billing as BillingTransformer;
use Narvar\Connect\Model\Data\Transformer\Address\Location as AddressLocation;
use Narvar\Connect\Model\Data\Transformer\Address\Billing as BillingAddress;
use Narvar\Connect\Model\Data\Transformer\Address\Shipping as ShippingAddress;
use Narvar\Connect\Model\Data\Transformer\Shipments as ShipmentsTransformer;
use Narvar\Connect\Model\Data\Transformer\Brand as BrandTransformer;
use Narvar\Connect\Model\Data\Transformer\Locale as LocaleTransformer;
use Narvar\Connect\Helper\Config\Locale as LocaleResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Narvar\Connect\Model\Delta\Validator as DeltaValidator;
use Narvar\Connect\Model\Audit\Log as AuditLog;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Event\Manager as EventManager;
use Narvar\Connect\Model\Logger\Logger;

class Transformer
{

    /**
     * Slug value for Order Creation API
     */
    const ORDER_SLUG = 'orders/';

    /**
     * Slug value for Order Shipment Creation API
     */
    const SHIPMENT_SLUG = 'shipments/';

    /**
     * @var \Narvar\Connect\Helper\Config\Activation
     */
    private $activationHelper;

    /**
     * @var \Narvar\Connect\Model\Data\DTO
     */
    private $dto;

    /**
     * @var \Narvar\Connect\Helper\Payment
     */
    private $paymentHelper;

    /**
     * @var \Narvar\Connect\Model\Data\Transformer\Order
     */
    private $orderTransformer;

    /**
     * @var \Narvar\Connect\Model\Data\Transformer\Customer
     */
    private $customerTransformer;

    /**
     * @var \Narvar\Connect\Model\Data\Transformer\Shipments
     */
    private $shipmentsTransformer;

    /**
     * @var \Narvar\Connect\Model\Data\Transformer\Address\Location
     */
    private $addressLocation;

    /**
     * @var \Narvar\Connect\Model\Data\Transformer\Address\Billing
     */
    private $billingAddress;

    /**
     * @var \Narvar\Connect\Model\Data\Transformer\Address\Shipping
     */
    private $shippingAddress;

    /**
     * @var \Narvar\Connect\Model\Data\Transformer\Billing
     */
    private $billing;

    /**
     * @var \Narvar\Connect\Model\Data\Transformer\Order\Items
     */
    private $orderItemsTransformer;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Narvar\Connect\Helper\Audit\Log
     */
    private $auditLogHelper;

    /**
     * @var \Narvar\Connect\Model\Delta\Validator
     */
    private $deltaValidator;

    /**
     * @var \Narvar\Connect\Helper\Audit\Status
     */
    private $auditStatusHelper;

    /**
     * @var \Narvar\Connect\Helper\Connector
     */
    private $connector = null;
    
    /**
     * @var \Magento\Framework\Event\Manager
     */
    private $eventManager;
    /**
     * @var BrandTransformer
     */
    private $brandTransformer;
    /**
     * @var LocaleTransformer
     */
    private $localeTransformer;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Debug
     */
    private $debugMode;

    /**
     * @var InvoiceTransformer
     */
    private $invoiceTransformer;

    /**
     * @var InvoiceItemsTransformer
     */
    private $invoiceItemsTransformer;
    /**
     * @var LocaleResolver
     */
    private $locale;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor
     *
     * @param Activation $activationHelper
     * @param DTO $dto
     * @param Payment $paymentHelper
     * @param OrderTransformer $orderTransformer
     * @param OrderItemsTransformer $orderItemsTransformer
     * @param InvoiceTransformer $invoiceTransformer
     * @param InvoiceItemsTransformer $invoiceItemsTransformer
     * @param CustomerTransformer $customerTransformer
     * @param AddressLocation $addressLocation
     * @param BillingAddress $billingAddress
     * @param BillingTransformer $billing
     * @param ShipmentsTransformer $shipmentsTransformer
     * @param ShippingAddress $shippingAddress
     * @param JsonHelper $jsonHelper
     * @param AuditLogHelper $auditLogHelper
     * @param AuditStatusHelper $auditStatusHelper
     * @param DeltaValidator $deltaValidator
     * @param ConnectorFactory $connector
     * @param EventManager $eventManager $eventManager
     * @param BrandTransformer $brandTransformer
     * @param LocaleTransformer $localeTransformer
     * @param LocaleResolver $locale
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param Debug $debugMode
     */
    public function __construct(
        Activation $activationHelper,
        DTO $dto,
        Payment $paymentHelper,
        OrderTransformer $orderTransformer,
        OrderItemsTransformer $orderItemsTransformer,
        InvoiceTransformer $invoiceTransformer,
        InvoiceItemsTransformer $invoiceItemsTransformer,
        CustomerTransformer $customerTransformer,
        AddressLocation $addressLocation,
        BillingAddress $billingAddress,
        BillingTransformer $billing,
        ShipmentsTransformer $shipmentsTransformer,
        ShippingAddress $shippingAddress,
        JsonHelper $jsonHelper,
        AuditLogHelper $auditLogHelper,
        AuditStatusHelper $auditStatusHelper,
        DeltaValidator $deltaValidator,
        ConnectorFactory $connector,
        EventManager $eventManager,
        BrandTransformer $brandTransformer,
        LocaleTransformer $localeTransformer,
        LocaleResolver $locale,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        Debug $debugMode
    ) {
        $this->activationHelper = $activationHelper;
        $this->dto = $dto;
        $this->paymentHelper = $paymentHelper;
        $this->orderTransformer = $orderTransformer;
        $this->customerTransformer = $customerTransformer;
        $this->addressLocation = $addressLocation;
        $this->billingAddress = $billingAddress;
        $this->billing = $billing;
        $this->orderItemsTransformer = $orderItemsTransformer;
        $this->shipmentsTransformer = $shipmentsTransformer;
        $this->shippingAddress = $shippingAddress;
        $this->jsonHelper = $jsonHelper;
        $this->auditLogHelper = $auditLogHelper;
        $this->deltaValidator = $deltaValidator;
        $this->auditStatusHelper = $auditStatusHelper;
        $this->connector = $connector;
        $this->eventManager = $eventManager;
        $this->brandTransformer = $brandTransformer;
        $this->localeTransformer = $localeTransformer;
        $this->logger = $logger;
        $this->debugMode = $debugMode;
        $this->invoiceTransformer = $invoiceTransformer;
        $this->invoiceItemsTransformer = $invoiceItemsTransformer;
        $this->locale = $locale;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Method to check entity type and call respective
     *
     * @param string $entityType
     * @param array $data
     */
    public function transform($entityType, $data, $source)
    {
        $this->logger->info('MAGENTO SOURCE: ' . $source . PHP_EOL);
        $this->dto->setData($data);
        if (! $this->activationHelper->getIsActivatedById($this->dto->getOrder()->getStoreId()) ) {
            $this->logger->info('MAGENTO START EntityType: activationHelper' . ' ' . PHP_EOL);
            return;
        }
        
        if ($entityType === AuditType::ENT_TYPE_ORDER) {
            $this->logger->info('MAGENTO START EntityType: ENT_TYPE_ORDER' . ' ' . PHP_EOL);
            $this->transformOrder($entityType);
        }

        if ($entityType === AuditType::ENT_TYPE_SHIPMENT) {
            $this->logger->info('MAGENTO START EntityType: ENT_TYPE_SHIPMENT' . ' ' . PHP_EOL);
            $this->transformShipment($entityType);
        }

        if ($entityType === AuditType::ENT_TYPE_INVOICE) {
            $this->logger->info('MAGENTO START EntityType: ENT_TYPE_INVOICE' . ' ' . PHP_EOL);
            $this->transformInvoice($entityType);
        }
        $this->logger->info('MAGENTO END '. PHP_EOL);
    }

    /**
     * Method to transform order data
     * by using order, billing address, address location and order items transformer
     * And call the post api
     *
     * @param string $entityType
     */
    public function transformOrder($entityType)
    {
        $requestData = $this->prepareOrder();
        
        $this->processTransform($entityType, AuditAction::ACTION_CREATE, self::ORDER_SLUG, $requestData);
    }

    /**
     * Method to transform order data after invoice updated with item_id
     * by using order, billing address, address location and order items transformer
     * And call the post api
     *
     * @param string $entityType
     */
    public function transformInvoice($entityType)
    {
        $requestData = $this->prepareInvoice();

        $this->processTransform($entityType, AuditAction::ACTION_CREATE, self::ORDER_SLUG, $requestData);
    }

    /**
     * Method to transform shipping data
     * by using shipment and shipping address transformer
     * and call the update api
     *
     * @param string $entityType
     */
    public function transformShipment($entityType)
    {
        if (count($this->dto->getShipment()->getTracks()) <= 0) {
            /*Debug*/
            if ($this->debugMode->getDebugMode($this->dto->getOrder()->getStore())) {
                $message = __METHOD__ . PHP_EOL;
                $message .= 'EntityType:' . ' ' . $entityType . PHP_EOL;
                $message .= 'Shipment has not any tracks' . PHP_EOL;
                $this->logger->info($message);
            }
            return;
        }
        
        $updateSlug = sprintf(
            '%s%s/%s',
            self::ORDER_SLUG,
            $this->dto->getOrder()->getIncrementId(),
            self::SHIPMENT_SLUG
        );

        $shipmentInfo = $this->prepareShipment();

        /*Debug*/
        if (empty($shipmentInfo)) {
            if ($this->debugMode->getDebugMode($this->dto->getOrder()->getStore())) {
                $message = __METHOD__ . PHP_EOL;
                $message .= 'EntityType:' . ' ' . $entityType . PHP_EOL;
                $message .= 'There is no shipment info' . PHP_EOL;
                $this->logger->info($message);
            }
        }

        if (!empty($shipmentInfo)) {
            $this->locale->setStoreId( $this->dto->getOrder()->getStoreId());
            $locale =  $this->locale->getLocale();
            $brand = $this->scopeConfig->getValue('narvar_connect/account/narvar_store_brand',
            ScopeInterface::SCOPE_STORE,
                $this->dto->getOrder()->getStoreId()
            );
            $shipmentInfo['order_info']['attributes'] = [
                'checkout_brand' => $brand,
                'checkout_language' => \Locale::getDisplayLanguage(strstr( $locale, '_', true)),
                'checkout_country' => $this->dto->getOrder()->getBillingAddress()->getCountryId(),
                'checkout_locale' => $locale,
            ];
            $requestData = $this->jsonHelper->jsonEncode($shipmentInfo);
            $this->processTransform($entityType, AuditAction::ACTION_UPDATE, $updateSlug, $requestData);
            //$this->eventManager->dispatch('sales_order_save_commit_after', ['order' => $this->dto->getOrder()]);
        }
    }

    /**
     * Method to process transform the data to narvar and give entry in log data
     *
     * @param string $entityType
     * @param string $action
     * @param string $slug
     * @param string json $requestData
     */
    private function processTransform($entityType, $action, $slug, $requestData)
    {
        $orderId = $this->dto->getOrder()->getId();
        $lastRequestData = $this->auditLogHelper->lastCallRequestData($orderId, $entityType);
        $validate = $this->deltaValidator->isIdentical($lastRequestData, $requestData);

        $message = __METHOD__ . PHP_EOL;
        $message .= 'Magento EntityType:' . ' ' . $entityType . PHP_EOL;
        $message .= 'Magento Action:' . ' ' . $action . PHP_EOL;
        $message .= 'Magento Slug:' . ' ' . $slug . PHP_EOL;
        $message .= 'Magento Request Data:' . ' ' . $requestData . PHP_EOL;
        $message .= 'Magento Validated:' . ' ' .  print_r(($validate) ? 'false' : 'true', true) . PHP_EOL;
        $this->logger->info($message);

        if (! $validate) {
            $logData = $this->prepareLog($entityType, $action, $slug, $requestData);
            
            if ($this->auditLogHelper->hasFailures($orderId, $entityType)) {
                $logData[AuditLog::RESPONSE] = __('Previous Order Data is not pushed to narvar');
                $logData[AuditLog::STATUS] = $this->auditStatusHelper->getFailure();
                $logModel = $this->auditLogHelper->create($logData);
            } else {
                $logModel = $this->auditLogHelper->create($logData);
                $this->callApiWithRetry($logModel, true);
            }
        }
    }

    /**
     * Method to prepare the log data for new record
     *
     * @param string $entityType
     * @param string $action
     * @param string $slug
     * @param string $requestData
     * @return multitype:NULL unknown string mixed
     */
    private function prepareLog($entityType, $action, $slug, $requestData)
    {
        return [
            AuditLog::ORDER_ID => $this->dto->getOrder()->getId(),
            AuditLog::ORDER_INC_ID => $this->dto->getOrder()->getIncrementId(),
            AuditLog::ENT_TYPE => $entityType,
            AuditLog::ACTION => $action,
            AuditLog::STATUS => $this->auditStatusHelper->getPending(),
            AuditLog::SLUG => $slug,
            AuditLog::REQ_DATA => $requestData,
            AuditLog::RESPONSE => null
        ];
    }

    /**
     * Method to prepare the order data into required API Format
     *
     * @return json
     */
    private function prepareOrder()
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
        $orderInfo = [
            'order_info' => array_merge($brand, $locale, $order, $customer, $billing, $orderItems)
        ];
        $this->locale->setStoreId( $this->dto->getOrder()->getStoreId());
        $locale =  $this->locale->getLocale();
        $brand = $this->scopeConfig->getValue('narvar_connect/account/narvar_store_brand',
            ScopeInterface::SCOPE_STORE,
            $this->dto->getOrder()->getStoreId()
        );
        $orderInfo['order_info']['attributes'] = [
            'checkout_brand' => $brand,
            'checkout_language' => \Locale::getDisplayLanguage(strstr( $locale, '_', true)),
            'checkout_country' => $this->dto->getOrder()->getBillingAddress()->getCountryId(),
            'checkout_locale' => $locale,
        ];
        return $this->jsonHelper->jsonEncode($orderInfo);
    }

    /**
     * Method to prepare the order data into required API Format
     *
     * @return json
     */
    private function prepareInvoice()
    {
        $order = $this->invoiceTransformer->transform($this->dto);

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
        $orderItems = $this->invoiceItemsTransformer->transform($this->dto);
        $orderInfo = [
            'order_info' => array_merge($brand, $locale, $order, $customer, $billing, $orderItems)
        ];
        $this->locale->setStoreId( $this->dto->getOrder()->getStoreId());
        $locale =  $this->locale->getLocale();
        $brand = $this->scopeConfig->getValue('narvar_connect/account/narvar_store_brand',
            ScopeInterface::SCOPE_STORE,
            $this->dto->getOrder()->getStoreId()
        );
        $orderInfo['order_info']['attributes'] = [
            'checkout_brand' => $brand,
            'checkout_language' => \Locale::getDisplayLanguage(strstr( $locale, '_', true)),
            'checkout_country' => $this->dto->getOrder()->getBillingAddress()->getCountryId(),
            'checkout_locale' => $locale,
        ];
        return $this->jsonHelper->jsonEncode($orderInfo);
    }

    /**
     * Method to prepare the shipment data into required API Format
     *
     * @return array
     */
    private function prepareShipment()
    {
        $shipmentInfo = [];
        $shipments = $this->shipmentsTransformer->transform($this->dto);
        if (! empty($shipments['shipments'])) {
            $shippingAddress = $this->shippingAddress->transform($this->dto);
            foreach ($shipments['shipments'] as $key => $shipment) {
                $shipments['shipments'][$key]['shipped_to'] = $shippingAddress['shipped_to'];
            }
            $brand = $this->brandTransformer->transform($this->dto);
            $locale = $this->localeTransformer->transform($this->dto);
            $shipmentInfo =[
                'order_info' => array_merge($brand, $locale, $shipments)
            ];
        }
        
        return $shipmentInfo;
    }

    /**
     * Method to call the Post/Put API with given parameter and retry mechanism will work for once
     *
     * @param AuditLog $logModel
     * @param boolean $retry
     */
    public function callApiWithRetry(AuditLog $logModel, $retry = false)
    {
        $message = __METHOD__ . PHP_EOL;
        $message .= 'Magento RequestData:' . ' ' . $logModel->getRequestData() . PHP_EOL;
        $message .= 'Magento Action:' . ' ' . $logModel->getAction() . PHP_EOL;
        $message .= 'Magento Slug:' . ' ' . $logModel->getSlug() . PHP_EOL;
        $this->logger->info($message);
        $this->auditLogHelper->updateProcessing($logModel);
        try {
            $storeId = ['storeId' => $this->dto->getOrder()->getStoreId()];
            if ($logModel->getAction() === AuditAction::ACTION_CREATE) {
                $responseMsg = $this->connector->create(['data' => $storeId])->post($logModel->getSlug(), $logModel->getRequestData());
            }
            
            if ($logModel->getAction() === AuditAction::ACTION_UPDATE) {
                $responseMsg = $this->connector->create(['data' => $storeId])->put($logModel->getSlug(), $logModel->getRequestData());
            }
            
            $this->auditLogHelper->updateSuccess($logModel, $responseMsg);
        } catch (ConnectorException $e) {
            $this->auditLogHelper->updateFailure($logModel, $e->getMessage());
            if ($retry) {
                $this->auditLogHelper->updateOnHold($logModel, $e->getMessage());
                $this->callApiWithRetry($logModel);
            }
        }
    }
}
