<?php

namespace frontend\helpers;

use yii\bootstrap\ActiveField;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class MyActiveField extends ActiveField
{

  /**
   * {@inheritdoc}
   */
  public function checkboxList($items, $options = [])
  {
    if ($this->inline) {
      if (!isset($options['template'])) {
        $this->template = $this->inlineCheckboxListTemplate;
      } else {
        $this->template = $options['template'];
        unset($options['template']);
      }
      if (!isset($options['itemOptions'])) {
        $options['itemOptions'] = [
            'labelOptions' => ['class' => 'checkbox-inline'],
        ];
      }
    } elseif (!isset($options['item'])) {
      $itemOptions = isset($options['itemOptions']) ? $options['itemOptions'] : [];
      $encode = ArrayHelper::getValue($options, 'encode', true);
      $options['item'] = function ($index, $label, $name, $checked, $value) use ($itemOptions, $encode) {
        $options = array_merge([
            'label' => Html::tag('span', '', ['class' => 'fa fa-check']) . ' ' . ($encode ? Html::encode($label) : $label),
            'value' => $value
        ], $itemOptions);
        return '<div class="checkbox">' . Html::checkbox($name, $checked, $options) . '</div>';
      };
    }
    parent::checkboxList($items, $options);
    return $this;
  }

  public function radioList($items, $options = [])
  {
    if ($this->inline) {
      if (!isset($options['template'])) {
        $this->template = $this->inlineRadioListTemplate;
      } else {
        $this->template = $options['template'];
        unset($options['template']);
      }
      if (!isset($options['itemOptions'])) {
        $options['itemOptions'] = [
            'labelOptions' => ['class' => 'radio-inline'],
        ];
      }
    } elseif (!isset($options['item'])) {
      $itemOptions = isset($options['itemOptions']) ? $options['itemOptions'] : [];
      $encode = ArrayHelper::getValue($options, 'encode', true);
      $options['item'] = function ($index, $label, $name, $checked, $value) use ($itemOptions, $encode) {
        $options = array_merge([
            'label' => Html::tag('span', '', ['class' => '']) . ' ' . ($encode ? Html::encode($label) : $label),
            'value' => $value
        ], $itemOptions);
        return '<div class="col-sm-12 "><div class="custom-radio">' . Html::radio($name, $checked, $options) . '</div></div>';
      };
    }
    parent::radioList($items, $options);
    return $this;
  }
}
