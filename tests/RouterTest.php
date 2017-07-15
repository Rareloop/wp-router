<?php

namespace Rareloop\WordPress\Router\Test;

use PHPUnit\Framework\TestCase;
use Rareloop\Router\Route;
use Rareloop\Router\RouteGroup;
use Rareloop\Router\Router as RareRouter;
use Rareloop\WordPress\Router\Test\FakeRouter as Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Brain\Monkey;
use Brain\Monkey\Filters;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;

class RouterTest extends TestCase
{
    public function setUp() {
        parent::setUp();
        Router::reset();
        Monkey\setUp();
    }

    public function tearDown() {
        parent::tearDown();
        Monkey\tearDown();
    }

    private function setSiteUrl($url) {
        Functions\when('get_bloginfo')->alias(function ($key) use ($url) {
            if ($key === 'url') {
                return $url;
            }
        });
    }

    /** @test */
    public function map_returns_a_route_object()
    {
        $this->setSiteUrl('http://example.com/');
        Router::init();

        $route = Router::map(['GET'], '/test/123', function () {});

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['GET'], $route->getMethods());
        $this->assertSame('/test/123', $route->getUri());
    }

    /** @test */
    public function get_returns_a_route_object()
    {
        $this->setSiteUrl('http://example.com/');
        Router::init();

        $route = Router::get('/test/123', function () {});

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['GET'], $route->getMethods());
        $this->assertSame('/test/123', $route->getUri());
    }

    /** @test */
    public function post_returns_a_route_object()
    {
        $this->setSiteUrl('http://example.com/');
        Router::init();

        $route = Router::post('/test/123', function () {});

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['POST'], $route->getMethods());
        $this->assertSame('/test/123', $route->getUri());
    }

    /** @test */
    public function patch_returns_a_route_object()
    {
        $this->setSiteUrl('http://example.com/');
        Router::init();

        $route = Router::patch('/test/123', function () {});

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['PATCH'], $route->getMethods());
        $this->assertSame('/test/123', $route->getUri());
    }

    /** @test */
    public function put_returns_a_route_object()
    {
        $this->setSiteUrl('http://example.com/');
        Router::init();

        $route = Router::put('/test/123', function () {});

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['PUT'], $route->getMethods());
        $this->assertSame('/test/123', $route->getUri());
    }

    /** @test */
    public function delete_returns_a_route_object()
    {
        $this->setSiteUrl('http://example.com/');
        Router::init();

        $route = Router::delete('/test/123', function () {});

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['DELETE'], $route->getMethods());
        $this->assertSame('/test/123', $route->getUri());
    }

    /** @test */
    public function options_returns_a_route_object()
    {
        $this->setSiteUrl('http://example.com/');
        Router::init();

        $route = Router::options('/test/123', function () {});

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['OPTIONS'], $route->getMethods());
        $this->assertSame('/test/123', $route->getUri());
    }

    /** @test */
    public function match_returns_a_response_object()
    {
        $this->setSiteUrl('http://example.com/');
        Router::init();

        $request = Request::create('/test/123', 'GET');
        $count = 0;

        $route = Router::get('/test/123', function () use (&$count) {
            $count++;

            return 'abc123';
        });
        $response = Router::match($request);

        $this->assertSame(1, $count);
        $this->assertInstanceOf(Response::class, $response);
    }

    /** @test */
    public function basepath_is_correctly_set_from_wordpress_url()
    {
        $this->setSiteUrl('http://example.com/sub-path/');
        $request = Request::create('/sub-path/test/123', 'GET');
        Router::init();

        $route = Router::get('/test/123', function () {
            return 'abc123';
        });

        $response = Router::match($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('abc123', $response->getContent());
    }

    /** @test */
    public function basepath_is_correctly_set_from_wordpress_url_when_no_trailing_slash()
    {
        $this->setSiteUrl('http://example.com/sub-path');
        $request = Request::create('/sub-path/test/123', 'GET');
        Router::init();

        $route = Router::get('/test/123', function () {
            return 'abc123';
        });

        $response = Router::match($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('abc123', $response->getContent());
    }

    /** @test */
    public function can_add_routes_in_a_group()
    {
        $request = Request::create('/prefix/all', 'GET');
        $count = 0;

        Router::group('prefix', function ($group) use (&$count) {
            $count++;
            $this->assertInstanceOf(RouteGroup::class, $group);

            $group->get('all', function () {
                return 'abc123';
            });
        });
        $response = Router::match($request);

        $this->assertSame(1, $count);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('abc123', $response->getContent());
    }

    /** @test */
    public function can_generate_canonical_uri_with_trailing_slash_for_named_route()
    {
        $route = Router::get('/posts/all', function () {})->name('test.name');

        $this->assertSame('/posts/all/', Router::url('test.name'));
    }

    /** @test */
    public function filter_is_fired_when_router_is_created()
    {
        $this->setSiteUrl('http://example.com/sub-path');

        Filters\expectApplied('rareloop_router_created')
            ->once()
            ->with(\Mockery::type(RareRouter::class));

        Router::init();
    }

    /** @test */
    public function filter_is_fired_when_router_is_configured()
    {
        $this->setSiteUrl('http://example.com/sub-path');

        Filters\expectApplied('rareloop_router_configured')
            ->once()
            ->with(\Mockery::type(RareRouter::class));

        Router::init();
    }

    /** @test */
    public function router_will_match_request_when_wp_loaded_is_fired()
    {
        $this->setSiteUrl('http://example.com/');
        Actions\expectAdded('wp_loaded')->with([Router::class, 'processRequest'])->once();

        Router::init();
    }

    /** @test */
    public function response_will_be_sent_when_a_route_matches()
    {
        $this->setSiteUrl('http://example.com/');
        $count = 0;

        // Create a mock response that the router will return
        $mockResponse = \Mockery::mock('Symfony\Component\HttpFoundation\Response')->makePartial();
        $mockResponse->setStatusCode(200);
        $mockResponse->shouldReceive('send')->times(1);

        // Use a mock instead of the real router
        $mockRouter = \Mockery::mock('Rareloop\Router\Router')->makePartial();
        $mockRouter->shouldReceive('match')->times(1)->andReturn($mockResponse);
        Filters\expectApplied('rareloop_router_configured')
            ->once()
            ->andReturn($mockRouter);

        // Provide a callback to test whether shutdown is called
        Router::setShutdownCallback(function () use (&$count) {
            $count++;
        });
        Router::init();
        Router::processRequest();

        $this->assertSame(1, $count);
    }

    /** @test */
    public function response_will_be_ignored_when_a_route_does_not_match()
    {
        $this->setSiteUrl('http://example.com/');
        $count = 0;

        // Create a mock response that the router will return
        $mockResponse = \Mockery::mock('Symfony\Component\HttpFoundation\Response')->makePartial();
        $mockResponse->setStatusCode(404);
        $mockResponse->shouldReceive('send')->times(0);

        // Use a mock instead of the real router
        $mockRouter = \Mockery::mock('Rareloop\Router\Router')->makePartial();
        $mockRouter->shouldReceive('match')->times(1)->andReturn($mockResponse);
        Filters\expectApplied('rareloop_router_configured')
            ->once()
            ->andReturn($mockRouter);

        // Provide a callback to test whether shutdown is called
        Router::setShutdownCallback(function () use (&$count) {
            $count++;
        });
        Router::init();
        Router::processRequest();

        $this->assertSame(0, $count);
    }
}
