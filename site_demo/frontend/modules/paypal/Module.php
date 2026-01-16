<?php

namespace frontend\modules\paypal;

use PayPal\Api\ExecutePayment;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;


/**
 * polls module definition class
 */
class Module extends \yii\base\Module
{

  public $clientId;
  public $clientSecret;
  public $baseUrl;
  public $config;

  /**
   * {@inheritdoc}
   */
  public $controllerNamespace = 'frontend\modules\paypal\controllers';

  /**
   * {@inheritdoc}
   */
  public function init()
  {
    parent::init();

    $this->baseUrl = Url::base(true) . '/paypal/default/callback';
    $this->config = ArrayHelper::merge(
        [
            'mode' => 'sandbox', // development (sandbox) or production (live) mode
            'http.ConnectionTimeOut' => 30,
            'http.Retry' => 1,
            'log.LogEnabled' => YII_DEBUG ? 1 : 0,
            'log.FileName' => Yii::getAlias('@runtime/logs/paypal.log'),
            'log.LogLevel' => 'fine',
            'validation.level' => 'log',
            'cache.enabled' => 'true',
            'currency' => "USD",
        ], $this->config);
    if ($this->config['mode'] == 'sandbox') {
      $this->clientId = 'AU2ihGPf9eOoVkl9KnxCIrHtpeYq9dog4EoleYITQydsjKlGvY4HOOxfUnrbTFMZQ28K8r8SnkJIKkES';
      $this->clientSecret = 'EFvVDVmg18-pCCLNiui08WeTGE_bmKDbMEcxrt0GTxq1PfKIcIoC57Qb26EXkyfuAvuv0HpMUIsVq_RE';
    }
    // Set file name of the log if present
    if (isset($this->config['log.FileName'])
        && isset($this->config['log.LogEnabled'])
        && ((bool)$this->config['log.LogEnabled'] == true)
    ) {
      $logFileName = \Yii::getAlias($this->config['log.FileName']);
      if ($logFileName) {
        if (!file_exists($logFileName)) {
          if (!touch($logFileName)) {
            throw new \ErrorException('Can\'t create paypal.log file at: ' . $logFileName);
          }
        }
      }
      $this->config['log.FileName'] = $logFileName;
    }
  }
}
