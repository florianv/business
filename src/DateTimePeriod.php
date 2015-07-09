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
 * DateTime period implementation.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class DateTimePeriod implements \IteratorAggregate
{
    private $datePeriod;

    /**
     * Creates a new DateTime period.
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @throws \LogicException If start date is not earlier than end date
     */
    public function __construct(\DateTime $startDate, \DateTime $endDate)
    {
        $endDate = clone $endDate;
        $endDate->modify('+1 day');

        if ($startDate >= $endDate) {
            throw new \LogicException('Start date must be earlier than end date.');
        }

        $this->datePeriod = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->datePeriod;
    }
}
