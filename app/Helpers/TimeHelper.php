<?php

if (!function_exists('lrc_formatMillisecondsTwoDigits')) {
    /**
     * Convert milliseconds to "hours:minutes:seconds.milliseconds" format with 2-digit milliseconds.
     * - Only displays hours if time exceeds 60 minutes.
     *
     * @param int $milliseconds Time in milliseconds
     * @return string Formatted time as "h:mm:ss.ms" or "mm:ss.ms"
     */
    function lrc_formatMillisecondsTwoDigits(int $milliseconds): string
    {
        $totalSeconds = floor($milliseconds / 1000);
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        $ms = floor(($milliseconds % 1000) / 10);

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d.%02d', $hours, $minutes, $seconds, $ms);
        }

        return sprintf('%d:%02d.%02d', $minutes, $seconds, $ms);
    }
}

if (!function_exists('lrc_formatMillisecondsAdaptive')) {
    /**
     * Convert milliseconds to a dynamically formatted time string with 2-digit milliseconds.
     * - Displays "s.ms" if under 60 seconds.
     * - Displays "m:ss.ms" if under 60 minutes.
     * - Displays "h:mm:ss.ms" if 1 hour or more.
     * - The number of displayed millisecond digits (precision) can be specified (default is 2).
     *
     * @param int $milliseconds Time in milliseconds
     * @param int $msPrecision Number of digits to display for milliseconds (recommended: 1-3).
     * @return string Formatted time
     */
    function lrc_formatMillisecondsAdaptive(int $milliseconds, int $msPrecision = 2): string
    {
        $totalSeconds = floor($milliseconds / 1000);
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        $ms = floor(($milliseconds % 1000) / pow(10, 3 - $msPrecision));

        if ($totalSeconds < 60) {
            return sprintf('%d.%0'.$msPrecision.'d', $seconds, $ms);
        } elseif ($totalSeconds < 3600) {
            return sprintf('%d:%02d.%0'.$msPrecision.'d', $minutes, $seconds, $ms);
        } else {
            return sprintf('%d:%02d:%02d.%0'.$msPrecision.'d', $hours, $minutes, $seconds, $ms);
        }
    }
}
