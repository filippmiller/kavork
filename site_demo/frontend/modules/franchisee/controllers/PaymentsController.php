<?php

namespace frontend\modules\franchisee\controllers;

use common\components\widget\BulkButtonWidget;
use frontend\components\Controller;
use frontend\modules\franchisee\models\Franchisee;
use frontend\modules\franchisee\models\FranchiseePayments;
use frontend\modules\franchisee\models\FranchiseePaymentsSearch;
use frontend\modules\franchisee\models\FranchiseeRegistration;
use frontend\modules\franchisee\models\FranchiseeTariffs;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * PaymentsController implements the CRUD actions for FranchiseePayments model.
 */
class PaymentsController extends Controller
{

  private $def_sel_column = [
      'id',
      'franchisee_id',
    //'code',
      'status',
      'tariff_id',
    //'comment',
      'count',
      'sum',
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
   * Lists all FranchiseePayments models.
   * @return mixed
   */
  public function actionIndex()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('FranchiseePaymentsView')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $searchModel = new FranchiseePaymentsSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    $canCreate = Yii::$app->user->can('FranchiseePaymentsCreate');
    $actions = "";
    $actions .= Yii::$app->user->can('FranchiseePaymentsUpdate') ? "{update}" : "";
    $actions .= Yii::$app->user->can('FranchiseePaymentsDelete') ? "{delete}" : "";
    $panelButtons = '';
    if (Yii::$app->user->can('FranchiseePaymentsDelete')) {
      $panelButtons = BulkButtonWidget::widget();
    };
    $columns = include(__DIR__ . '/../views/payments/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_FranchiseePayments", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_FranchiseePayments");
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

    $franchasee = Franchisee::find()
        ->where(['id' => Yii::$app->cafe->getFranchiseeId()])
        ->one();

    $tariff = $franchasee->tariff_id ?
        FranchiseeTariffs::find()
            ->where(['id' => $franchasee->tariff_id])
            ->one() : null;


    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'franchasee' => $franchasee,
        'user' => Yii::$app->user->identity,
        'isRoot' => Yii::$app->user->can('root'),
        'tariff' => $tariff,
        'columns' => $columns,
        'canCreate' => $canCreate,
        'panelButtons' => $panelButtons,
        'title' => Yii::t('app', 'Personal Account'),
        'forAllCafe' => true,]);
  }


  /**
   * Config column in FranchiseePayments model.
   * @param integer $id
   * @return mixed
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionColumns()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('FranchiseePaymentsView')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $model = new FranchiseePayments();
    $searchModel = new FranchiseePaymentsSearch();

    $request = Yii::$app->request;

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($request->post('column')) {
      $col = $request->post('column');

      if (Yii::$app->user->isGuest) {
        Yii::$app->session->set("columns_FranchiseePayments", $col);
      } else {
        $user = Yii::$app->getUser()->getIdentity();
        $user->setActiveColumn("columns_FranchiseePayments", $col);
      }

      return [
          'forceReload' => '#crud-datatable-pjax',
          'content' => Yii::$app->view->closeModal(),
          'forceClose' => 'true',
      ];
    }
    $actions = "";
    $columns = include(__DIR__ . '/../views/payments/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_FranchiseePayments", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_FranchiseePayments");
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
        'title' => Yii::t('app', 'Change visible columns in FranchiseePayments table'),
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
   * Creates a new FranchiseePayments model.
   * For ajax request will return json object
   * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate($franchisee_id = 0)
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('FranchiseePaymentsCreate')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist') . ' 0');
      return false;
    }
    $request = Yii::$app->request;
    $model = new FranchiseePayments();

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist') . ' 1');
      return false;
    }

    if (!Yii::$app->user->can('AllFranchisee')) {
      $franchisee_id = Yii::$app->cafe->franchiseeId;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    if (!$franchisee_id) {
      if ($request->isGet) {
        $franchisee = Franchisee::find()
            ->select(['id','name'])
            ->asArray()
            ->all();
        if(empty($model->franchisee_id)){
          $model->franchisee_id = Yii::$app->cafe->franchiseeId;
        }
        $data = [
            'model' => $model,
            'franchisee'=>ArrayHelper::map($franchisee, 'id', 'name'),
        ];
        return [
            'title' => Yii::t('app', 'Select Franchisee for new payment'),
            'content' => $this->renderAjax('franchisee_change', $data),
            'size' => 'normal',
            'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                Html::button('' .Yii::t('app', 'Next'). ' <i class="fa fa-angle-double-right"></i>', ['class' => 'btn btn-primary', 'type' => "submit"])
        ];
      } else {
        $franchisee_id = $request->post('franchisee_id');
      }
    }

    $tariffs = FranchiseeTariffs::find()
        ->where([
            'active' => FranchiseeTariffs::ACTIVE_YES
        ])
        ->all();

    $franchisee = Franchisee::find()
        ->where(['id' => $franchisee_id])
        ->one();

    if (!$franchisee) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }


    $model->franchisee_id = $franchisee_id;
    $model->tariff_id = $franchisee->tariff_id;

    /*
    *   Process for ajax request
    */
    if ($request->isGet) {
      return [
          'title' => Yii::t('app', 'Create new FranchiseePayments'),
          'content' => $this->renderAjax('create', [
              'model' => $model,
              'tariffs' => $tariffs,
              'franchisee' => $franchisee,
              'isAjax' => true,
          ]),
          'size' => 'large',
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Buy'), ['class' => 'btn btn-primary', 'type' => "submit"])
      ];
    } else if (
        $model->load($request->post()) &&
        $model->validate() &&
        $model->makePay() &&
        $model->save(false)
    ) {
      return
          [
              'redirect' => $model->payment_url
          ];
    } else {
      return [
          'title' => Yii::t('app', 'Create new FranchiseePayments'),
          'content' => $this->renderAjax('create', [
              'model' => $model,
              'tariffs' => $tariffs,
              'franchisee' => $franchisee,
              'isAjax' => true,
          ]),
          'size' => 'large',
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
			  Html::button(' ', ['style' => 'background: url(/img/paypal.svg);background-size: cover;
  background-position: center;width: 120px; height: 37px;', 'class' => 'btn btn-default', 'type' => "submit"])

      ];
    }
  }

  public function actionNew($id)
  {
    if (!Yii::$app->user->isGuest) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $request = Yii::$app->request;

    if (!$request->isAjax) {
      return $this->goHome();
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    $tariff = FranchiseeTariffs::find()
        ->where([
            'active' => FranchiseeTariffs::ACTIVE_YES,
            'id' => $id
        ])
        ->one();

    if (!$tariff) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $franchasee = new FranchiseeRegistration();
    $franchasee->tariff = $tariff;

    if ($request->isPost) {
      if (
          $franchasee->load($request->post()) &&
          $franchasee->validate() &&
          $franchasee->preparePayment() &&
          $franchasee->payment->makePay() &&
          $franchasee->payment->save()
      ) {
        return
            [
                'redirect' => $franchasee->payment->payment_url
            ];
      }
    }

    return [
        'title' => Yii::t('app', 'Registration account'),
        'content' => $this->renderAjax('registration', [
            'model' => $franchasee
        ]),
		'size'=>'large',
        'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
            Html::button(' ', ['style' => 'background: url(img/paypal.svg);background-size: cover;
  background-position: center;width: 120px; height: 37px;', 'class' => 'btn btn-default', 'type' => "submit"])
    ];
  }

  /**
   * Finds the FranchiseePayments model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return FranchiseePayments the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = FranchiseePayments::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException(Yii::t('app', 'Page does not exist'));
    }
  }
}
