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

use Business\DateTimeStorage;

class DateTimeStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testHash()
    {
        $dateTimeStorage = new DateTimeStorage();
        $dateTime = new \DateTime('2015-07-08');

        $this->assertEquals('2015-07-08', $dateTimeStorage->getHash($dateTime));
    }
}
