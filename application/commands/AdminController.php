<?php

namespace app\commands;

use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\console\Controller;
use app;
use app\models\Image;

/**
 * Admin commands
 * @package app\commands
 */
class AdminController extends Controller
{
    /**
     * Generate thumbnails for Image model
     */
    public function actionThumbnails()
    {
        mb_internal_encoding('UTF-8');
        $images = Image::find()->all();
        /** @var $images Image[] */
        foreach ($images as $image) {
            $dir = '@webroot' . mb_substr($image->image_src, 0, mb_strrpos($image->image_src, '/')) . '/';
            $img = \yii\imagine\Image::thumbnail(
                \Yii::getAlias($dir . $image->filename),
                80,
                80,
                ManipulatorInterface::THUMBNAIL_INSET
            );
            $img->save(\Yii::getAlias($dir . 'small-' . $image->filename));
        }
    }

    /**
     * Clear test reviews, orders and other information
     * @throws \yii\db\Exception
     */
    public function actionClearTests()
    {
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\models\Cart::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\models\ErrorLog::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\models\ErrorUrl::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\backend\models\Notification::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\models\Order::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\models\OrderItem::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\models\OrderTransaction::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\backend\models\OrderChat::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%order_category}}')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%order_eav}}')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%order_property}}')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\reviews\models\Review::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\models\Submission::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%submission_category}}')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%submission_eav}}')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%submission_property}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM ' . app\models\ObjectPropertyGroup::tableName()
            . ' WHERE `object_id` IN (SELECT `id` FROM ' . app\models\Object::tableName()
            . ' WHERE `object_class` IN (\'app\\models\\Submission\', \'app\\models\\Order\'))')->execute();
        Yii::$app->db->createCommand('DELETE FROM ' . app\models\ObjectStaticValues::tableName()
            . ' WHERE `object_id` IN (SELECT `id` FROM ' . app\models\Object::tableName()
            . ' WHERE `object_class` IN (\'app\\models\\Submission\', \'app\\models\\Order\'))')->execute();
    }
}