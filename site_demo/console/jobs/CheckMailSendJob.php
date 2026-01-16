<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 04.09.18
 * Time: 0:40
 */

namespace console\jobs;

use frontend\modules\shop\models\ShopSale;
use frontend\modules\templates\models\Template;
use frontend\modules\visits\models\VisitorLog;
use Yii;

class CheckMailSendJob extends BaseJob
{
  public $visit_id;
  public $sale_id;

  public function execute($queue)
  {
    if (!empty($this->visit_id)) {
      /* @var $model VisitorLog */
      $model = VisitorLog::find()->andWhere(['id' => $this->visit_id])->one();

      if (!$model) {
        Yii::error('Visit not found');
        return false;
      }
    } else if (!empty($this->sale_id)) {
      /* @var $model VisitorLog */
      $model = ShopSale::find()->andWhere(['id' => $this->sale_id])->one();

      if (!$model) {
        Yii::error('Sale not found');
        return false;
      }
    } else {
      Yii::error('Not found anything');
      return false;
    }

    $cafe = $model->cafe;

    if (!$cafe) {
      Yii::error('Cafe not found');
      return false;
    }

    $language = 'en-EN';
    if ($model->visitor) {
      $language = $model->visitor->lg;
    }

    $email = $model->getVisitorEmail();

    if (empty($email)) {
      Yii::error('Email not found');
      return false;
    }

    /* @var $template Template */
    $template = $cafe->findTemplate(Template::TYPE_CHECK_MAIL);

    $content = $template->renderTemplate($model->getCheckData(), $language, $cafe->id);

    try {
      $sended = \Yii::$app->mailer->compose()
          ->setFrom(Yii::$app->params['robotEmail'])
          ->setTo($email)
          ->setSubject($template->subject)
          ->setHtmlBody($content);

      if ($cafe->pdf_to_mail) {
        Yii::$app->helper->addPdfToMail($sended, $content);
      }
      $sended->send();
    } catch (\Exception $e) {
      var_dump($e->getMessage());
    }
  }

}