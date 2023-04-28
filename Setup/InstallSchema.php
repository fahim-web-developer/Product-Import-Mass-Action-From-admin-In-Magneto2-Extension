<?php
namespace MageAfi\ProductImport\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('mageafi_productimport_post')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('mageafi_productimport_post')
			)
				->addColumn(
					'post_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'Post ID'
				)
				->addColumn(
					'user',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable => false'],
					'User'
				)
				->addColumn(
					'password',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Password'
				)
				->addColumn(
					'status',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					1,
					[],
					'Post Status'
				)
				->addColumn(
						'created_at',
						\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
						null,
						['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
						'Created At'
				)->addColumn(
					'updated_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
					'Updated At')
				->setComment('Post Table');
			$installer->getConnection()->createTable($table);


			$installer->getConnection()->addIndex(
				$installer->getTable('mageafi_productimport_post'),
				$setup->getIdxName(
					$installer->getTable('mageafi_productimport_post'),
					['user','password'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['user','password'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);

			/**
	         * Create table 'user post'
	         */
	        $table = $setup->getConnection()
	            ->newTable($setup->getTable('mageafi_productimport'))
	            ->addColumn(
	                'id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                [
	                    'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
	                ],
	                'Greeting ID'
	            )->addColumn(
	                'name',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                255,
	                [
	                    'nullable' => false,
	                    'default' => ''
	                ],
	                'User Name'
	            )
	            ->addColumn(
	                'file',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                255,
	                [
	                    'nullable' => false,
	                    'default' => ''
	                ],
	                'File Name'
	            )
	            ->addColumn(
	                'path',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                255,
	                [
	                    'nullable' => false,
	                    'default' => ''
	                ],
	                'File Path'
	            )->setComment("File Path");

	        $setup->getConnection()->createTable($table);



			$installer->getConnection()->addIndex(
				$installer->getTable('mageafi_productimport'),
				$setup->getIdxName(
					$installer->getTable('mageafi_productimport'),
					['name','file'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['name','file'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);
		}
		$installer->endSetup();
	}
}