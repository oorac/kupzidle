<?php declare(strict_types=1);

namespace App\Utils;

final class Number
{
    public const DEFAULT_CURRENCY = "Kč";

    public static function getPriceWithCurrency(float $price, string $currency = self::DEFAULT_CURRENCY): string
    {
        return number_format($price, 2, ',', ' ') . ' ' . $currency;
    }
}
