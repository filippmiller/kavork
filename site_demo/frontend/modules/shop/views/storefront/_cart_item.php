<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 21.09.18
 * Time: 18:21
 */

?>
<tr>
  <td><?= $transaction->getProductTitle(); ?></td>
  <td class="__quantity__"><?= $transaction->quantity; ?></td>
  <td><?= $transaction->price; ?></td>
  <td><?= $transaction->sum; ?></td>
  <td>
    <button type="button" data-id="<?= $transaction->id; ?>"
            class="__shop_cart_remove__ btn bg-orange btn-group fg-white btn-xs">
      <?= Yii::t('app', 'remove unit'); ?>
    </button>
  </td>
</tr>
