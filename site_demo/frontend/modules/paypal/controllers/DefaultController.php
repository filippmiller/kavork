<?php

namespace frontend\modules\paypal\controllers;

use frontend\modules\franchisee\models\FranchiseePayments;
use frontend\modules\paypal\models\Paypal;
use frontend\modules\users\models\Users;
use Yii;

class DefaultController extends \frontend\components\Controller
{

  /*public function actionIndex()
  {
    $payment = FranchiseePayments::find()
        ->andWhere(['id' => 12])
        ->one();

    $payment->applyTariff();
    return 12;
  }*/
  /*public function actionIndex()
  {
    $payment = new Paypal();
    $payment->addItem([
        'price'=>7
    ]);
    $payment = $payment->make_payment();
    ddd($payment, $payment->getToken(),$payment->getApprovalLink());
    return 12;
  }/**/

  /*public function actionIndex(){
    $user_data = Users::find()->one()->toArray();
    return Yii::$app->view->renderFile('@console/views/mails/new_user.twig',$user_data);
  }*/

  public function actionCallback($token, $success, $paymentId = false, $PayerID = false)
  {
    $payment = FranchiseePayments::find()
        ->where([
            'code' => $token,
        ])
        ->one();

    if (!$payment) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    if ($success == "false") {
      if ($payment->status == FranchiseePayments::STATUS_WAIT) {
        $payment->status = FranchiseePayments::STATUS_CANCEL;
        $payment->save();
        Yii::$app->session->addFlash('success', Yii::t('app', 'Payment status change to CANCEL'));
      } else {
        Yii::$app->session->addFlash('error', Yii::t('app', 'Payment status can\'t changed'));
      }
      return $this->redirect('/franchisee/payments');
    }

    $pay = new Paypal();
    $paymentAns = $pay->finishPayment($PayerID, $paymentId);
    //ddd(Yii::$app->session->getAllFlashes());
    if (!$paymentAns) {
      return $this->redirect('/franchisee/payments');
    }

    if ($paymentAns->getState() == 'approved') {
      $payment->status = FranchiseePayments::STATUS_DONE;
      $payment->save();
      Yii::$app->session->addFlash('success', Yii::t('app', 'Payment status is approved'));
      $payment->applyTariff();
      return $this->redirect('/franchisee/payments');
    }

    throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
    return false;
  }
}