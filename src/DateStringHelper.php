<?php

namespace App;

class DateStringHelper
{
    public static function parseDeliveryDate($var): string
    {
        $yyyMMDD = preg_match("/[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $var);
        // DD/MM/YYYY format
        $DDMMYYYY = preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/", $var);
        if ($yyyMMDD) {
            return substr($var, -10);
        } elseif ($DDMMYYYY) {
            return substr($var, -10);
        } elseif (DateStringHelper::hasMonth($var)) {
            return DateStringHelper::getDateFromMonthyString($var);
        } elseif (DateStringHelper::hasTommorow($var)) {
            return date('Y-m-d', strtotime('+1 day'));
        } else {
            return 'N/A';
        }
    }

    public static function hasTommorow($var)
    {
        return strpos(strtolower($var), 'tomorrow') !== false;
    }

    public static function isDay($value)
    {
        return (preg_match('/^[0-9]{1,2}$/', $value) && (int) $value <= 31) || (preg_match('/[0-9]{1,2}(th|st|rd|nd)$/', $value));
    }

    public static function isYear($value)
    {
        return preg_match('/^[0-9]{4}$/', $value) && (int) $value <= 9999 && (int) $value >= 1500;
    }

    public static function isMonthNumber($value)
    {
        return preg_match('/^[0-9]{1,2}$/', $value) && (int) $value <= 12;
    }

    public static function isMonthName($value)
    {
        return preg_match('/^(January|February|March|April|May|June|July|August|September|October|November|December|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)$/', $value);
    }

    public static function hasMonth($value)
    {
        $ar = explode(' ', $value);
        foreach ($ar as  $value) {
            if (DateStringHelper::isMonthNumber($value)) {
                return true;
            }
            if (DateStringHelper::isMonthName($value)) {
                return true;
            }
        }
        return false;
    }

    public static function getDateFromMonthyString($value)
    {
        $ar = explode(' ', $value);
        $month = '';
        $day = '';
        $year = '';
        foreach ($ar as  $value) {
            if (DateStringHelper::isDay($value)) {
                if (preg_match('/[0-9]{1,2}(th|st|rd|nd)$/', $value)) {
                    $day = substr($value, 0, -2);
                } else {
                    $day = $value;
                }
                if (strlen($day) == 1) {
                    $day = '0' . $day;
                }
            }
            if (DateStringHelper::isMonthNumber($value)) {
                $month = $value;
            }
            if (DateStringHelper::isMonthName($value)) {
                $month = DateStringHelper::getMonthNumber($value);
            }
            if (DateStringHelper::isYear($value)) {
                $year = $value;
            }
        }
        return "$year-$month-$day";
    }

    public static function isMonth($value)
    {
        return DateStringHelper::isMonthNumber($value) || DateStringHelper::isMonthName($value);
    }

    public static function getMonthNumber($month)
    {
        $months = [
            'Jan' => '01',
            'Feb' => '02',
            'Mar' => '03',
            'Apr' => '04',
            'May' => '05',
            'Jun' => '06',
            'Jul' => '07',
            'Aug' => '08',
            'Sep' => '09',
            'Oct' => '10',
            'Nov' => '11',
            'Dec' => '12',
            'January' => '01',
            'February' => '02',
            'March' => '03',
            'April' => '04',
            'May' => '05',
            'June' => '06',
            'July' => '07',
            'August' => '08',
            'September' => '09',
            'October' => '10',
            'November' => '11',
            'December' => '12',
        ];

        return $months[$month];
    }
}
