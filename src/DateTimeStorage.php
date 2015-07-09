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
final class DateTimeStorage extends \SplObjectStorage
{
    /**
     * {@inheritdoc}
     */
    public function getHash($object)
    {
        return $object->format('Y-m-d');
    }
}
