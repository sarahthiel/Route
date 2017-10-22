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

namespace sebastianthiel\Route\Adapter;

use sebastianthiel\Route\Exception\InvalidFormatException;
use sebastianthiel\Route\Exception\RouteException;
use sebastianthiel\Route\RouteInterface;

/**
 * Class StdAdapter
 *
 * the StdAdapter converts a Route object with all children into an array
 * which can be used by the stdResolver
 *
 * INPUT:
 * routes can be defined in the following format:
 *
 * /foo/bar - static route matches only /foo/bar
 *
 * Brackets:
 * brackets define optional parts of an uri. Optional parts always have to
 * include the complete uri until the end. Optional parts in between a uri
 * are not permitted
 * e.g. /foo[/bar[/baz]]
 * matches:
 * /foo, /foo/bar and /boo/bar/baz
 *
 * Named variables:
 * named variables start with an colon, followed by the name.
 * By default everything until the next slash will match. More
 * specific patterns can be defined in curly brackets. Pattern
 * are defined in PCRE. Grouping and curly brackets are not allowed
 * Slashes must be escaped
 *
 * e.g
 * /:foo/ - will match /bar/, /23/, /baz23/, etc.
 * /:foo{[0-9]}/ - will match /23/, but not /bar/ or /baz23/
 *
 * OUTPUT:
 * see StdResolver input requirements
 */
class StdAdapter implements RouteAdapterInterface
{
    /** @var string pattern, used for splitting a uri at the optional parts */
    protected $matchOptionals = '/\{[^\}]+\}(*SKIP)(*FAIL)|\[/';

    /** @var string pattern used to match variables and pattern definition */
    protected $matchVariables = '(\:([a-z]([a-z0-9-_]*[a-z0-9])*)(?:(\{([^\}]+)\})|))';

    /** @var string default match pattern, for variables */
    protected $variablesPattern = '[^\/]+';

    /** @var int maximum size of a chunk, used for route resolving */
    protected $maxChunkSize = 30;

    /** @var RouteInterface */
    protected $routes;

    /**
     * StdAdapter constructor.
     *
     * @param RouteInterface $routes
     */
    public function __construct(RouteInterface $routes)
    {
        $this->routes = $routes;
    }

    /**
     * returns an array, to be consumed by StdResolver
     *
     * @return array
     */
    public function convert() : array
    {
        $patternMap = [];
        /** @var RouteInterface[] $routes */
        $routes = $this->routes->getRoutes();

        foreach ($routes as $route) {
            $pattern = $this->resolveOptionals($route->getPattern());

            foreach ($pattern as $p) {
                list($regexp, $vars) = $this->resolveVariables($p);

                /**
                 * static routes without variables can be matched
                 * without using regular expressions
                 */
                if (count($vars) == 0) {
                    $regexp = $p;
                }
                $this->pushPattern($patternMap, $route->getMethods(), $regexp, $vars, $route->getAttributes());
            }
        }

        $this->createChunks($patternMap);

        return $patternMap;
    }

    /**
     * get all possible uris from a pattern
     *
     * @param string $pattern
     *
     * @return array
     * @throws InvalidFormatException
     */
    protected function resolveOptionals(string $pattern) : array
    {
        $parts = preg_split($this->matchOptionals, rtrim($pattern, ']'));

        $currentPath = '';
        $routes = [];

        foreach ($parts as $part) {
            if ($part == '') {
                continue;
            }

            //check for any closing bracket in the part
            if (preg_match('/\](?![^{]*})/', $part)) {
                throw new InvalidFormatException();
            }

            $currentPath .= $part;
            $routes[] = $currentPath;
        }

        return $routes;
    }

    /**
     * match all variables in a uri
     * and extract the name and pattern
     * specifications
     *
     * @param string $pattern
     *
     * @return array
     */
    protected function resolveVariables(string $pattern) : array
    {
        if (!preg_match_all($this->matchVariables, $pattern, $matches, PREG_PATTERN_ORDER)) {
            return [preg_quote($pattern, '/'), []];
        }

        $replaces = [];
        $search = [];
        $variables = [];

        foreach ($matches[4] as $key => $value) {
            $search[$key] = preg_quote($matches[0][$key], '/');
            $replaces[$key] = '(' . (($value == '') ? $this->variablesPattern : $value) . ')';
            $variables[$key + 1] = $matches[1][$key];
        }

        $pattern = str_replace($search, $replaces, preg_quote($pattern, '/'));

        return [$pattern, $variables];
    }

    /**
     * add a pattern and metadata to the resulting array
     * in the desired structure
     *
     * @param array  $map
     * @param array  $methods
     * @param string $regexp
     * @param array  $vars
     * @param array  $attributes
     *
     * @return array
     * @throws RouteException
     */
    protected function pushPattern(array &$map, array $methods, string $regexp, array $vars, array $attributes) : array
    {
        foreach ($methods as $method) {
            if (!isset($map[$method])) {
                $map[$method] = [[], []];
            }
            if (count($vars) == 0) {
                if (isset($map[$method][0][$regexp])) {
                    throw new RouteException(sprintf('Route "%s" exists', $regexp));
                }

                $map[$method][0][$regexp] = $attributes;
                continue;
            }
            $map[$method][1][] = [$regexp, $vars, $attributes];
        }

        return $map;
    }

    /**
     * create smaller chunks of the variable routes.
     *
     * @param array $map
     */
    protected function createChunks(array &$map)
    {
        foreach ($map as &$method) {
            if (count($method[1]) == 0) {
                return;
            }
            $chunkSize = (int) ceil(count($method[1]) / ceil(count($method[1]) / $this->maxChunkSize));
            $chunks = array_chunk($method[1], $chunkSize);
            $method[1] = array_map([$this, 'mapRouteData'], $chunks);
        }
    }

    /**
     * map the route data to a group count,
     * to be able to determine which route
     * matched in the resolving process
     *
     * @param array $chunk
     *
     * @return array
     */
    protected function mapRouteData(array $chunk) : array
    {
        $groupCount = 1;
        $tmpRegEx = [];
        $map = [];

        foreach ($chunk as $route) {
            $varCount = count($route[1]);
            $groupCount = max($groupCount, $varCount);

            $tmpRegEx[] = $route[0] . str_repeat('()', $groupCount - $varCount);
            ++$groupCount;
            $map[$groupCount] = [$route[1], $route[2]];
        }

        $regex = '/^(?|' . implode('|', $tmpRegEx) . ')$/';

        return [$regex, $map];
    }
}
