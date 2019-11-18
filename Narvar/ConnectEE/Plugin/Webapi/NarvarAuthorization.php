<?php
/**
 * Narvar Rest Api Authorization Plugin
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Plugin\Webapi;

use Narvar\ConnectEE\Model\Webapi\Authorization\Service as AuthorizationService;

use Magento\Framework\App\RequestInterface;

/**
 * Plugin around \Magento\Framework\Authorization::isAllowed
 *
 * Plugin to allow guest users to access resources with anonymous permission
 */
class NarvarAuthorization
{
    /**
     * @var RequestInterface
     */
    private $request;
    
    /**
     * Initialize dependencies.
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }
    
    /**
     * Check if resource for which access is needed has narvar user permissions defined in webapi config.
     *
     * @param \Magento\Framework\Authorization $subject
     * @param callable $proceed
     * @param string $resource
     * @param string $privilege
     * @return bool true If resource permission is anonymous,
     * to allow any user access without further checks in parent method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsAllowed(
        \Magento\Framework\Authorization $subject,
        \Closure $proceed,
        $resource,
        $privilege = null
    ) {
        if ($resource == AuthorizationService::PERMISSION_NARVAR) {
            return true;
        } else {
            return $proceed($resource, $privilege);
        }
    }
}
