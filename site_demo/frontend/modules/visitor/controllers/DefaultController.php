<?php

namespace frontend\modules\visitor\controllers;

use frontend\components\Controller;
use frontend\modules\visitor\models\Visitor;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Default controller for the `visits` module
 */
class DefaultController extends Controller
{

  public function actionAjax($term)
  {
    $module = Yii::$app->getModule('selfservice');
    $selfMOde = (Yii::$app->session->get($module::SESSION_MODE_KEY));

    if (Yii::$app->user->isGuest || !Yii::$app->cafe->can("startVisit")) {
      throw new ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $request = Yii::$app->request;
    /*if (!$request->isAjax) {
      throw new ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }*/
    Yii::$app->response->format = Response::FORMAT_JSON;

    $results = [];

    $visitors = Visitor::findByString($term, false, false);

    if (!$visitors) return [];

    $visitors = $visitors->limit(30)->all();

    foreach ($visitors as $model) {
      $data = $model->getAttributes($selfMOde ? ['id', 'f_name', 'l_name', 'email', 'code', 'phone'] : null);
      if ($selfMOde) {
        if (!empty($data['email'])) {
          for ($i = 1; $i < 5; $i++) {
            $data['email'][$i] = '*';
          }
        }
      }

      if ($selfMOde) {
        if (!empty($data['phone'])) {
          for ($i = 0; $i < strlen($data['phone']) - 4; $i++) {
            $data['phone'][$i] = '*';
          }
        }
      }

      $data['name'] = trim($data['f_name'] . ' ' . $data['l_name']);
      $results[] = $data;
    }

    return $results;
  }
}
