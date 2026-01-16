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
class LandingAsset extends AssetBundle
{
  public $sourcePath = '@frontend/views/site/assets';

  public $css = [
      'css/landing.css',
      'css/swiper.css',
  ];
  //public $css = [
  //	'css/swiper.css',
  //];
  //public $js = [
  //	'js/swiper.min.js',
  //];
  public $js = [
    //'js/lazy-line-painter-1.9.6.min.js',
      'js/swiper.min.js',
	  'js//waypoints.min.js',
	  'js/jquery.counterup.js',
      'js/landing.js',
  ];

  public $depends = [
      'frontend\assets\AppAsset',
      CrudAsset::class,
  ];

  public $publishOptions = [
      'forceCopy' => true,
  ];
}
