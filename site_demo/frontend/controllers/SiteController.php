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
                'logout' => ['post', 'get'], // Allow both for backwards compatibility, prefer POST
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

    // If user is logged in but no cafe selected, redirect to cafe selection
    if (!Yii::$app->cafe->id) {
      return $this->redirect(['/site/change-cafe']);
    }

    return $this->render('index', [
        'page_wrap' => 'index',
        'screen_class' => 'start-screen',
    ]);
  }

  public function actionDemo()
  {
    if (!Yii::$app->user->isGuest) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
    }

    $request = Yii::$app->request;

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
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

    // No cafes assigned - logout and show error
    if (count($cafe_list) == 0) {
      Yii::$app->user->logout();
      $session->addFlash('error', Yii::t('app', 'You do not have a free cafe. Contact your administrator.'));
      return $this->redirect(['/login']);
    }

    // User has only one cafe - auto-select it
    if (count($cafe_list) == 1) {
      // If cafe already set, redirect to home
      if ($session->get('cafe_id', false)) {
        return $this->goBack('/');
      }

      // Auto-set the only available cafe
      $cafe_id = $cafe_list[0]['id'];
      $session->set('cafe_id', $cafe_id);

      // Set cookie with proper security settings
      $cookies->add(new \yii\web\Cookie([
          'name' => 'cafe_id',
          'value' => $cafe_id,
          'httpOnly' => true,
          'secure' => Yii::$app->request->isSecureConnection,
          'sameSite' => \yii\web\Cookie::SAME_SITE_LAX,
          'expire' => time() + 30 * 24 * 60 * 60, // 30 days
      ]));

      // Re-initialize cafe component with selected cafe
      Yii::$app->cafe->init();

      // Auto-start session if needed
      if (!Yii::$app->user->can('AllCafeShow') &&
          Yii::$app->cafe->can('sessionAutoStart') &&
          !count(Yii::$app->cafe->getActiveUsers())
      ) {
        UserLog::sessionStart();
      }

      // Redirect to home page
      return $this->redirect(['/']);
    }

    // User has multiple cafes - handle POST selection
    if (Yii::$app->request->isPost && Yii::$app->request->post('cafe')) {

      $cafe_id = Yii::$app->request->post('cafe');
      $enterSelfService = Yii::$app->request->post('selfservice', null);

      // Validate that selected cafe is in user's cafe list
      foreach ($cafe_list as $cafe) {
        if ($cafe['id'] == $cafe_id) {
          // Set cafe in session
          $session->set('cafe_id', $cafe_id);

          // Set persistent cookie
          $cookies->add(new \yii\web\Cookie([
              'name' => 'cafe_id',
              'value' => $cafe_id,
              'httpOnly' => true,
              'secure' => Yii::$app->request->isSecureConnection,
              'sameSite' => \yii\web\Cookie::SAME_SITE_LAX,
              'expire' => time() + 30 * 24 * 60 * 60, // 30 days
          ]));

          // Handle self-service mode
          if ($enterSelfService) {
            return $this->redirect(['/selfservice/default/index']);
          }

          // Re-initialize Cafe component with selected cafe
          Yii::$app->cafe->init();

          // Auto-start session if needed
          if (!Yii::$app->user->can('AllCafeShow') && Yii::$app->cafe->can('sessionAutoStart')) {
            UserLog::sessionStart();
          }

          // Return appropriate response
          if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => Yii::t('app', 'Change cafe'),
                'content' => Yii::$app->view->reloadPage(),
                'footer' => "",
            ];
          } else {
            return $this->redirect(['/']);
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

  public function actionNeedpay()
  {
    $request = Yii::$app->request;
    if (Yii::$app->user->isGuest || !$request->isAjax) {
      return $this->goHome();
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    return [
        'title' => Yii::t('app', 'Need pay'),
        'content' => $this->renderAjax('needpay'),
        'size' => 'normal',
        'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
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
      $out['modal'] = '/needpay';
    }

    Yii::$app->response->format = Response::FORMAT_JSON;
    return $out;
  }

  public function actionTpls()
  {
    $request = Yii::$app->request;
    Yii::$app->response->format = Response::FORMAT_JSON;

    // User must be logged in to access templates
    if (Yii::$app->user->isGuest) {
      return [];
    }

    // If no cafe selected, return empty (this is intentional for security)
    // The frontend will detect this and prompt for cafe selection
    if (!Yii::$app->cafe->id) {
      return [];
    }

    $out = [];
    $path = Yii::$app->viewPath . '/browser/';

    if (!is_dir($path)) {
      Yii::error("Browser templates directory not found: $path", __METHOD__);
      return [];
    }

    $files = scandir($path);
    foreach ($files as $key => $value) {
      if (!in_array($value, array(".", "..")) && pathinfo($value, PATHINFO_EXTENSION) === 'twig') {
        $name = str_replace('.twig', '', $value);
        $filePath = $path . $value;
        if (is_readable($filePath)) {
          $out[$name] = file_get_contents($filePath);
        }
      }
    }

    return $out;
  }

  /**
   * Queue worker status endpoint
   * Shows queue health: pending jobs, failed jobs, worker process status
   * Access: /queue-status
   */
  public function actionQueueStatus()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $status = [
      'timestamp' => date('Y-m-d H:i:s'),
      'queue' => [
        'connected' => false,
        'waiting' => 0,
        'delayed' => 0,
        'reserved' => 0,
        'done' => 0,
      ],
      'worker' => [
        'running' => false,
        'pid' => null,
        'log_exists' => false,
        'recent_errors' => [],
      ],
    ];

    try {
      // Check queue database table
      $queue = Yii::$app->queue;
      if ($queue) {
        $status['queue']['connected'] = true;

        // Count jobs by status
        $db = Yii::$app->db;
        $status['queue']['waiting'] = (int)$db->createCommand(
          'SELECT COUNT(*) FROM {{%queue}} WHERE reserved_at IS NULL AND done_at IS NULL'
        )->queryScalar();

        $status['queue']['reserved'] = (int)$db->createCommand(
          'SELECT COUNT(*) FROM {{%queue}} WHERE reserved_at IS NOT NULL AND done_at IS NULL'
        )->queryScalar();

        $status['queue']['done'] = (int)$db->createCommand(
          'SELECT COUNT(*) FROM {{%queue}} WHERE done_at IS NOT NULL AND DATE(done_at) = CURDATE()'
        )->queryScalar();
      }
    } catch (\Exception $e) {
      $status['queue']['error'] = $e->getMessage();
    }

    // Check if worker process is running
    if (function_exists('shell_exec') && !in_array('shell_exec', explode(',', ini_get('disable_functions')))) {
      // Safe command - no user input, only literal strings
      $processes = shell_exec('ps aux | grep "yii queue/listen" | grep -v grep 2>/dev/null');
      if ($processes && trim($processes) !== '') {
        $status['worker']['running'] = true;
        // Extract PID (validate it's actually numeric)
        if (preg_match('/^\S+\s+(\d+)/', $processes, $matches)) {
          $status['worker']['pid'] = (int)$matches[1];
        }
      }
    }

    // Check if log file exists and get recent errors
    $logFile = '/var/log/queue-worker.log';
    if (file_exists($logFile) && is_readable($logFile)) {
      $status['worker']['log_exists'] = true;

      // Get last 50 lines and filter for errors - escape the file path
      $escapedLogFile = escapeshellarg($logFile);
      $lines = shell_exec("tail -n 50 {$escapedLogFile} 2>/dev/null");
      if ($lines) {
        $errorLines = array_filter(
          explode("\n", $lines),
          function($line) {
            return stripos($line, 'error') !== false ||
                   stripos($line, 'exception') !== false ||
                   stripos($line, 'failed') !== false;
          }
        );
        $status['worker']['recent_errors'] = array_values(array_slice($errorLines, -5));
      }
    }

    // Overall health check
    $status['healthy'] =
      $status['queue']['connected'] &&
      ($status['worker']['running'] || $status['queue']['waiting'] == 0);

    return $status;
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
