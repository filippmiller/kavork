<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 06.09.18
 * Time: 18:57
 */

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Internet Explorer 8 assets
 *
 * HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries
 */
class AppInternetExplorerAsset extends AssetBundle
{
  public $basePath = '@webroot';
  public $baseUrl = '@web';

  public $js = [
      'js/html5shiv.js',
      'js/respond.min.js',
  ];

  public $jsOptions = [
      'condition' => 'lte IE9',
      'position' => \yii\web\View::POS_HEAD,
  ];
}