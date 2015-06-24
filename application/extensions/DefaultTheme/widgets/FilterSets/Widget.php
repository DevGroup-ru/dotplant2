<?php

namespace app\extensions\DefaultTheme\widgets\FilterSets;

use app\modules\shop\models\FilterSets;
use Yii;
use app\extensions\DefaultTheme\components\BaseWidget;

class Widget extends BaseWidget
{
    public $viewFile = 'filter-sets';
    public $hideEmpty = true;

    /**
     * Actual run function for all widget classes extending BaseWidget
     *
     * @return mixed
     */
    public function widgetRun()
    {
        $categoryId = Yii::$app->request->get('last_category_id');
        if ($categoryId === null) {
            return '<!-- no category_id specified for FilterSet widget in request -->';
        }
        $filterSets = FilterSets::getForCategoryId($categoryId);
        if (count($filterSets) === 0) {
            return '<!-- no filter sets for current category -->';
        }

        if ($this->header === '') {
            $this->header = Yii::t('app', 'Filters');
        }

        return $this->render(
            $this->viewFile,
            [
                'filterSets' => $filterSets,
                'id' => 'filter-set-'.$this->getId(),
                'hideEmpty' => $this->hideEmpty
            ]
        );
    }
}