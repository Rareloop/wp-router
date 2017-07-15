# Rare WordPress Router
![CI](https://travis-ci.org/Rareloop/wp-router.svg?branch=master)

A WordPress wrapper around the [Rareloop PHP Router](https://github.com/rareloop/router). Easily handle custom endpoints on your WordPress site with this plugin.

## Installation

Although not a requirement, using Composer and a setup like [Bedrock](https://roots.io/bedrock/) is the recommended installation method.

```
composer require rareloop/wp-router
```

## Usage

### Creating Routes

#### Map

Creating a route is done using the `map` function:

```php
use Rareloop\WordPress\Router\Router;

// Creates a route that matches the uri `/posts/list` both GET 
// and POST requests. 
Router::map(['GET', 'POST'], 'posts/list', function () {
    return 'Hello World';
});
```

`map()` takes 3 parameters:

- `methods` (array): list of matching request methods, valid values:
    + `GET`
    + `POST`
    + `PUT`
    + `PATCH`
    + `DELETE`
    + `OPTIONS`
- `uri` (string): The URI to match against
- `action`  (function|string): Either a closure or a Controller string

#### Route Parameters
Parameters can be defined on routes using the `{keyName}` syntax. When a route matches that contains parameters, an instance of the `RouteParams` object is passed to the action.

```php
Router::map(['GET'], 'posts/{id}', function(RouteParams $params) {
    return $params->id;
});
```

#### Named Routes
Routes can be named so that their URL can be generated programatically:

```php
Router::map(['GET'], 'posts/all', function () {})->name('posts.index');

$url = Router::url('posts.index');
```

If the route requires parameters you can be pass an associative array as a second parameter:

```php
Router::map(['GET'], 'posts/{id}', function () {})->name('posts.show');

$url = Router::url('posts.show', ['id' => 123]);
```

#### HTTP Verb Shortcuts
Typically you only need to allow one HTTP verb for a route, for these cases the following shortcuts can be used:

```php
Router::get('test/route', function () {});
Router::post('test/route', function () {});
Router::put('test/route', function () {});
Router::patch('test/route', function () {});
Router::delete('test/route', function () {});
Router::options('test/route', function () {});
```

#### Setting the basepath
The router assumes you're working from the route of a domain. If this is not the case you can set the base path:

```php
Router::setBasePath('base/path');
Router::map(['GET'], 'route/uri', function () {}); // `/base/path/route/uri`
```

#### Controllers
If you'd rather use a class to group related route actions together you can pass a Controller String to `map()` instead of a closure. The string takes the format `{name of class}@{name of method}`. It is important that you use the complete namespace with the class name.

Example:

```php
// TestController.php
namespace \MyNamespace;

class TestController
{
    public function testMethod()
    {
        return 'Hello World';
    }
}

// routes.php
Router::map(['GET'], 'route/uri', '\MyNamespace\TestController@testMethod');
```

### Creating Groups
It is common to group similar routes behind a common prefix. This can be achieved using Route Groups:

```php
Router::group('prefix', function ($group) {
    $group->map(['GET'], 'route1', function () {}); // `/prefix/route1`
    $group->map(['GET'], 'route2', function () {}); // `/prefix/route2§`
});
```
