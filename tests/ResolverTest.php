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

namespace sebastianthiel\Route\Tests;

use Codeception\Test\Unit;
use sebastianthiel\Route\Adapter\StdAdapter;
use sebastianthiel\Route\Resolver\StdResolver;
use sebastianthiel\Route\Route;

/**
 * Class ResolverTest
 */
class ResolverTest extends Unit
{
    /**
     * @return array
     */
    public function routeProvider()
    {
        return [
            [
                new Route(['GET'], '/foo/bar', ['bar' => 'qux']),
                'GET',
                '/foo/bar',
                ['bar' => 'qux']
            ], [
                new Route(['GET'], '/foo/:bar', ['bar' => 'qux']),
                'GET',
                '/foo/bar',
                ['bar' => 'bar']
            ], [
                new Route(['GET'], '/foo/:bar', ['bar' => 'qux']),
                'GET',
                '/foo/10',
                ['bar' => 10]
            ], [
                new Route(['GET'], '/foo/bar', ['bar' => 'qux']),
                'GET',
                '/foo/quux',
                null
            ], [
                new Route(['GET'], '/foo/:bar{[0-9]+}', ['bar' => 'qux']),
                'GET',
                '/foo/10',
                ['bar' => 10]
            ], [
                new Route(['GET'], '/foo/:bar{[0-9]+}', ['bar' => 'qux']),
                'GET',
                '/foo/quux',
                null
            ], [
                new Route(['GET'], '/foo/bar', ['bar' => 'qux']),
                'POST',
                '/foo/bar',
                null
            ]
        ];
    }

    /**
     * @dataProvider routeProvider
     *
     * @param $route
     * @param $method
     * @param $uri
     * @param $expected
     */
    public function testResolving($route, $method, $uri, $expected)
    {
        $resolver = new StdResolver(new StdAdapter($route));
        $this->assertEquals($expected, $resolver->resolve($method, $uri));
    }
}
