<?php

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use vova07\imperavi\Widget as ImperaviWidget;
use yii\helpers\Url;

/**
     * @var $this \yii\web\View
     * @var $model \app\models\Category
     */
    $this->title = Yii::t('app', 'Category edit');

    $this->params['breadcrumbs'][] = ['url' => ['/backend/category/index'], 'label' => Yii::t('app', 'Categories')];
    if (($model->parent_id > 0) && (null !== $parent = \app\models\Category::findById($model->parent_id, null, null))) {
        $this->params['breadcrumbs'][] = [
            'url' => [
                '/backend/category/index',
                'id' => $parent->id,
                'parent_id' => $parent->parent_id
            ],
            'label' => $parent->name
        ];
    }
    $this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $form = ActiveForm::begin(['id' => 'category-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?php if (!$model->isNewRecord): ?>
        <?=
        Html::a(
            Icon::show('eye') . Yii::t('app', 'Preview'),
            [
                '/product/list',
                'category_id' => $model->id,
                'category_group_id' => $model->category_group_id,
            ],
            [
                'class' => 'btn btn-info',
                'target' => '_blank',
            ]
        )
        ?>
    <?php endif; ?>
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/backend/product/index']),
        ['class' => 'btn btn-danger']
    )
    ?>
    <?php if ($model->isNewRecord): ?>
        <?=
        Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save & Go next'),
            [
                'class' => 'btn btn-success',
                'name' => 'action',
                'value' => 'next',
            ]
        )
        ?>
    <?php endif; ?>
    <?= Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save & Go back'),
        [
            'class' => 'btn btn-warning',
            'name' => 'action',
            'value' => 'back',
        ]
    ); ?>
    <?=
    Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save'),
        [
            'class' => 'btn btn-primary',
            'name' => 'action',
            'value' => 'save',
        ]
    )
    ?>
</div>
<?php $this->endBlock('submit'); ?>

