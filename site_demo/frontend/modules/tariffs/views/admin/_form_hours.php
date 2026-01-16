<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 04.11.18
 * Time: 15:49
 */

use unclead\multipleinput\MultipleInput;

$columns = [
	[
		'name'         => 'hour',
		'title'        => Yii::t('app', 'From what hour'),
		'defaultValue' => 2,
		'enableError'  => true,
		'options'      => [
			'type' => 'textInput',
			'placeholder' => Yii::t('app', 'From what hour'),
		],
	],
	[
		'name'         => 'price',
		'title'        => Yii::t('app', 'Price'),
		//'defaultValue' => 1,
		'enableError'  => true,
		'options'      => [
			'type' => 'number',
			'placeholder' => Yii::t('app', 'Price'),
		],
	],
];

echo $form->field($model, '_hours')->widget(MultipleInput::className(), [
	'id'                => 'tariff_hour_price_editor',
	'max'               => 100,
	'min'               => 0, // should be at least 2 rows
	'allowEmptyList'    => true,
	'enableGuessTitle'  => true,
	'cloneButton'       => true,
	'addButtonPosition' => MultipleInput::POS_HEADER, // show add button in the header
	'addButtonOptions' => [
	    'label' => '<i class="fa fa-plus"></i><span class="text-capitalize"> '. Yii::t('config', 'create') .'</span>',
	    'class' => 'btn btn-default addbutmultiple',
	    ],
	'columns'           => $columns,
])
	->label(Yii::t('app', 'Setting next hours'));
