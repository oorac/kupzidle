<?php declare(strict_types=1);

namespace App\Translator;

use App\Helpers\LocalesHelper;
use App\Translator\Tracy\Panel;
use App\Utils\Arrays;
use Nette\Localization\Translator as NetteTranslator;

class Translator implements NetteTranslator
{
    /**
     * @var string|null
     */
    private ?string $active = null;

    /**
     * @var Panel|null
     */
    private ?Panel $tracyPanel;

    /**
     * @var Translations
     */
    private Translations $translations;

    /**
     * @var MessageSelector
     */
    private MessageSelector $messageSelector;

    /**
     * @param array $directories
     * @param string $storage
     * @param bool $debug
     * @param Panel|null $tracyPanel
     */
     public function __construct(array $directories, string $storage, bool $debug, ?Panel $tracyPanel = null)
     {
        $this->tracyPanel = $tracyPanel;
        $this->translations = new Translations($storage, $directories, $debug, $tracyPanel);
        $this->messageSelector = new MessageSelector();
     }

    /**
     * @param mixed $message
     * @param mixed ...$parameters
     * @return string
     */
    public function translate($message, ...$parameters): string
    {
        $locale = empty($parameters[1]) ? ($this->active ?? LocalesHelper::getActive()) : $parameters[1];
        $parameters = (array) reset($parameters);

        // check locales
        foreach ([$locale, LocalesHelper::getDefault()] as $locale) {
            $key = $locale . '.' . $message;
            if ($translation = $this->translations->get($key)) {
                return $this->saySentence($translation, $parameters);
            }

            $this->logMissing($key);
        }

        return $message;
    }

    /**
     * @param string $message
     * @param string|null $locale
     * @return bool
     */
    public function hasTranslation(string $message, ?string $locale = null): bool
    {
        return $this->translations->has(($locale ?: LocalesHelper::getActive()) . '.' . $message);
    }

    /**
     * @return Translations
     */
    public function getTranslations(): Translations
    {
        return $this->translations;
    }

    /**
     * @param string $locale
     * @return $this
     */
	public function setLocale(string $locale): self
    {
        $this->active = $locale;

        return $this;
    }

    /**
     * @return string
     */
	public function getLocale(): string
    {
        return $this->active ?? LocalesHelper::getActive();
    }

    /**
     * @param string $translation
     * @param array $params
     * @return string
     */
    private function saySentence(string $translation, array $params): string
    {
        if (empty($params)) {
            return $translation;
        }

        if (! str_contains($translation, '|')) {
            return $this->fillSentence($translation, $params);
        }

        $numeric = (float) Arrays::findNumber($params) ?: 0.0;
        $sentence = $this->messageSelector->choose($translation, $numeric);

        return $this->fillSentence($sentence, $params);
    }

    /**
     * @param string $translation
     * @param array $params
     * @return string
     */
    private function fillSentence(string $translation, array $params): string
    {
        $search = [];
        $replace = [];

        foreach ($params as $key => $value) {
            $search[] = ':' . $key;
            $replace[] = $value;
        }

        return str_replace($search, $replace, $translation);
    }

    /**
     * @param string $key
     */
    private function logMissing(string $key): void
    {
        if (! $this->tracyPanel) {
            return;
        }

        $this->tracyPanel->logMissingTranslation($key);
    }
}
