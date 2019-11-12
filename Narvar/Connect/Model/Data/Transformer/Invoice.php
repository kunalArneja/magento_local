<?php
/**
 * Invoice Data Transformer
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\Data\Transformer;

use Narvar\Connect\Model\Data\Transformer\AbstractTransformer;
use Narvar\Connect\Model\Data\Transformer\TransformerInterface;
use Narvar\Connect\Model\Data\DTO;
use Narvar\Connect\Helper\Config\Attribute as AttributeHelper;
use Narvar\Connect\Helper\Formatter;
use Magento\Sales\Model\Order as OrderModel;

class Invoice extends AbstractTransformer implements TransformerInterface
{
    /**
     * Method to perpare the Order data in Narvar API Format
     *
     * @see Narvar_Connect_Model_Data_Transformer_Interface::transform()
     */
    public function transform(DTO $dto)
    {
        $status = 'PARTIAL';
        if (count($dto->getInvoice()->getAllItems()) === count($dto->getOrder()->getAllItems())) {
            $status = 'ORDERED';
        }

        $canChange = true;
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($dto->getOrder()->getAllVisibleItems() as $item) {
            /** @var \Magento\Sales\Model\Order\Invoice\Item $invoiceItem */
            foreach ($dto->getInvoice()->getAllItems() as $invoiceItem) {
                if (!$invoiceItem->getOrderItem()->getParentItemId() && $item->getSku() === $invoiceItem->getSku()) {
                    if ((int)$item->getQtyOrdered() === (int)$item->getQtyInvoiced() && $canChange) {
                        $status = 'ORDERED';
                    } else {
                        $canChange = false;
                        $status = 'PARTIAL';
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
            'order_date' => $this->formatter->format(
                Formatter::FIELDSET_ORDER,
                'order_date',
                $dto->getOrder()->getCreatedAt()
            ),
            'status' => $this->formatter->format(
                Formatter::FIELDSET_ORDER,
                'status',
                $status
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
}
