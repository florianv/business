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
 * Collection of Holiday date representations.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class Holidays implements \Serializable, \JsonSerializable
{
    private $holidays;

    /**
     * Creates a new holiday collection.
     *
     * @param \DateTime[]|DateRange[] $holidays
     */
    public function __construct(array $holidays = [])
    {
        $this->holidays = new DateTimeStorage();

        $this->addHolidays($holidays);
    }

    /**
     * Checks if a given date is holiday.
     *
     * @param \DateTime $date
     *
     * @return bool
     *
     * @internal
     */
    public function isHoliday(\DateTime $date)
    {
        return $this->holidays->contains($date);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize($this->holidays);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $this->holidays = unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $holidays = [];
        /** @var \DateTime $holiday */
        foreach ($this->holidays as $holiday) {
            $holidays[] = $holiday->format(\DateTime::ISO8601);
        }

        return $holidays;
    }

    /**
     * Adds a day.
     *
     * @param \DateTime $holiday
     */
    public function addHoliday(\DateTime $holiday)
    {
        $this->holidays->attach($holiday);
    }

    /**
     * Adds a set of days.
     *
     * @param \DateTime[]|DateRange[]|DateRange $holidays
     */
    public function addHolidays($holidays)
    {
        foreach ($holidays as $holiday) {
            if ($holiday instanceof DateRange) {
                $this->addHolidays($holiday);

                continue;
            }

            $this->addHoliday($holiday);
        }
    }
}
