<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 17.10.18
 * Time: 18:43
 */

namespace frontend\modules\selfservice;

use Yii;
use yii\base\BootstrapInterface;

/**
 * Self service module definition class
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
  const SESSION_MODE_KEY = 'self-service-mode';

  public function bootstrap($app)
  {
    $mode = Yii::$app->session->get(self::SESSION_MODE_KEY);

    if (!Yii::$app->request->isAjax && $mode) {
      $currentUrl = Yii::$app->request->getUrl();

      if (
          strpos($currentUrl, '/selfservice') !== 0 && // Self-Service Access
          strpos($currentUrl, '/tpls') !== 0 &&          // Template Access
          strpos($currentUrl, '/debug') !== 0           // debug
      ) {
        // Redirect to self-service...
        return Yii::$app->response->redirect(['/selfservice/default/dashboard']);
      }
    }

  }
}