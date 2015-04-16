# Migration tips

## alpha => beta

### Users

Since users subsystem splited into module the most important thing is to replace your URLs including calls to yii\helpers\Url:

1. `default/(login|logout|...)` changed to `user/user/login`
2. `cabinet/profile` changed to `user/user/profile`
3. If you need aliases - set them in url router's config
4. Check if you used direct absolute links in your templates

If you redefined views - keep in mind, that default controller now doesn't handle user-related functions. So you need to rename your theme folders properly.

All social oauth callbacks in your apps should be changed to `user/user/auth` or your social login functions wan't work.

Small-passwords migration WARNING:

> By-default passwords should be at least 8 chars. If you had smaller password - please reset it.