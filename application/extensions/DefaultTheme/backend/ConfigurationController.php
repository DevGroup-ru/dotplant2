<?php

namespace app\extensions\DefaultTheme\backend;

use app\backend\components\BackendController;
use app\extensions\DefaultTheme\models\ThemeParts;
use app\extensions\DefaultTheme\models\ThemeVariation;

class ConfigurationController extends BackendController
{
    public function actionIndex()
    {
        $parts = ThemeParts::getAllParts();
        $variations = ThemeVariation::getAllVariations();

        return $this->render(
            'index',
            [
                'parts' => $parts,
                'variations' => $variations,
            ]
        );
    }
}