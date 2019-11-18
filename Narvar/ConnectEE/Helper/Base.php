<?php
/**
 * Base Helper
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;

class Base extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Config Section Path Value
     */
    const CONFIG_SECTION = 'narvar_connectee';

    /**
     * Config Group Variable Value
     * All child class will have its own group value
     */
    const CONFIG_GRP_VAR = 'CONFIG_GRP';

    /**
     * Constant value groups
     */
    const GROUPS = 'groups';

    /**
     * Constant value for slug request_path
     */
    const CONFIG_REQ_PATH = 'request_path';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Method to call get the configuration value based method name
     *
     * @param string $method
     * @param array $args
     * @return string
     * @throws LocalizedException
     * @throws \ReflectionException
     */
    public function __call($method, $args)
    {
        try {
            if (substr($method, 0, 3) == 'get') {
                $constants = $this->getConstants();
                $constVariable = strtoupper(preg_replace('/\B([A-Z])/', '_$1', substr($method, 3)));
                if (array_key_exists($constVariable, $constants)) {
                    $configPath = sprintf(
                        '%s/%s/%s',
                        self::CONFIG_SECTION,
                        $constants[self::CONFIG_GRP_VAR],
                        $constants[$constVariable]
                    );
                    
                    return $this->returnConfigValue($configPath, $args);
                }
            }
        } catch (LocalizedException $e) {
            throw new LocalizedException(__('%1', $e->getMessage()));
        }
        
        throw new LocalizedException(__('Method Not Found %1', $method));
    }

    /**
     * Based on request return value as config path or config value
     *
     * @param string $configPath
     * @param array $args
     * @return string
     */
    public function returnConfigValue($configPath, $args)
    {
        if (isset($args[0]) && $args[0] == self::CONFIG_REQ_PATH) {
            return $configPath;
        }

        if (isset($args[0]) && is_numeric($args[0])) {
            return $this->getConfigValue($configPath, $args[0]);
        }

        return $this->getConfigValue($configPath);
    }

    /**
     * Method to get the constants of called and parent class as array
     *
     * @return multitype:
     * @throws \ReflectionException
     */
    public function getConstants()
    {
        $constants = [];
        $currentClass = new \ReflectionClass(__CLASS__);
        $constants = $currentClass->getConstants();

        if (__CLASS__ != get_called_class()) {
            $calledClass = new \ReflectionClass(get_called_class());
            $constants = array_merge($currentClass->getConstants(), $calledClass->getConstants());
        }

        return $constants;
    }

    /**
     * Method to get the configuration value
     *
     * @param string $configPath
     * @param string $scopeCode
     * @return mixed
     */
    public function getConfigValue($configPath, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Method To Get the attributes by data type
     *
     * @param string $path
     * @return array :mixed
     */
    public function getAttributesByDataType($path)
    {
        $attributes = $this->getConfigValue($path);
        $returnAttributes= [];
        foreach ($attributes as $attribute) {
            $returnAttributes[] = $attribute;
        }
    
        return $returnAttributes;
    }
    
    /**
     * Method to get base url of the store
     *
     * @return base url string
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }
}
