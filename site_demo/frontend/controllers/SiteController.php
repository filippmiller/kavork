<?php

namespace frontend\controllers;

use frontend\components\Controller;
use frontend\models\ContactForm;
use frontend\modules\franchisee\models\FranchiseeTariffs;
use frontend\modules\tasks\models\Task;
use frontend\modules\users\models\LoginForm;
use frontend\modules\users\models\UserLog;
use frontend\modules\users\models\Users;
use frontend\modules\visits\models\VisitorLog;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
  /**
   * {@inheritdoc}
   */
  public function behaviors()
  {
    return [
        'access' => [
            'class' => AccessControl::className(),
            'only' => ['logout', 'signup'],
            'rules' => [
                [
                    'actions' => ['signup'],
                    'allow' => true,
                    'roles' => ['?'],
                ],
                [
                    'actions' => ['logout'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ],
        'verbs' => [
            'class' => VerbFilter::className(),
            'actions' => [
                'logout' => ['get'],
            ],
        ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function actions()
  {
    //ddd(Yii::$app->cafe->get()->testSuccessful(),Yii::$app->cafe);
    return [
        'error' => [
            'class' => 'yii\web\ErrorAction',
        ],
        'captcha' => [
            'class' => 'yii\captcha\CaptchaAction',
            'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
        ],
    ];
  }

  /**
   * Displays homepage.
   *
   * @return mixed
   */
  public function actionIndex()
  {
    if (Yii::$app->user->isGuest) {
      if (empty(Yii::$app->params['mainLanding'])) {
        return $this->actionLogin();
      } else {
        Yii::$app->layout = 'blank';
        $tariffs = FranchiseeTariffs::find()
            ->where([
                'active' => FranchiseeTariffs::ACTIVE_YES
            ])
            ->all();

        $model = new ContactForm();
        /* получаем данные из формы и запускаем функцию отправки contact, если все хорошо, выводим сообщение об удачной отправке сообщения на почту */
        if (
            $model->load(Yii::$app->request->post()) &&
            $model->contact(Yii::$app->params['emailto'])
        ) {
          return $this->renderAjax('contactFormThanks',[
              'contactForm'=>$model
          ]);
        }

        if(Yii::$app->request->isAjax){
          return $this->renderAjax('contactForm',[
              'contactForm'=>$model
          ]);
        }
        return $this->render('landing', [
            'screen_class' => 'landing',
            'hide_lang_change_def' => true,
            'tariffs' => $tariffs,
            'contactForm'=>$model,
        ]);
      }
    };

    return $this->render('index', [
        'page_wrap' => 'index',
        'screen_class' => 'start-screen',
    ]);
  }

  public function actionDemo()
  {
    if (!Yii::$app->user->isGuest) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $request = Yii::$app->request;

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    return [
        'title' => Yii::t('app', 'Demo system'),
        'content' => $this->renderAjax('demo'),
        'size' => 'normal',
        'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
            Html::a(Yii::t('app', 'Go to demo'), 'http://demo.kavork.com/', [
                'class' => 'btn btn-primary',
                'type' => "submit",
                'target' => '_blank',
            ])
    ];
  }

  public function actionChangeCafe()
  {
    $cafe_list = Users::getCafesList();

    $session = Yii::$app->session;
    $cookies = Yii::$app->response->cookies;

    if (count($cafe_list) == 0) {
      Yii::$app->user->logout();
      $session->addFlash('error', Yii::t('app', 'You do not have a free cafe. Contact your administrator.'));
      return $this->redirect(['/login']);
    }

    if (count($cafe_list) == 1) {
      if ($session->get('cafe_id', false)) {
        return $this->goBack('/');
      }
      $cafe_id = $cafe_list[0]['id'];
      $session->set('cafe_id', $cafe_id);
      $cookies->add(new \yii\web\Cookie([
          'name' => 'cafe_id',
          'value' => $cafe_id,
      ]));
      Yii::$app->cafe->init();
      if (!Yii::$app->user->can('AllCafeShow') &&
          Yii::$app->cafe->can('sessionAutoStart') &&
          !count(Yii::$app->cafe->getActiveUsers())
      ) {
        UserLog::sessionStart();
      }

      return $this->refresh();
    }

    if (Yii::$app->request->isPost && Yii::$app->request->post('cafe')) {

      $cafe_id = Yii::$app->request->post('cafe');
      $enterSelfService = Yii::$app->request->post('selfservice', null);

      foreach ($cafe_list as $cafe) {
        if ($cafe['id'] == $cafe_id) {
          $session->set('cafe_id', $cafe_id);

          $cookies->add(new \yii\web\Cookie([
              'name' => 'cafe_id',
              'value' => $cafe_id,
          ]));

          if ($enterSelfService) {
            return $this->redirect(['/selfservice/default/index']);
          }

          // Re-init Cafe component
          Yii::$app->cafe->init();

          if (!Yii::$app->user->can('AllCafeShow') && Yii::$app->cafe->can('sessionAutoStart')) {
            UserLog::sessionStart();
          }

          if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => Yii::t('app', 'Change cafe'),
                'content' => Yii::$app->view->reloadPage(),
                'footer' => "",
            ];
          } else {
            return $this->refresh();
          }
        }
      }
    }

    if (Yii::$app->request->isAjax) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      return [
          'title' => Yii::t('app', 'Change cafe'),
          'content' => $this->renderAjax('change-cafe', [
              'cafe_list' => $cafe_list,
              'isModal' => true,
              'cafe_change' => Yii::$app->cafe->id,
          ]),
          'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
              Html::button(Yii::t('app', 'Select'), ['class' => 'btn btn-primary', 'type' => "submit"])

      ];
    }
    Yii::$app->layout = "form_page";

    return $this->render('change-cafe', [
        'cafe_list' => $cafe_list,
    ]);
  }

  public function actionNeadpay()
  {
    $request = Yii::$app->request;
    if (Yii::$app->user->isGuest || !$request->isAjax) {
      //throw new ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      //return false;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    return [
        'title' => Yii::t('app', 'Nead pay'),
        'content' => $this->renderAjax('neadpay'),
        'size' => 'normal',
        'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
            ''//Html::a(Yii::t('app', 'Go to demo'),'/', ['class' => 'btn btn-primary', 'type' => "submit"])
    ];
  }

  public function actionGet_cafe_status()
  {
    $request = Yii::$app->request;
    if (Yii::$app->user->isGuest || !Yii::$app->cafe->id || !$request->isAjax) {
      return $this->goHome();
    }

    $out = [
        'visitors' => VisitorLog::getUserInCafe(),
      //'wait_pay' => VisitorLog::getUserWaitPay()
    ];

    if (Yii::$app->cafe->can('adminLog')) {
      $out['users'] = UserLog::getUserInCafe();
    }


    if (Yii::$app->cafe->can('task')) {
      $out['showTask'] = Yii::$app->user->can('TaskOnStart');

      if (Yii::$app->user->can('TaskReminder')) {
        $out['tasks'] = Task::waitToDo();
      }
    }

    if (
        Yii::$app->session->get('showActiveUntil', 0) < time() &&
        !Yii::$app->cafe->testActiveUntil()
    ) {
      Yii::$app->session->set('showActiveUntil', time() + Yii::$app->params['modalNotificatorPeriod'] * 60);
      $out['modal'] = '/neadpay';
    }

    Yii::$app->response->format = Response::FORMAT_JSON;
    return $out;
  }

  public function actionTpls()
  {
    $request = Yii::$app->request;
    Yii::$app->response->format = Response::FORMAT_JSON;
    if (Yii::$app->user->isGuest || !Yii::$app->cafe->id && !$request->isAjax && !$request->isGet) {
      return [];
    }

    $out = [];
    $path = Yii::$app->viewPath . '/browser/';
    $files = scandir($path);
    foreach ($files as $key => $value) {
      if (!in_array($value, array(".", ".."))) {
        $name = str_replace('.twig', '', $value);
        $out[$name] = file_get_contents($path . $value);
      }
    }


    Yii::$app->response->format = Response::FORMAT_JSON;

    return $out;
  }

  /**
   * Logs in a user.
   *
   * @return mixed
   */
  public function actionLogin()
  {
    if (!Yii::$app->user->isGuest) {
      return $this->goHome();
    }

    Yii::$app->layout = "form_page";

    $model = new LoginForm();
    if ($model->load(Yii::$app->request->post()) && $model->login()) {
      // Setting cafe_id from cookie if not setted
      if (
          !Yii::$app->session->get('cafe_id', false) &&
          $cafe_id = Yii::$app->request->cookies->getValue('cafe_id', false)
      ) {
        Yii::$app->session->set('cafe_id', $cafe_id);
      }

      return $this->goHome();
    } else {
      $model->password = '';

      return $this->render('login', [
          'model' => $model,
      ]);
    }
  }

  /**
   * Logs out the current user.
   *
   * @return mixed
   */
  public function actionLogout()
  {
    Yii::$app->user->logout();
    return $this->goHome();
  }

  public function _actionTest()
  {
    $js = <<<JS
        editor.setData([{"type":"logo","data":{"align":"center","height":"120","padding-v":"20","padding-h":"40"}},{"type":"button","data":{"text":{"en-EN":"en-EN Button text","fr":"fr Button text","ru-RU":"ru-RU Button text"},"font":"Arial","href":{"en-EN":"en-EN ","fr":"fr ","ru-RU":"ru-RU "},"color":"#FFFFFF","background":"#006ac1","font_size":"25","padding-v":"20","padding-h":"40","border-r":"12"}}])
JS;
    Yii::$app->view->registerJs($js);

    return $this->render('editor', [

    ]);
  }
}
