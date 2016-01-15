<?php


namespace app\modules\image\actions;


use app\modules\image\models\Image;

use creocoder\flysystem\Filesystem;
use Yii;
use yii\base\Action;
use yii\base\Exception;
use yii\web\NotAcceptableHttpException;

class UpdateNameAction extends Action
{
    public function run()
    {
        if (Yii::$app->request->isAjax === false) {
            throw new NotAcceptableHttpException();
        }
        $oldName = Yii::$app->request->post('oldname', '');
        $newName = Yii::$app->request->post('newname', '');
        if (!empty($oldName) && !empty($newName) && $oldName !== $newName) {
            try {
                /**
                 * @var Filesystem $fs
                 */
                $fs = Yii::$app->getModule('image')->fsComponent;
                if ($fs->has($oldName)) {
                    if ($fs->rename($oldName, $newName)) {
                        Image::updateAll(['filename' => $newName], ['filename' => $oldName]);
                    } else {
                        throw new Exception('Error in renaming');
                    }
                } else {
                    throw new Exception('No files to rename');
                }

            } catch (Exception $except) {
                return $except->getMessage();
            }
            return $newName;
        }
        return '';
    }
}