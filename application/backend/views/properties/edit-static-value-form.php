<?= $form->field($model, 'name')?>

<?= $form->field($model, 'value', [
    'copyFrom' => [
        "#propertystaticvalues-name",
    ]
])?>

<?= $form->field($model, 'slug', [
    'makeSlug' => [
        "#propertystaticvalues-name",
        "#propertystaticvalues-value",
    ]
])?>

<?= $form->field($model, 'sort_order')?>

<?= $form->field($model, 'title_append', [
    'copyFrom' => [
        "#propertystaticvalues-name",
        "#propertystaticvalues-value",
        "#product-breadcrumbs_label",
    ]
])?>
<?= $form->field($model, 'dont_filter')->checkbox() ?>
