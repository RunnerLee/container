<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-04
 */
require __DIR__.'/fixtures/AlphaClass.php';

use Runner\Container\Container;

class ContainerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    protected $container;

    public function setUp()
    {
        $this->container = new Container();
    }

    public function testClassName()
    {
        $this->container->bind(SplStack::class);
        $this->assertInstanceOf(SplStack::class, $this->container->make(SplStack::class));
    }

    public function testInstanceShouldBeDifferent()
    {
        $this->container->bind(SplStack::class);

        $this->assertNotSame(
            $this->container->make(SplStack::class),
            $this->container->make(SplStack::class)
        );

        $this->container->bind('alpha', SplStack::class, true);
        $this->container->bind('beta', 'alpha');

        $a = $this->container->make('alpha');
        $b = $this->container->make('beta');
        $c = $this->container->make(SplStack::class);

        $this->assertSame($a, $b);
        $this->assertSame($b, $this->container->make('beta'));
        $this->assertSame($b, $this->container->make('alpha'));
        $this->assertNotSame($a, $c);
    }

    public function testInstanceShouldBeSame()
    {
        $this->container->bind(SplStack::class, null, true);

        $this->assertSame(
            $this->container->make(SplStack::class),
            $this->container->make(SplStack::class)
        );
    }

    public function testClosure()
    {
        $this->container->bind('testing', function () {
            return new SplStack();
        });
        $this->container->bind('testing_stack', function (Container $container) {
            return $container->make(SplStack::class);
        });

        $this->assertInstanceOf(SplStack::class, $this->container->make('testing'));
        $this->assertInstanceOf(SplStack::class, $this->container->make('testing_stack'));
    }

    public function testAlias()
    {
        $this->container->bind('testing', function (Container $container) {
            return $container->make(ArrayAccess::class);
        });
        $this->container->bind(ArrayAccess::class, function () {
            return new ArrayObject();
        });

        $this->assertInstanceOf(ArrayObject::class, $this->container->make('testing'));
    }

    public function testOffsetGet()
    {
        $this->container->bind(SplStack::class);
        $this->assertInstanceOf(SplStack::class, $this->container[SplStack::class]);
    }

    public function testOffsetExists()
    {
        $this->assertFalse(isset($this->container[SplStack::class]));
        $this->container->bind(SplStack::class);
        $this->assertTrue(isset($this->container[SplStack::class]));
    }

    public function testOffsetSet()
    {
        $this->container['testing'] = $stack = new SplStack();

        $this->assertSame($stack, $this->container->make('testing'));
    }

    public function testOffsetUnset()
    {
        $this->container['testing'] = $stack = new SplStack();

        $this->assertTrue(isset($this->container['testing']));

        unset($this->container['testing']);

        $this->assertFalse(isset($this->container['testing']));
    }

    public function testInjection()
    {
        $object = new ArrayObject([
            'foo' => 'bar',
        ]);

        $this->container->bind(ArrayAccess::class, function () use ($object) {
            return $object;
        });

        $alpha = $this->container->make(AlphaClass::class);

        $this->assertSame($object, $alpha->getObject());
        $this->assertInstanceOf(SplStack::class, $alpha->getStack());
    }
}
