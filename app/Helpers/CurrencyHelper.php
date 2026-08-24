<?php

if (!function_exists('format_cop')) {
    function format_cop($amount): string
    {
        return '$ ' . number_format((float) $amount, 0, ',', '.');
    }
}
