<?php

use yii\db\Migration;
use app\modules\config\helpers\ApplicationConfigWriter;
use app\behaviors\spamchecker\AkismetSpamChecker;
use yii\helpers\ArrayHelper;

class m151102_110139_yandex_spam_checker_remove extends Migration
{
    public function up()
    {
        $this->delete(
            \app\models\SpamChecker::tableName(),
            ['name' => 'Yandex']
        );
        $webConfigurablesArray = include(Yii::getAlias('@app/config/web-configurables.php'));
        if (ArrayHelper::getValue($webConfigurablesArray, 'modules.core.spamCheckerApiKey', '') == 'app\behaviors\spamchecker\YandexSpamChecker') {
            $webConfigurablesArray['modules']['core']['spamCheckerApiKey'] = AkismetSpamChecker::class;
            $webConfigurables = new ApplicationConfigWriter([
                'filename' => '@app/config/web-configurables.php',
                'loadExistingConfiguration' => false,
            ]);
            $webConfigurables->addValues($webConfigurablesArray);
            $webConfigurables->commit();
        }
        $coreConfigurablesArray = include(Yii::getAlias('@app/config/configurables-state/core.php'));
        if (ArrayHelper::getValue($coreConfigurablesArray, 'spamCheckerApiKey', '') == 'app\behaviors\spamchecker\YandexSpamChecker') {
            $coreConfigurablesArray['spamCheckerApiKey'] = AkismetSpamChecker::class;
            $coreConfigurables = new ApplicationConfigWriter([
                'filename' => '@app/config/configurables-state/core.php',
                'loadExistingConfiguration' => false,
            ]);
            $coreConfigurables->addValues($coreConfigurablesArray);
            $coreConfigurables->commit();
        }
    }

    public function down()
    {
        echo "m151102_110139_yandex_spam_checker_remove cannot be reverted.\n";

        return false;
    }
}
