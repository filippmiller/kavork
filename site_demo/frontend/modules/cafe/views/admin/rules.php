<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 20.08.18
 * Time: 23:39
 */

use frontend\modules\cafe\models\CafeAuthItem;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\cafe\models\Cafe */

$this->title = Yii::t('app', 'Update Cafe: {nameAttribute}', [
    'nameAttribute' => '' . $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Cafes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="cafe-rules-update">

  <?php if (!$isAjax) { ?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php } ?>

  <div class="cafe-vat-accounts-form">

    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'checkboxTemplate' => "<div class=\"checkbox\">\n{beginLabel}\n{input}<span
              class=\"fa fa-check che_2\"></span>\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>",
        ],
    ]); ?>

    <?php
    if (Yii::$app->user->can('AllFranchisee')) {
      $rolesTree = CafeAuthItem::getTree();
    } else {
      $possibleRoles = [];
      $franchiseeRoles = explode(',', $model->franchisee->roles);

      $rolesTree = CafeAuthItem::getTree($franchiseeRoles);
    }

    echo Html::hiddenInput('Cafe[role_ids][]', false);
    ?>

    <?php foreach ($rolesTree as $role): ?>
      <div class="__NESTED_CHECKBOX_PARENT__">
        <div class="checkbox">
          <label>
            <?php
            echo Html::checkbox('Cafe[role_ids][]', in_array($role['name'], $model->role_ids), [
                'class' => '__main_checkbox__',
                'value' => $role['name'],
            ]);
            ?>
            <span class="fa fa-check"></span>
            <?= $role['description'] ?>
          </label>

          <?php if (!empty($role['children'])): ?>
            <span class="children_control">
                <i class="fa fa-plus"></i>
              </span>
          <?php endif; ?>
          <?php if (!empty($role['children'])): ?>
            <div class="__NESTED_CHECKBOX_CHILDREN__" style="padding-left: 25px; padding-bottom: 5px;display:none;">
              <?php foreach ($role['children'] as $childRole): ?>
                <div class="checkbox">
                  <label>
                    <?php
                    echo Html::checkbox('Cafe[role_ids][]', in_array($childRole['name'], $model->role_ids), [
                        'value' => $childRole['name'],
                    ]);
                    ?>
                    <span class="fa fa-check"></span>
                    <?= $childRole['description'] ?>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (!$isAjax) { ?>
      <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
      </div>
    <?php } ?>
    <?php ActiveForm::end(); ?>

  </div>

</div>