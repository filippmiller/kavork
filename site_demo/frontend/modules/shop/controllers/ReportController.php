<?php

namespace frontend\modules\shop\controllers;

use frontend\components\Controller;
use frontend\modules\shop\models\ShopReportSearch;
use frontend\modules\shop\models\ShopSale;
use frontend\modules\shop\models\ShopTransaction;
use Yii;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * CatalogController implements the CRUD actions for ShopProduct model.
 */
class ReportController extends Controller
{

  private $def_sel_column = [
      'image',
      '_product_title',
    //'_item_weight',
      'quantity',
    //'in_stock',
      'price',
      'sum',
    //'vat',
      'vat_total',
      'cost',
    //'description',
  ];

  public function actionIndex()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopReportView')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $searchModel = new ShopReportSearch();

    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    $panelButtons = '';
    $columns = include(__DIR__ . '/../views/report/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_ShopReport", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_ShopReport");
    }
    if (!$sel_column) {
      $sel_column = $this->def_sel_column;
    }
    foreach ($columns as $k => $column) {
      $column_name = !is_array($column) ? $column : (isset($column['attribute']) ? $column['attribute'] : false);
      if ($column_name && !in_array($column_name, $sel_column)) {
        unset($columns[$k]);
      }
    }

    return $this->render('index', [
        'searchModel' => $searchModel,
        'sales' => $searchModel,
        'dataProvider' => $dataProvider,
        'columns' => $columns,
        'title' => Yii::t('main', 'REPORTS'),
    ]);
  }

  /**
   * Config column in ShopProduct model.
   * @param integer $id
   * @return mixed
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionColumns()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopReportView')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $model = new ShopTransaction();
    $searchModel = new ShopReportSearch();

    $request = Yii::$app->request;

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($request->post('column')) {
      $col = $request->post('column');

      if (Yii::$app->user->isGuest) {
        Yii::$app->session->set("columns_ShopReport", $col);
      } else {
        $user = Yii::$app->getUser()->getIdentity();
        $user->setActiveColumn("columns_ShopReport", $col);
      }

      return [
          'forceClose' => 'true',
          'forceReload' => '#crud-datatable-pjax',
          'content' => Yii::$app->view->closeModal(),
      ];
    }
    $actions = "";
    $columns = include(__DIR__ . '/../views/report/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_ShopReport", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_ShopReport");
    }
    if (!$sel_column) {
      $sel_column = $this->def_sel_column;
    }
    foreach ($columns as $k => $column) {
      $column_name = !is_array($column) ? $column : (isset($column['attribute']) ? $column['attribute'] : false);
      if (!$column_name) {
        unset($columns[$k]);
      } else {
        $columns[$k] = $column_name;
      }
    }

    return [
        'title' => Yii::t('app', 'Change visible columns in Shop Report table'),
        'content' => $this->renderAjax('columns', [
            'sel_column' => $sel_column,
            'columns' => $columns,
            'model' => $model,
            'isAjax' => true,
        ]),
        'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
            Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"]),

    ];
  }

  public function actionView($id)
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopReportView')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $request = Yii::$app->request;

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    $model = ShopSale::find()
        ->andWhere([
            ShopSale::tableName() . '.cafe_id' => Yii::$app->cafe->id,
            'id' => $id
        ])
        ->one();

    if (!$model) {
      return [
          'title' => Yii::t('app', "View visit error"),
          'content' => Yii::t('app', "Visit not found"),
          'footer' => Html::button(Yii::t('app', 'Close'), [
              'class' => 'btn btn-default pull-left',
              'data-dismiss' => "modal",
          ]),
      ];
    }

    $button = Html::button(Yii::t('app', 'Close'), [
        'class' => 'btn btn-default pull-left',
        'data-dismiss' => "modal",
    ]);

    //ddd($model->getCheckData());
    $content = $this->renderAjax('view', [
        'model' => $model,
        'cafe' => Yii::$app->cafe,
    ]);

    //return $content;
    return [
        'title' => '<span class="fa fa-user antagon-color-main"></span> ' . Yii::t('app', "Estimation visit"),
        'content' => $content,
        'footer' => $button,
    ];

  }
}