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
                        $items = array_values(
                            array_filter(
                                explode('returnUrl', $part),
                                function ($item) {
                                    return $item !== '';
                                }
                            )
                        );
                        do {
                            $returnUrls[] = 'returnUrl' . $items[$level];
                            $level++;
                        } while ($level < count($items) && $level < $depth);
                        $returnUrlParams[] = implode($returnUrls);
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
