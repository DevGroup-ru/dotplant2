<?php

namespace app\modules\config\helpers;

use app;
use app\modules\config\components\ConfigurationSaveEvent;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * Class ConfigurationUpdater deals with saving configuration.
 * It is used by ConfigController and by extensions on their install/update stage for saving initial values.
 *
 * @package app\modules\config\helpers
 */
class ConfigurationUpdater
{
    /**
     * @param \app\modules\config\models\Configurable[] $configurables
     * @param bool $usePostData
     * @return bool
     */
    public static function updateConfiguration(&$configurables, $usePostData = true, $loadExistingConfiguration = true)
    {
        $commonConfigWriter = new ApplicationConfigWriter([
            'filename' => '@app/config/common-configurables.php',
            'loadExistingConfiguration' => $loadExistingConfiguration,
        ]);
        $webConfigWriter = new ApplicationConfigWriter([
            'filename' => '@app/config/web-configurables.php',
            'loadExistingConfiguration' => $loadExistingConfiguration,
        ]);
        $consoleConfigWriter = new ApplicationConfigWriter([
            'filename' => '@app/config/console-configurables.php',
            'loadExistingConfiguration' => $loadExistingConfiguration,
        ]);
        $kvConfigWriter = new ApplicationConfigWriter([
            'filename' => '@app/config/kv-configurables.php',
            'loadExistingConfiguration' => $loadExistingConfiguration,
        ]);
        $aliasesConfigWriter = new ApplicationConfigWriter([
            'filename' => '@app/config/aliases.php',
            'loadExistingConfiguration' => $loadExistingConfiguration,
        ]);


        $isValid = true;
        $errorModule = '';

        foreach ($configurables as $model) {
            $configurableModel = $model->getConfigurableModel();
            $configurableModel->loadState();
            $dataOk = true;
            if ($usePostData === true) {
                $dataOk = $configurableModel->load(Yii::$app->request->post());
            }
            if ($dataOk === true) {
                $event = new ConfigurationSaveEvent();
                $event->configurable = &$model;
                $event->configurableModel = &$configurableModel;

                $configurableModel->trigger($configurableModel->configurationSaveEvent(), $event);
                if ($event->isValid === true) {
                    if ($configurableModel->validate() === true) {
                        // apply application configuration
                        $commonConfigWriter->addValues(
                            $configurableModel->commonApplicationAttributes()
                        );
                        $webConfigWriter->addValues(
                            $configurableModel->webApplicationAttributes()
                        );
                        $consoleConfigWriter->addValues(
                            $configurableModel->consoleApplicationAttributes()
                        );
                        $kvConfigWriter->addValues(
                            [
                                'kv-' . $model->module => $configurableModel->keyValueAttributes(),
                            ]
                        );

                        $aliasesConfigWriter->addValues(
                            $configurableModel->aliases()
                        );

                        $configurableModel->saveState();

                        if (isset(Yii::$app->modules[$model->module]) === true) {
                            /** @var \yii\base\Module $module */
                            $module = Yii::$app->modules[$model->module];

                            // invalidate cache by module class name tag
                            TagDependency::invalidate(
                                Yii::$app->cache,
                                [
                                    ActiveRecordHelper::getCommonTag($module->className())
                                ]
                            );


                        }

                    } else {
                        Yii::$app->session->setFlash('info', 'Validation error:'.var_export($configurableModel));
                        $isValid = false;
                    }
                } else {
                    $isValid = false;
                }
                if ($isValid === false) {
                    $errorModule = $model->module;
                    // event is valid, stop saving data
                    break;
                }
            } // model load from user input

        }  // /foreach


        if ($isValid === true) {

            // add aliases to common config

            $isValid =
                $commonConfigWriter->commit() &&
                $webConfigWriter->commit() &&
                $consoleConfigWriter->commit() &&
                $kvConfigWriter->commit() &&
                $aliasesConfigWriter->commit();

            if (ini_get('opcache.enable')) {

                // invalidate opcache of this files!
                opcache_invalidate(
                    Yii::getAlias($commonConfigWriter->filename),
                    true
                );
                opcache_invalidate(
                    Yii::getAlias($webConfigWriter->filename),
                    true
                );
                opcache_invalidate(
                    Yii::getAlias($consoleConfigWriter->filename),
                    true
                );
                opcache_invalidate(
                    Yii::getAlias($kvConfigWriter->filename),
                    true
                );
                opcache_invalidate(
                    Yii::getAlias($aliasesConfigWriter->filename),
                    true
                );

            }
        }

        if (Yii::$app->get('session', false)) {
            if ($isValid === true) {
                Yii::$app->session->setFlash(
                    'success',
                    Yii::t(
                        'app',
                        'Configuration saved'
                    )
                );
            } else {
                Yii::$app->session->setFlash(
                    'error',
                    Yii::t(
                        'app',
                        'Error saving configuration for module {module}',
                        [
                            'module' => $errorModule,
                        ]
                    )
                );
            }
        }

        return $isValid;
    }
}