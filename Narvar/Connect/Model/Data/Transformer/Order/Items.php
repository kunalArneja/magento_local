<?php
/**
 * Order Items Data Transformer Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\Data\Transformer\Order;

use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Directory\Model\Country as CountryModel;
use Magento\Framework\App\Area;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\App\Emulation;
use Narvar\Connect\Helper\Formatter;
use Narvar\Connect\Helper\Config\Status as OrderStatusHelper;
use Narvar\Connect\Helper\Config\Attribute as AttributeHelper;
use Narvar\Connect\Model\Data\DTO;
use Narvar\Connect\Model\Delta\Validator;
use Narvar\Connect\Model\Data\Transformer\AbstractTransformer;
use Narvar\Connect\Model\Data\Transformer\TransformerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollection;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Helper\Image as ImageHelper;
use Narvar\Connect\Model\System\Config\Source\ConfigurableProduct;

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
     * @var ImageFactory
     */
    private $imageHelperFactory;
    /**
     * @var Emulation
     */
    private $appEmulation;

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
        ImageFactory $imageHelperFactory
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
        $this->imageHelperFactory = $imageHelperFactory;
        $this->appEmulation = $appEmulation;
    }

    /**
     * Method form order items data as Narvar API Required format Data
     *
     * @see \Narvar\Connect\Model\Data\Transformer\TransformerInterface::transform()
     */
    public function transform(DTO $dto)
    {
        $fieldGroup = Formatter::FIELDSET_ORDERITEM;
        $orderItems = $dto->getOrder()->getAllItems();
        $storeId = $dto->getOrder()->getStoreId();
        $returnData = [];
        $lineNumber = 1;
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() == ProductType::TYPE_SIMPLE) {
                $commonAttrData = $this->getCommonAttrData($fieldGroup, $dto, $orderItem);
                $parentItem = $orderItem;
                if ($orderItem->getParentItem()) {
                    $parentItem = $orderItem->getParentItem();
                }
                if (!$parentItem->getProduct()) {
                    continue;
                }
                if ($this->configAttributes->getConfigurableProductOption() == ConfigurableProduct::PARENT) {
                    $product = $parentItem->getProduct();
                } elseif ($this->configAttributes->getConfigurableProductOption() == ConfigurableProduct::SIMPLE) {
                    $product = $orderItem->getProduct();
                }
                $orderItemData = [
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
                        $parentItem->getBaseDiscountAmount() / $parentItem->getQtyOrdered()
                    ),
                    'discount_percent' => $this->formatter->format(
                        $fieldGroup,
                        'discount_percent',
                        $parentItem->getDiscountPercent()
                    ),
                    'line_number' => $this->formatter->format($fieldGroup, 'line_number', $lineNumber),
                    'fulfillment_status' => $this->formatter->format(
                        $fieldGroup,
                        'fulfillment_status',
                        $this->orderedToNotShipped($parentItem)
                    ),
                    'is_gift' => $this->formatter->format(
                        $fieldGroup,
                        'is_gift',
                        $parentItem->getGiftMessageAvailable() > 0 ? true : false
                    ),
                    'item_id' => $this->formatter->format(
                        $fieldGroup,
                        'item_id',
                        $parentItem->getId()
                    ),
                    'item_image' => $this->formatter->format(
                        $fieldGroup,
                        'item_image',
                        $this->getProductImage($product, $storeId)
                    ),
                    'item_url' => $this->formatter->format(
                        $fieldGroup,
                        'item_url',
                        $this->getProductUrl($parentItem->getProduct(), $storeId)
                    ),
                    'name' => $this->formatter->format(
                        $fieldGroup,
                        'item_url',
                        $product->getName()
                    ),
                    'sku' => $this->formatter->format(
                        $fieldGroup,
                        'sku',
                        $orderItem->getSku()
                    ),
                    'quantity' => $this->formatter->format(
                        $fieldGroup,
                        'quantity',
                        $parentItem->getQtyOrdered()
                    ),
                    'unit_price' => $this->formatter->format(
                        $fieldGroup,
                        'unit_price',
                        $parentItem->getBasePrice() - ($parentItem->getBaseDiscountAmount() / $parentItem->getQtyOrdered())
                    ),
                    'dimension' => $this->getDimensionData($fieldGroup, $dto, $orderItem)
                ];

                if (!$dto->getOrder()->getOrigData()) {
                    $productItemData = [
                        'attributes' => $this->getCustomAttrValues($fieldGroup, $dto, $orderItem)
                    ];
                    $orderItemData = array_merge($orderItemData, $productItemData);
                }

                array_push($returnData, array_merge($orderItemData, $commonAttrData));
                $lineNumber ++;
            }
        }
        
        $orderItemsInfo = [
            'order_items' => $returnData
        ];
        
        return $orderItemsInfo;
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
        $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        $imageUrl = $this->imageHelperFactory->create()
            ->init($product, 'product_page_image_medium')->getUrl();
        $this->appEmulation->stopEnvironmentEmulation();
        return $imageUrl;
    }

    /**
     * Method to get the Common Attributes Data
     *
     * @param string $fieldGroup
     * @param DTO $dto
     * @param Item $orderItem
     * @return multitype
     */
    private function getCommonAttrData(
        $fieldGroup,
        DTO $dto,
        Item $orderItem = null
    ) {
        $commonAttrs = [
            AttributeHelper::ATTR_MANUFACTURER_KEY => $this->configAttributes->getProductAttribute(
                AttributeHelper::ATTR_MANUFACTURER
            ),
            AttributeHelper::ATTR_FINAL_SALE_DATE => $this->configAttributes->getAttrFinalSaleDate(),
            AttributeHelper::ATTR_BACK_ORDER => $this->configAttributes->getAttrBackOrder(),
            AttributeHelper::ATTR_ITEM_PRMSDATE => $this->configAttributes->getAttrItemPrmsdate()
        ];
        if (!$dto->getOrder()->getOrigData()) {
            $commonAttrs[AttributeHelper::ATTR_IS_FINAL_SALE] = $this->configAttributes->getAttrIsFinalSale();
        }
        
        return $this->getAttributeValueByKey($fieldGroup, $commonAttrs, $dto, $orderItem);
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
     * @param Item $orderItem
     * @return multitype
     */
    private function getDimensionData(
        $fieldGroup,
        DTO $dto,
        Item $orderItem = null
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
        
        $attributeKeyData = $this->getAttributeValueByKey($fieldGroup, $configValues, $dto, $orderItem);
        
        return array_merge($attributeKeyData, $uom);
    }

    /**
     * Method to form Custom Attributes Data for Order Item
     *
     * @param string $fieldGroup
     * @param DTO $dto
     * @param Item $orderItem
     * @return multitype
     */
    private function getCustomAttrValues(
        $fieldGroup,
        DTO $dto,
        Item $orderItem = null
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
        $attributeKeyData = $this->getAttributeValueByKey($fieldGroup, $configValues, $dto, $orderItem);
        
        if ($this->configAttributes->getAttrAdditionalAttr() &&
                $this->configAttributes->getAttrAdditionalAttr() != '-1') {
            $configValuesCustom = explode(',', $this->configAttributes->getAttrAdditionalAttr());
            $attributeCodeData = $this->getAttributeValueByCode($fieldGroup, $configValuesCustom, $dto, $orderItem);
            
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
        $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        $url = $product->getProductUrl();
        $this->appEmulation->stopEnvironmentEmulation();
        return $url;
    }
}
