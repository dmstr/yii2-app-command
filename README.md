Phundament Yii 2 Application Management Command
===============================================

**NOTE! PROJECT IS IN EARLY DEVELOPMENT STAGE, ALWAYS BACKUP OR COMMIT YOUR FILES BEFORE USING IT!**

Console base-command to manage your application source code and configuration settings.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist dmstr/yii2-app-command "*"
```

or add

```
"dmstr/yii2-app-command": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, configure it in your `console` config:

```php
    'controllerMap'       => [
        'app' => 'dmstr\\console\\controllers\\AppController',
    ],

```

Run the command
```php
./yii app
```