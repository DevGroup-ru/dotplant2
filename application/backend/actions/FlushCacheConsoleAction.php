<?php

namespace app\backend\actions;

use yii\web\Application;
use Yii;

class FlushCacheConsoleAction extends FlushCacheAction
{
    public function run()
    {
        $config = require(Yii::getAlias('@app/config') . '/web.php');
        $app = new Application($config);
        Yii::setAlias('@webroot', Yii::getAlias('@app/web'));
        $this->flushCache($app);
        $this->flushAssets();
        echo "ok\n";
        $app->end();
    }
}
