<?php

namespace app\modules\core\models;

use app;
use app\components\ExtensionModule;
use Yii;
use yii\base\ErrorException;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\db\ActiveQuery;
use yii\data\ActiveDataProvider;
use Packagist\Api\Result\Package\Version;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "{{%extensions}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $is_active
 * @property string $force_version
 * @property integer $type
 * @property string $latest_version
 * @property string $current_package_version_timestamp
 * @property string $latest_package_version_timestamp
 * @property string $homepage
 * @property string $namespace_prefix
 */
class Extensions extends \yii\db\ActiveRecord
{
    private static $identity_map = [];
    private static $identity_map_by_package_name = [];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%extensions}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'namespace_prefix'], 'required'],
            [['is_active', 'type'], 'integer'],
            [['current_package_version_timestamp', 'latest_package_version_timestamp'], 'safe'],
            [['name', 'force_version', 'latest_version', 'homepage', 'namespace_prefix'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'is_active' => Yii::t('app', 'Is Active'),
            'force_version' => Yii::t('app', 'Force Version'),
            'type' => Yii::t('app', 'Type'),
            'latest_version' => Yii::t('app', 'Latest Version'),
            'current_package_version_timestamp' => Yii::t('app', 'Current Package Version Timestamp'),
            'latest_package_version_timestamp' => Yii::t('app', 'Latest Package Version Timestamp'),
            'homepage' => Yii::t('app', 'Homepage'),
            'namespace_prefix' => Yii::t('app', 'Namespace Prefix'),
        ];
    }

    /**
     * Returns model instance by ID using IdentityMap
     * @param integer $id
     * @return Extensions|null
     */
    public static function findById($id)
    {
        if (!isset(static::$identity_map[$id])) {
            static::$identity_map[$id] = Yii::$app->cache->get(static::tableName() . ':' . $id);
            if (static::$identity_map[$id] === false) {
                static::$identity_map[$id] = static::findOne($id);
                if (is_object(static::$identity_map[$id]) === true) {
                    Yii::$app->cache->set(
                        static::tableName() . ':' . $id,
                        static::$identity_map[$id],
                        86400,
                        new TagDependency(
                            [
                                'tags' => [
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(static::className(), $id),
                                ],
                            ]
                        )
                    );
                    Yii::$app->cache->set(
                        static::tableName() . ':name:' . static::$identity_map[$id]->name,
                        static::$identity_map[$id],
                        86400,
                        new TagDependency(
                            [
                                'tags' => [
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(static::className(), $id),
                                ],
                            ]
                        )
                    );
                }
            }
            if (is_object(static::$identity_map[$id]) === false) {
                static::$identity_map_by_package_name[static::$identity_map[$id]->name] = static::$identity_map[$id];
            }
        }
        return static::$identity_map[$id];
    }

    /**
     * Returns model instance by name using IdentityMap
     * @param string $name
     * @return Extensions|null
     */
    public static function findByName($name)
    {
        if (!isset(static::$identity_map_by_package_name[$name])) {
            static::$identity_map_by_package_name[$name] = Yii::$app->cache->get(static::tableName() . ':name:' . $name);
            if (static::$identity_map_by_package_name[$name] === false) {
                static::$identity_map_by_package_name[$name] = static::find()
                    ->where(['name' => $name])
                    ->one();
                if (is_object(static::$identity_map_by_package_name[$name]) === true) {
                    $id = static::$identity_map_by_package_name[$name]->id;
                    Yii::$app->cache->set(
                        static::tableName() . ':' . $id,
                        static::$identity_map_by_package_name[$name],
                        86400,
                        new TagDependency(
                            [
                                'tags' => [
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(static::className(), $id),
                                ],
                            ]
                        )
                    );
                    Yii::$app->cache->set(
                        static::tableName() . ':name:' . $name,
                        static::$identity_map_by_package_name[$name],
                        86400,
                        new TagDependency(
                            [
                                'tags' => [
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(static::className(), $id),
                                ],
                            ]
                        )
                    );
                }
            }
            if (is_object(static::$identity_map_by_package_name[$name]) === true) {
                $id = static::$identity_map_by_package_name[$name]->id;
                static::$identity_map[$id] = static::$identity_map_by_package_name[$name];
            }
        }
        return static::$identity_map_by_package_name[$name];
    }

    public static function isPackageInstalled($name)
    {
        $package = static::findByName($name);
        return $package !== null;
    }

    public static function isPackageActive($name)
    {
        $package = static::findByName($name);
        if ($package === null) {
            return false;
        } else {
            return boolval($package->is_active);
        }
    }

    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['is_active' => $this->is_active]);
        $query->andFilterWhere(['type' => $this->type]);
        $query->andFilterWhere(['latest_version' => $this->latest_version]);

        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'force_version', $this->force_version]);
        $query->andFilterWhere(['like', 'current_package_version_timestamp', $this->current_package_version_timestamp]);
        $query->andFilterWhere(['like', 'latest_package_version_timestamp', $this->latest_package_version_timestamp]);
        $query->andFilterWhere(['like', 'homepage', $this->homepage]);
        $query->andFilterWhere(['like', 'namespace_prefix', $this->namespace_prefix]);

        return $dataProvider;
    }

    /**
     * @return ExtensionTypes Instance of corresponding ExtensionTypes model
     */
    public function getExtensionType()
    {
        return ExtensionTypes::findById($this->type);
    }


    public function activateExtension($update = true)
    {
        // non-active extension exists in database and composer but not installed(or uninstalled)
        if ($update === true) {
            /** @var \app\modules\core\helpers\UpdateHelper $updateHelper */
            $updateHelper = Yii::$app->updateHelper;

            try {
                $updateHelper->updateComposer($this->name)->mustRun();
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', "Error updating composer");
                return false;
            }
        }

        // install!
        /** @var \app\components\ExtensionModule $moduleClassName */
        $moduleClassName = $this->namespace_prefix . 'Module';
        if (class_exists($moduleClassName) === true) {
            return $moduleClassName::installModule(false, false);
        } else {
            Yii::$app->session->setFlash('error', "Extension module class not found.");
            return false;
        }
    }

    public function deactivateExtension()
    {
        /** @var \app\components\ExtensionModule $moduleClassName */
        $moduleClassName = $this->namespace_prefix . 'Module';
        if (class_exists($moduleClassName) === true) {
            $moduleId = $moduleClassName::$moduleId;
            if (isset(Yii::$app->modules[$moduleId])) {

                /** @var ExtensionModule $module */
                $module = Yii::$app->getModule($moduleId);
                if ($module->uninstallModule()) {
                    $this->is_active = 0;
                    return $this->save();
                } else {
                    Yii::$app->session->setFlash('error', "Can't uninstall extension.");
                    return false;
                }

            } else {
                Yii::$app->session->setFlash('error', "Extension module is not loaded in application config.");
                return false;
            }
        } else {
            Yii::$app->session->setFlash('error', "Extension module class not found.");
            return false;
        }
    }

    public function removeExtensionPackage()
    {

        /** @var \app\modules\core\helpers\UpdateHelper $updateHelper */
        $updateHelper = Yii::$app->updateHelper;

        try {
            $process = $updateHelper->composerRemove($this->name)->mustRun();
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', "Error removing composer package");
            return false;
        }
        return $process->getExitCode()===0;

    }

    public function updateExtensionPackage()
    {
        /** @var \app\modules\core\helpers\UpdateHelper $updateHelper */
        $updateHelper = Yii::$app->updateHelper;

        try {
            return $updateHelper->updateComposer($this->name)->mustRun();
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', "Error updating composer package");
            return false;
        }
    }

    public function installExtensionPackage()
    {

        /** @var \app\modules\core\helpers\UpdateHelper $updateHelper */
        $updateHelper = Yii::$app->updateHelper;

        try {
            return $updateHelper->composerRequire($this->name)->mustRun();
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', "Error adding composer package");
            return false;
        }

    }

    public static function installExtension($name, $updateComposer = false)
    {
        $extension = null;

        /** @var app\modules\core\helpers\UpdateHelper $updateHelper */

        if (Extensions::isPackageInstalled($name) === true) {
            // we should just activate it
            $extension = Extensions::findByName($name);
        } else {
            $client = new app\modules\core\components\PackagistClient();

            $package = $client->get($name);

            /** @var Version[] $versions */
            $versions = $package->getVersions();
            /** @var Version $version */
            // we are assuming that first version is latest
            $version = array_shift($versions);

            $extension = new Extensions();
            $extension->name = $name;
            $extension->homepage = $version->getHomepage();
            $extension->current_package_version_timestamp = date('Y-m-d H:i:s', strtotime($version->getTime()));
            $extension->latest_package_version_timestamp = date('Y-m-d H:i:s', strtotime($version->getTime()));

            $autoload = $version->getAutoload();
            if (isset($autoload['psr-4'])) {
                $namespaces = array_keys($autoload['psr-4']);
                $prefix = array_shift($namespaces);

                if (isset(array_keys($autoload['psr-4'])[0])) {
                    $extension->namespace_prefix = $prefix;
                }
            }
            $extension->is_active = 0;


            if ($extension->save() === false) {
                throw new ErrorException(Yii::t('app', 'Unable to save extension to database'));
            }

            if ($extension->installExtensionPackage() === false) {
                throw new ErrorException(Yii::t('app', 'Could not install extension package'));
            }
            $loader = require(Yii::getAlias('@app/vendor/autoload.php'));
            $psr4 = require(Yii::getAlias('@app/vendor/composer/autoload_psr4.php'));
            foreach ($psr4 as $prefix => $paths) {
                $loader->setPsr4($prefix, $paths);
            }
        }

        if ($extension === null) {
            throw new NotFoundHttpException;
        }
        try {
            $result = $extension->activateExtension($updateComposer);
        } catch (\Exception $e) {
            throw new ErrorException(Yii::t('app', 'Unable to activate extension').': '.$e->getMessage());
        }
        if ($result) {
            $extension->is_active = 1;
            $extension->save();
        }
        return $extension;
    }

    public static function installStudioPackage($name, $path)
    {
        $extension = null;

        /** @var app\modules\core\helpers\UpdateHelper $updateHelper */

        if (Extensions::isPackageInstalled($name) === true) {
            // we should just activate it
            $extension = Extensions::findByName($name);
        } else {
            $extension = new Extensions();
            $extension->name = $name;
            $extension->current_package_version_timestamp = date('Y-m-d H:i:s');
            $extension->latest_package_version_timestamp = date('Y-m-d H:i:s');

            $composerPath = rtrim($path,'/').'/composer.json';

            $data = Json::decode(file_get_contents($composerPath), true);

            $autoload = ArrayHelper::getValue($data, 'autoload', []);

            if (isset($autoload['psr-4'])) {
                $namespaces = array_keys($autoload['psr-4']);
                $prefix = array_shift($namespaces);

                if (isset(array_keys($autoload['psr-4'])[0])) {
                    $extension->namespace_prefix = $prefix;
                }
            }
            $extension->is_active = 0;
            if ($extension->save() === false) {
                throw new ErrorException(Yii::t('app', 'Unable to save extension to database'));
            }
            $loader = require(Yii::getAlias('@app/vendor/autoload.php'));
            $psr4 = require(Yii::getAlias('@app/vendor/composer/autoload_psr4.php'));
            foreach ($psr4 as $prefix => $paths) {
                $loader->setPsr4($prefix, $paths);
            }
        }
        $result = $extension->activateExtension(false);
        if ($result) {
            $extension->is_active = 1;
            $extension->save();
        }
        return $extension;
    }
}
