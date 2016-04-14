<?php

namespace app\modules\core\behaviors;

use yii\base\Behavior;
use yii\base\Controller;
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

    private $_canonical;

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'setCanonical',
        ];
    }

    public function setCanonical()
    {
        $request = Yii::$app->request;
        $setCanonical = false;
        $queryParams = !empty($request->queryParams) ? $request->queryParams : [];
        $this->_canonical = $request->absoluteUrl;

        foreach ($this->labels as $label) {
            foreach ($queryParams as $key => $value) {
                if ($key == $label) {
                    if (false == empty($value)) {
                        $rm = $key .'='. $value;
                    } else {
                        $rm = $key;
                    }
                    $this->_canonical = str_replace(['?'.$rm.'&', '?'.$rm, '&'.$rm], ['?','',''], $this->_canonical);
                    $setCanonical = true;
                }
            }
        }

        if ($setCanonical) {
            $this->owner->view->registerLinkTag(
                [
                    'rel' => 'canonical',
                    'href' => $this->_canonical
                ]
            );
        }
    }
}