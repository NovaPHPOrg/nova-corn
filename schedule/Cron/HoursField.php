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
use DateTimeZone;

/**
 * Hours field.  Allows: * , / -
 */
class HoursField extends AbstractField
{
    protected $rangeStart = 0;
    protected $rangeEnd = 23;

    public function isSatisfiedBy(DateTime $date, $value)
    {
        return $this->isSatisfied($date->format('H'), $value);
    }

    public function increment(DateTime $date, $invert = false, $parts = null)
    {
        // Change timezone to UTC temporarily. This will
        // allow us to go back or forwards and hour even
        // if DST will be changed between the hours.
        if (is_null($parts) || $parts == '*') {
            $timezone = $date->getTimezone();
            $date->setTimezone(new DateTimeZone('UTC'));
            if ($invert) {
                $date->modify('-1 hour');
            } else {
                $date->modify('+1 hour');
            }
            $date->setTimezone($timezone);

            $date->setTime((int)$date->format('H'), $invert ? 59 : 0);
            return $this;
        }

        $parts = strpos($parts, ',') !== false ? explode(',', $parts) : array($parts);
        $hours = array();
        foreach ($parts as $part) {
            $hours = array_merge($hours, $this->getRangeForExpression($part, 23));
        }

        $current_hour = $date->format('H');
        $position = $invert ? count($hours) - 1 : 0;
        if (count($hours) > 1) {
            for ($i = 0; $i < count($hours) - 1; $i++) {
                if ((!$invert && $current_hour >= $hours[$i] && $current_hour < $hours[$i + 1]) ||
                    ($invert && $current_hour > $hours[$i] && $current_hour <= $hours[$i + 1])) {
                    $position = $invert ? $i : $i + 1;
                    break;
                }
            }
        }

        $hour = $hours[$position];
        if ((!$invert && $date->format('H') >= $hour) || ($invert && $date->format('H') <= $hour)) {
            $date->modify(($invert ? '-' : '+') . '1 day');
            $date->setTime($invert ? 23 : 0, $invert ? 59 : 0);
        } else {
            $date->setTime((int)$hour, $invert ? 59 : 0);
        }

        return $this;
    }
}
