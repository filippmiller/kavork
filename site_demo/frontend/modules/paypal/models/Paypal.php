<?php

namespace frontend\modules\paypal\models;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\ExecutePayment;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $order_id
 * @property integer $status
 */
class Paypal extends Model
{
  public $Module;
  public $apiContext;
  public $payer;
  public $paymentType;
  public $itemsList = array();
  public $shipping = 0;
  public $paymentDescription = "Payment without description";


  function __construct($paymentType = 'paypal')
  {
    $this->paymentType = $paymentType;
    $this->init();
  }


  public function init()
  {
    $this->Module = \Yii::$app->getModule('paypal');

    $this->apiContext = new \PayPal\Rest\ApiContext(
        new \PayPal\Auth\OAuthTokenCredential(
            $this->Module->clientId,     // ClientID
            $this->Module->clientSecret      // ClientSecret
        )
    );
    $this->apiContext->setConfig($this->Module->config);
    $this->payer = new Payer();
    $this->payer->setPaymentMethod($this->paymentType);
  }

  public function addItem($item)
  {
    $item = ArrayHelper::merge(
        [
            'name' => 'NO NAME',
            'currency' => $this->Module->config['currency'],
            'quantity' => 1,
            'vat' => 0
        ], $item);
    $this->itemsList[] = $item;
  }

  public function setShipping($val)
  {
    $this->shipping = $val;
  }

  public function setPaymentDescription($val)
  {
    $this->paymentDescription = $val;
  }

  public function make_payment()
  {
    $itemList = new ItemList();
    $subTotal = 0;
    $vat = 0;

    foreach ($this->itemsList as $item) {
      $item1 = new Item();
      $item1->setName($item['name'])
          ->setCurrency($item['currency'])
          ->setQuantity($item['quantity'])
          ->setPrice($item['price']);
      $subTotal += $item['quantity'] * $item['price'];
      $vat += $item['vat'] * $item['quantity'];
      $itemList->addItem($item1);
    }
    $details = new Details();
    $details->setShipping($this->shipping)
        ->setTax($vat)
        ->setSubtotal($subTotal);
    $amount = new Amount();
    $amount->setCurrency($this->Module->config['currency'])
        ->setTotal($subTotal + $vat + $this->shipping)
        ->setDetails($details);
    $transaction = new Transaction();
    $transaction->setAmount($amount)
        ->setItemList($itemList)
        ->setDescription($this->paymentDescription)
        ->setInvoiceNumber(uniqid());
    if ($this->paymentType == 'paypal') {
      $redirectUrls = new RedirectUrls();
      $redirectUrls->setReturnUrl($this->Module->baseUrl . "?success=true")
          ->setCancelUrl($this->Module->baseUrl . "?success=false");
      $payment = new Payment();
      $payment->setIntent('order')
          ->setPayer($this->payer)
          ->setRedirectUrls($redirectUrls)
          ->setTransactions(array($transaction));
    }
    if ($this->paymentType == 'credit_card') {
      $payment = new Payment();
      $payment->setIntent("sale")
          ->setPayer($this->payer)
          ->setTransactions(array($transaction));
    }
    $request = clone $payment;
    try {
      $payment->create($this->apiContext);
    } catch (Exception $ex) {
      echo "Created Payment Order Using PayPal. Please visit the URL to Approve.";
      exit(1);
    }
    //$approvalUrl = $payment->getApprovalLink();
    return $payment;
  }


  public function finishPayment($PayerID, $paymentId)
  {
    $payment = Payment::get($paymentId, $this->apiContext);
    $execution = new PaymentExecution();
    $execution->setPayerId($PayerID);
    try {
      $result = $payment->execute($execution, $this->apiContext);
      try {
        $payment = Payment::get($paymentId, $this->apiContext);
      } catch (\Exception $ex) {
        Yii::$app->session->addFlash('error', Yii::t('app', 'Error Payment. Try later or contact your administrator.'));
        return false;
      }
    } catch (\Exception $ex) {
      Yii::$app->session->addFlash('error', Yii::t('app', 'Executed Payment. Try later or contact your administrator.'));
      return false;
    }

    return $payment;
  }
}