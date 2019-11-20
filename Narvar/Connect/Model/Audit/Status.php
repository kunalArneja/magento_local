<?php
/**
 * Audit Status Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\Audit;

use Magento\Framework\Model\AbstractModel;
use Narvar\Connect\Helper\Audit\Status as StatusHelper;

class Status extends AbstractModel
{

    /**
     * Constant Value of Table Name
     */
    const TABLE_NAME = 'narvar_audit_status';

    /**
     * Constant value for table field status id
     */
    const STATUS_ID = 'status_id';

    /**
     * Constant value for table field status label
     */
    const STATUS_LABEL = 'status_label';

    /**
     * @var \Narvar\Connect\Helper\Audit\Status
     */
    protected $statusHelper;

    /**
     * Consturctor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param StatusHelper $statusHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        StatusHelper $statusHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->statusHelper = $statusHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Narvar\Connect\Model\ResourceModel\Audit\Status');
    }

    /**
     * Method to get the Non Processed Status List
     *
     * @return array
     */
    public function getNonProcessedStatus()
    {
        return [
            $this->statusHelper->getPending(),
            $this->statusHelper->getProcessing(),
            $this->statusHelper->getOnHold(),
            $this->statusHelper->getFailure()
        ];
    }
}