<?php
/**
 * Admin Audit Log Process Controller
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Controller\Adminhtml\Audit\Log;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Narvar\ConnectEE\Model\UploaderFactory;

class Process extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Narvar_ConnectEE::audit_log_process';
    
    /**
     *
     * @var \Narvar\ConnectEE\Model\UploaderFactory
     */
    private $uploader;
    
    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
        UploaderFactory $uploader
    ) {
        parent::__construct($context);
        $this->uploader = $uploader;
    }
    
    /**
     * Process the failure logs
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        try {
            $this->uploader->create()->processByStore();
            $this->messageManager->addSuccess('Successfully processed the failure records');
        } catch (LocalizedException $e) {
            $this->messageManager->addError(__('Unable to process some data: %1', $e->getMessage()));
        }
        
        return $this->resultRedirectFactory->create()->setPath('narvar_connectee/*/');
    }
}
