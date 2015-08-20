<?php

namespace app\modules\seo\controllers;

use app\modules\page\models\Page;
use app\modules\seo\helpers\SitemapHelper;
use app\modules\seo\models\Config;
use app\modules\seo\models\Sitemap;
use app\modules\seo\models\SitemapXML;
use app\modules\shop\models\Category;
use app\modules\shop\models\Product;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class SitemapController extends Controller
{
    // Begin of sitemap beta
    // @todo include filter pages
    /** @var  SitemapXML */
    protected $sitemap;

    protected function pagesSitemap($parentId = 1)
    {
        // @todo exclude subdomain urls
        $pages = Page::find()
            ->select(['id', 'slug_compiled'])
            ->where(['parent_id' => $parentId, 'published' => 1])
            ->asArray(true)
            ->all();
        array_reduce($pages, function ($carry, $item) {
            $this->sitemap->addUrl('/' . $item['slug_compiled']);
            $this->pagesSitemap($item['id']);
        });
    }

    protected function categoriesSitemap($parentId = 0, $prefix = '')
    {
        $categories = Category::find()
            ->select(['id', 'category_group_id', 'slug'])
            ->where(['parent_id' => $parentId, 'active' => 1])
            ->asArray(true)
            ->all();
        array_reduce($categories, function ($carry, $item) use ($prefix) {
            $this->sitemap->addUrl($prefix . '/' . $item['slug']);
            $this->categoriesSitemap($item['id'], $prefix . '/' . $item['slug']);
            $this->productsSitemap($item['id'], $prefix . '/' . $item['slug']);
        });
    }

    protected function productsSitemap($categoryId, $categoryUrl)
    {
        $product = Product::find()
            ->select(['id', 'slug'])
            ->where(['main_category_id' => $categoryId, 'active' => 1])
            ->asArray(true)
            ->all();
        array_reduce($product, function ($carry, $item) use ($categoryUrl) {
            $this->sitemap->addUrl($categoryUrl . '/' . $item['slug']);
        });
    }

    public function actionGenerateSitemapBeta()
    {
        $this->sitemap = new SitemapXML(Yii::getAlias('@webroot/'), 'http://' . Yii::$app->getModule('core')->serverName);
        $this->sitemap->addUrl('');
        $this->pagesSitemap();
        $this->categoriesSitemap();
        $this->sitemap->save();
    }
    // End of sitemap beta

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
            $sitemapChanged = Config::getModelByKey(SitemapHelper::SITEMAP_CHANGED);

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
