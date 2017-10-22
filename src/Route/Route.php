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
 * Class Route
 */
class Route implements RouteInterface
{
    /** @var array valid methods for this route */
    protected $methods;

    /** @var string route pattern */
    protected $pattern;

    /** @var Route parent route */
    protected $parent;

    /** @var static[] child routes */
    protected $routes;

    /** @var array additional attributes */
    protected $attributes;

    /**
     * Route constructor.
     *
     * @param            $methods
     * @param string     $pattern
     * @param array      $attributes
     * @param Route|null $parent
     */
    public function __construct(array $methods = [], string $pattern = '/', array $attributes = [], Route $parent = null)
    {
        $this->methods = (count($methods) > 0) ? array_intersect($methods, RouteInterface::METHODS) : RouteInterface::METHODS;
        $this->attributes = $attributes;
        $this->parent = $parent;
        $this->routes = [];

        $this->setPattern($pattern);
    }

    /**
     * return the routes pattern
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPattern();
    }

    /**
     * @param $pattern
     */
    protected function setPattern($pattern)
    {
        $pattern = strtolower($pattern);
        $this->pattern = '/' . ltrim($pattern, '/');
    }

    /**
     * @return array
     */
    public function getMethods() : array
    {
        return $this->methods;
    }

    /**
     * get the routes pattern
     * pattern from the parent route will
     * be prepended
     *
     * @return string
     */
    public function getPattern() : string
    {
        return (
               ($this->parent)
                   ? rtrim($this->parent->getPattern(), '/')
                   : ''
               ) . $this->pattern;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @param $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $this->mergeArray($this->attributes, $attributes);
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function getAttribute($key)
    {
        return $this->attributes[$key]
               ?? (
               !empty($this->parent)
                   ? $this->parent->getAttribute($key)
                   : null);
    }

    /**
     * @return array
     */
    public function getAttributes() : array
    {
        return (!empty($this->parent))
            ? $this->mergeArray($this->parent->getAttributes(), $this->attributes)
            : $this->attributes;
    }

    /**
     * @param array $methods
     *
     * @return array
     */
    public function getRoutes(array $methods = []) : array
    {
        /**
         * add this route to the list if it
         * has any attributes
         * and the it is valid for at least one
         * of the requested methods
         */
        $validMethods = (count($methods) > 0) ? array_intersect($methods, $this->methods) : $this->methods;
        $routes = (count($validMethods) > 0 && count($this->routes) == 0) ? [$this] : [];

        if (count($this->routes) == 0) {
            return $routes;
        }

        /** @var static $route */
        foreach ($this->routes as $route) {
            $routes = array_merge($routes, $route->getRoutes($methods));
        }

        return $routes;
    }

    /**
     * add a route as children
     *
     * @param RouteInterface $route
     *
     * @return RouteInterface
     */
    public function addRoute(RouteInterface $route) : RouteInterface
    {
        $this->routes[] = $route;

        return $route;
    }

    /**
     * create a new route and add it as children
     *
     * @param        $methods
     * @param string $path
     * @param array  $attributes
     *
     * @return RouteInterface
     */
    public function map($methods, string $path, array $attributes = []) : RouteInterface
    {
        return $this->addRoute(new static((array) $methods, $path, $attributes, $this));
    }

    protected function mergeArray($old, $new)
    {
        if (!is_array($old)) {
            return $new;
        }

        foreach ($new as $key => $value) {
            if (!isset($old[$key])) {
                $old[$key] = $value;
                continue;
            }

            $old[$key] = $this->mergeArray($old[$key], $new[$key]);
        }

        return $old;
    }

}
