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
 * Represents a standard business day.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class Day extends AbstractDay implements \Serializable, \JsonSerializable
{
    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([$this->dayOfWeek, $this->openingIntervals]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->dayOfWeek, $this->openingIntervals) = unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return
            [
                'dayOfWeek' => $this->dayOfWeek,
                'openingIntervals' => $this->openingIntervals,
            ];
    }
}
