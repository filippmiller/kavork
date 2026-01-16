<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: max
 * Date: 02.12.18
 * Time: 17:12
 */

use frontend\modules\cafe\models\Cafe;
use frontend\modules\mails\models\MailsLog;
use frontend\modules\mails\models\TemplateMail;
use frontend\modules\visitor\models\Visitor;
use frontend\modules\visits\models\VisitorLog;
use Yii;
use yii\console\Controller;

class MailController extends Controller
{

  private $maxTime = 240;
  private $start_time;

  /**
   * Произвести отправку писем из планировщика /mails/logs
   */
  public function actionIndex()
  {

    $this->start_time = time();

    $task = MailsLog::find()->where(['<', 'status', 2])->one();
    if (!$task) return;

    $cafe = Cafe::find()->where(['id' => $task->cafe_id])->one();
    $mail = new TemplateMail();
    $mail->content = $task->content;

    $users = VisitorLog::find()
        ->select([Visitor::tableName() . '.*', 'notice_visit' => 'visitor_log.notice', 'v_id' => 'max(visitor_log.id)'])
        ->orderBy(Visitor::tableName() . '.id')
        ->leftJoin('visitor', 'visitor_log.visitor_id=visitor.id')
        ->addGroupBy('visitor_log.visitor_id')
        ->addGroupBy('visitor_log.notice')
        ->andHaving('visitor.id is not null or 
          JSON_EXTRACT(
            IF(visitor_log.notice is null or visitor_log.notice = \'\',\'{}\',visitor_log.notice),
             \'$.visitor_email\'
             ) is not null')
        ->andWhere('visitor_log.cafe_id=' . $task->cafe_id)
        ->andWhere('visitor_log.id>' . $task->last_visitor_id);

    $params = json_decode($task->params, true);

    $join_table = [];
    foreach ($params as $code => $param) {

      if ($code == 'visits') {
        if (is_string($param)) $param = explode(',', $param);
        $users->andWhere(['visitor_log.id' => $param]);
        break;
      } elseif ($code == 'visitor') {
        if (is_string($param)) $param = explode(',', $param);
        $users = Visitor::find()
            ->select(Visitor::tableName() . '.*  , id as v_id')
            ->andWhere(['id' => $param]);
        break;
      } else {
        if ($code == 'visit_type') {
          if ($param == -1) {
            $users->andWhere('visitor_log.visitor_id is not null');
          } else if ($params == 0) {
            $users->andWhere('visitor_log.visitor_id is null');
          } else if ($param == 50) {
            $users->andWhere('guest_m>0 or guest_chi>0');
          } else if ($param == 100) {
            $users->andWhere('guest_chi>0');
          } else {
            $users->andWhere('visitor_log.type=' . $param);
          }
          continue;
        }

        if ($code == "visit_date") {
          $users->andWhere('DATE(visitor_log.finish_time)>=' . $param[0]);
          $users->andWhere('DATE(visitor_log.finish_time)<=' . $param[1]);
          continue;
        }

        if ($code == "registration_date") {
          $users->andWhere('DATE(visitor.create)>=' . $param[0]);
          $users->andWhere('DATE(visitor.create)<=' . $param[1]);
          continue;
        }

        if ($code == 'visit_count_max') {
          $users->andHaving('count(visitor_log.visitor_id) >=' . $param);
          continue;
        }
        if ($code == 'visit_count_min') {
          $users->andHaving('count(visitor_log.visitor_id) <=' . $param);
          continue;
        }
      }
    }

    /* foreach ($join_table as $join=>$v){
       if($join='visitor_log'){
         $users->leftJoin($join,'visitor_log.visitor_id=visitor.id')
           ->addGroupBy('visitor_log.visitor_id');

       }
     }*/


    var_dump($users->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);
    d($params);
    $users = $users
        ->offset($task->count)
        ->asArray()
        ->all();

    $cafe->logo = '/img/logos/' . $cafe->logo;
    foreach ($users as $user) {
      if (!$user) continue;
      if (empty($user['email'])) {
        if (empty($user['notice_visit'])) continue;
        $ne = json_decode($user['notice_visit'], true);
        if (empty($ne['visitor_email'])) continue;
        $user['email'] = $ne['visitor_email'];
        $user['lg'] = 'en-EN';//?????
      }

      $content = $mail->renderTemplate([
          'cafe' => $cafe,
          'visitor' => $user
      ], $user['lg'], $cafe->params_id, $cafe->id);

      $task->last_visitor_id = $user['v_id'];
      $task->status = 1;
      $task->save();

      echo 'send to: ' . $user['email'] . '   ';
      //$user['email']='matuhinmax@mail.ru';
      //$user['email']='bara-artur@yandex.ru';
      //$fn=dirname(__FILE__)."/test.txt";
      //file_put_contents($fn,$content);
      try {
        $sended = Yii::$app->mailer->compose()
            ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->params['adminName']])
            ->setTo($user['email'])
            ->setSubject($mail->subject)
            ->setHtmlBody($content);

        if ($cafe->pdf_to_mail) {
          Yii::$app->helper->addPdfToMail($sended, $content);
        }
        $sended->send();
        $task->count++;
        $task->save();
        echo "  ok\n";

        if ($this->maxTime && time() - $this->start_time > $this->maxTime) {
          echo "Finish by timer after " . (time() - $this->start_time) . ' seconds' . "\n";
          exit;
        }
      } catch (\Exception $e) {
        echo "  error\n";
        ddd($e);
      }
    }
    $task->status = 2;
    $task->save();
    echo "  finish task\n";
  }
}
