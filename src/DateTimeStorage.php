<?php

/*
 * This file is part of Business.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Business;

/**
 * Storage for DateTime objects.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class DateTimeStorage extends \SplObjectStorage implements \JsonSerializable
{
    /**
     * {@inheritdoc}
     */
    public function getHash($object): string
    {
        return $object->format('Y-m-d');
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        $data = [];
        /** @var \DateTime $dateTime */
        foreach ($this as $dateTime) {
            $data[] = $dateTime->format(\DateTime::ISO8601);
        }

        return $data;
    }
}
