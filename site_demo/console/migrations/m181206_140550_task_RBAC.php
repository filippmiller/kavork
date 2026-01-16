<?php

class m181206_140550_task_RBAC extends \yii\db\Migration
{

  private $auth;

  public function up()
  {
    //$this->execute('set time_zone = \'-5:00\';');
    $this->execute('set time_zone = \'-0:00\';');
    $this->execute('ALTER TABLE `task` CHANGE `start_date` `start_date` TEXT NOT NULL;');
    $this->execute('UPDATE `task` SET `start_date` = from_unixtime(`start_date`);');
    $this->execute('ALTER TABLE `task` CHANGE `start_date` `start_date` DATE NOT NULL;');

    $this->execute('ALTER TABLE `task` CHANGE `start_time` `start_time` TEXT NOT NULL;');
    $this->execute('UPDATE `task` SET `start_time` = from_unixtime(`start_time`);');
    $this->execute('ALTER TABLE `task` CHANGE `start_time` `start_time` TIME NOT NULL;');

    $this->execute('ALTER TABLE `task` CHANGE `end_time` `end_time` TEXT NOT NULL;');
    $this->execute('UPDATE `task` SET `end_time` = from_unixtime(`end_time`);');
    $this->execute('ALTER TABLE `task` CHANGE `end_time` `end_time` TIME NOT NULL;');

    $this->execute('set time_zone = \'-4:00\';');
    $this->execute('ALTER TABLE `do_task` CHANGE `datetime` `datetime` TEXT NOT NULL;');
    $this->execute('UPDATE `do_task` SET `datetime` = from_unixtime(`datetime`);');
    $this->execute('ALTER TABLE `do_task` CHANGE `datetime` `datetime` DATETIME NOT NULL;');

    $this->execute('ALTER TABLE `do_task` CHANGE `closedate` `closedate` TEXT NULL DEFAULT NULL;');
    $this->execute('UPDATE `do_task` SET `closedate` = from_unixtime(`closedate`);');
    $this->execute('ALTER TABLE `do_task` CHANGE `closedate` `closedate` DATETIME NULL DEFAULT NULL;');

    $this->addColumn('do_task','text',$this->string()->after('cafe'));

    $this->addForeignKey(
        'fk-do_task-user_id-user-id',
        'do_task',
        'user_id',
        '{{%user}}',
        'id',
        'SET NULL',
        'CASCADE'
    );

    $this->execute('ALTER TABLE `do_task` CHANGE `cafe` `cafe_id` INT(11) NULL DEFAULT NULL;');
    $this->execute('UPDATE `do_task` INNER JOIN `task` ON `do_task`.`task_id` = `task`.id SET `do_task`.text = `task`.text');

    $this->addForeignKey(
        'fk-do_task-cafe_id-cafe-id',
        'do_task',
        'cafe_id',
        '{{%cafe}}',
        'id',
        'SET NULL',
        'CASCADE'
    );
    $this->addForeignKey(
        'fk-do_task-task_id',
        'do_task',
        'task_id',
        '{{%task}}',
        'id',
        'SET NULL',
        'CASCADE'
    );

    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $roles = array(
        $this->auth->getRole('root'),
        $this->auth->getRole('admin'),
    );

    $this->createPermission(
        'TaskView',
        'Task - просмотр (общая таблица)',
        $roles
    );

    $this->createPermission(
        'TaskUpdate',
        'Task  - редактирование',
        $roles
    );
    /*$this->createPermission(
      'TaskDelete',
      'Task - удаление',
      $roles
    );*/
    $this->createPermission(
        'TaskCreate',
        'Task - создание',
        $roles
    );
    $this->createPermission(
        'TaskOnStart',
        'Task - Уведомление при старте системы',
        $roles
    );
    $this->createPermission(
        'TaskMain',
        'Task - Кнопка списока задач на стартовой',
        $roles
    );
    $this->createPermission(
        'TaskReminder',
        'Task - Напоминание о заданиях',
        $roles
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
