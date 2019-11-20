<?php
/**
 * Narvar Shipment Plugin
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Plugin\Sales\Order;

use Narvar\Connect\Helper\Audit\Type;
use Narvar\Connect\Model\Data\TransformerFactory;

class Creditmemo
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
     * Method to push shipment details to narvar
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     */
    public function afterSave(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $data = [
            'order' => $creditmemo->getOrder(),
            'shipment' => null,
            'invoice' => null,
            'creditmemo' => $creditmemo
        ];
        
        $this->transformer->create()->transform(Type::ENT_TYPE_INVOICE, $data, 'plugin');
    }
}
