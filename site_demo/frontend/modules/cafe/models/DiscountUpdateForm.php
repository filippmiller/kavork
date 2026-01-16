<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 13.10.18
 * Time: 22:49
 */

namespace frontend\modules\cafe\models;

use frontend\modules\franchisee\models\Franchisee;
use Yii;
use yii\validators\NumberValidator;
use yii\validators\RequiredValidator;

class DiscountUpdateForm extends Discount
{
  public $child_discount;

  public $cafe_discounts = [];

  public $franchisee_discounts = [];

  /**
   * @var Cafe
   */
  private $_cafe;

  /**
   * @var Franchisee
   */
  private $_franchisee;

  public function __construct(Cafe $cafe)
  {
    $this->_cafe = $cafe;
    $this->_franchisee = $cafe->franchisee;

    $this->child_discount = $this->_cafe->child_discount;

    if (isset($this->_cafe->data[self::DISCOUNT_KEY])) {
      $this->cafe_discounts = $this->_cafe->data[self::DISCOUNT_KEY];
    }

    if (isset($this->_franchisee->data[self::DISCOUNT_KEY])) {
      $this->franchisee_discounts = $this->_franchisee->data[self::DISCOUNT_KEY];
    }

    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['child_discount'], 'required'],
        [['child_discount'], 'default', 'value' => 0],
        [['child_discount'], 'integer', 'min' => 0, 'max' => 100],

        [['cafe_discounts'], 'safe'],
        [['franchisee_discounts'], 'safe'],

        [['cafe_discounts'], 'validateDiscountItems'],
        [['franchisee_discounts'], 'validateDiscountItems', 'when' => function () {
          return Yii::$app->user->can('FranchiseeDiscountUpdate');
        }],
    ];
  }

  public function validateDiscountItems($attribute)
  {
    $requiredValidator = new RequiredValidator();
    $integerValidator = new NumberValidator([
        'integerOnly' => true,
        'min' => 1,
    ]);
    $numberValidator = new NumberValidator([
        'min' => 0,
        'max' => 100,
    ]);

    $requiredAttributes = [
        'number',
        'use',
        'value',
    ];

    $integerAttributes = [
        'number',
    ];

    $percentAttributes = [
        'value',
    ];

    foreach ($requiredAttributes as $requiredAttribute) {
      foreach ($this->$attribute as $index => $row) {
        $error = null;
        $requiredValidator->validate($row[$requiredAttribute], $error);
        if (!empty($error)) {
          $key = $attribute . '[' . $index . '][' . $requiredAttribute . ']';
          $this->addError($key, $error);
        }
      }
    }

    foreach ($integerAttributes as $integerAttribute) {
      foreach ($this->$attribute as $index => $row) {
        $error = null;
        $integerValidator->validate($row[$integerAttribute], $error);
        if (!empty($error)) {
          $key = $attribute . '[' . $index . '][' . $integerAttribute . ']';
          $this->addError($key, $error);
        }
      }
    }

    foreach ($percentAttributes as $percentAttribute) {
      foreach ($this->$attribute as $index => $row) {
        $error = null;
        $numberValidator->validate($row[$percentAttribute], $error);
        if (!empty($error)) {
          $key = $attribute . '[' . $index . '][' . $percentAttribute . ']';
          $this->addError($key, $error);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'child_discount' => Yii::t('app', 'Child discount'),
        'cafe_discounts' => Yii::t('app', 'Cafe discounts'),
        'franchisee_discounts' => Yii::t('app', 'Franchisee discounts'),
    ];
  }

  public function save()
  {
    if (!$this->validate()) {
      return false;
    }

    $this->_cafe->child_discount = $this->child_discount;

    $cafeData = $this->_cafe->data;
    $cafeData[self::DISCOUNT_KEY] = $this->cafe_discounts;
    $this->_cafe->data = $cafeData;
    $this->_cafe->save(false, ['data', 'child_discount']);

    if (Yii::$app->user->can('FranchiseeDiscountUpdate')) {
      $franchiseeData = $this->_franchisee->data;
      $franchiseeData[self::DISCOUNT_KEY] = $this->franchisee_discounts;
      $this->_franchisee->data = $franchiseeData;
      $this->_franchisee->save(false, ['data']);
    }

    return true;
  }

}