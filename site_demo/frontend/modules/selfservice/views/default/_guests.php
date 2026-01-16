<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 21.10.18
 * Time: 17:35
 */

use kartik\touchspin\TouchSpin;

?>
<div id="collapseeng" class="panel-collapse collapse">
	<div class="panel-body">
		<div class="col-md-6 col-md-offset-3">
			<div class="margin-bottom-20">
				<div class="col-md-6  margin-bottom-20">
					<div class="text-center">
						<div><span class="adult_text"><?= Yii::t('selfservice', 'adult') ?></span>
						</div>
					</div>
					<?= $form->field($model, 'guest_m')->widget(TouchSpin::class, [
						'pluginOptions' => [
							'min'              => 0,
							'max'              => 13,
							'step'             => 1,
							'decimals'         => 0,
							'boostat'          => 5,
							'maxboostedstep'   => 10,
							'buttondown_class' => 'btn btn-neutral-border',
							'buttonup_class'   => 'btn btn-neutral-border',
							'buttonup_txt'     => '<i class="fa fa-plus"></i>',
							'buttondown_txt'   => '<i class="fa fa-minus"></i>',
						],
					])->label(false); ?>
				</div>
				<div class="col-md-6" style="margin-bottom: 20px">
					<div class="text-center">
						<div><span
								style="font-size: 16px;text-transform: uppercase;font-weight: 600;display: inline-block;max-width: 100%;"><?= Yii::t('selfservice', 'CHILDREN') ?></span>
						</div>
					</div>
					<?= $form->field($model, 'guest_chi')->widget(TouchSpin::class, [
						'pluginOptions' => [
							'min'              => 0,
							'max'              => 13,
							'step'             => 1,
							'decimals'         => 0,
							'boostat'          => 5,
							'maxboostedstep'   => 10,
							'buttondown_class' => 'btn btn-neutral-border',
							'buttonup_class'   => 'btn btn-neutral-border',
							'buttonup_txt'     => '<i class="fa fa-plus"></i>',
							'buttondown_txt'   => '<i class="fa fa-minus"></i>',
						],
					])->label(false); ?>
				</div>
			</div>
		</div>
		<!--<div class="col-md-3 col-xs-12">
			<button type="button" class="btn btn-white-border-2x btn-lg" style="background:#0568CC !important;margin-top: 20px;
    float: right;" data-toggle="collapse" href="#collapseeng" aria-expanded="false"><i class="fa fa-chevron-up"></i>
				<?= Yii::t('selfservice', 'OK, HIDE') ?>
			</button>
		</div>-->
		<div class="row">
			<div class="col-md-12 text-center">
				<div style="font-size:20px;margin-bottom:10px;"><?= Yii::t('selfservice', 'How many person included into anticafe') ?></div>
				<div id="gueststarea" style="height:auto;position:relative;margin:0 auto !important;">
					<div class="_indicator_you_"></div>
					<div class="_indicator_guest_" style=""></div>
					<div class="_indicator_children_" style=""></div>
				</div>
			</div>
		</div>

	</div>
</div>