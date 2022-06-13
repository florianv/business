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
use PHPUnit\Framework\TestCase;

class DateTimeStorageTest extends TestCase
{
    public function testHash()
    {
        $dateTimeStorage = new DateTimeStorage();
        $dateTime = new \DateTime('2015-07-08');

        $this->assertEquals('2015-07-08', $dateTimeStorage->getHash($dateTime));
    }

    public function testJsonSerialize()
    {
        $dateTimeStorage = new DateTimeStorage();
        $dateTimeStorage->attach(new \DateTime('2016-02-23'));
        $dateTimeStorage->attach(new \DateTime('2016-02-24'));
        $dateTimeStorage->attach(new \DateTime('2016-02-25'));

        $this->assertJsonStringEqualsJsonFile(
            __DIR__.'/Expected/DateTimeStorage/testJsonSerialize.json',
            json_encode($dateTimeStorage)
        );
    }
}
