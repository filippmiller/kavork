<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 04.09.18
 * Time: 0:37
 */

namespace console\jobs;

use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;

abstract class BaseJob extends BaseObject implements RetryableJobInterface
{

  public $_max_attempts = 3;
  public $_ttr = 30;

  public function canRetry($attempt, $error)
  {
    return ($attempt < $this->_max_attempts) && ($error instanceof \Exception);
  }

  public function getTtr()
  {
    return $this->_ttr;
  }
}