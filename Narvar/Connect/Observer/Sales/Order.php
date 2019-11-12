<?php
/**
 * Narvar Order Save After Observer
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Observer\Sales;

use Narvar\Connect\Helper\Audit\Type;
use Narvar\Connect\Model\Data\TransformerFactory;
use Magento\Framework\Event\ObserverInterface;

class Order implements ObserverInterface
{

    /**
     *
     * @var \Narvar\Connect\Model\Data\TransformerFactory
     */
    private $transformer;
    
    /**
     * Constructor
     *
     * @param Transformer $transformer
     */
    public function __construct(TransformerFactory $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Method to push Order details to narvar
     *
     * @param \Magento\Sales\Model\Order $order
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $data = [
            'order' => $order,
            'shipment' => null,
            'invoice' => null,
        ];
        
        $this->transformer->create()->transform(Type::ENT_TYPE_ORDER, $data);
    }
}
