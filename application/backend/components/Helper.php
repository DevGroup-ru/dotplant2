<?php

namespace app\backend\components;

use \Yii;

class Helper
{
    private static $returnUrl;

    public static function getReturnUrl($depth = 2)
    {
        if (is_null(self::$returnUrl)) {
            $url = parse_url(Yii::$app->request->url);
            $returnUrlParams = [];
            $level = 0;
            if (isset($url['query'])) {
                $parts = explode('&', $url['query']);
                foreach ($parts as $part) {
                    $pieces = explode('=', $part);
                    $returnUrls = [];
                    if (strpos($part, 'returnUrl') !== false) {
                        $temp = explode('&', urldecode($part));
                        do {
                            $returnUrls[] = $temp[$level];
                            $level++;
                        } while ($level < $depth);
                        $returnUrlParams[] = urlencode(implode($returnUrls));
                        continue;
                    }
                    if (count($pieces) == 2 && strlen($pieces[1]) > 0) {
                        $returnUrlParams[] = $part;
                    }
                }
            }
            if (count($returnUrlParams) > 0) {
                self::$returnUrl = $url['path'] . '?' . implode('&', $returnUrlParams);
            } else {
                self::$returnUrl = $url['path'];
            }
        }
        return self::$returnUrl;
    }
}
