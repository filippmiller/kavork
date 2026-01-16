<?php

use frontend\modules\report\models\ReportAutoSend;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
use unclead\multipleinput\TabularColumn;

$columns = [
    [
        'name' => 'id',
        'type' => TabularColumn::TYPE_HIDDEN_INPUT
    ],
    [
        'name' => 'email',
        'title' => Yii::t('app', 'Email'),
		'options'      => [
			'placeholder' => Yii::t('app', 'Email'),
		],
    ],
    [
        'name' => 'type',
        'type' => MultipleInputColumn::TYPE_DROPDOWN,
        'enableError' => true,
        'title' => Yii::t('app', 'Report type'),
        'defaultValue' => 33,
        'items' => ReportAutoSend::getTypes()
    ]
];
?>

<?= $form->field($model, 'report')->widget(MultipleInput::className(), [
    'id' => 'cafe_report',
    'max' => 100,
    'min' => 0, // should be at least 2 rows
    'allowEmptyList' => true,
    'enableGuessTitle' => true,
    'cloneButton' => true,
    'attributeOptions' => [
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
        'validateOnChange' => false,
        'validateOnSubmit' => true,
        'validateOnBlur' => false,
    ],
    'addButtonPosition' => MultipleInput::POS_HEADER, // show add button in the header
	'addButtonOptions' => [
	    'label' => '<i class="fa fa-plus"></i><span class="text-capitalize"> '. Yii::t('config', 'create') .'</span>',
	    'class' => 'btn btn-default addbutmultiple',
	    ],
    'columns' => $columns,
])
    ->label();
?>