<?php

namespace common\components\widget;

use kartik\daterange\DateRangePicker;
use Yii;
use yii\widgets\InputWidget;

class NumberRangerWidget extends InputWidget
{
  public $model;
  public $attribute;
  public $className;
  public $type = false;

  public function init()
  {
    parent::init();
    if ($this->className === null) {
      $this->className = explode('/', str_replace('\\', '/', $this->model->className()));
      $this->className = $this->className[count($this->className) - 1];
    }
  }

  private function genName($name, $isModel = false)
  {
    if ($isModel) {
      return $this->className . '[' . $this->attribute . '_' . $name . ']';
    }
    return $this->className . '_' . $this->attribute . '_' . $name;
  }

  private function genValue($name, $default = "")
  {
    $name = $this->attribute . '_' . $name;
    return (isset($this->model->$name) && is_numeric($this->model->$name) ? $this->model->$name : $default);
  }

  public function run()
  {
    //$this->registerAssets();

    $name = $this->id;
    if (!$this->type) {
      $params = $this->model->getSlideParams($this->attribute);

      if ($params['min'] == $params['max']) $params['max'] += 100;

      $sliderJs = '
      ' . $name . '=$( "#' . $name . '" ).slider({
        range: true,
        values: [ $( "[name=\"' . $this->genName('from', true) . '\"]" ).val()||' . $params['min'] . ', $( "[name=\"' . $this->genName('to', true) . '\"]" ).val()||' . $params['max'] . ' ],
        min: ' . $params['min'] . ',
        max: ' . $params['max'] . ',
        step: ' . $params['step'] . ',
        slide: function( event, ui ) {
          $( "[name=\"' . $this->genName('from', true) . '\"]" ).val( ui.values[ 0 ]);
          $( "[name=\"' . $this->genName('to', true) . '\"]" ).val( ui.values[ 1 ]);
        },
        stop: function( event, ui ) {$( "[name=\"' . $this->genName('from', true) . '\"]" ).change()}
      });';

      $js = <<<JS
$('body').on('click', function() {
    $('.temp_show.active').find('.ui-slider').slider( "destroy" );
    $('.temp_show.active').removeClass('active');
});    
$(document).on('click', '.range_filter-wrap input', function() {
  var input = $(this);
  var wrap_id = input.attr('for'); 
  var wrap = $("#" + wrap_id + '-wrap');
  wrap.addClass('active');
  $sliderJs
});
JS;
      $tiptop = '<div id="' . $name . '"></div>';
      $html = '
      <div class="range_filter-wrap stopEvent">
        <input for="' . $name . '" type="text" class="onlyFloat range_filter-input showControl" name="' . $this->genName('from', true) . '" value="' . $this->genValue('from') . '">
        -
        <input for="' . $name . '" type="text" class="onlyFloat range_filter-input showControl" name="' . $this->genName('to', true) . '" value="' . $this->genValue('to') . '">
        <div class="temp_show" id="' . $name . '-wrap">
          ' . $tiptop . '
        </div>
      </div>';
      Yii::$app->view->registerJs($js, \yii\web\View::POS_READY);
      return $html;
    }

    if ($this->type == 'datetime') {
      return DateRangePicker::widget([
          'model' => $this->model,
          'attribute' => $this->attribute,
          'convertFormat' => true,
          'pluginOptions' => [
              'timePicker' => true,
              'timePickerIncrement' => 30,
              'locale' => [
                  'format' => Yii::$app->params['lang']['datetime_js']
              ],

          ]
      ]);
    };
    return "";
  }

}