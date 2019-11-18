<?php
/**
 * Handshake Helper
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Helper\Cron;

use Magento\Store\Model\ScopeInterface;
use Narvar\ConnectEE\Helper\Base;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\App\Request\Http as Request;

class HistoricalOrders extends Base
{
    /**
     * Narvar Connect batch_initial Config Group
     */
    const CONFIG_GRP = 'batch_initial';

    /**
     * Constant String batch_initial_period
     */
    const BATCH_INITIAL_PERIOD = 'batch_initial_period';

    const BATCH_INITIAL_DISABLE= 'batch_initial_disable';

    /**
     * Constant String name of narvar_audit_log table
     */
    const NARVAR_AUDIT_LOG = 'narvar_audit_log';

    /**
     * Constant store_id
     */
    const STORE_ID = 'store_id';

    /**
     * Const sales_order
     */
    const SALES_ORDER = 'sales_order';

    /**
     * Const entity_id
     */
    const ENTITY_ID = 'entity_id';

    /**
     * Const order_id
     */
    const ORDER_ID = 'order_id';

    /**
     * Const that define page_size
     */
    const PAGE_SIZE = 1000;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \DateTime
     */
    private $dateTime;

    /**
     * InitialBatch constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Request $request
     * @param \DateTime $dateTime
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        OrderCollectionFactory $orderCollectionFactory,
        Request $request,
        \DateTime $dateTime
    ) {
        $this->resource = $resource;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->request = $request;
        $this->dateTime = $dateTime;
        parent::__construct($context, $storeManager);
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isEmptyNarvarAuditLogTable($storeId)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName(self::NARVAR_AUDIT_LOG);

        $sql = $connection->select()
            ->join( self::SALES_ORDER,
                self::SALES_ORDER . '.' . self::ENTITY_ID . " = " . $tableName . '.' . self::ORDER_ID,
                self::STORE_ID)
            ->from($tableName)
            ->where(self::SALES_ORDER . '.' . self::STORE_ID . " = " . $storeId)
            ->limit(1);

        $result = $connection->fetchAll($sql);

        return empty($result);
    }

    public function getHistoricalOrdersPeriod($storeId)
    {
        $path = sprintf('%s/%s/%s', self::CONFIG_SECTION, self::CONFIG_GRP, self::BATCH_INITIAL_PERIOD);
        $period = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);

        if( is_numeric($period) ){
            return $period;
        }

        return false;
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getIsDisableHistoricalOrders($storeId)
    {
        $path = sprintf('%s/%s/%s', self::CONFIG_SECTION, self::CONFIG_GRP, self::BATCH_INITIAL_DISABLE);
        $disable = (bool)$this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, (int)$storeId);
        return $disable;
    }

    /**
     * @param string $period
     * @param string $storeId
     * @param int|null $page
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection|false $orders
     */
    public function getOrders($period, $storeId, $page = null)
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter(self::STORE_ID, ['eq' => $storeId]);

        if( is_numeric($period) ){
            $startDate = $this->dateTime
                ->setTime(00, 00, 00)
                ->modify("-".$period." days" )
                ->format('Y-m-d H:i:s');
            $collection->addFieldToFilter('created_at', ['gt' => $startDate]);
        }

        if( $page ){
            $collection->setPageSize(self::PAGE_SIZE);
            $lastPageNumber = $collection->getLastPageNumber();

            if( $page > $lastPageNumber ){
                return false;
            }
            $collection->setCurPage($page);
        }

        $orders = $collection->load();

        if(! $orders->getSize() ){
            return false;
        }

        return $orders;
    }

}
