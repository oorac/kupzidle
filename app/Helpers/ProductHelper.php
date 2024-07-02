<?php declare(strict_types=1);

namespace App\Helpers;

/**
 * Soubor formátovacích metod pro produkty
 */
class ProductHelper
{
	/**
	 * Vygeneruje počet plných a prázdných hvězdiček na základě součtu hlasů a jejich počtu
	 * @param int $sum Počet znaků
	 * @param int $count Počet hlasů (nemusí být zadáno, pokud chceme jednoduše zobrazit určitý počet hvězdiček)
	 * @return string
	 */
	public static function rating($sum, $count = 1)
	{
		$rating = ($count > 0) ? round($sum / $count) : 0;
		return str_repeat('<i class="fa fa-star"></i>', $rating) .
			str_repeat('<i class="fa fa-star-o"></i>', 5 - $rating);
	}

	/**
	 * Vrátí slevu podle staré a nové ceny ve formátu např.: "-20%"
	 * @param float $oldPrice Původní cena
	 * @param float $newPrice Nová cena
	 * @return string Sleva ve formátu "-X%"
	 */
	public static function sale($oldPrice, $newPrice)
	{
		return '-' . round(($oldPrice - $newPrice) / ($oldPrice / 100)) . '%';
	}

	public static function salePrice($price, $sale, bool $currency = false)
	{
		$sale = $price * ($sale / 100);
		if($currency)
			return '-' . FormatHelper::currency($sale);
		else
			return '-' . $sale;
	}

	/**
	 * Zformátuje cenu a připočte k ní DPH
	 * @param float $price Cena bez DPH
	 * @param int $vat Sazba DPH v %
	 * @param bool $round Zda si přejeme výslednou cenu zaokrouhlit na celé koruny
	 * @return string Zformátovaná cena s DPH
	 */
	public static function priceWithVat($price, $vat, $round = false, bool $currency = true)
	{
		$amount = $price * (1 + ($vat / 100));

		if ($round)
			$amount = round($amount);
		if($currency)
			return FormatHelper::currency($amount);
		else
			return $amount;
	}

	/**
	 * Zformátuje DPH z celkové ceny
	 * @param float $price Celková cena s DPH
	 * @param int $vat Sazba DPH v %
	 * @param bool $round Zda si přejeme částku zaokrouhlit na celé koruny
	 * @return string Zformátovaná částka DPH z celkové ceny
	 */
	public static function vatFromPrice($price, $vat, $round)
	{
		$amount = $price * ($vat / 100);
		if ($round)
			$amount = round($amount);
		return round($amount);
	}

    /**
     * Zformátuje DPH z celkové ceny
     * @param float $price Celková cena s DPH
     * @param int $vat Sazba DPH v %
     * @param bool $round Zda si přejeme částku zaokrouhlit na celé koruny
     * @return string Zformátovaná částka DPH z celkové ceny
     */
    public static function priceWithoutVat($price, $vat, bool $currency = true)
    {
        $amount = $price / (1 + ($vat / 100));
		if($currency)
        	return FormatHelper::currency($amount);
		else
			return $amount;
    }

	/**
	 * Zformátuje cenu a připočte k ní DPH podle toho, zda jsme plátci
	 * @param float $price Cena bez DPH
	 * @param bool $vatPayer Zda jsme plátci DPH
	 * @param int $vat Sazba DPH v %
	 * @return string Zformátovaná částka
	 */
	public static function price($price, $vatPayer, $vat)
	{
		if ($vatPayer)
			return self::priceWithVat($price, $vat);
		return FormatHelper::currency($price);
	}

    /**
     * Zformátuje cenu a odečte slevu
     * @param float $price Cena
     * @param float $sale Sleva v %
     * @return string Zformátovaná částka
     */
    public static function priceWithSale($price, $sale = null, bool $currency = true)
    {
        If(is_null($sale))
		{
			if($currency)
				return FormatHelper::currency($price);
			else
				return $price;
		}
        $price_with_sale = $price * ((100 - $sale) / 100);
		if($currency)
			return FormatHelper::currency($price_with_sale);
		else
			return $price_with_sale;
    }
}
