<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 21.09.18
 * Time: 13:52
 */

namespace frontend\modules\shop\controllers;

use common\components\VatHelper;
use console\jobs\CheckMailSendJob;
use frontend\components\Controller;
use frontend\modules\shop\models\ShopProduct;
use frontend\modules\shop\models\ShopSale;
use frontend\modules\shop\models\ShopTransaction;
use frontend\modules\templates\models\Template;
use frontend\modules\visitor\models\Visitor;
use frontend\modules\visits\models\StartVisit;
use frontend\modules\visits\models\VisitorLog;
use kartik\widgets\Typeahead;
use Yii;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * StorefrontController
 */
class StorefrontController extends Controller
{
  public function actionIndex($visit_id = null, $visitor_id = null, $method = null, $pay_method = null, $sale_id = null, $cart = null)
  {
    $request = Yii::$app->request;
    $is_visit = false;

    if ($method === null) {
      $method = $request->post('method');
    }

    $visitor = null;
    if ($visitor_id === null) {
      $startVisitData = $request->post('StartVisit');
      $is_visit = $startVisitData !== null;
      if ($startVisitData !== null && !empty($startVisitData['id'])) {
        $visitor_id = (int)$startVisitData['id'];
      } else {
        if ($startVisitData['id'] == VisitorLog::TYPE_NEW) {
          $visitor = new StartVisit();
          if ($visitor->load($request->post()) && $visitor->save()) {
            $visitor_id = $visitor->id;
          }
        }
      }
    }


    if ($visitor_id && !$visitor) {
      $visitor = Visitor::find()->where(['id' => $visitor_id])->one();
    }

    $cafe = Yii::$app->cafe->get();

    $visit_data = "";
    if ($request->post('VisitorLog')) {
      $visitLog = new VisitorLog();
      if ($visitLog->load($request->post()) && $visitLog->validate()) {
        $visit_data = json_encode($visitLog->getArrayData());
      }
    }

    $visitLog = null;
    if (!empty($visit_id)) {
      $visitLog = VisitorLog::find()
          ->andWhere([
              'cafe_id' => $cafe->id,
              'id' => $visit_id,
          ])
          ->one();
    }

    $transactions = null;
    if ($visitLog !== null) {
      if ($visitLog->shopSale) {
        $transactions = $visitLog->shopSale->transactions;
      }
    }

    switch ($method) {
      case 'recalculate':
        return $this->recalculate($visitLog, $visitor);
        break;
      case 'pay':
        return $this->pay($pay_method, $visitor);
        break;
      case 'start':
        return $this->pay(null, $visitor);
        break;
      case 'view':
        return $this->actionPrint_check($sale_id);
        break;
      case 'cart':
        return $this->cart($visit_id);
        break;
    }

    $products = ShopProduct::find()
        ->active()
        ->inShop()
        ->inCafe()
        ->all();

    Yii::$app->response->format = Response::FORMAT_JSON;

    $produnct_buy = false;
    if ($request->post('products')) {
      try {
        $produnct_buy = json_decode($request->post('products'), true);
      } catch (\Exception $e) {
      }
    }

    return [
        'title' => \Yii::t('app', 'Shop'),
        'content' => $this->renderAjax('content', [
            'cafe' => $cafe,
            'visit' => $visitLog,
            'visitor' => $visitor,
            'products' => $products,
            'produnct_buy' => $produnct_buy,
            'transactions' => $transactions,
            'is_visit' => $is_visit,
            'visit_data' => $visit_data,
            'isCart' => $cart,
        ]),
        'footer' => '<style>.modal-footer:before{display:none}</style><style>.modal-footer{padding:0}</style>',
    ];
  }

