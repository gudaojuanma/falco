## Requirements

- php > 7.0
- ext-phalcon > 3.0
- ext-swoole > 2.0
- ext-yaml > 2.0

## Migration

```sh
# migrate
$ bin/php falco.php migrate

# rollback
$ bin/php falco.php migrate:rollback
```

## Assets

```sh
$ bin/php falco.php assets:combing
```

```php
<?php
namespace App\Http\Controllers;

use Phalcon\Mvc\Controller;
    
class HomeController extends Controller {
    public function indexAction()
    {
        $this->assets->pick('bootstrap.css', 'jquery.js', 'bootstrap.js');
        $this->view->pick('home/index');
    }
}
```

## Queue

```sh
# start worker
$ bin/php falco.php queue:work

# shutdown
$ bin/php falco.php queue:shutdown

## stats
$ bin/php falco.php queue:stats
```

```php
<?php
namespace App\Http\Controllers;

use Phalcon\Mvc\Controller;
    
class HomeController extends Controller {
    public function indexAction()
    {
        dispatch(\App\Jobs\Greeting::class, ['Stranger']);
    }
}

```