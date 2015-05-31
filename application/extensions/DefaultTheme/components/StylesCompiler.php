<?php

namespace app\extensions\DefaultTheme\components;

use Leafo\ScssPhp\Compiler;
use Yii;
use yii\base\Component;

class StylesCompiler extends Component
{
    private $_compiler = null;

    private function getCompiler()
    {
        if ($this->_compiler === null) {
            $this->_compiler = new Compiler();
            $this->_compiler->setImportPaths($this->getImportPath());
        }
        return $this->_compiler;
    }

    public function variables($variables=[])
    {
        $fn = $this->getImportPath().'variables-custom.scss';
        $content = file_get_contents($fn);
        foreach ($variables as $key=>$value) {
            $scss_key = '$' . $key;
            $regexp = '#^('.preg_quote($scss_key).': )(.*);$#Umsi';
            $count = 0;
            $content = preg_replace($regexp, '$1'.$value.';', $content, -1, $count);
            if ($count === 0) {
                $content .= "\n$scss_key: $value;\n";
            }
        }

        file_put_contents($fn, $content);
    }

    public function compile()
    {
        $out = $this->getCompiler()->compile($this->getMainStyleFile());

        file_put_contents($this->getBasePath().'/css/default-theme.css', $out);
    }

    private function getMainStyleFile()
    {
        return file_get_contents($this->getImportPath() . 'default-theme.scss');
    }

    private function getImportPath()
    {
        return $this->getBasePath() . '/sass/';
    }
    private function getBasePath()
    {
        return Yii::getAlias('@app/extensions/DefaultTheme/assets/theme');
    }
}