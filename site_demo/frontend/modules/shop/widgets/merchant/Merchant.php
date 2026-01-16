<?php

/**
 * Created by PhpStorm.
 * User: acid
 * Date: 17.09.18
 * Time: 17:50
 */

namespace frontend\modules\shop\widgets\merchant;

use frontend\modules\shop\widgets\merchant\models\MerchantIncomeForm;
use frontend\modules\shop\widgets\merchant\models\MerchantSaleForm;

class Merchant extends \yii\base\Widget
{
  public function run()
  {
    $saleModel = new MerchantSaleForm();
    $incomeModel = new MerchantIncomeForm();

    echo $this->render('form', [
        'saleModel' => $saleModel,
        'incomeModel' => $incomeModel,
    ]);
  }
}