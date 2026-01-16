<?php

namespace frontend\assets;

use Yii;
use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
  public $basePath = '@webroot';
  public $baseUrl = '@web';

  public $css = [
     '//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,300,400,600,700,800',
	//'//fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese',
    //'//fonts.googleapis.com/css?family=VT323',
      '//fonts.googleapis.com/css?family=Roboto+Mono:400,700&subset=cyrillic,greek,latin-ext',
      'css/bootstrap.min.css',
      'css/bootkit.css',
      'css/bootkit.essentials.min.css',
      'css/index-tiles.css',
      'css/login.css',
      'css/docs.css',
      'css/table.css',
      'css/site.css',
      'css/editor.css',
      'css/shop.css',
  ];

  public $js = [
      '//code.jquery.com/ui/1.12.1/jquery-ui.js',
      'js/twig.min.js',
      'js/hashcode.js',
      'js/hashcode.js',
      'js/my.js',
      'js/editor.js',
    //"/js/jquery.ui.touch-punch.min.js",
  ];

  public $depends = [
      'frontend\assets\AppInternetExplorerAsset',
      'yii\web\YiiAsset',
      'yii\bootstrap\BootstrapAsset',
      'yii\bootstrap\BootstrapPluginAsset',
      'mihaildev\elfinder\Assets',
      'mihaildev\ckeditor\Assets',
      'kartik\growl\GrowlAsset',  // Notification
      'kartik\base\AnimateAsset', // Notification Animation
  ];

  public $jsOptions = [
      'charset' => 'utf-8',
  ];

  public function init()
  {
    parent::init();

    \mihaildev\elfinder\Assets::addLangFile(Yii::$app->language, Yii::$app->getView());
  }
}
