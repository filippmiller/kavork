<?php

use yii\db\Migration;

/**
 * Class m181013_194149_user_RBAC_FranchiseeDiscountUpdate
 */
class m181013_194149_user_RBAC_FranchiseeDiscountUpdate extends Migration
{
	/* @var \yii\rbac\ManagerInterface */
	public $auth;

	public $items = [
		[
			'name'        => 'FranchiseeDiscountUpdate',
			'description' => 'Franchisee - Редактирование настроек Дисконта',
		],
	];

	public function up()
	{
		//применить миграцию
		$this->auth = \Yii::$app->authManager;
		$defaultRoles = [
			$this->auth->getRole('root'),
		];

		foreach ($this->items as $item) {
			$this->createPermission($item['name'], $item['description'], $defaultRoles);
		}
	}

	public function down()
	{
		$this->auth = \Yii::$app->authManager;
		foreach ($this->items as $item) {
			$permission = $this->auth->getPermission($item['name']);
			if ($permission) {
				$this->auth->remove($permission);
			}
		}
	}

	private function createPermission($name, $description = '', $roles = [])
	{
		$permit = $this->auth->createPermission($name);
		$permit->description = $description;
		$this->auth->add($permit);
		foreach ($roles as $role) {
			$this->auth->addChild($role, $permit);//Связываем роль и привелегию
		}
	}
}
