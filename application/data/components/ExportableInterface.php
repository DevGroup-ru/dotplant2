<?php

namespace app\data\components;

/**
 * Interface ExportableInterface
 * Model that implements this interface can export additional data
 * such as relations, categories bindings, images etc.
 * @package app\data\components
 */
interface ExportableInterface {
    /**
     * Additional fields with labels.
     * Translation should be implemented internally in this function.
     * For now will be rendered as checkbox list with label.
     * Note: properties should not be in the result - they are served other way.
     * Format:
     * [
     *      'field_key' => 'Your awesome translated field title',
     *      'another' => 'Another field label',
     * ]
     * @return array
     */
    public static function exportableAdditionalFields();

    /**
     * Returns additional fields data by field key.
     * If value of field is array it will be converted to string
     * using multipleValuesDelimiter specified in ImportModel
     * @return array
     */
    public function getAdditionalFields();
}