<?php
namespace app\modules\data\components;

/**
 * Interface ImportableInterface
 * Model that implement this interface can process additional data
 * from import documents such as additional fields or relations
 * @package app\modules\data\components
 */
interface ImportableInterface {
    /**
     * Process fields before the actual model is saved(inserted or updated)
     * @param array $fields
     * @return void
     */
    public function processImportBeforeSave(array $fields, $multipleValuesDelimiter, array $additionalFields);

    /**
     * Process fields after the actual model is saved(inserted or updated)
     * @param array $fields
     * @return void
     */
    public function processImportAfterSave(array $fields, $multipleValuesDelimiter, array $additionalFields);
}