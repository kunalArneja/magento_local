<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 16.05.18
 * Time: 11:28
 */

namespace Narvar\Connect\Helper\Config;

use Magento\Backend\Block\Template\Context;
use Narvar\Connect\Helper\Config\Activation as ActivationHelper;
use Magento\Store\Model\ScopeInterface;

class ActivationBlockChecker
{
    /**
     * @var Activation
     */
    private $activationHelper;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    private $context;

    /**
     * IndependentActivationChecker constructor.
     *
     * @param Activation $activationHelper
     */
    public function __construct(ActivationHelper $activationHelper)
    {
        $this->activationHelper = $activationHelper;
    }

    /**
     * Check if module activated taking in account current scope level
     *
     * @return bool
     */
    public function check()
    {
        $activationDateConfigPath = $this->activationHelper->getActivationDate(ActivationHelper::CONFIG_REQ_PATH);
        $request = $this->getContext()->getRequest();
        $scopeConfig = $this->getContext()->getScopeConfig();

        switch ($request)
        {
            case $request->getParam(ScopeInterface::SCOPE_STORE) !== null:
                $activationDate = $scopeConfig->getValue(
                    $activationDateConfigPath,
                    ScopeInterface::SCOPE_STORE,
                    $request->getParam(ScopeInterface::SCOPE_STORE)
                );
                                
                return empty($activationDate);
                break;

            case $request->getParam(ScopeInterface::SCOPE_WEBSITE) !== null:

                $activationDate = $scopeConfig->getValue(
                    $activationDateConfigPath,
                    ScopeInterface::SCOPE_WEBSITE,
                    $request->getParam(ScopeInterface::SCOPE_WEBSITE)
                );

                return empty($activationDate);
                break;

            default:
                $activationDate = $scopeConfig->getValue($activationDateConfigPath);
                
                return empty($activationDate);
                break;
        }
    }

    /**
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

}