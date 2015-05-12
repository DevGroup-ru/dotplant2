# Migration tips

## alpha => beta

### Users

Since users subsystem splited into module the most important thing is to replace your URLs including calls to `yii\helpers\Url`:

1. `default/(login|logout|...)` changed to `user/user/login`
2. `cabinet/profile` changed to `user/user/profile`
3. If you need aliases - set them in url router's config
4. Check if you used direct absolute links in your templates

If you redefined views - keep in mind, that default controller now doesn't handle user-related functions. So you need to rename your theme folders properly.

All social oauth callbacks in your apps should be changed to `user/user/auth` or your social login functions wan't work.

Small-passwords migration WARNING:

> By-default passwords should be at least 8 chars. If you had smaller password - please reset it.


### Shop

Shop splited into module 'shop'.

LastViewedProducts changed it's namespace from `app\components\LastViewedProducts` to `app\modules\shop\helpers\LastViewedProducts`.

### Pages
Pages split to page module.
All changes in DB contained in m150428_120959_page_move migration

### Reviews
Pages split to reviews module.
All changes in DB contained in m150508_084640_review_move , m150506_133039_review_module  migrations
ReviewsWidget changed it's namespace from `app\reviews\widgets` to `app\modules\review\widgets`.
If you used the own template of the ReviewsWidget , change the action of form

### Data
All changes in DB contained in m150512_060716_data_module_move  migrations