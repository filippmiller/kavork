<?php

namespace frontend\modules\shop\controllers;

use common\components\widget\BulkButtonWidget;
use frontend\components\Controller;
use frontend\modules\shop\models\ShopCategory;
use frontend\modules\shop\models\ShopCategorySearch;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * CategoryController implements the CRUD actions for ShopCategory model.
 */
class CategoryController extends Controller
{

  private $def_sel_column = [
      'id',
      'franchisee_id',
      'title',
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
   * Lists all ShopCategory models.
   * @return mixed
   */
  public function actionIndex()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopCategoryView')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $searchModel = new ShopCategorySearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    $canCreate = Yii::$app->user->can('ShopCategoryCreate');
    $actions = "";
    $actions .= Yii::$app->user->can('ShopCategoryUpdate') ? "{update}" : "";
    $actions .= Yii::$app->user->can('ShopCategoryDelete') ? "{delete}" : "";
    $panelButtons = '';
    if (Yii::$app->user->can('ShopCategoryDelete')) {
      $panelButtons = BulkButtonWidget::widget();
    };
    $columns = include(__DIR__ . '/../views/category/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_ShopCategory", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_ShopCategory");
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
        'canCreate' => $canCreate,
        'panelButtons' => $panelButtons,
        'title' => Yii::t('main', 'CATEGORIES'),
        'forAllCafe' => true,]);
  }


  /**
   * Config column in ShopCategory model.
   * @param integer $id
   * @return mixed
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionColumns()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopCategoryView')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $model = new ShopCategory();
    $searchModel = new ShopCategorySearch();

    $request = Yii::$app->request;

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($request->post('column')) {
      $col = $request->post('column');

      if (Yii::$app->user->isGuest) {
        Yii::$app->session->set("columns_ShopCategory", $col);
      } else {
        $user = Yii::$app->getUser()->getIdentity();
        $user->setActiveColumn("columns_ShopCategory", $col);
      }

      return [
          'forceClose' => 'true',
          'forceReload' => '#crud-datatable-pjax',
          'content' => Yii::$app->view->closeModal(),
      ];
    }
    $actions = "";
    $columns = include(__DIR__ . '/../views/category/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_ShopCategory", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_ShopCategory");
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
        'title' => Yii::t('app', 'Change visible columns in ShopCategory table'),
        'content' => $this->renderAjax('columns', [
            'sel_column' => $sel_column,
            'columns' => $columns,
            'model' => $model,
            'isAjax' => true
        ]),
        'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
            Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"])

    ];
  }

  /**
   * Creates a new ShopCategory model.
   * For ajax request will return json object
   * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopCategoryCreate')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $request = Yii::$app->request;
    $model = new ShopCategory();

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    /*
    *   Process for ajax request
    */
    Yii::$app->response->format = Response::FORMAT_JSON;
    if ($request->isGet) {
      return [
          'title' => Yii::t('app', 'Create new ShopCategory'),
          'content' => $this->renderAjax('create', [
              'model' => $model,
              'isAjax' => true
          ]),
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"])
      ];
    } else if ($model->load($request->post()) && $model->save()) {
      return $this->returnBlank(
          [
              'forceReload' => '#crud-datatable-pjax',
              'title' => Yii::t('app', 'Create new ShopCategory'),
          ],
          Yii::t('app', 'Create ShopCategory success')
      );
    } else {
      return [
          'title' => Yii::t('app', 'Create new ShopCategory'),
          'content' => $this->renderAjax('create', [
              'model' => $model,
              'isAjax' => true,
          ]),
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"])

      ];
    }
  }

  /**
   * Updates an existing ShopCategory model.
   * For ajax request will return json object
   * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdate($id)
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopCategoryUpdate')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $request = Yii::$app->request;
    $model = $this->findModel($id);

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    /*
    *   Process for ajax request
    */
    $title = Yii::t('app', 'Update Shop Category: {title}', [
        'title' => '' . $model->title,
    ]);
    Yii::$app->response->format = Response::FORMAT_JSON;
    if ($request->isGet) {
      return [
          'title' => $title,
          'content' => $this->renderAjax('update', [
              'model' => $model,
              'isAjax' => true,
          ]),
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"])
      ];
    } else if ($model->load($request->post()) && $model->save()) {
      return $this->returnBlank(
          [
              'forceReload' => '#crud-datatable-pjax',
              'title' => $title,
          ],
          Yii::t('app', 'Categorie updated successfully')
      );
    } else {
      return [
          'title' => $title,
          'content' => $this->renderAjax('update', [
              'model' => $model,
              'isAjax' => true,
          ]),
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"])
      ];
    }
  }

  /**
   * Delete an existing ShopCategory model.
   * For ajax request will return json object
   * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   */
  public function actionDelete($id)
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopCategoryDelete')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $request = Yii::$app->request;
    $this->findModel($id)->delete();

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
   * Delete multiple existing ShopCategory model.
   * For ajax request will return json object
   * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   */
  public function actionBulkDelete()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('ShopCategoryDelete')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $request = Yii::$app->request;
    $pks = explode(',', $request->post('pks')); // Array or selected records primary keys
    foreach ($pks as $pk) {
      $model = $this->findModel($pk);
      $model->delete();
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
   * Finds the ShopCategory model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return ShopCategory the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = ShopCategory::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException(Yii::t('app', 'Page does not exist'));
    }
  }
}
