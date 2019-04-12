<h1 align="center">Runner IoC Container</h1>

<p align="center">A small IoC Container for PHP</p>

<p align="center">
<a href="https://travis-ci.org/RunnerLee/container"><img src="https://travis-ci.org/RunnerLee/container.svg?branch=master" /></a>
<a href="https://scrutinizer-ci.com/g/RunnerLee/container/?branch=master"><img src="https://scrutinizer-ci.com/g/RunnerLee/container/badges/coverage.png?b=master" title="Scrutinizer Code Coverage"></a>
<a href="https://scrutinizer-ci.com/g/RunnerLee/container/?branch=master"><img src="https://scrutinizer-ci.com/g/RunnerLee/container/badges/quality-score.png?b=master" title="Scrutinizer Code Quality"></a>
<a href="https://styleci.io/repos/176199761"><img src="https://styleci.io/repos/176199761/shield?branch=master" alt="StyleCI"></a>
<a href="https://github.com/RunnerLee/container"><img src="https://poser.pugx.org/runner/container/v/stable" /></a>
<a href="http://www.php.net/"><img src="https://img.shields.io/badge/php-%3E%3D5.6-8892BF.svg" /></a>
<a href="https://github.com/RunnerLee/container"><img src="https://poser.pugx.org/runner/container/license" /></a>
</p>

### Installation
```
$ composer require runner/container
```

### Usage

create an instance of the container and define a class as service with an alias, and then you can get the service instance by alias

```php
use Runner\Container\Container;

$container = new Container();

$container->bind('stack', SplStack::class);

$container->make('stack');
```

use an interface name as alias and bind a concrete implementation to it

```php
class Demo extends ArrayObject
{
    public function __construct()
    {
        parent::__construct([], 0, 'ArrayIterator');
    }
}

$container = new Container();

$container->bind(ArrayAccess::class, Demo::class);

$container->make(ArrayAccess::class);

```

bind an alias as concrete to another alias

```php
$container->bind('stack', SplStack::class);

$container->bind('holy', 'stack');

$container->make('holy');
```

bind a closure as concrete into container

```php
$container->bind('stack', function () {
    return new SplStack;
});

$container->make('stack');
```

have fun :)





