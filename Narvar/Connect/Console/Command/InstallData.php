<?php

namespace Narvar\Connect\Console\Command;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Narvar\Connect\Model\Audit\Status;
use Narvar\Connect\Model\Audit\StatusFactory;
use Narvar\Connect\Model\Cron\Log as CronLog;
use Narvar\Connect\Model\Cron\LogFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InstallData
 * @package Narvar\Connect\Console\Command
 */
class InstallData extends Command
{
    /**
     * @var StatusFactory
     */
    private $auditStatusFactory;

    /**
     * @var LogFactory
     */
    private $cronLogFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        StatusFactory $auditStatusFactory,
        LogFactory $cronLogFactory,
        DateTime $dateTime,
        $name = null
    ) {
        parent::__construct($name);

        $this->auditStatusFactory = $auditStatusFactory;
        $this->cronLogFactory = $cronLogFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * Configure the current command
     */
    protected function configure()
    {
        $this->setName('narvar:connect:installdata')->setDescription('Install Narvar Connect data');
    }

    /**
     * Execute migration process.
     *
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        foreach ($this->getAuditStatus() as $status) {
            $this->createStatus($status);
        }

        foreach ($this->getCronLogs() as $cronLog) {
            $this->createCronLog($cronLog);
        }
    }

    /**
     * Method to get the Audit Status Model Object
     *
     * @param array $status
     */
    public function createStatus($status)
    {
        $model = $this->auditStatusFactory->create()->load($status[Status::STATUS_LABEL], Status::STATUS_LABEL);
        if(!$model->getId()) {
            try {
                return $model->setData($status)
                    ->save();
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Method to get the Cron Log Model Object
     *
     * @param array $cronLog
     */
    public function createCronLog($cronLog)
    {
        $model = $this->cronLogFactory->create()->load($cronLog[CronLog::JOB_CODE], CronLog::JOB_CODE);
        if(!$model->getId()) {
            try {
                return $model->setData($cronLog)
                    ->save();
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Method to get the Default Audit Log Status Data
     *
     * @return array
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
     * @return array
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
