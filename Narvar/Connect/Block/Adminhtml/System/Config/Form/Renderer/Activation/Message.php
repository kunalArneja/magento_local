<?php
/**
 * Form field block class for activation date in Narvar Connect System configuration page
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Block\Adminhtml\System\Config\Form\Renderer\Activation;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

class Message extends Field
{
    /**
     * Retrieve HTML markup for given form element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '<b>Select a store from the Current Configuration Scope to configure Activation Information Configuration Group</b>';

        return $html;
    }
}
