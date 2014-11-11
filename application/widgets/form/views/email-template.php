<?php
/**
 * @var \app\models\Form $form
 * @var \app\models\Submission $submission
 */
?>
<h1><?= $form->name . ' #' . $submission->id; ?></h1>
<table>
    <tr>
        <td>IP</td>
        <td><?= $submission->ip; ?></td>
    </tr>
    <tr>
        <td>User-Agent</td>
        <td><?= $submission->user_agent; ?></td>
    </tr>
    <?php foreach ($submission->abstractModel->attributes as $name => $value): ?>
    <tr>
        <td><?= $submission->abstractModel->getAttributeLabel($name); ?></td>
        <td><?= $value; ?></td>
    </tr>
    <?php endforeach; ?>
</table>