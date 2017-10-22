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

/**
 * Interface RouteAdapterInterface
 *
 * A router adapter converts routes or collections of routes to a simpler
 * simpler format, which can be used by a specific resolver
 */
interface RouteAdapterInterface
{
    /**
     * This method returns an processed array which can be used by the resolver
     * without any further modifications
     *
     * the format will be defined by the resolver
     *
     * @return array
     */
    public function convert() : array;
}