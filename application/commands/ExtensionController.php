<?php

namespace app\commands;

use app\modules\core\models\Extensions;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Console;

class ExtensionController extends Controller
{

    public function actionInstall($package)
    {
        $installed = Extensions::isPackageInstalled($package);

        if ($path = $this->checkInJson($package, true)) {
            // it's in studio
            Extensions::installStudioPackage($package, $path);
        } else {
            // it's in composer
            Extensions::installExtension($package, !$this->checkInJson($package, false));
        }
    }

    public function actionUninstall($package)
    {
        $this->stderr('Not working now');
        return 1;
        $extension = Extensions::findByName($package);
        if ($extension === null) {
            $this->stderr('[ Error ] Extension is not installed', Console::FG_RED);
            return 1;
        }
        if ($extension->deactivateExtension() === false) {
            $this->stderr('[ Error ] Could not deactivate extension', Console::FG_RED);
            return 2;
        }
        if ($this->checkInJson($package, true) === false) {
            // we don't remove packages in studio.json - only composer
            if ($extension->removeExtensionPackage() === false) {
                $this->stderr('[ Error ] Could not remove extension package', Console::FG_RED);
                return 3;
            }
        }
        $this->stdout('[ OK ] Extension uninstalled', Console::FG_RED);
    }

    private function checkInJson($package, $studio=false)
    {
        if ($studio === true) {
            $fn = Yii::getAlias('@app/studio.json');
        } else {
            $fn = Yii::getAlias('@app/composer.json');
        }
        if (file_exists($fn) === false) {
            return false;
        }
        $json = Json::decode(file_get_contents($fn));
        if ($studio === true && isset($json['packages'])) {

            return ArrayHelper::getValue($json['packages'], $package, false);
        } elseif ($studio === false) {
            return ArrayHelper::getValue($json['require'], $package, false);
        }

    }
}