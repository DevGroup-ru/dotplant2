<?php

namespace app\commands;

use Imagine\Image\ManipulatorInterface;
use app\backend\actions\FlushCacheConsoleAction;
use Yii;
use yii\console\Controller;
use app;
use app\modules\image\models\Image;

/**
 * Admin commands
 * @package app\commands
 */
class AdminController extends Controller
{

    public function actions()
    {
        return [
            'flush-cache' => [
                'class' => FlushCacheConsoleAction::className(),
            ],
        ];
    }

    /**
     * Generate thumbnails for Image model
     * @param bool $updateThumbnailSrc
     * @param bool $deleteIfNotExists
     * @throws \Exception
     */
    public function actionThumbnails($updateThumbnailSrc = false, $deleteIfNotExists = false)
    {
        $images = Image::find()->all();
        /** @var $images Image[] */
        foreach ($images as $image) {
            $dir = '@webroot' . mb_substr($image->filename, 0, mb_strrpos($image->filename, '/')) . '/';
            $filename = \Yii::getAlias($dir . $image->filename);
            if (!file_exists($filename)) {
                echo "File not found: " . $filename . "\n";
                if ($deleteIfNotExists) {
                    $image->delete();
                }
                continue;
            }
            $img = \yii\imagine\Image::thumbnail(
                $filename,
                80,
                80,
                ManipulatorInterface::THUMBNAIL_INSET
            );
            $img->save(\Yii::getAlias($dir . 'small-' . $image->filename));
            if ($updateThumbnailSrc) {
                $image->thumbnail_src = mb_substr($image->filename, 0, mb_strrpos($image->filename, '/'))
                    . '/small-' . $image->filename;
                $image->save(true, ['thumbnail_src']);
            }
        }
    }

    /**
     * Clear test review, orders and other information
     * @throws \yii\db\Exception
     */
    public function actionClearTests()
    {
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\models\ErrorLog::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\models\ErrorUrl::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\backend\models\Notification::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\modules\shop\models\Order::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\modules\shop\models\OrderItem::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\modules\shop\models\OrderTransaction::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\backend\models\OrderChat::tableName())->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%order_category}}')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%order_eav}}')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%order_property}}')->execute();
//        Yii::$app->db->createCommand('TRUNCATE TABLE ' . app\reviews\models\Review::tableName())->execute(); @todo need to implement correct reviews deleting
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

    public function actionTestMail($email)
    {

        $mailComponent = Yii::$app->mail;

        $status = $mailComponent->compose('test')
            ->setFrom(Yii::$app->getModule('core')->emailConfig['mailFrom'])
            ->setTo($email)
            ->setSubject(
                Yii::t(
                    'app', 'subject test mail on {site}',
                    [
                        'site' => 'Site'
                    ]
                )
            )->send();
        echo $status ? "OK\n" : "NOT OK\n";
    }


    protected function createStructure($path, $children, $themeName)
    {
        foreach ($children as $child) {
            $newPath = $path . DIRECTORY_SEPARATOR . $child['name'];
            if (isset($child['type']) && $child['type'] == 'dir') {
                mkdir($newPath);
                chmod($newPath, isset($child['writable']) && $child['writable'] ? 0777 : 0775);
                if (isset($child['children']) && !empty($child['children'])) {
                    $this->createStructure($newPath, $child['children'], $themeName);
                } else {
                    file_put_contents($newPath . DIRECTORY_SEPARATOR . '.keep', '');
                }
            } else {
                file_put_contents($newPath,
                    isset($child['content']) ? str_replace('{%theme-name%}', $themeName, $child['content']) : '');
            }
        }
    }

    /**
     * Create theme structure
     * @param string $name
     * @throws \yii\base\ExitException
     */
    public function actionCreateTheme($name = 'theme')
    {
        $name = trim($name);
        if (preg_match('#^[a-z]+$#i', $name) == 0) {
            echo "Bad theme name" . PHP_EOL;
            Yii::$app->end();
        }
        $themeRoot = Yii::getAlias('@webroot/' . $name);
        if (file_exists($themeRoot)) {
            echo "Directory \"{$themeRoot}\" already exists: " . PHP_EOL;
            Yii::$app->end();
        }
        $structure = include __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'theme-structure.php';
        mkdir($themeRoot);
        $this->createStructure($themeRoot, $structure, $name);
        echo "Theme \"{$name}\" has been created" . PHP_EOL;
        if (exec("which npm")) {
            echo "Installing node.js dependencies" . PHP_EOL;
            exec("cd $themeRoot ; npm install");
        }
    }
    /**
    * Parse url to route and corresponding params
    *
    * @param string $url
    * @param bool $prettify
    * @return string json_encode'd
    */
    public function actionParseUrl($url = "/", $prettify = false) {
        $_SERVER["SERVER_NAME"] = \Yii::$app->getModule('core')->serverName;
        $config = require(\Yii::getAlias("@app") . "/config/web.php");
        $webApp = new \yii\web\Application($config);

        $this->stdout(
            json_encode(
                $webApp->getUrlManager()->parseRequest(
                    new \yii\web\Request(
                        [
                            "url" => $url,
                            "pathInfo" => ltrim($url, "/")
                        ]
                    )
                ),
                $prettify ? JSON_PRETTY_PRINT : 0
            )
        );
    }
}
