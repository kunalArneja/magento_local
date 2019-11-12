<?php
/**
 * Narvar Api File uploader Helper
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Helper;

use Narvar\Connect\Exception\ConnectorException;
use Narvar\Connect\Helper\Config\Account as AccountHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Narvar\Connect\Model\Logger\Logger;
use Narvar\Connect\Helper\Config\Debug;

class CurlFileUploader extends \Magento\Framework\DataObject
{

    /**
     * Response Array Key Name Messages
     */
    const MESSAGES = 'messages';

    /**
     * Response Array Key Name Message
     */
    const MSG_MESSAGE = 'message';

    /**
     * Narvar endpoint api Url
     *
     * @var url
     */
    private $narvarApiUrl;

    /**
     * Initialize a cURL session
     *
     * @var
     */
    private $curl;

    /**
     * CURL Connection Time Out
     *
     * @var integer
     */
    private $timeout = 60;

    /**
     * Receive the the Response form API Call
     *
     * @var json
     */
    private $response;

    /**
     * Values of allowed constuctor parameter keys
     *
     * @var array
     */
    private $validParams = [
        'url',
        'username',
        'password',
        'storeId'
    ];
    
    /**
     * @var \Narvar\Connect\Helper\Config\Account as AccountHelper;
     */
    private $accountHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var string
     */
    private $authorization;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Debug
     */
    private $debug;


    private $debugMode;

    /**
     * Constructor
     *
     * @param AccountHelper $accountHelper
     * @param JsonHelper $jsonHelper
     * @param Logger $logger
     * @param Debug $debug
     * @param array $data
     * @throws ConnectorException
     */
    public function __construct(
        AccountHelper $accountHelper,
        JsonHelper $jsonHelper,
        Logger $logger,
        Debug $debug,
        array $data = []
    ) {
        $this->accountHelper = $accountHelper;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->debug = $debug;
        $this->validate($data);
        $this->debugMode($data);
        $this->initCurl();
        $this->setNarvarApiUrl($data);
        $this->setCurlTimeOut();
        $this->setBasicAuthAccount($data);
    }
    
    /**
     * Method to valid given constructor paramter is valid
     *
     * @param array $data
     * @throws ConnectorException
     */
    private function validate($data)
    {
        if (! empty($data)) {
            foreach ($data as $key => $value) {
                if (! in_array($key, $this->validParams)) {
                    throw new ConnectorException(__('Invalid Parameters Passed'));
                }
            }
        }
    }

    /**
     * Init curl
     */
    private function initCurl()
    {
        $this->curl = curl_init();
    }

    /**
     * Set the Narvar End Point API Url
     *
     * @param array $data
     * @return string
     */
    private function setNarvarApiUrl($data)
    {
        if (isset($data['url'])) {
            $this->narvarApiUrl = $data['url'];
        } else {
            $apiEndPointConfigPath = $this->accountHelper->getNarvarApiEndpoint(AccountHelper::CONFIG_REQ_PATH);
            $this->narvarApiUrl = $this->accountHelper->getConfigValue($apiEndPointConfigPath, $data['storeId']);
        }

        return $this->narvarApiUrl;
    }

    /**
     * Set the Time out Configuration for Curl
     */
    private function setCurlTimeOut()
    {
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);
    }


    /**
     * Method to get Value for Debug Mode
     *
     * @param null|array $data
     * @return bool
     */
    protected function debugMode(array $data = null)
    {
        if (!isset($this->debugMode)) {
            if (isset($data['storeId'])) {
                $this->debugMode = $this->debug->getDebugMode($data['storeId']);
            } else {
                $this->debugMode = $this->debug->getDebugMode();
            }
        }

        return $this->debugMode;
    }


    /**
     * Set the Basic Authentication
     *
     * @param array $data
     */
    private function setBasicAuthAccount($data)
    {
        $username = $this->accountHelper->getNarvarAccountId();
        $password = $this->accountHelper->getNarvarAuthToken();

        if (isset($data['storeId'])) {
            $usernameConfigPath= $this->accountHelper->getNarvarAccountId(AccountHelper::CONFIG_REQ_PATH);
            $passwordConfigPath = $this->accountHelper->getNarvarAuthToken(AccountHelper::CONFIG_REQ_PATH);
            $username = $this->accountHelper->getConfigValue($usernameConfigPath, $data['storeId']);
            $password = $this->accountHelper->getConfigValue($passwordConfigPath, $data['storeId']);
        }

        if (isset($data['username']) && isset($data['password'])) {
            $username = $data['username'];
            $password = $data['password'];
        }

        /*Debug*/
        if ($this->debugMode()) {
            $message = __METHOD__ . PHP_EOL;
            $message .= 'Data:' . ' ' . print_r($data, true);
            $message .= 'User:' . ' ' . $username . PHP_EOL;
            $message .= 'Password:' . ' ' . $password . PHP_EOL;
            $this->logger->info($message);
        }

        $this->authorization = base64_encode($username . ':' . $password);
    }

    /**
     * Method to perform Post Request of Narvar API
     *
     * @param string $slug
     * @param array $fileArray
     * @return multitype <string, mixed>
     */
    public function upload($slug, $fileArray)
    {
        $uri = $this->narvarApiUrl . $slug;

        $this->prepareFileToUpload($uri, $fileArray);

        $this->response = curl_exec($this->curl);

        /*Debug*/
        if ($this->debugMode()) {
            $message = __METHOD__ . PHP_EOL;
            $message .= 'Uri:' . ' ' . $uri . PHP_EOL;
            $message .= 'Files:' . ' ' . print_r($fileArray, true);
            $message .= 'Response:' . ' ' . $this->response . PHP_EOL;
            $this->logger->info($message);
        }

        return $this->responseHandling();
    }

    /**
     * Handle the Response and throws exception if any failure met
     * Otherwise return success message
     *
     * @throws Mage_Core_Exception
     * @return string
     */
    private function responseHandling()
    {
        try {
            $responseMessage = $this->parseResponseMessage();
            $responseStatusCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

            curl_close($this->curl);

            if ($responseStatusCode >= 400) {
                throw new ConnectorException(__('%1', $responseMessage));
            }

            return $responseMessage;

        } catch (ConnectorException $e) {
            throw new ConnectorException(__('Call to Narvar API Failed: %1', $responseMessage));
        }
    }

    /**
     * Method to retrive the message to display to user from whole messages
     *
     * @return string
     */
    private function parseResponseMessage()
    {
        $responseMsgArray = $this->jsonHelper->jsonDecode($this->response);

        foreach ($responseMsgArray[self::MESSAGES] as $message) {
            $responseMsg = $message[self::MSG_MESSAGE];
        }

        return $responseMsg;
    }

    /**
     * Prepare content of file for curl upload
     *
     * @param $curl
     * @param $url
     * @param array $files
     * @return mixed
     */
    private function prepareFileToUpload($url, array $files = array())
    {
        static $disallow = array("\0", "\"", "\r", "\n");

        foreach ($files as $key => $filepath) {
            switch (true) {
                case false === $filepath = realpath(filter_var($filepath)):
                case !is_file($filepath):
                case !is_readable($filepath):
                    continue; // or return false, throw new InvalidArgumentException
            }
            $data = file_get_contents($filepath);
            $filePathArray = explode(DIRECTORY_SEPARATOR, $filepath);
            $fileName = end($filePathArray );
            $key = str_replace($disallow, "_", $key);
            $fileName = str_replace($disallow, "_", $fileName);
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$key}\"; filename=\"{$fileName}\"",
                "Content-Type: application/octet-stream",
                "",
                $data,
            ));
        }

        do {
            $boundary = "---------------------" . md5(mt_rand() . microtime());
        } while (preg_grep("/{$boundary}/", $body));

        array_walk($body, function (&$part) use ($boundary) {
            $part = "--{$boundary}\r\n{$part}";
        });

        $body[] = "--{$boundary}--";
        $body[] = "";

        return curl_setopt_array($this->curl, array(
            CURLOPT_URL             => $url,
            CURLOPT_POST            => true,
            CURLINFO_HEADER_OUT     => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_VERBOSE         => true,
            CURLOPT_POSTFIELDS      => implode("\r\n", $body),
            CURLOPT_HTTPHEADER      => array(
                "Expect: 100-continue",
                "Content-Type: multipart/form-data; boundary={$boundary}",
                'accept-encoding: application/gzip',
                "Authorization: Basic $this->authorization"
            ),
        ));
    }
}


