<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Format date to dd/mm/yyyy format
     * Handle both Carbon instances and string dates
     */
    public static function formatDate($date, $default = '')
    {
        if (empty($date)) {
            return $default;
        }

        try {
            // If it's already a Carbon instance
            if ($date instanceof Carbon) {
                return $date->format('d/m/Y');
            }

            // If it's a string, try to parse it
            if (is_string($date)) {
                $carbonDate = Carbon::parse($date);
                return $carbonDate->format('d/m/Y');
            }

            // If it's a DateTime instance
            if ($date instanceof \DateTime) {
                return $date->format('d/m/Y');
            }

            return $default;
        } catch (\Exception $e) {
            // If parsing fails, return the original value or default
            return is_string($date) ? $date : $default;
        }
    }

    /**
     * Format datetime to dd/mm/yyyy format (ignore time)
     */
    public static function formatDateTime($datetime, $default = '')
    {
        return self::formatDate($datetime, $default);
    }

    /**
     * Format date for display in CRUD columns
     */
    public static function getCrudDateFormatter()
    {
        return function($entry, $fieldName) {
            $date = $entry->{$fieldName} ?? null;
            return DateHelper::formatDate($date);
        };
    }
}
