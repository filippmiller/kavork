<?php

namespace frontend\modules\franchisee\controllers;

use frontend\components\Controller;
use frontend\modules\franchisee\models\Franchisee;
use frontend\modules\franchisee\models\FranchiseeSearch;
use Yii;
use yii\helpers\Html;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * AdminController implements the CRUD actions for Franchisee model.
 */
class AdminController extends Controller
{

  private $def_sel_column = [
      'id',
      'name',
      'active_until',
      'code',
      'roles',
      'languages',
      'created_at',
  ];

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
    ];
  }

  /**
   * Lists all Franchisee models.
   * @return mixed
   */
  public function actionIndex()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('FranchiseeView')) {
      throw new ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
    }

    $searchModel = new FranchiseeSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


    $canCreate = Yii::$app->user->can('FranchiseeCreate');
    $actions = "";
    $actions .= Yii::$app->user->can('FranchiseeDelete') ? "{blocked}" : "";
    $actions .= Yii::$app->user->can('FranchiseeUpdate') ? "{update}" : "";
    $afterTable = '';

    $columns = include(__DIR__ . '/../views/admin/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_Franchisee", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_Franchisee");
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
        'afterTable' => $afterTable,
        'title' => Yii::t('app', 'Franchises'),
        'forAllCafe' => true,]);
  }


  /**
   * Config column in Franchisee model.
   * @param integer $id
   * @return mixed
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionColumns()
  {
    $model = new Franchisee();
    $searchModel = new FranchiseeSearch();

    $request = Yii::$app->request;

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException('Page does not exist');
      return false;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($request->post('column')) {
      $col = $request->post('column');

      if (Yii::$app->user->isGuest) {
        Yii::$app->session->set("columns_Franchisee", $col);
      } else {
        $user = Yii::$app->getUser()->getIdentity();
        $user->setActiveColumn("columns_Franchisee", $col);
      }

      return [
          'forceReload' => '#crud-datatable-pjax',
          'forceClose' => 'true',
          'content' => Yii::$app->view->closeModal(),
      ];
    }
    $actions = "";
    $columns = include(__DIR__ . '/../views/admin/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_Franchisee", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_Franchisee");
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
        'title' => Yii::t('app', 'Change visible columns in Franchisee table'),
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
   * Creates a new Franchisee model.
   * For ajax request will return json object
   * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate()
  {
    $request = Yii::$app->request;
    $model = new Franchisee();

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException('Page does not exist');
      return false;
    }

    $model->max_cafe = 1;
    /*
    *   Process for ajax request
    */
    Yii::$app->response->format = Response::FORMAT_JSON;
    if ($request->isGet) {
      return [
          'title' => Yii::t('app', 'Create new Franchisees'),
          'content' => $this->renderAjax('create', [
              'model' => $model,
              'isAjax' => true,
          ]),
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"]),
      ];
    } else if ($model->load($request->post()) && $model->save()) {
      \Yii::$app->session->addFlash('success', Yii::t('app', 'Franchisee success create'));
      // If User can create Cafes - show him direct link to "Create cafe" with current Franchisee set in
      if (Yii::$app->user->can('CafeCreate')) {
        return [
            'forceReload' => '#crud-datatable-pjax',
            'title' => Yii::t('app', 'Create new Franchisees'),
            'modalRedirect' => '/cafe/admin/create?franchisee_id=' . $model->id
        ];
      }

      return $this->returnBlank(
          [
              'forceReload' => '#crud-datatable-pjax',
              'title' => Yii::t('app', 'Create new Franchisees'),
          ]
      );
    } else {
      return [
          'title' => Yii::t('app', 'Create new Franchisees'),
          'content' => $this->renderAjax('create', [
              'model' => $model,
              'isAjax' => true,
          ]),
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"]),

      ];
    }
  }

  /**
   * Updates an existing Franchisee model.
   * For ajax request will return json object
   * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdate($id)
  {

    $request = Yii::$app->request;
    $model = $this->findModel($id);

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    /*
    *   Process for ajax request
    */
    $title = Yii::t('app', 'Update Franchisee: {nameAttribute}', ['nameAttribute' => $model->name]);
    Yii::$app->response->format = Response::FORMAT_JSON;
    if ($request->isGet) {
      return [
          'title' => $title,
          'content' => $this->renderAjax('update', [
              'model' => $model,
              'isAjax' => true,
          ]),
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"]),
      ];
    } else if ($model->load($request->post()) && $model->save()) {
      return $this->returnBlank(
          [
              'forceReload' => '#crud-datatable-pjax',
              'title' => $title,
          ],
          Yii::t('app', 'Franchisee updated successfully')
      );
    } else {
      return [
          'title' => $title,
          'content' => $this->renderAjax('update', [
              'model' => $model,
              'isAjax' => true,
          ]),
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"]),
      ];
    }
  }

  /**
   * Finds the Franchisee model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Franchisee the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Franchisee::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException(Yii::t('app', 'Page does not exist'));
    }
  }
}
