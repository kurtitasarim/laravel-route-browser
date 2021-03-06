<?php

namespace DaveJamesMiller\RouteBrowser;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;

class RouteListController
{
    public function __invoke(Request $request, Router $router)
    {
        $collection = $router->getRoutes();
        $routes = $collection->getRoutes();

        // Based on RouteCollection::matchAgainstRoutes() - sort fallback routes to the end
        [$fallbacks, $routes] = collect($routes)->partition('isFallback');

        /** @var Collection $routes */
        $routes = $routes
            ->merge($fallbacks)
            ->map(static function (Route $route) use ($router, $request) {
                return new RoutePresenter($route, $router, $request);
            })
            ->filter(static function (RoutePresenter $route) use ($request) {
                return $route->matches($request['method'], $request['uri']);
            });

        return view('route-browser::list', compact('routes'));
    }
}
