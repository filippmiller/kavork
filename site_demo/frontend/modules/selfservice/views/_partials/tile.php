<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 17.10.18
 * Time: 22:42
 */

use yii\helpers\Html;

$defaultParams = [
	'href'      => '#',
	'class'     => 'bg-dodger-blue',
	'icon'      => false,
	'title'     => 'Title',
	'sub_title' => false,
	'brand'     => false,
];

$params = array_merge($defaultParams, isset($params) ? $params : []);

?>
<div class="tile-link-wrapper super-width height">
	<a class="tile-link super-width height" data-mh="tile" href="<?= $params['href']; ?>">
		<div class="tile super-width <?= $params['class']; ?> link-adjust">
			<div class="super-width">
				<div class="fg-white font-weight-400 icon_but">
					<?php if ($params['icon']): ?>
						<div class="col-sm-2 hidden-xs bord_but" data-mh="tile">
							<h1 class="margin-off"><span class="<?= $params['icon']; ?>"></span></h1>
						</div>
					<?php endif; ?>
					<div class="col-sm-10 col-xs-12 head_but" data-mh="tile">
						<?= $params['title']; ?>
						<?php if ($params['sub_title']): ?>
							<div class="footer_but">
								<?php
								foreach ((array)$params['sub_title'] as $sub_titl) {
									echo Html::tag('div', $sub_titl);
								}
								?>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php if ($params['brand']): ?>
					<div class="brand">
						<div class="tile-label">
							<div class="text-center">
								<?= $params['brand']; ?>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</a>
</div>
