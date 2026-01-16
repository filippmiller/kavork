<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 16.07.17
 * Time: 14:43
 */

namespace common\components\widget;

use yii\helpers\Html;

/**
 * PageSize widget is an addition to the \yii\grid\GridView that enables
 * changing the size of a page on GridView.
 *
 * <?= GridView::widget([
 *      'dataProvider' => $dataProvider,
 *      'filterModel' => $searchModel,
 * 		'filterSelector' => 'select[name="per-page"]',
 *      'columns' => [
 *          ...
 *      ],
 *  ]); ?>
 *
 * Please note that `per-page` here is the string you use for `pageSizeParam` setting of the PageSize widget.
 */

class GridPageSize extends \yii\base\Widget
{
	/**
	 * @var string the label text.
	 */
	public $label = 'items';

	/**
	 * @var integer the default page size. This page size will be used when the $_GET['per-page'] is empty.
	 */
	public $defaultPageSize = 50;

	/**
	 * @var string the name of the GET request parameter used to specify the size of the page.
	 * This will be used as the input name of the dropdown list with page size options.
	 */
	public $pageSizeParam = 'per-page';

	/**
	 * @var array the list of page sizes
	 */
	public $sizes = [20 => 20, 50 => 50, 100 => 100, 200 => 200, 500 => 500, 1000 => 1000, 2000 => 2000, 5000 => 5000];

	/**
	 * @var string the template to be used for rendering the output.
	 */
	public $template = '<div class="form-group list_table">{list}</div>';

	/**
	 * @var array the list of options for the drop down list.
	 */
	public $options = [
		'class' => 'form-control',
	];

	/**
	 * @var array the list of options for the label
	 */
	public $labelOptions;

	/**
	 * @var boolean whether to encode the label text.
	 */
	public $encodeLabel = true;

	/**
	 * Runs the widget and render the output
	 */
	public function run()
	{
		if (empty($this->options['id'])) {
			$this->options['id'] = $this->id;
		}

		if ($this->encodeLabel) {
			$this->label = Html::encode($this->label);
		}

		$perPage = !empty($_GET[$this->pageSizeParam]) ? $_GET[$this->pageSizeParam] : $this->defaultPageSize;

		$listHtml = Html::dropDownList($this->pageSizeParam, $perPage, $this->sizes, $this->options);
		$labelHtml = Html::label($this->label, $this->options['id'], $this->labelOptions);

		$output = str_replace(['{list}', '{label}'], [$listHtml, $labelHtml], $this->template);

		return $output;
	}
}