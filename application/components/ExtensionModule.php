<?php

namespace app\components;

use app;
use app\modules\config\helpers\ConfigurationUpdater;
use Symfony\Component\Process\Process;
use Yii;

abstract class ExtensionModule extends \yii\base\Module
{
    public $migrationsFolder = 'migrations';

    /**
     * ID of module for application config.
     * You must override it in your extension.
     */
    public static $moduleId = 'temporary-module';

    public static function installModule($applyAppMigrations = true, $updateComposer = true)
    {
        Yii::$app->setModule(
            static::$moduleId,
            static::className()
        );
        $module = Yii::$app->getModule(static::$moduleId);
        $module->updateModule($applyAppMigrations, $updateComposer, true);
    }

    /**
     * Updates/installs module
     *
     * @param bool $applyAppMigrations True if we need to apply app migrations
     * @param bool $updateComposer True if we need to update composer
     */
    public function updateModule($applyAppMigrations = true, $updateComposer = true, $updateConfig = true)
    {
        // apply migrations
        $migrationPath = $this->getMigrationsPath();

        /** @var Process $process */
        $process = Yii::$app->updateHelper
            ->applyMigrations(
                $migrationPath,
                $applyAppMigrations,
                $updateComposer
            );
        $process->mustRun();


        if ($updateConfig === true) {
            $this->updateConfig();
        }
    }

    /**
     * @return string Full real path to module migrations
     */
    public function getMigrationsPath()
    {
        $reflectionClass = new \ReflectionClass($this);
        $directory = dirname($reflectionClass->getFileName());

        return realpath(
            "$directory/{$this->migrationsFolder}/"
        );
    }

    /**
     * Update configurable config if it exists.
     * Unexistent values will be filled with default values.
     */
    public function updateConfig()
    {
        if ($this->getBehavior('configurableModule') !== null) {
            // This extension has config behavior

            // Find corresponding configurable
            $configurable = app\modules\config\models\Configurable::find()
                ->where(['module' => $this->id])
                ->one();

            if ($configurable !== null) {
                // We should save default values of it
                $array = [$configurable];
                ConfigurationUpdater::updateConfiguration(
                    $array,
                    false
                );
            }
        }
    }


}