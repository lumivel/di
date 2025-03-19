<?php

namespace Lumivel\Di\Test;

use Lumivel\Di\Concerns\ServiceProvider\AbstractProvider;
use Lumivel\Di\Container;
use Lumivel\Di\Contracts\{Container as ContainerContract, Definition as DefinitionContract};
use Lumivel\Di\Definition;
use Lumivel\Di\Delegates\AutowireResolver;
use Lumivel\Di\Delegates\ParameterResolver;
use Lumivel\Di\Test\Src\Bar;
use Lumivel\Di\Test\Src\Foo;
use Lumivel\Di\Test\Src\FooBar;
use Lumivel\Di\Test\Src\IFoo;
use Lumivel\Di\Test\Src\ImplementServiceProvider;
use Lumivel\Di\Throws\ContainerException;
use Lumivel\Di\Throws\NotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Container::class)]
#[TestDox('Container')]
#[UsesClass(AutowireResolver::class)]
#[UsesClass(ParameterResolver::class)]
#[UsesClass(Definition::class)]
class ContainerTest extends TestCase
{
    #[TestDox('It should adds and gets')]
    public function testItShouldAddsAndGets(): void
    {
        $container = new Container();
        $container->add(Foo::class);
        self::assertInstanceOf(ContainerContract::class, $container);
        self::assertInstanceOf(DefinitionContract::class, $container->get(Foo::class));
        self::assertInstanceOf(Definition::class, $container->get(Foo::class));
    }

    #[TestDox('It should adds and resolves')]
    public function testItShouldAddsAndResolves(): void
    {
        $container = new Container();
        $container->add(Foo::class);

        self::assertTrue($container->has(Foo::class));
        self::assertFalse($container->has(Bar::class));
        self::assertInstanceOf(Foo::class, $container->resolve(Foo::class));
    }

    #[TestDox('It should adds and resolves with closure')]
    public function testItShouldAddsAndResolvesWithClosure(): void
    {
        $container = new Container();
        $container->add(IFoo::class, fn () => new Foo);

        self::assertInstanceOf(Foo::class, $container->resolve(IFoo::class));
    }

    #[TestDox('It should adds and resolves dependencies recursively')]
    public function testItShouldAddsAndResolvesDependenciesRecursively(): void
    {
        $container = new Container();
        $container->add(Foo::class)->addArgument(Bar::class);
        $container->add(Bar::class)->addArgument(FooBar::class);
        $container->add(FooBar::class);

        $foo = $container->resolve(Foo::class);
        self::assertInstanceOf(Foo::class, $foo);
        self::assertInstanceOf(Bar::class, $foo->bar);
        self::assertInstanceOf(FooBar::class, $foo->bar->fooBar);
    }

    #[TestDox('It should adds and resolves shared')]
    public function testItShouldAddsAndResolvesShared(): void
    {
        $container = new Container();

        $container->addShared(Foo::class);
        $foo = $container->resolve(Foo::class);
        $fooAnother = $container->resolve(Foo::class);
        self::assertSame($foo, $fooAnother);
        $fooNew = $container->resolve(Foo::class, true);
        self::assertNotSame($foo, $fooNew);

        $container->add(Bar::class);
        $bar = $container->resolve(Bar::class);
        $barAnother = $container->resolve(Bar::class);
        self::assertNotSame($bar, $barAnother);
    }

