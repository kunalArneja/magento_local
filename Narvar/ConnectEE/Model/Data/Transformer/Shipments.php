<?php
/**
 * Shipment Data Transformer
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Data\Transformer;

use Magento\Directory\Model\Country as CountryModel;
use Narvar\ConnectEE\Helper\Formatter;
use Narvar\ConnectEE\Helper\Config\Status as OrderStatusHelper;
use Narvar\ConnectEE\Helper\Config\Attribute as AttributeHelper;
use Narvar\ConnectEE\Model\Data\DTO;
use Narvar\ConnectEE\Model\Delta\Validator;
use Narvar\ConnectEE\Model\Data\Transformer\AbstractTransformer;
use Narvar\ConnectEE\Model\Data\Transformer\TransformerInterface;
use Magento\Sales\Model\Order\ItemFactory as OrderItem;
use Narvar\ConnectEE\Helper\Config\Carriers as CarrierHelper;
use Narvar\ConnectEE\Model\System\Config\Source\ConfigurableProduct;

class Shipments extends AbstractTransformer implements TransformerInterface
{
    /**
     *
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    private $orderItem;

    /**
     *
     * @var \Narvar\ConnectEE\Helper\Config\Carriers
     */
    private $carrierHelper;

    /**
     * Constructor
     *
     * @param OrderItem $orderItem
     * @param CarrierHelper $carrierHelper
     * @param Formatter $formatter
     * @param Validator $deltaValidator
     * @param OrderStatusHelper $orderStatusHelper
     * @param AttributeHelper $configAttributes
     * @param CountryModel $countryModel
     */
    public function __construct(
        OrderItem $orderItem,
        CarrierHelper $carrierHelper,
        Formatter $formatter,
        Validator $deltaValidator,
        OrderStatusHelper $orderStatusHelper,
        AttributeHelper $configAttributes,
        CountryModel $countryModel
    ) {
        $this->orderItem = $orderItem;
        $this->carrierHelper = $carrierHelper;
    
        parent::__construct(
            $formatter,
            $deltaValidator,
            $orderStatusHelper,
            $configAttributes,
            $countryModel
        );
    }

    /**
     * Method to perpare the Order data in Narvar API Format
     *
     * @see \Narvar\ConnectEE\Model\Data\Transformer\TransformerInterface::transform()
     */
    public function transform(DTO $dto)
    {
        $status = 'SPLIT_SHIP-INIT';
        if (count($dto->getShipment()->getAllItems()) === count($dto->getOrder()->getAllItems())) {
            $status = 'SPLIT_SHIP-COMPLETE';
        }

        $canChange = true;
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($dto->getOrder()->getAllVisibleItems() as $item) {
            /** @var \Magento\Sales\Model\Order\Shipment\Item $shippedItem */
            foreach ($dto->getShipment()->getAllItems() as $shippedItem) {
                if ($item->getSku() === $shippedItem->getSku()) {
                    if ((int)$item->getQtyOrdered() === (int)$item->getQtyShipped() && $canChange) {
                        $status = 'SPLIT_SHIP-COMPLETE';
                    } else {
                        $canChange = false;
                        $status = 'SPLIT_SHIP-INIT';
                    }
                }
            }
        }

        return [
            'order_number' => $this->formatter->format(
                Formatter::FIELDSET_ORDER,
                'order_number',
                $dto->getOrder()->getIncrementId()
            ),
            'shipments' => $this->formShipmentData($dto),
            'status' => $status
        ];
    }

    /**
     * Method to form the Shipment data in required API Format
     *
     * @param DTO $dto
     * @return multitype
     */
    public function formShipmentData(DTO $dto)
    {
        $shipItems = $dto->getShipment()->getAllItems();
        
        $shipSource = $this->getShipSource($shipItems, $dto);
        $itemsInfo = $this->prepareShipmentItems($shipItems);
        $tracks = $dto->getShipment()->getTracks();
        $shipmentData = [];
        foreach ($tracks as $track) {
            $trackInfo = null;
            $trackInfo = [
                'ship_method' => $this->formatter->format(
                    Formatter::FIELDSET_SHIPMENT,
                    'ship_method',
                    $dto->getOrder()->getShippingDescription()
                ),
                'carrier' => $this->formatter->format(
                    Formatter::FIELDSET_SHIPMENT,
                    'carrier',
                    strtoupper($track->getCarrierCode())
                ),
                'carrier_service' => $this->formatter->format(
                    Formatter::FIELDSET_SHIPMENT,
                    'carrier_service',
                    ''
                ),
                'ship_source' => $shipSource,
                'items_info' => $itemsInfo,
                'ship_date' => $this->formatter->format(
                    Formatter::FIELDSET_SHIPMENT,
                    'ship_date',
                    $track->getCreatedAt()
                ),
                'ship_discount' => $this->formatter->format(
                    Formatter::FIELDSET_SHIPMENT,
                    'ship_discount',
                    $dto->getOrder()->getShippingDiscountAmount()
                ),
                'ship_tax' => $this->formatter->format(
                    Formatter::FIELDSET_SHIPMENT,
                    'ship_tax',
                    $dto->getOrder()->getShippingTaxAmount()
                ),
                'ship_total' => $this->formatter->format(
                    Formatter::FIELDSET_SHIPMENT,
                    'ship_total',
                    $dto->getOrder()->getShippingInclTax()
                ),
                'tracking_number' => $this->formatter->format(
                    Formatter::FIELDSET_SHIPMENT,
                    'tracking_number',
                    $track->getTrackNumber()
                )
            ];
            array_push($shipmentData, $trackInfo);
        }
        
        return $shipmentData;
    }

    /**
     * Method to get the ship source value
     *
     * @param array $shipItems
     * @param DTO $dto
     * @return string|Ambigous <string, Ambigous>
     */
    private function getShipSource($shipItems, DTO $dto)
    {
        $shipSource = '';
        if ($this->configAttributes->getAttrShipSource() == '' ||
            $this->configAttributes->getAttrShipSource() == '-1') {
            return $shipSource;
        }
        
        foreach ($shipItems as $shipItem) {
            $orderItem = $this->getOrderItem($shipItem->getOrderItemId());
            $tempShipSource = $this->getAttributeValue(
                Formatter::FIELDSET_SHIPMENT,
                $this->configAttributes->getAttrShipSource(),
                $dto,
                $orderItem,
                'ship_source'
            );
            if (! empty($tempShipSource)) {
                $shipSource = $tempShipSource;
            }
        }
        
        return $shipSource;
    }

    /**
     * Method to get the order Item Id
     *
     * @param int $orderItemId
     * @return \Magento\Framework\Model\$this
     */
    private function getOrderItem($orderItemId)
    {
        return $this->orderItem->create()->load($orderItemId);
    }

    /**
     * Method to prepare the Shipment Item data for API
     *
     * @param array $shipItems
     * @return array:
     */
    private function prepareShipmentItems($shipItems)
    {
        $shipItemsData = [];

        /** @var \Magento\Sales\Model\Order\Shipment\Item $shipItem */
        foreach ($shipItems as $shipItem) {
            $sku = $shipItem->getOrderItem()->getSku();
            $shipItemData = [
                'sku' => $this->formatter->format(
                    Formatter::FIELDSET_SHIPMENT,
                    'sku',
                    $sku
                ),
                'quantity' => $this->formatter->format(
                    Formatter::FIELDSET_SHIPMENT,
                    'quantity',
                    $shipItem->getQty()
                )
            ];

            array_push($shipItemsData, $shipItemData);
        }

        return $shipItemsData;
    }
}
