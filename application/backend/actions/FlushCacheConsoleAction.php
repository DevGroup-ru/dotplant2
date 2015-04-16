<?php


namespace app\backend\actions;

use yii\web\Application;
use Yii;

class FlushCacheConsoleAction extends FlushCacheAction {


    public function run()
    {
        $config = require(Yii::getAlias('@app/config').'/web.php');
        $_SERVER['REQUEST_URI'] = '/';
        Yii::setAlias('@webroot', Yii::getAlias('@app/web'));
        $app = new Application($config);
        $this->flushCache($app);
        $this->flushAssets();
        echo "ok\n";
        $app->end();
    }

}