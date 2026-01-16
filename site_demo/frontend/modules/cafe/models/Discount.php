<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 15.10.18
 * Time: 19:41
 */

namespace frontend\modules\cafe\models;

use Yii;
use yii\base\Model;

class Discount extends Model
{
  const PERIOD_WEEK = 0;
  const PERIOD_MONTH = 1;
  const PERIOD_ALL = 2;

  const DISCOUNT_KEY = 'discounts';


  public static function getPeriodLabels($index = null)
  {
    $items = [
        self::PERIOD_WEEK => Yii::t('app', 'Week'),
        self::PERIOD_MONTH => Yii::t('app', 'Month'),
        self::PERIOD_ALL => Yii::t('app', 'All'),
    ];

    if ($index !== null) {
      return isset($items[$index]) ? $items[$index] : Yii::t('app', 'Unknown');
    }

    return $items;
  }
}