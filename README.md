# DotPlant2

DotPlant2 - open-source E-Commerce CMS based on Yii Framework 2(yii2).

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/DevGroup-ru/dotplant2?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge) - join chat and get free support

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/DevGroup-ru/dotplant2/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/DevGroup-ru/dotplant2/?branch=master)

[Documentation](http://docs.dotplant.ru/) is in development - some docs are only available in one language for now(ru or en).

## Minimal system requirements:

- PHP 5.5 or higher
- *nix-based server(well tested on Ubuntu 14.10)
- MySQL 5.5+
- Memcached server or APC for caching purposes is highly recommended

Needed PHP modules:
- gd
- mcrypt
- json
- pdo, pdo-mysql
- memcached(for memcache cache only)
- curl
- intl(optional but recommended)

## Installation

Simple installation:

``` bash

$ cd application
$ php install.php

```

Advanced users can install DotPlant as any other yii cms, ie.:

``` bash

$ cd application
$ php ../composer.phar install
$ ./yii migrate

```

Backend is located at http://YOUR_HOSTNAME/backend/

Installation tutorial for clean Ubuntu 14.10 setup - http://docs.dotplant.ru/en/Installation_and_configuring/Full_stack_installation/Ubuntu_14_10.html

## Current project status

DotPlant 2 is in alpha stage. You can use it on production, but be ready for major changes like migrations merging, auth subsystem rewriting, variable names changes and major view-markup changes.

We are planning to start writing migration help pages when CMS will reach beta stage.
