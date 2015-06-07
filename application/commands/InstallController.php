<?php

namespace app\commands;

use app\modules\installer\components\InstallerFilter;
use app\modules\installer\components\InstallerHelper;
use yii\console\Controller;
use yii\helpers\Console;

class InstallController extends Controller
{
    private $db = null;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'installer' => [
                'class' => InstallerFilter::className(),
            ],
        ];
    }

    public function actionIndex()
    {
        $this->stdout("Checking permissions\n", Console::FG_YELLOW);
        $permissions = InstallerHelper::checkPermissions();
        $ok = true;
        foreach ($permissions as $file => $result) {

            if ($result) {
                $this->stdout('[ OK ]    ', Console::FG_GREEN);
            } else {
                $this->stdout('[ Error ] ', Console::FG_RED);
            }
            $this->stdout($file);
            $this->stdout("\n");
            $ok = $ok && $result;
        }
        if ($ok) {
            if ($this->confirm("\nSome of your files are not accessible.\nContinue at your own risk?", true)) {
                return $this->dbConfig();
            } else {
                return 1;
            }
        } else {
            return $this->dbConfig();
        }
    }

    private function dbConfig()
    {

    }
}