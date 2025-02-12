<?php

declare(strict_types=1);

namespace MezzioTest\Helper;

use Mezzio\Helper\UrlHelper;
use Mezzio\Helper\UrlHelperMiddleware;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/** @covers \Mezzio\Helper\UrlHelperMiddleware */
final class UrlHelperMiddlewareTest extends TestCase
{
    /** @var UrlHelper&MockObject */
    private UrlHelper $helper;

    private UrlHelperMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->helper = $this->createMock(UrlHelper::class);

        $this->middleware = new UrlHelperMiddleware($this->helper);
    }

    public function testInvocationInjectsHelperWithRouteResultWhenPresentInRequest(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $routeResult = $this->createMock(RouteResult::class);
        $request     = $this->createMock(ServerRequestInterface::class);

        $request
            ->expects(self::once())
            ->method('getAttribute')
            ->with(RouteResult::class, false)
            ->willReturn($routeResult);

        $this->helper
            ->expects(self::once())
            ->method('setRouteResult')
            ->with($routeResult);

        $this->helper
            ->expects(self::once())
            ->method('setRequest')
            ->with($request);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        self::assertSame($response, $this->middleware->process($request, $handler));
    }

    public function testInvocationDoesNotInjectHelperWithRouteResultWhenAbsentInRequest(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);

        $request
            ->expects(self::once())
            ->method('getAttribute')
            ->with(RouteResult::class, false)
            ->willReturn(false);

        $this->helper
            ->expects(self::never())
            ->method('setRouteResult')
            ->with(self::anything());

        $this->helper
            ->expects(self::once())
            ->method('setRequest')
            ->with($request);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        self::assertSame($response, $this->middleware->process($request, $handler));
    }
}
