<?php
/**
 * sebastianthiel Route
 *
 * @package    sebastianthiel/Router
 * @author     Sebastian Thiel <me@sebastian-thiel.eu>
 * @license    https://opensource.org/licenses/MIT  MIT
 * @version    0.1
 */

declare(strict_types=1);

namespace sebastianthiel\Route\Resolver;

use sebastianthiel\Route\Adapter\RouteAdapterInterface;

/**
 * Interface RouteResolverInterface
 */
interface RouteResolverInterface
{
    /**
     * RouteResolverInterface constructor.
     *
     * @param RouteAdapterInterface $routes
     */
    public function __construct(RouteAdapterInterface $routes);

    /**
     * matches the given uri against all routes
     * returns the routes attributes
     *
     * @param string $method
     * @param string $uri
     *
     * @return array|null
     */
    public function resolve(string $method, string $uri) : ?array;
}