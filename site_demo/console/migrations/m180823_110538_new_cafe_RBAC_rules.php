<?php

use yii\db\Migration;

/**
 * Class m180823_110538_new_cafe_RBAC_rules
 */
class m180823_110538_new_cafe_RBAC_rules extends Migration
{
  /* @var \yii\rbac\ManagerInterface */
  public $auth;

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $roleRoot = $this->auth->getRole('root');
    $roleAdmin = $this->auth->getRole('admin');

    $this->createPermission(
        'CafeRulesView',
        'Cafe - просмотр разрешенией Кафе',
        [$roleRoot, $roleAdmin]
    );

    $this->createPermission(
        'CafeRulesSet',
        'Cafe - изменение разрешенией Кафе',
        [$roleRoot, $roleAdmin]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    echo "m180820_202740_new_cafe_RBAC cannot be reverted.\n";

    return false;
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
