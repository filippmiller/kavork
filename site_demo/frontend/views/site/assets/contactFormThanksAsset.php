<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 17.10.18
 * Time: 22:22
 */

namespace frontend\views\site\assets;

use johnitvn\ajaxcrud\CrudAsset;
use yii\web\AssetBundle;

/**
 * LandingAsset module asset bundle.
 */
class contactFormThanksAsset extends AssetBundle
{
  public $sourcePath = '@frontend/views/site/assets';

  public $js = [
      'js/contactFormThanks.js'
  ];

  public $depends = [
      'frontend\assets\AppAsset',
      CrudAsset::class,
  ];

  public $publishOptions = [
      'forceCopy' => true,
  ];
}
