<?php

declare(strict_types=1);

/*
 * Copyright (c) 2025. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace nova\plugin\corn\schedule\Cron;

use DateTime;

/**
 * Minutes field.  Allows: * , / -
 */
class MinutesField extends AbstractField
{
    protected $rangeStart = 0;
    protected $rangeEnd = 59;

    public function isSatisfiedBy(DateTime $date, $value): bool
    {
        return $this->isSatisfied($date->format('i'), $value);
    }

    public function increment(DateTime $date, $invert = false, $parts = null): FieldInterface|static
    {
        if (is_null($parts)) {
            if ($invert) {
                $date->modify('-1 minute');
            } else {
                $date->modify('+1 minute');
            }
            return $this;
        }

        $parts = str_contains($parts, ',') ? explode(',', $parts) : array($parts);
        $minutes = array();
        foreach ($parts as $part) {
            $minutes = array_merge($minutes, $this->getRangeForExpression($part, 59));
        }

        $current_minute = $date->format('i');
        $position = $invert ? count($minutes) - 1 : 0;
        if (count($minutes) > 1) {
            for ($i = 0; $i < count($minutes) - 1; $i++) {
                if ((!$invert && $current_minute >= $minutes[$i] && $current_minute < $minutes[$i + 1]) ||
                    ($invert && $current_minute > $minutes[$i] && $current_minute <= $minutes[$i + 1])) {
                    $position = $invert ? $i : $i + 1;
                    break;
                }
            }
        }

        if ((!$invert && $current_minute >= $minutes[$position]) || ($invert && $current_minute <= $minutes[$position])) {
            $date->modify(($invert ? '-' : '+') . '1 hour');
            $date->setTime((int)$date->format('H'), $invert ? 59 : 0);
        } else {
            $date->setTime((int)$date->format('H'), (int)$minutes[$position]);
        }

        return $this;
    }
}
