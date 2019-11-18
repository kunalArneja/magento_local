<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 27.03.18
 * Time: 18:15
 */

namespace Narvar\ConnectEE\Helper\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Request\Http;

/**
 * Class Locale
 * @package Narvar\ConnectEE\Helper\Config
 */
class Locale
{
    /**
     * SCOPE_STORE const
     */
    const SCOPE_STORE = 'store';

    const GENERAL = 'general';

    const LOCALE = 'locale';

    const CODE = 'code';

    /**
     * @var Http
     */
    private $request;

    /**
     * @var mixed
     */
    public $storeId;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Locale constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Http $request
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Http $request
    ) {
        $this->request = $request;
        $this->storeId = $this->request->getParam(self::SCOPE_STORE) ? $this->request->getParam(self::SCOPE_STORE) : null;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string || null
     */
    public function getLocale()
    {
        $locale = null;
        if( !empty($this->storeId) ){
            $locale = $this->getConfigValue( sprintf('%s/%s/%s', self::GENERAL, self::LOCALE, self::CODE), $this->storeId);
        }
        return $locale;
    }

    /**
     * @param $path
     * @param null $scopeCode
     * @return mixed
     */
    private function getConfigValue($path, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            self::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * @param $storeId
     */
    public function setStoreId($storeId)
    {
        if( !$this->storeId ){
            $this->storeId = $storeId;
        }
    }
}