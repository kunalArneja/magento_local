<?php
/**
 * Narvar Order Plugin
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Plugin\Sales;

use Narvar\Connect\Helper\Audit\Type;
use Narvar\Connect\Model\Data\TransformerFactory;

class Order
{

    /**
     *
     * @var \Narvar\Connect\Model\Data\Transformer
     */
    private $transformer;
    
    /**
     * Constructor
     *
     * @param TransformerFactory $transformer
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
    public function afterSave(\Magento\Sales\Model\Order $order)
    {
        $data = [
            'order' => $order,
            'shipment' => null,
            'invoice' => null,
        ];
        
        $this->transformer->create()->transform(Type::ENT_TYPE_ORDER, $data);
    }
}
