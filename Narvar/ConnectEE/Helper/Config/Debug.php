<?php
namespace Narvar\ConnectEE\Helper\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Request\Http;

class Debug
{

    /**
     * SCOPE_STORE const
     */
    const SCOPE_STORE = 'store';

    const GENERAL = 'general';

    const LOCALE = 'locale';

    const CODE = 'code';

    const DEBUG_MODE = 'narvar_connectee/log/debug_mode';

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

    public function getDebugMode($storeId = null)
    {
        return $this->scopeConfig->getValue(self::DEBUG_MODE, self::SCOPE_STORE, $storeId);
    }
}
