<?php

namespace frontend\modules\cafe\controllers;

use frontend\components\Controller;
use frontend\modules\cafe\models\Cafe;
use frontend\modules\cafe\models\CafeAuthItem;
use frontend\modules\cafe\models\CafeSearch;
use frontend\modules\cafe\models\DiscountUpdateForm;
use frontend\modules\franchisee\models\Franchisee;
use frontend\modules\users\models\Users;
use johnitvn\ajaxcrud\CrudAsset;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\PjaxAsset;

/**
 * AdminController implements the CRUD actions for Cafe model.
 */
class AdminController extends Controller
{

  private $def_sel_column = [
      'name',
      'max_person',
      'address',
      'tps_code',
      'tvq_code',
      'last_task',
      'tps_value',
      'tvq_value',
      'currency',
      'role_ids'
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
        ],
    ];
  }

  /**
   * Lists all Cafe models.
   * @return mixed
   */
  public function actionIndex()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('CafeView')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $searchModel = new CafeSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    $canCreate = Yii::$app->user->can('CafeCreate');
    $actions = "";
    $actions .= Yii::$app->user->can('CafeUpdate') ? "{update}" : "";
    $actions .= Yii::$app->user->can('CafeSetParam') ? "{vat_accounts}" : "";
    $actions .= Yii::$app->user->can('CafeRulesSet') ? "{rules}" : "";
    //$actions .= Yii::$app->user->can('FranchiseeDiscountUpdate') ? "{discounts}" : "";
	$actions .= Yii::$app->user->can('CafeDiscountUpdate') ? "{discounts}" : "";
    $afterTable = '';

    $columns = include(__DIR__ . '/../views/admin/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_Cafe", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_Cafe");
    }
    if (!$sel_column) {
      $sel_column = $this->def_sel_column;
    }

    $remove_column = array();
    if (!Yii::$app->user->can('AllFranchisee')) {
      $remove_column[] = "franchisee_id";
    }

    foreach ($columns as $k => $column) {
      $column_name = !is_array($column) ? $column : (isset($column['attribute']) ? $column['attribute'] : false);
      if ($column_name && (!in_array($column_name, $sel_column) || in_array($column_name, $remove_column))) {
        unset($columns[$k]);
      }
    }

    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'columns' => $columns,
        'canCreate' => $canCreate,
        'afterTable' => $afterTable,
        'title' => Yii::t('app', "Cafe list"),
        'forAllCafe' => true,
    ]);
  }


  /**
   * Config column in Cafe model.
   * @param integer $id
   * @return mixed
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionColumns()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('CafeView')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $model = new Cafe();
    $searchModel = new CafeSearch();

    $request = Yii::$app->request;

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($request->post('column')) {
      $col = $request->post('column');

      if (Yii::$app->user->isGuest) {
        Yii::$app->session->set("columns_Cafe", $col);
      } else {
        $user = Yii::$app->getUser()->getIdentity();
        $user->setActiveColumn("columns_Cafe", $col);
      }

      return [
          'forceReload' => '#crud-datatable-pjax',
          'forceClose' => true,
          'content' => Yii::$app->view->blankPage(),
      ];
    }
    $actions = "";
    $columns = include(__DIR__ . '/../views/admin/_columns.php');
    if (Yii::$app->user->isGuest) {
      $sel_column = Yii::$app->session->get("columns_Cafe", false);
    } else {
      $user = Yii::$app->getUser()->getIdentity();
      $sel_column = $user->getActiveColumn("columns_Cafe");
    }
    if (!$sel_column) {
      $sel_column = $this->def_sel_column;
    }

    $remove_column = array();
    if (!Yii::$app->user->can('AllFranchisee')) {
      $remove_column[] = "franchisee_id";
    }

    foreach ($columns as $k => $column) {
      $column_name = !is_array($column) ? $column : (isset($column['attribute']) ? $column['attribute'] : false);
      if (!$column_name || in_array($column_name, $remove_column)) {
        unset($columns[$k]);
      } else {
        $columns[$k] = $column_name;
      }
    }

    return [
        'title' => Yii::t('app', "Change visible columns in Cafe table"),
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
   * Creates a new Cafe model.
   * For ajax request will return json object
   * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate($franchisee_id = null)
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('CafeCreate')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    $request = Yii::$app->request;
    $model = new Cafe();

    // If we got predefined frinchisee - set it
    if ($franchisee_id !== null) {
      $model->franchisee_id = $franchisee_id;
    } else if (Yii::$app->user->can('AllFranchisee')) {

    } else {
      $franchisee_id = Yii::$app->cafe->franchiseeId;
      $count_cafe = Cafe::find()
          ->andWhere([
              'franchisee_id' => $franchisee_id
          ])->count();

      $franchasee = Franchisee::find()
          ->where(['id' => $franchisee_id])
          ->one();

      if ($franchasee->max_cafe <= $count_cafe) {
        Yii::$app->session->addFlash('error', Yii::t('app', 'Created the maximum number of cafes.'));

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => Yii::t('app', "Create new Cafe"),
            'content' => '<h6 class="text-danger font-weight-400">'. Yii::t('app', 'Created the maximum number of cafes.') .'</h6>'
        ];
      }
    }

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    if ($request->isAjax) {
      /*
      *   Process for ajax request
      */
      Yii::$app->response->format = Response::FORMAT_JSON;
      if ($request->isGet) {
        return [
            'title' => Yii::t('app', "Create new Cafe"),
            'content' => $this->renderAjax('create', [
                'model' => $model,
                'isAjax' => true
            ]),
            'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"])
        ];
      } else if ($model->load($request->post()) && $model->save()) {

        \Yii::$app->session->addFlash('success', Yii::t('app', 'Create Cafe success'));

        if (Yii::$app->user->can('UsersCreate')) {
          $user_admins_count = Users::find()
              ->where(['franchisee_id' => $model->franchisee_id])
              ->leftJoin('auth_assignment', 'user.id= auth_assignment.user_id')
              ->andWhere('auth_assignment.item_name=\'admin\'')
              ->count();

          if ($user_admins_count == 0) {
            return [
                'forceReload' => '#crud-datatable-pjax',
                'title' => Yii::t('app', 'Create new Cafe'),
                'modalRedirect' => '/users/admin/create?franchisee_id=' . $model->franchisee_id . '&roles=admin'
            ];
          };
        }

        return $this->returnBlank(
            [
                'forceReload' => '#crud-datatable-pjax',
                'title' => Yii::t('app', "Create new Cafe"),
            ]
        );
      } else {
        return [
            'title' => Yii::t('app', "Create new Cafe"),
            'content' => $this->renderAjax('create', [
                'model' => $model,
                'isAjax' => true,
            ]),
            'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"])

        ];
      }
    } else {
      /*
      *   Process for non-ajax request
      */
      if ($model->load($request->post()) && $model->save()) {
        return $this->redirect(['index']);
      } else {
        return $this->render('create', [
            'model' => $model,
        ]);
      }
    }

  }

  /**
   * Updates an existing Cafe model.
   * For ajax request will return json object
   * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdate($id)
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('CafeUpdate')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $request = Yii::$app->request;
    $model = $this->findModel($id);

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }
    if ($request->isAjax) {
      /*
      *   Process for ajax request
      */
      $title = Yii::t('app', 'Update Cafe: {nameAttribute}', [
          'nameAttribute' => '' . $model->name,
      ]);
      Yii::$app->response->format = Response::FORMAT_JSON;
      if ($request->isGet) {
        return [
            'title' => $title,
            'size' => 'large',
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
            Yii::t('app', 'Cafe updated successfully')
        );
      } else {
        return [
            'title' => $title,
            'model' => $model,
            'content' => $this->renderAjax('update', [
                'model' => $model,
                'isAjax' => true,
            ]),
            'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"])
        ];
      }
    } else {
      /*
      *   Process for non-ajax request
      */
      if ($model->load($request->post()) && $model->save()) {
        return $this->redirect(['index']);
      } else {
        return $this->render('update', [
            'model' => $model,
        ]);
      }
    }
  }

  /**
   * Updates an existing Cafe model.
   * For ajax request will return json object
   * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionSelfmodeBanner()
  {
    if (Yii::$app->user->isGuest ||
        !Yii::$app->user->can('Announcement') ||
        !Yii::$app->cafe->can('Announcement') ||
        !(
            Yii::$app->cafe->can('selfServiceHybridMode') ||
            Yii::$app->cafe->can('selfServiceLoginOnlyMode')
        )
    ) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $id = Yii::$app->cafe->id;
    $request = Yii::$app->request;
    $model = $this->findModel($id);

    if ($request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    /*
    *   Process for ajax request
    */
    $title = Yii::t('app', 'Update selfmode baner', [
        'nameAttribute' => '' . $model->name,
    ]);

    if ($request->isPost) {
      if ($model->load($request->post()) && $model->save()) {
        Yii::$app->session->addFlash('success', Yii::t('app', 'Data saved successfully'));
        return $this->refresh();
      } else {
        Yii::$app->session->addFlash('error', Yii::t('app', 'Error saving data'));
      }
    }

    return $this->render('update_banner', [
        'model' => $model,
        'title' => $title,
    ]);
  }

  public function actionUpdateVat($params_id, $id = false)
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('CafeSetParam')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return true;
    }

    $request = Yii::$app->request;
    if (!$request->isAjax && !YII_DEBUG) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return true;
    }

    if (!empty($id)) {
      $model = $this->findModel($id);
      if (!$model) {
        throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
        return true;
      }
    } else {
      $model = new Cafe();
    }

    $model->params_id = $params_id;

    return $this->renderAjax('vat_accounts', [
        'model' => $model,
        'isAjax' => true,
    ]);
  }

  public function actionUpdateRules($id)
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('CafeRulesSet')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return true;
    }

    $request = Yii::$app->request;
    $model = $this->findModel($id);

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return true;
    }

    $model->role_ids = ArrayHelper::getColumn($model->authItems, 'name');

    /*
    *   Process for ajax request
    */
    $title = Yii::t('app', 'Update Rules for Cafe: {nameAttribute}', [
        'nameAttribute' => '' . $model->name,
    ]);

    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($request->isGet) {
      return [
          'title' => $title,
          'content' => $this->renderAjax('rules', [
              'model' => $model,
              'isAjax' => true,
          ]),
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"]),
      ];
    } else if ($model->load($request->post()) && $model->save()) {
      $model->unlinkAll('authItems', true);
      foreach ($model->role_ids as $role_id) {
        $role = CafeAuthItem::findOne($role_id);
        if ($role !== null) {
          $model->link('authItems', $role);
        }
      }

      return $this->returnBlank(
          [
              'forceReload' => '#crud-datatable-pjax',
              'title' => $title,
          ],
          Yii::t('app', 'Rules update success')
      );
    } else {
      return [
          'title' => $title,
          'model' => $model,
          'content' => $this->renderAjax('rules', [
              'model' => $model,
              'isAjax' => true,
          ]),
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"]),
      ];
    }

  }

  public function actionUpdateDiscounts($id)
  {
    //if (Yii::$app->user->isGuest || !Yii::$app->user->can('FranchiseeDiscountUpdate')) {
	if (Yii::$app->user->isGuest || !Yii::$app->user->can('CafeDiscountUpdate')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return true;
    }

    $request = Yii::$app->request;
    $cafe = $this->findModel($id);

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return true;
    }

    $model = new DiscountUpdateForm($cafe);

    /*
    *   Process for ajax request
    */
    $title = Yii::t('app', 'Update Discounts for Cafe: {nameAttribute}', [
        'nameAttribute' => '' . $cafe->name,
    ]);

    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($request->isGet) {
      return [
          'title' => $title,
          'content' => $this->renderAjax('discounts', [
              'cafe' => $cafe,
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
          Yii::t('app', 'Discounts updated successfully')
      );
    } else {
      return [
          'title' => $title,
          'model' => $model,
          'content' => $this->renderAjax('discounts', [
              'cafe' => $cafe,
              'model' => $model,
              'isAjax' => true,
          ]),
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'type' => "submit"]),
      ];
    }
  }

  public function actionConfiguration()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('CafeUpdate')) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return true;
    }

    $request = Yii::$app->request;
    if (!$request->isAjax || !$request->isGet) {
      return $this->goHome();
      //throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      //return true;
    }


    CrudAsset::register(Yii::$app->view);
    PjaxAsset::register(Yii::$app->view);

    $cafe = Yii::$app->cafe->get();
    $data = $cafe->testSuccessful();

    $title = Yii::t('app', $cafe->initSuccessful ? 'Configuration' : 'Start init');
    $content = $this->renderAjax('configuration', [
        'data' => $data,
        'initSuccessful' => $cafe->initSuccessful,
    ]);

    if ($request->get('_pjax')) {
      if ($cafe->initSuccessful) {
        $content .= '<script>modal_open(\'/cafe/admin/configuration\')</script>';
      }
      return $content;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;
    $footer = '';
    if ($cafe->initSuccessful) {
      $footer .= Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]);
    } else {
      $footer .= Html::a(
          ' <i class="icon-metro-exit"></i> ' . Yii::t('main', 'LOGOUT'),
          "/logout",
          [
              'class' => "btn bg-scarlet fg-white pull-left",
          ]
      );
    }

    if (count(Users::getCafesList()) > 1) {
      $footer .= Html::a(
          ' <i class="fa fa-cube"></i> <i class="fa fa-exchange"></i> <i class="fa fa-cube"></i> ' . Yii::t('app', 'Change cafe'),
          "/change-cafe",
          [
              'role' => "modal-new",
              'class' => "btn btn-science-blue ch_cafe",
          ]
      );
    }

    return [
        'title' => $title,
        'closeButton' => !!$cafe->initSuccessful,
        'content' => $content,
        'footer' => $footer,
      'reloadPageOnClose' => true,
    ];
  }

  /**
   * Finds the Cafe model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Cafe the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Cafe::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException(Yii::t('app', 'Page does not exist'));
    }
  }
}
