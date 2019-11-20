<?php
/**
 * Setup Install Schema Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Setup;

use Narvar\Connect\Model\Audit\Status as AuditStatus;
use Narvar\Connect\Model\Audit\Log as AuditLog;
use Narvar\Connect\Model\Cron\Log as CronLog;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * Installs DB schema
     *
     * @see \Magento\Framework\Setup\InstallSchemaInterface::install()
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        
        $auditStatusTableName = $installer->getTable(AuditStatus::TABLE_NAME);
        $auditLogTableName = $installer->getTable(AuditLog::TABLE_NAME);
        $cronLogTableName = $installer->getTable(CronLog::TABLE_NAME);
        
        if ($installer->getConnection()->isTableExists($auditStatusTableName) != true) {
            $auditStatusTable = $installer->getConnection()
                ->newTable($auditStatusTableName)
                ->addColumn(
                    AuditStatus::STATUS_ID,
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Status Id'
                )
                ->addColumn(
                    AuditStatus::STATUS_LABEL,
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false
                    ],
                    'Status Label'
                )
                ->setComment('Narvar Audit Status Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($auditStatusTable);
        }
        
        if ($installer->getConnection()->isTableExists($auditLogTableName) != true) {
            $auditLogTableQuery = "
                CREATE TABLE IF NOT EXISTS {$auditLogTableName} (        
                    `log_id` int(11) unsigned NOT NULL auto_increment,
                	`order_id` int(10) unsigned NOT NULL,
                	`order_inc_id` varchar(50) default NULL,	
                	`action` ENUM('create', 'delete', 'update'),
                	`entity_type` ENUM('order', 'shipment'),
                	`request_time` datetime DEFAULT NULL,
                	`finish_time` datetime DEFAULT NULL,
                	`request_data` longtext DEFAULT NULL,
                	`response` longtext DEFAULT NULL,
                	`status` int(11) unsigned NOT NULL,
                	`slug` varchar(255) default NULL,	
                    PRIMARY KEY (`log_id`),
                	KEY `FK_IDX_ENTITY_ID` (`order_id`),
                	KEY `FK_IDX_INCREMENT_ID` (`order_inc_id`),
                	KEY `FK_IDX_STATUS_ID` (`status`), 
                    CONSTRAINT `FK_IDX_ENTITY_ID` FOREIGN KEY (`order_id`) 
                        REFERENCES {$installer->getTable('sales_order')} (`entity_id`) ON DELETE CASCADE,
                    CONSTRAINT `FK_IDX_INCREMENT_ID` FOREIGN KEY (`order_inc_id`) 
                        REFERENCES {$installer->getTable('sales_order')} (`increment_id`) ON DELETE CASCADE,    
                    CONSTRAINT `FK_IDX_STATUS_ID` FOREIGN KEY (`status`) 
                        REFERENCES {$auditStatusTableName} (`status_id`) ON DELETE CASCADE   
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ";
            $installer->getConnection()->query($auditLogTableQuery);
        }
        
        if ($installer->getConnection()->isTableExists($cronLogTableName) != true) {
            $cronLogTableQuery = "
                CREATE TABLE IF NOT EXISTS {$cronLogTableName} (
                    `log_id` int(11) unsigned NOT NULL auto_increment,
                    `job_code` ENUM('bulk_push', 'audit_clean'),
                    `last_executed_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`log_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                ";
            $installer->getConnection()->query($cronLogTableQuery);
        }
        
        $installer->endSetup();
    }
}
