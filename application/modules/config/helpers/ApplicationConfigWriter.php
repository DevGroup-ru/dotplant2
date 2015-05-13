<?php

namespace app\modules\config\helpers;

use app;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * Writer for application configs
 * @package app\modules\config\helpers
 */
class ApplicationConfigWriter extends Component
{
    /**
     * @var string Filename to read-write for
     */
    public $filename = null;

    /**
     * @var array Configuration array that will be written to filesystem
     */
    public $configuration = null;

    /**
     * @var string Code to append
     */
    public $append = '';

    /**
     * @var bool true if we should load configuration from original file and merge all configuration with it
     */
    public $loadExistingConfiguration = true;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if ($this->filename === null) {
            throw new InvalidConfigException(
                Yii::t(
                    'app',
                    'Filename should be set for ApplicationConfigWriter'
                )
            );
        }
        $filename = Yii::getAlias($this->filename);
        if (is_readable($filename) === true) {
            if ($this->loadExistingConfiguration === true) {
                $this->configuration = include($filename);
            } else {
                $this->configuration = [];
            }
            if (is_array($this->configuration) === false) {
                Yii::trace('Application configuration file is not an array - ' . $filename);

                $this->configuration = [];
            }
        } elseif (file_exists($filename) === false) {
            Yii::trace('Application configuration file does not exist - ' . $filename);
            $this->configuration = [];
        } else {
            throw new InvalidConfigException(
                Yii::t(
                    'app',
                    'Application config file {file} is not readable.',
                    [
                        'file' => $filename,
                    ]
                )
            );
        }


    }

    /**
     * Add values to current configuration array with merging.
     * @param array $values Values to merge from
     */
    public function addValues($values)
    {
        $this->configuration = ArrayHelper::merge($this->configuration, $values);
    }

    /**
     * Writes all configuration to application configuration file
     * @return bool result, true if success
     */
    public function commit()
    {
        $data = <<<EOF
<?php

/*
 * ! WARNING !
 *
 * This file is auto-generated.
 * Please don't modify it by-hand or all your changes can be lost.
 */

{$this->append}

return
EOF;
        $data .= VarDumper::export($this->configuration);

        $data .= ";\n\n";

        $filename = Yii::getAlias($this->filename);

        return file_put_contents($filename, $data, LOCK_EX) !== false;

    }
}