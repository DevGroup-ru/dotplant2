<?php

namespace app\properties\traits;

use yii\db\ActiveRecord;

trait PropertyTrait
{
    public function saveModelWithProperties($data = [])
    {
        $result = false;
        $data = is_array($data) ? $data : [$data];
        /** @var ActiveRecord $this */
        if ($this->save()) {
            $result = true;
            $this->saveProperties($data);
        }
        return $result;
    }
}
?>