<?php

namespace common\components;

class CheckboxColumn extends \kartik\grid\CheckboxColumn
{

  private $add_before = "<label>";
  private $add_after = "<span class=\"fa fa-check\"></span></label>";

  public function renderDataCell($model, $key, $index)
  {
    return $this->addWrap(parent::renderDataCell($model, $key, $index));
  }

  public function renderHeaderCellContent()
  {
    return $this->add_before . parent::renderHeaderCellContent() . $this->add_after;
  }

  private function addWrap($element)
  {
    $element = str_replace('<input', $this->add_before . '<input', $element);
    $element = str_replace('</td>', $this->add_after . '</td>', $element);
    return $element;
  }

}