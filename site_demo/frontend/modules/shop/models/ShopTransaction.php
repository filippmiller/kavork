<?php

namespace frontend\modules\shop\models;

use common\components\VatHelper;
use frontend\modules\cafe\models\Cafe;
use Yii;
use yii\base\InvalidArgumentException;

/**
 * This is the model class for table "{{%shop_transaction}}".
 *
 * @property int $id
 * @property int $operation_type_id
 * @property int $cafe_id
 * @property int $product_id
 * @property int $sale_id
 * @property int $quantity
 * @property double $price
 * @property double $sum
 * @property double $cost
 * @property array $vat
 * @property string $comment
 * @property string $data
 * @property int $created_by
 * @property int $updated_by
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ShopProduct $product
 * @property ShopProduct $sale
 */
class ShopTransaction extends ShopBaseModel
{
  const OPERATION_TYPE_INCOME = 1;
  const OPERATION_TYPE_SALE = 2;
  const OPERATION_TYPE_WRITE_OFF = 3;

  public $is_sale_page = false;
  public $vat_total;

  // Report variables
  public $_summary_attributes = [];

  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return '{{%shop_transaction}}';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['operation_type_id', 'cafe_id', 'quantity', 'price'], 'required'],
        [['operation_type_id', 'cafe_id', 'product_id', 'sale_id', 'quantity', 'created_by', 'updated_by'], 'integer'],
        [['price', 'sum', 'cost'], 'number'],
        [['vat', 'updated_at', 'created_at'], 'safe'],
        [['comment'], 'string'],
        [['data'], 'safe'],
        [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::className(), 'targetAttribute' => ['product_id' => 'id']],
        [['sale_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopSale::className(), 'targetAttribute' => ['sale_id' => 'id']],
        [['cafe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cafe::className(), 'targetAttribute' => ['cafe_id' => 'id']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'id' => Yii::t('app', 'ID'),
        'operation_type_id' => Yii::t('app', 'Operation Type ID'),
        'product_id' => Yii::t('app', 'Product ID'),
        'cafe_id' => Yii::t('app', 'Cafe ID'),
        'sale_id' => Yii::t('app', 'Sale ID'),
        'quantity' => Yii::t('app', 'Quantity'),
        'price' => Yii::t('app', 'Price'),
        'sum' => Yii::t('app', 'Sum'),
        'cost' => Yii::t('app', 'Cost'),
        'vat' => Yii::t('app', 'Vat'),
        'vat_total' => Yii::t('app', 'Total vat'),
        'comment' => Yii::t('app', 'Comment'),
        'data' => Yii::t('app', 'Data'),
        'created_by' => Yii::t('app', 'Created By'),
        'updated_by' => Yii::t('app', 'Updated By'),
        'updated_at' => Yii::t('app', 'Updated At'),
        'created_at' => Yii::t('app', 'Created At'),

        '_item_weight' => Yii::t('app', 'Weight'),
        '_visitor_name' => Yii::t('app', 'Buyer name (ID)'),
        '_product_title' => Yii::t('app', 'Item'),
        '_product_barcode' => Yii::t('app', 'Barcode'),
    ];
  }

  public static function makeIncome($product, $quantity, $params = [])
  {
    return self::createTransaction(self::OPERATION_TYPE_INCOME, $product, $quantity, $params);
  }

  public static function makeSale($product, $quantity, $params = [])
  {
    return self::createTransaction(self::OPERATION_TYPE_SALE, $product, $quantity, $params);
  }

  public static function makeWriteOff($product, $quantity, $params = [])
  {
    return self::createTransaction(self::OPERATION_TYPE_WRITE_OFF, $product, $quantity, $params);
  }

  public static function createTransaction($operType, $product, $quantity, $params = [])
  {
    if (is_numeric($product)) {
      $product = ShopProduct::find()->andWhere([
          'id' => $product,
          'is_active' => true,
      ])->one();
    }

    if (!$product) {
      throw new InvalidArgumentException('Product not found');
    }

    $isFakeProduct = $product->isNewRecord;

    /* @var $product ShopProduct */

    $cafe = null;

    if (isset($params['cafe'])) {
      $cafe = $params['cafe'];
    }

    if ($cafe === null) {
      $cafe = Yii::$app->cafe->get();
    }

    if (!$cafe) {
      throw new InvalidArgumentException('Cafe not found');
    }

    if (!$isFakeProduct) {
      $warehouse = $product->getWarehouse($cafe->id)->one();

      if (!$warehouse) {
        $warehouse = new ShopWarehouse();
        $warehouse->setAttributes([
            'cafe_id' => $cafe->id,
            'product_id' => $product->id,
            'quantity' => 0,
        ]);
      }

      $diffQuantity = $quantity;
      if (in_array($operType, self::negativeQuantityOperations())) {
        $diffQuantity = $diffQuantity * -1; // Making quantity NEGATIVE

        if ($warehouse->quantity < $diffQuantity) {
          throw new InvalidArgumentException('Product quantity not enough to create transaction');
        }
      }

      $warehouse->quantity += $diffQuantity;
    }

    $dbTransaction = Yii::$app->getDb()->beginTransaction();
    try {
      $transaction = new ShopTransaction();
      $transaction->setAttributes([
          'operation_type_id' => $operType,
          'cafe_id' => $cafe->id,
          'quantity' => $quantity,
      ]);

      if ($isFakeProduct) {
        // Storing FAKE PRODUCT title
        $d['product_title'] = $product->title;
        $transaction->data = $d;
      } else {
        $transaction->product_id = $product->id;
      }

      if (isset($params['sale'])) {
        $sale = null;

        if ($params['sale'] instanceof ShopSale) {
          $sale = $params['sale'];
        } else if (is_array($params['sale'])) {
          if (isset($params['sale']['visitor_log_id'])) {
            $sale = ShopSale::find()->where([
                'visitor_log_id' => $params['sale']['visitor_log_id'],
                'pay_state' => 0
            ])->one();
          }

          if (!$sale) {
            $sale = new ShopSale();
            $sale->setAttributes($params['sale']);
            if (!$sale->save()) {
              throw new \Exception('Sale save error');
            }
          }
        }

        if ($sale) {
          $transaction->sale_id = $sale->id;
        }
      }

      if (isset($params['price'])) {
        $productPrice = $params['price'];
      } else {
        $productPrice = $product->price;
      }

      $transaction->price = $productPrice;

	    self::updateMoneyFields($cafe, $transaction, $product->tax_required);

      $saved = true;
      if (!$isFakeProduct) {
        $saved = $warehouse->save();
      }

      if ($saved && $transaction->save()) {
        $dbTransaction->commit();
        return true;
      }

      throw new \Exception('Transaction save error');
    } catch (\Exception $e) {
      $dbTransaction->rollBack();
    }

    return false;
  }

  public function deleteWithProductReturn($quantityToRemove = null)
  {
    $transaction = Yii::$app->db->beginTransaction();

    try {
      $saved = true;
      $deleted = false;

      if ($this->product) {
        $warehouse = $this->product->getWarehouse($this->cafe_id)->one();

	    if ($quantityToRemove === null) {
		    $quantity = $this->quantity;
	    } else {
		    $quantity = (int) $quantityToRemove;
		    $this->quantity = $this->quantity - $quantity;
	    }

        if ($warehouse) {
          if (!in_array($this->operation_type_id, self::negativeQuantityOperations())) {
            $quantity = $quantity * -1; // Making quantity NEGATIVE
          }

          $warehouse->quantity = $warehouse->quantity + $quantity;
          $saved = $warehouse->save();
        } else {
          $saved = true;
        }
      }

      if ($saved) {
      	if ($this->quantity > 0) {
	        self::updateMoneyFields($this->cafe, $this, $this->product ? $this->product->tax_required : true);
	        $saved = $this->save();
        }
        $deleted = $this->delete();
      }

      if ($saved && $deleted) {
        $transaction->commit();
        return true;
      }

      throw new \Exception('Product return error');

    } catch (\Exception $e) {
      $transaction->rollBack();
    }

    return false;
  }

  public static function updateMoneyFields($cafe, &$transaction, $tax_required = true) {
	  if ($tax_required) {
		  list($sum, $cost, $vat) = VatHelper::calculate($transaction->price, null, $cafe->param->vat_list);

		  $transaction->sum = $sum * $transaction->quantity;
		  $transaction->cost = $cost * $transaction->quantity;
		  $transaction->vat = $vat;
	  } else {
		  $priceSummary = $transaction->price * $transaction->quantity;

		  $transaction->sum = $priceSummary;
		  $transaction->cost = $priceSummary;
	  }
  }

  public static function negativeQuantityOperations()
  {
    return [
        self::OPERATION_TYPE_SALE,
        self::OPERATION_TYPE_WRITE_OFF,
    ];
  }

  /**
   * @inheritdoc
   */
  public function canGetProperty($name, $checkVars = true, $checkBehaviors = true)
  {
    if (isset($this->_summary_attributes[$name])) {
      return true;
    }

    return parent::canGetProperty($name, $checkVars, $checkBehaviors);
  }

  /**
   * @inheritdoc
   */
  public function canSetProperty($name, $checkVars = true, $checkBehaviors = true)
  {
    if (strpos($name, '_summary_') === 0) {
      return true;
    }

    return parent::canSetProperty($name, $checkVars, $checkBehaviors);
  }

  /**
   * @inheritdoc
   */
  public function __get($name)
  {
    if (strpos($name, '_summary_') === 0 && isset($this->_summary_attributes[$name])) {
      return $this->_summary_attributes[$name];
    }

    return parent::__get($name);
  }

  /**
   * @inheritdoc
   */
  public function __set($name, $value)
  {
    if (strpos($name, '_summary_') === 0) {
      $this->_summary_attributes[$name] = $value;
    } else {
      parent::__set($name, $value);
    }
  }

  public function getProductTitle()
  {
    if (isset($this->product)) {
      return $this->product->title;
    } elseif (!empty($this->data['product_title'])) {
      return $this->data['product_title'];
    }

    return null;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getProduct()
  {
    return $this->hasOne(ShopProduct::className(), ['id' => 'product_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSale()
  {
    return $this->hasOne(ShopSale::className(), ['id' => 'sale_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCafe()
  {
    return $this->hasOne(Cafe::className(), ['id' => 'cafe_id']);
  }
}
