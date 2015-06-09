<?php

namespace app\modules\seo\helpers;

use app\modules\seo\models\Config;
use app\modules\seo\models\Sitemap;
use yii\helpers\Url;

class SitemapHelper
{
    const SITEMAP_CHANGED = 'sitemap_changed';

    public static function registerPage($route, $uid)
    {
        $url = Url::toRoute($route, true);
        if (\Yii::$app->db->createCommand()->insert(Sitemap::tableName(), ['uid' => $uid, 'url' => $url])->execute()) {
            return self::setChanged();
        }
        return false;
    }

    public static function deletePage($uid)
    {
        if (\Yii::$app->db->createCommand()->delete(Sitemap::tableName(), ['uid' => $uid])->execute()) {
            return self::setChanged();
        }
        return false;
    }

    private static function setChanged()
    {
        /* @var $changed Config */
        $changed = Config::getModelByKey(self::SITEMAP_CHANGED);
        if ($changed === null) {
            $changed = new Config(
                [
                    'key' => self::SITEMAP_CHANGED,
                    'value' => 'TRUE',
                ]
            );
        } elseif ($changed->value === 'FALSE') {
            $changed->value = 'TRUE';
        }
        return $changed->save();
    }
}
