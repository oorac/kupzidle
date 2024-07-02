<?php

namespace App\Controls\Address\Form;

use App\Translator\Translator;

interface IAddressFormFactory
{
    /**
     * @param Translator $translator
     * @return AddressForm
     */
    public function create(Translator $translator): AddressForm;
}