<?php
/**
 * Narvar Api Response Model
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Service;

use Magento\Framework\Webapi\Response as WebApiResponse;

class Response
{
    /**
     * Messages
     *
     * @var array
     */
    private $messages = [];

    /**
     * Http Response Code
     */
    private $statusCode = 201;

    /**
     * Response Status
     */
    private $status;
    
    /**
     * Add message to response
     *
     * @param string $message
     * @param int $code
     * @param string $field
     * @param string $status
     * @return \Narvar\ConnectEE\Model\Service\Response
     */
    public function addNarvarMessage($message, $code, $status, $field = null)
    {
        $params = [];
        $params['level'] = $status;
        $params['message'] = $message;
        if ($field !== null) {
            $params['field'] = $field;
        }
        
        $this->messages[] = $params;
        $this->setStatusCode($code);
        $this->setStatus($status);
        
        return $this;
    }

    /**
     * Method to add the Success message in respone
     *
     * @param string $message
     * @param int $code
     * @param string $field
     */
    public function addNarvarSuccessMessage($message, $code, $field = null)
    {
        $this->addNarvarMessage($message, $code, WebApiResponse::MESSAGE_TYPE_SUCCESS, $field);
    }

    /**
     * Method to add the Error message in respone
     *
     * @param string $message
     * @param int $code
     * @param string $field
     */
    public function addNarvarErrorMessage($message, $code, $field = null)
    {
        $this->addNarvarMessage($message, $code, WebApiResponse::MESSAGE_TYPE_ERROR, $field);
    }

    /**
     * Return messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Clear messages
     *
     * @return \Narvar\ConnectEE\Model\Service\Response
     */
    public function clearMessages()
    {
        $this->messages = [];
        
        return $this;
    }

    /**
     * Method to set the http status in response
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Method to set the http status code in response
     *
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * Method to get the http status code in response
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Method to get the response
     */
    public function getResponse()
    {
        $response = [
            [
                'status' => $this->status,
                'status_code' => $this->statusCode,
                'messages' => $this->getMessages()
            ]
        ];
        
        return $response;
    }
}
