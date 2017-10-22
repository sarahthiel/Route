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
use sebastianthiel\Route\Route;

/**
 * Class AdapterTest
 */
class AdapterTest extends Unit
{
    /**
     *
     */
    public function testStaticRoute()
    {
        $expected = [
            'GET' => [
                [
                    '/foo/bar/baz' => ['qux' => 'quux']
                ],
                []
            ]
        ];


        $route = new Route(['GET'], '/foo/bar/baz', ['qux' => 'quux']);
        $adapter = new StdAdapter($route);

        $this->assertEquals($expected, $adapter->convert());
    }

    /**
     *
     */
    public function testOptionals()
    {
        $expected = [
            'GET' => [
                [
                    '/foo'         => ['qux' => 'quux'],
                    '/foo/bar'     => ['qux' => 'quux'],
                    '/foo/bar/baz' => ['qux' => 'quux']
                ],
                []
            ]
        ];


        $route = new Route(['GET'], '/foo[/bar[/baz]]', ['qux' => 'quux']);
        $adapter = new StdAdapter($route);

        $this->assertEquals($expected, $adapter->convert());
    }

    /**
     *
     */
    public function testPlaceholder()
    {
        $expected = [
            'GET' => [
                0 => [], //static routes
                1 => [  //dynamic routes
                        0 => [  //first chunk
                                0 => '/^(?|\\/foo\\/([^\\/]+)\\/([0-9]+))$/',   //regexp
                                1 => [  //params
                                        3 => [  // capture group count
                                                0 => [  //named parameter
                                                        1 => 'bar',
                                                        2 => 'baz',
                                                ],
                                                1 => [  //route attributes
                                                        'qux' => 'quux',
                                                ],
                                        ],
                                ],
                        ],
                ],
            ],
        ];

        $route = new Route(['GET'], '/foo/:bar/:baz{[0-9]+}', ['qux' => 'quux']);
        $adapter = new StdAdapter($route);
        var_export($adapter->convert());
        $this->assertEquals($expected, $adapter->convert());
    }

    /**
     * @return array
     */
    public function uglyRoutesProvider()
    {
        return [
            ['/foo//bar'],
            ['/foo/[]'],
            ['/foo[/bar[/baz]']
        ];
    }

    /**
     * @dataProvider uglyRoutesProvider
     *
     * @param $pattern
     */
    public function testUglyRoutes($pattern)
    {
        $route = new Route(['GET'], $pattern);
        $adapter = new StdAdapter($route);
        $adapter->convert();
    }

    public function badRoutesProvider()
    {
        return [
            ['/foo[/bar]/baz']
        ];
    }


    /**
     * @dataProvider badRoutesProvider
     *
     * @expectedException \sebastianthiel\Route\Exception\InvalidFormatException
     *
     * @param $pattern
     */
    public function testBadRoutes($pattern)
    {
        $route = new Route(['GET'], $pattern);
        $adapter = new StdAdapter($route);
        $adapter->convert();
    }

    /**
     * @expectedException \sebastianthiel\Route\Exception\RouteException
     */
    public function testDoubleRoute()
    {
        $route = new Route();
        $route->map(['GET'],'/foo');
        $route->map(['GET'],'/foo');
        $adapter = new StdAdapter($route);
        $adapter->convert();
    }
}
