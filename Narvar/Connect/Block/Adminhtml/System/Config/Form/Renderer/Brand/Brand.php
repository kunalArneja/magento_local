<?php

namespace Narvar\Connect\Block\Adminhtml\System\Config\Form\Renderer\Brand;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

class Brand extends Field
{
    /**
     * @var string
     */
    public $storeCode;

    /**
     * Brand constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Narvar\Connect\Helper\Config\Brand $brand
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Narvar\Connect\Helper\Config\Brand $brand
    ) {
        parent::__construct($context);
        $this->storeCode = $brand->getBrand();
    }

    /**
     * Retrieve element HTML markup
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setReadonly('readonly');
        if(!$this->storeCode){
            $element->setValue('default');
        }else{
            $element->setValue($this->storeCode);
        }

        return $element->getElementHtml();
    }

}
