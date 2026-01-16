<?php

use yii\db\Schema;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
$actionParams = $generator->generateActionParams();

echo "<?php\n";

?>
  use yii\helpers\Url;
  use kartik\grid\GridView;
  use common\components\widget\NumberRangerWidget;
  use yii\helpers\ArrayHelper;
  use frontend\modules\users\models\Users;
  use frontend\modules\franchisee\models\Franchisee;
  use app\helpers\GridHelper;

  return [
  [
  'class' => 'common\components\CheckboxColumn',
  'width' => '20px',
  ],
  [
  'class' => 'kartik\grid\SerialColumn',
  'width' => '30px',
  ],
<?php
$count = 0;
foreach ($generator->getColumns() as $name => $type) {
  $row = getColumnParams($name, $type, $generator);
  if ($row === false) continue;
  $params = '    ' . $row;
  if (strpos($name, 'pass') !== false || strpos($name, 'hash') !== false || strpos($name, 'sess') !== false) continue;
  if ($name == 'id' || $name == 'created_at' || $name == 'updated_at' || strpos($name, 'data') !== false) {
    echo $params . "\n";
  } else if (++$count < 80) {
    echo $params . "\n";
  } else {
    echo '/*' . $params . '*/' . "\n";
  }
}
?>
  [
  'class' => 'kartik\grid\ActionColumn',
  'dropdown' => false,
  'template'=>$actions,
  'vAlign'=>'middle',
  'urlCreator' => function($action, $model, $key, $index) {
  return Url::to([$action,'<?= substr($actionParams, 1) ?>'=>$key]);
  },
  'updateOptions'=>[
  'role'=>'modal-remote',
  'title'=>'',
  'class' => 'btn btn-info btn-xs admin',
  'label'=>"<i class=\"fa fa-pencil\"></i> ".<?= $generator->generateString('Edit data'); ?>,
  ],
  'deleteOptions'=>[
  'role'=>'modal-remote',
  'title'=>'',
  'class' => 'btn btn-danger btn-xs admin',
  'label'=>"<i class=\"glyphicon glyphicon-trash\"></i> ".<?= $generator->generateString('Delete'); ?>,
  'data-confirm'=>false, 'data-method'=>false,// for override yii data api
  'data-request-method'=>'post',
  'data-toggle'=>'tooltip',
  'data-confirm-title'=><?= $generator->generateString('Are you sure?'); ?>,
  'data-confirm-message'=><?= $generator->generateString('Are you sure want to delete this item'); ?>,]
  ],

  ];

<?php

function getColumnParams($name, $type, $generator)
{
  $name_standart = '\'' . $name . '\',';

  if (strpos($name, 'color') !== false) {
    return '[
      \'attribute\' => \'' . $name . '\',
      \'value\' => function ($model, $key, $index, $widget) {
        return "<span class=\'badge\' style=\'background-color: {$model->' . $name . '}\'> </span>  <code>" . $model->' . $name . ' . \'</code>\';
      },
      \'width\' => \'120px\',
      \'filterType\' => GridView::FILTER_COLOR,
      \'filterWidgetOptions\' => [
        \'showDefaultPalette\' => false,
        \'pluginOptions\' => \Yii::$app->params["colorPluginOptions"],
      ],
      \'vAlign\' => \'middle\',
      \'format\' => \'raw\',
      \'noWrap\' => true
    ],';
  }

  if (strpos($name, 'franchisee_id') !== false) {
    return '[
      \'attribute\'=>\'' . $name . '\',
      \'filterType\' => GridView::FILTER_SELECT2,
      \'format\' => \'raw\',
      \'filter\'=> ArrayHelper::merge(
        [
          \'\'=>' . $generator->generateString('ALL') . '
        ],
        Franchisee::getList()
      ),
      \'value\' => function ($model, $key, $index, $column) {
        return isset($model->franchisee) ? $model->franchisee->name : null;
      },
    ],';
  }

  if ($name == 'lg') {
    return '[
      \'attribute\'=>\'lg\',
      \'filterType\' => GridView::FILTER_SELECT2,
      \'format\' => \'raw\',
      \'filter\'=> ArrayHelper::merge(
          [
              \'\'=>Yii::t(\'app\',"ALL")
          ],
          Yii::$app->params[\'lg_list\']
      ),
      \'value\' => function ($model, $key, $index, $column) {
        $lg= Yii::$app->params[\'lg_list\'];
        return $lg[isset($lg[$model->lg])?$model->lg:Yii::$app->params[\'defaultLang\']];
      },
    ],';
  }
  if (strpos($name, 'user_id') !== false) {
    return '[
      \'attribute\' => \'user_id\',
      \'filterType\' => GridView::FILTER_SELECT2,
      \'format\' => \'raw\',
      \'filter\'=> ArrayHelper::merge(
          [
              \'0\'=>Yii::t(\'app\',"ALL"),
              \'-1\'=>Yii::t(\'app\',"nobody"),
          ],
          ArrayHelper::map((array)Yii::$app->cafe->getUsersList(), \'id\', \'name\')
      ),
      \'value\' => function ($model, $key, $index, $column) {
        $user=$model->user;
        return $user?$user->name:\'-\';
      },
    ],';
  }
  if (strpos($name, 'cafe') !== false) {
    if (!$generator->allCafe) return false;
    return '[
      \'attribute\'=>\'' . $name . '\',
      \'filterType\' => GridView::FILTER_SELECT2,
      \'format\' => \'raw\',
      \'filter\'=> ArrayHelper::merge(
          [
              \'0\'=>Yii::t(\'app\',"ALL")
          ],
          ArrayHelper::map((array)Users::getCafesList(), \'id\', \'name\')
      ),
      \'value\' => function ($model, $key, $index, $column) {
        $cafe=$model->cafe;
        return $cafe->name;
      },
    ],';
  }

  if ($name == 'visitor_id') {
    return "[
      'attribute' => '$name',
      'value' => function (\$model, \$key, \$index, \$column) {
        if(!\$model->visitor_id)return " . $generator->generateString('Anonymous') . ";
        \$visitor = \$model->visitor;
        return \$visitor->f_name.' '.\$visitor->l_name;
      },
    ],";
  }

  if (in_array($name, ['id', 'franchisee_id', 'last_task'])) return $name_standart;

  switch ($type) {
    case Schema::TYPE_SMALLINT:
    case Schema::TYPE_INTEGER:
    case Schema::TYPE_BIGINT:
    case Schema::TYPE_BOOLEAN:
    case Schema::TYPE_FLOAT:
    case Schema::TYPE_DOUBLE:
    case Schema::TYPE_DECIMAL:
    case Schema::TYPE_MONEY:
      return "[
      'attribute' => '$name',
      'filter'=>NumberRangerWidget::widget([
        'model'=>\$searchModel,
        'attribute'=>'$name',
        'value'=>function (\$model, \$key, \$index, \$column) {
          return number_format(\$model->$name,  2,'.',' ');
        }
      ])
    ],";
      break;
    case Schema::TYPE_TIME:
    case Schema::TYPE_TIMESTAMP:
    case Schema::TYPE_DATE:
    case Schema::TYPE_DATETIME:
      return "[
      'attribute' => '$name',
      'filterType' => GridView::FILTER_DATE_RANGE,
      'filterWidgetOptions' =>GridHelper::getFilterDateRangeConfig(),
      'value'=> function (\$model, \$key, \$index, \$column) {
        if(!\$model->$name)return '-';
        \$$type=strtotime(\$model->$name);
        return date(Yii::\$app->params['lang']['$type'], \$$type);
      },
    ],";
      break;

  };

  return $name_standart;
}