<?php

namespace Narvar\Connect\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Narvar\Connect\Model\Audit\Log as AuditLog;

class UpgradeSchema implements UpgradeSchemaInterface
{

    const CORE_CONFIG_DATA = 'core_config_data';

    const PATH = 'path';

    const PATH_VALUE = 'narvar_connect';

    const NARVAR_CRON_LOG = 'narvar_cron_log';

    /**
     * Upgrade DB schema
     *
     * @see \Magento\Framework\Setup\InstallSchemaInterface::upgrade()
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $narvarAuditLogTable = $setup->getTable(AuditLog::TABLE_NAME);
        $narvarCrontLogTable = $setup->getTable(self::NARVAR_CRON_LOG);
        $coreConfigDataTable = $setup->getTable(self::CORE_CONFIG_DATA);

        if (version_compare($context->getVersion(), '0.1.2', '<')) {

            $connection = $setup->getConnection();

            /**
             * Unset configs of Narvar module from core_config_data
             */
            $connection->delete($coreConfigDataTable, self:: PATH . ' LIKE ' . '\'' . self::PATH_VALUE . '%\'');

            /**
             * Reset narvar_cron_audit table and add new column for Store ID
             */
            $connection->delete($narvarCrontLogTable, '');
            $connection->addColumn($narvarCrontLogTable, 'store_id', Table::TYPE_INTEGER, null, array(
                'identity'  => true,
                'nullable'  => false,
                'primary'   => false,
                'unsigned'  => true,
            ), 'Store Id');

            /**
             * Delete all data from narvar_audit_log table
             */
            $connection->delete($narvarAuditLogTable, '');

            /**
             * Modify entity_type of narvar_audit_log table
             */
            $connection->changeColumn(
                $narvarAuditLogTable,
                'entity_type',
                'entity_type',
                ['type' => Table::TYPE_TEXT, 'length' => 30, 'nullable' => false]
            );

        }

        $setup->endSetup();
    }
}