<?php
/** @var bool $autocomplete */
/** @var bool $useFontAwesome */
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin(
    [
        'action' => ['/default/search'],
        'id' => 'search-form',
        'method' => 'get',
        'type' => ActiveForm::TYPE_INLINE,

        'options' => [
            'class' => 'search-form',
        ],

    ]
);
$model = new \app\models\Search;
$model->load(Yii::$app->request->get());
$icon = $useFontAwesome
    ? \kartik\icons\Icon::show('search')
    : Html::tag('span', '', ['class'=>'icon-search-field']);

$field = $form->field(
    $model,
    'q',
    [
        'options' => [
            'placeholder' => Yii::t('app', 'Search'),
        ],
        'addon' => [
            'append' => [
                'content' => $icon,
                'options' => [
                    'class' => 'search-append',
                ],
            ],
        ],
    ]
);
if ($autocomplete === true) {
    echo $field->widget(
        \app\widgets\AutoCompleteSearch::className(),
        [
            'id' => 'search-autocomplete-'.uniqid()
        ]
    );
} else {
    echo $field;
}
ActiveForm::end();


$js = <<<JS
$(".search-form input[type=text]").blur(function(){
    $(this).removeClass('active');
});
$(".search-form .search-append").click(function(){
    $(".search-form input[type=text]").addClass('active').focus();
    return false;
});
JS;
$this->registerJs($js);

?>

