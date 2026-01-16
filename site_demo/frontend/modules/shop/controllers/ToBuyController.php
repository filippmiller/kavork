<?php

namespace frontend\modules\shop\controllers;

use frontend\components\Controller;
use frontend\modules\shop\models\ShopProduct;
use kartik\mpdf\Pdf;
use Yii;

/**
 * CatalogController implements the CRUD actions for ShopProduct model.
 */
class ToBuyController extends Controller
{

  public function actionIndex()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopCatalogView') || !Yii::$app->cafe->can('shopListToBay')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $list = ShopProduct::listToBay()->all();

    return $this->render('index.twig', [
        'list' => $list,
        'title' => Yii::t('main', "LIST TO BUY")
    ]);
  }

  public function actionDownload()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopCatalogView') || !Yii::$app->cafe->can('shopListToBay')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
    $headers = Yii::$app->response->headers;
    $headers->add('Content-Type', 'application/pdf');

    $list = ShopProduct::listToBay()->all();

    $content = $this->renderPartial('index.twig', [
        'list' => $list,
        'title' => Yii::t('app', "List to buy"),
        'hide_button' => true,
        'suf_label' => "_short"
    ]);

    // setup kartik\mpdf\Pdf component
    $pdf = new Pdf([
        'content' => $content,
      // set mPDF properties on the fly
        'options' => ['title' => Yii::t('app', "List to buy")],
      // call mPDF methods on the fly
        'methods' => [
            'SetHeader' => Yii::t('app', "List to buy") . "   " . Yii::$app->cafe->name,
            'SetFooter' => [
                '<table class=footer style="width: 100%;">
                  <tr>
                    <td>' . Yii::t('app', "Page") . ' {PAGENO}</td>
                    <td style="text-align: right;">' . Yii::t('app', "Generation date") . ' ' . date(Yii::$app->params['lang']['datetime']) . '</td>
                  </tr>
                </table>'
            ]
        ]
    ]);

    // return the pdf output as per the destination setting
    return $pdf->render();
  }
}