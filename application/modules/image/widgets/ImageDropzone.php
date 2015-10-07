<?php

namespace app\modules\image\widgets;

use app\modules\image\models\Image;
use devgroup\dropzone\DropZone;
use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

class ImageDropzone extends DropZone
{
    public $removeUrl = 'remove';
    public $url = 'upload';
    public $objectId;
    public $modelId;

    public $uploadDir = 'upload';

    public static function saveThumbnail($dir, $filename, $width = 80, $height = 80)
    {
        if (trim($filename) && file_exists(Yii::getAlias($dir . '/' . $filename))) {

            $image = \yii\imagine\Image::thumbnail(
                Yii::getAlias($dir . '/' . $filename),
                $width,
                $height,
                ManipulatorInterface::THUMBNAIL_INSET
            );
            $image->save($dir . '/small-' . $filename);


            return 'small-' . $filename;
        }

        return '';
    }

    protected function addFiles($files = [])
    {
        $path = Yii::getAlias('@webroot' . $this->uploadDir . '/');

        $i = 0;
        foreach ($files as $file) {
            $fhName = 'file_' . $i ++;

            // Create the mock file:
            $this->getView()->registerJs(
                'var ' . $fhName . ' = { name: "' . $file['name'] . '", size: ' . Yii::$app->getModule('image')->fsComponent->getSize(
                    $file['name']
                ) . ' };'
            );
            // Call the default addedfile event handler
            $this->getView()->registerJs(
                $this->dropzoneName . '.emit("addedfile", ' . $fhName . ');'
            );
            // And optionally show the thumbnail of the file:
            $this->getView()->registerJs(
                $this->dropzoneName . '.emit("thumbnail", ' . $fhName . ', "' . $file['thumbnail'] . '");'
            );
            $this->getView()->registerJs(
                'jQuery(' . $fhName . '.previewElement).find("[name=\"id[]\"]").val(' . $file['id'] . ');
                jQuery(' . $fhName . '.previewElement).find("[name=\"file[]\"]").val("' . $file['file'] . '");
                jQuery(' . $fhName . '.previewElement).data("filename", "' . $file['name'] . '");
                jQuery(' . $fhName . '.previewElement).find(".title textarea").text(' . Json::encode($file['image_title']) . ');
                jQuery(' . $fhName . '.previewElement).find(".title textarea").attr("name", "title[' . $file['id'] . ']");
                jQuery(' . $fhName . '.previewElement).find(".alt textarea").text(' . Json::encode($file['image_alt']) . ');
                jQuery(' . $fhName . '.previewElement).find(".alt textarea").attr("name", "alt[' . $file['id'] . ']");'
            );
        }
    }

    protected function createDropzone()
    {
        $this->getView()->registerJs(
            'var ' . $this->dropzoneName . ' = new Dropzone("#' . $this->id . '", ' . Json::encode(
                $this->options
            ) . ');'
        );
    }

    public function run()
    {
        parent::run();

        ImageDropzoneAsset::register($this->getView());
    }

    public function init()
    {
        parent::init();

        Html::addCssClass($this->htmlOptions, 'custom-dz');

        /** @var Image[] $files */
        $files = Image::find()->where(
            [
                'and',
                'object_id = :objectId',
                'object_model_id = :modelId',
            ],
            [
                ':objectId' => $this->objectId,
                ':modelId' => $this->modelId,
            ]
        )->orderBy(['sort_order' => SORT_ASC])->all();


        /** Image $file */

        foreach ($files as $file) {
            $thumbnail_src = $file->getThumbnail('80x80');

            $this->storedFiles[] = [
                'id' => $file->id,
                'name' => $file->filename,
                'file' => $file->file,
                'thumbnail' => $thumbnail_src,
                'image_title' => $file->image_title,
                'image_alt' => $file->image_alt,
            ];
        }

        $params = ArrayHelper::merge(
            isset($this->options['params']) ? $this->options['params'] : [],
            [
                'objectId' => $this->objectId,
                'modelId' => $this->modelId,
            ]
        );
        $this->sortable = true;
        $this->options = ArrayHelper::merge(
            $this->options,
            [
                'acceptedFiles' => 'image/*',
                'params' => $params,
                'previewTemplate' => '<div class="file-row">
                        ' . Html::input('hidden', 'id[]') . Html::input('hidden', 'file[]') . '
                        <!-- This is used as the file preview template -->
                        <div>
                            <span class="preview"><img style="width: 80px; height: 80px;" data-dz-thumbnail /></span>
                        </div>
                        <div>
                            <p class="name" data-dz-name></p>
                            <div class="dz-error-message"><span data-dz-errormessage></span></div>
                        </div>
                        <div class="title">
                            <label>' . Yii::t('app','Image Title') . '</label>
                            ' . Html::textarea(
                        'image_title',
                        '',
                        ['style' => 'width: 100%; min-width: 80px; height: 80px;']
                    ) . '
                        </div>
                        <div class="alt">
                            <label>' . Yii::t('app','Image Alt') . '</label>
                            ' . Html::textarea(
                        'image_alt',
                        '',
                        ['style' => 'width: 100%; min-width: 80px; height: 80px;']
                    ) . '
                        </div>
                        <div>
                            <p class="size" data-dz-size></p>
                            <div class="dz-progress progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                              <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                            </div>
                            <div class="dz-success-mark"><span>✔</span> OK</div>
                            <div class="dz-error-mark"><span>✘</span> ERROR</div>
                        </div>
                        <div>
                          <button data-dz-remove class="btn btn-danger delete">
                            <i class="fa fa-trash-o"></i>
                            <span>' . Yii::t('app', 'Delete') . '</span>
                          </button>
                        </div>
                      </div>',
                'thumbnailWidth' => '80',
                'thumbnailHeight' => '80',
                'previewsContainer' => "#{$this->id}",
            ]
        );

        $this->eventHandlers = [
            'removedfile' => 'function(file) {
                jQuery.get(
                    "' . Url::toRoute($this->removeUrl) . '",
                    {
                        "id" : jQuery(file.previewElement).find("[name=\"id[]\"]").val(),
                        "filename" : jQuery(file.previewElement).data("filename")
                    }
                ).done(function (data) { return data });
            }',
            'success' => 'function(file, response) {
                response = jQuery.parseJSON(response);
                jQuery(file.previewElement).find("[data-dz-name]").text(response.filename);
                jQuery(file.previewElement).data("filename", response.filename);
                jQuery(file.previewElement).find("[name=\"id[]\"]").val(response.afterUpload.id);
                jQuery(file.previewElement).find("[name=\"file[]\"]").val(response.afterUpload.file);
                jQuery(file.previewElement).find(".title textarea").attr("name", "title["+response.afterUpload.id+"]");
                jQuery(file.previewElement).find(".alt textarea").attr("name", "alt["+response.afterUpload.id+"]");
            }',
            'complete' => 'function(file) {
                jQuery(file.previewElement).removeClass("dz-processing");
            }',
        ];
    }
}
