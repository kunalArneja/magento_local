<?php
/**
 * Invoice Items Data Transformer Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\Data\Transformer\Invoice;

use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Directory\Model\Country as CountryModel;
use Magento\Framework\App\Area;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Narvar\Connect\Helper\Formatter;
use Narvar\Connect\Helper\Config\Status as OrderStatusHelper;
use Narvar\Connect\Helper\Config\Attribute as AttributeHelper;
use Narvar\Connect\Model\Data\DTO;
use Narvar\Connect\Model\Delta\Validator;
use Narvar\Connect\Model\Data\Transformer\AbstractTransformer;
use Narvar\Connect\Model\Data\Transformer\TransformerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollection;
use Magento\Catalog\Helper\Image as ImageHelper;
use Narvar\Connect\Model\System\Config\Source\ConfigurableProduct;
use Magento\Framework\App\State;

class Items extends AbstractTransformer implements TransformerInterface
{

    /**
     * @var Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollection;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;
    /**
     * @var Emulation
     */
    private $appEmulation;
    /**
     * @var ImageFactory
     */
    private $imageHelperFactory;
    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var State
     */
    private $state;

    /**
     * Constructor
     *
     * @param CategoryCollection $categoryCollection
     * @param ImageHelper $imageHelper
     * @param Formatter $formatter
     * @param Validator $deltaValidator
     * @param OrderStatusHelper $orderStatusHelper
     * @param AttributeHelper $configAttributes
     * @param CountryModel $countryModel
     * @param Emulation $appEmulation
     * @param ImageFactory $imageHelperFactory
     * @param StoreManagerInterface $storeManager
     * @param State $state
     */
    public function __construct(
        CategoryCollection $categoryCollection,
        ImageHelper $imageHelper,
        Formatter $formatter,
        Validator $deltaValidator,
        OrderStatusHelper $orderStatusHelper,
        AttributeHelper $configAttributes,
        CountryModel $countryModel,
        Emulation $appEmulation,
        ImageFactory $imageHelperFactory,
        StoreManagerInterface $storeManager,
        State $state
    ) {
        $this->categoryCollection = $categoryCollection;
        $this->imageHelper = $imageHelper;

        parent::__construct(
            $formatter,
            $deltaValidator,
            $orderStatusHelper,
            $configAttributes,
            $countryModel
        );
        $this->appEmulation = $appEmulation;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->storeManager = $storeManager;
        $this->state = $state;
    }

    /**
     * Method form order items data as Narvar API Required format Data
     *
     * @see \Narvar\Connect\Model\Data\Transformer\TransformerInterface::transform()
     */
    public function transform(DTO $dto)
    {
        $fieldGroup = Formatter::FIELDSET_ORDERITEM;
        $items = $dto->getInvoice()->getAllItems();
        $storeId = $dto->getInvoice()->getStoreId();
        $returnData = [];
        $lineNumber = 1;
        /** @var \Magento\Sales\Model\Order\Invoice\Item $item */
        foreach ($items as $item) {
            if (!$item->isDeleted() && !$item->getOrderItem()->getParentItem()) {
                if ($this->configAttributes->getConfigurableProductOption() == ConfigurableProduct::SIMPLE &&
                    count($item->getOrderItem()->getChildrenItems()) > 0) {
                    $product = $item->getOrderItem()->getChildrenItems()[0]->getProduct();
                } else {
                    $product = $item->getOrderItem()->getProduct();
                }

                if (count($item->getOrderItem()->getChildrenItems()) > 0) {
                    $orderItem = $item->getOrderItem()->getChildrenItems()[0];
                } else {
                    $orderItem = $item->getOrderItem();
                }

                if (!$product) {
                    continue;
                }
                $commonAttrData = $this->getCommonAttrData($fieldGroup, $dto, $orderItem);
                $unitPrice = $item->getBasePrice() - ($item->getBaseDiscountAmount() / $item->getQty()) ;
                $discountAmount = (float) $item->getBaseDiscountAmount() / $item->getQty();
                $discountPercent = (float) $item->getOrderItem()->getDiscountPercent();

                $itemData = [
                    'categories' => $this->getProductsCategories(
                        $product->getCategoryIds()
                    ),
                    'description' => $this->formatter->format(
                        $fieldGroup,
                        'description',
                        $product->getShortDescription()
                    ),
                    'discount_amount' => $this->formatter->format(
                        $fieldGroup,
                        'discount_amount',
                        $discountAmount
                    ),
                    'discount_percent' => $this->formatter->format(
                        $fieldGroup,
                        'discount_percent',
                        $discountPercent
                    ),
                    'line_number' => $this->formatter->format($fieldGroup, 'line_number', $lineNumber),
                    'fulfillment_status' => $this->formatter->format(
                        $fieldGroup,
                        'fulfillment_status',
                        $this->orderedToNotShipped($orderItem)
                    ),
                    'is_gift' => $this->formatter->format(
                        $fieldGroup,
                        'is_gift',
                        $item->getOrderItem()->getGiftMessageAvailable() > 0 ? true : false
                    ),
                    'item_id' => $this->formatter->format(
                        $fieldGroup,
                        'item_id',
                        $item->getOrderItem()->getId()
                    ),
                    'item_image' => $this->formatter->format(
                        $fieldGroup,
                        'item_image',
                        $this->getProductImage($orderItem->getProduct(), $storeId)
                    ),
                    'item_url' => $this->formatter->format(
                        $fieldGroup,
                        'item_url',
                        $this->getProductUrl($product, $storeId)
                    ),
                    'name' => $this->formatter->format(
                        $fieldGroup,
                        'item_url',
                        $product->getName()
                    ),
                    'sku' => $this->formatter->format(
                        $fieldGroup,
                        'sku',
                        $orderItem->getProduct()->getSku()
                    ),
                    'quantity' => $this->formatter->format(
                        $fieldGroup,
                        'quantity',
                        $item->getQty()
                    ),
                    'unit_price' => $this->formatter->format(
                        $fieldGroup,
                        'unit_price',
                        $unitPrice
                    ),
                    'dimension' => $this->getDimensionData($fieldGroup, $dto, $orderItem),
                    'attributes' => $this->getCustomAttrValues($fieldGroup, $dto, $orderItem)
                ];

                array_push($returnData, array_merge($itemData, $commonAttrData));
                $lineNumber ++;
            }
        }

        $itemsInfo = [
            'order_items' => $returnData
        ];

        return $itemsInfo;
    }

    /**
     * Method to get the Product Image Url
     * If product image is not set, then get the placeholder from either config/skin path
     *
     * @param Product $product
     * @param $storeId
     * @return string
     */
    private function getProductImage(Product $product, $storeId)
    {
        $needStop = false;
        if ($this->storeManager->getStore()->getId() != $storeId ||
            $this->state->getAreaCode() == Area::AREA_ADMINHTML) {
            $needStop = true;
            $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        }
        $imageUrl = $this->imageHelperFactory->create()
            ->init($product, 'product_page_image_medium')->getUrl();
        if ($needStop) {
            $this->appEmulation->stopEnvironmentEmulation();
        }
        return $imageUrl;
    }

    /**
     * Method to get the Common Attributes Data
     *
     * @param string $fieldGroup
     * @param DTO $dto
     * @param Item $item
     * @return multitype
     */
    private function getCommonAttrData(
        $fieldGroup,
        DTO $dto,
        Item $item = null
    ) {
        $commonAttrs = [
            AttributeHelper::ATTR_MANUFACTURER_KEY => $this->configAttributes->getProductAttribute(
                AttributeHelper::ATTR_MANUFACTURER
            ),
            AttributeHelper::ATTR_FINAL_SALE_DATE => $this->configAttributes->getAttrFinalSaleDate(),
            AttributeHelper::ATTR_BACK_ORDER => $this->configAttributes->getAttrBackOrder(),
            AttributeHelper::ATTR_IS_FINAL_SALE => $this->configAttributes->getAttrIsFinalSale(),
            AttributeHelper::ATTR_ITEM_PRMSDATE => $this->configAttributes->getAttrItemPrmsdate()
        ];

        return $this->getAttributeValueByKey($fieldGroup, $commonAttrs, $dto, $item);
    }

    /**
     * Method to get the Product Categories
     *
     * @param array $categoryIds
     * @return multitype:NULL
     */
    private function getProductsCategories($categoryIds)
    {
        $categories = $this->categoryCollection->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter(
                'entity_id',
                [
                    'in' => $categoryIds
                ]
            );

        $catNames = [];
        foreach ($categories as $category) {
            $catNames[] = $category->getName();
        }

        return $catNames;
    }

    /**
     * Method to form Dimension Data for Order Item
     *
     * @param string $fieldGroup
     * @param DTO $dto
     * @param Item $item
     * @return multitype
     */
    private function getDimensionData(
        $fieldGroup,
        DTO $dto,
        Item $item = null
    ) {
        $configValues = [
            AttributeHelper::ATTR_LENGTH => $this->configAttributes->getAttrLength(),
            AttributeHelper::ATTR_WIDTH => $this->configAttributes->getAttrWidth(),
            AttributeHelper::ATTR_HEIGHT => $this->configAttributes->getAttrHeight(),
            AttributeHelper::ATTR_WEIGHT => $this->configAttributes->getProductAttribute(AttributeHelper::ATTR_WEIGHT)
        ];

        $uom = [
            AttributeHelper::UOM => (string) $this->configAttributes->getAttrDimUom(),
            AttributeHelper::ATTR_WEIGHT_UOM => (string) $this->configAttributes->getAttrWeightUom()
        ];

        $attributeKeyData = $this->getAttributeValueByKey($fieldGroup, $configValues, $dto, $item);

        return array_merge($attributeKeyData, $uom);
    }

    /**
     * Method to form Custom Attributes Data for Order Item
     *
     * @param string $fieldGroup
     * @param DTO $dto
     * @param Item $item
     * @return multitype
     */
    private function getCustomAttrValues(
        $fieldGroup,
        DTO $dto,
        Item $item = null
    ) {
        $configValues = [
            AttributeHelper::ATTR_COLOR => $this->configAttributes->getProductAttribute(AttributeHelper::ATTR_COLOR),
            AttributeHelper::ATTR_COLOR_ID => $this->configAttributes->getProductAttribute(
                AttributeHelper::ATTR_COLOR_ID
            ),
            AttributeHelper::ATTR_SIZE => $this->configAttributes->getProductAttribute(AttributeHelper::ATTR_SIZE),
            AttributeHelper::ATTR_SIZE_ID => $this->configAttributes->getProductAttribute(
                AttributeHelper::ATTR_SIZE_ID
            ),
            AttributeHelper::ATTR_STYLE => $this->configAttributes->getProductAttribute(AttributeHelper::ATTR_STYLE)
        ];
        $attributeKeyData = $this->getAttributeValueByKey($fieldGroup, $configValues, $dto, $item);

        if ($this->configAttributes->getAttrAdditionalAttr() &&
            $this->configAttributes->getAttrAdditionalAttr() != '-1') {
            $configValuesCustom = explode(',', $this->configAttributes->getAttrAdditionalAttr());
            $attributeCodeData = $this->getAttributeValueByCode($fieldGroup, $configValuesCustom, $dto, $item);

            return array_merge($attributeKeyData, $attributeCodeData);
        }

        return $attributeKeyData;
    }

    /**
     * @param $parentItem
     * @return string
     */
    private function orderedToNotShipped($parentItem)
    {
        $status = $this->getItemStatus($parentItem);

        if ($status == $this->orderStatusHelper->getOrderedStatus()) {
            return $this->orderStatusHelper->getNotShippedStatus();
        }

        return $status;
    }

    /**
     * @param $product \Magento\Catalog\Model\Product
     * @param $storeId
     * @return mixed
     */
    private function getProductUrl($product, $storeId)
    {
        $needStop = false;
        if ($this->storeManager->getStore()->getId() != $storeId ||
            $this->state->getAreaCode() == Area::AREA_ADMINHTML) {
            $needStop = true;
            $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        }
        $url = $product->getProductUrl();
        if ($needStop) {
            $this->appEmulation->stopEnvironmentEmulation();
        }
        return $url;
    }
}
