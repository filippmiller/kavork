<?php
use frontend\modules\franchisee\models\Franchisee;
use frontend\modules\templates\models\Template;
use frontend\modules\users\models\Users;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

return [
	[
		'class' => 'common\components\CheckboxColumn',
		'width' => '20px',
	],
	[
		'class' => 'kartik\grid\SerialColumn',
		'width' => '30px',
	],
	'id',
	[
		'attribute'  => 'scope_id',
		'filterType' => GridView::FILTER_SELECT2,
		'format'     => 'raw',
		'filter'     => ArrayHelper::merge(
			[
				'' => Yii::t('app', 'ALL'),
			],
			Template::getScopeLabels()
		),
		'value'      => function ($model, $key, $index, $column) {
			return Template::getScopeLabels($model->scope_id);
		},
	],
	[
		'attribute'  => 'cafe_id',
		'filterType' => GridView::FILTER_SELECT2,
		'format'     => 'raw',
		'filter'     => ArrayHelper::merge(
			[
				'' => Yii::t('app', 'ALL'),
			],
			ArrayHelper::map(Users::getCafesList(), 'id', 'name')
		),
		'value'      => function ($model, $key, $index, $column) {
			$list = ArrayHelper::map(Users::getCafesList(), 'id', 'name');
			return isset($list[$model->cafe_id]) ? $list[$model->cafe_id] : "-";
		},
	],
	[
		'attribute'  => 'franchisee_id',
		'filterType' => GridView::FILTER_SELECT2,
		'visible'    => Yii::$app->user->can('AllFranchisee'),
		'format'     => 'raw',
		'filter'     => ArrayHelper::merge(
			[
				'' => Yii::t('app', 'ALL'),
			],
			Franchisee::getList()
		),
		'value'      => function ($model, $key, $index, $column) {
			$list = Franchisee::getList();
			return isset($list[$model->franchisee_id]) ? $list[$model->franchisee_id] : "-";
		},
	],
	[
		'attribute' => 'type_id',
		'format'     => 'raw',
		'filter'     => ArrayHelper::merge(
			[
				'' => Yii::t('app', 'ALL'),
			],
			Template::getTypeLabels()
		),
		'value'      => function ($model, $key, $index, $column) {
			return Template::getTypeLabels($model->type_id);
		},
	],
	[
		'attribute' => '_used_in_cafe',
		'format'    => 'raw',
		'filter'    => [
			1 => Yii::t('main', 'Yes'),
			2 => Yii::t('main', 'No'),
		],
		'value'     => function ($model, $key, $index, $column) {
			/* @var $model Template */
			$exists = $model->getCafe(Yii::$app->cafe->getId())->exists();

			if ($exists) {
				return '<div class="text-center"><span class="text-success glyphicon glyphicon-ok"></span></span></div>';
			}

			return '';
		},
	],
	[
		'class'      => 'kartik\grid\ActionColumn',
		'dropdown'   => false,
		'template'   => '<div class="button_action">'.$actions.'</div>',
		'vAlign'     => 'middle',
		'urlCreator' => function ($action, $model, $key, $index) {
			return Url::to([$action, 'id' => $key]);
		},
		'buttons'    => [
			'use'    => function ($url, $model, $key) {
				if ($model->getCafe(Yii::$app->cafe->getId())->exists()) {
					return '';
				}

				$label = "<div class=\"btn bg-blue fg-white btn-xs admin\"><i class=\"fa fa-check\"></i> " . Yii::t('app', 'Use') . "</div>";

				return Html::a($label, $url, [
					'role' => 'modal-remote',
				]);
			},
			'send-on-email'    => function ($url, $model, $key) {
				if ($model->type_id == Template::TYPE_CHECK_MAIL) {
					return Html::a("<i class=\"fa fa-envelope\"></i> " . Yii::t('app', 'Test sending on email'), $url, [
						'class' => 'btn btn-info btn-xs admin',
						'role' => 'modal-remote',
					]);
				}
				return '';
			},
        'print'    => function ($url, $model, $key) {
          if ($model->type_id == Template::TYPE_CHECK_PRINT) {
            return Html::a("<i class=\"fa fa-print\"></i> ".Yii::t('app', 'Print'), $url, [
                  'class'=> 'btn btn-info btn-xs print ',
                  'role' => 'modal-remote',
                ]);
            }
          return '';
        },
			'update' => function ($url, $model, $key) {
				if ($model->scope_id == Template::SCOPE_DEFAULT && !Yii::$app->user->can('root')) {
					return '';
				}

				$label = "<div class=\"btn btn-science-blue btn-xs admin\"><i class=\"fa fa-pencil\"></i> " . Yii::t('app', 'Edit data') . "</div>";

				return Html::a($label, $url);
			},
			'delete' => function ($url, $model, $key) {
				if ($model->scope_id == Template::SCOPE_DEFAULT) {
					return '';
				}

				$label = "<div class=\"btn btn-danger btn-xs admin\"><i class=\"glyphicon glyphicon-trash\"></i> " . Yii::t('app', 'Delete') . "</div>";

				return Html::a($label, $url, [
					'role'                 => 'modal-remote',
					'title'                => '',
					'data-confirm'         => false,
					'data-method'          => false, // for override yii data api
					'data-request-method'  => 'post',
					'data-toggle'          => 'tooltip',
					'data-confirm-title'   => Yii::t('app', 'Are you sure?'),
					'data-confirm-message' => Yii::t('app', 'Are you sure want to delete this item'),
				]);
			},
		],
	],

];

