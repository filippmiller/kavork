<?php

namespace frontend\modules\cafe\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\validators\NumberValidator;
use yii\validators\RequiredValidator;

/**
 * This is the model class for table "cafe_params".
 *
 * @property int $id
 * @property string $name
 * @property string $vat_list
 * @property string $banknote_list
 * @property string $time_zone
 * @property string $datetime
 * @property string $datetime_js
 * @property string $datetime_short
 * @property string $datetime_short_js
 * @property string $date
 * @property string $date_js
 * @property string $time
 * @property string $time_js
 *
 * @property Cafe[] $caves
 */
class CafeParams extends \common\components\ActiveRecord
{

  const TIME_12 = 1;
  const TIME_24 = 2;

  const DATE_MM_DD_YYYY = 1;
  const DATE_DD_MM_YYYY = 2;
  const DATE_YYYY_MM_DD = 3;

  const TIME_SECOND_SHOW = 1;
  const TIME_SECOND_HIDDEN = 2;

  public $vat_list_items = [];

  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'cafe_params';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['name'], 'required'],
        [['name'], 'string', 'max' => 20],
        [['vat_list', 'vat_list_items'], 'safe'],
        [['vat_list_items'], 'validateVatListItems'],
        [['time_format', 'date_format', 'show_second', 'first_weekday'], 'integer'],
        [['time_zone'], 'string', 'max' => 30],
        [['banknote_list'], 'safe'],
        ['banknote_list', 'match', 'pattern' => '/^\d+(?:,\d+)*$/'],
    ];
  }

  public function validateVatListItems($attribute)
  {
    $requiredValidator = new RequiredValidator();
    $numberValidator = new NumberValidator([
        'min' => 0.1,
        'max' => 100,
    ]);

    $requiredAttributes = [
        'name',
        'value',
    ];

    $numberAttributes = [
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

    foreach ($numberAttributes as $numberAttribute) {
      foreach ($this->$attribute as $index => $row) {
        $error = null;
        $numberValidator->validate($row[$numberAttribute], $error);
        if (!empty($error)) {
          $key = $attribute . '[' . $index . '][' . $numberAttribute . ']';
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
        'id' => Yii::t('app', 'ID'),
        'name' => Yii::t('app', 'Name'),
        'vat_list' => Yii::t('app', 'Vat List'),
        'banknote_list' => Yii::t('app', 'Banknote list'),
        'time_zone' => Yii::t('app', 'Time Zone'),
        'time_format' => Yii::t('app', 'Time format'),
        'date_format' => Yii::t('app', 'Date format'),
        'show_second' => Yii::t('app', 'Show second'),
        'first_weekday' => Yii::t('app', 'First weekday'),
        'vat_list_items' => Yii::t('app', 'Vat list items'),
    ];
  }

  /**
   * Composes lang params - date, time, etc...
   *
   * @return array
   */
  public static function composeLangParams($data)
  {
    $lang = [];

    $time_format = $data['time_format'];
    $date_format = $data['date_format'];
    $show_second = ($data['show_second'] == CafeParams::TIME_SECOND_SHOW);

    if ($date_format == CafeParams::DATE_YYYY_MM_DD) {
      $date = 'Y-m-d';
      $date_js = 'YYYY-MM-DD';
    } else if ($date_format == CafeParams::DATE_MM_DD_YYYY) {
      $date = 'm-d-Y';
      $date_js = 'MM-DD-YYYY';
    } else {
      $date = 'd-m-Y';
      $date_js = 'DD-MM-YYYY';
    }

    if ($time_format == CafeParams::TIME_12) {
      $time = 'g:i' . ($show_second ? ":s" : "") . ' A';
      $time_js = 'g:i' . ($show_second ? ":s" : "") . ' A';
      $time_short = 'g:i A';
      $time_short_js = 'g:i A';
      $time_char = 'g' . ' A';
    } else {
      $time = 'G:i' . ($show_second ? ":s" : "");
      $time_js = 'G:i' . ($show_second ? ":s" : "");
      $time_short = 'G:i';
      $time_short_js = 'G:i';
      $time_char = 'G';
    }

    $lang['datetime'] = $date . ' ' . $time;
    $lang['datetime_js'] = $date_js . ' ' . $time_js;
    $lang['datetime_short'] = $date . ' ' . $time_short;
    $lang['datetime_short_js'] = $date_js . ' ' . $time_short_js;
    $lang['date'] = $date;
    $lang['date_js'] = $date_js;
    $lang['time'] = $time;
    $lang['time_char'] = $time_char;
    $lang['time_js'] = $time_js;
    $lang['time_js2'] = strtolower($time_js);
    $lang['date_js2'] = strtolower($date_js);
    $lang['time24Hour'] = $time_format == CafeParams::TIME_24;
    $lang['timeShowSeconds'] = $show_second;

    return $lang;
  }

  /**
   * Returns array of franchisee with id => name
   */
  public static function getList()
  {
    return ArrayHelper::map(self::find()->asArray()->all(), 'id', 'name');
  }

  public function afterFind()
  {
    parent::afterFind(); // TODO: Change the autogenerated stub

    $this->vat_list_items = json_decode($this->vat_list, true);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCafes()
  {
    return $this->hasMany(Cafe::className(), ['params_id' => 'id']);
  }

  public function beforeSave($insert)
  {
    Yii::$app->cache->flush();

    if ($this->vat_list_items) {
      $this->vat_list = $this->vat_list_items;
    }
    if (is_array($this->vat_list)) {
      $this->vat_list = json_encode($this->vat_list_items);
    }

    return parent::beforeSave($insert); // TODO: Change the autogenerated stub
  }

  public function afterSave($insert, $changedAttributes)
  {
    $this->vat_list_items = json_decode($this->vat_list, true);
    parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
  }
}
