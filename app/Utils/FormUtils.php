<?php declare(strict_types=1);

namespace App\Utils;

use App\Forms\Form;
use Nette\Forms\Controls\BaseControl;
use stdClass;

class FormUtils
{
    /**
     * @param string $value
     * @return string
     */
    public static function normalizePhoneNumber(string $value): string
    {
        return sprintf('%014d', Strings::filterNumbersOnly($value));
    }

    /**
     * @param Form $form
     * @param stdClass $data
     * @param string $property
     * @return string
     */
    public static function validatePhoneNumber(Form $form, stdClass $data, string $property): string
    {
        $phone = $data->$property;
        $phone = Strings::filterNumbersOnly($phone);
        $phone = ltrim($phone, '0');

        if (strlen($phone) === 12) {
            $data->$property = sprintf('%014d', $phone);
        } else {
            $form[$property]->addError('Zkontrolujte správnost telefonního čísla');
        }

        return $phone;
    }

    /**
     * @param string $value
     * @return string
     */
    public static function filterPhoneNumber(string $value): string
    {
        $value = ltrim(Strings::filterNumbersOnly($value), '0');

        return strlen($value) === 9 ? '420' . $value : $value;
    }

    /**
     * @param string $value
     * @return string
     */
    public static function filterBirthNumber(string $value): string
    {
        $value = ltrim(Strings::filterNumbersOnly($value), '0');

        return strlen($value) === 9 ? '420' . $value : $value;
    }

    /**
     * @param BaseControl $input
     * @return bool
     */
    public static function validateBirthNumber(BaseControl $input): bool
    {
        return ((int) $input->getValue()) % 11 === 0;
    }

    /**
     * @param string $value
     * @return float
     */
    public static function filterFloat(string $value): float
    {
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^\d.]/', '', $value);

        return (float) $value;
    }
}
