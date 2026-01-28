<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 10.09.18
 * Time: 19:10
 */

namespace app\helpers;

use Yii;
use yii\helpers\ArrayHelper;

class GridHelper
{
  public static function getFilterDateRangeConfig($config = [], $value = false, $time = true)
  {
    if ($value) {
      $format = $time ? Yii::$app->params['lang']['datetime'] : Yii::$app->params['lang']['date'];
      if (is_string($value)) $value = explode(' - ', $value);
      foreach ($value as &$val) {
        if (empty($val)) $val = time();
        if (is_numeric($val)) $val = date($format, $val);
      }
      $config['value'] = implode(' - ', $value);
    }

    $config = ArrayHelper::merge(Yii::$app->params['datetime_option'], [
        'pluginOptions' => [
            "timePicker24Hour" => Yii::$app->params['lang']['time24Hour'],
            'locale' => [
                'format' => $time ? Yii::$app->params['lang']['datetime'] : Yii::$app->params['lang']['date'],
            ],
        ],
    ], $config);

    if (!$time) {
      $config['pluginOptions']['timePicker'] = false;
    }
    //ddd($config);
    return $config;
  }

  public static function getDbDateFromDateRangeFormat($date, $interval = null, $dateAndTime = true)
  {
    $dateRangeWidgetOption = self::getFilterDateRangeConfig([], false, $dateAndTime);
    $dateTimeFormat = $dateRangeWidgetOption['pluginOptions']['locale']['format'];
    $dateTime = \DateTime::createFromFormat($dateTimeFormat, $date);
    if (!$dateTime) {
      return false;
    }
    if ($interval !== null) {
      $dateTime->add(new \DateInterval($interval));
    }
    return $dateTime->format($dateAndTime ? 'Y-m-d H:i:s' : 'Y-m-d');
  }

  public static function getBooleanFilter()
  {
    return [
        0 => Yii::t('app', 'No'),
        1 => Yii::t('app', 'Yes'),
    ];
  }
}
