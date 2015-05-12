<?php

namespace app\components;

use app;
use app\modules\config\models\Configurable;
use app\modules\config\helpers\ConfigurationUpdater;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
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
        return $module->updateModule($applyAppMigrations, $updateComposer, true);
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
                $this->getMigrationTable(),
                $applyAppMigrations,
                $updateComposer
            );
        $process->mustRun();


        if ($updateConfig === true) {
            $this->updateConfig();
        }

        return $process->getExitCode() === 0;
    }

    public function uninstallModule()
    {

        $this->removeConfig();

        // apply migrations
        $migrationPath = $this->getMigrationsPath();

        /** @var Process $process */
        $process = Yii::$app->updateHelper
            ->applyMigrations(
                $migrationPath,
                $this->getMigrationTable(),
                false,
                false,
                true
            );
        $process->mustRun();

        return $process->getExitCode() === 0;
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

    public function getMigrationTable()
    {
        return 'migrations_' . md5($this->className());
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
            $configurable = Configurable::find()
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
        \yii\caching\TagDependency::invalidate(
            Yii::$app->cache,
            [
                ActiveRecordHelper::getCommonTag(Configurable::className()),

            ]
        );
    }

    /**
     * Remove module configuration from config files.
     */
    public function removeConfig()
    {
        if ($this->getBehavior('configurableModule') !== null) {
            // This extension has config behavior

            // Find corresponding configurable
            $configurables = Configurable::find()
                ->where('module!=:module', [':module' => $this->id])
                ->all();

            if ($configurables !== null) {
                // The trick is to use previous saved values from other configurables
                // and exclude current module from configuration
                // that is done by setting not to load existing configuration

                ConfigurationUpdater::updateConfiguration(
                    $configurables,
                    false,
                    false
                );

            }


        }
        \yii\caching\TagDependency::invalidate(
            Yii::$app->cache,
            [
                ActiveRecordHelper::getCommonTag(Configurable::className()),

            ]
        );
    }


}