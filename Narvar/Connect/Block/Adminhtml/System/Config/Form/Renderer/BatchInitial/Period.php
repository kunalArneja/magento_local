<?php

namespace Narvar\Connect\Block\Adminhtml\System\Config\Form\Renderer\BatchInitial;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Narvar\Connect\Helper\Config\Activation;
use Magento\Store\Model\ScopeInterface;

class Period extends Field
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    /**
     * Retrieve element HTML markup
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $configPath = sprintf('%s/%s/%s', Activation::CONFIG_SECTION, Activation::CONFIG_GRP, Activation::ACTIVATION_DATE);

        if ($storeId = $this->getRequest()->getParam(ScopeInterface::SCOPE_STORE)) {
            $enabled = $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE, $storeId);
        } else if ($websiteId = $this->getRequest()->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            $enabled = $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_WEBSITE, $websiteId);
        } else {
            $enabled = $this->scopeConfig->getValue($configPath);
        }

        if($enabled){
            $element->setReadonly('readonly');
        }

        return $element->getElementHtml();
    }

}
