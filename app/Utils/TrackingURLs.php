<?php declare(strict_types=1);

namespace App\Utils;

final class TrackingURLs
{
    /**
     * @param string $parcelNumbers
     * @return string
     */
    public static function czechPost(string $parcelNumbers): string
    {
        return 'https://www.postaonline.cz/trackandtrace/-/zasilka/cislo?parcelNumbers=' . $parcelNumbers;
    }
}
