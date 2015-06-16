# Migration tips

## alpha => 2.0.0-beta

## step-by-step

1. dump your database
2. clone dotplant repo
3. run `install.sh` from `application` folder
4. open `<your_server_name>/installer.php` and walk thru steps
5. restore database from dump
6. run migrations from `application/yii migrate --migrationPath="@app/extensions/migrations/alpha-beta/"`
7. run migrations from `application/yii migrate`

### Database migrations

First you need to run migrations from a-to-b
`./yii migrate --migrationPath="@app/extensions/migrations/alpha-beta/"`

### Theme

You should change your theme pathMap routes. It's look like a next example.

> **WARNING** Theme part can be not actual! Someone, please check it.

Old theme configuration:

```php
'components' => [
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@app/views' => '@webroot/theme/views/controllers',
                    '@app/widgets' => '@webroot/theme/views/widgets',
                ],
                'baseUrl' => '@webroot/theme/views/controllers',
            ],
        ],
    ],

```

New theme configuration:

```php
'components' => [
    'view' => [
        'theme' => [
            'pathMap' => [
                '@app/views' => '@webroot/theme/views/controllers',
                '@app/modules/page/views' => '@webroot/theme/views/controllers',
                '@app/modules/shop/views' => '@webroot/theme/views/controllers',
                '@app/widgets' => '@webroot/theme/views/widgets',
            ],
            'baseUrl' => '@webroot/theme/views/controllers',
        ],
    ],
],
```

For new projects we advice to use new theme structure which you can create via `./yii admin/theme-create`.  

### Users

Since users subsystem splitted into module the most important thing is to replace your URLs including calls to `yii\helpers\Url`:

1. `default/(login|logout|...)` changed to `user/user/login`
2. `cabinet/profile` changed to `user/user/profile`
3. If you need aliases - set them in url router's config
4. Check if you used direct absolute links in your templates

If you redefined views - keep in mind, that default controller now doesn't handle user-related functions. So you need to rename your theme folders properly.

All social oauth callbacks in your apps should be changed to `user/user/auth` or your social login functions wan't work.

Small-passwords migration WARNING:

> By-default passwords should be at least 8 chars. If you had smaller password - please reset it.


### Shop

Shop splitted into module 'shop'.

LastViewedProducts changed it's namespace from `app\components\LastViewedProducts` to `app\modules\shop\helpers\LastViewedProducts`.

show-proppertiews-widget.php view wrap container attribute changed from id to class.

`\app\widgets\ProductCompareListWidget` was replaced by `\app\modules\shop\widgets\ProductCompare` and has several changes in parameters

`\app\widgets\CategoriesWidget` was replaced by `\app\modules\shop\widgets\CategoriesList` and has some changes in parameters:
* `viewFile` = view file
* `rootCategory` = id of category wich root for others
* `depth` = how deep menu is (null = all levels)
* `includeRoot` = include `rootCategory` or not
* `fetchModels` = put category models into view or not (default is false)
* `excludedCategories` = array of categories id`s for exclude


### Pages
Pages split to page module.
All changes in DB contained in m150428_120959_page_move migration

### Reviews
Pages split to reviews module.
All changes in DB contained in m150508_084640_review_move , m150506_133039_review_module  migrations
ReviewsWidget changed it's namespace from `app\reviews\widgets` to `app\modules\review\widgets`.
If you used the own template of the ReviewsWidget , change the action of form
Also migrations is creating a new form record for reviews in database. You can add create different forms for different pages now and add custom fields to review.
Important! You must add your email address to form model for receiving email notifications.

### Data
All changes in DB contained in m150512_060716_data_module_move  migrations

### robots.txt
Save content of robots.txt in DB (/seo/manage/robots).

### Images

Images subsystem splitted to module `Images`. To migrate do this steps:

1. Update dependencies via composer
2. insert into theme console config
```
        'components' => [
            'fs' => [
                'class' => '\creocoder\flysystem\LocalFilesystem',
                'path' => '@webroot/files',
            ],
        ],
```
3. Apply migration m150413_094340_thumbnail.php
4. Widget ImgSearch.php changed it's name, namespace and parameters:
    * name from `ImgSearch.php` to `ObjectImageWidget.php`
    * namespace from `app\widgets` to `app\modules\image\widgets`
    * parameters `model` instead of `objectId` and `objectModelId`; added bool `useWatermark`
5. Model `Image` changed namespace from `app\models` to `app\modules\image\models`
6. Removed columns `image_src` and `thumbnail_src` from table `image`
7. Image, thumbnail, watermark and thumbnail width watermark src can be received by controller `app\modules\image\controllers\ImageController`

### Templates and views

application/views/layouts/main-page is removed.
Now DotPlant2 comes with awesome multipurpose theme.


### Config migrations
Config model is deleted.
Here's how config keys are replaced:

- core.autoCompleteResultsCount - Core module autoCompleteResultsCount
- core.fileUploadPath - Core module fileUploadPath
- core.imperavi.uploadDir - Backend module -> wysiwygUploadDir
- spamCheckerConfig values - Core module spamCheckerApiKey and spamCheckerInterpretFields
- all email config values - Core module `Email configuration`
- ErrorMonitor, Newsletter config values are not fixed yet - this modules will be rewritten
