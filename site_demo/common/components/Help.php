<?php

namespace common\components;

use kartik\mpdf\Pdf;
use Yii;
use yii\base\Component;

/**
 * Class Help
 * @package frontend\components
 */
class Help extends Component
{

  public static function svg($name, $class = false)
  {
    $path = Yii::getAlias('@app') . '/views/svg/' . $name . '.svg';
    if (!is_readable($path)) {
      return '<pre>Фаил не найден ' . $path . '</pre>';
    }
    $output = file_get_contents($path);
    if ($class) {
      $output = str_replace('<svg', '<svg class="' . $class . '" ', $output);
    }
    return $output;
  }
}