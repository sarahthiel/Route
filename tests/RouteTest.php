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
use sebastianthiel\Route\Route;

/**
 * Class RouteTest
 */
class RouteTest extends Unit
{
    /**
     *
     */
    public function testConstructor()
    {
        $route = new Route(['GET'], 'foo/', ['bar' => 'baz'], null);

        $this->assertEquals(['GET'], $route->getMethods());
        $this->assertEquals('/foo/', $route->getPattern());
        $this->assertEquals(['bar' => 'baz'], $route->getAttributes());
    }

    /**
     *
     */
    public function testToString()
    {
        $route = new Route([], 'foo/bar/baz');
        $this->assertEquals('/foo/bar/baz', (string) $route);
    }

    /**
     *
     */
    public function testMethods()
    {
        $route = new Route(['POST', 'PUT']);
        $this->assertEquals(['POST', 'PUT'], $route->getMethods());
    }

    /**
     *
     */
    public function testPattern()
    {
        $route = new Route([], 'foo/bar/baz');
        $this->assertEquals('/foo/bar/baz', $route->getPattern());
    }

    /**
     *
     */
    public function testAttributes()
    {
        $route = new Route();

        $this->assertEquals(null, $route->getAttribute('foo'));

        $route->setAttribute('foo', 'bar');
        $this->assertEquals('bar', $route->getAttribute('foo'));
        $this->assertEquals(['foo' => 'bar'], $route->getAttributes());

        $route->setAttribute('baz', 'qux');
        $this->assertEquals('qux', $route->getAttribute('baz'));
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $route->getAttributes());

        $route->setAttributes(['foo' => 'quux', 'corge' => 'grault']);
        $this->assertEquals('quux', $route->getAttribute('foo'));
        $this->assertEquals('grault', $route->getAttribute('corge'));
        $this->assertEquals(['foo' => 'quux', 'baz' => 'qux', 'corge' => 'grault'], $route->getAttributes());
    }

    public function testAddRoute()
    {
        $route = new Route();
        $childRoute = new Route([], '/foo');
        $returnedRoute = $route->addRoute($childRoute);

        $this->assertEquals([$childRoute], $route->getRoutes());
        $this->assertEquals($childRoute, $returnedRoute);
    }

    /**
     *
     */
    public function testInheritance()
    {
        $route = new Route(['GET'], '/foo/', ['foo' => 'bar', 'baz' => 'qux']);
        $childRoute = $route->map(['POST'],'bar/', ['foo' => 'quux', 'corge' => 'grault']);

        $this->assertEquals('/foo/bar/', $childRoute->getPattern());

        $this->assertEquals('bar', $route->getAttribute('foo'));
        $this->assertEquals('quux', $childRoute->getAttribute('foo'));
        $this->assertEquals('qux', $route->getAttribute('baz'));
        $this->assertEquals('qux', $childRoute->getAttribute('baz'));
        $this->assertEquals(null, $route->getAttribute('corge'));
        $this->assertEquals('grault', $childRoute->getAttribute('corge'));
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $route->getAttributes());
        $this->assertEquals(['foo' => 'quux', 'baz' => 'qux', 'corge' => 'grault'], $childRoute->getAttributes());
    }
}