<section id="widget-grid">
    <div class="row">
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

            <?php BackendWidget::begin(
                [
                    'title' => Yii::t('app', 'Category'),
                    'icon' =>'tree',
                    'footer' => $this->blocks['submit']
                ]
            ); ?>

            <?= $form->field($model, 'active')->widget(\kartik\switchinput\SwitchInput::className()) ?>

            <?php if ($model->parent_id == 0): ?>
                <?=
                $form->field($model, 'category_group_id')
                    ->dropDownList(
                        \app\components\Helper::getModelMap(\app\models\CategoryGroup::className(), 'id', 'name')
                    )
                ?>
            <?php endif; ?>

            <?= $form->field($model, 'name')?>

            <?=
                $form->field($model, 'title', [
                    'addon' => [
                        'append' => [
                            'content' => Html::button(
                                Icon::show('copy'),
                                ['class'=>'btn btn-primary', 'id'=>'copy-title']
                            ),
                            'asButton' => true,
                        ]
                    ]
                ])
            ?>

            <?=
            $form->field(app\models\ViewObject::getByModel($model, true), 'view_id')
                ->dropDownList(
                    app\models\View::getAllAsArray()
                );
            ?>

            <?= $form->field($model, 'announce')->widget(ImperaviWidget::className(), [
                'settings' => [
                    'replaceDivs' => false,
                    'minHeight' => 200,
                    'paragraphize' => true,
                    'pastePlainText' => true,
                    'buttonSource' => true,
                    'plugins' => [
                        'table',
                        'fontsize',
                        'fontfamily',
                        'fontcolor',
                        'video',
                    ],
                    'replaceStyles' => [],
                    'replaceTags' => [],
                    'deniedTags' => [],
                    'removeEmpty' => [],
                ],
            ]); ?>

            <?= $form->field($model, 'content')->widget(ImperaviWidget::className(), [
                'settings' => [
                    'replaceDivs' => false,
                    'minHeight' => 200,
                    'paragraphize' => true,
                    'pastePlainText' => true,
                    'buttonSource' => true,
                    'plugins' => [
                        'table',
                        'fontsize',
                        'fontfamily',
                        'fontcolor',
                        'video',
                    ],
                    'replaceStyles' => [],
                    'replaceTags' => [],
                    'deniedTags' => [],
                    'removeEmpty' => [],
                ],
            ]); ?>

            <?= $form->field($model, 'sort_order'); ?>

            <?php BackendWidget::end(); ?>

            <?php
            BackendWidget::begin(
                [
                    'title'=> Yii::t('app', 'Images'),
                    'icon'=>'image',
                    'footer'=>$this->blocks['submit']
                ]
            ); ?>

            <div id="actions">
                <?=
                \yii\helpers\Html::tag(
                    'span',
                    Icon::show('plus') . Yii::t('app', 'Add files..'),
                    [
                        'class' => 'btn btn-success fileinput-button'
                    ]
                ) ?>
            </div>

            <?= \app\widgets\image\ImageDropzone::widget([
                    'name' => 'file',
                    'url' => ['/backend/product/upload'],
                    'removeUrl' => ['/backend/product/remove'],
                    'uploadDir' => '/theme/resources/product-images',
                    'sortable' => true,
                    'sortableOptions' => [
                        'items' => '.dz-image-preview',
                    ],
                    'objectId' => $object->id,
                    'modelId' => $model->id,
                    'htmlOptions' => [
                        'class' => 'table table-striped files',
                        'id' => 'previews',
                    ],
                    'options' => [
                        'clickable' => ".fileinput-button",
                    ],
                ]); ?>

            <?php BackendWidget::end(); ?>
        </article>

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php BackendWidget::begin(
                [
                    'title' => Yii::t('app', 'SEO'),
                    'icon' => 'cogs',
                    'footer' => $this->blocks['submit']
                ]
            ); ?>

                <?=
                    $form->field($model, 'slug', [
                        'addon' => [
                            'append' => [
                                'content' => Html::button(
                                    Icon::show('code'),
                                    ['class' => 'btn btn-primary', 'id' => 'translit-slug']
                                ),
                                'asButton' => true,
                            ]
                        ]
                    ])
                ?>

                <?=
                    $form->field($model, 'h1', [
                        'addon' => [
                            'append' => [
                                'content' => Html::button(
                                    Icon::show('copy'),
                                    ['class' => 'btn btn-primary', 'id' => 'copy-h1']
                                ),
                                'asButton' => true,
                            ]
                        ]
                    ])
                ?>

                <?=
                    $form->field($model, 'breadcrumbs_label', [
                        'addon' => [
                            'append' => [
                                'content' => Html::button(
                                    Icon::show('copy'),
                                    ['class' => 'btn btn-primary', 'id' => 'copy-breadcrumbs_label']
                                ),
                                'asButton' => true,
                            ]
                        ]
                    ])
                ?>

                <?= $form->field($model, 'meta_description')->textarea() ?>

                <?= $form->field($model, 'title_append')?>

            <?php BackendWidget::end(); ?>

            <?=
                \app\properties\PropertiesWidget::widget([
                    'model' => $model,
                    'form' => $form,
                ]);
            ?>

        </article>
    </div>
</section>

<?php ActiveForm::end(); ?>
<script>
    $(function(){
        $("#translit-slug").click(function () {
            Admin.makeSlug(
                [
                    "#category-name",
                    "#category-title",
                    "#category-h1",
                    "#category-breadcrumbs_label"
                ],
                "#category-slug"
            );
            return false;
        });

        $("#copy-title").click(function () {
            Admin.copyFrom(
                [
                    "#category-name",
                    "#category-h1",
                    "#category-breadcrumbs_label"
                ],
                "#category-title"
            );
            return false;
        });

        $("#copy-h1").click(function () {
            Admin.copyFrom(
                [
                    "#category-name",
                    "#category-title",
                    "#category-breadcrumbs_label"
                ],
                "#category-h1"
            );
            return false;
        });


        $("#copy-breadcrumbs_label").click(function () {
            Admin.copyFrom(
                [
                    "#category-name",
                    "#category-title",
                    "#category-h1"
                ],
                "#category-breadcrumbs_label"
            );
            return false;
        });
    });
</script>