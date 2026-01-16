<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
echo "<?php\n";
?>
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 
use johnitvn\ajaxcrud\BulkButtonWidget;
use common\components\widget\GridPageSize;

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">
    <div id="ajaxCrudDatatable">
        <?="<?="?>GridView::widget([
            'id'=>'crud-datatable',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            //'floatHeader'=>true,
            //'floatHeaderOptions'=>['scrollingTop'=>'50'],
            'pjax'=>true,
            'striped' => true,
            'condensed' => true,
            'responsive' => true,
            'export' => false,
            'columns' => $columns,
            'toolbar'=> [
                ['content'=>
                    $panelButtons .
                    Html::a('<i class="glyphicon glyphicon-th-list"></i>', ['columns'],
                    ['role'=>'modal-remote','title'=> <?=$generator->generateString('Columns visibled');?>,'class'=>'btn btn-default']).

                    ($canCreate?Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create'],
                    ['role'=>'modal-remote','title'=> <?=$generator->generateString('Create new '.Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))); ?>,'class'=>'btn btn-default']):'').

                    Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
                    ['data-pjax'=>1, 'class'=>'btn btn-default', 'title'=><?=$generator->generateString('Reset Grid');?>]).

                    '{toggleData}'.
                    '{export}'
                ],
            ],	
            'panel' => [
                'type' => 'default',
                'heading' => '<i class="glyphicon glyphicon-list"></i> '.<?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass))) . ' listing');?>,
                'before'=> GridPageSize::widget(),
            ]
        ])<?="?>\n"?>
    </div>
</div>
<?='<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>'."\n"?>
<?='<?php Modal::end(); ?>'?>

