<?php

/*
 * This file is part of Business.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Business\Tests;

final class TestUtil
{
    /**
     * Gets a property value.
     *
     * @param object $object
     * @param string $propertyName
     *
     * @return mixed
     */
    public static function getPropertyValue($object, $propertyName)
    {
        $reflect = new \ReflectionClass($object);
        $property = $reflect->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    private function __construct()
    {
    }
}
