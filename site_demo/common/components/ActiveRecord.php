<?php

namespace common\components;

class ActiveRecord extends \yii\db\ActiveRecord
{
  public function getAttributeLabel($attribute)
  {
    $label = parent::getAttributeLabel($attribute);

    $atrebute_label = $this->generateAttributeLabel($attribute);
    if ($atrebute_label == $label) {
      $first = mb_substr($atrebute_label, 0, 1);//первая буква
      $last = mb_substr($atrebute_label, 1);//все кроме первой буквы
      $first = strtoupper($first);
      $last = strtolower($last);
      $atrebute_label = $first . $last;

      $label = \Yii::t('app', $atrebute_label);
    };

    return $label;
  }
}