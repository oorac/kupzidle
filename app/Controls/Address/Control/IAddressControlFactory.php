<?php declare(strict_types=1);

namespace App\Controls\Address\Control;

use App\Translator\Translator;
use Nette\Application\UI\Presenter;

interface IAddressControlFactory
{
    /**
     * @param Translator $translator
     * @param Presenter $presenter
     * @return AddressControl
     */
	public function create(Translator $translator, Presenter $presenter): AddressControl;
}
