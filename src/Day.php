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
        return serialize($this->__serialize());
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->__unserialize($data);
    }

    public function __serialize(): array
    {
        return [$this->dayOfWeek, $this->openingIntervals];
    }

    public function __unserialize(array $data): void
    {
        [$this->dayOfWeek, $this->openingIntervals] = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return
            [
                'dayOfWeek'        => $this->dayOfWeek,
                'openingIntervals' => $this->openingIntervals,
            ];
    }
}
