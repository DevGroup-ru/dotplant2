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
            $returnUrls = []; // returnUrl array
            $pos = -1; // returnUrl position at string
            $i = 0;    // iterator
            $depthLevel = 0;
            if (isset($url['query'])) {
                $parts = explode('&', rawurldecode($url['query']));
                foreach ($parts as $part) {
                    $pieces = explode('=', $part);
                    $isReturnUrlString = strpos($part, 'returnUrl');
                    if ($isReturnUrlString !== false) {
                        $pos = $i;
                        if ($depthLevel < $depth) {
                            $returnUrls[] = rawurlencode($part);
                        }
                        ++$depthLevel;
                        continue;
                    }
                    if (count($pieces) == 2 && strlen($pieces[1]) > 0) {
                        $returnUrlParams[] = $part;
                    }
                    $i++;
                }
                array_splice($returnUrlParams, $pos, 0, implode($returnUrls));
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
