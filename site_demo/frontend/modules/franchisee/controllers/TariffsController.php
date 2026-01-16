<?php

namespace frontend\modules\franchisee\controllers;

use common\components\widget\BulkButtonWidget;
use frontend\components\Controller;
use frontend\modules\franchisee\models\FranchiseeTariffs;
use frontend\modules\franchisee\models\FranchiseeTariffsSearch;
use Yii;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * TarifsController implements the CRUD actions for FranchiseeTariffs model.
 */
class TariffsController extends Controller
{

  private $def_sel_column = [
      'id',
      'name',
      'cafe_count',
      'roles',
      'day_price',
      'days_period',
      'active',
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
   * Lists all FranchiseeTariffs models.
   * @return mixed
   */
  public function actionIndex()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('FranchiseeTariffsView')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $searchModel = new FranchiseeTariffsSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    $canCreate = Yii::$app->user->can('FranchiseeTariffsCreate');
    $actions = "";
    $actions .= Yii::$app->user->can('FranchiseeTariffsUpdate') ? "{update}" : "";
    $actions .= Yii::$app->user->can('FranchiseeTariffsDelete') ? "{delete}" : "";
    $panelButtons = '';
    if (Yii::$app->user->can('FranchiseeTariffsDelete')) {
      $panelButtons = BulkButtonWidget::widget();
    };
    $columns = include(__DIR__ . '/../views/tariffs/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_FranchiseeTariffs", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_FranchiseeTariffs");
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
        'title' => Yii::t('app', 'FranchiseeTariffs list'),
        'forAllCafe' => true,]);
  }


  /**
   * Config column in FranchiseeTariffs model.
   * @param integer $id
   * @return mixed
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionColumns()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('FranchiseeTariffsView')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $model = new FranchiseeTariffs();
    $searchModel = new FranchiseeTariffsSearch();

    $request = Yii::$app->request;

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($request->post('column')) {
      $col = $request->post('column');

      if (Yii::$app->user->isGuest) {
        Yii::$app->session->set("columns_FranchiseeTariffs", $col);
      } else {
        $user = Yii::$app->getUser()->getIdentity();
        $user->setActiveColumn("columns_FranchiseeTariffs", $col);
      }

      return [
          'forceClose' => 'true',
          'forceReload' => '#crud-datatable-pjax',
          'content' => Yii::$app->view->closeModal(),
      ];
    }
    $actions = "";
    $columns = include(__DIR__ . '/../views/tariffs/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_FranchiseeTariffs", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_FranchiseeTariffs");
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
        'title' => Yii::t('app', 'Change visible columns in FranchiseeTariffs table'),
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
   * Creates a new FranchiseeTariffs model.
   * For ajax request will return json object
   * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('FranchiseeTariffsCreate')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $request = Yii::$app->request;

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $model = new FranchiseeTariffs();
    $model->active = FranchiseeTariffs::ACTIVE_YES;
    $model->days_period = 30;
    $model->cafe_count = 1;
    /*
    *   Process for ajax request
    */
    Yii::$app->response->format = Response::FORMAT_JSON;
    if ($request->isGet) {
      return [
          'title' => Yii::t('app', 'Create new FranchiseeTariffs'),
          'content' => $this->renderAjax('create', [
              'model' => $model,
              'isAjax' => true
          ]),
		  'size'=>'large',
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"])
      ];
    } else if ($model->load($request->post()) && $model->save()) {
      return $this->returnBlank(
          [
              'forceReload' => '#crud-datatable-pjax',
              'title' => Yii::t('app', 'Create new FranchiseeTariffs'),
          ],
          Yii::t('app', "Create FranchiseeTariffs success")
      );
    } else {
      return [
          'title' => Yii::t('app', 'Create new FranchiseeTariffs'),
          'content' => $this->renderAjax('create', [
              'model' => $model,
              'isAjax' => true,
          ]),
		  'size'=>'large',
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"])

      ];
    }
  }

  /**
   * Updates an existing FranchiseeTariffs model.
   * For ajax request will return json object
   * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdate($id)
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('FranchiseeTariffsUpdate')) {
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
    $title = Yii::t('app', 'Update Franchisee Tariffs: {id} ', [
        'id' => '' . $model->id,
    ]);
    Yii::$app->response->format = Response::FORMAT_JSON;
    if ($request->isGet) {
      return [
          'title' => $title,
          'content' => $this->renderAjax('update', [
              'model' => $model,
              'isAjax' => true,
          ]),
		  'size'=>'large',
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"])
      ];
    } else if ($model->load($request->post()) && $model->save()) {
      return $this->returnBlank(
          [
              'forceReload' => '#crud-datatable-pjax',
              'title' => $title
          ],
          Yii::t('app', "Update FranchiseeTariffs success")
      );
    } else {
      return [
          'title' => $title,
          'content' => $this->renderAjax('update', [
              'model' => $model,
              'isAjax' => true,
          ]),
		  'size'=>'large',
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"])
      ];
    }
  }

  /**
   * Delete an existing FranchiseeTariffs model.
   * For ajax request will return json object
   * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   */
  public function actionDelete($id)
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('FranchiseeTariffsDelete')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $request = Yii::$app->request;
    try{
      $this->findModel($id)->delete();
    }catch (Exception $e){
      Yii::$app->response->format = Response::FORMAT_JSON;
      return [
          'title' => Yii::t('app','Remote Error'),
          'content' => Yii::t('app','Could not remove tariff because it is used.'),
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]),
      ];
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
   * Delete multiple existing FranchiseeTariffs model.
   * For ajax request will return json object
   * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   */
  public function actionBulkDelete()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('FranchiseeTariffsDelete')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $request = Yii::$app->request;
    $pks = explode(',', $request->post('pks')); // Array or selected records primary keys
    $hasError = 0;
    foreach ($pks as $pk) {
      try{
        $model = $this->findModel($pk);
        $model->delete();
      }catch (Exception $e){
        $hasError++;
      }
    }

    if ($request->isAjax) {
      /*
      *   Process for ajax request
      */
      Yii::$app->response->format = Response::FORMAT_JSON;

      if($hasError){
        return [
            'title' => Yii::t('app','Remote Error'),
            'content' => Yii::t('app','Could not remove {n} tariff because it is used.',[
                'n'=>$hasError,
            ]),
            'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]),
        ];
      }else{
        return ['forceClose' => true, 'forceReload' => '#crud-datatable-pjax'];
      }
    } else {
      /*
      *   Process for non-ajax request
      */
      return $this->redirect(['index']);
    }

  }

  /**
   * Finds the FranchiseeTariffs model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return FranchiseeTariffs the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = FranchiseeTariffs::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException(Yii::t('app', 'Page does not exist'));
    }
  }
}
