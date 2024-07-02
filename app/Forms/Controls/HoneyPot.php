<?php declare(strict_types=1);

namespace App\Forms\Controls;

use App\Utils\Arrays;
use App\Utils\Random;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\Html;

class HoneyPot extends BaseControl
{
    private const STYLE = 'height: 1px; width: 1px; line-height: 1px; font-size: 1px; opacity: 0.0001; overflow: hidden; display: inline-block; position: absolute; left: -9999999px;';

    private const TYPE_TEXT = 'text';
    private const TYPE_NUMBER = 'number';
    private const TYPE_EMAIL = 'email';

    public function __construct()
    {
        parent::__construct();

        // Auto-render hack
        $this->setOption('type', 'hidden');

        // must be empty on submit!
        $this->getRules()->addRule(Form::BLANK);
    }

    /**
     * @return Html
     */
    public function getControl(): Html
    {
        $this->setOption('rendered', true);
        $id = str_replace('.', '', uniqid('form_hid__rand____digilabs_____', true));

        return match (Arrays::random([self::TYPE_TEXT, self::TYPE_NUMBER, self::TYPE_EMAIL])) {
            self::TYPE_NUMBER => $this->getControlNumber($id),
            self::TYPE_EMAIL => $this->getControlEmail($id),
            default => $this->getControlText($id),
        };
    }

    /**
     * @param null $caption
     */
    public function getLabel($caption = null): void {}

    /**
     * @param string $id
     * @return Html
     */
    private function getControlText(string $id): Html
    {
        $value = Random::bool() ? Random::generate(Random::int(14, 96), 'A-Za-z ') : null;

        return Html::el('div')
            ->addHtml('<input name="' . $this->getHtmlName() . '" type="text" data-hid="' . $id . '" value="' . $value . '" autocomplete="' . $id . '" required>')
            ->addHtml($this->generateScript($id, $value))
            ->setAttribute('style', self::STYLE);
    }

    /**
     * @param string $id
     * @return Html
     */
    private function getControlNumber(string $id): Html
    {
        $value = Random::bool() ? (string) Random::int(0, 99999999) : null;

        return Html::el('div')
            ->addHtml('<input name="' . $this->getHtmlName() . '" type="text" data-hid="' . $id . '" value="' . $value . '" autocomplete="' . $id . '" required>')
            ->addHtml($this->generateScript($id, $value))
            ->setAttribute('style', self::STYLE);
    }

    /**
     * @param string $id
     * @return Html
     */
    private function getControlEmail(string $id): Html
    {
        $value = Random::bool() ? Random::generate(Random::int(4, 16)) . '@' . Random::generate(Random::int(4, 16)) . '.' . Random::generate(Random::int(2, 3), 'a-z') : null;

        return Html::el('div')
            ->addHtml('<input name="' . $this->getHtmlName() . '" type="text" data-hid="' . $id . '" value="' . $value . '" autocomplete="' . $id . '" required>')
            ->addHtml($this->generateScript($id, $value))
            ->setAttribute('style', self::STYLE);
    }

    /**
     * @param string $id
     * @param string|null $value
     * @return string
     */
    private function generateScript(string $id, ?string $value): string
    {
        if ($value) {
            $timeout = Random::int(100, 2000);

            return '<script>setTimeout(function () { let node = document.querySelector(\'[data-hid="' . $id . '"]\'); node.required = false; node.value = ""; }, ' . $timeout . ' )</script>';
        }

        return '<script>document.querySelector(\'[data-hid="' . $id . '"]\').required = false;</script>';
    }
}
