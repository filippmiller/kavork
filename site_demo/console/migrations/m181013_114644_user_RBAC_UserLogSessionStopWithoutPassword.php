<?php

use yii\db\Migration;

/**
 * Class m181013_114644_cafe_RBAC_sessionStopPasswordRequestBypass
 */
class m181013_114644_user_RBAC_UserLogSessionStopWithoutPassword extends Migration
{
	/* @var \yii\rbac\ManagerInterface */
	public $auth;

	public $items = [
		[
			'name'        => 'UserLogSessionStopWithoutPassword',
			'description' => 'UserLog - Завершение сессии без пароля',
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
