<?php

namespace App\Controls\Address\Form;

use App\Forms\Form;
use App\Translator\Translator;

class AddressForm extends Form
{
    /**
     * @var Translator
     * @inject
     */
    public Translator $translator;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
        parent::__construct();
        
        $this->addHidden('id');
        $this->addText('title', $this->translator->translate('forms.title') . ' *')
            ->setHtmlAttribute('placeholder', $this->translator->translate('placeholder.title'))
            ->setRequired($this->translator->translate('errors.ThisIsRequired'));

        $this->addText('street', $this->translator->translate('forms.street') . ' *')
            ->setHtmlAttribute('placeholder', $this->translator->translate('placeholder.street'))
            ->setRequired($this->translator->translate('errors.ThisIsRequired'));

        $this->addText('city', $this->translator->translate('forms.city') . ' *')
            ->setHtmlAttribute('placeholder', $this->translator->translate('placeholder.city'))
            ->setRequired($this->translator->translate('errors.ThisIsRequired'));

        $this->addText('zipCode', $this->translator->translate('forms.zipCode') . ' *')
            ->setHtmlAttribute('placeholder', $this->translator->translate('placeholder.zipCode'))
            ->setRequired($this->translator->translate('errors.ThisIsRequired'));

        $this->addText('phone', $this->translator->translate('forms.phone'))
            ->setHtmlAttribute('placeholder', $this->translator->translate('placeholder.phone'))
            ->setRequired($this->translator->translate('errors.ThisIsRequired'));

        $this->addSubmit('submit', $this->translator->translate('actions.Save'));
    }
}