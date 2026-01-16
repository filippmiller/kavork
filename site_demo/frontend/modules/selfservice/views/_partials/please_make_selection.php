<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 17.10.18
 * Time: 22:34
 */
?>
<div class="row">
<div class="col-md-12">
	<div class="text-center">
		<div class="text-center margin-top-10">
			<div class="fg-white font-size-24">
        <?php
        if(
            !Yii::$app->cafe->can('Announcement') ||
            empty(Yii::$app->cafe->get()->selfmode_banner) ||
            empty(Yii::$app->cafe->get()->selfmode_banner[Yii::$app->language])
        ){
        ?>
				<i class="icon-metro-arrow-up-4 select_icon"></i>
				<div class="text_select"><?= Yii::t('selfservice', 'Please make the selection') ?></div>
        <?php } else {
         echo  Yii::$app->cafe->get()->selfmode_banner[Yii::$app->language];
        }?>
			</div>
		</div>
	</div>
</div>
</div>
