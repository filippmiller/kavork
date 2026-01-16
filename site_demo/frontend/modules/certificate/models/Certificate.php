<?php

namespace frontend\modules\certificate\models;

use Yii;

class Certificate
{
  const TYPE_NONE = 0;
  const TYPE_FREE_VISIT = 1;
  const TYPE_FREE_TIME = 2;
  const TYPE_DISCOUNT_PERCENT = 4;
  const TYPE_DISCOUNT_CASH = 5;

  public static function getTypes()
  {
    return [
        self::TYPE_NONE,
        self::TYPE_FREE_VISIT,
        self::TYPE_FREE_TIME,
        self::TYPE_DISCOUNT_PERCENT,
        self::TYPE_DISCOUNT_CASH,
    ];
  }

  public static function getTypeLabels($index = null)
  {
    $labels = [
        self::TYPE_NONE => Yii::t('app', 'None'),
        self::TYPE_FREE_VISIT => Yii::t('app', 'Free visit'),
        self::TYPE_FREE_TIME => Yii::t('app', 'Free time'),
        self::TYPE_DISCOUNT_PERCENT => Yii::t('app', 'Discount %'),
        self::TYPE_DISCOUNT_CASH => Yii::t('app', 'Discount Cash'),
    ];

    if ($index !== null) {
      return (isset($labels[$index])) ? $labels[$index] : 'Unknown';
    }

    return $labels;
  }

  static function getCertificateList()
  {

  }
}
