<?php

namespace frontend\modules\users\controllers;

use frontend\components\Controller;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\ForbiddenHttpException;

/**
 * TimeCardController implements the CRUD actions for UserLog model.
 */
class TimeCardController extends Controller
{
  public function actionIndex($date = false)
  {
    if (Yii::$app->user->isGuest || !Yii::$app->cafe->id || !Yii::$app->cafe->can('adminReport') || !Yii::$app->user->can('UserLogWeakReport')) {
      throw new ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
    }

    $panelButtons = '';

    if (Yii::$app->cafe->can('adminReport') && Yii::$app->user->can('UserLogWeakReport')) {
      $panelButtons .= Html::a('<i class="fa fa-clock-o"></i> ' . Yii::t('app', 'Week reports'), ['/users/report/index'],
          ['role' => 'modal-remote', 'class' => 'btn btn-info']);
    }

    $select = [
        'user_id' => 'l.user_id',
        'last_7' => 'SUM( IF(DATEDIFF(:now, l.start) <= 7, TIME_TO_SEC(timediff(IFNULL(l.finish, :now ), l.start)), 0) )',
        'last_30' => 'SUM( IF(DATEDIFF(:now, l.start) <= 30, TIME_TO_SEC(timediff(IFNULL(l.finish, :now ), l.start)), 0) )',
        'last_month' => 'SUM(IF(
			 l.start >= DATE_ADD(LAST_DAY(DATE_SUB(:now, INTERVAL 2 MONTH)), INTERVAL 1 DAY)
			 AND 
			 l.start <= DATE_SUB(:now, INTERVAL 1 MONTH)
			 , TIME_TO_SEC(timediff(IFNULL(l.finish, :now ), l.start)), 0) )',
        'summary' => 'SUM( TIME_TO_SEC(timediff(IFNULL(l.finish, :now ), l.start)) )',
    ];

    $cafeUsersList = Yii::$app->cafe->getCafeUsersList();
    $userIds = ArrayHelper::getColumn($cafeUsersList, 'id');

    $models = (new Query())
        ->select($select)
        ->from('user_log l')
        ->leftJoin('user', 'user.id = l.user_id')
        ->leftJoin('(SELECT id, user_id FROM user_log WHERE finish IS NULL) e', 'e.user_id = l.user_id')
        ->andWhere([
            'l.user_id' => $userIds
        ])
        ->params([
            ':now' => date('Y-m-d H:i:s'),
        ])
        ->groupBy('l.user_id')
        ->orderBy([
            new Expression('e.id IS NULL'),
            'user.name' => SORT_ASC,
        ])
        ->all();

    $activeSessions = (new Query())
        ->select([
            '*',
            'duration' => 'TIME_TO_SEC(TIMEDIFF(:now, start))',
        ])
        ->from('user_log')
        ->where(['finish' => null])
        ->indexBy('user_id')
        ->params([
            ':now' => date('Y-m-d  H:i:s'),
        ])
        ->groupBy('user_id')
        ->all();

    $dataProvider = new ArrayDataProvider([
        'allModels' => $models,
        'pagination' => false,
    ]);

    return $this->render('index', [
        'panelButtons' => $panelButtons,
        'dataProvider' => $dataProvider,
        'activeSessions' => $activeSessions,
    ]);
  }
}