<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 17.10.18
 * Time: 22:22
 */

namespace frontend\modules\selfservice\assets;

use johnitvn\ajaxcrud\CrudAsset;
use yii\web\AssetBundle;

/**
 * Self Service module asset bundle.
 */
class SelfServiceAsset extends AssetBundle
{
  public $sourcePath = '@frontend/modules/selfservice/assets';

  public $css = [
      'css/self_service.css',
  ];

  public $js = [
      'js/self_service.js',
	  'js/jquery.matchHeight.js',
  ];

  public $depends = [
      'frontend\assets\AppAsset',
      CrudAsset::class,
  ];

  public $publishOptions = [
      'forceCopy' => true,
  ];
}
