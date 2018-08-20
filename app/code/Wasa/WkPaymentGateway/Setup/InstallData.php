<?php

namespace Wasa\WkPaymentGateway\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * Prepare database for install
         */
        $setup->startSetup();

        $statusData[] = [
            'status' => 'pending_wasa_checkout',
            'label' => 'Pending Wasa Checkout'
        ];
        $setup->getConnection()
            ->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $statusData);


        $statesData[] = [
            'status' => 'pending_wasa_checkout',
            'state' => 'pending_wasa_checkout',
            'is_default' => 1
        ];
        $setup->getConnection()
            ->insertArray($setup->getTable('sales_order_status_state'), ['status', 'state', 'is_default'], $statesData);

        /**
         * Prepare database after install
         */
        $setup->endSetup();
    }
}