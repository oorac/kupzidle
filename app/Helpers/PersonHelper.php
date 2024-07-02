<?php declare(strict_types=1);

namespace App\Helpers;

use App\Models\User;
use App\Utils\CzechVocative;

class PersonHelper
{
    /**
     * @param User $user
     * @return string
     */
    public static function getVocativeName(User $user): string
    {
        if (str_contains($name = $user->getFirstname(), '@')) {
            return $name;
        }

        return CzechVocative::convert($name, $user->getSex());
    }
}