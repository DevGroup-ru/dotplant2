<?php

namespace app\modules\data\components;

/**
 * Interface ExportableInterface
 * Model that implements this interface can export additional data
 * such as relations, categories bindings, images etc.
 * @package app\modules\data\components
 */
interface ExportableInterface {
    /**
     * Additional fields with labels and export representation options.
     * Translation should be implemented internally in this function.
     * For now will be rendered as checkbox list with label.
     * Note: properties should not be in the result - they are served other way.
     * Format:
     * [
     *      'field_key' => [
     *          'label' => 'Your awesome translated field title',
     *          'processValueAs' => [
     *              'name' => 'label for name',
     *              'id' => 'id',
     *          ],
     *      ],
     *      'another' => [
     *          // ...
     *      ],
     * ]
     * @return array
     */
    public static function exportableAdditionalFields();

    /**
     * Returns additional fields data by field key.
     * If value of field is array it will be converted to string
     * using multipleValuesDelimiter specified in ImportModel
     *
     * Configuration array example:
     * [
     *      'field_key' => [
     *          'enabled' => 1,
     *          'processValueAs' => 'id',
     *          'key' => 'field_key' // just dublicate
     *      ]
     * ]
     *
     *
     * @param array $configuration
     * @return array
     */
    public function getAdditionalFields(array $configuration);
}