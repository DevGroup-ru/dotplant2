<?php
/**
 * @var yii\web\View $this
 * @var app\backgroundtasks\models\Task $searchModel
 * @var $review \app\modules\review\models\Review
 */
use kartik\helpers\Html;
use app\models\Object;
use app\models\PropertyGroup;
use app\models\Property;
use app\models\Form;
use kartik\icons\Icon;
use yii\widgets\ActiveForm;
use app\modules\review\models\Review;

    $this->title = Yii::t('app', 'Review show');
    $this->params['breadcrumbs'] = [
        ['label' => Yii::t('app', 'Reviews'), 'url' => ['index']],
        $this->title,
    ];

    $formObject = Object::getForClass(Form::className());
    $groups = PropertyGroup::getForModel($formObject->id, $review->submission->form_id);
    $review->submission->getPropertyGroups(true);
?>
<div class="review-show">
    <section id="widget-grid">
        <div class="row">
            <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <div class="jarviswidget">
                    <header>
                        <h2><i class="fa fa-cubes"></i> <?= Yii::t('app', 'Review data')?></h2>
                    </header>
                    <div class="widget-body">
                        <div class="form-group field-page-name">
                            <p class="control-label col-md-2" ><?= Yii::t('app', 'Resource') ?></p>
                            <div class="col-md-10">
                                <?php if (null !== $object = \app\models\Object::findById($review->object_id)) :?>
                                <?php $class = $object->object_class; ?>
                                <?php $resource = $class::findById($review->object_model_id); ?>
                                    <?php if (null !== $resource) : ?>
                                     <?= $resource->name ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-offset-2 col-md-10"></div>
                        </div>
                        <div class="form-group field-page-name">
                            <p class="control-label col-md-2" ><?= Yii::t('app', 'Author email') ?></p>
                            <div class="col-md-10">
                                <?= $review->author_email ?>
                            </div>
                            <div class="col-md-offset-2 col-md-10"></div>
                        </div>
                        <div class="form-group field-page-name">
                            <p class="control-label col-md-2" ><?= Yii::t('app', 'Review text') ?></p>
                            <div class="col-md-10">
                                <?= $review->review_text ?>
                            </div>
                            <div class="col-md-offset-2 col-md-10"></div>
                        </div>
                    </div>
                </div>

                <div class="jarviswidget">
                    <header>
                        <h2><i class="fa fa-cubes"></i> <?= Yii::t('app', 'Submission')?></h2>
                    </header>
                    <div class="widget-body">

                        <div class="form-group field-page-name">
                            <p class="control-label col-md-2" ><?= Yii::t('app', 'Date') ?></p>
                            <div class="col-md-10">
                                <?= Yii::$app->formatter->asDatetime($review->submission->date_received) ?>
                            </div>
                            <div class="col-md-offset-2 col-md-10"></div>
                        </div>

                        <div class="form-group field-page-name">
                            <p class="control-label col-md-2" ><?= Yii::t('app', 'Form') ?></p>
                            <div class="col-md-10">
                                <?php $form = Form::findById($review->submission->form_id);?>
                                <?php if (null !== $form) :?>
                                    <?= Html::encode($form->name) ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-offset-2 col-md-10"></div>
                        </div>

                        <div class="form-group field-page-name">
                            <p class="control-label col-md-2" ><?= Yii::t('app', 'IP') ?></p>
                            <div class="col-md-10">
                                <?= $review->submission->ip ?>
                            </div>
                            <div class="col-md-offset-2 col-md-10"></div>
                        </div>

                        <div class="form-group field-page-name">
                            <p class="control-label col-md-2" ><?= Yii::t('app', 'Useragent') ?></p>
                            <div class="col-md-10">
                                <?= $review->submission->user_agent ?>
                            </div>
                            <div class="col-md-offset-2 col-md-10"></div>
                        </div>

                        <div class="form-group field-page-name">
                            <p class="control-label col-md-2" ><?= Yii::t('app', 'User') ?></p>
                            <div class="col-md-10">
                                <?php if (null !== $user = \app\modules\user\models\User::findIdentity($review->submission->processed_by_user_id) ): ?>
                                    <?= $user->getDisplayName() ?>
                                <?php else : ?>
                                    <?= Yii::t('app', 'Guest') ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-offset-2 col-md-10"></div>
                        </div>

                        <div class="form-group field-page-name">
                            <p class="control-label col-md-2" ><?= Yii::t('app', 'Spam') ?></p>
                            <div class="col-md-10">
                                <?= Yii::$app->formatter->asBoolean($review->submission->spam) ?>
                            </div>
                            <div class="col-md-offset-2 col-md-10"></div>
                        </div>


                    </div>
                </div>

            </article>
            <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <div class="jarviswidget">
                    <header>
                        <h2><i class="fa fa-cubes"></i> <?= Yii::t('app', 'Properties')?></h2>
                    </header>
                    <div class="widget-body">
                    <?php foreach ($groups as $group): ?>
                            <?php $properties = Property::getForGroupId($group->id); ?>
                            <?php foreach ($properties as $property) :?>
                                <?php if ($propertyValues = $review->submission->getPropertyValuesByPropertyId($property->id)): ?>
                                    <?= Html::beginTag('div', ['class' => 'col-md-8']) ?>
                                    <?= $property->handler('form', $review->submission->abstractModel, $propertyValues, 'backend_render_view')?>
                                    <?= Html::endTag('div') ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                    <?php endforeach; ?>
                    </div>
                </div>
                <div class="jarviswidget">
                    <header></header>
                    <div class="widget-body">
                        <?php $form = ActiveForm::begin(['action' => ['update-status', 'id' => $review->id]], ['id' => 'review-form']); ?>
                        <?= $form->field($review, 'status')->dropDownList(Review::getStatuses())?>
                        <div class="form-group no-margin">
                            <?php if ($review->submission->spam == 1): ?>
                                <?=
                                Html::a(
                                    Icon::show('check-square-o') . Yii::t('app', 'Not spam'),
                                    [
                                        'mark-spam',
                                        'id' => $review->submission_id,
                                        'spam' => 0
                                    ],
                                    ['class' => 'btn btn-success']
                                )
                                ?>
                            <?php else: ?>
                                <?=
                                Html::a(
                                    Icon::show('minus-square') . Yii::t('app', 'Mark spam'),
                                    [
                                        'mark-spam',
                                        'id' => $review->submission_id,
                                    ],
                                    ['class' => 'btn btn-danger']
                                )
                                ?>
                            <?php endif; ?>
                            <?=
                            Html::a(
                                Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
                                Yii::$app->request->get('returnUrl', ['/review/backend/index']),
                                ['class' => 'btn btn-danger']
                            )
                            ?>
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
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </article>
        </div>
    </section>
</div>
