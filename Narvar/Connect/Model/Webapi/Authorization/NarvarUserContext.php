<?php
/**
 * Rest Api Narvar User Context Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\Webapi\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Webapi\Request;
use Magento\Framework\Webapi\Exception as WebApiException;
use Narvar\Connect\Helper\Config\Returns as ReturnHelper;
use Narvar\Connect\Helper\Handshake;
use Narvar\Connect\Helper\Config\Account as AccountConfigHelper;

class NarvarUserContext implements UserContextInterface
{
    /**
     * @var \Magento\Framework\Webapi\Request
     */
    private $request;
    
    /**
     * @var \Narvar\Connect\Helper\Config\Returns
     */
    private $returnHelper;
    
    /**
     * @var int
     */
    private $userId;
    
    /**
     * @var string
     */
    private $userType;
    /**
     * @var AccountConfigHelper
     */
    private $accountConfigHelper;

    /**
     * Constructor
     *
     * @param Request $request
     * @param ReturnHelper $returnHelper
     * @param AccountConfigHelper $accountlConfigHelper
     */
    public function __construct(
        Request $request,
        ReturnHelper $returnHelper,
        AccountConfigHelper $accountConfigHelper
    ) {
        $this->request = $request;
        $this->returnHelper = $returnHelper;
        $this->accountConfigHelper = $accountConfigHelper;
    }
    
    /**
     *
     * @see \Magento\Authorization\Model\UserContextInterface::getUserId()
     */
    public function getUserId()
    {
        $this->processRequest();
        return $this->userId;
    }
    
    /**
     *
     * @see \Magento\Authorization\Model\UserContextInterface::getUserType()
     */
    public function getUserType()
    {
        $this->processRequest();
        return $this->userType;
    }
    
    /**
     * Method to process the user validation and set user group
     */
    private function processRequest()
    {
        $this->userType = null;
        
        if ($this->isValidUser()) {
            $this->userId = 0;
            $this->userType = UserContextInterface::USER_TYPE_INTEGRATION;
        }
    }
    
    /**
     * Method to verify the valid user or not
     *
     * @throws WebApiException
     * @return boolean
     */
    private function isValidUser()
    {
        if ($this->request->getPathInfo() === Handshake::RETURN_SLUG) {
            if ($this->request->getHeader('AUTHORIZATION') &&
                $this->getBasicAuth() === $this->request->getHeader('AUTHORIZATION')) {

                return true;
            }
            
            throw new WebApiException(
                __('Bad Credentials, Please provide valid auth key and auth token'),
                WebApiException::HTTP_FORBIDDEN,
                WebApiException::HTTP_FORBIDDEN
            );
        }
        
        return false;
    }

    /**
     * @param $storeId
     * @return string
     * @throws WebApiException
     */
    private function getBasicAuth()
    {
        $user = $this->returnHelper->getAuthKey();
        $pass = $this->returnHelper->getAuthToken();

        //In order to avoid security issues we need to check whether settings are not empty
        if (empty($user) || empty($pass)) {
            throw new WebApiException(
                __('Module is not activated'),
                WebApiException::HTTP_FORBIDDEN,
                WebApiException::HTTP_FORBIDDEN
            );
        }

        return 'Basic ' . base64_encode(sprintf('%s:%s', $user, $pass));
    }
}
