<?php declare(strict_types=1);

namespace App\Translator\Tracy;

use Contributte;
use App\Helpers\LocalesHelper;
use Tracy\IBarPanel;

class Panel implements IBarPanel
{
    /**
     * @var array
     */
    private array $missingTranslations = [];

    /**
     * @var array
     */
    private array $translations = [];

    /**
     * @param string $translation
     * @return $this
     */
    public function logMissingTranslation(string $translation): self
    {
        $this->missingTranslations[] = $translation;

        return $this;
    }

    /**
     * @param array $translations
     * @return $this
     */
    public function logTranslations(array $translations): self
    {
        $this->translations = $translations;

        return $this;
    }

    /**
     * @return string
     */
	public function getTab(): string
	{
		$icon = '<svg version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><path style="fill:#FC644F;" d="M389.333,270.933l120.533,163.2C419.2,464,210.133,457.6,129.067,416V125.867 c80,41.6,290.133,49.067,380.8,18.133L389.333,270.933L389.333,270.933z"/><polygon style="fill:#F1543F;" points="129.067,414.933 194.133,382.933 146.133,237.867 129.067,262.4 "/><path style="fill:#FF7058;" d="M40.533,46.933V336c0,0,75.733-20.267,152.533,45.867v-288 C116.267,26.667,40.533,46.933,40.533,46.933z"/><path style="fill:#40596B;" d="M32,0L32,0C16,0,2.133,12.8,2.133,28.8V512H60.8V28.8C60.8,12.8,48,0,32,0z"/></svg>';

		return '<span title="Translator">' . $icon . ' ' . LocalesHelper::getActive() . ' / ' . LocalesHelper::getDefault() . '</span>';
	}

    /**
     * @return string
     */
	public function getPanel(): string
	{
		// missing
        $missingSection = '';
        if (! empty($this->missingTranslations)) {

            $missingSection .= '<div class="tracy-inner-container">';
            $missingSection .= '<h2>Usage - Missing Translations (' . $this->formatNumber(count($this->missingTranslations)) . ')</h2>';
            $missingSection .= '<table class="">';
            $missingSection .= '<tbody>';

            foreach ($this->missingTranslations as $translation) {
                $missingSection .= '<tr>';
                $missingSection .= '<td>' . $translation . '</td>';
                $missingSection .= '</tr>';
            }

            $missingSection .= '</tbody>';
            $missingSection .= '</table>';
            $missingSection .= '</div>';
        }

        // configuration
        $configurationSection = '';
        $configurationSection .= '<div class="tracy-inner-container">';
            $configurationSection .= '<h2>Configuration</h2>';
            $configurationSection .= '<table class="">';
                $configurationSection .= '<tbody>';

                $configurationSection .= '<tr>';
                $configurationSection .= '<td>Active</td>';
                $configurationSection .= '<td>' . LocalesHelper::getActive(). '</td>';
                $configurationSection .= '</tr>';

                $configurationSection .= '<tr>';
                $configurationSection .= '<td>Default</td>';
                $configurationSection .= '<td>' . LocalesHelper::getDefault(). '</td>';
                $configurationSection .= '</tr>';

                $configurationSection .= '<tr>';
                $configurationSection .= '<td>Whitelist</td>';
                $configurationSection .= '<td>' . implode(', ', LocalesHelper::getWhitelist()) . '</td>';
                $configurationSection .= '</tr>';

                $configurationSection .= '</tbody>';
            $configurationSection .= '</table>';
        $configurationSection .= '</div>';

		// all
        $separated = [];
        $locales = [];

        $allSection = '';
		if (! empty($this->translations)) {

		    $allSection .= '<div class="tracy-inner-container">';
            $allSection .= '<h2>All Translations (' . $this->formatNumber(count($this->translations)) . ')</h2>';
            $allSection .= '<table class="tracy-sortable">';
            $allSection .= '<tbody>';

            foreach ($this->translations as $key => $translation) {
                $parts = explode('.', $key);
                $locale = array_shift($parts);
                $key = implode('.', $parts);

                // for analyse
                $separated[$key][$locale] = $translation;
                if (! in_array($locale, $locales, true)) {
                    $locales[] = $locale;
                }

                 $allSection .= '<tr>';
                    $allSection .= '<td>' . $key . '</td>';
                    $allSection .= '<td>' . $locale . '</td>';
                    $allSection .= '<td>' . $translation . '</td>';
                 $allSection .= '</tr>';
		    }

            $allSection .= '</tbody>';
            $allSection .= '</table>';
            $allSection .= '</div>';
        }

		// post analyse
        $missingTranslations = [];
		$missingCount = 0;
        foreach ($separated as $key => $defines) {
            foreach ($locales as $locale) {
                if (empty($defines[$locale])) {
                    $missingTranslations[$key][] = $locale;
                    $missingCount++;
                }
            }
		}

        $analyseSection = '';
        if ($missingCount > 0) {

            $analyseSection .= '<div class="tracy-inner-container">';
            $analyseSection .= '<h2>Analyse - Missing Translations (' . $this->formatNumber($missingCount) . ')</h2>';
            $analyseSection .= '<table class="">';
            $analyseSection .= '<tbody>';

            foreach ($missingTranslations as $key => $locales) {
                $analyseSection .= '<tr>';
                $analyseSection .= '<td>' . $key . '</td>';
                $analyseSection .= '<td>' . implode(', ', $locales) . '</td>';
                $analyseSection .= '</tr>';
            }

            $analyseSection .= '</tbody>';
            $analyseSection .= '</table>';
            $analyseSection .= '</div>';
        }

        // build panels
        $panel = [];
		$panel[] = '<h1>Translator</h1>';
        $panel[] = $missingSection;
        $panel[] = $analyseSection;
        $panel[] = $configurationSection;
        $panel[] = $allSection;

		return implode($panel);
	}

    /**
     * @param int $number
     * @return string
     */
	private function formatNumber(int $number): string
    {
        return number_format($number, 0, '.', ' ');
    }
}
