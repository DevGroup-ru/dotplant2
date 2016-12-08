<?php
/**
 * @var yii\web\View $this
 * @var app\backgroundtasks\models\Task $searchModel
 * @var \app\modules\review\models\Review|\app\properties\HasProperties $review
 */
use app\models\Object;
use app\models\PropertyGroup;
use app\models\Form;
use kartik\form\ActiveForm;
use app\backend\widgets\BackendWidget;
use app\modules\review\models\Review;
use yii\helpers\Html;
use kartik\icons\Icon;
use app\components\Helper;

    $this->title = Yii::t('app', 'Review show');
    $this->params['breadcrumbs'] = [
        ['label' => Yii::t('app', 'Reviews'), 'url' => ['index']],
        $this->title,
    ];

    /** @var \app\models\Submission|\app\properties\HasProperties $submission */
    $submission = $review->submission;
    if (null !== $submission) {
        $formObject = Object::getForClass(Form::className());
        $groups = PropertyGroup::getForModel($formObject->id, $submission->form_id);
        $submission->getPropertyGroups(true);
    } else {
        $groups = [];
    }
?>

    <?php $this->beginBlock('buttons'); ?>
    <div class="form-group no-margin">
        <?php if (false === $review->isNewRecord): ?>
        <?= Html::a(Icon::show('minus-square') . Yii::t('app', 'Mark spam'),
            [
                'mark-spam',
                'id' => $review->submission_id,
            ],
            ['class' => 'btn btn-danger']
        ); ?>
        <?php endif; ?>
        <div class="btn-group">
        <?php if (false === $review->isNewRecord): ?>
            <?= Html::a(Icon::show('file') . Yii::t('app', 'New'),
                \yii\helpers\Url::toRoute(['create', 'parent_id' => $review->id]),
                ['class' => 'btn btn-success']
            ); ?>
        <?php endif; ?>
        <?= Html::a(Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
                Yii::$app->request->get('returnUrl', ['/review/backend/index']),
                ['class' => 'btn btn-default']
            ); ?>
        <?= Html::submitButton(Icon::show('save') . Yii::t('app', 'Save'), [
                'class' => 'btn btn-primary',
                'name' => 'action',
                'value' => 'save',
            ]); ?>
        </div>
    </div>
    <?php $this->endBlock(); ?>

<?php
    $form = ActiveForm::begin([
        'id' => 'product-form',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]);
?>
<div class="review-show">
    <div class="row">
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php
                BackendWidget::begin([
                    'title' => Yii::t('app', 'Reviews'),
                    'icon' => 'pencil',
                    'footer' => $this->blocks['buttons']
                ]);
            ?>
            <?= $form->field($review, 'object_id')->dropDownList(
                array_merge(
                    [0 => ''],
                    Helper::getModelMap(Object::className(), 'id', 'name', true, true)
                )
            ); ?>
            <?php
            /**
             * @TODO: Create link for view/edit object
             */
            $_jsDataFunc = <<< 'JSCODE'
function (term, page) {
    return {
        search: {term:term.term, object:$('select#review-object_id').val()}
    };
}
JSCODE;
            $_jsTemplateResultFunc = <<< 'JSCODE'
