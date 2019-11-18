<?php
/**
 * Order Data Transformer
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Data\Transformer;

use Narvar\ConnectEE\Model\Data\Transformer\AbstractTransformer;
use Narvar\ConnectEE\Model\Data\Transformer\TransformerInterface;
use Narvar\ConnectEE\Model\Data\DTO;
use Narvar\ConnectEE\Helper\Config\Attribute as AttributeHelper;
use Narvar\ConnectEE\Helper\Formatter;
use Magento\Sales\Model\Order as OrderModel;

class Order extends AbstractTransformer implements TransformerInterface
{
    /**
     * Method to perpare the Order data in Narvar API Format
     *
     * @see Narvar_Connect_Model_Data_Transformer_Interface::transform()
     */
    public function transform(DTO $dto)
    {
        return [
            'order_number' => $this->formatter->format(
                Formatter::FIELDSET_ORDER,
                'order_number',
                $dto->getOrder()->getIncrementId()
            ),
            'order_date' => $this->formatter->format(
                Formatter::FIELDSET_ORDER,
                'order_date',
                $dto->getOrder()->getCreatedAt()
            ),
            'status' => $this->formatter->format(
                Formatter::FIELDSET_ORDER,
                'status',
                $this->getOrderStatus($dto->getOrder())
            ),
            'currency_code' => $this->formatter->format(
                Formatter::FIELDSET_ORDER,
                'currency_code',
                $dto->getOrder()->getOrderCurrencyCode()
            ),
            'is_shoprunner_eligible' => $this->getAttributeValue(
                Formatter::FIELDSET_ORDER,
                $this->configAttributes->getAttrShopRunnerEligible(),
                $dto,
                null,
                'is_shoprunner_eligible'
            )
        ];
    }
    
    /**
     * Method to get the Order Status
     *
     * @param OrderModel $order
     * @return mixed|string
     */
    private function getOrderStatus(OrderModel $order)
    {
        $orderItems = $order->getAllItems();
        $status = [];
        foreach ($orderItems as $orderItem) {
            if (!$orderItem->getParentItemId()) {
                $status[] = $this->getItemStatus($orderItem);
            }
        }
       
        if (count(array_unique($status)) == 1) {
            return current($status);
        }
        
        if (in_array($this->orderStatusHelper->getOrderedStatus(), $status)) {
            return $this->orderStatusHelper->getPartialStatus();
        }
        
        if (in_array($this->orderStatusHelper->getPartialStatus(), $status)) {
            return $this->orderStatusHelper->getPartialStatus();
        }
        
        if (in_array($this->orderStatusHelper->getCancelledStatus(), $status)
            && in_array($this->orderStatusHelper->getShippedStatus(), $status)) {
            return $this->orderStatusHelper->getShippedStatus();
        }
        
        return $this->orderStatusHelper->getPartialStatus();
    }
}
