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
 * Represents a time interval.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class TimeInterval implements \JsonSerializable
{
    private $start;
    private $end;

    /**
     * Creates a time interval.
     *
     * @param Time $start
     * @param Time $end
     *
     * @throws \InvalidArgumentException If the opening time is not earlier than closing time
     */
    public function __construct(Time $start, Time $end)
    {
        $this->start = $start;
        $this->end = $end;

        if ($start->isAfterOrEqual($end)) {
            throw new \InvalidArgumentException(sprintf(
                'The opening time "%s" must be before the closing time "%s".',
                $start->toString(),
                $end->toString()
            ));
        }
    }

    /**
     * Creates a new interval from time strings.
     *
     * @param string $startTime The start time
     * @param string $endTime   The end time
     *
     * @return TimeInterval
     *
     * @throws \InvalidArgumentException
     */
    public static function fromString($startTime, $endTime)
    {
        return new self(Time::fromString($startTime), Time::fromString($endTime));
    }

    /**
     * Checks if the interval contains the given time.
     *
     * @param Time $time
     *
     * @return bool
     */
    public function contains(Time $time)
    {
        return $this->start->isBeforeOrEqual($time) && $this->end->isAfter($time);
    }

    /**
     * Gets the end time.
     *
     * @return Time
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Gets the start time.
     *
     * @return Time
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
        ];
    }
}
