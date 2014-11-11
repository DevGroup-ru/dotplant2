<?php

if (isset($error) && $error == 1) {
    echo $message;
} else {

    ?>
    <table style="margin: 25px auto;">
        <tr>
        <?php

        $counter = 1;
        foreach ($prods as $prod) {
            $url = \yii\helpers\Url::to(
                [
                    'product/show',
                    'model' => $prod,
                    'last_category_id' => $prod->main_category_id,
                    'category_group_id' => $prod->category->category_group_id,
                ]
            );

            $img = app\widgets\ImgSearch::widget(
                [
                    'object_id'=>1,
                    'object_model_id'=>$prod->id,
                    'displayCountPictures'=>1,
                    'viewFile' => 'img-thumbnail-list',
                ]
            );
            ?>


                <td style="padding: 15px">
                    <table>
                        <tr>
                            <td><b><?= $prod->name ?></b></td>
                        </tr>
                        <tr>
                            <td><?= $img ?></td>
                        </tr>
                        <tr>
                            <td><?= Yii::t('shop', 'Price') ?>: <span style="color:green;font-weight:bold; font-size:20px;"><?= $prod->price ?></span><td>
                        </tr>
                        <tr>
                            <td>
                                <?=
                                \app\properties\PropertiesWidget::widget(
                                    [
                                        'model' => $prod,
                                        'form' => null,
                                        'viewFile' => 'show-properties-widget',
                                    ]
                                );
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>


        <?php
            if ((($counter++ % 3) == 0)) {
                echo '</tr><tr>';
            }
        }
        ?>
        </tr>
    </table>
<?php
}