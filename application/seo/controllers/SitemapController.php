<?php

namespace app\seo\controllers;

use app\seo\helpers\SitemapHelper;
use app\seo\models\Config;
use app\seo\models\Sitemap;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class SitemapController extends Controller
{
    public function actionGenerateSitemap()
    {
        if ($this->regenerate()) {
            $default = [];
            if ($mainPage = \Yii::$app->getModule('seo')->mainPage) {
                $default[] = [
                    'uid' => 'main_page',
                    'url' => $mainPage,
                ];
            }
            $urls = ArrayHelper::merge(
                $default,
                Sitemap::find()->asArray()->all()
            );
            $sitemap = $this->getSiteMap($urls);
            if ($sitemap->saveXML(\Yii::getAlias('@webroot/sitemap.xml'))) {
                echo "sitemap is generated\n";
            } else {
                echo "file can't be written\n";
            }
        }
    }

    /**
     * @return bool
     */
    private function regenerate()
    {
        if (!file_exists(\Yii::getAlias('@webroot/sitemap.xml'))) {
            return true;
        } else {
            /* @var $sitemapChanged Config */
            $sitemapChanged = Config::findOne(SitemapHelper::SITEMAP_CHANGED);

            if ($sitemapChanged !== null
                && (
                    $sitemapChanged->value == 'TRUE'
                    || $sitemapChanged->value == 'true'
                    || $sitemapChanged->value == '1'
                )
            ) {
                $sitemapChanged->value = 'FALSE';
                return $sitemapChanged->save();
            }
        }
        return false;
    }

    /**
     * @param array $urls
     * @return \SimpleXMLElement
     */
    private function getSiteMap($urls = [])
    {
        /* @var $sitemap \SimpleXMLElement */
        $sitemap = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>'
        );
        foreach ($urls as $urlModel) {
            /* @var $url \SimpleXMLElement */
            $url = $sitemap->addChild('url');
            $url->addChild('loc', Html::encode($urlModel['url']));
        }
        return $sitemap;
    }
}
