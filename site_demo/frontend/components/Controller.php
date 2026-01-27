<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 04.04.19
 * Time: 10:43
 */

namespace frontend\components;

/**
 * Class Controller
 * @package frontend\components
 */

class Controller extends \yii\web\Controller
{
  /**
   * @param $data
   * @return mixed
   */
  public function returnBlank($data, $successfulMessage = null)
  {
    if (!empty($successfulMessage)) {
      \Yii::$app->session->addFlash('success', $successfulMessage);
    }

    $data['forceClose'] = true;
    $data['content'] = $this->view->getFlash();

    return $data;
  }
}