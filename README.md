yii2-hidemy-proxy-parse
===============================
Parse proxy from hidemy.name extension for Yii2

Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).
Either run

```
php composer.phar require darovec/yii2-hidemy-proxy-parse
```

or add


```
"darovec/yii2-hidemy-proxy-parse": "*"
```

to the require section of your `composer.json` file.

Usage
-----

```
use darovec\hidemyproxy\Proxy;

$ipOne = Proxy::one();
$ipList = Proxy::list();
```

<b>$ipOne</b> - single proxy ip

<b>$ipList</b> - list of proxy ip

Usage exemple
-------------

```php
<?php
namespace app\controllers;

use yii\web\Controller;
use darovec\hidemyproxy\Proxy;

class ProxyController extends Controller
{
	public function actionIndex()
	{
		$proxyIp = Proxy::one();
	}

}
```