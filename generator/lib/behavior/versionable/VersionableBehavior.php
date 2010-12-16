<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once dirname(__FILE__) . '/VersionableBehaviorObjectBuilderModifier.php';

/**
 * Keeps tracks of all the modifications in an ActiveRecord object
 *
 * @author    Francois Zaninotto
 * @version		$Revision$
 * @package		propel.generator.behavior.versionable
 */
class VersionableBehavior extends Behavior
{
	// default parameters value
	protected $parameters = array(
		'version_column' => 'version',
		'version_table' => '',
	);

	protected $objectBuilderModifier;
	
	/**
	 * Add the version_column to the current table
	 */
	public function modifyTable()
	{
		$table = $this->getTable();
		if(!$table->containsColumn($this->getParameter('version_column'))) {
			$table->addColumn(array(
				'name' => $this->getParameter('version_column'),
				'type' => 'INTEGER',
				'default' => 0
			));
		}
		$database = $table->getDatabase();
		$versionTableName = $this->getParameter('version_table') ? $this->getParameter('version_table') : ($table->getName() . '_version');
		if (!$database->hasTable($versionTableName)) {
			// create the version table
			$versionTable = $database->addTable(array(
				'name' => $versionTableName,
				'phpName' => $this->getVersionTablePhpName(),
				'package' => $table->getPackage(),
				'schema' => $table->getSchema(),
				'namespace' => $table->getNamespace(),
			));
			// copy all the columns
			foreach ($table->getColumns() as $column) {
				$columnInVersionTable = clone $column;
				if ($columnInVersionTable->isAutoincrement()) {
					$columnInVersionTable->setAutoIncrement(false);
				}
				$versionTable->addColumn($columnInVersionTable);
			}
			// create the foreign key
			$fk = new ForeignKey();
			$fk->setForeignTableCommonName($table->getCommonName());
			$fk->setForeignSchemaName($table->getSchema());
			$fk->setOnDelete('CASCADE');
			$fk->setOnUpdate(null);
			$tablePKs = $table->getPrimaryKey();
			foreach ($versionTable->getPrimaryKey() as $key => $column) {
				$fk->addReference($column, $tablePKs[$key]);
			}
			$versionTable->addForeignKey($fk);
			// add the version column to the primary key
			$versionTable->getColumn($this->getParameter('version_column'))->setPrimaryKey(true);
		}
	}
	
	public function getVersionTablePhpName()
	{
		return $this->getTable()->getPhpName() . 'Version';
	}

	public function getObjectBuilderModifier()
	{
		if (is_null($this->objectBuilderModifier))
		{
			$this->objectBuilderModifier = new VersionableBehaviorObjectBuilderModifier($this);
		}
		return $this->objectBuilderModifier;
	}
	
}