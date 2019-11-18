<?php
/**
 * Narvar Shipment Plugin
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Plugin\Sales\Order;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Narvar\ConnectEE\Helper\Audit\Type;
use Narvar\ConnectEE\Model\Data\TransformerFactory;

class ShipmentRepository
{

    /**
     *
     * @var \Narvar\ConnectEE\Model\Data\TransformerFactory
     */
    private $transformer;
    /**
     * @var OrderRepositoryInterface
     */
    private $order;

    /**
     * Constructor
     *
     * @param TransformerFactory $transformer
     * @param OrderRepositoryInterface $order
     */
    public function __construct(
        TransformerFactory $transformer,
        OrderRepositoryInterface $order
    ) {
        $this->transformer = $transformer;
        $this->order = $order;
    }

    /**
     * Method to push shipment details to narvar
     *
     * @param \Magento\Sales\Model\Order\ShipmentRepository $shipment
     * @param $result
     * @param ShipmentInterface $entity
     */
    public function afterSave(
        \Magento\Sales\Model\Order\ShipmentRepository $shipment,
        $result,
        ShipmentInterface $entity)
    {
        $data = [
            'order' => $this->order->get($entity->getOrderId()),
            'shipment' => $entity,
            'invoice' => null,
            'rma' => null
        ];
        
        $this->transformer->create()->transform(Type::ENT_TYPE_SHIPMENT, $data);
        return $result;
    }
}
