<?php

/**
 * @var $psvDoubledSlugs array
 * @var $this \yii\web\View
 */

use app\models\PropertyStaticValues;
use app\models\PropertyGroup;
use yii\bootstrap\Alert;
use yii\helpers\Html;

?>
<?php
Alert::begin(
    [
        'closeButton' => false,
         'options' => [
             'class' => 'alert-block alert-warning',
         ],
    ]
);
?>
<h4 class="alert-heading"><?= Yii::t('app', 'Doubles searching results') ?></h4>
<div class="panel-group smart-accordion-default" id="accordion">
    <?php if (count($psvDoubledSlugs) > 0): ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" class="collapsed"> <i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> <?= Yii::t('app', 'Property static value') ?> <span class="badge"><?= count($psvDoubledSlugs) ?></span></a></h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
            <div class="panel-body no-padding">
                <table class="table table-bordered table-striped table-condensed table-hover">
                    <thead>
                    <tr>
                        <th><?= Yii::t('app', 'Property group') ?></th>
                        <th><?= Yii::t('app', 'Property') ?></th>
                        <th><?= Yii::t('app', 'Slug') ?> (<?= Yii::t('app', 'Property static value') ?>)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($psvDoubledSlugs as $slug => $properties): ?>
                        <?php foreach ($properties as $property): ?>
                            <tr>
                                <td>
                                    <?=
                                    ($pg = PropertyGroup::findById($property['property_group_id'])) !== null
                                        ? Html::a(
                                            $pg->name,
                                            [
                                                '/backend/properties/group',
                                                'id' => $pg->id,
                                                'returnUrl' => '/backend/dashboard/index',
                                            ]
                                        )
                                        : Yii::t('app', 'Unknown')
                                    ?>
                                </td>
                                <td>
                                    <?=
                                    Html::a(
                                        $property['name'],
                                        [
                                            '/backend/properties/edit-property',
                                            'id' => $property['id'],
                                            'property_group_id' => $property['property_group_id'],
                                            'returnUrl' => '/backend/dashboard/index',
                                        ]
                                    )
                                    ?>
                                </td>
                                <td>
                                    <?=
                                    ($psv = PropertyStaticValues::find()->where(['property_id' => $property['id'], 'slug' => $slug])->one()) !== null
                                        ? Html::a(
                                            $slug,
                                            [
                                                '/backend/properties/edit-static-value',
                                                'id' => $psv->id,
                                                'property_id' => $property['id'],
                                                'property_group_id' => $property['property_group_id'],
                                                'returnUrl' => '/backend/dashboard/index',
                                            ]
                                        )
                                        : Html::encode($slug)
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php Alert::end() ?>
