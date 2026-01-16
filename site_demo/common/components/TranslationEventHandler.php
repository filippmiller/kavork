<?php

namespace common\components;

use Yii;
use yii\i18n\MissingTranslationEvent;

class TranslationEventHandler
{
  public static function handleMissingTranslation(MissingTranslationEvent $event)
  {
    if (Yii::$app->params['defaultLang'] != $event->language) return;

    $path = Yii::getAlias("@common/language/" . $event->language . '/');
    if (!is_dir($path)) {
      mkdir($path, 0777, true);
    }
    $path .= '/' . $event->category . '.php';
    $path = str_replace('//', '/', $path);
    if (is_readable($path)) {
      $lg = require($path);
    } else {
      $lg = [];
    }

    if (!isset($lg[$event->message])) $lg[$event->message] = $event->message;

    $out = "<?php\n  return " . var_export($lg, true) . ";\n?>\n";
    file_put_contents($path, $out);
  }
}