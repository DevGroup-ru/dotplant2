<?php

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use kartik\widgets\DateTimePicker;
use vova07\imperavi\Widget as ImperaviWidget;
use yii\helpers\Url;


$this->title = Yii::t('app', 'Page edit');
$this->params['breadcrumbs'][] = ['url' => ['/backend/page/index'], 'label' => Yii::t('app', 'Pages')];
if ($model->parent_id>0) {
    $this->params['breadcrumbs'][] = ['url' => ['/backend/page/index', 'id'=>$model->parent_id, 'parent_id'=>$model->parent->parent_id], 'label' => $model->parent->breadcrumbs_label];
}
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $form = ActiveForm::begin(['id' => 'page-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?php if (!$model->isNewRecord): ?>
        <?=
        Html::a(
            Icon::show('eye') . Yii::t('app', 'Preview'),
            [
                '/page/show',
                'id' => $model->id,
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
        Yii::$app->request->get('returnUrl', ['/backend/page/index']),
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

            <?php BackendWidget::begin(['title'=> Yii::t('app', 'Page'), 'icon'=>'pencil', 'footer'=>$this->blocks['submit']]); ?>

                <?=
                    $form->field($model, 'name')
                ?>

                <?=
                    $form->field($model, 'title')
                ?>

                <?=
                    $form->field($model, 'show_type')
                    ->dropDownList([
                        'show' => Yii::t('app', 'Show page'),
                        'list' => Yii::t('app', 'Page list'),
                    ]);
                ?>

                <?=
                    $form->field(app\models\ViewObject::getByModel($model, true), 'view_id')
                    ->dropDownList(
                        app\models\View::getAllAsArray()
                    );
                ?>

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
                    ],
                ]); ?>

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
                    ],
                ]); ?>

                <?= $form->field($model, 'sort_order'); ?>

                <?= $form->field($model, 'date_added')->widget(DateTimePicker::classname(), [
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd hh:ii',
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        
                    ]
                ]); ?>

                <?= $form->field($model, 'published')->checkbox() ?>



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
            <?php BackendWidget::begin(['title'=> Yii::t('app', 'SEO'), 'icon'=>'search', 'footer'=>$this->blocks['submit']]); ?>

                <?=
                    $form->field($model, 'slug', [
                        'addon' => [
                            'append' => [
                                'content' => Html::button(
                                    Icon::show('code'),
                                    ['class'=>'btn btn-primary', 'id'=>'translit-slug']
                                ),
                                'asButton' => true,
                            ]
                        ]
                    ])
                ?>
                <?= $form->field($model, 'slug_absolute')->checkbox() ?>
                <?=
                $form->field($model, 'subdomain')
                ?>

                <?=
                    $form->field($model, 'h1', [
                        'addon' => [
                            'append' => [
                                'content' => Html::button(
                                    Icon::show('copy'),
                                    ['class'=>'btn btn-primary', 'id'=>'copy-h1']
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
                                    ['class'=>'btn btn-primary', 'id'=>'copy-breadcrumbs_label']
                                ),
                                'asButton' => true,
                            ]
                        ]
                    ])
                ?>

                <?= $form->field($model, 'meta_description')->textarea() ?>

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
                    
                    "#page-title", 
                    "#page-h1", 
                    "#page-breadcrumbs_label"
                ], 
                "#page-slug"
            );
            return false;
        });


        $("#copy-h1").click(function () {
            Admin.copyFrom(
                [
                    
                    "#page-title", 
                    "#page-breadcrumbs_label"
                ], 
                "#page-h1"
            );
            return false;
        });


        $("#copy-breadcrumbs_label").click(function () {
            Admin.copyFrom(
                [
                    
                    "#page-title", 
                    "#page-h1"
                ], 
                "#page-breadcrumbs_label"
            );
            return false;
        });
    });
</script>