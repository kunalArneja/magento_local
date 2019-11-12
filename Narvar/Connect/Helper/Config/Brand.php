<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 27.03.18
 * Time: 18:15
 */

namespace Narvar\Connect\Helper\Config;

use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Request\Http;

class Brand
{
    /**
     * Key name of
     */
    const STORE = 'store';

    /**
     * @var Http
     */
    private $request;

    /**
     * @var mixed
     */
    public $storeId;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Brand constructor.
     * @param StoreManagerInterface $storeManager
     * @param Http $request
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Http $request
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->storeId = $this->request->getParam(self::STORE);
    }

    /**
     * @return string || null
     */
    public function getBrand()
    {
        $brand = null;
        if( !empty($this->storeId) ){
            $brand = $this->storeManager->getStore($this->storeId)->getCode();
        }
        return $brand;
    }

    /**
     * @param $storeId
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }
}