  public function recalculateForm()
  {
    $postProducts = Yii::$app->request->post('products');

    $priceSummary = 0;
    $quantitySummary = 0;

    $sum = 0;
    $cost = 0;
    $vat = [];
    $vatSummary = 0;

    if ($postProducts) {
      $productIds = array_keys($postProducts);

      $fakeProductIds = [];
      foreach ($productIds as $productIdIndex => $productId) {
        if ($productId < 0) {
          $fakeProductIds[] = $productId;
          unset($productIds[$productIdIndex]);
        }
      }

      $products = ShopProduct::find()
          ->active()
          ->inShop()
          ->inCafe()
          ->andWhere(['id' => $productIds])
          ->asArray()
          ->indexBy('id')
          ->all();

      foreach ($postProducts as $postProductId => $postProductParams) {
        $itemPrice = null;
        $tax = false;

        if (isset($products[$postProductId])) {
          $tax = $products[$postProductId]['tax_required'];
          $price = $products[$postProductId]['price'];
          $itemPrice = $price * $postProductParams['quantity'];
          $quantitySummary += $postProductParams['quantity'];
        } elseif (in_array($postProductId, $fakeProductIds)) {
          $tax = $postProductParams['tax_required'];
          $price = $postProductParams['price'];
          $itemPrice = $price * $postProductParams['quantity'];
          $quantitySummary += $postProductParams['quantity'];
        }

        if (!empty($itemPrice)) {
          if ($tax) {
            list($itemSum, $itemCost, $itemVat, $itemVatSummary) = VatHelper::calculate($itemPrice);
            $sum += $itemSum;
            $cost += $itemCost;
            $vatSummary += $itemVatSummary;
          } else {
            $sum += $itemPrice;
            $cost += $itemPrice;
          }
        }
      }
    }


    return [$quantitySummary, $sum, $cost, $vat, $vatSummary];
  }

  public function recalculate($visit = null, $visitor = null)
  {
    list($quantitySummary, $sum, $cost, $vat, $vatSummary) = $this->recalculateForm();

    return $this->renderPartial('_footer_summary', [
        'visitor' => $visitor ? $visitor : ($visit ? $visit->visitor : null),
        'sum' => $sum,
        'cost' => $cost,
        'vat' => $vat,
        'vatSummary' => $vatSummary,
        'quantitySummary' => $quantitySummary,
    ]);
  }

  public function pay($method, $visitor = null)
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $request = Yii::$app->request;

    $postProducts = $request->post('products');

    $visit = null;
    if ($method === null) {
      $visit = new VisitorLog();
      $visit->visitor_id = $visitor ? $visitor->id : null;
      $visit->type = $visitor ? VisitorLog::TYPE_REGULAR : VisitorLog::TYPE_ANONYMOUS;
      $visit->user_id = Yii::$app->user->id;

      if ($request->post('visit_data')) {
        try {
          $visit_data = json_decode($request->post('visit_data'), true);
          $visit->load(['VisitorLog' => $visit_data]);
        } catch (Exception $e) {
        }
      }
      $visit->save();
    }

    $sale = null;
    if ($postProducts) {
      $cafe = Yii::$app->cafe->get();
      $productIds = array_keys($postProducts);

      $fakeProductIds = [];
      foreach ($productIds as $productIdIndex => $productId) {
        if ($productId < 0) {
          $fakeProductIds[] = $productId;
          unset($productIds[$productIdIndex]);
        }
      }

      $products = ShopProduct::find()
          ->active()
          ->inShop()
          ->inCafe()
          ->andWhere(['id' => $productIds])
          ->asArray()->indexBy('id')->all();

      $saleParams = [
          'cafe_id' => $cafe->id,
          'pay_state' => $method,
      ];

      if ($visitor) {
        $saleParams['visitor_id'] = $visitor->id;
      }

      if ($visit) {
        $saleParams['visitor_log_id'] = $visit->id;
      }

      $saleModel = new ShopSale();
      $saleModel->setAttributes($saleParams);

      $transactionParams = [];

      if ($saleModel->save()) {
        $sale = $saleModel;
        $transactionParams['sale'] = $sale;
      }

      foreach ($postProducts as $postProductId => $postProductParams) {
        if (isset($products[$postProductId])) {
          ShopTransaction::makeSale((int)$postProductId, $postProductParams['quantity'], $transactionParams);
        } elseif (in_array($postProductId, $fakeProductIds)) {
          $fakeProductModel = new ShopProduct();
          $fakeProductModel->setAttributes([
              'title' => $postProductParams['title'],
              'price' => $postProductParams['price'],
              'tax_required' => $postProductParams['tax_required'],
          ]);
          ShopTransaction::makeSale($fakeProductModel, $postProductParams['quantity'], $transactionParams);
        }
      }
      $saleModel->updateTransaction();

      Yii::$app->session->addFlash('success', Yii::t('app', 'Products sold'));
    }

