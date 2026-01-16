<?php

use frontend\modules\users\models\Users;
use frontend\components\GridView;
use yii\helpers\Html;

$columns = [
	[
		'label' => '#',
		'value' => 'user_id',
	],
	[
		'label'  => Yii::t('app', 'Admin'),
		'format' => 'raw',
		'value'  => function ($data) {
			$model = Users::find()->where(['id' => $data['user_id']])->asArray()->one();
			if ($model) {
				return $model['name'];
			}

			return null;
		},
	],
	[
		'label' => Yii::t('app', 'Last {n} day duration',['n'=>7]),
		'value' => function ($data) {
			return Yii::$app->helper->echo_time($data['last_7']);
		},
	],
	[
		'label' => Yii::t('app', 'Last {n} day duration',['n'=>30]),
		'value' => function ($data) {
			return Yii::$app->helper->echo_time($data['last_30']);
		},
	],
	[
		'label' => Yii::t('app', 'Last month duration'),
		'value' => function ($data) {
			return Yii::$app->helper->echo_time($data['last_month']);
		},
	],
	[
		'label' => Yii::t('app', 'All duration'),
		'value' => function ($data) {
			return Yii::$app->helper->echo_time($data['summary']);
		},
	],
	[
		'label' => Yii::t('app', 'This session start'),
		'value' => function ($data) use ($activeSessions) {
			if (isset($activeSessions[$data['user_id']])) {
				return date(Yii::$app->params['lang']['date'], strtotime($activeSessions[$data['user_id']]['start']));
			}

			return null;
		},
	],
	[
		'label' => Yii::t('app', 'This session duration'),
		'value' => function ($data) use ($activeSessions) {
			if (isset($activeSessions[$data['user_id']])) {
				return Yii::$app->helper->echo_time($activeSessions[$data['user_id']]['duration']);
			}

			return null;
		},
	],
	[
		'label'  => Yii::t('app', 'Management'),
		'format' => 'raw',
		'value'  => function ($data) use ($activeSessions) {
			if (isset($activeSessions[$data['user_id']])) {
				$session = $activeSessions[$data['user_id']];
				if ($session['cafe_id'] === Yii::$app->cafe->getId()) {
					return Html::a('<i class="fa fa-stop"></i> ' . Yii::t('app', 'Stop'),
						['/users/log/view', 'id' => $session['id']], [
						'class' => 'btn btn-xs btn-warning',
						'role'  => 'modal-remote',
					]);
				} else {
					return '';
				}
			}

			return Html::a('<i class="fa fa-play"></i> ' . Yii::t('app', 'Start'),
				['/users/start', 'user_id' => $data['user_id']], [
				'class' => 'btn btn-xs btn-primary',
				'role'  => 'modal-remote',
			]);
		},
	],
];
$this->title = Yii::t('app', 'TimeCard');
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="time-card-index">
	 <div id="ajaxCrudDatatable">
    <?= GridView::widget([
        'id' => 'crud-datatable',
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
      //'floatHeader'=>true,
      //'floatHeaderOptions'=>['scrollingTop'=>'50'],
        'pjax' => true,
        'striped' => true,
        'condensed' => true,
        'responsive' => true,
        'export' => false,
        'columns' => $columns,
        'toolbar' => [
            ['content' => '&nbsp;&nbsp;&nbsp;'],
        ],
        'panelBeforeTemplate' => '<div class="row">
		     <div class="col-sm-4 padding-off-right vertical-align">
		    
		     </div>
		     <div class="col-sm-4 mass_but">
		     ' . $panelButtons . '
		     </div>
		    <div class="col-sm-4 padding-off-left but_toolbar">
		    {toolbar}
		    </div>
		    </div>',
        'panel' => [
            'type' => 'default',
            'heading' => false,
            'after' => $afterTable,
        ]
    ]) ?>
  </div>
</div>