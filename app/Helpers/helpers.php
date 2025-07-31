
<?php

if (!function_exists('formatRupiah')) {
    /**
     * Format a number to Rupiah currency string, removing decimal if .00
     *
     * @param float|int $amount
     * @param bool $withCurrencySymbol
     * @return string
     */
    function formatRupiah($amount, $withCurrencySymbol = true)
    {
        if (fmod($amount, 1) == 0) {
            $formatted = number_format($amount, 0, ',', '.');
        } else {
            $formatted = number_format($amount, 2, ',', '.');
        }

        return $withCurrencySymbol ? 'Rp ' . $formatted : $formatted;
    }
}
