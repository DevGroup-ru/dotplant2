<?php

namespace app\modules\shop\helpers;

use app\modules\shop\models\OrderStage;
use app\modules\shop\models\OrderStageLeaf;
use yii\helpers\Url;

class OrderStageHelper
{
    public static function getNextButtons(OrderStage $stage)
    {
        return array_reduce($stage->nextLeafs,
            function ($result, $item)
            {
                /** @var OrderStageLeaf $item */
                $result[] = [
                    'label' => $item->button_label,
                    'css' => $item->button_css_class,
                    'url' => Url::to(['/shop/cart/stage-leaf', 'id' => $item->id], true)
                ];
                return $result;
            }, []);
    }

    public static function getPreviousButtons(OrderStage $stage)
    {
        return array_reduce($stage->prevLeafs,
            function ($result, $item)
            {
                /** @var OrderStageLeaf $item */
                if (0 === intval($item->stageFrom->immutable_by_user)) {
                    $result[] = [
                        'label' => 'Вернуться на предыдущий шаг',
                        'css' => $item->button_css_class,
                        'url' => Url::to(['/shop/cart/stage-leaf', 'id' => $item->id, 'previous' => 1], true)
                    ];
                }
                return $result;
            }, []);
    }
}
?>