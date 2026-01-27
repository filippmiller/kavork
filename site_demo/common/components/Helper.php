<?php

namespace common\components;

use kartik\mpdf\Pdf;
use yii\base\Component;

class Helper extends Component
{

  public function cafe_where($table = false)
  {
    $cafe = \Yii::$app->cafe;
    $prefix = $table ? $table . '.' : '';

    return [
        'or',
        [
            'and',
            [$prefix . 'cafe_id' => $cafe->id],
            [$prefix . 'franchisee_id' => $cafe->franchiseeId],
        ],
        [
            'and',
            [$prefix . 'cafe_id' => null],
            [$prefix . 'franchisee_id' => $cafe->franchiseeId],
        ]
    ];
  }

  public function echo_time($time, $is_run = false)
  {
    if (!$time || $time < 0) $time = 0;

    $s = $time % 60;
    $time = round(($time - $s) / 60);
    $m = ($time) % 60;
    $h = round(($time - $m) / 60);

    if ($m < 10) $m = '0' . $m;
    if ($s < 10) $s = '0' . $s;
    $out = $h . ':' . $m;

    if ($is_run) {
      $out = str_replace(':', "<span class='separete_blink'></span>", $out);
    }

    return $out;
  }

  public function load_json($file)
  {
    $viewPath = \Yii::getAlias('@frontend/views/json');
    $var = file_get_contents($viewPath . DIRECTORY_SEPARATOR . $file);
    $var = json_decode($var, true);
    return $var;
  }

  public static function in_line($txt)
  {
    $txt = explode("\n", $txt);
    $out = "";
    foreach ($txt as $v) {
      $out .= trim($v);
    }
    return $out;
  }

  public function addPdfToMail($sended, $content)
  {
    $pattern = '#<style[^<]+</style>#i';
    preg_match_all($pattern, $content, $css_inline);
    $css_inline = implode("\n", $css_inline[0]);
    $css_inline = strip_tags($css_inline);

    $content_pdf = preg_replace("#<style[^<]+</style>#is", "", $content);
    $content_pdf = preg_replace("#<title[^<]+</title>#is", "", $content_pdf);

    $mpdf = new Pdf([
        'content' => $content_pdf,
        'cssInline' => $css_inline,
    ]);

    $mpdf->destination = Pdf::DEST_STRING;
    $mpdf = $mpdf->render();

    if ($sended && $mpdf) {
      $sended->attachContent($mpdf, ['fileName' => 'mail_copy.pdf', 'contentType' => 'application/pdf']);
    } else {
      return $mpdf;
    }
  }
}