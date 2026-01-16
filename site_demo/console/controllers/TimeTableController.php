<?php

namespace console\controllers;


use frontend\modules\cafe\models\Cafe;
use frontend\modules\templates\models\Template;
use frontend\modules\timetable\models\UserTimetable;
use Yii;
use yii\console\Controller;

class TimeTableController extends Controller
{
  public function actionIndex()
  {
    $timeZoneList = [];
    $script_tz = date_default_timezone_get();

    $period_timetable = Yii::$app->params['period_timetable'];//период запуска крона
    $time = round(time() / $period_timetable) * $period_timetable + Yii::$app->params['alert_timetable'];

    foreach (Yii::$app->params['timeZone'] as $k => $timezone) {
      date_default_timezone_set($timezone);
      $this->sendMeat($k, $time, $time + $period_timetable);
      echo($k . ' ' . date('Y-m-d H:i:s') . "\n");

    };
  }

  /*
   * Приглашение на смену админа
   */
  private function sendMeat($time_zone, $start, $stop)
  {
    $events = UserTimetable::find()
        ->leftJoin(Cafe::tableName(), 'cafe.id=' . UserTimetable::tableName() . '.cafe_id')
        ->leftJoin('cafe_params', 'cafe.params_id = cafe_params.id')
        ->andWhere([
            'and',
            ['>=', 'start', date('Y-m-d H:i:s', $start)],
            ['<', 'start', date('Y-m-d H:i:s', $stop)],
        ])
        ->andWhere(['cafe_params.time_zone' => $time_zone]);

    var_dump($events->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);
    $events = $events->all();

    foreach ($events as $event) {
      $user = $event->user;
      //$user->email = 'matuhinmax@mail.ru';
      if (empty($user->email)) continue;
      $cafe = $event->cafe;
      Yii::$app->cafe->start($cafe->id);//инициализация кафе
      Yii::$app->language = $user->lg; //устанавливаем язык

      $data = [
          'user' => $user->getAttributes(['email', 'name', 'lg']),
          'cafe' => $cafe->getAttributes(['id', 'name', 'max_person', 'address', 'currency', 'vat_code']),
          'event' => $event->getAttributes(['start', 'end']),
      ];
      $template = $cafe->findTemplate(Template::TYPE_TIMETABLE_MAIL);
      $content = $template->renderTemplate($data);

      file_put_contents("test.html", $content);
//exit;
      try {
        $sended = \Yii::$app->mailer->compose()
            ->setFrom(Yii::$app->params['robotEmail'])
            ->setTo($user->email)
            ->setSubject(Yii::t('main', 'Invitation to change to ') . ' ' . $cafe->name)
            ->setHtmlBody($content);

        if (Yii::$app->cafe->get()->pdf_to_mail) {
          Yii::$app->helper->addPdfToMail($sended,$content);
        }
        $sended->send();
      } catch (\Exception $e) {
        $sended = false;
      }
    };
  }

}