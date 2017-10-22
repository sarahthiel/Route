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
 * The resolver finds a matching route based on the
 * Group Count Based approach, described by Nikita Popov
 *
 * @link http://nikic.github.io/2014/02/18/Fast-request-routing-using-regular-expressions.html
 */
class StdResolver implements RouteResolverInterface
{
    /** @var array */
    protected $patternMap;

    /**
     * StdResolver constructor.
     *
     * @param RouteAdapterInterface $adapter
     */
    public function __construct(RouteAdapterInterface $adapter)
    {
        $this->patternMap = $adapter->convert();
    }

    /**
     * @param string $method
     * @param string $uri
     *
     * @return null|array
     */
    public function resolve(string $method, string $uri) : ?array
    {
        $map = &$this->patternMap;
        if (!isset($map[$method])) {
            return null;
        }

        $map = &$map[$method];
        if (isset($map[0][$uri])) {
            return $map[0][$uri];
        }

        $map = &$map[1];
        foreach ($map as $routeData) {
            if (!preg_match($routeData[0], $uri, $matches)) {
                continue;
            }

            $data = $routeData[1][count($matches)];

            $vars = $data[0];
            $attributes = $data[1];
            foreach ($vars as $key => $name) {
                $attributes[$name] = $matches[$key];
            }

            return $attributes;
        }

        return null;
    }
}
