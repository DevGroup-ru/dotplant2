<?php
/** @var \app\modules\review\models\Review $review */

use app\modules\shop\models\Product;
use yii\helpers\Html;
use yii\helpers\Url;

?>
    <h1><?=Yii::t('app', 'Review #{reviewId}', ['reviewId' => $review->id])?></h1>
    <h2><?=Yii::t('app', 'Review information')?></h2>
    <table style="width: 800px;" border="1" bordercolor="#ddd" cellspacing="0">
        <tr>
            <th style="text-align: left;"><?=$review->getAttributeLabel('date_submitted')?></th>
            <td><?=$review->date_submitted?></td>
        </tr>
        <tr style="background: #f5f5f5;">
            <th style="text-align: left;"><?=$review->getAttributeLabel('author_name')?></th>
            <td>
                <?php
                if ($review->user) {
                    echo Html::encode($review->user->getDisplayName());
                } else {
                    echo Html::encode($review->author_name);
                }
                ?>

            </td>
        </tr>
        <tr>
            <th style="text-align: left;"><?=$review->getAttributeLabel('author_email')?></th>
            <td>
                <?=
                isset($review->author_email) ? $review->author_email : Html::tag('em', Yii::t('yii', '(not set)'))
                ?>
            </td>
        </tr>

        <tr style="background: #f5f5f5;">
            <th style="text-align: left;"><?=$review->getAttributeLabel('author_phone')?></th>
            <td>
                <?=
                isset($review->author_phone) ? $review->author_phone : Html::tag('em', Yii::t('yii', '(not set)'))
                ?>

            </td>
        </tr>
        <tr>
            <th style="text-align: left;"><?=$review->getAttributeLabel('text')?></th>
            <td>
                <?=
                isset($review->text) ? $review->text : Html::tag('em', Yii::t('yii', '(not set)'))
                ?>
            </td>
        </tr>
        <?php
        $row = '';
        $obj = Product::findById($review->object_model_id);
        if ($obj !== null) {
            $row = Html::tag(
                'tr',
                Html::tag('th', 'Продукт', ['style' => 'text-align: left;']) . Html::tag(
                    'td',
                    Html::a($obj->name, Url::to(['/shop/product/show', 'model' => $obj], true))
                ),
                ['style' => 'background: #f5f5f5;']
            );
        }
        $output = Html::tag(
            'p',
            'See review details ' . Html::a('here', Url::to(['/backend/review/products'], true))
        );
        echo $row;
        ?>
    </table>
<?php
echo $output;