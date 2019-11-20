<?php
/**
 * Configuration Attributes Helper
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Helper\Config;

use Narvar\Connect\Helper\Base;
use Magento\Eav\Model\Entity as EavEntityModel;
use Magento\Eav\Model\Entity\Type as EntityTypeModel;
use Narvar\Connect\Model\Eav\Attributes as EavAttributesModel;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Below methods will used to get configuration value
 *
 * @method string getAttrNotificationPref()
 * @method string getAttrBackOrder()
 * @method string getAttrFinalSaleDate()
 * @method string getAttrIsFinalSale()
 * @method string getAttrItemPrmsdate()
 * @method string getAttrDimUom()
 * @method string getAttrLength()
 * @method string getAttrHeight()
 * @method string getAttrWidth()
 * @method string getAttrWeightUom()
 * @method string getAttrShipSource()
 * @method string getAttrShopRunnerEligible()
 * @method string getAttrAdditionalAttr()
 * @method string getConfigurableProductOption()
 */
class Attribute extends Base
{

    /**
     * Attribute Group
     */
    const CONFIG_GRP = 'custom_attributes';

    /**
     * Attribute Notification preference config field name
     */
    const ATTR_NOTIFICATION_PREF = 'notification_pref';

    /**
     * Attribute Back Order config field name
     */
    const ATTR_BACK_ORDER = 'is_backordered';

    /**
     * Attribute Final Sale Date config field name
     */
    const ATTR_FINAL_SALE_DATE = 'final_sale_date';

    /**
     * Attribute Is Final Sale config field name
     */
    const ATTR_IS_FINAL_SALE = 'is_final_sale';

    /**
     * Attribute Item Promise Date config field name
     */
    const ATTR_ITEM_PRMSDATE = 'item_promise_date';

    /**
     * Attribute Dimension config field name
     */
    const ATTR_DIM_UOM = 'dimuom';

    /**
     * Attribute Length config field name
     */
    const ATTR_LENGTH = 'length';

    /**
     * Attribute Width config field name
     */
    const ATTR_WIDTH = 'width';

    /**
     * Attribute Width config field name
     */
    const ATTR_WEIGHT = 'weight';

    /**
     * Attribute Height config field name
     */
    const ATTR_HEIGHT = 'height';

    /**
     * Attribute Dimension config field name
     */
    const ATTR_WEIGHT_UOM = 'weight_uom';

    /**
     * Attribute Ship Source config field name
     */
    const ATTR_SHIP_SOURCE = 'ship_source';
    
    /**
     * Attribute Ship Source config field name
     */
    const ATTR_SHOP_RUNNER_ELIGIBLE = 'is_shoprunner_eligible';

    /**
     * Attribute Additional Parameters config field name
     */
    const ATTR_ADDITIONAL_ATTR = 'additional_attrs';

    /**
     * Constant value for UOM
     */
    const UOM = 'uom';

    /**
     * Constant value for Attribute Color
     */
    const ATTR_COLOR = 'color';

    /**
     * Constant value for Attribute Size
     */
    const ATTR_SIZE = 'size';

    /**
     * Constant value for Color Id
     */
    const ATTR_COLOR_ID = 'color_id';

    /**
     * Constant value for Size Id
     */
    const ATTR_SIZE_ID = 'size_id';

    /**
     * Constant value for Style
     */
    const ATTR_STYLE = 'style';

    /**
     * Constant value for manufacturer
     */
    const ATTR_MANUFACTURER = 'manufacturer';

    /**
     * Constant value for manufacturer key in api
     */
    const ATTR_MANUFACTURER_KEY = 'vendor';

    /**
     * Attribute Line Number config field name
     */
    const ATTR_LINE_NUMBER = 'line_number';

    const CONFIGURABLE_PRODUCT_OPTION = 'configurable_product_option';

    /**
     *
     * @var Magento\Eav\Model\Entity
     */
    private $eavEntityModel;

    /**
     *
     * @var \Magento\Eav\Model\Entity\Type
     */
    private $eavEntityTypeModel;
    
    /**
     * Constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param EavEntityModel $eavEntityModel
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        EavEntityModel $eavEntityModel,
        EntityTypeModel $eavEntityTypeModel
    ) {
        $this->eavEntityModel = $eavEntityModel;
        $this->eavEntityTypeModel = $eavEntityTypeModel;
        
        parent::__construct($context, $storeManager);
    }

    /**
     * Method to extract the Attribute Code from config value
     *
     * @param string $configValue
     * @return string
     */
    public function extractAttributeCode($configValue)
    {
        $startPos = strpos($configValue, '_') + 1;
        
        return substr($configValue, $startPos);
    }

    /**
     * Method to extract the Attribute Entity Type Id from config value
     *
     * @param string $configValue
     * @return string
     */
    public function extractEntityTypeId($configValue)
    {
        return strtok($configValue, '_');
    }

    /**
     * Method to get the Entity Type Code by Entity Type Id
     *
     * @param int $entityTypeId
     * @return string|NULL
     */
    public function getEntityTypeCode($entityTypeId)
    {
        return $this->eavEntityTypeModel->load($entityTypeId)->getEntityTypeCode();
    }

    /**
     * Method to get the Product Entity Type Id
     */
    public function getProductEntityTypeId()
    {
        return $this->eavEntityModel->setType(EavAttributesModel::PRODUCT_ENTITY)->getTypeId();
    }

    /**
     * Method to get the Attribue code assoicated with Product entity Type
     *
     * @param string $attrCode
     * @return string
     */
    public function getProductAttribute($attrCode)
    {
        return sprintf('%s_%s', $this->getProductEntityTypeId(), $attrCode);
    }
}
