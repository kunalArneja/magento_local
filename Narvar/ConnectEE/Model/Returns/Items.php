<?php
/**
 * Returns Items Model
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Returns;

use Narvar\ConnectEE\Api\Data\ReturnsItemsInterface;

class Items extends \Magento\Framework\Api\AbstractExtensibleObject implements ReturnsItemsInterface
{

    /**
     * Param Item SKU
     */
    const PARAM_ITEM_SKU = 'item_sku';

    /**
     * Param Qty
     */
    const PARAM_QTY = 'qty';

    /**
     * Param Condition
     */
    const PARAM_CONDITION = 'condition';

    /**
     * Param Reason
     */
    const PARAM_REASON = 'reason';

    /**
     * Param Resolution
     */
    const PARAM_RESOLUTION = 'resolution';

    /**
     * Param Comment
     */
    const PARAM_COMMENT = 'comment';
    
    /**
     *
     * @see \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface::getItemSku()
     */
    public function getItemSku()
    {
        return $this->_get(self::PARAM_ITEM_SKU);
    }
    
    /**
     *
     * @see \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface::setItemSku()
     */
    public function setItemSku($itemSku)
    {
        $this->setData(self::PARAM_ITEM_SKU, $itemSku);
    }
    
    /**
     *
     * @see \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface::getQty()
     */
    public function getQty()
    {
        return $this->_get(self::PARAM_QTY);
    }
    
    /**
     *
     * @see \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface::setQty()
     */
    public function setQty($qty)
    {
        $this->setData(self::PARAM_QTY, $qty);
    }
    
    /**
     *
     * @see \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface::getReason()
     */
    public function getReason()
    {
        return $this->_get(self::PARAM_REASON);
    }
    
    /**
     *
     * @see \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface::setReason()
     */
    public function setReason($reason)
    {
        $this->setData(self::PARAM_REASON, $reason);
    }
    
    /**
     *
     * @see \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface::getCondition()
     */
    public function getCondition()
    {
        return $this->_get(self::PARAM_CONDITION);
    }
    
    /**
     *
     * @see \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface::setCondition()
     */
    public function setCondition($condition)
    {
        $this->setData(self::PARAM_CONDITION, $condition);
    }
    
    /**
     *
     * @see \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface::getResolution()
     */
    public function getResolution()
    {
        return $this->_get(self::PARAM_RESOLUTION);
    }
    
    /**
     *
     * @see \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface::setResolution()
     */
    public function setResolution($resolution)
    {
        $this->setData(self::PARAM_RESOLUTION, $resolution);
    }
    
    /**
     *
     * @see \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface::getComment()
     */
    public function getComment()
    {
        return $this->_get(self::PARAM_COMMENT);
    }
    
    /**
     *
     * @see \Narvar\ConnectEE\Api\Data\ReturnsItemsInterface::setComment()
     */
    public function setComment($comment)
    {
        $this->setData(self::PARAM_COMMENT, $comment);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }
    
    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes($extensionAttributes) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
