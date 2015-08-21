<?php

namespace app\modules\seo\models;

// @todo separate sitemap if site has many pages
// @todo add try-catch blocks
class SitemapXML
{
    protected $directory;
    protected $domain;
    protected $fileHandler;
    protected $urlsCount = 0;

    protected function getFileHandler()
    {
        if ($this->fileHandler === null) {
            $this->fileHandler = fopen($this->directory . 'sitemap.xml', 'w');
            $this->beginSitemap();
        }
        return $this->fileHandler;
    }

    protected function beginSitemap()
    {
        fwrite(
            $this->getFileHandler(),
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
        );
    }

    protected function endSitemap()
    {
        fwrite($this->getFileHandler(), "</urlset>");
    }

    protected function renameFile($oldName, $newName)
    {
        return rename($this->directory . $oldName, $this->directory . $newName);
    }

    public function __construct($directory, $domain)
    {
        $this->directory = $directory;
        $this->domain = $domain;
    }

    public function addUrl($url)
    {
        // @todo convert "wrong" symbols
        $url = strtr($url, [
            '&' => '&amp;',
            '\'' => '&#x27;',
            '"' => '&quot;',
            '<' => '&lt',
            '>' => '&gt;',
        ]);
        $url = $this->domain . str_replace('&', '&amp;', $url);
        fwrite($this->getFileHandler(), "\t<url>\n\t\t<loc>{$url}</loc>\n\t</url>\n");
    }

    public function save()
    {
        $this->endSitemap();
    }
}
