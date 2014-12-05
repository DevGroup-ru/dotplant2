<?php

namespace app\index\models;


interface IndexableDocumentInterface
{
    /**
     * @param string $pk
     * @return \yii\db\BaseActiveRecord|null
     */
    public static function findByPk($pk);

    /**
     * @param string $pk
     * @return void
     */
    public function setPk($pk);
} 