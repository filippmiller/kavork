<?php

use common\components\widget\GridPageSize;
use frontend\components\GridView;
use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\cafe\models\CafeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Cafes');
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);
if (empty($panelButtons)) $panelButtons = "";
?>
<div class="cafe-index">
  <div id="ajaxCrudDatatable">
    <?= GridView::widget([
        'id' => 'crud-datatable',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
      //'floatHeader'=>true,
      //'floatHeaderOptions'=>['scrollingTop'=>'50'],
        'pjax' => true,
        'striped' => true,
        'condensed' => true,
        'responsive' => true,
      //'export' => false,
        'columns' => $columns,
        'toolbar' => [
            ['content' =>
                Html::a('<i class="fa fa-refresh"></i> ' . Yii::t('app', 'Reset') . '', [''],
                    ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => Yii::t('app', 'Reset Grid')]) .

                Html::a('<i class="fa fa-th-list"></i> ' . Yii::t('app', 'Columns') . '', ['columns'],
                    ['role' => 'modal-remote', 'title' => Yii::t('app', 'Columns visibled'), 'class' => 'btn btn-default']) .

                '{export}',],
        ],
        'panelBeforeTemplate' => '<div class="row">
		     <div class="col-sm-4 padding-off-right vertical-align">
		    ' . GridPageSize::widget() . ' {summary}
		     </div>
		     <div class="col-sm-4 mass_but">
		     ' . ($canCreate ? Html::a('<i class="fa fa-plus"></i> <i class="fa fa-cube"></i> ' . Yii::t('app', 'Create new Cafes') . '', ['create'],
                ['role' => 'modal-remote', 'title' => Yii::t('app', 'Create new Cafes'), 'class' => 'btn btn-science-blue']) : '') . '
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
<?php Modal::begin([
    "id" => "ajaxCrudModal",
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>
