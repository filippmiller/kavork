<?php

use yii\db\Migration;

/**
 * Class m180903_160852_cafe_RBAC_persons_limit
 */
class m180903_160852_cafe_RBAC_persons_limit extends Migration
{
	public $itemTableName = '{{%cafe_auth_item}}';
	public $assignmentTableName = '{{%cafe_auth_assignment}}';

	public $items = [
		"personsLimit"       => 'Ограничение на количество людей в кафе',
	];

	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		foreach ($this->items as $itemName => $itemDescription) {
			$this->insert($this->itemTableName, [
				'name'        => $itemName,
				'description' => $itemDescription,
				'created_at'  => time(),
				'updated_at'  => time(),
			]);
		}

		$query = new \yii\db\Query();
		$query->select(['id'])->from('{{%cafe}}')->orderBy('id');

		foreach ($query->each() as $cafe) {
			foreach ($this->items as $itemName => $itemDescription) {
				$this->insert($this->assignmentTableName, [
					'item_name'  => $itemName,
					'cafe_id'    => $cafe['id'],
					'created_at' => time(),
				]);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		foreach ($this->items as $itemName => $itemDescription) {
			$this->delete($this->itemTableName, [
				'name' => $itemName,
			]);
		}
	}
}
