<?php

class m180803_123055_visitor_RBAC extends \yii\db\Migration
{

  private $auth;

  public function up()
  {
    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $role = $this->auth->getRole('root');

    /*$this->execute('TRUNCATE mysql.`time_zone` ;');
    $this->execute('TRUNCATE mysql.`time_zone_leap_second` ;');
    $this->execute('TRUNCATE mysql.`time_zone_name` ;');
    $this->execute('TRUNCATE mysql.`time_zone_transition` ;');
    $this->execute('TRUNCATE mysql.`time_zone_transition_type` ;');*/
    $this->execute('set time_zone = \'-4:00\';');

    $this->execute('ALTER TABLE `visitor` CHANGE  `lang` `lg` VARCHAR(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT \'en-EN\';');
    $this->execute('UPDATE `visitor` SET `lg` = \'en-EN\' WHERE `lg` = \'2\';;');
    $this->execute('UPDATE `visitor` SET `lg` = \'en-EN\' WHERE `lg` = \'0\';;');
    $this->execute('UPDATE `visitor` SET `lg` = \'fr-FR\' WHERE `lg` = \'1\';');


    $this->execute('ALTER TABLE `visitor` CHANGE `creat` `create` TEXT NOT NULL;');
    $this->execute('UPDATE `visitor` SET `create` = from_unixtime(`create`);');
    $this->execute('ALTER TABLE `visitor` CHANGE `create` `create` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;');


    $this->execute('ALTER TABLE `visitor` CHANGE `notice` `notice` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;');

    $this->addColumn('visitor', 'visit_cnt', $this->float()->after('l_name'));
    $this->execute('UPDATE `visitor` SET visitor.`visit_cnt` = (SELECT COUNT(*) from visitor_log where visitor_log.visitor_id=`visitor`.`id` )');

    //$this->execute('ALTER TABLE `visitor` CHANGE `f_name` `f_name` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;');
    $this->execute('ALTER TABLE `visitor` ADD INDEX(`f_name`);');
    //$this->execute('ALTER TABLE `visitor` CHANGE `l_name` `l_name` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;');
    $this->execute('ALTER TABLE `visitor` ADD INDEX(`l_name`);');

    $this->createPermission(
        'VisitorView',
        'Visitor - просмотр (общая таблица)',
        [$role]
    );

    $this->createPermission(
        'VisitorUpdate',
        'Visitor  - редактирование',
        [$role]
    );

    $this->createPermission(
        'VisitorDelete',
        'Visitor - удаление',
        [$role]
    );

    $this->createPermission(
        'VisitorCreate',
        'Visitor - создание',
        [$role]
    );
  }

  public function down()
  {
    //откат миграции
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
