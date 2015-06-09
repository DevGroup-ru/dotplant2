<?php

namespace app\modules\user\models;

use app;
use app\validators\ClassnameValidator;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * AuthClientConfig is the model for getting user input on exact AuthClient configuration
 * @package app\modules\user\models
 */
class AuthClientConfig extends Model
{
    /**
     * @var string Name of AuthClient class
     */
    public $class_name = '';

    const TYPE_OAUTH1 = 'oauth1';
    const TYPE_OAUTH2 = 'oauth2';
    const TYPE_OPENID = 'openid';

    public $clientType = null;

    // oauth1
    /**
     * @var string OAuth consumer key.
     */
    public $consumerKey;
    /**
     * @var string OAuth consumer secret.
     */
    public $consumerSecret;


    // oauth2
    /**
     * @var string OAuth client ID.
     */
    public $clientId;
    /**
     * @var string OAuth client secret.
     */
    public $clientSecret;

    // descriptive options for all clients

    /**
     * Unique client id, which separates it from other clients, it could be used in URLs, logs etc.
     *
     * @var string
     */
    public $id;

    /**
     * External auth provider name, which this client is match too.
     * Different auth clients can share the same name, if they refer to the same external auth provider.
     * For example: clients for Google OpenID and Google OAuth have same name "google".
     * This attribute can be used inside the database, CSS styles and so on.
     *
     * @var string
     */
    public $name;

    /**
     * User friendly name for the external auth provider, it is used to present auth client at the view layer.
     *
     * @var string
     */
    public $title;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class_name',], 'required', 'whenClient' => 'function(){return false;}'],
            [['class_name',], ClassnameValidator::className()],

            [['clientType'], 'required'],
            [['clientType'], 'in', 'range' => [self::TYPE_OAUTH1, self::TYPE_OAUTH2, self::TYPE_OPENID]],

            [['consumerKey', 'consumerSecret'], 'required', 'when'=>function($model){
                return $model->clientType === self::TYPE_OAUTH1;
            }],
            [['clientId', 'clientSecret'], 'required', 'when'=>function($model){
                return $model->clientType === self::TYPE_OAUTH2;
            }],

            [['id', 'name', 'title',], 'string', ],
        ];
    }

    /**
     * @return array Autocomplete items for class_name with built-in auth clients
     */
    public static function classNameAutoComplete()
    {
        $dotplant_authclients = self::getClassNames(
            Yii::getAlias('@app/modules/user/authclients/'),
            'app\modules\user\authclients'
        );

        $yii2_authclients = self::getClassNames(
            Yii::getAlias('@vendor/yiisoft/yii2-authclient/clients/'),
            'yii\authclient\clients'
        );

        return ArrayHelper::merge($dotplant_authclients, $yii2_authclients);
    }

    /**
     * Returns class names array from all files in directory
     * @param string $dir Directory to traverse
     * @param string $namespacePrefix Namespace prefix without trailing backslash
     * @return array
     */
    private static function getClassNames($dir, $namespacePrefix)
    {
        $result = [];
        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        /* @var \RecursiveDirectoryIterator[] $files */
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            /** @var \SplFileInfo $file */
            $result [] = $namespacePrefix . '\\' . $file->getBaseName('.php');
        }
        return $result;
    }

    /**
     * Determines type attribute based on class_name attribute
     */
    public function determineType()
    {
        $this->clientType = null;

        if (isset($this->class_name) === true) {
            $class_name = $this->class_name;
            $class = new $class_name;
            if ($class instanceof \yii\authclient\OAuth1) {
                $this->clientType = self::TYPE_OAUTH1;
            } elseif ($class instanceof \yii\authclient\OAuth2) {
                $this->clientType = self::TYPE_OAUTH2;
            } elseif ($class instanceof \yii\authclient\OpenId) {
                $this->clientType = self::TYPE_OPENID;
            }
        }
    }


}