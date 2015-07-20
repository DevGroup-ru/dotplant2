<?php

namespace app\modules\core\helpers;


use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Yii;
use yii\base\Component;

/**
 * Class UpdateHelper handles all functions like composer updating, migration applying
 * @package app\modules\core\helpers
 */
class UpdateHelper extends Component
{
    public $composerTimeout = 3600;
    public $composerIdleTimeout = 60;
    public $migrationTimeout = 3600;
    public $migrationIdleTimeout = 60;
    public $composerHomeDirectory = './.composer/';

    /**
     * Applies migrations for module.
     *
     * Function returns Process, but not runs it.
     * For synchronous run use applyMigrations()->mustRun()
     * For async run use applyMigrations()->start & wait
     *
     * @param string $migrationPath Path to migrations (`--migrationPath` argument for `yii migrate/up` command).
     * @param bool $applyAppMigrations True if we also need to apply application migrations before module migration
     * @param bool $updateComposer True if we also need to run composer update before all actions
     * @return \Symfony\Component\Process\Process
     */
    public function applyMigrations($migrationPath, $migrationTable = '{{%migration}}', $applyAppMigrations = true, $updateComposer = true, $down = false)
    {
        if ($applyAppMigrations === true) {
            $this->applyAppMigrations($updateComposer)->mustRun();
        } elseif ($updateComposer === true) {
            $this->updateComposer()->mustRun();
        }

        $builder = $this->migrationCommandBuilder($migrationPath, $migrationTable, $down);

        $process = $builder->getProcess();

        $process
            ->setTimeout($this->migrationTimeout)
            ->setIdleTimeout($this->migrationIdleTimeout);

        return $process;
    }

    /**
     * Applies all latest application migrations.
     * Migrations from modules are not applied.
     *
     * Function returns Process, but not runs it.
     * For synchronous run use applyAppMigrations()->mustRun()
     * For async run use applyAppMigrations()->start & wait
     *
     * @param bool $updateComposer True if we should update composer before applying migrations
     * @return \Symfony\Component\Process\Process
     */
    public function applyAppMigrations($updateComposer = true, $down = false)
    {
        if ($updateComposer === true) {
            $this->updateComposer()->mustRun();
        }

        $builder = $this->migrationCommandBuilder('', '{{%migration}}', $down);

        $process = $builder->getProcess();

        $process
            ->setTimeout($this->migrationTimeout)
            ->setIdleTimeout($this->migrationIdleTimeout);

        return $process;
    }

    /**
     * Returns ProcessBuilder instance with predefined process command for migration command execution.
     *
     * @return \Symfony\Component\Process\ProcessBuilder
     */
    private function migrationCommandBuilder($migrationPath = '', $migrationTable = '{{%migration}}', $down = false)
    {
        $builder = new ProcessBuilder();

        $builder
            ->setWorkingDirectory(Yii::getAlias('@app'))
            ->setPrefix($this->getPhpExecutable())
            ->setArguments([
                realpath(Yii::getAlias('@app').'/yii'),
                'migrate/'.($down?'down':'up'),
                '--color=0',
                '--interactive=0',
                '--migrationTable=' . $migrationTable,
                $down ? 65536 : 0
            ]);



        if (empty($migrationPath) === false) {
            $builder->add('--migrationPath=' . $migrationPath);
        }

        return $builder;
    }

    /**
     * Runs composer require command for installing new package to CMS.
     * Migrations and other actions should be handled separately.
     * This command only executes 'composer require your/package 0.0@dev'
     *
     * Function returns Process, but not runs it.
     * For synchronous run use composerRequire()->mustRun()
     * For async run use composerRequire()->start & wait
     *
     * @param string $package Package name, version can be appended with space
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     * @throws \Symfony\Component\Process\Exception\ProcessTimedOutException
     * @return \Symfony\Component\Process\Process
     */
    public function composerRequire($package)
    {
        $builder = $this->getComposerBuilder()
            ->add('require')
            ->add('--prefer-dist')
            ->add($package);

        $process = $builder->getProcess();
        $process
            ->setTimeout($this->composerTimeout)
            ->setIdleTimeout($this->composerIdleTimeout);

        return $process;
    }



    /**
     * Runs composer remove command for uninstalling new package to CMS.
     * Migrations and other actions should be handled separately.
     * This command only executes 'composer remove your/package'
     *
     * Function returns Process, but not runs it.
     * For synchronous run use composerRequire()->mustRun()
     * For async run use composerRequire()->start & wait
     *
     * @param string $package Package name, version can be appended with space
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     * @throws \Symfony\Component\Process\Exception\ProcessTimedOutException
     * @return \Symfony\Component\Process\Process
     */
    public function composerRemove($package)
    {
        $builder = $this->getComposerBuilder()
            ->add('remove')
            ->add($package);

        $process = $builder->getProcess();
        $process
            ->setTimeout($this->composerTimeout)
            ->setIdleTimeout($this->composerIdleTimeout);

        return $process;
    }

    /**
     * Updates composer dependencies using base composer.json located in application folder
     *
     * Function returns Process, but not runs it.
     * For synchronous run use updateComposer()->mustRun()
     * For async run use updateComposer()->start & wait
     *
     * @param string $exactPackages List of exact packages to update separated by space
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     * @throws \Symfony\Component\Process\Exception\ProcessTimedOutException
     * @return \Symfony\Component\Process\Process
     */
    public function updateComposer($exactPackages='')
    {
        $builder = $this->getComposerBuilder()
            ->add('update')
            ->add('--optimize-autoloader')
            ->add('--no-interaction');

        if (empty($exactPackages) === false) {
            $builder->add($exactPackages);
        }

        $process = $builder->getProcess();
        $process
            ->setTimeout($this->composerTimeout)
            ->setIdleTimeout($this->composerIdleTimeout);

        return $process;
    }

    /**
     * @return string Returns path to PHP executable based on predefined PHP variable PHP_BINDIR
     */
    private function getPhpExecutable()
    {
        return PHP_BINDIR . '/php';
    }

    /**
     * Returns ProcessBuilder instance with predefined process command for any composer command execution
     * (update, require, etc.)
     * @return \Symfony\Component\Process\ProcessBuilder
     */
    private function getComposerBuilder()
    {
        $builder = new ProcessBuilder();

        $builder
            ->setEnv('COMPOSER_HOME', $this->composerHomeDirectory)
            ->setWorkingDirectory(Yii::getAlias('@app'))
            ->setPrefix($this->getPhpExecutable())
            ->setArguments([
                realpath(
                    Yii::getAlias('@app').'/../'
                ) . '/composer.phar',
                '-v',
            ]);


        return $builder;
    }
}