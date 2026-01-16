<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 01.10.18
 * Time: 17:48
 */

namespace frontend\modules\shop\models\forms;

use Yii;
use yii\base\Model;

class FakeItemForm extends Model
{
  public $title;
  public $quantity = 1;
  public $price = 0;
  public $tax_required = 0;

  public function rules()
  {
    return [
        [['title', 'quantity', 'price'], 'required'],
        ['tax_required', 'boolean'],
        ['quantity', 'number', 'min' => 1],
        ['price', 'number', 'min' => 0],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'title' => Yii::t('app', 'Product Title'),
        'description' => Yii::t('app', 'Product Description'),
        'tax_required' => Yii::t('app', 'Tax'),
        'price' => Yii::t('app', 'Price'),
        'quantity' => Yii::t('app', 'Quantity'),
    ];
  }
}