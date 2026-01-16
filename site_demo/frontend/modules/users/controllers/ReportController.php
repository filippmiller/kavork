<?php

namespace frontend\modules\users\controllers;

use frontend\components\Controller;
use frontend\modules\users\models\Users;
use Yii;
use yii\db\Query;
use yii\helpers\Html;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * LogController implements the CRUD actions for UserLog model.
 */
class ReportController extends Controller
{

  public function actionIndex($date = false)
  {
    if (Yii::$app->user->isGuest || !Yii::$app->cafe->id || !Yii::$app->cafe->can('adminReport') || !Yii::$app->user->can('UserLogWeakReport')) {
      throw new ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
    }

    if (!$date) {
      $date = date("Y/m/d");
    }

    $time = strtotime($date);
    $d = Yii::$app->cafe->first_weekday - date('w', $time);
    $time_start = $time + $d * 24 * 60 * 60;
    $time_end = $time_start + 7 * 24 * 60 * 60 - 1;

    $date_start_sql = date('Y-m-d', $time_start);
    $date_end_sql = date('Y-m-d', $time_end);

    $date_start = date(Yii::$app->params['lang']['date'], $time_start);
    $date_end = date(Yii::$app->params['lang']['date'], $time_end);

    $date_next = date('Y/m/d', $time_start - 7 * 24 * 60 * 60);
    $date_prev = date('Y/m/d', $time_end + 1);

    Yii::$app->response->format = Response::FORMAT_JSON;

    $select = [
        'user_id' => 'user_id',
        'weakday' => 'DAYOFWEEK(finish)',
        'finish' => 'date(finish)',
        'duration' => 'sum(TIME_TO_SEC(timediff(finish, start)))'
    ];

    $days = [];
    for ($index = 0; $index < 7; $index++) {
      $day = date(
          Yii::$app->params['lang']['date'],
          $time_start + $index * 24 * 60 * 60);
      $days[$day] = date(
          'l',
          $time_start + $index * 24 * 60 * 60);
      $days[$day] = Yii::t('app', $days[$day]);
      //$i = $index + 1;
      //$select[$day] = "if (DAYOFWEEK(finish) = {$i}, TIME_TO_SEC(timediff(IFNULL(finish, :finish_date ), start)), null)";
      //$select[$day] = "if (DAYOFWEEK(finish) = {$i}, TIME_TO_SEC(timediff(finish, start)), null)";
    }

    $models_prew = (new Query())
        ->select($select)
        ->from('user_log')
        ->leftJoin('cafe', 'cafe.id=user_log.cafe_id')
        ->where("date(finish) >= :date_start AND date(finish) <= :date_finish AND cafe.franchisee_id = :franchisee_id")
        ->groupBy(['user_id', 'DAYOFWEEK(finish)'])
        ->orderBy('finish')
        ->params([
          //'finish_date' => date('Y-m-d H:i:s'),
            'date_start' => $date_start_sql,
            'date_finish' => $date_end_sql,
            'franchisee_id' => Yii::$app->cafe->franchiseeId,
        ])
        ->all();

    $models = [];
    $users = [];
    $total = [];
    foreach ($models_prew as $row) {
      $row['finish'] = date(Yii::$app->params['lang']['date'], strtotime($row['finish']));
      if (empty($models[$row['user_id']])) {
        $model = Users::find()->where(['id' => $row['user_id']])->asArray()->one();
        if ($model) {
          $users[$row['user_id']] = $model['name'];
        }
        $models[$row['user_id']] = [];
      }
      if (empty($models[$row['user_id']][$row['finish']])) $models[$row['user_id']][$row['finish']] = [];
      $models[$row['user_id']][$row['finish']] = $row['duration'];

      if (empty($total[$row['user_id']])) $total[$row['user_id']] = 0;
      $total[$row['user_id']] += $row['duration'];
    }

    /*$dataProvider = new ArrayDataProvider([
      'allModels'  => $models,
      'pagination' => false,
    ]);*/


    return [
        'title' => '<span class="fa fa-clock-o antagon-color-main"></span> ' . Yii::t('app', 'Week reports'),
        'content' => $this->renderAjax('weak', [
            'model' => $models,
            'days' => $days,
            'users' => $users,
          //'dataProvider' => $dataProvider,
            'date_start' => $date_start,
            'date_end' => $date_end,
            'date_next' => $date_next,
            'date_prev' => $date_prev,
            'total' => $total,
        ]),
        'footer' => Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]),
    ];
  }
}