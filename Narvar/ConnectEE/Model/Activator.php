<?php
/**
 * Narvar Activator Model
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model;

use Narvar\ConnectEE\Helper\Handshake as HandshakeHelper;
use Narvar\ConnectEE\Helper\Config\Account as AccountHelper;
use Narvar\ConnectEE\Helper\Config\Returns as ReturnsHelper;
use Narvar\ConnectEE\Helper\Config\Activation as ActivationHelper;
use Narvar\ConnectEE\Helper\Config\Labels as LabelsHelper;
use Narvar\ConnectEE\Helper\Config\Batch as BatchHelper;
use Narvar\ConnectEE\Helper\Base as ExtensionHelper;
use Narvar\ConnectEE\Helper\Formatter;
use Narvar\ConnectEE\Helper\Data as DataHelper;
use Narvar\ConnectEE\Helper\ConnectorFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Narvar\ConnectEE\Helper\Config\Brand;
use Narvar\ConnectEE\Helper\Config\Locale;
use Narvar\ConnectEE\Helper\Cron\Log as LogHelper;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Phrase;
use Magento\Rma\Helper\Data as RmaHelper;
use Magento\Framework\App\ResourceConnection;


class Activator
{

    /**
     * Constant String value
     */
    const VALUE = 'value';

    /**
     * Constant String fields
     */
    const FIELDS = 'fields';

    /**
     * Constant String inherit
     */
    const INHERIT = 'inherit';

    /**
     * @var string
     */
    private $configScope;

    /**
     * @var null || int
     */
    private $configScopeId = null;

    /**
     * @var \Narvar\ConnectEE\Helper\Base as ExtensionHelper;
     */
    private $extensionHelper;

    /**
     * @var \Narvar\ConnectEE\Helper\Config\Activation as ActivationHelper;
     */
    private $activationHelper;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var \Narvar\ConnectEE\Helper\Connector
     */
    private $connector;

    /**
     * @var \Narvar\ConnectEE\Helper\Formatter;
     */
    private $formatter;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    private $cacheFrontendPool;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetaData;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Brand
     */
    private $brand;

    /**
     * @var Locale
     */
    private $locale;

    /**
     * @var LogHelper
     */
    private $logHelper;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var array $stores
     */
    private $stores;

    /**
     * @var array ReturnsHelper
     */
    private $returnsHelper;

    /**
     * @var BatchHelper
     */
    private $batchHelper;

    /**
     * @var AccountHelper
     */
    private $accountHelper;

    /**
     * @var array $handshakeParams
     */
    private $handshakeParams;

    /**
     * @var array $configGroups
     */
    private $configGroups;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var RmaHelper
     */
    private $rmaHelper;

    /**
     * Constructor
     *
     * @param ExtensionHelper $extensionHelper
     * @param EncryptorInterface $encryptor
     * @param ConnectorFactory $connector
     * @param ActivationHelper $activationHelper
     * @param Formatter $formatter
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     * @param ManagerInterface $messageManager
     * @param JsonHelper $jsonHelper
     * @param ProductMetadataInterface $productMetaData
     * @param ScopeConfigInterface $scopeConfig
     * @param Brand $brand
     * @param Locale $locale
     * @param LogHelper $logHelper
     * @param StoreManager $storeManager
     * @param ReturnsHelper $returns
     * @param BatchHelper $batch
     * @param RmaHelper $rmaHelper
     * @param AccountHelper $account
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ExtensionHelper $extensionHelper,
        EncryptorInterface $encryptor,
        ConnectorFactory $connector,
        ActivationHelper $activationHelper,
        Formatter $formatter,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        ManagerInterface $messageManager,
        JsonHelper $jsonHelper,
        ProductMetadataInterface $productMetaData,
        ScopeConfigInterface $scopeConfig,
        Brand $brand,
        Locale $locale,
        LogHelper $logHelper,
        StoreManager $storeManager,
        ReturnsHelper $returns,
        BatchHelper $batch,
        RmaHelper $rmaHelper,
        AccountHelper $account,
        ResourceConnection $resourceConnection
    ) {
        $this->encryptor = $encryptor;
        $this->extensionHelper = $extensionHelper;
        $this->activationHelper = $activationHelper;
        $this->connector = $connector;
        $this->formatter = $formatter;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->messageManager = $messageManager;
        $this->jsonHelper = $jsonHelper;
        $this->productMetaData = $productMetaData;
        $this->scopeConfig = $scopeConfig;
        $this->brand = $brand;
        $this->locale = $locale;
        $this->logHelper = $logHelper;
        $this->storeManager = $storeManager;
        $this->returnsHelper = $returns;
        $this->batchHelper = $batch;
        $this->rmaHelper = $rmaHelper;
        $this->returnsHelper = $returns;
        $this->batchHelper = $batch;
        $this->accountHelper = $account;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Method fire when the configuration updated
     *
     * @param \Magento\Config\Model\Config $config
     * @return mixed $this->configGroups
     * @throws ValidatorException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function activationProcess($config)
    {
        if ($config->getSection() === HandshakeHelper::CONFIG_SECTION) {
            $this->configGroups = $config->getGroups();
            $responseMsgs = [];

            if ($config->getStore()) {
                $this->configScope = ScopeInterface::SCOPE_STORE;
                $this->configScopeId = $config->getStore();
                $this->requiredFieldChecker();
                $this->handshakeParams = $this->getHandshakeParams();

                $responseMsgs[] = $this->handshakeRequest($config->getStore());
            } else if ($config->getWebsite()) {
                $webSiteObj = $this->storeManager->getWebsite($config->getWebsite());
                $this->configScope = ScopeInterface::SCOPE_WEBSITE;
                $this->configScopeId = $config->getWebsite();
                $this->requiredFieldChecker();
                $this->handshakeParams = $this->getHandshakeParams();

                $this->stores = $webSiteObj->getStores();

                foreach ($this->stores as $store) {
                    $responseMsgs[] = $this->handshakeRequest($store->getId());
                }
            } else {
                $this->stores = $this->storeManager->getStores();
                $this->configScope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                $this->requiredFieldChecker();
                $this->handshakeParams = $this->getHandshakeParams();

                foreach ($this->stores as $store) {
                    $responseMsgs[] = $this->handshakeRequest($store->getId());
                }
            }

            foreach ($responseMsgs as $responseMsg) {
                $this->messageManager->addSuccess(nl2br($responseMsg));
            }

            $this->enableModule($config);

            return $this->configGroups;
        }
    }

    /**
     * Method to enable the module in configuration
     *
     * @param \Magento\Config\Model\Config $config
     */
    private function enableModule($config)
    {
        $returnGroup = $this->configGroups[ReturnsHelper::CONFIG_GRP][self::FIELDS];

        if (isset($returnGroup[ReturnsHelper::AUTH_KEY][self::INHERIT])) {
            $returnGroup[ReturnsHelper::AUTH_KEY_ENCRYPT][self::INHERIT] = 1;
        } else {
            $returnGroup[ReturnsHelper::AUTH_KEY_ENCRYPT][self::VALUE] = $this->handshakeParams[HandshakeHelper::AUTH_KEY];
        }

        if (! isset($returnGroup[ReturnsHelper::AUTH_TOKEN][self::INHERIT])) {
            unset($returnGroup[ReturnsHelper::AUTH_TOKEN][self::INHERIT]);
            $returnGroup[ReturnsHelper::AUTH_TOKEN][self::VALUE] = $this->handshakeParams[HandshakeHelper::AUTH_TOKEN];
        }
        $activationDateConfigPath = sprintf('%s/%s/%s', ActivationHelper::CONFIG_SECTION, ActivationHelper::CONFIG_GRP, ActivationHelper::ACTIVATION_DATE);

        if (! $this->getConfigValue($activationDateConfigPath, $this->configScopeId, $this->configScope)) {
            $currentDate = $this->formatter->currentDate();
            $activationDate[ActivationHelper::ACTIVATION_DATE][self::VALUE] = $currentDate;
            $activationStatus[ActivationHelper::IS_ACTIVATED][self::VALUE] = DataHelper::ENABLE_VALUE;

            $this->configGroups[ActivationHelper::CONFIG_GRP][self::FIELDS] = array_merge($activationDate, $activationStatus);

            if (empty($this->stores)) {
                $this->logHelper->initiate($config->getStore(), $currentDate);
            } else {
                foreach ($this->stores as $store) {
                    $this->logHelper->initiate($store->getId(), $currentDate);
                }
            }
        }

        $accountConfig = $this->configGroups[AccountHelper::CONFIG_GRP][self::FIELDS];
        $apiEndpoint  = isset($accountConfig[AccountHelper::NARVAR_API_ENDPOINT][self::VALUE])
            ? $accountConfig[AccountHelper::NARVAR_API_ENDPOINT][self::VALUE]
            : $this->getConfigValue(
                sprintf('%s/%s/%s', HandshakeHelper::CONFIG_SECTION, AccountHelper::CONFIG_GRP, AccountHelper::NARVAR_API_ENDPOINT),
                $config->getStore()
            );
        $this->configGroups[AccountHelper::CONFIG_GRP][self::FIELDS][AccountHelper::NARVAR_API_ENDPOINT][self::VALUE] = trim($apiEndpoint,'/') . '/';
        $this->configGroups[ReturnsHelper::CONFIG_GRP][self::FIELDS] = $returnGroup;

        $this->resetCache();

        $config->setGroups($this->configGroups);
    }

    /**
     * Method to get Narvar Account New Params
     *
     * @param int $storeId
     * @return array
     */
    private function getAccountValidationParams($storeId)
    {
        $accountConfig = $this->configGroups[AccountHelper::CONFIG_GRP][self::FIELDS];
        $url = isset($accountConfig[AccountHelper::NARVAR_API_ENDPOINT][self::VALUE])
            ? $accountConfig[AccountHelper::NARVAR_API_ENDPOINT][self::VALUE]
            : $this->getConfigValue(
                sprintf('%s/%s/%s', HandshakeHelper::CONFIG_SECTION, AccountHelper::CONFIG_GRP, AccountHelper::NARVAR_API_ENDPOINT),
                $storeId
            );

        $username = isset($accountConfig[AccountHelper::NARVAR_ACCOUNT_ID][self::VALUE])
            ? $accountConfig[AccountHelper::NARVAR_ACCOUNT_ID][self::VALUE]
            : $this->getConfigValue(
                sprintf('%s/%s/%s', AccountHelper::CONFIG_SECTION, AccountHelper::CONFIG_GRP, AccountHelper::NARVAR_ACCOUNT_ID),
                $storeId
            );
        $password = isset($accountConfig[AccountHelper::NARVAR_AUTH_TOKEN][self::VALUE])
            ? $accountConfig[AccountHelper::NARVAR_AUTH_TOKEN][self::VALUE]
            : $this->getConfigValue(
                sprintf('%s/%s/%s', AccountHelper::CONFIG_SECTION, AccountHelper::CONFIG_GRP, AccountHelper::NARVAR_AUTH_TOKEN),
                $storeId
            );

        return [
            'url' => $url,
            'username' => $username,
            'password' => $password
        ];
    }

    /**
     * Get the handshake params
     *
     * @return array of return configuration params
     * @throws ValidatorException
     */
    private function getHandshakeParams()
    {
        $returnConfig = $this->configGroups[ReturnsHelper::CONFIG_GRP][self::FIELDS];
        $version = sprintf('%s-%s', $this->productMetaData->getVersion(), $this->productMetaData->getEdition());
        $baseUrl = $this->extensionHelper->getBaseUrl();
        $returnUrl = sprintf('%s%s%s', $baseUrl, HandshakeHelper::RETURN_SLUG_REST, HandshakeHelper::RETURN_SLUG);
        $authKeyEncrypt = $this->getConfigValue($this->returnsHelper->getAuthKeyEncrypt(ReturnsHelper::CONFIG_REQ_PATH), $this->configScopeId, $this->configScope);

        if (isset($returnConfig[ReturnsHelper::AUTH_KEY][self::INHERIT])) {
            $authKeyEncrypt = $this->canInheritConfig($this->returnsHelper->getAuthKeyEncrypt(ReturnsHelper::CONFIG_REQ_PATH));
        } else {
            $authKey = $this->getConfigValue(
                $this->returnsHelper->getAuthKey(ReturnsHelper::CONFIG_REQ_PATH),
                $this->configScopeId,
                $this->configScope
            );

            if (empty($authKeyEncrypt) || $authKey != $returnConfig[ReturnsHelper::AUTH_KEY][self::VALUE]) {
                $authKeyEncrypt = $this->encryptor->encrypt($returnConfig[ReturnsHelper::AUTH_KEY][self::VALUE]);
            }
        }

        if (isset($returnConfig[ReturnsHelper::AUTH_TOKEN][self::INHERIT])) {
            $authToken = $this->canInheritConfig(
                $this->returnsHelper->getAuthToken(ReturnsHelper::CONFIG_REQ_PATH),
                LabelsHelper::RETURN_AUTH_TOKEN,
                false
            );

            if (empty($authToken)) {
                unset($returnConfig[ReturnsHelper::AUTH_TOKEN][self::INHERIT]);
                $this->configGroups[ReturnsHelper::CONFIG_GRP][self::FIELDS] = $returnConfig;
            }
        }

        if (empty($authToken)) {
            $authToken = $this->generateAuthToken();
        }

        return [
            HandshakeHelper::VERSION => $version,
            HandshakeHelper::BASE_URL => $this->extensionHelper->getBaseUrl(),
            HandshakeHelper::RETURN_REQ_URL => $returnUrl,
            HandshakeHelper::AUTH_KEY => str_replace(':', '', $authKeyEncrypt),
            HandshakeHelper::AUTH_TOKEN => $authToken
        ];
    }

    /**
     * Method to generate the Auth Token for access Magento return request
     *
     * @return string
     */
    private function generateAuthToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(48));
    }

    /**
     * Method to get config value
     *
     * @param $path
     * @param null $scopeId
     * @param string $scopeType
     * @return mixed
     */
    private function getConfigValue($path, $scopeId = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            $path,
            $scopeType,
            $scopeId
        );
    }

    /**
     * Gather data for handshake and make API request
     *
     * @param null $storeId
     * @return mixed
     */
    private function handshakeRequest($storeId)
    {
        $storeIdentity = $this->identifyStore($storeId);
        $storeTitle = $this->storeManager->getStore($storeId)->getName();

        if ($this->canProcess($storeId)) {
            $handshakeParams = array_merge($this->handshakeParams, $storeIdentity);
            $validationParams = $this->getAccountValidationParams($storeId);
            $responseMsg = $this->connector->create(['data' => $validationParams])->post(
                HandshakeHelper::SLUG,
                $this->jsonHelper->jsonEncode($handshakeParams)
            );
        } else {
            $responseMsg = ActivationHelper::ALREADY_REGISTERED;
        }

        return $responseMsg . ' for ' . $storeTitle . ' store ' . '('. $storeIdentity[HandshakeHelper::BRAND] .')';
    }

    /**
     * Get Brand and Locale code of store
     *
     * @param $storeId
     * @return array
     */
    public function identifyStore($storeId)
    {
        $this->brand->setStoreId($storeId);
        $this->locale->setStoreId($storeId);

        $brand = $this->brand->getBrand();
        $locale = $this->locale->getLocale();

        return [
            HandshakeHelper::BRAND => $brand,
            HandshakeHelper::LOCALE => $locale
        ];
    }

    /**
     * Check which of required Narvar module configs fields is not set
     *
     * @throws ValidatorException
     */
    private function requiredFieldChecker()
    {
        $label = false;
        $accountConfig = $this->configGroups[AccountHelper::CONFIG_GRP][self::FIELDS];
        $batchConfig = $this->configGroups[BatchHelper::CONFIG_GRP][self::FIELDS];
        $returnConfig = $this->configGroups[ReturnsHelper::CONFIG_GRP][self::FIELDS];

        $apiEndPoint = isset($accountConfig[AccountHelper::NARVAR_API_ENDPOINT][self::VALUE])
            ? $accountConfig[AccountHelper::NARVAR_API_ENDPOINT][self::VALUE]
            : $this->getConfigValue(
                sprintf('%s/%s/%s', HandshakeHelper::CONFIG_SECTION, AccountHelper::CONFIG_GRP, AccountHelper::NARVAR_API_ENDPOINT),
                $this->configScopeId,
                $this->configScope
            );

        if (empty($apiEndPoint)) {
            $label = LabelsHelper::NARVAR_API_ENDPOINT;
        }

        $accountId = isset($accountConfig[AccountHelper::NARVAR_ACCOUNT_ID][self::VALUE])
            ? $accountConfig[AccountHelper::NARVAR_ACCOUNT_ID][self::VALUE]
            : $this->getConfigValue(
                sprintf('%s/%s/%s', HandshakeHelper::CONFIG_SECTION, AccountHelper::CONFIG_GRP, AccountHelper::NARVAR_ACCOUNT_ID),
                $this->configScopeId,
                $this->configScope
            );

        if (empty($accountId)) {
            $label = LabelsHelper::NARVAR_ACCOUNT_ID;
        }

        $authToken = isset($accountConfig[AccountHelper::NARVAR_AUTH_TOKEN][self::VALUE])
            ? $accountConfig[AccountHelper::NARVAR_AUTH_TOKEN][self::VALUE]
            : $this->getConfigValue(
                sprintf('%s/%s/%s', HandshakeHelper::CONFIG_SECTION, AccountHelper::CONFIG_GRP, AccountHelper::NARVAR_AUTH_TOKEN),
                $this->configScopeId,
                $this->configScope
            );

        if (empty($authToken)) {
            $label = LabelsHelper::NARVAR_AUTH_TOKEN;
        }

        $returnAuthKey = isset($returnConfig[ReturnsHelper::AUTH_KEY][self::VALUE])
            ? $returnConfig[ReturnsHelper::AUTH_KEY][self::VALUE]
            : $this->getConfigValue(
                $this->returnsHelper->getAuthKey(ReturnsHelper::CONFIG_REQ_PATH),
                $this->configScopeId,
                $this->configScope
            );

        if (empty($returnAuthKey)) {
            $label = LabelsHelper::RETURN_AUTH_KEY;
        }

        $freqBulkPush = isset($batchConfig[BatchHelper::BATCH_BULK_PUSH_FREQ][self::VALUE])
            ? $batchConfig[BatchHelper::BATCH_BULK_PUSH_FREQ][self::VALUE]
            : $this->getConfigValue($this->batchHelper->getBatchBulkPushFreq(BatchHelper::CONFIG_REQ_PATH),
                $this->configScopeId,
                $this->configScope
            );

        if (empty($freqBulkPush)) {
            $label = LabelsHelper::FREQ_BULK_FAILURE_PUSH;
        }

        $firstBatchProcess = isset($batchConfig[BatchHelper::BATCH_PUSH_TIME][self::VALUE])
            ? $batchConfig[BatchHelper::BATCH_PUSH_TIME][self::VALUE]
            : $this->getConfigValue($this->batchHelper->getBatchPushTimeByStore(BatchHelper::CONFIG_REQ_PATH),
                $this->configScopeId,
                $this->configScope
            );

        if ($firstBatchProcess === null) {
            $label = LabelsHelper::FIRST_TIME_BATCH_PROCESS;
        }

        $auditCleanInterval = isset($batchConfig[BatchHelper::BATCH_BULK_PUSH_FREQ][self::VALUE])
            ? $batchConfig[BatchHelper::BATCH_BULK_PUSH_FREQ][self::VALUE]
            : $this->getConfigValue($this->batchHelper->getBatchBulkPushFreqByStore(BatchHelper::CONFIG_REQ_PATH),
                $this->configScopeId,
                $this->configScope
            );

        if (empty($auditCleanInterval)) {
            $label = LabelsHelper::AUDIT_CLEAN_INTERVAL;
        }

        if ($this->rmaHelper->isEnabled()) {
            $conditionReturn = isset($returnConfig[ReturnsHelper::CONDITION][self::VALUE])
                ? $returnConfig[ReturnsHelper::CONDITION][self::VALUE]
                : $this->getConfigValue(
                    $this->returnsHelper->getCondition(ReturnsHelper::CONFIG_REQ_PATH),
                    $this->configScopeId,
                    $this->configScope
                );

            if (empty($conditionReturn)) {
                $label = LabelsHelper::RETURN_CONDITION;
            }

            $resolutionReturn = isset($returnConfig[ReturnsHelper::RESOLUTION][self::VALUE])
                ? $returnConfig[ReturnsHelper::RESOLUTION][self::VALUE]
                : $this->getConfigValue(
                    $this->returnsHelper->getResolution(ReturnsHelper::CONFIG_REQ_PATH),
                    $this->configScopeId,
                    $this->configScope
                );

            if (empty($resolutionReturn)) {
                $label = LabelsHelper::RETURN_RESOLUTION;
            }
        } else {
            $emailReturn = isset($returnConfig[ReturnsHelper::RETURN_ORDER_EMAIL][self::VALUE])
                ? $returnConfig[ReturnsHelper::RETURN_ORDER_EMAIL][self::VALUE]
                : $this->getConfigValue(
                    $this->returnsHelper->getReturnOrderEmail(ReturnsHelper::CONFIG_REQ_PATH),
                    $this->configScopeId,
                    $this->configScope
                );

            if (empty($emailReturn)) {
                $label = LabelsHelper::EMAIL_RETURN_REQUEST;
            }
        }

        if ($label) {
            $phrase = new Phrase(__("'%1' field cannot be empty.", $label));
            throw new ValidatorException($phrase);
        }
    }

    /**
     * Method to reset config cache
     */
    public function resetCache()
    {
        $this->cacheTypeList->cleanType('config');
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

    /**
     * Method to check if possible to inherit configs
     *
     * @param $configPath
     * @param string $label
     * @param bool $throwException
     * @return string|null
     * @throws ValidatorException
     */
    private function canInheritConfig($configPath, $label = '', $throwException = true)
    {
        if ($this->configScope === ScopeInterface::SCOPE_STORE) {
            $websiteId = $this->storeManager->getStore($this->configScopeId)->getWebsiteId();
            $config = $this->getConfigValue($configPath, $websiteId, ScopeInterface::SCOPE_WEBSITE);
        } else {
            $config = $this->scopeConfig->getValue($configPath);
        }

        if (isset($config)) {
            return $config;
        }

        if ($throwException) {
            $phrase = new Phrase(__("'%1' field cannot be empty.", $label));
            throw new ValidatorException($phrase);
        }

        return null;
    }

    /**
     * Method to check should we sent a request
     *
     * @param $storeId
     * @return bool
     */
    private function canProcess($storeId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('core_config_data');
        $path = $this->activationHelper->getActivationDate(ActivationHelper::CONFIG_REQ_PATH);

        if ($this->configScope === ScopeInterface::SCOPE_STORE) {
            return true;
        } else {
            $websiteActivated = false;

            if ($this->configScope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
                $sql = $connection->select()
                    ->from($tableName)
                    ->where("path = '$path'")
                    ->where("scope = '" . ScopeInterface::SCOPE_WEBSITES . "'")
                    ->where("scope_id = $websiteId");

                $websiteActivated = $connection->fetchAll($sql);
            }

            $sql = $connection->select()
                ->from($tableName)
                ->where("path = '$path'")
                ->where("scope = '" . ScopeInterface::SCOPE_STORES . "'")
                ->where("scope_id = $storeId");

            $storeActivated = $connection->fetchAll($sql);

            if (empty($websiteActivated) && empty($storeActivated)) {
                return true;
            }
        }
        return false;
    }

}