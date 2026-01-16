<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 19.10.18
 * Time: 16:12
 */

use yii\helpers\Url;

$cafe = Yii::$app->cafe;

?>  


    <div class="col-md-12 self_title"><?=Yii::t('selfservice', 'Self-Registration');?></div>
	<div class="col-md-12 welcom">
		<?=Yii::t('selfservice','Hi, we are happy to see you at the anticafe!');?>
	</div>
<div class="col-md-offset-1 col-md-10">

<?php

if (Yii::$app->cafe->can('startVisit') && ($mode == 'selfServiceLoginOnlyMode')) {
	echo $this->render('/_partials/tile', [
		'params' => [
			'href'      => Url::to(['/selfservice/default/new-user']),
			'class'     => 'bg-lima',
			'icon'      => 'fa fa-user-plus',
			'title'     => Yii::t('selfservice','Is this your first time at the anticafe?'),
			'sub_title' => Yii::t('selfservice','Please register to become part of our community!'),
		],
	]);

	echo $this->render('/_partials/tile', [
		'params' => [
			'href'      => Url::to(['/selfservice/default/existing-user']),
			'class'     => 'bg-dodger-blue',
			'icon'      => 'fa fa-user',
			'title'     => Yii::t('selfservice','Have you visited anticafe before?'),
			'sub_title' => Yii::t('selfservice','Please check yourself in to begin your time!'),
		],
	]);
}

//if ($mode == 'selfServiceHybridMode') {
// 	echo $this->render('/_partials/tile', [
//		'params' => [
//			'href'      => Url::to(['/selfservice/default/mode', 'value' => 'selfServiceLoginOnlyMode']),
//			'class'     => 'bg-blue',
//			'icon'      => 'icon-metro-enter',
//			'title'     => Yii::t('selfservice','Self-registration'),
//			'sub_title' => Yii::t('selfservice','Self registration via "Login" or "Registration" '),
//		],
//	]);   



//	echo $this->render('/_partials/tile', [
//		'params' => [
//			'href'      => Url::to(['/selfservice/default/checkout']),
//			'class'     => 'bg-orange',
//			'icon'      => 'icon-metro-exit',
//			'title'     => Yii::t('selfservice','Self-checkout'),
//			'sub_title' => Yii::t('selfservice','Self checkout via "Pay By Card"'),
//		],
//	]);
//}

?>
</div>
<div class="row">
<div class="col-md-12">
<div class="reclama"></div>
<?= $this->render('/_partials/please_make_selection'); ?>
</div>
</div>
