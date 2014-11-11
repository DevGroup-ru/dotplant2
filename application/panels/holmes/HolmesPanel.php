<?php

namespace app\panels\holmes;

use yii\debug\Panel;

class HolmesPanel extends Panel
{

    public function getName()
    {
        return 'Holmes';
    }

    public function getSummary()
    {
        return \Yii::$app->view->render('@app/panels/holmes/views/summary', ['panel' => $this]);
    }

    public function save()
    {
        return [];
    }
}
