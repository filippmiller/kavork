<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 11.04.19
 * Time: 10:57
 */

namespace console\controllers;

use Faker\Factory;
use frontend\modules\franchisee\models\FranchiseePayments;
use frontend\modules\franchisee\models\FranchiseeRegistration;
use frontend\modules\franchisee\models\FranchiseeTariffs;
use frontend\modules\tariffs\models\Tariffs;
use frontend\modules\users\models\Users;
use frontend\modules\visitor\models\Visitor;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;

class DemoController extends Controller
{
  public $dumpPath = '@console/../demoDump';

  public $franchisee_id = null;

  /**
   * Очистка базы даных и создание тестового акаунта
   */
  public function actionIndex()
  {
    $query = (new \yii\db\Query());

    $this->log('Запуск очистки базы');
    $this->loadDump('1.clear.sql');
    $this->log('Загрузка стандартных данных');
    $this->loadDump('2.add_base_data.sql');

    $this->operation("Создание ROOT пользователя");
    $this->operationStatus(
        $this->addUser('root', 'rootroot', 'root')
    );

    $this->operation("Создание тестового платежа и его проведение");
    $this->operationStatus($this->addPayment());

    $this->operation("Задаем пароль для пользователя DEMO");
    $this->operationStatus(
        $this->setPasswordUser('demo', 'demoadmin')
    );

    $this->operation("Создание MANAGER пользователя");
    $this->operationStatus(
        $this->addUser('manager', 'manager', null, $this->franchisee_id)
    );

    $this->operation("Создание тариф для кафе");
    $this->operationStatus(
        $this->addTariff()
    );

    $this->operation("Создание поситителей");
    $this->operationStatus(
        $this->addVisitor(500)
    );

    $this->log('Успешное завершение');
  }

  private function timeStamp()
  {
    echo $this->ansiFormat(date('H:i:s'), Console::FG_GREEN) . '  ';
  }

  private function log($msg)
  {
    if (empty($msg)) return;
    $this->timeStamp();
    echo $msg . "\n";
  }

  private function operation($msg)
  {
    if (empty($msg)) return;
    $this->timeStamp();
    echo $msg;
  }

  private function operationStatus($ok = true)
  {
    echo "\t\t" . $this->ansiFormat($ok ? "OK" : 'ERR', $ok ? Console::FG_YELLOW : Console::FG_RED) . "\n";
  }

  private function loadDump($mask = '*.sql')
  {
    $path = FileHelper::normalizePath(Yii::getAlias($this->dumpPath));
    if (file_exists($path)) {
      if (is_dir($path)) {
        $files = FileHelper::findFiles($path, ['only' => [$mask]]);
        if (!$files) {
          $this->operation('File ' . $this->ansiFormat($mask, Console::FG_YELLOW) . ' does not contain any SQL files');
          $this->operationStatus(false);
          exit;
        }
      } else {
        $files = [$path];
      }

      $db = Yii::$app->getDb();
      if (!$db) {
        $this->operation('DB component not configured');
        $this->operationStatus(false);
        exit;
      }
      foreach ($files as $path) {
        exec('mysql --host=' . $this->getDsnAttribute('host', $db->dsn) . ' --user=' . $db->username . ' --password=' . $db->password . ' ' . $this->getDsnAttribute('dbname', $db->dsn) . ' < ' . $path);
        $name = explode('/', $path);
        $name = array_pop($name);
        $name = Console::ansiFormat($name, [Console::FG_GREEN]);
        echo Console::ansiFormat(" > ", [Console::FG_YELLOW]);
        echo 'Dump file [' . $name . '] was imported' . PHP_EOL;
      }
    } else {
      $this->operation('DUMP does not exist');
      $this->operationStatus(false);
    }
  }

  private function getDsnAttribute($name, $dsn)
  {
    if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
      return $match[1];
    } else {
      return null;
    }
  }

  private function addUser($name, $password, $role, $franchisee_id = null)
  {
    $user = new Users();
    $user->name = $name;
    $user->state = 0;
    $user->franchisee_id = $franchisee_id;
    $user->email = $name . '@test.com';
    $user->new_password = $password;
    if (!$user->save()) return false;

    if (!$role) return true;

    //даем роль админа кафе
    $auth = \Yii::$app->authManager;
    $role = $auth->getRole($role);
    return !!$auth->assign($role, $user->id);
  }

  private function addPayment()
  {
    $payment = new FranchiseeRegistration();
    $payment->name = 'demo';
    $payment->cafe_name = 'Demo';
    $payment->params_id = 1;
    $payment->tariff = FranchiseeTariffs::find()->one();
    $payment->currency = key(Yii::$app->params['currency']);
    $payment->email = 'demo@test.com';
    $payment->language_ids = array_keys(Yii::$app->params['lg_list']);

    $payment->preparePayment();
    //$payment->payment->makePay() &&
    $payment->payment->code = "DEMO PAY";
    $payment->payment->created_at = date('Y-m-d H:i:s');
    $payment->payment->status = FranchiseePayments::STATUS_DONE;
    $payment->payment->save();
    return $payment->payment->applyTariff();
  }

  private function setPasswordUser($name, $password)
  {
    $user = Users::find()
        ->where(['name' => $name])
        ->one();

    if (!$user) {
      return false;
    }
    $this->franchisee_id = $user->franchisee_id;

    $user->new_password = $password;
    return $user->save();
  }

  private function addTariff()
  {
    $tariff = new Tariffs();
    $tariff->type_id = Tariffs::TYPE_REGIONAL;
    $tariff->params_id = 1;
    $tariff->franchisee_id = $this->franchisee_id;
    $tariff->min_sum = 10;
    $tariff->max_sum = 200;
    $tariff->first_hour = 20;
    $tariff->start_visit = 1;
    $tariff->active = 0;
    return $tariff->save();
  }

  private function addVisitor($count){
    /*$admin_id = Users::find()
        ->orderBy(['id'=>SORT_DESC])
        ->one();
    $admin_id =$admin_id->id;*/

    $lg_list = array_keys(Yii::$app->params['lg_list']);

    $lastCode = Visitor::getLastCode(false,$this->franchisee_id);

    for ($i = 0; $i<$count;$i++){
      $lastCode[1]++;
      $faker = Factory::create();

      $visitor = new Visitor();
      $visitor->franchisee_id = $this->franchisee_id;
      $visitor->f_name = $faker->firstName;
      $visitor->l_name = $faker->lastName;
      if(random_int(0,10)<2)$visitor->phone = $faker->phoneNumber;
      if(random_int(0,10)<5)$visitor->email = $faker->email;
      $visitor->create = date('Y-m-d H:i:s',time()-random_int(82800,4968000));
      $visitor->code = implode('',$lastCode);
      $visitor->lg = $lg_list[random_int(0,count($lg_list)-1)];
      $visitor->save();
      //ddd($visitor->errors);
    }
    return true;
  }
}