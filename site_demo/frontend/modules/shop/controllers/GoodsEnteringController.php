<?php

namespace frontend\modules\shop\controllers;

use common\components\widget\BulkButtonWidget;
use frontend\components\Controller;
use frontend\modules\shop\models\ShopTransaction;
use frontend\modules\shop\models\ShopTransactionSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * GoodsEnteringController implements the CRUD actions for ShopSale model.
 */
class GoodsEnteringController extends Controller
{
  private $def_sel_column = [
      'id',
      '_product_title',
      '_product_barcode',
      'quantity',
      'created_at',
  ];

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
        'verbs' => [
            'class' => VerbFilter::className(),
            'actions' => [
                'delete' => ['post'],
                'bulk-delete' => ['post'],
            ],
        ],];
  }

  /**
   * Lists all ShopSale models.
   * @return mixed
   */
  public function actionIndex()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopGoodsEnteringView')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $searchModel = new ShopTransactionSearch();

    $searchModel->operation_type_id = ShopTransaction::OPERATION_TYPE_INCOME;

    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    $actions = "";
    $actions .= Yii::$app->user->can('ShopGoodsEnteringDelete') ? "{delete}" : "";
    $panelButtons = '';
    if (Yii::$app->user->can('ShopGoodsEnteringDelete')) {
      $panelButtons = BulkButtonWidget::widget();
    };
    $columns = include(__DIR__ . '/../views/goods-entering/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_ShopGoodsEntering", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_ShopGoodsEntering");
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
        'dataProvider' => $dataProvider,
        'columns' => $columns,
        'panelButtons' => $panelButtons,
        'title' => Yii::t('main', 'GOODS ENTERING'),
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
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopGoodsEnteringView')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $model = new ShopTransaction();
    $searchModel = new ShopTransactionSearch();

    $request = Yii::$app->request;

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($request->post('column')) {
      $col = $request->post('column');

      if (Yii::$app->user->isGuest) {
        Yii::$app->session->set("columns_ShopGoodsEntering", $col);
      } else {
        $user = Yii::$app->getUser()->getIdentity();
        $user->setActiveColumn("columns_ShopGoodsEntering", $col);
      }

      return [
          'forceClose' => 'true',
          'forceReload' => '#crud-datatable-pjax',
          'content' => Yii::$app->view->closeModal(),
      ];
    }
    $actions = "";
    $columns = include(__DIR__ . '/../views/goods-entering/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_ShopGoodsEntering", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_ShopGoodsEntering");
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
        'title' => Yii::t('app', 'Change visible columns in Shop Goods Entering table'),
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

  /**
   * Delete an existing ShopTransaction model.
   * For ajax request will return json object
   * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   */
  public function actionDelete($id)
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopGoodsEnteringDelete')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $request = Yii::$app->request;
    $this->findModel($id)->deleteWithProductReturn();

    if ($request->isAjax) {
      /*
      *   Process for ajax request
      */
      Yii::$app->response->format = Response::FORMAT_JSON;
      return ['forceClose' => true, 'forceReload' => '#crud-datatable-pjax'];
    } else {
      /*
      *   Process for non-ajax request
      */
      return $this->redirect(['index']);
    }
  }

  /**
   * Delete multiple existing ShopTransaction model.
   * For ajax request will return json object
   * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   */
  public function actionBulkDelete()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopGoodsEnteringDelete')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $request = Yii::$app->request;
    $pks = explode(',', $request->post('pks')); // Array or selected records primary keys
    foreach ($pks as $pk) {
      $model = $this->findModel($pk);
      $model->deleteWithProductReturn();
    }

    if ($request->isAjax) {
      /*
      *   Process for ajax request
      */
      Yii::$app->response->format = Response::FORMAT_JSON;
      return ['forceClose' => true, 'forceReload' => '#crud-datatable-pjax'];
    } else {
      /*
      *   Process for non-ajax request
      */
      return $this->redirect(['index']);
    }

  }

  /**
   * Finds the ShopTransaction model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return ShopTransaction the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = ShopTransaction::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException(Yii::t('app', 'Page does not exist'));
    }
  }
}
