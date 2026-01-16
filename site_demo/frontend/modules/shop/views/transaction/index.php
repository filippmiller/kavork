<?php

use common\components\widget\GridPageSize;
use johnitvn\ajaxcrud\CrudAsset;
use frontend\components\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\shop\models\ShopTransactionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Shop Transactions');
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="shop-transaction-index">
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
        'export' => false,
        'columns' => $columns,
        'toolbar' => [
            ['content' =>
                $panelButtons .
                Html::a('<i class="glyphicon glyphicon-th-list"></i>', ['columns'],
                    ['role' => 'modal-remote', 'title' => Yii::t('app', 'Columns visibled'), 'class' => 'btn btn-default']) .

                ($canCreate ? Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create'],
                    ['role' => 'modal-remote', 'title' => Yii::t('app', 'Create new Shop Transactions'), 'class' => 'btn btn-default']) : '') .

                Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
                    ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => Yii::t('app', 'Reset Grid')]) .

                '{toggleData}' .
                '{export}',
            ],
        ],
        'panel' => [
            'type' => 'default',
            'heading' => '<i class="glyphicon glyphicon-list"></i> ' . Yii::t('app', 'Shop Transactions listing'),
            'before' => GridPageSize::widget(),
        ],
    ]) ?>
  </div>
</div>
<?php Modal::begin([
    "id" => "ajaxCrudModal",
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>
