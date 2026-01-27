<?php
namespace console\controllers;
use common\models\Admitad;
use common\models\Travelpayouts;
use common\models\Advertise;
use common\models\SdApi;
use yii\console\Controller;
use yii\helpers\Console;
use frontend\modules\coupons\models\Coupons;
use Yii;
use frontend\modules\actions\models\ActionsActions;
use common\models\Webgains;
use common\models\Mycommerce;
use frontend\modules\template\models\Template;
class TestController extends Controller
{
  public function beforeAction($action)
  {
    if (Console::isRunningOnWindows()) {
      // Set console code page to UTF-8 on Windows
      // Suppress errors as this is a best-effort operation
      @shell_exec('chcp 65001 2>nul');
    }
    return parent::beforeAction($action);
  }
  /**
   * Тест почты. отправка письма на matuhinmax@mail.ru
   */
  public function actionMail()
  {
    try {
      ddd(Yii::$app
          ->mailer
          ->compose()
          ->setTextBody('Текст сообщения')
          //->setHtmlBody('<b>текст сообщения в формате HTML</b>')
          ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->params['adminName']])
          ->setTo([
              'matuhinmax@gmail.com',
          ])
          //->setSubject(Yii::$app->name . ': Тест')
          ->send());
    } catch (\Exception $e) {
      ddd($e);
      echo 'error';
    }
  }
}
