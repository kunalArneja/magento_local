<?php
/**
 * Narvar RMA Plugin
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Plugin\Rma;

use Narvar\ConnectEE\Helper\Audit\Type;
use Narvar\ConnectEE\Model\Data\TransformerFactory;

class Rma
{
    /**
     *
     * @var \Narvar\ConnectEE\Model\Data\Transformer
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
     * Method to push RMA details to narvar
     *
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function afterSave(\Magento\Rma\Model\Rma $rma)
    {
        /**If this event triggered after incoming API return request not to continue process */
        if ($rma->getNotPushToNarvar()) {
            return;
        }

        $order = $rma->getOrder();
        $data = [
            'order' => $order,
            'rma' => $rma,
            'shipment' => null,
            'invoice' => null
        ];
        
        $this->transformer->create()->transform(Type::ENT_TYPE_RMA, $data);
    }
}
