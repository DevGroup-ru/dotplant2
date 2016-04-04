<?php

namespace app\modules\shop\models;

use yii\db\ActiveRecord;
use Yii;
use app\modules\user\models\User;
use app\modules\shop\models\WishlistProduct;
use app\modules\shop\models\Currency;

/**
 * This is the model class for table "{{%wishlist}}".
 * Model fields:
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property bool $default
 * Relations:
 * @property User $user
 * @property WishlistProduct[] $items
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
     * @param $user_id
     * @param array $wishlist_ids
     * @return array
     */
    public static function getWishlist($user_id, $wishlist_ids)
    {
        if ($user_id != 0) {
            return self::findAll(['user_id' => $user_id]);
        }
        return self::findAll([
            'id' => $wishlist_ids,
            'user_id' => $user_id,
        ]);
    }

    /**
     * @param string $title
     * @param $user_id
     * @param array $wishlist_ids
     * @param bool $default
     * @return Wishlist|null
     */
    public static function createWishlist($title, $user_id, $wishlist_ids, $default = true)
    {
        $model = new static;
        $model->user_id = $user_id;
        $model->title = $title;
        $model->default = $default;
        if ($model->save(true)) {
            $sessionWishlists = $wishlist_ids;
            $sessionWishlists[] = $model->id;
            Yii::$app->session->set('wishlists', $sessionWishlists);
            return $model;
        }
        Yii::error('Failed to save wishlist');
        return null;
    }

    /**
     * @param $productId
     * @return bool
     */
    public function addToWishlist($productId)
    {
        if ($productId !== null) {
            foreach ($this->items as $item) {
                if ($item->product_id == $productId) {
                    return false;
                }
            }
            $model = new WishlistProduct();
            $model->wishlist_id = $this->id;
            $model->product_id = $productId;
            if ($model->save(true)) {
                return true;
            }
            Yii::error('Failed to save item in wishlist');
            return false;
        }
        Yii::error('Incorrect product_id');
        return false;
    }

    /**
     * @param $user_id
     * @param array $wishlist_ids
     * @param $wishlistId
     * @return int
     */
    public static function countItems($user_id, $wishlist_ids, $wishlistId = null)
    {
        $query = Wishlist::find();
        $query->where(['user_id' => $user_id])
            ->joinWith('items', true, 'INNER JOIN');
        if (null !== $wishlistId) {
            $query->where([
                self::tableName() . '.id' => $wishlistId,
            ]);
        }
        if ($user_id == 0) {
            $query->andWhere([
                self::tableName() . '.id' => $wishlist_ids,
            ]);
        }
        return $query->count();
    }

    /**
     * @param $id
     * @param $user_id
     * @param array $wishlist_ids
     * @return bool
     */
    public static function setDefaultWishlist($id, $user_id, $wishlist_ids)
    {
        if (null !== $wishlists = Wishlist::getWishlist($user_id, $wishlist_ids)) {
            foreach ($wishlists as $wishlist) {
                /** @var Wishlist $wishlist */
                $wishlist->default = false;
                if ($wishlist->id == $id) {
                    $wishlist->default = true;
                }
                $wishlist->save();
            }
            return true;
        }
        Yii::error('Failed to set default wishlist');
        return false;
    }

    /**
     * @param string $title
     * @return bool
     */
    public function renameWishlist($title)
    {
        $this->title = $title;
        return $this->save(true, ['title']);
    }

    /**
     * @param $itemId
     * @return bool
     */
    public function removeItem($itemId)
    {
        /** @var WishlistProduct $item */

        $item = WishlistProduct::findOne([
            'wishlist_id' => $this->id,
            'product_id' => $itemId
        ]);
        return $item->delete();
    }

    /**
     * @return bool
     */
    public function clearWishlist()
    {
        WishlistProduct::deleteAll(['wishlist_id' => $this->id]);
        return true;
    }

    /**
     * @param $user_id
     * @param array $wishlist_ids
     * @param $wishlistId
     * @param array $selections
     * @return string
     */
    public static function getTotalPrice($user_id, $wishlist_ids, $wishlistId = null, $selections = null)
    {
        /** @var WishlistProduct $item */
        $total_price = 0;
        if (null !== $wishlistId) {
            /** @var Wishlist $wishlist */
            $wishlist = static::findOne([
                'id' => $wishlistId,
                'user_id' => $user_id
            ]);
            if (null !== $selections) {
                foreach ($wishlist->items as $item) {
                    if (in_array($item->product_id, $selections)) {
                        $total_price += $item->product->convertedPrice(null, false);
                    }
                }
            } else {
                foreach ($wishlist->items as $item) {
                    $total_price += $item->product->convertedPrice(null, false);
                }
            }

        } else {
            $wishlists = static::getWishlist($user_id, $wishlist_ids);
            foreach ($wishlists as $wishlist) {
                foreach ($wishlist->items as $item) {
                    $total_price += $item->product->convertedPrice(null, false);
                }
            }
        }
        return Currency::getMainCurrency()->format($total_price);
    }
}
