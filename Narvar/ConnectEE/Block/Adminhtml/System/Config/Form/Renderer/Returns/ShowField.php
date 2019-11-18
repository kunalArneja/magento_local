<?php
/**
 * Form field block class for return config feolds field in Narvar Connect System configuration page
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Block\Adminhtml\System\Config\Form\Renderer\Returns;

use Narvar\ConnectEE\Helper\Config\Returns;
use Magento\Rma\Helper\Data as RmaHelper;

class ShowField extends \Magento\Config\Block\System\Config\Form\Field
{
    
    /**
     * @var \Magento\Rma\Helper\Data
     */
    private $rmaHelper;
    
    /**
     * Consturctor
     *
     * @param RmaHelper $rmaHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        RmaHelper $rmaHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->rmaHelper = $rmaHelper;
        parent::__construct($context, $data);
    }
    
    /**
     * Method to remove the return config fields based magento edition
     *
     * @see \Magento\Config\Block\System\Config\Form\Field::render()
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $returnEmail = $this->getNarvarConfigFieldPath(Returns::RETURN_ORDER_EMAIL);
        $condition = $this->getNarvarConfigFieldPath(Returns::CONDITION);
        $resolution = $this->getNarvarConfigFieldPath(Returns::RESOLUTION);
        
        if ($this->rmaHelper->isEnabled()) {
            $element->removeField($returnEmail);
            if ($element->getHtmlId() !== $returnEmail) {
                return parent::render($element);
            }
            
            return;
        }
        
        $element->removeField($condition);
        $element->removeField($resolution);
        
        if ($element->getHtmlId() === $returnEmail) {
            return parent::render($element);
        }
    }
        
    /**
     * Method to get the html path of return config fields
     *
     * @param string $field
     * @return string
     */
    private function getNarvarConfigFieldPath($field)
    {
        return sprintf('%s_%s_%s', Returns::CONFIG_SECTION, Returns::CONFIG_GRP, $field);
    }
}
