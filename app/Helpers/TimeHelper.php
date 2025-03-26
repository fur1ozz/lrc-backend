<?php

if (!function_exists('lrc_formatMillisecondsTwoDigits')) {
    /**
     * Convert milliseconds to "hours:minutes:seconds.milliseconds" format with 2-digit milliseconds.
     * Only displays hours if time exceeds 60 minutes.
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
