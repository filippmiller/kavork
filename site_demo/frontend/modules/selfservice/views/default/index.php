<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 17.10.18
 * Time: 21:20
 */

use yii\helpers\Html;

$cafe = Yii::$app->cafe;

if (!isset($isModal)) {
	$isModal = false;
}

$wrapperClass = $isModal ? '' : 'login-form has-science-blue form-horizontal';

?>

<div class="<?= $wrapperClass ?>">

	<?php if (!$isModal): ?>
	<div class="text-center margin-off">
		<h2 class="fms">kavork</h2>
		<h6><?=Yii::t('landing','franchise management system');?></h6>
		<hr class="has-science-blue">
	</div>
	<?php endif; ?>
    <div class="flex-self">
	<?php
	if ($cafe->can('selfServiceLoginOnlyMode')) {
		
		echo Html::a('<span class="tile-content icon"><span class="icon icon-screen-login"></span></span><span class="brand"><span class="tile-label">' .\Yii::t('app', 'Login only') .'</span></span>', ['/selfservice/default/mode', 'value' => 'selfServiceLoginOnlyMode'], [
			'class' => 'tile bg-science-blue',
		]);
		
	}
     
	if ($cafe->can('selfServiceLogoutOnlyMode')) {
		
		echo Html::a('<span class="tile-content icon"><span class="icon icon-screen-logout"></span></span><span class="brand"><span class="tile-label">' .\Yii::t('app', 'Logout only') .'</span></span>', ['/selfservice/default/mode', 'value' => 'selfServiceLogoutOnlyMode'], [
		
			'class' => 'tile bg-science-blue',
		]);
	
	}

    if ($cafe->can('selfServiceLoginOnlyMode')) {

        echo Html::a(
            '<span class="tile-content icon"><span class="glyphicon glyphicon-qrcode" style="font-size: 40px;"></span></span><span class="brand"><span class="tile-label">' .\Yii::t('app', 'QR input') .'</span></span>',
            ['/selfservice/default/mode', 'value' => 'selfServiceLoginOnlyMode', 'qr-code' => 1],
            ['class' => 'tile bg-science-blue',]
        );

    }

    //if ($cafe->can('selfServiceHybridMode')) {
	//	echo Html::beginTag('div', ['class' => 'form-group margin-bottom-10']);
	//	echo Html::a(\Yii::t('app', 'Login and Logout'), ['/selfservice/default/mode', 'value' => 'selfServiceHybridMode'], [
	//		'class' => 'btn btn-science-blue form-control',
	//	]);
	//	echo Html::endTag('div');
	//}
	
	
	?>
   </div>
</div>