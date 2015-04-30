<?php

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;

$this->title = Yii::t('app', 'Broken images');
$this->params['breadcrumbs'][] = $this->title;
$frontend = '';
$backend = '';

foreach ($objects as $objectName => $objectsArray) {
    foreach ($objectsArray as $item) {
        switch ($objectName) {
            case 'Page':
                $frontend .= Html::tag(
                    'p',
                    Html::a(
                        $item->name,
                        [
                            '/page/show',
                            'id' => $item->id,
                        ]
                    )
                );
                $backend .= Html::tag(
                    'p',
                    Html::a(
                        $item->name,
                        [
                            '/backend/page/edit',
                            'id' => $item->id,
                            'parent_id' => $item->parent_id,
                        ]
                    )
                );
                break;
            case 'Category':
                $frontend .= Html::tag(
                    'p',
                    Html::a(
                        $item->name,
                        [
                            '/product/list',
                            'category_id' => $item->id,
                            'category_group_id' => $item->category_group_id,
                        ]
                    )
                );
                $backend .= Html::tag(
                    'p',
                    Html::a(
                        $item->name,
                        [
                            '/backend/category/edit',
                            'id' => $item->id,
                            'parent_id' => $item->parent_id,
                        ]
                    )
                );
                break;
            case 'Product':
                $frontend .= Html::tag(
                    'p',
                    Html::a(
                        $item->name,
                        [
                            '/product/show',
                            'model' => $item,
                            'category_group_id' => is_null(
                                $item->mainCategory
                            ) ? null : $item->mainCategory->category_group_id,
                        ]
                    )
                );
                $backend .= Html::tag(
                    'p',
                    Html::a(
                        $item->name,
                        [
                            '/backend/product/edit',
                            'id' => $item->id,
                        ]
                    )
                );
                break;
        }
    }
}
echo Html::beginTag('section', ['id' => 'widget-grid']);
echo Html::beginTag('div', ['class' => 'row']);
echo Html::beginTag('article', ['class' => 'col-xs-12 col-sm-6 col-md-6 col-lg-6']);
BackendWidget::begin(['title' => Yii::t('app', 'Frontend links')]);
echo Html::beginTag('div', ['class' => 'form-group no-margin']);
echo $frontend;
echo Html::endTag('div');
BackendWidget::end();
echo Html::endTag('article');

echo Html::beginTag('article', ['class' => 'col-xs-12 col-sm-6 col-md-6 col-lg-6']);
BackendWidget::begin(['title' => Yii::t('app', 'Backend links')]);
echo Html::beginTag('div', ['class' => 'form-group no-margin']);
echo $backend;
echo Html::endTag('div');
BackendWidget::end();
echo Html::endTag('article');
echo Html::endTag('div');
echo Html::endTag('section');
