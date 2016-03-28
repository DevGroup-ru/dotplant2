<?php

namespace app\modules\shop\models;

use yii\db\ActiveRecord;
use Yii;
use app\modules\user\models\User;
use app\modules\shop\models\WishlistProduct;
use yii\db\Query;
use app\modules\shop\models\Currency;
use app\modules\shop\models\Order;

/**
 * This is the model class for table "{{%wishlist}}".
 * Model fields:
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property bool $default
 * Relations:
 * @property User $user
 */
class Wishlist extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wishlist}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'title',
                    'default',
                ],
                'required'
            ],
            [
                [
                    'user_id',
                ],
                'integer'
            ],
            [
                [
                    'default',
                ],
                'boolean'
            ],
            [['default'], 'default', 'value' => true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User'),
            'title' => Yii::t('app', 'Title'),
        ];
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return WishlistProduct[]|null
     */
    public function getItems()
    {
        return $this->hasMany(WishlistProduct::className(), ['wishlist_id' => 'id']);
    }

    /**
     * @param $id
     * @return Wishlist|null
     */
    public static function findById($id)
    {
        return self::findOne([
            'id' => $id,
            'user_id' => !Yii::$app->user->isGuest ? Yii::$app->user->id : 0,
        ]);
    }


    /**
     * @param $id
     * @return array
     */
    public static function findByUserId($id)
    {
        if ($id != 0){
            return self::findAll(['user_id' => $id]);
        }
        return self::findAll([
            'id' => Yii::$app->session->get('wishlists', []),
            'user_id' => $id,
        ]);
    }

    /**
     * @param string $title
     * @param bool $default
     * @return Wishlist|null
     */
    public static function createWishlist($title, $default = true)
    {
        $model = new static;
        $model->user_id = !Yii::$app->user->isGuest ? Yii::$app->user->id : 0;
        $model->title = $title;
        $model->default = $default;
        if ($model->validate() && $model->save()){
            $sessionWishlists = Yii::$app->session->get('wishlists', []);
            $sessionWishlists[] = $model->id;
            Yii::$app->session->set('wishlists', $sessionWishlists);
            return $model;
        }
        return null;
    }

    /**
     * @param $productId
     * @return bool
     */
    public function addToWishlist($productId)
    {
        if ($productId !== null){
            foreach ($this->items as $item){
                if ($item->product_id == $productId){
                    return false;
                }
            }
            $model = new WishlistProduct();
            $model->wishlist_id = $this->id;
            $model->product_id = $productId;
            if ($model->validate() && $model->save()){
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param $wishlistId
     * @return int
     */
    public static function countItems($wishlistId = null)
    {
        $query = new Query();
        $query->from([Product::tableName(),WishlistProduct::tableName(), self::tableName()])
            ->where(Product::tableName() . '.id = ' . WishlistProduct::tableName() . '.product_id AND (' . WishlistProduct::tableName() . '.wishlist_id = ' . self::tableName() . '.id)');
        if (null !== $wishlistId){
            $query->andWhere([
                self::tableName() . '.id' => $wishlistId,
            ]);
        }
        if (Yii::$app->user->isGuest){
            $query->andWhere([
                self::tableName() . '.id' => Yii::$app->session->get('wishlists', []),
            ]);
        }
        $query->andWhere([
            self::tableName() . '.user_id' => (!Yii::$app->user->isGuest ? Yii::$app->user->id : 0),
        ]);
        return count($query->all());
    }

    /**
     * @return bool
     */
    public function deleteWishlist()
    {
        /** @var WishlistProduct $item */
        foreach ($this->items as $item){
            $item->delete();
        }
        $this->delete();
        return true;
    }

    /**
     * @return bool
     */
    public function setDefaultWishlist()
    {
        $wishlists = Wishlist::findByUserId(!Yii::$app->user->isGuest ? Yii::$app->user->id : 0);
        foreach ($wishlists as $wishlist){
            /** @var Wishlist $wishlist */
            $wishlist->default = false;
            if ($wishlist->id == $this->id){
                $wishlist->default = true;
            }
            $wishlist->save();
        }
        return true;
    }

    /**
     * @param string $title
     * @return bool
     */
    public function renameWishlist($title)
    {
        $this->title = $title;
        if ($this->validate() && $this->save()){
            return true;
        }
        return false;
    }

    /**
     * @param $itemId
     * @return bool
     */
    public function removeItem($itemId)
    {
        /** @var WishlistProduct $item */
        foreach ($this->items as $item){
            if ($item->product_id == $itemId){
                $item->delete();
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function clearWishlist()
    {
        /** @var WishlistProduct $item */
        foreach ($this->items as $item){
            $item->delete();
        }
        return true;
    }

    /**
     * @param $wishlistId
     * @param array $selections
     * @return string
     */
    public static function getTotalPrice($wishlistId = null, $selections = null)
    {
        /** @var WishlistProduct $item */
        $total_price = 0;
        if (null !== $wishlistId){
            $wishlist = static::findById($wishlistId);
            if (null !== $selections) {
                foreach ($wishlist->items as $item){
                    if (in_array($item->product_id, $selections)) {
                        $total_price += $item->product->convertedPrice(null, false);
                    }
                }
            } else {
                foreach ($wishlist->items as $item){
                    $total_price += $item->product->convertedPrice(null, false);
                }
            }

        } else {
            $wishlists = static::findByUserId(!Yii::$app->user->isGuest ? Yii::$app->user->id : 0);
            foreach ($wishlists as $wishlist){
                foreach ($wishlist->items as $item){
                    $total_price += $item->product->convertedPrice(null, false);
                }
            }
        }
        return Currency::getMainCurrency()->format($total_price);
    }


}
