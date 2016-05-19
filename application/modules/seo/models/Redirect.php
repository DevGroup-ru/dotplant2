<?php

namespace app\modules\seo\models;

use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "seo_redirect".
 *
 * @property integer $id
 * @property string $type
 * @property string $from
 * @property string $to
 * @property boolean $active
 */
class Redirect extends ActiveRecord
{
    const TYPE_STATIC = 'STATIC';
    const TYPE_PREG = 'PREG';

    /**
     * @return bool|string
     */
    private static function getFilename()
    {
        return \Yii::getAlias('@app/modules/seo/redirects/redirectsArray.php');
    }

    public static function getTypes()
    {
        return [
            self::TYPE_STATIC => Yii::t('app', 'static'),
            self::TYPE_PREG => Yii::t('app', 'regular'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%seo_redirect}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'from', 'to'], 'string'],
            [['from', 'to'], 'required'],
            [['active'], 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'type' => \Yii::t('app', 'Type'),
            'from' => \Yii::t('app', 'From'),
            'to' => \Yii::t('app', 'To'),
            'active' => \Yii::t('app', 'Active'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'type', 'from', 'to', 'active'],
            'search' => ['id', 'type', 'from', 'to', 'active'],
        ];
    }


    /**
     * Search redirects
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->load($params);
        $query = self::find();
        foreach ($this->attributes as $name => $value) {
            if ($value !== null && $value !== '') {
                if ($name == 'id' || $name == 'type' || $name == 'active') {
                    $query->andWhere("`$name` = :$name", [":$name" => $value]);
                } else {
                    $query->andWhere("`$name` LIKE :$name", [":$name" => "%$value%"]);
                }
            }
        }
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        return $dataProvider;
    }

    public static function generateRedirectFile()
    {
        $head = "<?php\n";
        $head .= "/*\n";
        $head .= " * This file is generated automatically\n";
        $head .= "*/\n";
        $redirects = [
            'static' => static::find()
                ->select(['to', 'from'])
                ->where(['active' => true, 'type' => self::TYPE_STATIC])
                ->asArray(true)
                ->indexBy('from')
                ->column(),
            'regular' => static::find()
                ->select(['to', 'from'])
                ->where(['active' => true, 'type' => self::TYPE_PREG])
                ->asArray(true)
                ->indexBy('from')
                ->column()
        ];
        $body = "return ".var_export($redirects, true).";\n";
        return file_put_contents(self::getFilename(), $head."\n".$body);
    }

    public static function deleteRedirectFile()
    {
        return unlink(self::getFilename());
    }
}
