<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 20.09.18
 * Time: 19:27
 */

?>
<p>
  <b><?= Yii::t('main', 'Weight'); ?> :</b> <?= $model->weight; ?> <?= Yii::t('main', 'gr.') ?>
</p>
<p>
  <b><?= Yii::t('main', 'Supplier'); ?> :</b> <?= ($model->supplier) ? $model->supplier->title : '-'; ?>
</p>
<p>
  <b><?= Yii::t('main', 'In Stock'); ?> :</b> <?= is_null($model->quantity) ? ''.Yii::t('app', 'infinite amount').'' : $model->quantity; ?>
</p>
<?php if (!empty($model->description)): ?>
  <p>
    <b><?= Yii::t('main', 'Description '); ?> :</b> <?= $model->description; ?>
  </p>
<?php endif; ?>
