<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: max
 * Date: 02.12.18
 * Time: 17:12
 */

use frontend\modules\cafe\models\Cafe;
use frontend\modules\report\models\ReportAutoSend;
use Yii;
use yii\console\Controller;

class ReportController extends Controller
{

  public function actionIndex()
  {
    $timeZoneList = [];
    $script_tz = date_default_timezone_get();

    foreach (Yii::$app->params['timeZone'] as $k => $timezone) {
      $date = new \DateTime('now', new \DateTimeZone($k));
      echo($timezone.'  '.$date->format('H') ."\n");
      if ($date->format('H') == Yii::$app->params['reportHour']) {
        $timeZoneList[] = $k;
      }
    };

    if (count($timeZoneList) == 0) return;
    $d = date('d');
    $w = date('w');

    //ddd(Yii::$app->params['reportHour']);
    $cafes = Cafe::find()
        ->select(['cafe.*', 'cafe_params.first_weekday'])
        ->leftJoin('cafe_params', 'cafe.params_id = cafe_params.id')
        ->where(['cafe_params.time_zone' => $timeZoneList])
        ->asArray()
        ->all();

    foreach ($cafes as $cafe) {
      $to_s = ReportAutoSend::find()
          ->where([
              'cafe_id' => $cafe['id'],
          ])
          ->asArray()
          ->all();
      $report = [
          ReportAutoSend::TYPE_DAILY => [],
          ReportAutoSend::TYPE_WEEKLY => [],
          ReportAutoSend::TYPE_MONTHLY => [],
      ];

      foreach ($to_s as $to) {
        $type = $to['type'];
        if ($type == ReportAutoSend::TYPE_DAILY) {
          $report[$to['type']][] = $to;
          continue;
        }
        $w1 = (7 + $w - $cafe['first_weekday']) % 7;
        if ($type == ReportAutoSend::TYPE_WEEKLY && $w1 == Yii::$app->params['weakReportDate']) {
          $report[$to['type']][] = $to;
          continue;
        }
        if ($type == ReportAutoSend::TYPE_MONTHLY && Yii::$app->params['monthsReportDate'] == $d) {
          $report[$to['type']][] = $to;
          continue;
        }
      }

      foreach ($report as $type => $to) {
        if (empty($to)) continue;

        $data = ReportAutoSend::getArrayReport($type);
        $this->sendReport($to, $cafe, $data);
      }
    }
  }


  private function sendReport($to, $cafe, $data)
  {
    Yii::$app->cafe->start($cafe['id']);//инициализация кафе

    $report = [
        'cafe' => $cafe,
        'blocks' => []
    ];

    $d_ = [];
    $width = 600;
    foreach ($data as $d) {
      $code = $d['period'] . '_' . $d['date_source'];
      $period = ReportAutoSend::getPeriod($d['period'],$cafe['first_weekday']);
      if (!isset($d_[$code])) {
        $d_[$code] = ReportAutoSend::getData($period, $d['date_source'],$cafe['id']);
      }

      $dd=empty($d['data'])?[]:$d['data'];
      $dd['data'] = $d_[$code];
      $dd['period'] = $period;
      $dd['width'] = $width;
      //d($dd);
      $report['blocks'][] = $this->renderPartial($d['layout'], $dd);
    }

    $content = $this->renderPartial('layout', $report);
    file_put_contents("test.html",$content);

    foreach ($to as $t){
      try {
        $sended = \Yii::$app->mailer->compose()
            ->setFrom(Yii::$app->params['robotEmail'])
            ->setTo($t['email'])
            ->setSubject(Yii::t('main', 'Report').' '.$cafe['name'])
            ->setHtmlBody($content);

        if (Yii::$app->cafe->get()->pdf_to_mail) {
          Yii::$app->helper->addPdfToMail($sended,$content);
        }
        $sended->send();
      } catch (\Exception $e) {
        $sended = false;
      }
    }
  }
}