<?php

namespace app\properties\traits;

use yii\db\ActiveRecord;

trait PropertyTrait
{
    public function saveModelWithProperties($data = [])
    {
        $data = is_array($data) ? $data : [$data];
        /** @var ActiveRecord $this */
        if ($this->save()) {
            $this->saveProperties($data);
        }
    }
}
?>