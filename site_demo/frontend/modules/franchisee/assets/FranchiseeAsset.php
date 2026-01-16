<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 17.10.18
 * Time: 22:22
 */

namespace frontend\modules\franchisee\assets;

use johnitvn\ajaxcrud\CrudAsset;
use yii\web\AssetBundle;

/**
 * Self Service module asset bundle.
 */
class FranchiseeAsset extends AssetBundle
{
  public $sourcePath = '@frontend/modules/franchisee/assets';

  //public $css = [
  //    'css/franchisee.css',
  //];

  public $js = [
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
