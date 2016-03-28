<?php

namespace app\modules\shop\widgets;

use Yii;
use yii\base\Widget;
use app;
use app\modules\shop\models\Wishlist;

class AddToWishlistWidget extends Widget
{

    public $id;
    public $viewFile = 'wishlist';


    public function run()
    {
        $wishlists = Wishlist::findByUserId(!Yii::$app->user->isGuest ? Yii::$app->user->id : 0);
        $model = new Wishlist();
        return $this->render($this->viewFile,
            [
                'id' => $this->id,
                'wishlists' => $wishlists,
                'model' => $model,
            ]
        );
    }
}