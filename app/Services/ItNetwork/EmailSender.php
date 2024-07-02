<?php

/*  _____ _______         _                      _
 * |_   _|__   __|       | |                    | |
 *   | |    | |_ __   ___| |___      _____  _ __| | __  ___ ____
 *   | |    | | '_ \ / _ \ __\ \ /\ / / _ \| '__| |/ / / __|_  /
 *  _| |_   | | | | |  __/ |_ \ V  V / (_) | |  |   < | (__ / /
 * |_____|  |_|_| |_|\___|\__| \_/\_/ \___/|_|  |_|\_(_)___/___|
 *                                _
 *              ___ ___ ___ _____|_|_ _ _____
 *             | . |  _| -_|     | | | |     |  LICENCE
 *             |  _|_| |___|_|_|_|_|___|_|_|_|
 *             |_|
 *
 * IT ZPRAVODAJSTVÍ  <>  PROGRAMOVÁNÍ  <>  HW A SW  <>  KOMUNITA
 *
 * Tento zdrojový kód je součástí výukových seriálů na
 * IT sociální síti WWW.ITNETWORK.CZ
 *
 * Kód spadá pod licenci prémiového obsahu a vznikl díky podpoře
 * našich členů. Je určen pouze pro osobní užití a nesmí být šířen.
 */

namespace App\Services\ItNetwork;
use Settings;

/**
 * Pomocná třída, poskytující metody pro odeslání emailu
 */
class EmailSender
{

	/**
	 * Odešle email jako HTML, lze tedy používat základní HTML tagy a nové
	 * řádky je třeba psát jako <br /> nebo používat odstavce. Kódování je
	 * odladěno pro UTF-8.
	 * @param string $address Adresa
	 * @param string $subject Předmět
	 * @param string $message Zpráva
	 * @param string $from Adresa odesílatele
	 * @throws UserException
	 */
	public function send($address, $subject, $message, $from, string $att = '')
	{
		$header = "From: " . $from;
		$header .= "\nMIME-Version: 1.0\n";
		$header .= "Content-Type: text/html; charset=\"utf-8\"\n";
		if(!empty($att))
		    $header .= '; name=\"'.$att.'\"\r\n';
		if (Settings::$debug)
		{
			file_put_contents('files/emails/' . uniqid(), $message);
			return;
		}
		if (!mb_send_mail($address, $subject, $message, $header))
			throw new UserException('Email se nepodařilo odeslat.');
	}

	/**
	 * Zkontroluje, zda byl zadán aktuální rok jako antispam a odešle email
	 * @param int $year Aktuální rok
	 * @param string $address Adresa
	 * @param string $subject Předmět
	 * @param string $message Zpráva
	 * @param $from Adresa odesílatele
	 * @throws UserException
	 */
	public function sendWithAntispam($year, $address, $subject, $message, $from)
	{
		if ($year != date("Y"))
			throw new UserException('Chybně vyplněný antispam.');
		$this->send($address, $subject, $message, $from);
	}

}
