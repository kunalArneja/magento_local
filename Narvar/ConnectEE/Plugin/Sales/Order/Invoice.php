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

use Narvar\ConnectEE\Helper\Audit\Type;
use Narvar\ConnectEE\Model\Data\TransformerFactory;

class Invoice
{
    /**
     *
     * @var \Narvar\ConnectEE\Model\Data\TransformerFactory
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
     * Method to push shipment details to narvar
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     */
    public function afterSave(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $data = [
            'order' => $invoice->getOrder(),
            'invoice' => $invoice,
            'shipment' => null,
            'rma' => null
        ];
        
        $this->transformer->create()->transform(Type::ENT_TYPE_INVOICE, $data);
    }
}
