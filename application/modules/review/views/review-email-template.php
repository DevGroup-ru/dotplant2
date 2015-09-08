<?php

/**
 * @var \app\models\Form $form
 * @var \app\models\Submission $submission
 */

/** @var \app\modules\review\models\Review $review */
$review = \app\modules\review\models\Review::findOne(['submission_id' => $submission->id]);

// @todo Add rating to email

?>
<h1><?= $form->name . ' #' . $submission->id ?></h1>
<table>
    <tr>
        <td><?= Yii::t('app', 'Date received') ?></td>
        <td><?= $submission->date_received ?></td>
    </tr>
    <tr>
        <td><?= Yii::t('app', 'IP') ?></td>
        <td><?= $submission->ip ?></td>
    </tr>
    <tr>
        <td><?= Yii::t('app', 'User-Agent') ?></td>
        <td><?= $submission->user_agent ?></td>
    </tr>
    <?php if ($review !== null): ?>
        <?php
        if ($review->getTargetObjectModel() !== null):
        ?>
            <tr>
                <td><?= Yii::t('app', 'Object') ?></td>
                <td><?= $review->getTargetObject()->name ?></td>
            </tr>
            <?php if (isset($review->getTargetObjectModel()->name)): ?>
            <tr>
                <td><?= Yii::t('app', 'Object model') ?></td>
                <td><?= $review->getTargetObjectModel()->name ?></td>
            </tr>
            <?php endif; ?>
        <?php endif; ?>
        <tr>
            <td><?= $review->getAttributeLabel('author_email') ?></td>
            <td><?= $review->author_email ?></td>
        </tr>
        <tr>
            <td><?= $review->getAttributeLabel('review_text') ?></td>
            <td><?= $review->review_text ?></td>
        </tr>
    <?php endif; ?>
    <?php foreach ($submission->abstractModel->attributes as $name => $value): ?>
        <tr>
            <td><?= $submission->abstractModel->getAttributeLabel($name) ?></td>
            <td><?= $value ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if ($review !== null): ?>
<p>
    <?=
        Yii::t('app', 'See review details ')
        . \kartik\helpers\Html::a(
            Yii::t('app', 'here'),
            'http://' . Yii::$app->getModule('core')->serverName
                . \yii\helpers\Url::to(['/review/backend-review/view', 'id' => $review->id])
        )
    ?>
</p>
<?php endif; ?>
