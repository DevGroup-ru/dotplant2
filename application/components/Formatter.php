<?php

namespace app\components;

use Yii;
use app;
use yii\helpers\StringHelper;

/**
 * Formatter is an extended version of base yii2 Formatter (\yii\i18n\Formatter).
 * This class adds some additional formats used mostly in grids.
 * @package app\components
 */
class Formatter extends \yii\i18n\Formatter
{
    /**
     * Formats the value as text and truncates it
     * @param $value
     * @param int $length
     * @param string $suffix
     * @param bool $asHtml
     * @return string
     */
    public function asTruncated($value, $length = 200, $suffix = '...', $asHtml = false)
    {
        return StringHelper::truncate($value, $length, $suffix, null, $asHtml);
    }
}