    #[TestDox('It should throws exception when service not found')]
    public function testItShouldThrowsExceptionWhenServiceNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $container = new Container();
        $container->resolve(Foo::class);
    }

    #[TestDox('It should add and get with service provider')]
    public function testItShouldAddAndGetWithServiceProvider(): void
    {
        $container = new Container();
        $provider = new class() extends AbstractProvider {
            public function canProvides(string $identifier): bool
            {
                return in_array($identifier, [Foo::class, FooBar::class, Bar::class]);
            }

            public function register(): void
            {
                $container = $this->getContainer();
                $container->add(Foo::class);
                $container->add(FooBar::class);
            }
        };

        $container->addProvider($provider);
        self::assertTrue($container->has(Foo::class));
        self::assertTrue($container->has(FooBar::class));
        self::assertInstanceOf(FooBar::class, $container->resolve(FooBar::class));
    }

    #[TestDox('It should throws exception when service provider lied')]
    public function testItShouldThrowsExceptionWhenServiceProviderLied(): void
    {
        $container = new Container();
        $provider = new class() extends AbstractProvider {
            public function canProvides(string $identifier): bool
            {
                return in_array($identifier, [Foo::class, Bar::class]);
            }

            public function register(): void
            {
                // This provider did not register services it says it will provide,
                // so it lied to provide them.
            }
        };

        $this->expectException(ContainerException::class);

        $container->addProvider($provider);
        self::assertTrue($container->has(Foo::class));
        $container->resolve(Foo::class);
        self::assertTrue($container->has(Bar::class));
        $container->resolve(Bar::class);
        self::assertFalse($container->has(FooBar::class));
    }

    #[TestDox('It should resolve with delegate')]
    public function testItShouldResolveWithDelegate(): void
    {
        $container = new Container();
        $parameterDelegate = new ParameterResolver;
        $parameterDelegate->addParameter('app.name', 'Lumivel DI');
        $container->addDelegate($parameterDelegate);

        self::assertTrue($container->has('app.name'));
        self::assertTrue($container->get('app.name') === 'Lumivel DI');
    }

    #[TestDox('It should resolve with autowire resolver delegate')]
    public function testItShouldResolveWithAutowireResolverDelegate(): void
    {
        $container = new Container();

        self::assertFalse($container->has(Foo::class));
        $container->addDelegate(new AutowireResolver);
        self::assertTrue($container->has(Foo::class));
        $container->resolve(Foo::class);
    }

    #[TestDox('It should add and allow to modify definition')]
    public function testItShouldAddAndAllowToModifyDefinition(): void
    {
        $container = new Container();
        $container->add(FooBar::class);
        $fooFirstResolve = $container->resolve(FooBar::class);
        $fooSecondResolve = $container->resolve(FooBar::class);

        self::assertNotSame($fooFirstResolve, $fooSecondResolve);
        $definition = $container->modify(FooBar::class);
        $definition->setShared();

        $fooFirstSharedResolve = $container->resolve(FooBar::class);
        $fooSecondSharedResolve = $container->resolve(FooBar::class);
        self::assertSame($fooFirstSharedResolve, $fooSecondSharedResolve);
    }

    #[TestDox('It should add delegate container and inject it')]
    public function testItShouldAddDelegateContainerAndInjectIt(): void
    {
        $container = new Container();
        $parameterSignature = $container->addDelegate(new ParameterResolver, true);
        $container->injectInstance($parameterSignature, 'getParameterDelegate');
        $parameterDelegate = $container->getParameterDelegate();

        self::assertFalse($container->has('app.name'));
        $parameterDelegate->addParameter('app.name', 'Lumivel DI');
        self::assertTrue($container->has('app.name'));
        self::assertTrue($container->get('app.name') === 'Lumivel DI');
    }

    #[TestDox('It should add delegate container and inject its method')]
    public function testItShouldAddDelegateContainerAndInjectItsMethod(): void
    {
        $container = new Container();
        $parameterSignature = $container->addDelegate(new ParameterResolver, true);
        $container->injectMethod($parameterSignature, 'addParameter');

        self::assertFalse($container->has('app.name'));
        $container->addParameter('app.name', 'Lumivel DI');
        self::assertTrue($container->has('app.name'));
        self::assertTrue($container->get('app.name') === 'Lumivel DI');
    }

    #[TestDox('It should add provider and inject it')]
    public function testItShouldAddProviderAndInjectIt(): void
    {

        $container = new Container();
        $providerSignature = $container->addDelegate(new ParameterResolver, true);
        $providerSignature = $container->addProvider(new ImplementServiceProvider, true);
        $container->injectInstance($providerSignature, 'getImplementServiceProvider');
        $implementServiceProviderProvider = $container->getImplementServiceProvider();

        self::assertInstanceOf(ImplementServiceProvider::class, $implementServiceProviderProvider);
    }

    #[TestDox('It should add provider and inject it method')]
    public function testItShouldAddProviderAndInjectItMethod(): void
    {

        $container = new Container();
        $providerSignature = $container->addDelegate(new ParameterResolver, true);
        $providerSignature = $container->addProvider(new ImplementServiceProvider, true);
        $container->injectMethod($providerSignature, 'injectIt');
        $injectItReturned = $container->injectIt();

        self::assertEquals('injected', $injectItReturned);
    }
}