function (data) {
    if (data.loading) return data.text;
    var tpl = '<div class="s2object-result">' +
        '<strong>' + (data.name || '') + '</strong>' +
        '<a href="' + (data.url || '#') + '">ссылка</a>' +
        '</div>';
    return tpl;
}
JSCODE;

            echo \app\backend\widgets\Select2Ajax::widget([
                'initialData' => null !== $review->targetObjectModel ? [[$review->targetObjectModel->id => $review->targetObjectModel->name]]: [],
                'form' => $form,
                'model' => $review,
                'modelAttribute' => 'object_model_id',
                'multiple' => false,
                'searchUrl' => \yii\helpers\Url::to(['ajax-search']),
                'additional' => [
                    'placeholder' => 'Поиск ...',
                ],
                'pluginOptions' => [
                    'escapeMarkup' => new \yii\web\JsExpression('function (markup) {return markup;}'),
                    'templateResult' => new \yii\web\JsExpression($_jsTemplateResultFunc),
                    'templateSelection' => new \yii\web\JsExpression('function (data) {return data.name || data.text;}'),
                    'ajax' => [
                        'data' => new \yii\web\JsExpression($_jsDataFunc),
                        'delay' => 500,
                    ]
                ]
            ]);
            ?>
            <?= $form->field($review, 'author_email'); ?>
            <?= $form->field($review, 'review_text')
                ->widget(Yii::$app->getModule('core')->wysiwyg_class_name(), Yii::$app->getModule('core')->wysiwyg_params()); ?>
            <?= $form->field($review, 'status')->dropDownList(Review::getStatuses()); ?>
            <?php BackendWidget::end(); ?>
        </article>

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php
            if (null !== $submission):
            BackendWidget::begin([
                'title' => Yii::t('app', 'Properties'),
                'icon' => 'cubes',
            ]);
            ?>
            <?=
                array_reduce($groups,
                    function ($result, $item) use ($submission)
                    {
                        /** @var PropertyGroup $item */
                        $result .= array_reduce(\app\models\Property::getForGroupId($item->id),
                            function ($res, $prop) use ($submission)
                            {
                                /** @var \app\models\Property $prop */
                                if (null !== $val = $submission->getPropertyValuesByPropertyId($prop->id)) {
                                    $html = Html::tag('div',
                                        $prop->handler('form', $submission->abstractModel, $val, 'backend_render_view'),
                                        ['class' => 'col-md-8']
                                    );
                                    $res .= Html::tag('div', $html, ['class' => 'form-group']);
                                }
                                return $res;
                            }, '');
                        return $result;
                    }, '');
            ?>

                <?= $form->field($submission, 'date_received'); ?>
                <?= $form->field($submission, 'form_id'); ?>
                <?= $form->field($submission, 'ip'); ?>
                <?= $form->field($submission, 'user_agent'); ?>
                <?= $form->field($submission, 'processed_by_user_id'); ?>
                <?= $form->field($submission, 'spam'); ?>
            <?php
                BackendWidget::end();
                endif;
            ?>

            <?php
            if (false === $review->isNewRecord):
                BackendWidget::begin([
                    'title' => Yii::t('app', 'Reviews'),
                    'icon' => 'cubes',
                ]);
            ?>
            <?=
                \devgroup\JsTreeWidget\TreeWidget::widget([
                    'treeDataRoute' => ['ajax-get-tree', 'root_id' => $review->root_id, 'current_id' => $review->id],
                    'doubleClickAction' => \devgroup\JsTreeWidget\ContextMenuHelper::actionUrl(
                        ['view']
                    ),
                    'contextMenuItems' => [
                        'edit' => [
                            'label' => 'Edit',
                            'icon' => 'fa fa-pencil',
                            'action' => \devgroup\JsTreeWidget\ContextMenuHelper::actionUrl(
                                ['view']
                            ),
                        ],
                        'delete' => [
                            'label' => 'Delete',
                            'icon' => 'fa fa-trash-o',
                            'action' => new \yii\web\JsExpression(
                                "function(node) {
                                jQuery('#delete-category-confirmation')
                                    .attr('data-url', '/review/backend-review/delete?id=' + jQuery(node.reference[0]).data('id'))
                                    .attr('data-items', '')
                                    .modal('show');
                                return true;
                            }"
                            ),
                        ],
                    ],
                    'options' => [
                        'types' => [
                            'leaf' => ['icon' => 'fa fa-comments'],
                            'current' => ['icon' => 'fa fa-check'],
                        ],
                    ]
                ]);
            ?>
            <?php
                BackendWidget::end();
            endif;
            ?>
        </article>
    </div>
</div>
<?php
$event = new \app\backend\events\BackendEntityEditFormEvent($form, $review);
$this->trigger(\app\modules\review\controllers\BackendReviewController::BACKEND_REVIEW_EDIT_FORM, $event);
$form->end();

$_js = <<<'JSCODE'
$(function(){
    $('select#review-object_id').on('change', function(event) {
        $('select#review-object_model_id').val(0).trigger('change');
    });
});
JSCODE;

$this->registerJs($_js, \yii\web\View::POS_END);
