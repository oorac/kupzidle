<?php declare(strict_types = 1);

namespace App\Controls\Address\Control;

use App\Controls\Address\Form\IAddressFormFactory;
use App\Controls\BaseControl;
use App\Forms\Form;
use App\Models\Address;
use App\Models\Repositories\AddressRepository;
use App\Services\Doctrine\EntityManager;
use App\Translator\Translator;
use Closure;
use Nette\Application\UI\Presenter;
use Nette\Neon\Exception;

/**
 * Class AddressControl
 * @package App\Controls\Address
 * @method onAdd(AddressControl $control, Address $address)
 * @method onAddError(AddressControl $control, string $message)
 * @method onEdit(AddressControl $control, Address $address)
 * @method onEditError(AddressControl $control, string $message)
 */
class AddressControl extends BaseControl
{
    /** @var array|callable[]|Closure[] */
    public array $onAdd = [];

    /** @var array|callable[]|Closure[] */
    public array $onAddError = [];

    /** @var array|callable[]|Closure[] */
    public array $onEdit = [];

    /** @var array|callable[]|Closure[] */
    public array $onEditError = [];

    /**
     * @var EntityManager
     * @inject
     */
    public EntityManager $entityManager;

    /**
     * @var Translator
     * @inject
     */
    public Translator $translator;

    /**
     * @var Presenter
     */
    public Presenter $presenter;

    /**
     * @var AddressRepository
     * @inject
     */
    public AddressRepository $addressRepository;

    /**
     * @var IAddressFormFactory
     * @inject
     */
    public IAddressFormFactory $addressFormFactory;

    /**
     * @param Translator $translator
     * @param Presenter $presenter
     * @param EntityManager $entityManager
     * @param AddressRepository $addressRepository
     * @param IAddressFormFactory $addressFormFactory
     */
    public function __construct(
        translator $translator,
        Presenter $presenter,
        EntityManager $entityManager,
        AddressRepository $addressRepository,
        IAddressFormFactory $addressFormFactory
    )
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->presenter = $presenter;
        $this->addressRepository = $addressRepository;
        $this->addressFormFactory = $addressFormFactory;

        $this->setTemplatePath(__DIR__ . '/addressControl.latte');
    }

    /**
     * @return void
     */
    public function render(): void
    {
        $template = $this->getTemplate();
        $template->setFile($this->getTemplatePath());
        $template->setTranslator($this->translator);

        $template->render();
    }

    /**
     * @return Form
     */
    protected function createComponentForm(): Form
    {
        $form = $this->addressFormFactory->create($this->translator);
        $form->onSuccess[] = [$this, 'process'];

        return $form;
    }

    /**
     * @param Form $form
     * @return void
     */
    public function process(Form $form): void
    {
        $data = $form->getValues();

        if (empty($data->offsetGet('id'))) {
            try {
                $address = (new Address())
                    ->setTitle($data->offsetGet('title'))
                    ->setStreet($data->offsetGet('street'))
                    ->setCity($data->offsetGet('city'))
                    ->setZipcode($data->offsetGet('zipCode'))
                    ->setPhone($data->offsetGet('phone'));

                $this->entityManager->persist($address);
                $this->entityManager->flush();
                $this->onAdd($this, $address);
            } catch (Exception $exception) {
                $this->onAddError($this, $this->translator->translate('errors.AnErrorOccurredDuringPurchase'));
                return;
            }
        } else {
            try {
                if (! $address = $this->addressRepository->find($data->offsetGet('id'))) {
                    $this->onEditError($this, $this->translator->translate('errors.RowDoesntExist'));
                    return;
                }

                $address->setTitle($data->offsetGet('title'))
                    ->setStreet($data->offsetGet('street'))
                    ->setCity($data->offsetGet('city'))
                    ->setZipcode($data->offsetGet('zipCode'))
                    ->setPhone($data->offsetGet('phone'));

                $this->entityManager->flush();
                $this->onEdit($this, $address);
            } catch (Exception $exception) {
                $this->onEditError($this, $this->translator->translate('errors.AnErrorOccurredDuringPurchase'));
                return;
            }
        }
    }
}