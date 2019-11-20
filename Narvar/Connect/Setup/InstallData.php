<?php
/**
 * Setup Install Data Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Setup;

use Narvar\Connect\Model\Audit\Status;
use Narvar\Connect\Model\Audit\StatusFactory;
use Narvar\Connect\Model\Cron\Log as CronLog;
use Narvar\Connect\Model\Cron\LogFactory;
use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class InstallData implements InstallDataInterface
{

    /**
     *
     * @var StatusFactory
     */
    private $auditStatusFactory;

    /**
     *
     * @var LogFactory
     */
    private $cronLogFactory;

    /**
     *
     * @var DateTime;
     */
    protected $dateTime;

    /**
     * Init
     *
     * @param StatusFactory $auditStatusFactory
     * @param LogFactory $cronLogFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        StatusFactory $auditStatusFactory,
        LogFactory $cronLogFactory,
        DateTime $dateTime
    ) {
        $this->auditStatusFactory = $auditStatusFactory;
        $this->cronLogFactory = $cronLogFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * Installs data for a module
     *
     * @see \Magento\Framework\Setup\InstallDataInterface::install()
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        
        $installer->startSetup();
        
        foreach ($this->getAuditStatus() as $status) {
            $this->createStatus($status);
        }
        
        foreach ($this->getCronLogs() as $cronLog) {
            $this->createCronLog($cronLog);
        }
        
        $installer->endSetup();
    }

    /**
     * Method to get the Audit Status Model Object
     *
     * @param array $status
     */
    public function createStatus($status)
    {
        return $this->auditStatusFactory->create()
            ->setData($status)
            ->save();
    }

    /**
     * Method to get the Cron Log Model Object
     *
     * @param array $cronLog
     */
    public function createCronLog($cronLog)
    {
        return $this->cronLogFactory->create()
            ->setData($cronLog)
            ->save();
    }

    /**
     * Method to get the Default Audit Log Status Data
     *
     * @return multitype:multitype:string
     */
    private function getAuditStatus()
    {
        return [
            [
                Status::STATUS_LABEL => 'pending'
            ],
            [
                Status::STATUS_LABEL => 'processing'
            ],
            [
                Status::STATUS_LABEL => 'onhold'
            ],
            [
                Status::STATUS_LABEL => 'failure'
            ],
            [
                Status::STATUS_LABEL => 'success'
            ],
            [
                Status::STATUS_LABEL => 'bulk'
            ]
        ];
    }

    /**
     * Method to get the default CronLog Data
     *
     * @return multitype:multitype:string NULL
     */
    private function getCronLogs()
    {
        return [
            [
                CronLog::JOB_CODE => CronLog::BULK_PUSH,
                CronLog::LAST_EXECUTED_AT => $this->dateTime->date()
            ],
            [
                CronLog::JOB_CODE => CronLog::AUDIT_CLEAN,
                CronLog::LAST_EXECUTED_AT => $this->dateTime->date()
            ]
        ];
    }
}
