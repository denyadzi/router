<?php

namespace Yiisoft\Router\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Router\Dispatcher;
use Yiisoft\Di\Container;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;

class DispatcherTest extends TestCase
{
    private ContainerInterface $container;
    
    public function setUp(): void
    {
        $this->container = new Container([
            ContainerInterface::class => fn($container) => $container,
        ]);
    }
    
    public function testWithMiddlewares_shouldAffectFlow()
    {
        $middleware200 = $this->createMock(MiddlewareInterface::class);
        $middleware200
            ->method('process')
            ->willReturn(new Response(200));
        $middleware400 = $this->createMock(MiddlewareInterface::class);
        $middleware400
            ->method('process')
            ->willReturn(new Response(400));

        $request = new ServerRequest('GET', '/');
        $fallbackHandler = $this->createMock(RequestHandlerInterface::class);

        $dispatcher = $this->container->get(Dispatcher::class)->withMiddlewares([$middleware200]);
        $response200 = $dispatcher->dispatch($request, $fallbackHandler);
        $dispatcher = $dispatcher->withMiddlewares([$middleware400]);
        $response400 = $dispatcher->dispatch($request, $fallbackHandler);

        $this->assertEquals(200, $response200->getStatusCode());
        $this->assertEquals(400, $response400->getStatusCode());
    }
}
