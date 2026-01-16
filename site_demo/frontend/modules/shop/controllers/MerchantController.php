<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 17.09.18
 * Time: 18:15
 */

namespace frontend\modules\shop\controllers;

use frontend\components\Controller;
use frontend\modules\shop\widgets\merchant\models\MerchantIncomeForm;
use frontend\modules\shop\widgets\merchant\models\MerchantSaleForm;
use kartik\growl\Growl;
use Yii;
use yii\helpers\Html;
use yii\web\Response;

/**
 * MerchantController
 */
class MerchantController extends Controller
{
  public function actionIncome()
  {
    $model = new MerchantIncomeForm();

    Yii::$app->response->format = Response::FORMAT_JSON;

    if (Yii::$app->request->isAjax) {
      if ($model->load(Yii::$app->request->post()) && $model->process()) {
        return [
            'success' => true,
            'message' => [
                'message' => Yii::t('main', 'Product "{0}" added', $model->product->title),
                'type' => Growl::TYPE_SUCCESS,
            ],
        ];
      }

      $result = [];
      foreach ($model->getErrors() as $attribute => $errors) {
        $inputId = Html::getInputId($model, $attribute);
        $result[$inputId] = $errors;
      }

      return [
          'validation' => $result,
          'message' => [
              'message' => implode('<br>', $model->getErrorSummary(false)),
              'type' => Growl::TYPE_DANGER,
          ],
      ];
    }

    return [];
  }

  public function actionSale()
  {
    $model = new MerchantSaleForm();

    Yii::$app->response->format = Response::FORMAT_JSON;

    if (Yii::$app->request->isAjax) {
      if ($model->load(Yii::$app->request->post()) && $model->process()) {
        return [
            'success' => true,
            'message' => [
                'message' => Yii::t('main', 'Product "{0}" sold', $model->product->title),
                'type' => Growl::TYPE_SUCCESS,
            ],
        ];
      }

      $result = [];
      foreach ($model->getErrors() as $attribute => $errors) {
        $inputId = Html::getInputId($model, $attribute);
        $result[$inputId] = $errors;
      }

      return [
          'validation' => $result,
          'message' => [
              'message' => implode('<br>', $model->getErrorSummary(false)),
              'type' => Growl::TYPE_DANGER,
          ],
      ];
    }

    return [];
  }
}