<?php
/**
 * Audit Status Helper
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Below methods will used to get status id value
 *
 * @method string getPending()
 * @method string getProcessing()
 * @method string getOnHold()
 * @method string getFailure()
 * @method string getSuccess()
 * @method string getBulk()
 */
namespace Narvar\Connect\Helper\Audit;

use Magento\Framework\App\Helper\Context;
use Narvar\Connect\Model\Audit\Status as AuditStatus;
use Narvar\Connect\Model\Audit\StatusFactory;
use Magento\Framework\Exception\LocalizedException;

class Status extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Constant value for status label pending
     */
    const PENDING = 'pending';

    /**
     * Constant value for status label process
     */
    const PROCESSING = 'processing';

    /**
     * Constant value for status label on-hold
     */
    const ON_HOLD = 'onhold';

    /**
     * Constant value for status label failure
     */
    const FAILURE = 'failure';

    /**
     * Constant value for status label success
     */
    const SUCCESS = 'success';

    /**
     * Constant value for status label bulk
     */
    const BULK = 'bulk';

    /**
     *
     * @var \Narvar\Connect\Model\Audit\StatusFactory
     */
    private $auditStatusFactory;

    /**
     * Constuctor
     *
     * @param Context $context
     * @param StatusFactory $auditStatusFactory
     */
    public function __construct(
        Context $context,
        StatusFactory $auditStatusFactory
    ) {
        $this->auditStatusFactory = $auditStatusFactory;
        parent::__construct($context);
    }

    /**
     * Method to get the audit log status Id value based method name
     *
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        if (substr($method, 0, 3) == 'get') {
            $currentClass = new \ReflectionClass(__CLASS__);
            $constants = $currentClass->getConstants();
            $constantVar = strtoupper(preg_replace('/\B([A-Z])/', '_$1', substr($method, 3)));
            if (array_key_exists($constantVar, $constants)) {
                return $this->getStatusId($constants[$constantVar]);
            }
        }
        
        throw new LocalizedException(__('Method Not Found %1', $method));
    }

    /**
     * Method to get the Status Id By using Status Label
     *
     * @param string $statusLabel
     * @return int
     */
    public function getStatusId($statusLabel)
    {
        return $this->auditStatusFactory->create()
            ->load($statusLabel, AuditStatus::STATUS_LABEL)
            ->getId();
    }
}
