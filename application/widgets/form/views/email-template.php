<?php
/**
 * @var \app\models\Form $form
 * @var \app\models\Submission $submission
 */
?>
<h1><?=$form->name . ' #' . $submission->id;?></h1>
<table>
    <tr>
        <td>Date received</td>
        <td><?=$submission->date_received;?></td>
    </tr>
    <tr>
        <td>IP</td>
        <td><?=$submission->ip;?></td>
    </tr>
    <tr>
        <td>User-Agent</td>
        <td><?=$submission->user_agent;?></td>
    </tr>


</table>
<?php $submission->getPropertyGroups(true,false,true);?>
<?php $properties = $submission->abstractModel->getPropertiesModels(); ?>
<?php foreach ($properties as $property): ?>
    <?php
    $property_values = $submission->getPropertyValuesByPropertyId($property->id);
    echo $property->handler($form, $submission->abstractModel, $property_values, 'backend_render_view');
    ?>
<?php endforeach; ?>