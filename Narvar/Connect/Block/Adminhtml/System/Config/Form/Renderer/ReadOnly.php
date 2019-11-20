<?php
/**
 * Form field block class for activation field in Narvar Connect System configuration page
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Block\Adminhtml\System\Config\Form\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Narvar\Connect\Helper\Config\ActivationBlockChecker as activationChecker;

class ReadOnly extends Field
{
    /**
     * @var activationChecker
     */
    private $activationChecker;

    /**
     * ReadOnly constructor.
     * @param activationChecker $activationChecker
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        activationChecker $activationChecker,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->activationChecker = $activationChecker;
        $this->activationChecker->setContext($context);
    }

    /**
     * Retrieve element HTML markup
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        if ($this->activationChecker->check()) {
            $element->setReadonly('readonly');
        }

        return $element->getElementHtml();
    }
}