    if ($visit) {
      Yii::$app->session->addFlash('success', Yii::t('app', 'New visitor added to cafe'));
      return [
          'forceClose' => 'true',
          'content' => Yii::$app->view->closeModal(),
      ];
    }

    if ($sale) {
      return $this->actionPrint_check($sale->id);
    } else {
      return [
          'forceClose' => 'true',
          'content' => Yii::$app->view->closeModal(),
      ];
    }
  }

  public function actionPrint_check($id, $method = null)
  {
    $model = $this->findSale($id);

    Yii::$app->response->format = Response::FORMAT_JSON;

    /* @var $sale ShopSale */

    $pay_method = $model->pay_state;
    $cafe = $model->cafe;

    $request = Yii::$app->request;

    $transactions = $model->transactions;

    $sum = 0;
    $cost = 0;
    $vat = [];
    $vatSummary = 0;
    foreach ($transactions as $transaction) {
      if (!empty($transaction->vat)) {
        list($itemSum, $itemCost, $itemVat, $itemVatSummary) = VatHelper::calculate($transaction->sum);
        $sum += $itemSum;
        $cost += $itemCost;
        $vatSummary += $itemVatSummary;
      } else {
        $sum += $transaction->sum;
        $cost += $transaction->sum;
      }
    }

    $afterContent = '';
    $footer = '';

    $footer .= Html::button(Yii::t('main', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]);

    if ($pay_method == VisitorLog::PAY_METHOD_CASH) {
      $afterContent = $this->renderPartial('_pay_cash_calculator', [
          'cost' => $cost,
          'cafe' => $cafe,
      ]);
    }

    $footer .= Html::a('<i class="fa fa-user"></i> '.Yii::t('app', $model->visitor_id ? "Change user" : "Set user").'',
        ['set-user', 'id' => $model->id],
        [
            'class' => 'btn btn-science-blue',
            'role' => "modal-remote",
        ]
    );

    if (Yii::$app->cafe->can('shopPrintCheck') && $method == null) {
      $this->view->registerJs('app.sale_print_check(' . $model->id . ')');
    }

    if (Yii::$app->cafe->can('endVisitMailCheckManual')) {
      if ($method == VisitorLog::CHECK_MAIL) {
        if ($model->canSendMail()) {
          Yii::$app->session->addFlash('success', Yii::t('app', 'Check sended to mail'));

          Yii::$app->queue->delay(Yii::$app->params['checkMailSendDelay'])->push(new CheckMailSendJob(['sale_id' => $model->id]));
        } else {
          $model->scenario = VisitorLog::SCENARIO_ANONYMOUS_MAIL_SET;

          if ($model->load($request->post()) && $model->validate()) {
            //сохраняем и идем на следующий шаг
            $model->save(false);
            return $this->actionPrint_check($id, VisitorLog::CHECK_MAIL);
          }

          $no_email_button = Html::button(
              Yii::t('app', 'Save and Send'),
              ['class' => 'btn btn-success', 'type' => "submit"]
          );

          $no_email_button .= Html::a('<i class="icon-metro-arrow-left"></i> '. Yii::t('app', 'Back') .'',
              ['/shop/storefront/print_check', 'id' => $model->id],
              ['class' => 'btn btn-default pull-left', "role" => "modal-remote"]
          );

          return [
              'title' => Yii::t('app', "Email set"),
              'content' => $this->renderAjax('check_mail_email_set', [
                      'model' => $model,
                      'cafe' => Yii::$app->cafe,
                  ]),
              'footer' => $no_email_button,
              'closeButton' => false,
          ];
        }
      }

      $buttonMailParams = ['class' => 'btn btn-success', "role" => "modal-remote"];
      if ($method == VisitorLog::CHECK_MAIL) {
        // Check sended
        $buttonMailParams['disabled'] = 'disabled';
      }
      $footer .= Html::a(
          '<i class="fa fa-envelope-o"></i> ' . Yii::t('app', 'Send mail'),
          ['/shop/storefront/print_check', 'id' => $model->id, 'method' => VisitorLog::CHECK_MAIL],
          $buttonMailParams
      );
    }

    if (
        (Yii::$app->cafe->can('endVisitPrintCheckManual') || Yii::$app->cafe->can('endVisitPrintCheckAuto')) &&
        $method == VisitorLog::CHECK_PRINT
    ) {
      Yii::$app->response->format = Response::FORMAT_RAW;
      /* @var $template Template */
      $template = $cafe->findTemplate(Template::TYPE_CHECK_PRINT);
      return $template->renderTemplate($model->getCheckData());
    }

    if (Yii::$app->cafe->can('endVisitPrintCheckManual')) {
      $footer .= Html::button(
          '<i class="fa fa-print"></i> ' . Yii::t('app', 'Print check'),
          ['class' => 'btn btn-info', "onClick" => 'app.sale_print_check(' . $model->id . '); return false;']
      );
    }

    return [
        'title' => \Yii::t('app', 'Check'),
        'content' => $this->renderAjax('pay', [
                'visitor' => $model->visitor,
                'sale' => $model,
                'sum' => $sum,
                'cost' => $cost,
                'vat' => $vat,
                'vatSummary' => $vatSummary,
            ]) . $afterContent,
        'footer' => $footer,
    ];
  }

  public function cart($visit_id = null)
  {
    $postProducts = Yii::$app->request->post('products');

    ShopSale::cart($visit_id, $postProducts);

    Yii::$app->response->format = Response::FORMAT_JSON;
    return [
        'forceClose' => 'true',
        'content' => Yii::$app->view->closeModal(),
    ];
  }

  public function actionSetUser($id)
  {
    $request = Yii::$app->request;

    if (Yii::$app->user->isGuest) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    if (!$request->isAjax) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    $sale = ShopSale::find()->where(['id' => $id])->one();
    if (!$sale) {
      throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $visitor = new StartVisit();

    $type_list = VisitorLog::typeList();
    unset($type_list[VisitorLog::TYPE_GROUP]);

    if ($request->isPost) {
      if ($visitor->load($request->post()) && $visitor->validate()) {
        if ($visitor->type == VisitorLog::TYPE_REGULAR) {
          $visitor = StartVisit::find()->where(['id' => $visitor->id])->one();
          $visitor->load($request->post());
        }
        if ($visitor->type == VisitorLog::TYPE_NEW) {
          $visitor->id = null;
        }

        if (
            count($visitor->errors) == 0 &&
            ($visitor->type == VisitorLog::TYPE_ANONYMOUS || $visitor->save())
        ) {
          if ($visitor->type == VisitorLog::TYPE_ANONYMOUS) {
            $sale->visitor_id = null;
          } else {
            $sale->visitor_id = $visitor->id;
          }
          if ($sale->save()) {
            return $this->actionPrint_check($sale->id);
          }
        }

      }
    } else {
      $visitor->type = VisitorLog::TYPE_ANONYMOUS;

      if ($sale->visitor_id == null || $sale->visitor_id == VisitorLog::TYPE_ANONYMOUS) {
      } else {
        $visitor_ = StartVisit::find()
            ->where(['id' => $sale->visitor_id])
            ->asArray()
            ->one();
        if ($visitor_) {
          $visitor->type = VisitorLog::TYPE_REGULAR;
          $visitor->setAttributes($visitor_);
        }
      }
    }

    $title = Yii::t('app', $visitor->type != VisitorLog::TYPE_ANONYMOUS ? "Change user" : "Set user");
    return [
        'title' => $title,
        'content' => $this->renderAjax('@app/modules/visits/views/default/start.twig', [
            'sale' => $sale,
            'model' => $visitor,
            'type_list' => $type_list,
            'AJ_classname' => Typeahead::classname(),
        ]),
        'footer' =>
            Html::a('<i class="icon-metro-arrow-left"></i> '. Yii::t('app', "Back"). '',
                ['/shop/storefront/index', 'method' => 'view', 'sale_id' => $sale->id],
                [
                    'class' => 'btn btn-default pull-left',
                    'role' => "modal-remote"
                ]
            ) .
            Html::button(Yii::t('app', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
            Html::button($title, ['class' => 'btn btn-primary', 'type' => "submit"])

    ];
  }

  public function actionCartRemove($visit_id = null)
  {
    $request = Yii::$app->request;
    $id = $request->post('id');

    $visit = null;
    if (!empty($visit_id)) {
      $visit = VisitorLog::find()->andWhere(['id' => $visit_id])->one();
    }

    if ($id) {
	    $currentTransaction = ShopTransaction::find()->andWhere(['id' => $id])->one();
      if ($currentTransaction) {
        $sale = $currentTransaction->sale;

        if ($currentTransaction->deleteWithProductReturn()) {
          $transactions = $sale->transactions;

          if ($transactions !== null) {
            $sum = 0;
            $cost = 0;
            $vat = [];
            $vatSummary = 0;
            $quantitySummary = 0;
            foreach ($transactions as $transaction) {
              if (!empty($transaction->vat)) {
                list($itemSum, $itemCost, $itemVat, $itemVatSummary) = VatHelper::calculate($transaction->sum);
                $sum += $itemSum;
                $cost += $itemCost;
                $vatSummary += $itemVatSummary;
                $quantitySummary += $transaction->quantity;
              } else {
                $sum += $transaction->sum;
                $cost += $transaction->sum;
                $quantitySummary += $transaction->quantity;
              }
            }

            return $this->renderPartial('_cart_summary', [
                'visit' => $visit,
                'visitor' => $visit ? $visit->visitor : null,
                'sum' => $sum,
                'cost' => $cost,
                'vat' => $vat,
                'vatSummary' => $vatSummary,
                'quantitySummary' => $quantitySummary,
            ]);
          }
        }
      }
    }

    return '';
  }

	public function actionCartRemoveUnit($visit_id = null)
	{
		$request = Yii::$app->request;
		$id = $request->post('id');

		$visit = null;
		if (!empty($visit_id)) {
			$visit = VisitorLog::find()->andWhere(['id' => $visit_id])->one();
		}

		if ($id) {
			$currentTransaction = ShopTransaction::find()->andWhere(['id' => $id])->one();
			if ($currentTransaction) {
				$sale = $currentTransaction->sale;

				if ($currentTransaction->deleteWithProductReturn(1)) {
					$transactions = $sale->transactions;

					if ($transactions !== null) {
						$sum = 0;
						$cost = 0;
						$vat = [];
						$vatSummary = 0;
						$quantitySummary = 0;
						foreach ($transactions as $transaction) {
							if (!empty($transaction->vat)) {
								list($itemSum, $itemCost, $itemVat, $itemVatSummary) = VatHelper::calculate($transaction->sum);
								$sum += $itemSum;
								$cost += $itemCost;
								$vatSummary += $itemVatSummary;
								$quantitySummary += $transaction->quantity;
							} else {
								$sum += $transaction->sum;
								$cost += $transaction->sum;
								$quantitySummary += $transaction->quantity;
							}
						}

						$cartItemView = $this->renderPartial('_cart_item', [
							'transaction' => $currentTransaction,
						]);

						$cartSummaryView = $this->renderPartial('_cart_summary', [
							'visit' => $visit,
							'visitor' => $visit ? $visit->visitor : null,
							'sum' => $sum,
							'cost' => $cost,
							'vat' => $vat,
							'vatSummary' => $vatSummary,
							'quantitySummary' => $quantitySummary,
						]);

						return $this->asJson([
							'cartItemView'    => $cartItemView,
							'cartSummaryView' => $cartSummaryView,
						]);
					}
				}
			}
		}

		return '';
	}

  public function findSale($id)
  {
    $model = ShopSale::find()->where(['id' => $id])->one();

    if (!$model) {
      throw new NotFoundHttpException('Sale not found');
    }

    return $model;
  }
}