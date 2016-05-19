<?php

namespace app\modules\seo\behaviors;

use app\modules\shop\models\Product;
use yii\base\Controller;
use yii\base\Behavior;
use yii\helpers\Url;
use Yii;

class SetCanonicalBehavior extends Behavior
{
    public $labels = [
        'gclid',
        'utm_medium',
        'utm_source',
        'utm_campaign',
        'utm_content',
        'utm_term',
        '_openstat'
    ];

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'setCanonical',
        ];
    }

    public function setCanonical()
    {
        $get = Yii::$app->request->get();
        $setCanonical = false;

        foreach ($this->labels as $label) {
            if (array_key_exists($label, $get)) {
                unset($get[$label]);
                $setCanonical = true;
            }
        }

        if ($setCanonical) {
            $get[0] = '/' . Yii::$app->controller->getRoute();
            if ('/' . Yii::$app->requestedAction->controller->route === Yii::getAlias('@product')) {
                $get['model'] = Product::findById(Yii::$app->request->get('model_id'));
                unset($get['model_id']);
            }
            $this->owner->view->registerLinkTag(
                [
                    'rel' => 'canonical',
                    'href' => Url::toRoute($get, true)
                ],
                'canonical'
            );

        }
    }
}
