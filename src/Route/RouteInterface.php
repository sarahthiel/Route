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

namespace sebastianthiel\Route;

/**
 * Interface RouteInterface
 */
interface RouteInterface
{
    /** list of al default methods, a route is valid for if none was provided */
    const METHODS = ['CONNECT', 'DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT', 'TRACE'];

    /**
     * return the routes pattern
     *
     * @return string
     */
    public function __toString();

    /**
     * @return array
     */
    public function getMethods() : array;

    /**
     * @return string
     */
    public function getPattern() : string;

    /**
     * @return array
     */
    public function getAttributes() : array;

    /**
     * @return array
     */
    public function getRoutes() : array;

    /**
     * @param        $methods
     * @param string $path
     *
     * @return RouteInterface
     */
    public function map($methods, string $path) : RouteInterface;
}
