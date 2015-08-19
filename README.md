# DotPlant2

[DotPlant2](http://dotplant.ru/) - open-source E-Commerce CMS based on Yii Framework 2(yii2).

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/DevGroup-ru/dotplant2?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge) - join chat and get free support(:ru: & :uk:)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/DevGroup-ru/dotplant2/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/DevGroup-ru/dotplant2/?branch=master)

[Documentation](http://docs.dotplant.ru/) is in development - some docs are only available in one language for now(ru or en).

[Sites made with DotPlant2](http://dotplant.ru/showcase) - [send us](http://dotplant.ru/contact) your site to be added.

## Features

### True SEO-friendly

Configurable routes and URLs system allows search engines to build correct site structure in their index.

Every page, product or category has it's own fields for SEO - URL slug, TITLE tag, H1, META description, breadcrumbs label and name that is used as anchor.

### Smart content editing

- Use content blocks(like chunks in MODx) for repeated content parts.
- Create and integrate feedback forms with automatic validation, spam-checking and management from backend.

### Configurable multipurpose E-Commerce

- Graph order workflow - you can configure the whole process and options as your business needs.
- Multi-currency support with pluggable automatic currency rate updating.
- Discounts for all! Users, products, orders, delivery, etc.
- Pluggable Payment types - 12 build-in payment handlers and it's easy to integrate any other.
- Filtration of products can be configured for every category.
- Support of configurable advanced fields for user, customer or company.
- Integration with 1C through CommerceML _alpha stage_

_And more to come..._

## Minimal system requirements:

- PHP 5.5 or higher
- *nix-based server
- MySQL 5.5+
- Memcached server or APC for caching purposes is highly recommended

Needed PHP modules:
- gd
- json
- pdo, pdo-mysql
- memcached(for memcache cache only)
- curl
- intl(optional but recommended)

Perfectly runs on $10 VPS from [DigitalOcean](https://www.digitalocean.com/?refcode=16218608bff6)

## Installation

First install composer dependencies:

``` bash

$ cd application
$ php ../composer.phar global require "fxp/composer-asset-plugin:1.0.0"
$ php ../composer.phar install --prefer-dist --optimize-autoloader

```

Your virtual server DocumentRoot directory should point to `application/web`.

Then go to http://YOUR_HOSTNAME/installer.php or run `./install` in application folder.

Backend is located at http://YOUR_HOSTNAME/backend/

**WARNING** This installation brings you an empty store without any products or categories.

## Demo data

**WARNING** Demo data is in Russian for now :(

**WARNING-2** Demo migration also downloads big(~90Mb) archive with sample images.

```
vagrant ssh
cd /var/www/dotplant2/application/
./demo.sh
./yii cache/flush cache --interactive=0
```

Go to http://YOUR_HOSTNAME/catalog


## Vagrant

This box currently uses master branch of dotplant2.

How to use:

```
git clone https://github.com/DevGroup-ru/dotplant2-vagrant.git && cd dotplant2-vagrant
vagrant up
vagrant ssh
```

Go to: http://192.168.56.111/

Backend: http://192.168.56.111/backend

Admin user is `admin` with password `password`.

Vagrant box doesn't installs demo data. You can do it manually(see above).

## Current project status

DotPlant 2 is in beta stage. You can use it on production, but be ready for minor changes like variable names changes,  view-markup changes and new theme parts&widgets.

## Migrating from alpha

See [migration tips](migration-tips.md) and feel free to ask for help in [gitter chat](https://gitter.im/DevGroup-ru/dotplant2).
