<h1 align="center">Runner IoC Container</h1>

<p align="center">A small IoC Container for PHP</p>

<p align="center">
<a href="https://travis-ci.org/RunnerLee/container"><img src="https://travis-ci.org/RunnerLee/container.svg?branch=master" /></a>
<a href="https://scrutinizer-ci.com/g/RunnerLee/container/?branch=master"><img src="https://scrutinizer-ci.com/g/RunnerLee/container/badges/coverage.png?b=master" title="Scrutinizer Code Coverage"></a>
<a href="https://scrutinizer-ci.com/g/RunnerLee/container/?branch=master"><img src="https://scrutinizer-ci.com/g/RunnerLee/container/badges/quality-score.png?b=master" title="Scrutinizer Code Quality"></a>
<a href="https://styleci.io/repos/176199761"><img src="https://styleci.io/repos/176199761/shield?branch=master" alt="StyleCI"></a>
<a href="https://github.com/RunnerLee/container"><img src="https://poser.pugx.org/runner/container/v/stable" /></a>
<a href="http://www.php.net/"><img src="https://img.shields.io/badge/php-%3E%3D7.0-8892BF.svg" /></a>
<a href="https://github.com/RunnerLee/container"><img src="https://poser.pugx.org/runner/container/license" /></a>
</p>

### Installation
```
$ composer require runner/container
```

### Usage

create an instance of the container, and bind services into the container with a name.

#### basic binding

```php
use Runner\Container\Container;

$container = new Container();

$container->bind('stack', SplStack::class);

$container->make('stack');

$container->bind(ArrayAccess::class, function () {
    return new ArrayObject();
});
```

#### binding implementation

use an interface name as name and bind a concrete implementation to it

```php

$container->bind(ArrayAccess::class, function () {
    return new ArrayObject();
});

$container->make(ArrayAccess::class);

```

#### binding singleton

```php
$container->bind(
    'db', 
    function () {
        return new PDO();
    }, 
    true
);

$container->bind();
```

#### binding instance

just another way to binding singleton

```php
$pdo = new PDO();

$container->instance('db', $pdo);
```

#### alias binding
bind an alias as concrete to a registered service

```php
$container->bind(CacheInterface::class, function () {
    return new FileCache();
});

$container->bind('cache', CacheInterface::class, true);

$container->make('cache');
```

have fun :)

#### contextual binding

bind different implementation to classes while doing injecting

```php
$container->bind(CacheInterface::class, function () {
    return new FileCache();
});

$container->bind('redis_cache', function () {
    return new RedisCache();
});

$container->bindContext(
    PageController::class,
    CacheInterface::class,
    function (Container $container) {
        return $container->make('redis_cache');
    }
);

$controller = $container->make(PageController::class);
```

### References

- [Laravel Container](https://laravel.com/docs/5.8/container)
- [Pimple](https://pimple.symfony.com/)
- [依赖注入与Ioc容器](https://blog.csdn.net/dream_successor/article/details/79078905)
- [\[Wikipedia\] inversion of control](https://en.wikipedia.org/wiki/Inversion_of_control)

### License

MIT
