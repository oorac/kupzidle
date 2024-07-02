<?php

/*
 *  _____ _______         _                      _
 * |_   _|__   __|       | |                    | |
 *   | |    | |_ __   ___| |___      _____  _ __| | __  ___ ____
 *   | |    | | '_ \ / _ \ __\ \ /\ / / _ \| '__| |/ / / __|_  /
 *  _| |_   | | | | |  __/ |_ \ V  V / (_) | |  |   < | (__ / /
 * |_____|  |_|_| |_|\___|\__| \_/\_/ \___/|_|  |_|\_(_)___/___|
 *                   ___
 *                  |  _|___ ___ ___
 *                  |  _|  _| -_| -_|  LICENCE
 *                  |_| |_| |___|___|
 *
 * IT ZPRAVODAJSTVÍ  <>  PROGRAMOVÁNÍ  <>  HW A SW  <>  KOMUNITA
 *
 * Tento zdrojový kód pochází z IT sociální sítě WWW.ITNETWORK.CZ
 *
 * Můžete ho upravovat a používat jak chcete, musíte však zmínit
 * odkaz na http://www.itnetwork.cz
 */

namespace App\Services\ItNetwork;

/**
 * Jednoduchá třída pro generování HTML kódu metodou SAX
 */
class HtmlBuilder
{
	/**
	 * @var string Výsledné HTML
	 */
	private $html = '';
	/**
	 * @var array Zásobník otevřených elementů
	 */
	private $elementStack = array();

	/**
	 * Vyrenderuje HTML element a jeho HTML kód připojí k privátnímu řetězci
	 * @param string $name Název elementu
	 * @param array $htmlParams Pole HTML atributů a jejich hodnot
	 * @param bool $pair Zda je elemenent párový
	 */
	private function renderElement($name, $htmlParams, $pair)
	{
		$this->html .= '<' . htmlspecialchars($name);
		foreach ($htmlParams as $key => $value)
		{
			$this->html .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
		}
		if (!$pair)
			$this->html .= ' /';
		$this->html .= '>';
		if ($pair)
			array_push($this->elementStack, $name);
	}

	/**
	 * Vyrendruje jednoduchý nepárový element
	 * @param string $name Název elementu
	 * @param array $htmlParams Pole HTML atributů a jejich hodnot
	 */
	public function addElement($name, $htmlParams = array())
	{
		$this->renderElement($name, $htmlParams, false);
	}

	/**
	 * Otevře párový element
	 * @param string $name Název
	 * @param array $htmlParams Pole HTML atributů a jejich hodnot
	 */
	public function startElement($name, $htmlParams = array())
    {
        $this->renderElement($name, $htmlParams, true);
    }

	/**
	 * Přidá HTML kód a to buď do otevřeného elementu nebo klidně mimo něj.
	 * @param string $value Hodnota
	 * @param bool $doNotEscape Zda se má hodnota převést na entity či nikoli
	 */
	public function addValue($value, $doNotEscape = false)
    {
        $this->html .= $doNotEscape ? $value : htmlspecialchars($value);
    }

	/**
	 * Uzavře poslední otevřený párový element nebo element s daným názvem.
	 * @param null $name Nepovinný název elementu
	 */
	public function endElement($name = null)
    {
        if (!$name)
            $name = array_pop($this->elementStack);
        $this->html .= '</' . htmlspecialchars($name) . '>';
    }

	/**
	 * Otevře párový element, vloží do něj hodnotu a poté ho uzavře.
	 * @param string $name Název
	 * @param string $value Hodnota
	 * @param array $htmlParams Pole HTML atributů a jejich hodnot
	 * @param bool $doNotEscape Zda se má hodnota převést na entity či nikoli
	 */
	function addValueElement($name, $value, $htmlParams = array(), $doNotEscape = false)
	{
		$this->startElement($name, $htmlParams, true);
		$this->addValue($value, $doNotEscape);
		$this->endElement();
	}

	/**
	 * Vrátí výsledný řetězec s HTML kódem
	 * @return string Výsledné HTML
	 */
	public function render()
    {
        return $this->html;
    }
}
