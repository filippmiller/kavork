<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 21.10.18
 * Time: 13:07
 */
?>
<div style="text-align:center !important;line-height:42px;color:#FFFFFF;font-size:42px;">
	<?= Yii::t('selfservice', 'Welcome') ?> <?= $model->visitor->getFullname(); ?>
	<?php if ($newUser): ?>
		<div class="col-md-12 opis_white" style="padding-top:10px;">
			<?= Yii::t('selfservice', 'Your personal ID') ?>: <span style="color:#EFBE29"><?= $model->visitor->code; ?></span>
		</div>
	<?php endif; ?>
    <div class="col-md-12" style="font-size:30px;"><?= Yii::t('selfservice', 'We are happy to see you at the anticafe!') ?></div>
	<div class="col-md-12" style="padding-top:10px;padding-bottom:2px;color:#FA9A00;"> &nbsp; </div>
	<div class="row" style="line-height:42px;color:#eeeeee;font-size:36px;">

	</div>

		<div class="col-md-12" style="padding-top:10px;padding-bottom:2px;color:#FA9A00;"> &nbsp; </div>
	<div class="row">
		<div class="col-md-2"></div>
		<div class="col-md-8"
		     style="margin-top:10px;text-align:center !important;line-height:36px;color:#fff;background:#7BAD18;font-size:30px;">
			<?= Yii::t('selfservice', 'Your time has started at') ?> <span
				style=""><?= date(Yii::$app->params['lang']['time'], strtotime($model->add_time)); ?></span>
		</div>
		<div class="col-md-2"></div>
	</div>
</div>

<?php include '_go_home.php';?>