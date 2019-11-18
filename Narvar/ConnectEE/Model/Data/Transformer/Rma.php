<?php
/**
 * Rma Data Transformer
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
use Narvar\ConnectEE\Model\Delta\Validator;
use Narvar\ConnectEE\Helper\Formatter;
use Narvar\ConnectEE\Helper\Config\Status as OrderStatusHelper;
use Narvar\ConnectEE\Helper\Config\Attribute as AttributeHelper;
use Narvar\ConnectEE\Model\Data\DTO;
use Magento\Rma\Helper\Eav as RmaEavHelper;

class Rma extends AbstractTransformer implements TransformerInterface
{
    /**
     * Const for RMA_REASON EAV Group
     */
    const RMA_REASON = 'reason';

    /**
     * Const for RMA_ITEM_CONDITION EAV Group
     */
    const RMA_ITEM_CONDITION = 'condition';

    /**
     * Const for RMA_RESOLUTION EAV Group
     */
    const RMA_RESOLUTION = 'resolution';

    /**
     * @var RmaEavHelper
     */
    private $rmaEavHelper;

    /**
     * Constructor
     *
     * @param Formatter $formatter
     * @param Validator $deltaValidator
     * @param OrderStatusHelper $orderStatusHelper
     * @param AttributeHelper $configAttributes
     * @param CountryModel $countryModel
     * @param RmaEavHelper $rmaEavHelper
     */
    public function __construct(
        Formatter $formatter,
        Validator $deltaValidator,
        OrderStatusHelper $orderStatusHelper,
        AttributeHelper $configAttributes,
        CountryModel $countryModel,
        RmaEavHelper $rmaEavHelper
    ) {
        $this->rmaEavHelper = $rmaEavHelper;
    
        parent::__construct(
            $formatter,
            $deltaValidator,
            $orderStatusHelper,
            $configAttributes,
            $countryModel
        );
    }

    /**
     * Method to prepare the Order data in Narvar API Format
     *
     * @see \Narvar\ConnectEE\Model\Data\Transformer\TransformerInterface::transform()
     * @param DTO $dto
     * @return array
     */
    public function transform(DTO $dto)
    {
        return [
            'order_number' => $this->formatter->format(
                Formatter::FIELDSET_ORDER,
                'order_number',
                $dto->getOrder()->getIncrementId()
            ),
            'rma' => $this->formRmaData($dto)
        ];
    }

    /**
     * Method to form the Rma data in required API Format
     *
     * @param DTO $dto
     * @return array
     */
    public function formRmaData(DTO $dto)
    {
        $rmaData = [
            'rma_number' => $this->formatter->format(
                Formatter::FIELDSET_RMA,
                'rma_number',
                $dto->getRma()->getIncrementId()
            ),
            'rma_date' => $this->formatter->format(
                Formatter::FIELDSET_RMA,
                'rma_date',
                $dto->getRma()->getDateRequested()
            ),
            'rma_status' => $this->formatter->format(
                Formatter::FIELDSET_RMA,
                'rma_status',
                $dto->getRma()->getStatus()
            ),
            'rma_items' => $this->formRmaItemsData($dto)
        ];

        return $rmaData;
    }

    /**
     * Prepare Rma Items Data
     *
     * @param DTO $dto
     * @return array
     */
    private function formRmaItemsData(DTO $dto)
    {
        $rmaItems = $dto->getRma()->getItems();
        $storeId = $dto->getOrder()->getStoreId();
        $reasons = $this->rmaEavHelper->getAttributeOptionValues(self::RMA_REASON, $storeId);
        $conditions = $this->rmaEavHelper->getAttributeOptionValues(self::RMA_ITEM_CONDITION, $storeId);
        $resolutions = $this->rmaEavHelper->getAttributeOptionValues(self::RMA_RESOLUTION, $storeId);
        $rmaItemsData = [];

        /**
         * @var \Magento\Rma\Model\Item $item
         */
        foreach ($rmaItems as $item) {
            $rmaInfo = [
                'product_name' => $this->formatter->format(
                    Formatter::FIELDSET_RMA_ITEMS,
                    'product_name',
                    $item->getProductName()
                ),
                'product_sku' => $this->formatter->format(
                    Formatter::FIELDSET_RMA_ITEMS,
                    'product_sku',
                    $item->getProductSku()
                ),
                'reason' => $this->formatter->format(
                    Formatter::FIELDSET_RMA_ITEMS,
                    'reason',
                    $item->getReason() > 0
                        ? $reasons[$item->getReason()]
                        : $item->getReasonOther()
                ),
                'resolution' => $this->formatter->format(
                    Formatter::FIELDSET_RMA_ITEMS,
                    'resolution',
                    $resolutions[$item->getResolution()]
                ),
                'item_condition' => $this->formatter->format(
                    Formatter::FIELDSET_RMA_ITEMS,
                    'item_condition',
                    $conditions[$item->getCondition()]
                ),
                'qty_requested' => $this->formatter->format(
                    Formatter::FIELDSET_RMA_ITEMS,
                    'qty_requested',
                    $item->getQtyRequested()
                ),
                'qty_returned' => $this->formatter->format(
                    Formatter::FIELDSET_RMA_ITEMS,
                    'qty_returned',
                    $item->getQtyReturned()
                ),
                'qty_approved' => $this->formatter->format(
                    Formatter::FIELDSET_RMA_ITEMS,
                    'qty_approved',
                    $item->getQtyReturned()
                ),
                'status' => $this->formatter->format(
                    Formatter::FIELDSET_RMA_ITEMS,
                    'status',
                    $item->getStatus()
                )
            ];

            array_push($rmaItemsData, $rmaInfo);
        }

        return $rmaItemsData;
    }

}
