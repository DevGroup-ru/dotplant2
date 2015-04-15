<?php

namespace app\backend\actions;

use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use yii\base\Action;
use yii\base\Exception;
use yii\base\Module;
use yii\caching\Cache;

class FlushCacheAction extends Action
{

    public $view = 'message';

    /**
     * Recursive flush all app cache
     * @param Module $current Current Module
     * @return string execute message
     */
    protected function flushCache(Module $current = null)
    {
        $message = '';

        if ($current === null) {
            $current = \Yii::$app;
        }

        $modules = $current->getModules();
        foreach ($modules as $moduleName => $module) {
            if (is_array($module)) {
                $module = $current->getModule($moduleName, true);
            }
            if ($module instanceof Module) {
                $message .= $this->flushCache($module);
            }
        }

        $components = $current->getComponents();
        foreach ($components as $componentName => $component) {
            if (is_array($component)) {
                $component = $current->get($componentName);
            }
            if ($component instanceof Cache) {
                $message .= $component->flush() ?
                    '<p>' . \Yii::t(
                        'app',
                        '{currentModuleName} {componentName} is flushed',
                        [
                            'currentModuleName' => $current->className(),
                            'componentName' => $component->className(),
                        ]
                    ) . '</p>' :
                    '';
            }
        }
        return $message;
    }

    /**
     * Flush webroot/assets/
     * @return string execute message
     */
    protected function flushAssets()
    {
        $message = '';
        $except = [\Yii::getAlias('@webroot/assets/.gitignore'), \Yii::getAlias('@webroot/assets/index.html')];
        $dir = \Yii::getAlias('@webroot/assets');
        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        /* @var RecursiveDirectoryIterator[] $files */
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if (!in_array($file->getRealPath(), $except)) {
                try {
                    if ($file->isDir() && $file->isLink() === false) {
                        rmdir($file->getRealPath());
                    } elseif ($file->isLink() === true) {
                        unlink($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename());
                    } else {
                        unlink($file->getRealPath());
                    }
                } catch (Exception $ex) {
                    $message .= '<p>'.$ex->getMessage().'</p>';
                }
            }
        }
        $message .= '<p>' . \Yii::t('app', 'Assets are flushed') . '</p>';
        return $message;
    }

    public function run()
    {
        $message = $this->flushCache();
        $message .= $this->flushAssets();
        return $this->controller->renderPartial($this->view, ['message' => $message]);
    }
}