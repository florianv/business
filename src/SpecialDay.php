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

use SuperClosure\Serializer;

/**
 * Represents a special day.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class SpecialDay extends AbstractDay implements \Serializable, \JsonSerializable
{
    private $openingIntervalsCache = [];
    private $openingIntervalsEvaluator;

    /**
     * Creates a new special day.
     *
     * @param int      $dayOfWeek                 The day of week
     * @param callable $openingIntervalsEvaluator A callable to evaluate opening intervals of the day
     */
    public function __construct($dayOfWeek, callable $openingIntervalsEvaluator)
    {
        $this->setDayOfWeek($dayOfWeek);
        $this->openingIntervalsEvaluator = $openingIntervalsEvaluator;
    }

    /**
     * {@inheritdoc}
     */
    public function getClosestOpeningTimeBefore(Time $time, \DateTime $context)
    {
        $this->evaluateOpeningIntervals($context);

        return parent::getClosestOpeningTimeBefore($time, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getClosestOpeningTimeAfter(Time $time, \DateTime $context)
    {
        $this->evaluateOpeningIntervals($context);

        return parent::getClosestOpeningTimeAfter($time, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function isTimeWithinOpeningHours(Time $time, \DateTime $context)
    {
        $this->evaluateOpeningIntervals($context);

        return parent::isTimeWithinOpeningHours($time, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getOpeningTime(\DateTime $context)
    {
        $this->evaluateOpeningIntervals($context);

        return parent::getOpeningTime($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getClosingTime(\DateTime $context)
    {
        $this->evaluateOpeningIntervals($context);

        return parent::getClosingTime($context);
    }

    /**
     * Evaluates the opening intervals.
     *
     * @param \DateTime $context
     *
     * @throws \RuntimeException If the evaluated interval is invalid
     */
    private function evaluateOpeningIntervals(\DateTime $context)
    {
        $contextHash = $context->format(\DateTime::ISO8601);

        if (!isset($this->openingIntervalsCache[$contextHash])) {
            $intervals = call_user_func($this->openingIntervalsEvaluator, $context);

            if (!is_array($intervals)) {
                throw new \RuntimeException('The special day evaluator must return an array of opening intervals.');
            }

            $this->openingIntervalsCache[$contextHash] = $intervals;
        }

        $this->setOpeningIntervals($this->openingIntervalsCache[$contextHash]);
    }

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
        return [
            $this->dayOfWeek,
            $this->openingIntervalsCache,
            $this->getSerializer()->serialize($this->openingIntervalsEvaluator),
        ];
    }

    public function __unserialize(array $data): void
    {
        list($this->dayOfWeek, $this->openingIntervalsCache) = $data;
        $this->openingIntervalsEvaluator = $this->getSerializer()->unserialize($data[2]);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        $day = clone $this;
        $day->evaluateOpeningIntervals(new \DateTime('now'));

        return [
            'dayOfWeek'        => $day->getDayOfWeek(),
            'openingIntervals' => $day->openingIntervals,
        ];
    }

    /**
     * Gets a closure serializer object.
     *
     * @throws \RuntimeException If jeremeamia/superclosure is not installed
     *
     * @return Serializer
     */
    private function getSerializer()
    {
        if (!class_exists('SuperClosure\Serializer')) {
            throw new \RuntimeException('You must install "jeremeamia/superclosure" in order to serialize a special day.');
        }

        return new Serializer();
    }
}
