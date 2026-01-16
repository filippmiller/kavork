<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 28.08.18
 * Time: 12:29
 */

namespace frontend\controllers;

use frontend\components\Controller;
use frontend\modules\visits\models\VisitorLog;

/**
 * Test controller
 */
class TestController extends Controller
{

  /*public function actionTest()
  {
    $content='

      <title>294 RUE SAINTE-CATHERINE O,H2X 2A1</title><meta content="exported via StampReady" name="sr_export"><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
      <style type="text/css">
      html { width: 100%; }
      body { -webkit-text-size-adjust: none; -ms-text-size-adjust: none; margin: 0; padding: 0; }
      table { border-spacing: 0; border-collapse: collapse; margin:0 auto; }
      img { display: block !important; }
      </style>
      <table width="100%" border="0" cellpadding="0" cellspacing="0" style=\'background-color:;padding:40px 0;\' bgcolor=\'\'><tr><td align="center" ><table width="700px" border="0" cellpadding="0" cellspacing="0"><tr><td style="text-align: center; background:#ffffff;padding:10px 0px 10px 0px">
  <a style="
      background:#006ac1;
      color: #FFFFFF;
      padding:20px 40px 20px 40px;
      font-size: 25px;
      font-family: Arial;
      border-radius: 12px;
      text-align: center;
      display: inline-block;
      "
    href="en-EN"
  >
    en-EN Button text
  </a>
</td></tr></table></td></tr></table>
';

    $mpdf=Yii::$app->helper->addPdfToMail(false,$content);


    return $mpdf;
  }*/

  /*public function actionIndex()
	{
		return $this->render('test');
	}*/

  public function actionVisit()
  {
    $visit = VisitorLog::find()->one();
    $visit->notice = time();
    $visit->save();
  }
}