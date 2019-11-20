<?php
/**
 * Narvar Order Repository Plugin
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Plugin\Sales;

use Magento\Sales\Api\Data\OrderInterface;
use Narvar\Connect\Helper\Audit\Type;
use Narvar\Connect\Model\Data\TransformerFactory;

class OrderRepository
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
     * Method to push order details to narvar
     *
     * @param \Magento\Sales\Model\OrderRepository $order
     * @param $result
     * @param OrderInterface $entity
     */
    public function afterSave(
      \Magento\Sales\Model\OrderRepository $order,
        $result,
        OrderInterface $entity)
    {        

        $data = [
            'order' => $entity,
            'shipment' => null,
            'invoice' => null
        ];
        
        $this->transformer->create()->transform(Type::ENT_TYPE_ORDER, $data, 'plugin');
        return $result;
    }
}