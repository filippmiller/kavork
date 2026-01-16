<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 12.09.18
 * Time: 22:13
 */

namespace common\components;

use yii\base\Component;

class VatHelper extends Component
{
  const ADD_TO_COST_ONLY = 'add_to_cost_only'; // Calculate with "Add to cost" only
  const WITHOUT_ADD_TO_COST = 'without_add_to_cost'; // Calculate without "Add to cost"

  public static function calculate($sum, $cost = null, $vat = null, $params = [])
  {
    if ($cost === null) {
      $cost = $sum;
    }

    if ($vat === null) {
      $vat = \Yii::$app->cafe->params['vat_list'];
    }

    if (!is_array($vat)) {
      $vat = json_decode($vat, true);
    }

    $vatSummary = 0;
    foreach ($vat as $vatIndex => $vatParams) {
      $amount = $vatParams['only_for_base_cost'] ? $sum : $cost;
      $vatValue = $vatParams['value'];

      if ($vatParams['add_to_cost']) {
        if (isset($params[self::WITHOUT_ADD_TO_COST])) {
          continue;
        }
        $vatAmount = round($amount * $vatValue / 100, 2);
        $cost += $vatAmount;
      } else {
        if (isset($params[self::ADD_TO_COST_ONLY])) {
          continue;
        }
        $vatValue = 1 + $vatValue / 100;
        $vatAmount = round($amount - $amount / $vatValue, 2);
      }

      $vatSummary += $vatAmount;
      $vat[$vatIndex]['vat'] = $vatAmount;

      unset($vat[$vatIndex]['only_for_base_cost']);
      unset($vat[$vatIndex]['add_to_cost']);
    }

    // Final rounding...
    $cost = round($cost, 2);
    $sum = round($cost - $vatSummary, 2);

    return [$sum, $cost, $vat, $vatSummary];
  }

}